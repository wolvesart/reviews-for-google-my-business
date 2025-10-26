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
                    <h2><?php _e('Create a new category', 'google-my-business-reviews'); ?></h2>
                    <p><?php _e('Categories allow you to organize your Google My Business reviews and filter them on your website.', 'google-my-business-reviews'); ?></p>

                    <div class="input-button">
                        <input type="text"
                               id="gmb-new-category-name"
                               placeholder="<?php esc_attr_e('New category name (e.g: Training, Coaching, Design...)', 'google-my-business-reviews'); ?>"
                        >
                        <button type="button"
                                id="gmb-create-category-btn"
                                class="button button-primary">
                            <?php _e('Create category', 'google-my-business-reviews'); ?>
                        </button>
                    </div>
                </div>

                <div class="card">
                    <h2><?php printf(__('Existing categories (%d)', 'google-my-business-reviews'), count($categories)); ?></h2>

                    <?php if (!empty($categories)): ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                            <tr>
                                <th><?php _e('Name', 'google-my-business-reviews'); ?></th>
                                <th><?php _e('Slug', 'google-my-business-reviews'); ?></th>
                                <th><?php _e('Action', 'google-my-business-reviews'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($cat->name); ?></strong>
                                    </td>
                                    <td>
                                        <code>
                                            <?php echo esc_html($cat->slug); ?>
                                        </code>
                                    </td>
                                    <td>
                                        <button type="button"
                                                class="button button-secondary button-small gmb-delete-category-btn"
                                                data-category-id="<?php echo esc_attr($cat->term_id); ?>"
                                                >
                                            <?php _e('Delete', 'google-my-business-reviews'); ?>
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
                                <?php _e('No categories created yet.', 'google-my-business-reviews'); ?><br>
                                <?php _e('Create your first category above to start organizing your reviews.', 'google-my-business-reviews'); ?>
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