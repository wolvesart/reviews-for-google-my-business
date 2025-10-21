<?php
/**
 * Google My Business Reviews - Shortcode et affichage HTML
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
function gmb_enqueue_frontend_styles() {
    wp_enqueue_style(
        'gmb-frontend-styles',
        WOLVES_GMB_PLUGIN_URL . 'assets/css/frontend.css',
        array(),
        WOLVES_GMB_VERSION
    );

    // Add custom inline styles if options are set
    $custom_css = gmb_generate_custom_css();
    if (!empty($custom_css)) {
        wp_add_inline_style('gmb-frontend-styles', $custom_css);
    }
}

/**
 * Generate custom CSS based on user options
 * Uses CSS Custom Properties (CSS variables) for cleaner overrides
 */
function gmb_generate_custom_css() {
    $custom_vars = array();

    // Card background color
    $card_bg = get_option('gmb_card_bg_color');
    if ($card_bg && $card_bg !== '#17171A') {
        $custom_vars[] = "--gmb-card-bg: {$card_bg}";
    }

    // Card border radius
    $card_radius = get_option('gmb_card_border_radius');
    if ($card_radius !== false && $card_radius !== '' && $card_radius !== '32') {
        $custom_vars[] = "--gmb-card-radius: {$card_radius}px";
    }

    // Star color
    $star_color = get_option('gmb_star_color');
    if ($star_color && $star_color !== '#F85430') {
        $custom_vars[] = "--gmb-star-color: {$star_color}";
    }

    // Resume text color
    $resume_text_color = get_option('gmb_resume_text_color');
    if ($resume_text_color && $resume_text_color !== '#FFFFFF') {
        $custom_vars[] = "--gmb-resume-text-color: {$resume_text_color}";
    }

    // Text color
    $text_color = get_option('gmb_text_color');
    if ($text_color && $text_color !== '#AEAEAE') {
        $custom_vars[] = "--gmb-text-color: {$text_color}";
    }

    // Read more color hover
    $read_more_color_hover = get_option('gmb-accent-color');
    if ($read_more_color_hover && $read_more_color_hover !== '#FFFFFF') {
        $custom_vars[] = "--gmb-accent-color: {$read_more_color_hover}";
    }

    // Text color name
    $text_color_name = get_option('gmb_text_color_name');
    if ($text_color_name && $text_color_name !== '#FFFFFF') {
        $custom_vars[] = "--gmb-text-color-name: {$text_color_name}";
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
 * - [gmb_reviews category="formation" limit="5"] - Affiche 5 avis de la catégorie "formation"
 * - [gmb_reviews category=""] - Affiche uniquement les avis sans catégorie
 *
 * @param array $atts Attributs du shortcode
 * @return string HTML des avis
 */
function gmb_reviews_shortcode($atts) {
    // Charger les styles uniquement si le shortcode est utilisé
    gmb_enqueue_frontend_styles();

    $atts = shortcode_atts(array(
        'limit' => 50,
        'category' => null  // Slug de la catégorie (null = toutes les catégories)
    ), $atts);

    $data = gmb_fetch_reviews();

    // Gestion des erreurs
    if (isset($data['error']) && $data['error']) {
        return '<div class="gmb-error">' . $data['message'] . '</div>';
    }

    // Filtrer par catégorie si spécifié
    if ($atts['category'] !== null) {
        $data['reviews'] = gmb_filter_reviews_by_category($data['reviews'], $atts['category']);
    }

    ob_start();
    require WOLVES_GMB_PLUGIN_DIR . 'templates/reviews-display.php';
    return ob_get_clean();
}
add_shortcode('gmb_reviews', 'gmb_reviews_shortcode');

// ============================================================================
// FONCTIONS UTILITAIRES
// ============================================================================

/**
 * Génère le HTML des étoiles
 */
function gmb_render_stars($rating) {
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
 * Convertit le format de notation GMB en numérique
 */
function gmb_convert_star_rating($star_rating) {
    $ratings = array(
        'STAR_RATING_UNSPECIFIED' => 0,
        'ONE' => 1,
        'TWO' => 2,
        'THREE' => 3,
        'FOUR' => 4,
        'FIVE' => 5
    );

    return isset($ratings[$star_rating]) ? $ratings[$star_rating] : 0;
}
