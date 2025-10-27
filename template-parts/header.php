<div class="gmb-header" style="background-image: url(<?php echo  esc_url(WOLVES_GMB_PLUGIN_URL) . 'assets/images/gmb-pattern.png'; ?>);">
    <div class="gmb-logo">
        <?php
        $logo_path = WOLVES_GMB_PLUGIN_DIR . 'template-parts/svg/gmb-logo.svg';
        if (file_exists($logo_path)) {
            include $logo_path;
        }
        ?>
    </div>
    <h1><?php esc_html_e('Reviews for Google My Business', 'reviews-for-google-my-business'); ?></h1>
    <div class="infos">
        <ul>
            <li><span class="dashicons dashicons-admin-page"></span><a href="<?php echo esc_url(admin_url('admin.php?page=gmb-settings#help')); ?>"><?php esc_html_e('Documentation', 'reviews-for-google-my-business'); ?></a></li>
        </ul>
    </div>
</div>