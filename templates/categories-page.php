<?php
/**
 * Reviews for Google My Business - Categories management page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get all categories (WordPress taxonomy)
$wgmbr_categories = get_terms(array(
        'taxonomy' => 'wgmbr_category',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
));

// If error, initialize with an empty array
if (is_wp_error($wgmbr_categories)) {
    $wgmbr_categories = array();
}
?>

<div class="wrap gmb-wrap">
    <?php include_once(WGMBR_PLUGIN_DIR . 'template-parts/header.php'); ?>

    <div class="gmb-container">
        <div class="section row">
            <div class="card-list">
                <div class="card">
                    <h2><?php esc_html_e('Create a new category', 'reviews-for-google-my-business'); ?></h2>
                    <p><?php esc_html_e('Categories allow you to organize your Google My Business reviews and filter them on your website.', 'reviews-for-google-my-business'); ?></p>

                    <div class="input-button">
                        <input type="text"
                               id="gmb-new-category-name"
                               placeholder="<?php esc_attr_e('New category name (e.g: Training, Coaching, Design...)', 'reviews-for-google-my-business'); ?>"
                        >
                        <button type="button"
                                id="gmb-create-category-btn"
                                class="button button-primary">
                            <?php esc_html_e('Create category', 'reviews-for-google-my-business'); ?>
                        </button>
                    </div>
                </div>

                <div class="card">
                    <h2><?php
                    /* translators: %d: Number of categories */
                    printf(esc_html__('Existing categories (%d)', 'reviews-for-google-my-business'), count($wgmbr_categories)); ?></h2>

                    <?php if (!empty($wgmbr_categories)): ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                            <tr>
                                <th><?php esc_html_e('Name', 'reviews-for-google-my-business'); ?></th>
                                <th><?php esc_html_e('Slug', 'reviews-for-google-my-business'); ?></th>
                                <th><?php esc_html_e('Action', 'reviews-for-google-my-business'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($wgmbr_categories as $cat): ?>
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
                                            <?php esc_html_e('Delete', 'reviews-for-google-my-business'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div>
                            <p>
                                <?php esc_html_e('No categories created yet.', 'reviews-for-google-my-business'); ?><br>
                                <?php esc_html_e('Create your first category above to start organizing your reviews.', 'reviews-for-google-my-business'); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php require WGMBR_PLUGIN_DIR . 'template-parts/notice-categories.php'; ?>

        </div>
    </div>