<?php
/**
 * Reviews for Google My Business - Shortcode and HTML display
 * Shortcode [wgmbr_reviews] and rendering functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// STYLES FRONTEND
// ============================================================================

/**
 * Register frontend styles
 * Common styles always load with the shortcode; layout-specific styles
 * (slider = Swiper CSS, masonry = CSS columns) load only for the active layout
 */
function wgmbr_enqueue_frontend_styles() {
    wp_enqueue_style(
        'wgmbr-frontend-styles',
        WGMBR_PLUGIN_URL . 'assets/css/frontend.css',
        array(),
        WGMBR_VERSION
    );

    if (wgmbr_get_layout() === 'masonry') {
        wp_enqueue_style(
            'wgmbr-masonry-styles',
            WGMBR_PLUGIN_URL . 'assets/css/masonry.css',
            array('wgmbr-frontend-styles'),
            WGMBR_VERSION
        );
    } else {
        wp_enqueue_style(
            'wgmbr-slider-styles',
            WGMBR_PLUGIN_URL . 'assets/css/slider.css',
            array('wgmbr-frontend-styles'),
            WGMBR_VERSION
        );
    }

    // Add custom inline styles if options are set
    $custom_css = wgmbr_generate_custom_css();
    if (!empty($custom_css)) {
        wp_add_inline_style('wgmbr-frontend-styles', $custom_css);
    }
}

/**
 * Register frontend scripts
 * app.js (modal + read more) always loads; Swiper loads only for the slider
 * layout, the "Show more" AJAX script only for the masonry layout
 */
function wgmbr_enqueue_frontend_scripts() {
    wp_enqueue_script(
        'wgmbr-frontend-app',
        WGMBR_PLUGIN_URL . 'assets/js/app.js',
        array(),
        WGMBR_VERSION,
        true  // Load in footer
    );

    if (wgmbr_get_layout() === 'masonry') {
        wp_enqueue_script(
            'wgmbr-frontend-masonry',
            WGMBR_PLUGIN_URL . 'assets/js/masonry.js',
            array('wgmbr-frontend-app'),
            WGMBR_VERSION,
            true
        );

        wp_localize_script('wgmbr-frontend-masonry', 'wgmbrFront', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'i18n' => array(
                'loading' => esc_html__('Loading...', 'reviews-for-google-my-business'),
                'error' => esc_html__('Unable to load more reviews. Please try again.', 'reviews-for-google-my-business'),
            ),
        ));
    } else {
        wp_enqueue_script(
            'wgmbr-frontend-slider',
            WGMBR_PLUGIN_URL . 'assets/js/slider.js',
            array(),
            WGMBR_VERSION,
            true
        );
    }
}

/**
 * Generate custom CSS based on user options
 * Uses CSS Custom Properties (CSS variables) for cleaner overrides
 *
 * Security: Implements "Escape Late" principle - all values are escaped before output
 */
function wgmbr_generate_custom_css() {
    $custom_vars = array();

    $default_colors = WGMBR_DEFAULT_COLORS;

    // Card background color
    $card_bg = get_option('wgmbr_color_card_bg');
    if ($card_bg && $card_bg !== $default_colors['card_bg']) {
        // ESCAPE LATE: Escape hex color for CSS output
        $custom_vars[] = "--gmb-color-card-bg: " . esc_attr($card_bg);
    }

    // Card border radius
    $card_radius = get_option('wgmbr_radius_card');
    if ($card_radius !== false && $card_radius !== '' && $card_radius !== WGMBR_DEFAULT_CARD_RADIUS) {
        // ESCAPE LATE: Ensure it's a safe integer for CSS output
        $custom_vars[] = "--gmb-radius-card: " . absint($card_radius) . "px";
    }

    // Star color
    $color_star = get_option('wgmbr_color_star');
    if ($color_star && $color_star !== $default_colors['star']) {
        // ESCAPE LATE: Escape hex color for CSS output
        $custom_vars[] = "--gmb-color-star: " . esc_attr($color_star);
    }

    // Text color
    $color_text_primary = get_option('wgmbr_color_text_primary');
    if ($color_text_primary && $color_text_primary !== $default_colors['text_primary']) {
        // ESCAPE LATE: Escape hex color for CSS output
        $custom_vars[] = "--gmb-color-text-primary: " . esc_attr($color_text_primary);
    }

    // Summary text color
    $color_test_resume = get_option('wgmbr_color_text_resume');
    if ($color_test_resume && $color_test_resume !== $default_colors['text_resume']) {
        // ESCAPE LATE: Escape hex color for CSS output
        $custom_vars[] = "--gmb-color-text-resume: " . esc_attr($color_test_resume);
    }

    // Navigation color (slider arrows + pagination dots)
    $color_accent = get_option('wgmbr_color_accent');
    if ($color_accent && $color_accent !== $default_colors['accent']) {
        // ESCAPE LATE: Escape hex color for CSS output
        $custom_vars[] = "--gmb-color-accent: " . esc_attr($color_accent);
    }

    // "Show more" button color (masonry layout)
    $color_show_more = get_option('wgmbr_color_show_more');
    if ($color_show_more && $color_show_more !== $default_colors['show_more']) {
        // ESCAPE LATE: Escape hex color for CSS output
        $custom_vars[] = "--gmb-color-show-more: " . esc_attr($color_show_more);
    }

    // Generate CSS only if there are custom values
    if (!empty($custom_vars)) {
        return ":root {\n  " . implode(";\n  ", $custom_vars) . ";\n}";
    }

    return '';
}

// ============================================================================
// SHORTCODE
// ============================================================================

/**
 * Shortcode to display GMB reviews
 *
 * @param array $atts Shortcode attributes
 * @return string Reviews HTML
 */
function wgmbr_reviews_shortcode($atts) {
    // Load styles and scripts only if shortcode is used
    wgmbr_enqueue_frontend_styles();
    wgmbr_enqueue_frontend_scripts();

    $atts = shortcode_atts(array(
        'limit' => WGMBR_DEFAULT_REVIEW_LIMIT,
        'category' => null,  // Category slug (null = all, string = one or more separated by comma)
        'show_summary' => 'true',  // Display summary (true/false)
        'order' => 'recent'  // Display order: 'recent' (newest first) or 'random'
    ), $atts, 'wgmbr_reviews');

    // VALIDATE: Safelist the order attribute, then map it to a WP_Query orderby
    $order = in_array($atts['order'], array('recent', 'random'), true) ? $atts['order'] : 'recent';
    $orderby = ($order === 'random') ? 'rand' : 'date';

    // Check if API is authenticated and if there are reviews
    $has_token = get_option('wgmbr_access_token') ? true : false;
    $total_reviews = wgmbr_get_total_reviews_count();

    // If not authenticated and no reviews exist, show error message
    if (!$has_token && $total_reviews === 0) {
        $admin_url = admin_url('admin.php?page=wgmbr-settings');
        return sprintf(
            '<div class="gmb-notice warning">
                <p>
                    <strong>%s</strong><br>
                    %s <a href="%s" style="text-decoration: underline;">%s</a>
                </p>
            </div>',
            esc_html__('Google My Business API is not authenticated.', 'reviews-for-google-my-business'),
            esc_html__('Please configure OAuth from the', 'reviews-for-google-my-business'),
            esc_url($admin_url),
            esc_html__('GMB Reviews page in the admin', 'reviews-for-google-my-business')
        );
    }

    $layout = wgmbr_get_layout();
    $limit = max(1, (int) $atts['limit']);
    $has_category_filter = ($atts['category'] !== null);
    $category_param = $has_category_filter ? wgmbr_parse_category_param($atts['category']) : null;
    $has_more = false;

    // Get reviews from CPT
    if ($layout === 'masonry') {
        // Masonry: only fetch the first batch, the rest loads on demand via AJAX
        $args = array(
            'posts_per_page' => min(WGMBR_MASONRY_INITIAL, $limit),
            'orderby' => $orderby,
        );

        if ($has_category_filter) {
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary to filter reviews by category, standard WordPress method
            $args['tax_query'] = wgmbr_build_category_tax_query($category_param);
        }

        $result = wgmbr_get_all_reviews_with_query($args);
        $reviews = $result['reviews'];
        $has_more = (count($reviews) < $limit) && ($result['query']->found_posts > count($reviews));
    } elseif ($has_category_filter) {
        // Filter by category(ies)
        $reviews = wgmbr_get_reviews_by_category($category_param, $limit, $orderby);
    } else {
        // All reviews
        $reviews = wgmbr_get_all_reviews(array(
            'posts_per_page' => $limit,
            'orderby' => $orderby
        ));
    }

    // Convert show_summary to boolean
    $show_summary = filter_var($atts['show_summary'], FILTER_VALIDATE_BOOLEAN);

    // Prepare data for template (compatible format with old system)
    $data = array(
        'error' => false,
        'source' => 'Custom Post Type',
        'reviews' => $reviews,
        'total' => wgmbr_get_total_reviews_count(),
        'average_rating' => wgmbr_get_average_rating(),
        'show_summary' => $show_summary,  // Summary display control
        'layout' => $layout,
        'has_more' => $has_more,
        // Raw shortcode context, passed to the "Show more" AJAX endpoint
        'query_context' => array(
            'limit' => $limit,
            'order' => $order,
            'category' => $has_category_filter ? (string) $atts['category'] : '',
            'category_filter' => $has_category_filter ? '1' : '0',
        ),
    );

    ob_start();
    require WGMBR_PLUGIN_DIR . 'templates/reviews-display.php';
    return ob_get_clean();
}
add_shortcode('wgmbr_reviews', 'wgmbr_reviews_shortcode');

// ============================================================================
// FONCTIONS UTILITAIRES
// ============================================================================

/**
 * Generate stars HTML
 */
function wgmbr_render_stars($rating) {
    $full_stars = floor($rating);
    $half_star = ($rating - $full_stars) >= 0.5;
    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);

    $html = '';

    for ($i = 0; $i < $full_stars; $i++) {
        $html .= '<span class="gmb-star gmb-star-full"></span>';
    }

    if ($half_star) {
        $html .= '<span class="gmb-star gmb-star-half"></span>';
    }

    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= '<span class="gmb-star gmb-star-empty"></span>';
    }

    return $html;
}

/**
 * Render masonry grid items (card wrapped with its post ID)
 * Shared between the initial shortcode render and the "Show more" AJAX endpoint
 *
 * @param array $reviews Parsed review objects
 * @param int $start_index Review index of the first card (for modal templates)
 * @return string HTML (escaped in templates/review-card.php)
 */
function wgmbr_render_masonry_items($reviews, $start_index = 0) {
    ob_start();

    foreach ($reviews as $wgmbr_i => $wgmbr_review) {
        $wgmbr_review_index = $start_index + $wgmbr_i;
        $wgmbr_is_modal = false;
        $wgmbr_parsed_item = $wgmbr_review;
        ?>
        <div class="gmb-masonry-item" data-post-id="<?php echo absint($wgmbr_review->post_id); ?>">
            <?php include WGMBR_PLUGIN_DIR . 'templates/review-card.php'; ?>
        </div>
        <?php
    }

    return ob_get_clean();
}

/**
 * Render hidden modal templates for a set of reviews
 * Shared between the initial shortcode render and the "Show more" AJAX endpoint
 *
 * @param array $reviews Parsed review objects
 * @param int $start_index Review index of the first template
 * @return string HTML (escaped in templates/review-card.php)
 */
function wgmbr_render_modal_templates($reviews, $start_index = 0) {
    ob_start();

    foreach ($reviews as $wgmbr_i => $wgmbr_review) {
        $wgmbr_review_index = $start_index + $wgmbr_i;
        $wgmbr_is_modal = true;
        $wgmbr_parsed_item = $wgmbr_review;
        ?>
        <div class="gmb-modal-template" data-review-index="<?php echo absint($wgmbr_review_index); ?>">
            <?php include WGMBR_PLUGIN_DIR . 'templates/review-card.php'; ?>
        </div>
        <?php
    }

    return ob_get_clean();
}

// ============================================================================
// AJAX: LOAD MORE REVIEWS (MASONRY)
// ============================================================================

/**
 * AJAX handler for the masonry "Show more" button
 *
 * Public read-only endpoint: it only returns already-public review content
 * (same data as the shortcode output), performs no state change and requires
 * no capability. No nonce on purpose: pages embedding the shortcode are
 * typically served from page cache longer than a nonce lifetime, which would
 * break the button. All inputs are strictly validated/sanitized instead.
 */
function wgmbr_load_more_reviews_ajax() {
    // VALIDATE: Safelist the order value
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only public endpoint, see docblock
    $order = isset($_POST['order']) ? sanitize_key(wp_unslash($_POST['order'])) : 'recent';
    $orderby = ($order === 'random') ? 'rand' : 'date';

    // SANITIZE + VALIDATE: limit bounded to the plugin maximum
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only public endpoint, see docblock
    $limit = isset($_POST['limit']) ? absint(wp_unslash($_POST['limit'])) : WGMBR_DEFAULT_REVIEW_LIMIT;
    $limit = max(1, min($limit, WGMBR_DEFAULT_REVIEW_LIMIT));

    // SANITIZE: IDs of reviews already displayed (positive integers only)
    $exclude = array();
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only public endpoint, see docblock
    if (isset($_POST['exclude']) && is_array($_POST['exclude'])) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only public endpoint, see docblock
        $exclude = array_filter(array_map('absint', wp_unslash($_POST['exclude'])));
    }

    $shown = count($exclude);

    // VALIDATE: never serve more than the shortcode limit
    if ($shown >= $limit) {
        wp_send_json_success(array('html' => '', 'modals' => '', 'has_more' => false));
    }

    $batch = min(WGMBR_MASONRY_BATCH, $limit - $shown);

    $args = array(
        'posts_per_page' => $batch,
        'orderby' => $orderby,
        'post__not_in' => $exclude,
    );

    // SANITIZE: category filter (same semantics as the shortcode attribute)
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only public endpoint, see docblock
    $category_filter = isset($_POST['category_filter']) && sanitize_key(wp_unslash($_POST['category_filter'])) === '1';
    if ($category_filter) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only public endpoint, see docblock
        $category_raw = isset($_POST['category']) ? sanitize_text_field(wp_unslash($_POST['category'])) : '';
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary to filter reviews by category, standard WordPress method
        $args['tax_query'] = wgmbr_build_category_tax_query(wgmbr_parse_category_param($category_raw));
    }

    $result = wgmbr_get_all_reviews_with_query($args);
    $reviews = $result['reviews'];
    $fetched = count($reviews);

    // Modal template indexes continue after the cards already on the page
    $has_more = ($shown + $fetched) < $limit && $result['query']->found_posts > $fetched;

    wp_send_json_success(array(
        'html' => wgmbr_render_masonry_items($reviews, $shown),
        'modals' => wgmbr_render_modal_templates($reviews, $shown),
        'has_more' => $has_more,
    ));
}

add_action('wp_ajax_wgmbr_load_more_reviews', 'wgmbr_load_more_reviews_ajax');
add_action('wp_ajax_nopriv_wgmbr_load_more_reviews', 'wgmbr_load_more_reviews_ajax');