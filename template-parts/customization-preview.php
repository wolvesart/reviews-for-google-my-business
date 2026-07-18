<?php
/**
 * Customization tab — live preview panel
 *
 * Server-rendered preview of the review cards using the real frontend markup
 * and stylesheet. Live updates are done client-side by writing the CSS custom
 * properties on .gmb-preview-stage (see admin.js), no AJAX involved.
 * The slider preview is static on purpose: Swiper is never loaded in admin.
 *
 * Expected variable: $wgmbr_current_layout ('slider' or 'masonry')
 */

if (!defined('ABSPATH')) {
    exit;
}

$wgmbr_current_layout = isset($wgmbr_current_layout) ? $wgmbr_current_layout : wgmbr_get_layout();

// Current customization values, applied as scoped CSS custom properties
$wgmbr_default_colors = WGMBR_DEFAULT_COLORS;
$wgmbr_preview_vars = array(
    '--gmb-color-card-bg' => sanitize_hex_color(get_option('wgmbr_color_card_bg', $wgmbr_default_colors['card_bg'])),
    '--gmb-color-star' => sanitize_hex_color(get_option('wgmbr_color_star', $wgmbr_default_colors['star'])),
    '--gmb-color-text-primary' => sanitize_hex_color(get_option('wgmbr_color_text_primary', $wgmbr_default_colors['text_primary'])),
    '--gmb-color-text-resume' => sanitize_hex_color(get_option('wgmbr_color_text_resume', $wgmbr_default_colors['text_resume'])),
    '--gmb-color-accent' => sanitize_hex_color(get_option('wgmbr_color_accent', $wgmbr_default_colors['accent'])),
    '--gmb-color-show-more' => sanitize_hex_color(get_option('wgmbr_color_show_more', $wgmbr_default_colors['show_more'])),
    '--gmb-radius-card' => absint(get_option('wgmbr_radius_card', WGMBR_DEFAULT_CARD_RADIUS)) . 'px',
);

$wgmbr_preview_style = '';
foreach ($wgmbr_preview_vars as $wgmbr_var_name => $wgmbr_var_value) {
    if ($wgmbr_var_value) {
        $wgmbr_preview_style .= $wgmbr_var_name . ':' . $wgmbr_var_value . ';';
    }
}

// Real reviews when available, sample data otherwise
$wgmbr_preview_reviews = wgmbr_get_all_reviews(array('posts_per_page' => 6));
$wgmbr_preview_average = wgmbr_get_average_rating();
$wgmbr_preview_total = wgmbr_get_total_reviews_count();

if (empty($wgmbr_preview_reviews)) {
    $wgmbr_sample_data = array(
        array('name' => 'Marie D.', 'rating' => 5.0, 'comment' => __('Great experience from start to finish. The team listened to our needs and delivered beyond expectations.', 'reviews-for-google-my-business'), 'job' => __('Marketing Manager', 'reviews-for-google-my-business')),
        array('name' => 'Thomas L.', 'rating' => 4.5, 'comment' => __('Professional and responsive. I recommend without hesitation.', 'reviews-for-google-my-business'), 'job' => ''),
        array('name' => 'Sophie B.', 'rating' => 5.0, 'comment' => __('An outstanding service. Every detail was handled with care, communication was smooth, and the final result truly exceeded what we had imagined. We will definitely come back.', 'reviews-for-google-my-business'), 'job' => __('Founder', 'reviews-for-google-my-business')),
        array('name' => 'Hugo M.', 'rating' => 4.0, 'comment' => __('Solid work and good follow-up.', 'reviews-for-google-my-business'), 'job' => ''),
    );

    $wgmbr_preview_reviews = array();
    foreach ($wgmbr_sample_data as $wgmbr_sample) {
        $wgmbr_item = new stdClass();
        $wgmbr_item->name = $wgmbr_sample['name'];
        $wgmbr_item->photo = '';
        $wgmbr_item->rating = $wgmbr_sample['rating'];
        $wgmbr_item->comment = $wgmbr_sample['comment'];
        $wgmbr_item->job = $wgmbr_sample['job'];
        $wgmbr_item->post_id = 0;
        $wgmbr_preview_reviews[] = $wgmbr_item;
    }

    $wgmbr_preview_average = 4.8;
    $wgmbr_preview_total = count($wgmbr_preview_reviews);
}
?>

<div class="gmb-preview-panel">
    <div class="gmb-preview-panel-head">
        <h2><?php esc_html_e('Preview', 'reviews-for-google-my-business'); ?></h2>
        <p class="description"><?php esc_html_e('Non-interactive preview. The final rendering depends on your theme.', 'reviews-for-google-my-business'); ?></p>
    </div>

    <div class="gmb-preview-stage"
         data-layout="<?php echo esc_attr($wgmbr_current_layout); ?>"
         style="<?php echo esc_attr($wgmbr_preview_style); ?>">

        <div class="gmb-reviews-summary">
            <span class="gmb-rating-number"><?php echo esc_html(number_format($wgmbr_preview_average, 1)); ?></span>
            <div class="gmb-overall-rating">
                <div class="gmb-stars">
                    <?php echo wp_kses_post(wgmbr_render_stars($wgmbr_preview_average)); ?>
                </div>
                <span class="gmb-total-reviews">
                    <?php
                    printf(
                        /* translators: %d: total number of reviews */
                        esc_html__('Based on %d reviews', 'reviews-for-google-my-business'),
                        absint($wgmbr_preview_total)
                    );
                    ?>
                </span>
            </div>
        </div>

        <!-- Static slider preview (no Swiper in admin) -->
        <div class="gmb-preview-slider">
            <span class="gmb-preview-arrow prev" aria-hidden="true"></span>
            <div class="gmb-preview-slider-track">
                <?php
                foreach (array_slice($wgmbr_preview_reviews, 0, 2) as $wgmbr_preview_review) {
                    $wgmbr_is_modal = false;
                    $wgmbr_review_index = 0;
                    $wgmbr_parsed_item = $wgmbr_preview_review;
                    include WGMBR_PLUGIN_DIR . 'templates/review-card.php';
                }
                ?>
            </div>
            <span class="gmb-preview-arrow next" aria-hidden="true"></span>
            <div class="gmb-preview-bullets" aria-hidden="true">
                <span class="is-active"></span><span></span><span></span>
            </div>
        </div>

        <!-- Masonry preview (same pure CSS technique as the frontend) -->
        <div class="gmb-preview-masonry">
            <?php
            foreach ($wgmbr_preview_reviews as $wgmbr_preview_review) {
                $wgmbr_is_modal = false;
                $wgmbr_review_index = 0;
                $wgmbr_parsed_item = $wgmbr_preview_review;
                ?>
                <div class="gmb-preview-masonry-item">
                    <?php include WGMBR_PLUGIN_DIR . 'templates/review-card.php'; ?>
                </div>
                <?php
            }
            ?>
        </div>

        <!-- Static "Show more" button (masonry layout only, decorative) -->
        <div class="gmb-preview-show-more" aria-hidden="true">
            <span class="gmb-show-more-btn"><?php esc_html_e('Show more', 'reviews-for-google-my-business'); ?></span>
        </div>
    </div>

    <div class="button-wrapper gmb-preview-actions">
        <button type="submit" class="button button-primary">
            <?php esc_html_e('Save customization', 'reviews-for-google-my-business'); ?>
        </button>
        <button type="button" class="button" onclick="resetGMBCustomization(this)">
            <?php esc_html_e('Reset to default values', 'reviews-for-google-my-business'); ?>
        </button>
    </div>
</div>
