<?php
namespace MohitKhare;

/**
 * Developer utilities and token counting tools.
 * Homepage: https://mohitkhare.me
 */
class Client
{
    const VERSION = '0.1.0';
    const BASE_URL = 'https://mohitkhare.me';

    public function getVersion(): string { return self::VERSION; }
    public function getBaseUrl(): string { return self::BASE_URL; }
}
