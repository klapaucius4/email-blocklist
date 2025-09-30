<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Helper;

?>

<div class="wrap">
    <h2><?php echo __('Email Blocklist Settings', 'email-blocklist'); ?></h2>

    <form method="post" action="options.php">
        <?php settings_fields('email-blocklist-settings-group'); ?>

        <?php do_settings_sections('email-blocklist-settings'); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="eb_enabled">
                        <?php _e('Blocking enabled', 'email-blocklist') ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" id="eb_enabled" name="eb_enabled" value="1" <?php checked('1', get_option('eb_enabled')); ?> />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eb_local_blocklist">
                        <?php _e('Local blocklist', 'email-blocklist') ?> (<?php echo Helper::getCounOfLinexOfField('eb_local_blocklist'); ?>)
                    </label>
                </th>
                <td>
                    <textarea rows="8" class="regular-text" id="eb_local_blocklist" name="eb_local_blocklist"><?php echo esc_textarea(get_option('eb_local_blocklist')); ?></textarea>
                    <p class="description"><?php _e('One domain name per line.', 'email-blocklist') ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="eb_local_allowlist">
                        <?php _e('Local allowlist', 'email-blocklist') ?> (<?php echo Helper::getCounOfLinexOfField('eb_local_allowlist'); ?>)
                    </label>
                </th>
                <td>
                    <textarea rows="8" class="regular-text" id="eb_local_allowlist" name="eb_local_allowlist"><?php echo esc_textarea(get_option('eb_local_allowlist')); ?></textarea>
                    <p class="description"><?php _e('One domain name per line.', 'email-blocklist') ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="eb_global_blocklist">
                        <?php _e('Global blocklist', 'email-blocklist') ?>
                    </label>
                    <p style="margin: 0; font-weight: 300;"><?php _e('Count', 'email-blocklist'); ?>: <?php echo Helper::getGlobalBlocklistCount(); ?></p>
                    <p style="margin: 0; font-weight: 300;"><?php _e('Update date', 'email-blocklist'); ?>: </p>
                </th>
                <td>
                    <textarea rows="8" class="regular-text" id="eb_global_blocklist" name="eb_global_blocklist" disabled><?php echo Helper::getGlobalBlocklist(true); ?></textarea>
                    <p class="description"><?php _e('Domains fetched from the global database. If you want to disable blocking for any of them, add it above to the \'Local allowlist\' field.', 'email-blocklist') ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>