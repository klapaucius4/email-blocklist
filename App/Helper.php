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

    public static function sanitizeDomainsList(string $input): string
    {
        if (is_array($input)) {
            $input = implode("\n", $input);
        }
        $input = (string) $input;

        $lines = preg_split('/\r\n|\r|\n/', $input);

        $validated = [];

        foreach ($lines as $line) {
            $line = trim($line);
            $line = preg_replace('/[\x00-\x1F\x7F\x{200B}-\x{200F}]+/u', '', $line);
            $line = str_replace([',',';','"',"'",'<','>','(',')'], '', $line);
            $line = preg_replace('/\s+/', '', $line);

            if ($line === '') {
                continue;
            }

            if (strpos($line, '@') !== false) {
                $sanitizedEmail = sanitize_email($line);
                if ($sanitizedEmail && filter_var($sanitizedEmail, FILTER_VALIDATE_EMAIL)) {
                    $validated[] = $sanitizedEmail;
                }

                continue;
            }

            $host = preg_replace('#^https?://#i', '', $line);
            $host = preg_replace('#/.*$#', '', $host);
            $host = ltrim($host, '@');

            $host = strtolower($host);


            if (Helper::isValidDomainName($host)) {
                $validated[] = sanitize_text_field($host);
            }
        }

        $validated = array_values(array_unique($validated));

        return implode("\n", $validated);
    }

    public static function isValidDomainName($domainName)
    {
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainName) && preg_match("/^.{1,253}$/", $domainName) && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainName));
    }

    public static function checkIfEmailIsBlocked(string $email): bool
    {
        $email = strtolower(trim($email));

        if (! is_email($email)) {
            return false;
        }

        $domain = substr(strrchr($email, "@"), 1);

        $localBlocklist = get_option('eb_local_blocklist', '');
        $localAllowlist = get_option('eb_local_allowlist', '');

        $localBlocklistArray = array_map('strtolower', array_filter(array_map('trim', explode("\n", $localBlocklist))));
        $localAllowlistArray = array_map('strtolower', array_filter(array_map('trim', explode("\n", $localAllowlist))));

        if (in_array($email, $localAllowlistArray, true) || in_array($domain, $localAllowlistArray, true)) {
            return false;
        }

        if (in_array($email, $localBlocklistArray, true) || in_array($domain, $localBlocklistArray, true)) {
            return true;
        }

        if (get_option('eb_global_blocklist_enabled')) {
            $globalBlocklist = get_option('eb_global_blocklist', []);
            $globalBlocklist = array_map('strtolower', array_filter(array_map('trim', $globalBlocklist)));

            if (in_array($email, $globalBlocklist, true) || in_array($domain, $globalBlocklist, true)) {
                return true;
            }
        }

        if (get_option('eb_block_plus_emails')) {
            if (substr($email, 0, strpos($email, '@')) !== false) {
                $plusEmail = substr($email, 0, strpos($email, '@')) . '+' . substr($email, strpos($email, '@'));
                if (in_array($plusEmail, $localBlocklistArray, true) || in_array($plusEmail, $globalBlocklist ?? [], true)) {
                    return true;
                }
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

    private static function isLoginRequest()
    {
        global $pagenow;
        return $pagenow === 'wp-login.php';
    }
}
