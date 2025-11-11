<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="gmb-header" style="background-image: url(<?php echo  esc_url(WGMBR_PLUGIN_URL) . 'assets/images/gmb-pattern.png'; ?>);">
    <div class="gmb-logo">
        <?php
        $wgmbr_logo_path = WGMBR_PLUGIN_DIR . 'template-parts/svg/gmb-logo.svg';
        if (file_exists($wgmbr_logo_path)) {
            include $wgmbr_logo_path;
        }
        ?>
    </div>
    <h1><?php esc_html_e('Reviews for Google My Business', 'reviews-for-google-my-business'); ?></h1>
    <div class="infos">
        <ul>
            <li><span class="dashicons dashicons-admin-page"></span><a href="<?php echo esc_url(admin_url('admin.php?page=wgmbr-settings#help')); ?>"><?php esc_html_e('Documentation', 'reviews-for-google-my-business'); ?></a></li>
        </ul>
    </div>
</div>