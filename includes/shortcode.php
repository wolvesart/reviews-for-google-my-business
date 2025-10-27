<?php
/**
 * Reviews for Google My Business - Shortcode et affichage HTML
 * Shortcode [gmb_reviews] et fonctions de rendu
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// STYLES FRONTEND
// ============================================================================

/**
 * Enregistre les styles frontend
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

    // Card background color
    $card_bg = get_option('wgmbr_color_card_bg');
    if ($card_bg && $card_bg !== '#F3F5F7') {
        $custom_vars[] = "--gmb-color-card-bg: {$card_bg}";
    }

    // Card border radius
    $card_radius = get_option('wgmbr_radius_card');
    if ($card_radius !== false && $card_radius !== '' && $card_radius !== '16') {
        $custom_vars[] = "--gmb-radius-card: {$card_radius}px";
    }

    // Star color
    $color_star = get_option('wgmbr_color_star');
    if ($color_star && $color_star !== '#FFC83E') {
        $custom_vars[] = "--gmb-color-star: {$color_star}";
    }

    // Text color
    $color_text_primary = get_option('wgmbr_color_text_primary');
    if ($color_text_primary && $color_text_primary !== '#222222') {
        $custom_vars[] = "--gmb-color-text-primary: {$color_text_primary}";
    }

    // Text color resume
    $color_test_resume = get_option('wgmbr_color_text_resume');
    if ($color_test_resume && $color_test_resume !== '#222222') {
        $custom_vars[] = "--gmb-color-text-resume: {$color_test_resume}";
    }

    // Accent color
    $color_accent = get_option('wgmbr_color_accent');
    if ($color_accent && $color_accent !== '#0F68DD') {
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
 * Shortcode pour afficher les avis GMB
 *
 * Usage:
 * - [gmb_reviews limit="10"] - Affiche tous les avis (limite 10)
 * - [gmb_reviews category="formation"] - Affiche uniquement les avis de la catégorie "formation"
 * - [gmb_reviews category="formation,coaching"] - Affiche les avis des catégories "formation" ET "coaching"
 * - [gmb_reviews category="formation,coaching,dev" limit="5"] - Affiche 5 avis de plusieurs catégories
 * - [gmb_reviews category=""] - Affiche uniquement les avis sans catégorie
 * - [gmb_reviews show_summary="false"] - Masque le résumé (note moyenne et nombre d'avis)
 *
 * @param array $atts Attributs du shortcode
 * @return string HTML des avis
 */
function wgmbr_reviews_shortcode($atts) {
    // Charger les styles uniquement si le shortcode est utilisé
    wgmbr_enqueue_frontend_styles();

    $atts = shortcode_atts(array(
        'limit' => 50,
        'category' => null,  // Slug de la catégorie (null = toutes, string = une ou plusieurs séparées par virgule)
        'show_summary' => 'true'  // Afficher le résumé (true/false)
    ), $atts);

    // Récupérer les avis depuis les CPT
    if ($atts['category'] !== null) {
        // Parser les catégories multiples (séparées par des virgules)
        $category_param = $atts['category'];

        // Si ce n'est pas une chaîne vide, vérifier s'il y a plusieurs catégories
        if ($category_param !== '') {
            $categories = array_map('trim', explode(',', $category_param));
            // Si une seule catégorie, utiliser la string, sinon utiliser le tableau
            $category_param = (count($categories) === 1) ? $categories[0] : $categories;
        }

        // Filtrer par catégorie(s)
        $reviews = wgmbr_get_reviews_by_category($category_param, (int) $atts['limit']);
    } else {
        // Tous les avis
        $reviews = wgmbr_get_all_reviews(array(
            'posts_per_page' => (int) $atts['limit']
        ));
    }

    // Convertir show_summary en boolean
    $show_summary = filter_var($atts['show_summary'], FILTER_VALIDATE_BOOLEAN);

    // Préparer les données pour le template (format compatible avec l'ancien système)
    $data = array(
        'error' => false,
        'source' => 'Custom Post Type',
        'reviews' => $reviews,
        'total' => wgmbr_get_total_reviews_count(),
        'average_rating' => wgmbr_get_average_rating(),
        'show_summary' => $show_summary,  // Contrôle de l'affichage du résumé
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
 * Génère le HTML des étoiles
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

// Note: wgmbr_convert_star_rating() est maintenant définie dans includes/post-types.php
