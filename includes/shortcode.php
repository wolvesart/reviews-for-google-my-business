<?php
/**
 * Reviews for Google My Business - Shortcode and HTML display
 * Shortcode [gmb_reviews] and rendering functions
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
 */
function wgmbr_enqueue_frontend_styles() {
    wp_enqueue_style(
        'gmb-frontend-styles',
        WOLVES_GMB_PLUGIN_URL . 'assets/css/frontend.css',
        array(),
        WOLVES_GMB_VERSION
    );

    // Add custom inline styles if options are set
    $custom_css = wgmbr_generate_custom_css();
    if (!empty($custom_css)) {
        wp_add_inline_style('gmb-frontend-styles', $custom_css);
    }
}

/**
 * Generate custom CSS based on user options
 * Uses CSS Custom Properties (CSS variables) for cleaner overrides
 */
function wgmbr_generate_custom_css() {
    $custom_vars = array();

    $default_colors = WGMBR_DEFAULT_COLORS;

    // Card background color
    $card_bg = get_option('wgmbr_color_card_bg');
    if ($card_bg && $card_bg !== $default_colors['card_bg']) {
        $custom_vars[] = "--gmb-color-card-bg: {$card_bg}";
    }

    // Card border radius
    $card_radius = get_option('wgmbr_radius_card');
    if ($card_radius !== false && $card_radius !== '' && $card_radius !== WGMBR_DEFAULT_CARD_RADIUS) {
        $custom_vars[] = "--gmb-radius-card: {$card_radius}px";
    }

    // Star color
    $color_star = get_option('wgmbr_color_star');
    if ($color_star && $color_star !== $default_colors['star']) {
        $custom_vars[] = "--gmb-color-star: {$color_star}";
    }

    // Text color
    $color_text_primary = get_option('wgmbr_color_text_primary');
    if ($color_text_primary && $color_text_primary !== $default_colors['text_primary']) {
        $custom_vars[] = "--gmb-color-text-primary: {$color_text_primary}";
    }

    // Summary text color
    $color_test_resume = get_option('wgmbr_color_text_resume');
    if ($color_test_resume && $color_test_resume !== $default_colors['text_resume']) {
        $custom_vars[] = "--gmb-color-text-resume: {$color_test_resume}";
    }

    // Accent color
    $color_accent = get_option('wgmbr_color_accent');
    if ($color_accent && $color_accent !== $default_colors['accent']) {
        $custom_vars[] = "--gmb-color-accent: {$color_accent}";
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
    // Load styles only if shortcode is used
    wgmbr_enqueue_frontend_styles();

    $atts = shortcode_atts(array(
        'limit' => WGMBR_DEFAULT_REVIEW_LIMIT,
        'category' => null,  // Category slug (null = all, string = one or more separated by comma)
        'show_summary' => 'true'  // Display summary (true/false)
    ), $atts);

    // Check if API is authenticated and if there are reviews
    $has_token = get_option('wgmbr_access_token') ? true : false;
    $total_reviews = wgmbr_get_total_reviews_count();

    // If not authenticated and no reviews exist, show error message
    if (!$has_token && $total_reviews === 0) {
        $admin_url = admin_url('admin.php?page=gmb-settings');
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

    // Get reviews from CPT
    if ($atts['category'] !== null) {
        // Parse multiple categories (comma-separated)
        $category_param = $atts['category'];

        // If not an empty string, check if there are multiple categories
        if ($category_param !== '') {
            $categories = array_map('trim', explode(',', $category_param));
            // If single category, use string, otherwise use array
            $category_param = (count($categories) === 1) ? $categories[0] : $categories;
        }

        // Filter by category(ies)
        $reviews = wgmbr_get_reviews_by_category($category_param, (int) $atts['limit']);
    } else {
        // All reviews
        $reviews = wgmbr_get_all_reviews(array(
            'posts_per_page' => (int) $atts['limit']
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
    );

    ob_start();
    require WOLVES_GMB_PLUGIN_DIR . 'templates/reviews-display.php';
    return ob_get_clean();
}
add_shortcode('gmb_reviews', 'wgmbr_reviews_shortcode');

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

// Note: wgmbr_convert_star_rating() is now defined in includes/post-types.php
