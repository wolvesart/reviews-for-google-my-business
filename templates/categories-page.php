<?php
/**
 * Google My Business Reviews - Page de gestion des catégories
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Récupérer toutes les catégories
$categories = gmb_get_all_categories();
?>

<div class="wrap gmb-admin-wrap">
    <?php include_once(WOLVES_GMB_PLUGIN_DIR . 'template-parts/header.php'); ?>

    <!-- Section de création de catégorie -->
    <div class="card" style="margin-top: 20px;">
        <h2 style="margin-top: 0;">Créer une nouvelle catégorie</h2>
        <p>Les catégories vous permettent d'organiser vos avis Google My Business et de les filtrer sur votre site web.</p>

        <div style="display: flex; gap: 10px; align-items: flex-start; margin: 20px 0;">
            <div style="flex: 1;">
                <input type="text"
                       id="gmb-new-category-name"
                       placeholder="Nom de la nouvelle catégorie (ex: Formation, Coaching, Design...)"
                       style="width: 100%; padding: 8px 12px; font-size: 14px;">
                <p class="description" style="margin-top: 8px;">
                    Le slug sera généré automatiquement à partir du nom (ex: "Formation Figma" → <code>formation-figma</code>)
                </p>
            </div>
            <button type="button"
                    id="gmb-create-category-btn"
                    class="button button-primary"
                    style="height: 38px; margin-top: 0;">
                Créer la catégorie
            </button>
        </div>
    </div>

    <!-- Liste des catégories existantes -->
    <div class="card" style="margin-top: 20px;">
        <h2 style="margin-top: 0;">Catégories existantes (<?php echo count($categories); ?>)</h2>

        <?php if (!empty($categories)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 40%;">Nom</th>
                        <th style="width: 40%;">Slug</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <strong style="font-size: 14px;"><?php echo esc_html($cat->name); ?></strong>
                            </td>
                            <td>
                                <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px; font-size: 13px;">
                                    <?php echo esc_html($cat->slug); ?>
                                </code>
                            </td>
                            <td>
                                <button type="button"
                                        class="button button-small gmb-delete-category-btn"
                                        data-category-id="<?php echo esc_attr($cat->id); ?>"
                                        style="color: #d63638;">
                                    Supprimer
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="background: #fff3cd; border-left: 4px solid #f0b429; padding: 12px 16px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0;">
                    <strong>⚠️ Attention :</strong> La suppression d'une catégorie la retirera automatiquement de tous les avis qui l'utilisent.
                </p>
            </div>
        <?php else: ?>
            <div style="background: #f6f7f7; padding: 40px 20px; text-align: center; border-radius: 8px;">
                <span class="dashicons dashicons-category" style="font-size: 48px; color: #c3c4c7; margin-bottom: 10px;"></span>
                <p style="color: #666; font-size: 14px; margin: 0;">
                    Aucune catégorie créée pour le moment.<br>
                    Créez votre première catégorie ci-dessus pour commencer à organiser vos avis.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Informations d'utilisation -->
    <div class="card" style="margin-top: 20px;">
        <h2 style="margin-top: 0;">💡 Utilisation des catégories</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
            <div style="background: #f6f7f7; padding: 15px; border-radius: 8px;">
                <h3 style="margin-top: 0; font-size: 14px; color: #2271b1;">
                    <span class="dashicons dashicons-admin-links" style="font-size: 16px; vertical-align: middle;"></span>
                    Assigner aux avis
                </h3>
                <p style="margin: 0; font-size: 13px; line-height: 1.6;">
                    Allez dans <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-manage-reviews')); ?>">Avis Google → Liste des avis</a>
                    et cochez les catégories appropriées pour chaque avis.
                </p>
            </div>

            <div style="background: #f6f7f7; padding: 15px; border-radius: 8px;">
                <h3 style="margin-top: 0; font-size: 14px; color: #2271b1;">
                    <span class="dashicons dashicons-editor-code" style="font-size: 16px; vertical-align: middle;"></span>
                    Utiliser dans le shortcode
                </h3>
                <p style="margin: 0; font-size: 13px; line-height: 1.6;">
                    Filtrez les avis par catégorie avec :<br>
                    <code style="background: #fff; padding: 2px 6px; border-radius: 3px;">[gmb_reviews category="slug"]</code>
                </p>
            </div>

            <div style="background: #f6f7f7; padding: 15px; border-radius: 8px;">
                <h3 style="margin-top: 0; font-size: 14px; color: #2271b1;">
                    <span class="dashicons dashicons-info" style="font-size: 16px; vertical-align: middle;"></span>
                    Catégories multiples
                </h3>
                <p style="margin: 0; font-size: 13px; line-height: 1.6;">
                    Un avis peut avoir plusieurs catégories. Il apparaîtra dans le filtrage de chacune d'elles.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
// Charger les scripts JavaScript
require_once WOLVES_GMB_PLUGIN_DIR . 'templates/manage-reviews-scripts.php';
?>
