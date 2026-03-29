<?php

declare(strict_types=1);

namespace MohitKhare;

/**
 * Compute word counts, sentence boundaries, reading time,
 * and Flesch-Kincaid readability scores.
 *
 * Stateless and safe for long-running processes like queue
 * workers or daemon scripts.
 *
 * @see https://mohitkhare.me
 */
class TextAnalyzer
{
    /** Average reading speed in words per minute */
    private const READING_WPM = 238;

    /**
     * Analyze a text string and return its properties.
     *
     * @return array{words: int, sentences: int, readability: float, reading_time_sec: int}
     */
    public function analyze(string $text): array
    {
        $words = $this->countWords($text);
        $sentences = $this->countSentences($text);
        $syllables = $this->countSyllables($text);

        return [
            'words' => $words,
            'sentences' => $sentences,
            'readability' => $this->fleschKincaid(
                words: $words,
                sentences: $sentences,
                syllables: $syllables,
            ),
            'reading_time_sec' => (int) ceil(($words / self::READING_WPM) * 60),
        ];
    }

    /**
     * Count words in a text string.
     */
    public function countWords(string $text): int
    {
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        return count($words);
    }

    /**
     * Count sentences based on terminal punctuation.
     */
    public function countSentences(string $text): int
    {
        $count = preg_match_all('/[.!?]+/', $text);
        return max(1, $count);
    }

    /**
     * Estimate syllable count using English heuristics.
     */
    public function countSyllables(string $text): int
    {
        $words = preg_split('/\s+/', strtolower(trim($text)), -1, PREG_SPLIT_NO_EMPTY);
        $total = 0;

        foreach ($words as $word) {
            $word = preg_replace('/[^a-z]/', '', $word);
            if ($word === '') {
                continue;
            }

            // Count vowel groups
            $count = preg_match_all('/[aeiouy]+/', $word);
            // Subtract silent e at end
            if (str_ends_with($word, 'e') && $count > 1) {
                $count--;
            }

            $total += max(1, $count);
        }

        return max(1, $total);
    }

    /**
     * Calculate Flesch-Kincaid readability grade level.
     *
     * Lower values indicate text that is easier to read.
     * Grade 5-6 is typical for general audiences.
     */
    public function fleschKincaid(
        int $words,
        int $sentences,
        int $syllables,
    ): float {
        if ($words === 0 || $sentences === 0) {
            return 0.0;
        }

        $grade = 0.39 * ($words / $sentences)
               + 11.8 * ($syllables / $words)
               - 15.59;

        return round(max(0.0, $grade), 1);
    }
}
