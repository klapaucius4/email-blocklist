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
    }

    public function pluginActivate(): void
    {
        if (! get_option('eb_enabled')) {
            update_option('eb_enabled', 0, false);
        }

        if (! get_option('eb_local_blocklist')) {
            update_option('eb_local_blocklist', '', false);
        }

        if (! get_option('eb_local_allowlist')) {
            update_option('eb_local_allowlist', '', false);
        }

        $this->updateGlobalBlocklist();
    }

    public static function pluginUninstall(): void
    {
        delete_option('eb_enabled');
        delete_option('eb_local_blocklist');
        delete_option('eb_local_allowlist');
        delete_option('eb_global_blocklist');
        delete_option('eb_global_blocklist_version');
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
        register_setting('email-blocklist-settings-group', 'eb_local_blocklist', [$this, 'validateField']);
        register_setting('email-blocklist-settings-group', 'eb_local_allowlist', [$this, 'validateField']);
    }

    public function validateField($value)
    {
        return $value;
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
}
