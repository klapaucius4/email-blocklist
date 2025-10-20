<?php

/*
 * Plugin Name:       Email Blocklist
 * Plugin URI:        https://wordpress.org/plugins/email-blocklist/
 * Description:       Keep your WordPress site clean by blocking unwanted signups and comments with a blocklist of spam and temporary email domains.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Michał Kowalik
 * Author URI:        https://michalkowalik.pl
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       email-blocklist
 * Domain Path:       /languages
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use App\Helper;

$emailBlocklist = new EmailBlocklist();

class EmailBlocklist
{
    const BLOCKLIST_RESOURCE_URL = 'https://raw.githubusercontent.com/klapaucius4/email-blocklist/refs/heads/master/blocklist.json';
    const BLOCKLIST_META_RESOURCE_URL = 'https://raw.githubusercontent.com/klapaucius4/email-blocklist/refs/heads/master/blocklist-meta.json';

    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'pluginActivate']);
        register_uninstall_hook(__FILE__, ['EmailBlocklist', 'pluginUninstall']);
        add_action('plugins_loaded', [$this, 'loadTextdomain']);
        add_action('admin_menu', [$this, 'addSettingsPageToMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_filter('plugin_action_links', [$this, 'addPluginActionLinks'], 10, 5);
        add_action('admin_enqueue_scripts', [$this, 'loadAdminStyle']);

        add_filter('registration_errors', [$this, 'protectSignupEmail'], 10, 3);
        add_action('user_profile_update_errors', [$this, 'protectAccountUpdate'], 10, 3);
        add_filter('preprocess_comment', [$this, 'protectCommentSubmission'], 10, 1);


        add_action('load-settings_page_email-blocklist-settings', [$this, 'callUpdateGlobalBlocklist']);
    }

    public function pluginActivate(): void
    {
        if (! get_option('eb_enabled')) {
            update_option('eb_enabled', 1, false);
        }

        if (! get_option('eb_local_blocklist')) {
            update_option('eb_local_blocklist', '', false);
        }

        if (! get_option('eb_local_allowlist')) {
            update_option('eb_local_allowlist', '', false);
        }

        if (! get_option('eb_global_blocklist_enabled')) {
            update_option('eb_global_blocklist_enabled', 1, false);
        }

        $this->updateGlobalBlocklist();

        if (! get_option('eb_block_plus_emails')) {
            update_option('eb_block_plus_emails', 1, false);
        }

        if (! get_option('eb_protect_signup_submissions')) {
            update_option('eb_protect_signup_submissions', 1, false);
        }

        if (! get_option('eb_protect_comment_submissions')) {
            update_option('eb_protect_comment_submissions', 1, false);
        }

        if (! get_option('eb_blocked_email_notice_text')) {
            update_option('eb_blocked_email_notice_text', Helper::getDefaultString('blocked_email_notice_text'), false);
        }
    }

    public static function pluginUninstall(): void
    {
        delete_option('eb_enabled');
        delete_option('eb_local_blocklist');
        delete_option('eb_local_allowlist');
        delete_option('eb_global_blocklist_enabled');
        delete_option('eb_global_blocklist');
        delete_option('eb_global_blocklist_version');
        delete_option('eb_global_blocklist_update_timestamp');
        delete_option('eb_block_plus_emails');
        delete_option('eb_protect_signup_submissions');
        delete_option('eb_protect_comment_submissions');
        delete_option('eb_blocked_email_notice_text');
    }

    private function updateGlobalBlocklist(): bool
    {
        $blocklistMetaResponse = wp_remote_get(self::BLOCKLIST_META_RESOURCE_URL);

        if (is_wp_error($blocklistMetaResponse)) {
            Helper::logError($blocklistMetaResponse->get_error_message());
            return false;
        }

        $globalBlocklist = get_option('eb_global_blocklist', []);
        $globalBlocklistVersion = get_option('eb_global_blocklist_version', 0);
        $decodedBlockMetalistBody = json_decode(wp_remote_retrieve_body($blocklistMetaResponse));

        if (! empty($globalBlocklist) && $globalBlocklistVersion >= $decodedBlockMetalistBody->blocklist_version) {
            return true;
        }

        $blocklistResponse = wp_remote_get(self::BLOCKLIST_RESOURCE_URL);

        if (is_wp_error($blocklistResponse)) {
            Helper::logError($blocklistResponse->get_error_message());
            return false;
        }

        $decodedBlocklistBody = json_decode(wp_remote_retrieve_body($blocklistResponse));

        if (! is_array($decodedBlocklistBody)) {
            return false;
        }

        update_option('eb_global_blocklist', $decodedBlocklistBody);
        update_option('eb_global_blocklist_version', $decodedBlockMetalistBody->blocklist_version);
        update_option('eb_global_blocklist_update_timestamp', time());

        return true;
    }

    public function loadTextdomain(): void
    {
        load_plugin_textdomain('email-blocklist');
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
        register_setting('email-blocklist-settings-group', 'eb_enabled');
        register_setting('email-blocklist-settings-group', 'eb_local_blocklist', [
            'sanitize_callback' => function($value) {
                return Helper::sanitizeListField($value, 'eb_local_blocklist');
            },
            'type' => 'string',
            'default' => '',
        ]);
        register_setting('email-blocklist-settings-group', 'eb_local_allowlist', [
            'sanitize_callback' => function($value) {
                return Helper::sanitizeListField($value, 'eb_local_allowlist');
            },
            'type' => 'string',
            'default' => '',
        ]);
        register_setting('email-blocklist-settings-group', 'eb_global_blocklist_enabled');
        register_setting('email-blocklist-settings-group', 'eb_block_plus_emails');
        register_setting('email-blocklist-settings-group', 'eb_protect_signup_submissions');
        register_setting('email-blocklist-settings-group', 'eb_protect_comment_submissions');
        register_setting('email-blocklist-settings-group', 'eb_blocked_email_notice_text', [
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
        wp_enqueue_style('eb_admin_css', plugin_dir_url(__FILE__) . '/assets/admin-style.css', false, '1.0.0');
    }

    public function protectSignupEmail(WP_Error $errors, string $sanitizedUserLogin,  string $userEmail): WP_Error
    {
        if (! get_option('eb_protect_signup_submissions')) {
            return $errors;
        }

        if (! is_string($userEmail)) {
            $errors->add('eb_invalid_email', __('Invalid email address.', 'email-blocklist'));
        }

        if (Helper::checkIfEmailIsBlocked($userEmail)) {
            $errors->add('eb_blocked_email', get_option('eb_blocked_email_notice_text', Helper::getDefaultString('blocked_email_notice_text')));
        }

        return $errors;
    }

    public function protectAccountUpdate(WP_Error $errors, bool $update, stdClass $user): WP_Error
    {
        if (! get_option('eb_protect_signup_submissions')) {
            return $errors;
        }

        if (! is_object($user) || ! isset($user->user_email)) {
            $errors->add('eb_invalid_email', __('Invalid email address.', 'email-blocklist'));
        }

        if (Helper::checkIfEmailIsBlocked($user->user_email)) {
            $errors->add('eb_blocked_email', get_option('eb_blocked_email_notice_text', Helper::getDefaultString('blocked_email_notice_text')));
        }

        return $errors;
    }

    public function protectCommentSubmission(array $commentdata): array
    {
        if (! get_option('eb_protect_comment_submissions')) {
            return $commentdata;
        }

        $errors = new WP_Error();
        $email = isset($commentdata['comment_author_email']) ? $commentdata['comment_author_email'] : '';

        if (empty($email) || Helper::checkIfEmailIsBlocked($email)) {
            $errors->add('eb_blocked_email', get_option('eb_blocked_email_notice_text', Helper::getDefaultString('blocked_email_notice_text')));
        }

        if ($errors->has_errors()) {
            add_filter('pre_comment_approved', function () {
                return 'spam';
            });
        }

        return $commentdata;
    }

    public function callUpdateGlobalBlocklist(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        if (empty($_GET['update_global_blocklist']) || 1 !== absint($_GET['update_global_blocklist'])) {
            return;
        }

        if (empty($_GET['_wpnonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'eb_update_global_blocklist')) {
            wp_die(__('Missing or invalid nonce.', 'email-blocklist'));
        }

        if (get_transient('eb_global_blocklist_updated')) {
            add_settings_error('eb_global_blocklist', 'eb_global_blocklist_updated', __('You just updated the global blocklist. Please wait a moment before trying again.', 'email-blocklist'), 'notice');

            return;
        }

        if ($this->updateGlobalBlocklist()) {
            set_transient('eb_global_blocklist_updated', true, 60);
            add_settings_error('eb_global_blocklist', 'eb_global_blocklist_updated', __('The global blocklist has been updated.', 'email-blocklist'), 'updated');
        } else {
            add_settings_error('eb_global_blocklist', 'eb_global_blocklist_updated', __('The global blocklist has not been updated. Please try again later.', 'email-blocklist'), 'error');
        }
    }
}
