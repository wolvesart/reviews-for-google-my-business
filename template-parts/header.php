<div class="gmb-header" style="background-image: url(<?php echo WOLVES_GMB_PLUGIN_URL . 'assets/images/gmb-pattern.png'; ?>);">
    <div class="gmb-logo">
        <?php
        $logo_path = WOLVES_GMB_PLUGIN_DIR . 'template-parts/svg/gmb-logo.svg';
        if (file_exists($logo_path)) {
            include $logo_path;
        }
        ?>
    </div>
    <h1><?php _e('Google reviews', 'google-my-business-reviews'); ?></h1>
    <div class="infos">
        <ul>
            <li><span class="dashicons dashicons-admin-page"></span><a href="<?php echo admin_url('admin.php?page=gmb-settings#help'); ?>"><?php _e('Documentation', 'google-my-business-reviews'); ?></a></li>
        </ul>
    </div>
</div>