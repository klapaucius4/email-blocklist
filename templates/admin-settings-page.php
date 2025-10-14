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
                    <p class="label-desc"><?php _e('Enter domain names (one per line) to block them. You can also enter a full email address - in this case, only that specific address will be blocked.', 'email-blocklist'); ?></p>
                </th>
                <td>
                    <textarea rows="8" class="regular-text" id="eb_local_blocklist" name="eb_local_blocklist" placeholder="<?php echo Helper::getDefaultString('domain_list_placeholder'); ?>"><?php echo esc_textarea(get_option('eb_local_blocklist')); ?></textarea>
                    <p class="description"><?php _e('One domain name or email address per line.', 'email-blocklist') ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="eb_local_allowlist">
                        <?php _e('Local allowlist', 'email-blocklist') ?> (<?php echo Helper::getCounOfLinexOfField('eb_local_allowlist'); ?>)
                    </label>
                    <p class="label-desc"><?php _e('Enter domain names, one per line, to exclude them from being blocked. You can also enter a full email address - then this specyfic email will be excluded from being blocked.', 'email-blocklist'); ?></p>
                </th>
                <td>
                    <textarea rows="8" class="regular-text" id="eb_local_allowlist" name="eb_local_allowlist" placeholder="<?php echo Helper::getDefaultString('domain_list_placeholder'); ?>"><?php echo esc_textarea(get_option('eb_local_allowlist')); ?></textarea>
                    <p class="description"><?php _e('One domain name or email address per line.', 'email-blocklist') ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eb_global_blocklist_enabled">
                        <?php _e('Global blocklist enabled', 'email-blocklist') ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" id="eb_global_blocklist_enabled" name="eb_global_blocklist_enabled" value="1" <?php checked('1', get_option('eb_global_blocklist_enabled')); ?> />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="eb_global_blocklist">
                        <?php _e('Global blocklist', 'email-blocklist') ?>
                    </label>
                    <p class="label-desc"><?php _e('Count', 'email-blocklist'); ?>: <?php echo Helper::getGlobalBlocklistCount(); ?></p>
                    <p class="label-desc"><?php _e('Update date', 'email-blocklist'); ?>: </p>
                </th>
                <td>
                    <textarea rows="8" class="regular-text" id="eb_global_blocklist" name="eb_global_blocklist" disabled><?php echo Helper::getGlobalBlocklist(true); ?></textarea>
                    <p class="description"><?php _e('Domains fetched from the global database. If you want to disable blocking for any of them, add it above to the \'Local allowlist\' field.', 'email-blocklist') ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eb_block_plus_emails">
                        <?php _e('Block "+" emails', 'email-blocklist') ?>
                    </label>
                    <p class="label-desc"><?php _e('Block all emails containing the "+" character, e.g. "johnsmith+test1@gmail.com".', 'email-blocklist'); ?></p>
                </th>
                <td>
                    <input type="checkbox" id="eb_block_plus_emails" name="eb_block_plus_emails" value="1" <?php checked('1', get_option('eb_block_plus_emails')); ?> />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eb_protect_signup_submissions">
                        <?php _e('Protect signup submissions', 'email-blocklist') ?>
                    </label>
                    <p class="label-desc"><?php _e('It also protects any email update submissions.', 'email-blocklist'); ?></p>
                </th>
                <td>
                    <input type="checkbox" id="eb_protect_signup_submissions" name="eb_protect_signup_submissions" value="1" <?php checked('1', get_option('eb_protect_signup_submissions')); ?> />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eb_protect_comment_submissions">
                        <?php _e('Protect comment submissions', 'email-blocklist') ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" id="eb_protect_comment_submissions" name="eb_protect_comment_submissions" value="1" <?php checked('1', get_option('eb_protect_comment_submissions')); ?> />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eb_blocked_email_notice_text">
                        <?php _e('Email blocked notice', 'email-blocklist') ?>
                    </label>
                </th>
                <td>
                    <input type="text" id="eb_blocked_email_notice_text" class="regular-text" name="eb_blocked_email_notice_text" value="<?php echo get_option('eb_blocked_email_notice_text'); ?>" placeholder="<?php echo Helper::getDefaultString('blocked_email_notice_text'); ?>" />
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>