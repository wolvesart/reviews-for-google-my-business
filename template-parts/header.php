<div class="gmb-header">
    <div class="gmb-logo">
        <?php
        $logo_path = WOLVES_GMB_PLUGIN_DIR . 'template-parts/svg/gmb-logo.svg';
        if (file_exists($logo_path)) {
            include $logo_path;
        }
        ?>
    </div>
    <h1>Avis Google</h1>
    <div class="infos">
        <ul>
            <li class="file"><a href="/wp-admin/admin.php?page=gmb-settings">Documentation</a></li>
        </ul>
    </div>
</div>