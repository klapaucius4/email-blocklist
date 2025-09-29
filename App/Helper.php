<?php

namespace App;

class Helper
{
    public static function logError(string $errorContent): void
    {
        error_log(__('Error from the Email Blocklist plugin:', 'email-blocklist') . ' ' . $errorContent);
    }

    public static function getGlobalBlocklist(): array
    {
        return [];
    }
}
