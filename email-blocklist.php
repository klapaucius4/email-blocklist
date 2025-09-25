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

$emailBlocklist = new EmailBlocklist();

class EmailBlocklist
{
    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'pluginActivate']);
        register_uninstall_hook(__FILE__, ['EmailBlocklist', 'pluginUninstall']);
        add_action('plugins_loaded', [$this, 'loadTextdomain']);
    }

    public function pluginActivate(): void
    {
        if (! get_option('eb_enabled')) {
            update_option('eb_enabled', 0, false);
        }

        if (! get_option('eb_global_blocklist')) {
            update_option('eb_global_blocklist', '', false);
        }

        if (! get_option('eb_local_blocklist')) {
            update_option('eb_local_blocklist', '', false);
        }

        if (! get_option('eb_local_allowlist')) {
            update_option('eb_local_allowlist', '', false);
        }
    }

    public static function pluginUninstall(): void
    {
        delete_option('eb_enabled');
        delete_option('eb_global_blocklist');
        delete_option('eb_local_blocklist');
        delete_option('eb_local_allowlist');
    }

    public function loadTextdomain(): void
    {
        load_plugin_textdomain('email-blocklist');
    }
}
