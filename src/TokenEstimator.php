<?php

declare(strict_types=1);

namespace MohitKhare;

/**
 * Approximate token counts using byte-pair encoding heuristics.
 *
 * Calibrated against the tiktoken reference implementation with
 * model-specific adjustments for GPT-4, Claude, and Llama tokenizers.
 * Stateless and safe for long-running processes.
 *
 * @see https://mohitkhare.me
 */
class TokenEstimator
{
    private readonly string $model;
    private readonly float $tokensPerWord;

    public function __construct(string $model = 'gpt-4')
    {
        $this->model = $model;
        $config = Client::MODELS[$model] ?? Client::MODELS['default'];
        $this->tokensPerWord = $config['tokensPerWord'];
    }

    /**
     * Estimate the token count for a single text string.
     *
     * Uses a word-count heuristic scaled by the model's average
     * tokens-per-word ratio, then adds overhead for whitespace
     * and special tokens.
     */
    public function estimate(string $text): int
    {
        if ($text === '') {
            return 0;
        }

        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);

        // Base estimate from word count
        $tokens = (int) ceil($wordCount * $this->tokensPerWord);

        // Add overhead for punctuation and special characters
        $specialChars = preg_match_all('/[^\w\s]/', $text);
        $tokens += (int) ceil($specialChars * 0.5);

        return max(1, $tokens);
    }

    /**
     * Estimate token counts for multiple texts at once.
     *
     * @param string[] $texts Array of text strings
     * @param string|null $model Override the default model for this batch
     * @return int[] Token counts in the same order as input
     */
    public function estimateBatch(array $texts, ?string $model = null): array
    {
        if ($model !== null && $model !== $this->model) {
            $estimator = new self(model: $model);
            return array_map(fn(string $t) => $estimator->estimate($t), $texts);
        }

        return array_map(fn(string $t) => $this->estimate($t), $texts);
    }

    /**
     * Truncate text to fit within a token budget.
     *
     * @param string $text      Input text
     * @param int    $maxTokens Maximum allowed tokens
     * @param string $strategy  Truncation strategy: 'end', 'middle', or 'sentences'
     * @return self|null Returns $this for chaining, or null if text was empty
     */
    public function truncate(
        string $text,
        int $maxTokens,
        string $strategy = 'end',
    ): ?TruncatedText {
        if ($text === '') {
            return null;
        }

        $currentTokens = $this->estimate($text);
        if ($currentTokens <= $maxTokens) {
            return new TruncatedText($text, $currentTokens, false);
        }

        $ratio = $maxTokens / max($currentTokens, 1);
        $targetLength = (int) floor(strlen($text) * $ratio);

        $truncated = match ($strategy) {
            'middle' => $this->truncateMiddle($text, $targetLength),
            'sentences' => $this->truncateSentences($text, $maxTokens),
            default => substr($text, 0, $targetLength),
        };

        return new TruncatedText($truncated, $this->estimate($truncated), true);
    }

    public function getModel(): string
    {
        return $this->model;
    }

    private function truncateMiddle(string $text, int $targetLength): string
    {
        $half = (int) floor($targetLength / 2);
        return substr($text, 0, $half) . ' [...] ' . substr($text, -$half);
    }

    private function truncateSentences(string $text, int $maxTokens): string
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', $text);
        $result = '';

        foreach ($sentences as $sentence) {
            $candidate = $result === '' ? $sentence : $result . ' ' . $sentence;
            if ($this->estimate($candidate) > $maxTokens) {
                break;
            }
            $result = $candidate;
        }

        return $result ?: substr($text, 0, 100);
    }
}
