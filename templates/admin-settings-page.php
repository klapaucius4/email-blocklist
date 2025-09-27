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

        <?php submit_button(); ?>
    </form>
</div>