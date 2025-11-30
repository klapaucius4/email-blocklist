<?php

namespace EmailBlocklist;

class Helper
{
    const INVALID_CHARS_OF_LIST_FIELD = [',',';','"',"'",'<','>','(',')'];

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
        $globalBlocklist = get_option('embl_global_blocklist', []);

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
        return count(get_option('embl_global_blocklist', []));
    }

    public static function sanitizeListField(string $inputValue, string $settingName): string
    {
        if (!self::validateListField($inputValue)) {
            $errorMessage = sprintf(
                __('The field "%s" contains invalid characters. Please correct it.', 'email-blocklist'),
                $settingName
            );

            add_settings_error($settingName, 'invalid_field', $errorMessage, 'error');

            return (string) get_option($settingName);
        }

        if (is_array($inputValue)) {
            $inputValue = implode("\n", $inputValue);
        }

        $lines = preg_split('/\r\n|\r|\n/', (string) $inputValue);
        $validated = [];

        foreach ($lines as $line) {
            $normalized = self::normalizeListLine($line);

            if ($normalized === null) {
                continue;
            }

            $sanitized = self::sanitizeEmailOrDomain($normalized);

            if ($sanitized !== null) {
                $validated[] = $sanitized;
            }
        }

        $validated = array_values(array_unique($validated));

        return implode("\n", $validated);
    }

    private static function normalizeListLine(string $line): ?string
    {
        $line = trim($line);

        $line = preg_replace('/[\x00-\x1F\x7F\x{200B}-\x{200F}]+/u', '', $line);

        $line = str_replace(self::INVALID_CHARS_OF_LIST_FIELD, '', $line);

        $line = preg_replace('/\s+/', '', $line);

        return $line !== '' ? $line : null;
    }

    private static function sanitizeEmailOrDomain(string $line): ?string
    {
        if (strpos($line, '@') !== false) {
            $email = sanitize_email($line);
            return ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) ? $email : null;
        }

        $host = preg_replace('#^https?://#i', '', $line);
        $host = preg_replace('#/.*$#', '', $host);
        $host = ltrim($host, '@');
        $host = strtolower($host);

        if (Helper::isValidDomainName($host)) {
            return sanitize_text_field($host);
        }

        return null;
    }

    private static function validateListField(string $inputValue): bool
    {
        return strpbrk($inputValue, implode('', self::INVALID_CHARS_OF_LIST_FIELD)) === false;
    }

    public static function isValidDomainName($domainName)
    {
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainName) && preg_match("/^.{1,253}$/", $domainName) && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainName));
    }

    public static function checkIfEmailIsBlocked(string $email): bool
    {
        $email = strtolower(trim($email));

        [$local, $domain] = explode('@', $email, 2);

        if (get_option('embl_block_plus_emails') && strpos($local, '+') !== false) {
            return true;
        }

        $localBlocklist = get_option('embl_local_blocklist', '');
        $localAllowlist = get_option('embl_local_allowlist', '');

        $localBlocklistArray = array_map('strtolower', array_filter(array_map('trim', explode("\n", $localBlocklist))));
        $localAllowlistArray = array_map('strtolower', array_filter(array_map('trim', explode("\n", $localAllowlist))));

        if (in_array($domain, $localAllowlistArray, true) || in_array($email, $localAllowlistArray, true)) {
            return false;
        }

        if (in_array($domain, $localBlocklistArray, true) || in_array($email, $localBlocklistArray, true)) {
            return true;
        }

        if (get_option('embl_global_blocklist_enabled')) {
            $globalBlocklist = get_option('embl_global_blocklist', []);
            $globalBlocklist = array_map('strtolower', array_filter(array_map('trim', $globalBlocklist)));

            if (in_array($domain, $globalBlocklist, true) || in_array($email, $globalBlocklist, true)) {
                return true;
            }
        }

        return false;
    }

    public static function getDefaultString(string $name): string
    {
        if ($name === 'blocked_email_notice_text') {
            return __('The email address you entered is not allowed. Please use a another one.', 'email-blocklist');
        }

        if ($name === 'domain_list_placeholder') {
            $string = __('exampledomain.com', 'email-blocklist') . "\n";
            $string .= __('another-domain.pl', 'email-blocklist') . "\n";
            $string .= __('example@mail.com', 'email-blocklist') . "\n";
            return $string;
        }

        return '';
    }

    public static function getUpdateGlobalBlocklistUrl(): string
    {
        $url = add_query_arg(['page' => 'email-blocklist-settings', 'update_global_blocklist' => 1], admin_url('options-general.php'));

        return wp_nonce_url($url, 'embl_update_global_blocklist');
    }

    public static function getScanExistingUsersUrl(): string
    {
        $url = add_query_arg(['page' => 'email-blocklist-settings', 'scan_existing_users' => 1], admin_url('options-general.php'));

        return wp_nonce_url($url, 'embl_scan_existing_users');
    }

    public static function clearMetaDataOfAllUsers(): void
    {
        $allUsers = get_users([
            'fields' => 'ID',
        ]);
        
        foreach ($allUsers as $userId) {
            delete_user_meta($userId, 'embl_potential_spam_user');
        }
    }
}
