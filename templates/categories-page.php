<?php
/**
 * Google My Business Reviews - Page de gestion des catégories
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Récupérer toutes les catégories (taxonomie WordPress)
$categories = get_terms(array(
        'taxonomy' => 'gmb_category',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
));

// Si erreur, initialiser avec un tableau vide
if (is_wp_error($categories)) {
    $categories = array();
}
?>

<div class="wrap gmb-wrap">
    <?php include_once(WOLVES_GMB_PLUGIN_DIR . 'template-parts/header.php'); ?>

    <div class="gmb-container">
        <div class="section row">
            <div class="card-list">
                <div class="card">
                    <h2><?php _e('Create a new category', 'wolves-avis-google'); ?></h2>
                    <p><?php _e('Categories allow you to organize your Google My Business reviews and filter them on your website.', 'wolves-avis-google'); ?></p>

                    <div class="input-button">
                        <input type="text"
                               id="gmb-new-category-name"
                               placeholder="<?php esc_attr_e('New category name (e.g: Training, Coaching, Design...)', 'wolves-avis-google'); ?>"
                        >
                        <button type="button"
                                id="gmb-create-category-btn"
                                class="button button-primary">
                            <?php _e('Create category', 'wolves-avis-google'); ?>
                        </button>
                    </div>
                </div>

                <div class="card">
                    <h2><?php printf(__('Existing categories (%d)', 'wolves-avis-google'), count($categories)); ?></h2>

                    <?php if (!empty($categories)): ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                            <tr>
                                <th><?php _e('Name', 'wolves-avis-google'); ?></th>
                                <th><?php _e('Slug', 'wolves-avis-google'); ?></th>
                                <th><?php _e('Action', 'wolves-avis-google'); ?></th>
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
                                                class="button button-secondary button-small gmb-delete-category-btn"
                                                data-category-id="<?php echo esc_attr($cat->term_id); ?>"
                                                style="color: #d63638;">
                                            <?php _e('Delete', 'wolves-avis-google'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div>
                            <span class="dashicons dashicons-category"></span>
                            <p>
                                <?php _e('No categories created yet.', 'wolves-avis-google'); ?><br>
                                <?php _e('Create your first category above to start organizing your reviews.', 'wolves-avis-google'); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php require WOLVES_GMB_PLUGIN_DIR . 'template-parts/notice-categories.php'; ?>

        </div>
    </div>

    <?php
    // Charger les scripts JavaScript
    require_once WOLVES_GMB_PLUGIN_DIR . 'templates/manage-reviews-scripts.php';
    ?>
