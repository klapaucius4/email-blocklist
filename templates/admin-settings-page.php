<?php

if (! defined('ABSPATH')) {
    exit;
}

?>

<div class="wrap">
    <h2><?php echo __('Email Blocklist Settings', 'email-blocklist'); ?></h2>

    <form method="post" action="options.php">
        <?php settings_fields('email-blocklist-settings-group'); ?>

        <?php do_settings_sections('email-blocklist-settings'); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="eb_local_allowlist">
                        <?php _e('Local allowlist', 'block-temporary-email') ?> (<?php echo count(array_filter(explode("\n", get_option('eb_local_allowlist')))); ?>)
                    </label>
                </th>
                <td>
                    <textarea rows="3" class="regular-text" id="eb_local_allowlist" name="eb_local_allowlist"><?php echo esc_attr(get_option('eb_local_allowlist')); ?></textarea>
                    <p class="description"><?php _e('One domain name per line.', 'block-temporary-email') ?></p>
                </td>
            </tr>
            <tr style="vertical-align: top">
                <th scope="row">
                    <label for="eb_local_blocklist">
                        <?php _e('Local blocklist', 'block-temporary-email') ?> (<?php echo count(array_filter(explode("\n", get_option('eb_local_blocklist')))); ?>)
                    </label>
                </th>
                <td>
                    <textarea rows="3" class="regular-text" id="eb_local_blocklist" name="eb_local_blocklist"><?php echo esc_attr(get_option('eb_local_blocklist')); ?></textarea>
                    <p class="description"><?php _e('One domain name per line.', 'block-temporary-email') ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>