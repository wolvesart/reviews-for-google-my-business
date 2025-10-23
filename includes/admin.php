<?php
/**
 * Google My Business Reviews - Interface Admin
 * Page de configuration et actions administrateur
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// MENU ADMIN
// ============================================================================

/**
 * Ajoute une page d'admin pour gérer l'authentification
 */
function gmb_add_admin_menu() {
    // Page principale : Les avis
    add_menu_page(
        'Avis Google',
        'Avis Google',
        'manage_options',
        'gmb-manage-reviews',
        'gmb_manage_reviews_page',
        'dashicons-star-filled',
        30
    );

    // Sous-page : Catégories
    add_submenu_page(
        'gmb-manage-reviews',
        'Catégories',
        'Catégories',
        'manage_options',
        'gmb-categories',
        'gmb_categories_page'
    );

    // Sous-page : Configuration
    add_submenu_page(
        'gmb-manage-reviews',
        'Configuration',
        'Configuration',
        'manage_options',
        'gmb-settings',
        'gmb_settings_page'
    );
}
add_action('admin_menu', 'gmb_add_admin_menu');

/**
 * Enregistre les styles admin
 */
function gmb_enqueue_admin_styles($hook) {
    // Charger les styles pour toutes les pages GMB
    if ('toplevel_page_gmb-manage-reviews' !== $hook && 'avis-google_page_gmb-settings' !== $hook && 'avis-google_page_gmb-categories' !== $hook) {
        return;
    }

    // Enqueue custom admin styles
    wp_enqueue_style(
        'gmb-admin-styles',
        WOLVES_GMB_PLUGIN_URL . 'assets/css/admin.css',
        array(),
        WOLVES_GMB_VERSION
    );

    // Corriger les chemins absolus générés par webpack pour utiliser le chemin du plugin
    $custom_css = '.gmb-header { background-image: url("' . WOLVES_GMB_PLUGIN_URL . 'assets/images/gmb-pattern.png") !important; }';
    wp_add_inline_style('gmb-admin-styles', $custom_css);
}
add_action('admin_enqueue_scripts', 'gmb_enqueue_admin_styles');

// ============================================================================
// PAGE DE CONFIGURATION
// ============================================================================

/**
 * Page de paramètres admin
 */
function gmb_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.'));
    }

    $has_token = get_option('gmb_access_token') ? true : false;
    $available_locations = get_option('gmb_available_locations', array());
    $current_account_id = get_option('gmb_account_id');
    $current_location_id = get_option('gmb_location_id');
    $has_credentials = GMB_CLIENT_ID && GMB_CLIENT_SECRET;

    // Charger le template
    require_once WOLVES_GMB_PLUGIN_DIR . 'templates/admin-page.php';
}

/**
 * Page de gestion des avis
 */
function gmb_manage_reviews_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.'));
    }

    // Récupérer les avis
    $data = gmb_fetch_reviews();

    // Charger le template
    require_once WOLVES_GMB_PLUGIN_DIR . 'templates/manage-reviews-page.php';
}

/**
 * Page de gestion des catégories
 */
function gmb_categories_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.'));
    }

    // Charger le template
    require_once WOLVES_GMB_PLUGIN_DIR . 'templates/categories-page.php';
}

// ============================================================================
// ACTIONS AJAX
// ============================================================================

/**
 * Action AJAX pour rafraîchir les locations
 */
function gmb_refresh_locations_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permissions insuffisantes'));
        return;
    }

    gmb_auto_fetch_accounts_and_locations();
    wp_send_json_success();
}
add_action('wp_ajax_gmb_refresh_locations', 'gmb_refresh_locations_ajax');

/**
 * Action AJAX pour vider le cache
 */
function gmb_clear_cache_ajax() {
    delete_transient('gmb_reviews_cache');
    wp_send_json_success();
}
add_action('wp_ajax_gmb_clear_cache', 'gmb_clear_cache_ajax');

/**
 * Action AJAX pour tester la connexion
 */
function gmb_test_connection_ajax() {
    $data = gmb_fetch_reviews();

    if (isset($data['error']) && $data['error']) {
        // Retourner plus de détails pour le debug
        $error_data = array(
            'message' => $data['message']
        );

        // Ajouter les détails de la réponse API si disponibles
        if (isset($data['api_response'])) {
            $error_data['response'] = $data['api_response'];
        }

        wp_send_json_error($error_data);
    } else {
        wp_send_json_success(array('count' => count($data['reviews'])));
    }
}
add_action('wp_ajax_gmb_test_connection', 'gmb_test_connection_ajax');

// ============================================================================
// ACTIONS POST
// ============================================================================

/**
 * Action pour sauvegarder les identifiants API
 */
function gmb_save_credentials() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
    }

    check_admin_referer('gmb_save_credentials', 'gmb_credentials_nonce');

    if (!isset($_POST['gmb_client_id']) || !isset($_POST['gmb_client_secret']) || !isset($_POST['gmb_redirect_uri'])) {
        wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=error'));
        exit;
    }

    $client_id = sanitize_text_field(wp_unslash($_POST['gmb_client_id']));
    $client_secret = sanitize_text_field(wp_unslash($_POST['gmb_client_secret']));
    $redirect_uri = esc_url_raw(wp_unslash($_POST['gmb_redirect_uri']));

    // Sauvegarder en base de données de manière sécurisée
    update_option('gmb_client_id', $client_id);
    update_option('gmb_client_secret', $client_secret);
    update_option('gmb_redirect_uri', $redirect_uri);

    wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=credentials_saved'));
    exit;
}
add_action('admin_post_gmb_save_credentials', 'gmb_save_credentials');

/**
 * Action pour sauvegarder la location sélectionnée
 */
function gmb_save_location() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
    }

    check_admin_referer('gmb_save_location', 'gmb_location_nonce');

    if (!isset($_POST['gmb_location']) || empty($_POST['gmb_location'])) {
        wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=error'));
        exit;
    }

    $location_data = sanitize_text_field(wp_unslash($_POST['gmb_location']));
    $parts = explode('|', $location_data);

    if (count($parts) !== 2) {
        wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=error'));
        exit;
    }

    $account_id = $parts[0];
    $location_id = $parts[1];

    // Sauvegarder en base de données
    update_option('gmb_account_id', $account_id);
    update_option('gmb_location_id', $location_id);

    // Vider le cache des avis pour forcer le rafraîchissement
    delete_transient('gmb_reviews_cache');

    wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=location_saved'));
    exit;
}
add_action('admin_post_gmb_save_location', 'gmb_save_location');

/**
 * Révoque l'accès OAuth
 */
function gmb_revoke_access() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
    }

    check_admin_referer('gmb_revoke_action');

    delete_option('gmb_access_token');
    delete_option('gmb_refresh_token');
    delete_option('gmb_token_expires');
    delete_transient('gmb_reviews_cache');

    wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=revoked'));
    exit;
}
add_action('admin_post_gmb_revoke', 'gmb_revoke_access');

/**
 * Action pour sauvegarder la personnalisation
 */
function gmb_save_customization() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
    }

    check_admin_referer('gmb_save_customization', 'gmb_customization_nonce');

    // Sauvegarder l'affichage du résumé (checkbox)
    $show_summary = isset($_POST['gmb_show_summary']) ? '1' : '0';
    update_option('gmb_show_summary', $show_summary);

    // Sauvegarder la personnalisation
    if (isset($_POST['gmb_resume_text_color'])) {
        update_option('gmb_resume_text_color', sanitize_hex_color($_POST['gmb_resume_text_color']));
    }
    if (isset($_POST['gmb_card_bg_color'])) {
        update_option('gmb_card_bg_color', sanitize_hex_color($_POST['gmb_card_bg_color']));
    }
    if (isset($_POST['gmb_card_border_radius'])) {
        update_option('gmb_card_border_radius', absint($_POST['gmb_card_border_radius']));
    }
    if (isset($_POST['gmb_star_color'])) {
        update_option('gmb_star_color', sanitize_hex_color($_POST['gmb_star_color']));
    }
    if (isset($_POST['gmb_text_color'])) {
        update_option('gmb_text_color', sanitize_hex_color($_POST['gmb_text_color']));
    }
    if (isset($_POST['gmb-accent-color'])) {
        update_option('gmb-accent-color', sanitize_hex_color($_POST['gmb-accent-color']));
    }
    if (isset($_POST['gmb_text_color_name'])) {
        update_option('gmb_text_color_name', sanitize_hex_color($_POST['gmb_text_color_name']));
    }

    wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=customization_saved&tab=customization'));
    exit;
}
add_action('admin_post_gmb_save_customization', 'gmb_save_customization');

/**
 * Action AJAX pour réinitialiser la personnalisation
 */
function gmb_reset_customization_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permissions insuffisantes'));
        return;
    }

    // Supprimer toutes les options de personnalisation
    delete_option('gmb_show_summary');
    delete_option('gmb_resume_text_color');
    delete_option('gmb_card_bg_color');
    delete_option('gmb_card_border_radius');
    delete_option('gmb_star_color');
    delete_option('gmb_text_color');
    delete_option('gmb_text_color_name');
    delete_option('gmb_modal_bg_color');
    delete_option('gmb-accent-color');

    wp_send_json_success();
}
add_action('wp_ajax_gmb_reset_customization', 'gmb_reset_customization_ajax');
