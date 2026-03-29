<?php

declare(strict_types=1);

namespace MohitKhare;

/**
 * Core client and configuration for MohitKhare developer utilities.
 *
 * Acts as the entry point and service locator for the token estimation
 * and text analysis tools. Provides version metadata, model constants,
 * and factory methods for the utility classes.
 *
 * @see https://mohitkhare.me
 * @license MIT
 */
class Client
{
    public const VERSION = '0.1.1';
    public const BASE_URL = 'https://mohitkhare.me';

    /** Supported model families for token estimation */
    public const MODELS = [
        'gpt-4'         => ['tokensPerWord' => 1.3, 'encoding' => 'cl100k_base'],
        'gpt-3.5-turbo' => ['tokensPerWord' => 1.3, 'encoding' => 'cl100k_base'],
        'claude-3'      => ['tokensPerWord' => 1.35, 'encoding' => 'claude'],
        'llama-3'       => ['tokensPerWord' => 1.25, 'encoding' => 'llama'],
        'default'       => ['tokensPerWord' => 1.3, 'encoding' => 'cl100k_base'],
    ];

    /** Truncation strategies */
    public const TRUNCATION_STRATEGIES = ['end', 'middle', 'sentences'];

    private readonly string $defaultModel;

    /**
     * @param string $defaultModel Default model for token estimation
     */
    public function __construct(string $defaultModel = 'gpt-4')
    {
        if (!isset(self::MODELS[$defaultModel])) {
            throw new \InvalidArgumentException(
                "Unknown model '{$defaultModel}'. Supported: " . implode(', ', array_keys(self::MODELS))
            );
        }

        $this->defaultModel = $defaultModel;
    }

    public function getVersion(): string
    {
        return self::VERSION;
    }

    public function getBaseUrl(): string
    {
        return self::BASE_URL;
    }

    public function getDefaultModel(): string
    {
        return $this->defaultModel;
    }

    /**
     * Create a new TokenEstimator instance.
     */
    public function createEstimator(): TokenEstimator
    {
        return new TokenEstimator(model: $this->defaultModel);
    }

    /**
     * Create a new TextAnalyzer instance.
     */
    public function createAnalyzer(): TextAnalyzer
    {
        return new TextAnalyzer();
    }

    /**
     * Shorthand: estimate tokens for a single string.
     */
    public function estimateTokens(string $text): int
    {
        return $this->createEstimator()->estimate($text);
    }

    /**
     * Shorthand: analyze text properties.
     *
     * @return array{words: int, sentences: int, readability: float, reading_time_sec: int}
     */
    public function analyzeText(string $text): array
    {
        return $this->createAnalyzer()->analyze($text);
    }

    /**
     * Get the tokens-per-word ratio for a model.
     */
    public static function tokensPerWord(string $model = 'default'): float
    {
        $config = self::MODELS[$model] ?? self::MODELS['default'];
        return $config['tokensPerWord'];
    }
}
