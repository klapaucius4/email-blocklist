<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use EmailBlocklist\Helper;

?>

<div class="wrap">
    <h2><?php esc_html_e('Email Blocklist Settings', 'email-blocklist'); ?></h2>

    <form method="post" action="options.php">
        <?php settings_fields('email-blocklist-settings-group'); ?>

        <?php do_settings_sections('email-blocklist-settings'); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="embl_enabled">
                        <?php esc_html_e('Blocking enabled', 'email-blocklist') ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" id="embl_enabled" name="embl_enabled" value="1" <?php checked('1', esc_attr(get_option('embl_enabled'))); ?> />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="embl_local_blocklist">
                        <?php esc_html_e('Local blocklist', 'email-blocklist') ?> (<?php echo esc_html(Helper::getCounOfLinexOfField('embl_local_blocklist')); ?>)
                    </label>
                    <p class="label-desc"><?php esc_html_e('Enter domain names (one per line) to block them. You can also enter a full email address - in this case, only that specific address will be blocked.', 'email-blocklist'); ?></p>
                </th>
                <td>
                    <textarea rows="8" class="regular-text" id="embl_local_blocklist" name="embl_local_blocklist" placeholder="<?php echo esc_attr(Helper::getDefaultString('domain_list_placeholder')); ?>"><?php echo esc_textarea(get_option('embl_local_blocklist')); ?></textarea>
                    <p class="description"><?php esc_html_e('One domain name or email address per line.', 'email-blocklist') ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="embl_local_allowlist">
                        <?php esc_html_e('Local allowlist', 'email-blocklist') ?> (<?php echo esc_html(Helper::getCounOfLinexOfField('embl_local_allowlist')); ?>)
                    </label>
                    <p class="label-desc"><?php esc_html_e('Enter domain names, one per line, to exclude them from being blocked (if they are on the global blocklist). You can also enter a full email address - then that specific email will be excluded from being blocked.', 'email-blocklist'); ?></p>
                </th>
                <td>
                    <textarea rows="8" class="regular-text" id="embl_local_allowlist" name="embl_local_allowlist" placeholder="<?php echo esc_attr(Helper::getDefaultString('domain_list_placeholder')); ?>"><?php echo esc_textarea(get_option('embl_local_allowlist')); ?></textarea>
                    <p class="description"><?php esc_html_e('One domain name or email address per line.', 'email-blocklist') ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="embl_global_blocklist_enabled">
                        <?php esc_html_e('Global blocklist enabled', 'email-blocklist') ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" id="embl_global_blocklist_enabled" name="embl_global_blocklist_enabled" value="1" <?php checked('1', esc_attr(get_option('embl_global_blocklist_enabled'))); ?> />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="embl_global_blocklist">
                        <?php esc_html_e('Global blocklist', 'email-blocklist') ?>
                    </label>
                    <p class="label-desc"><?php esc_html_e('Number of domains', 'email-blocklist'); ?>: <?php echo esc_html(Helper::getGlobalBlocklistCount()); ?></p>
                    <p class="label-desc"><?php esc_html_e('Blocklist version', 'email-blocklist'); ?>: <?php echo esc_html(get_option('embl_global_blocklist_version', '-')); ?></p>
                    <p class="label-desc"><?php esc_html_e('Updated', 'email-blocklist'); ?>: <?php echo esc_html(date('Y-m-d H:i:s', get_option('embl_global_blocklist_update_timestamp', 0))); ?></p>
                    <p class="mb-0"><a href="<?php echo esc_url(Helper::getUpdateGlobalBlocklistUrl()); ?>" class="button"><?php esc_html_e('Update global blocklist', 'email-blocklist'); ?></a></p>
                    <p class="label-desc"><?php esc_html_e('(the global blocklist is automatically updated daily by WP-Cron)', 'email-blocklist'); ?></p>
                </th>
                <td>
                    <textarea rows="8" class="regular-text" id="embl_global_blocklist" name="embl_global_blocklist" disabled><?php echo esc_textarea(Helper::getGlobalBlocklist(true)); ?></textarea>
                    <p class="description"><?php esc_html_e('Domains fetched from the global database. If you want to disable blocking for any of them, add it above to the \'Local allowlist\' field.', 'email-blocklist') ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="embl_block_plus_emails">
                        <?php esc_html_e('Block "+" emails', 'email-blocklist') ?>
                    </label>
                    <p class="label-desc"><?php esc_html_e('Block all emails containing the "+" character, e.g. "johnsmith+test1@gmail.com".', 'email-blocklist'); ?></p>
                </th>
                <td>
                    <input type="checkbox" id="embl_block_plus_emails" name="embl_block_plus_emails" value="1" <?php checked('1', esc_attr(get_option('embl_block_plus_emails'))); ?> />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="embl_blocked_email_notice_text">
                        <?php esc_html_e('Email blocked notice', 'email-blocklist') ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="embl_blocked_email_notice_text" class="regular-text" name="embl_blocked_email_notice_text" value="<?php echo esc_attr(get_option('embl_blocked_email_notice_text')); ?>" placeholder="<?php echo esc_attr(Helper::getDefaultString('blocked_email_notice_text')); ?>" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <p><?php esc_html_e('Scan Existing Users', 'email-blocklist'); ?></p>
                    <p class="label-desc"><?php esc_html_e('Scan and highlight existing accounts with blocklisted emails at the', 'email-blocklist'); ?> <a href="<?php echo esc_url(admin_url('users.php')); ?>"><?php echo esc_html_e('Users Admin Page', 'email-blocklist'); ?></a>.</p>
                </th>
                <td>
                    <p class="mb-0"><a href="<?php echo esc_url(Helper::getScanExistingUsersUrl()); ?>" class="button"><?php esc_html_e('Scan Existing Users', 'email-blocklist'); ?></a></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>