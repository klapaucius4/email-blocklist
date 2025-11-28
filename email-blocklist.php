<?php

/*
 * Plugin Name:       Email Blocklist
 * Plugin URI:        https://wordpress.org/plugins/email-blocklist/
 * Description:       Keep your WordPress site clean by blocking unwanted signups and comments with a blocklist of spam and temporary email domains.
 * Version:           1.1.3
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Michał Kowalik
 * Author URI:        https://michalkowalik.pl
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       email-blocklist
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use EmailBlocklist\Helper;

$emailBlocklist = new EmailBlocklist();

class EmailBlocklist
{
    const EMBL_BLOCKLIST_RESOURCE_URL = 'https://raw.githubusercontent.com/klapaucius4/email-blocklist/refs/heads/master/blocklist.json';
    const EMBL_BLOCKLIST_META_RESOURCE_URL = 'https://raw.githubusercontent.com/klapaucius4/email-blocklist/refs/heads/master/blocklist-meta.json';

    private bool $emailIsBlocked = false;

    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'pluginActivate']);
        register_uninstall_hook(__FILE__, ['EmailBlocklist', 'pluginUninstall']);

        add_action('admin_menu', [$this, 'addSettingsPageToMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_filter('plugin_action_links', [$this, 'addPluginActionLinks'], 10, 5);
        add_action('admin_enqueue_scripts', [$this, 'loadAdminStyle']);

        add_filter('is_email', array( $this, 'isEmailNotBlocked' ), 10, 2);

        add_filter('login_errors', [$this, 'addErrorNotices'], 10, 1);
        add_filter('registration_errors', [$this, 'addErrorNotices'], 10, 1);
        add_filter('user_profile_update_errors', [$this, 'addErrorNotices'], 10, 1);

        add_action('load-settings_page_email-blocklist-settings', [$this, 'callUpdateGlobalBlocklist']);
        add_action('admin_notices', [$this, 'displayAdminNotices']);

        add_action('wp', [$this, 'updateGlobalBlocklistCronInit']);
        add_action('embl_update_global_blocklist_cron_hook', [$this, 'updateGlobalBlocklistCronTask']);

        add_action('load-settings_page_email-blocklist-settings', [$this, 'callScanExistingUsers']);
    }

    public function pluginActivate(): void
    {
        if (! get_option('embl_enabled')) {
            update_option('embl_enabled', 1, false);
        }

        if (! get_option('embl_local_blocklist')) {
            update_option('embl_local_blocklist', '', false);
        }

        if (! get_option('embl_local_allowlist')) {
            update_option('embl_local_allowlist', '', false);
        }

        if (! get_option('embl_global_blocklist_enabled')) {
            update_option('embl_global_blocklist_enabled', 1, false);
        }

        $this->updateGlobalBlocklist();

        if (! get_option('embl_block_plus_emails')) {
            update_option('embl_block_plus_emails', 1, false);
        }

        if (! get_option('embl_blocked_email_notice_text')) {
            update_option('embl_blocked_email_notice_text', Helper::getDefaultString('blocked_email_notice_text'), false);
        }
    }

    public static function pluginUninstall(): void
    {
        delete_option('embl_enabled');
        delete_option('embl_local_blocklist');
        delete_option('embl_local_allowlist');
        delete_option('embl_global_blocklist_enabled');
        delete_option('embl_global_blocklist');
        delete_option('embl_global_blocklist_version');
        delete_option('embl_global_blocklist_update_timestamp');
        delete_option('embl_block_plus_emails');
        delete_option('embl_blocked_email_notice_text');
    }

    private function updateGlobalBlocklist(): bool
    {
        $blocklistMetaResponse = wp_remote_get(self::EMBL_BLOCKLIST_META_RESOURCE_URL);

        if (is_wp_error($blocklistMetaResponse)) {
            Helper::logError($blocklistMetaResponse->get_error_message());

            return false;
        }

        $globalBlocklist = get_option('embl_global_blocklist', []);
        $globalBlocklistVersion = get_option('embl_global_blocklist_version', 0);
        $decodedBlocklistMetaBody = json_decode(wp_remote_retrieve_body($blocklistMetaResponse));

        if (! isset($decodedBlocklistMetaBody->blocklist_version)) {
            Helper::logError(__('The global blocklist version cannot be read.', 'email-blocklist'));
            
            return false;
        }

        if (! empty($globalBlocklist) && $globalBlocklistVersion >= $decodedBlocklistMetaBody->blocklist_version) {
            update_option('embl_global_blocklist_update_timestamp', current_time('timestamp'));

            return true;
        }

        $blocklistResponse = wp_remote_get(self::EMBL_BLOCKLIST_RESOURCE_URL);

        if (is_wp_error($blocklistResponse)) {
            Helper::logError($blocklistResponse->get_error_message());

            return false;
        }

        $decodedBlocklistBody = json_decode(wp_remote_retrieve_body($blocklistResponse));

        if (! is_array($decodedBlocklistBody) || empty($decodedBlocklistBody)) {
            Helper::logError(__('The global blocklist content cannot be read.', 'email-blocklist'));

            return false;
        }

        update_option('embl_global_blocklist', $decodedBlocklistBody);
        update_option('embl_global_blocklist_version', $decodedBlocklistMetaBody->blocklist_version);
        update_option('embl_global_blocklist_update_timestamp', current_time('timestamp'));

        return true;
    }

    public function addSettingsPageToMenu(): void
    {
        add_options_page(
            __('Email Blocklist Settings', 'email-blocklist'),
            __('Email Blocklist', 'email-blocklist'),
            'manage_options',
            'email-blocklist-settings',
            [$this, 'renderSettingsPage']
        );
    }

    public function renderSettingsPage(): void
    {
        include plugin_dir_path(__FILE__) . '/templates/admin-settings-page.php';
    }

    public function registerSettings()
    {
        register_setting('email-blocklist-settings-group', 'embl_enabled', [
            'sanitize_callback' => 'rest_sanitize_boolean',
            'type' => 'boolean',
            'default' => 1,
        ]);
        register_setting('email-blocklist-settings-group', 'embl_local_blocklist', [
            'sanitize_callback' => function ($value) {
                return Helper::sanitizeListField($value, 'embl_local_blocklist');
            },
            'type' => 'string',
            'default' => '',
        ]);
        register_setting('email-blocklist-settings-group', 'embl_local_allowlist', [
            'sanitize_callback' => function ($value) {
                return Helper::sanitizeListField($value, 'embl_local_allowlist');
            },
            'type' => 'string',
            'default' => '',
        ]);
        register_setting('email-blocklist-settings-group', 'embl_global_blocklist_enabled', [
            'sanitize_callback' => 'rest_sanitize_boolean',
            'type' => 'boolean',
            'default' => 1,
        ]);
        register_setting('email-blocklist-settings-group', 'embl_block_plus_emails', [
            'sanitize_callback' => 'rest_sanitize_boolean',
            'type' => 'boolean',
            'default' => 1,
        ]);
        register_setting('email-blocklist-settings-group', 'embl_blocked_email_notice_text', [
            'sanitize_callback' => 'sanitize_text_field',
            'type' => 'string',
            'default' => Helper::getDefaultString('blocked_email_notice_text')
        ]);
    }

    public function addPluginActionLinks(array $actions, string $pluginFile): array
    {
        static $plugin;

        if (! isset($plugin)) {
            $plugin = plugin_basename(__FILE__);
        }

        if ($plugin === $pluginFile) {
            $settings = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=email-blocklist-settings')) . '">' . __('Settings', 'email-blocklist') . '</a>';

            $actions = array_merge(['settings' => $settings], $actions);
        }

        return $actions;
    }

    public function loadAdminStyle()
    {
        wp_enqueue_style('embl_admin_css', plugin_dir_url(__FILE__) . '/assets/admin-style.css', false, '1.1.3');
    }

    /**
     * @param string|false $isEmail
     * @return string|false
     */
    public function isEmailNotBlocked($isEmail, string $email)
    {
        if (! $isEmail) {
            return false;
        }

        if (! get_option('embl_enabled')) {
            return $isEmail;
        }

        if (Helper::checkIfEmailIsBlocked($email)) {
            $this->emailIsBlocked = true;

            return false;
        }

        return $isEmail;
    }

    /**
     * @param WP_Error|string $errors
     * @return WP_Error|string
     */
    public function addErrorNotices($errors)
    {
        if (! get_option('embl_enabled')) {
            return $errors;
        }

        if ($this->emailIsBlocked) {

            $errorMessage = get_option('embl_blocked_email_notice_text', Helper::getDefaultString('blocked_email_notice_text'));

            if ($errors instanceof WP_Error) {
                $errors->add(
                    'embl_blocked_email',
                    $errorMessage
                );
            } elseif (is_string($errors)) {
                $errors .= '<br>' . $errorMessage;
            }

            $this->emailIsBlocked = false;
        }

        return $errors;
    }

    public function callUpdateGlobalBlocklist(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        if (empty($_GET['update_global_blocklist']) || 1 !== absint($_GET['update_global_blocklist'])) {
            return;
        }

        if (empty($_GET['_wpnonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'embl_update_global_blocklist')) {
            wp_die(__('Missing or invalid nonce.', 'email-blocklist'));
        }

        if (get_transient('embl_global_blocklist_updated')) {
            set_transient('embl_admin_notice', [
                'setting' => 'embl_global_blocklist',
                'code' => 'embl_global_blocklist_updated',
                'message' => __('You just updated the global blocklist. Please wait a moment before trying again.', 'email-blocklist'),
                'type' => 'notice',
            ], 30);

            wp_safe_redirect(remove_query_arg(['update_global_blocklist', '_wpnonce']));

            exit;
        }

        if ($this->updateGlobalBlocklist()) {
            set_transient('embl_global_blocklist_updated', true, 60);
            set_transient('embl_admin_notice', [
                'setting' => 'embl_global_blocklist',
                'code' => 'embl_global_blocklist_updated',
                'message' => __('The global blocklist has been updated.', 'email-blocklist'),
                'type' => 'updated',
            ], 30);
        } else {
            set_transient('embl_admin_notice', [
                'setting' => 'embl_global_blocklist',
                'code' => 'embl_global_blocklist_updated',
                'message' => __('The global blocklist has not been updated. Please try again later.', 'email-blocklist'),
                'type' => 'error',
            ], 30);
        }

        wp_safe_redirect(remove_query_arg(['update_global_blocklist', '_wpnonce']));

        exit;
    }

    public function displayAdminNotices(): void
    {
        $notice = get_transient('embl_admin_notice');

        if (! $notice) {
            return;
        }

        add_settings_error($notice['setting'], $notice['code'], $notice['message'], $notice['type']);
        delete_transient('embl_admin_notice');
    }

    public function updateGlobalBlocklistCronInit(): void
    {
        if (! get_option('embl_global_blocklist_enabled')) {
            return;
        }

        if (wp_next_scheduled('embl_update_global_blocklist_cron_hook')) {
            return;
        }

        $midnight = strtotime('tomorrow midnight');

        wp_schedule_event($midnight, 'daily', 'embl_update_global_blocklist_cron_hook');
    }

    public function updateGlobalBlocklistCronTask(): void
    {
        $this->updateGlobalBlocklist();
    }

    public function callScanExistingUsers(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        if (empty($_GET['scan_existing_users']) || 1 !== absint($_GET['scan_existing_users'])) {
            return;
        }

        if (empty($_GET['_wpnonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'embl_scan_existing_users')) {
            wp_die(__('Missing or invalid nonce.', 'email-blocklist'));
        }

        // to do

        wp_safe_redirect(esc_url(admin_url('users.php')));

        exit;
    }
}
