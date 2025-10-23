<div class="gmb-header" style="background-image: url(<?php echo WOLVES_GMB_PLUGIN_URL . '/assets/gmb-pattern.png'; ?>);">
    <div class="gmb-logo">
        <?php
        $logo_path = WOLVES_GMB_PLUGIN_DIR . 'template-parts/svg/gmb-logo.svg';
        if (file_exists($logo_path)) {
            include $logo_path;
        }
        ?>
    </div>
    <h1><?php _e('Google reviews', 'wolves-avis-google'); ?></h1>
    <div class="infos">
        <ul>
            <li class="file"><a href="<?php echo admin_url('admin.php?page=gmb-settings'); ?>"><?php _e('Documentation', 'wolves-avis-google'); ?></a></li>
        </ul>
    </div>
</div>