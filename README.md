# MohitKhare PHP Utilities

[![Packagist Version](https://img.shields.io/packagist/v/arnaudleroy-studio/mohitkhare)](https://packagist.org/packages/arnaudleroy-studio/mohitkhare)
[![PHP Version](https://img.shields.io/packagist/php-v/arnaudleroy-studio/mohitkhare)](https://packagist.org/packages/arnaudleroy-studio/mohitkhare)
[![License](https://img.shields.io/packagist/l/arnaudleroy-studio/mohitkhare)](LICENSE)

A collection of developer utilities extracted from production projects at mohitkhare.me, focused on token estimation, string analysis, and text processing for PHP applications that interface with large language models. Requires PHP 8.0+ and uses strict types, enums, and readonly properties where applicable.

## Installation

```bash
composer require arnaudleroy-studio/mohitkhare
```

## Quick Start

### Estimate token counts

```php
use MohitKhare\TokenEstimator;

$estimator = new TokenEstimator();

// Quick estimate for a single string
$count = $estimator->estimate('The quick brown fox jumps over the lazy dog.');
echo "{$count} tokens"; // ~10 tokens

// Batch estimation with named arguments
$results = $estimator->estimateBatch(
    texts: ['Hello world', 'PHP is a general-purpose scripting language', $longArticle],
    model: 'gpt-4',
);
```

### Analyze text properties

```php
use MohitKhare\TextAnalyzer;

$analyzer = new TextAnalyzer();

$stats = $analyzer->analyze('Your input text goes here...');

// Array destructuring for readability
['words' => $words, 'sentences' => $sentences, 'readability' => $score] = $stats;

echo "Flesch-Kincaid readability: {$score}";
```

### Truncate to token budgets

```php
// Fit a prompt within model context limits
$truncated = $estimator->truncate(
    text: $longDocument,
    maxTokens: 4096,
    strategy: 'end',
);

// Null-safe access when source might be empty
$preview = $estimator->truncate(
    text: $input?->getContent() ?? '',
    maxTokens: 200,
)?->getText();
```

### Chain operations with arrow functions

```php
$articles = ['First article body...', 'Second article body...', 'Third...'];

// Map to token counts in one pass
$counts = array_map(fn(string $text) => $estimator->estimate($text), $articles);

// Filter articles that fit within budget
$withinBudget = array_filter(
    $articles,
    fn(string $text) => $estimator->estimate($text) <= 2048
);
```

## Available Utilities

The package provides two main classes. `TokenEstimator` approximates token counts using byte-pair encoding heuristics calibrated against the tiktoken reference implementation, supporting model-specific adjustments for GPT-4, Claude, and Llama family tokenizers. `TextAnalyzer` computes word counts, sentence boundaries, reading time, and Flesch-Kincaid readability scores. Both classes are stateless and safe to use in long-running processes like queue workers or daemon scripts.

## Links

- [Mohit Khare](https://mohitkhare.me)
- [GitHub Repository](https://github.com/arnaudleroy-studio/mohitkhare-php)

## License

MIT License. See [LICENSE](LICENSE) for details.
