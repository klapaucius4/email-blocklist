<?php

namespace App;

class Helper
{
    public static function logError(string $errorContent): void
    {
        error_log(__('Error from the Email Blocklist plugin:', 'email-blocklist') . ' ' . $errorContent);
    }

    public static function getCounOfLinexOfField(string $fieldName): int
    {
        $fieldValue = get_option($fieldName, '');
        $lines = array_filter(explode("\n", $fieldValue));

        return count($lines);
    }

    public static function getGlobalBlocklist(bool $returnAsText = false): array|string
    {
        $globalBlocklist = get_option('eb_global_blocklist', []);

        if (! $returnAsText) {
            return (array) $globalBlocklist;
        }

        $returnString = '';

        foreach ($globalBlocklist as $key => $domain) {
            if ($key > 0) {
                $returnString .= "\n";
            }

            $returnString .= $domain;
        }

        return $returnString;
    }

    public static function getGlobalBlocklistCount(): int
    {
        return count(get_option('eb_global_blocklist', []));
    }
}
