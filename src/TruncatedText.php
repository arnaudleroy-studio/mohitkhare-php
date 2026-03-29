<?php

declare(strict_types=1);

namespace MohitKhare;

/**
 * Value object representing a truncated text result.
 */
class TruncatedText
{
    public function __construct(
        private readonly string $text,
        private readonly int $tokenCount,
        private readonly bool $wasTruncated,
    ) {}

    public function getText(): string
    {
        return $this->text;
    }

    public function getTokenCount(): int
    {
        return $this->tokenCount;
    }

    public function wasTruncated(): bool
    {
        return $this->wasTruncated;
    }

    public function __toString(): string
    {
        return $this->text;
    }
}
