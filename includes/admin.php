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
function wgmbr_add_admin_menu() {
    // Page principale : Les avis
    add_menu_page(
        __('Google Reviews', 'wolves-avis-google'),
        __('Google Reviews', 'wolves-avis-google'),
        'manage_options',
        'gmb-manage-reviews',
        'wgmbr_manage_reviews_page',
        'dashicons-star-filled',
        30
    );

    // Sous-page : Catégories
    add_submenu_page(
        'gmb-manage-reviews',
        __('Categories', 'wolves-avis-google'),
        __('Categories', 'wolves-avis-google'),
        'manage_options',
        'gmb-categories',
        'wgmbr_categories_page'
    );

    // Sous-page : Configuration
    add_submenu_page(
        'gmb-manage-reviews',
        __('Configuration', 'wolves-avis-google'),
        __('Configuration', 'wolves-avis-google'),
        'manage_options',
        'gmb-settings',
        'wgmbr_settings_page'
    );
}
add_action('admin_menu', 'wgmbr_add_admin_menu');

/**
 * Enregistre les styles admin
 */
function wgmbr_enqueue_admin_styles($hook) {
    // Charger les styles pour toutes les pages GMB
    if ('toplevel_page_gmb-manage-reviews' !== $hook && 'google-reviews_page_gmb-settings' !== $hook && 'google-reviews_page_gmb-categories' !== $hook) {
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
add_action('admin_enqueue_scripts', 'wgmbr_enqueue_admin_styles');

// ============================================================================
// PAGE DE CONFIGURATION
// ============================================================================

/**
 * Page de paramètres admin
 */
function wgmbr_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'wolves-avis-google'));
    }

    $has_token = get_option('wgmbr_access_token') ? true : false;
    $available_locations = get_option('wgmbr_available_locations', array());
    $current_account_id = get_option('wgmbr_account_id');
    $current_location_id = get_option('wgmbr_location_id');
    $has_credentials = GMB_CLIENT_ID && GMB_CLIENT_SECRET;

    // Charger le template
    require_once WOLVES_GMB_PLUGIN_DIR . 'templates/admin-page.php';
}

/**
 * Page de gestion des avis
 */
function wgmbr_manage_reviews_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'wolves-avis-google'));
    }

    // Récupérer les avis depuis les CPT
    $reviews = wgmbr_get_all_reviews(array(
        'posts_per_page' => -1, // Tous les avis
    ));

    // Récupérer toutes les catégories (taxonomie)
    $categories = get_terms(array(
        'taxonomy'   => 'gmb_category',
        'hide_empty' => false,
    ));

    // Préparer les données pour le template
    $data = array(
        'error' => false,
        'reviews' => $reviews,
        'total' => count($reviews),
    );

    // Si aucun avis, vérifier si c'est une erreur de configuration
    if (empty($reviews)) {
        $has_token = get_option('wgmbr_access_token');
        if (!$has_token) {
            $data['error'] = true;
            $data['message'] = __('API not authenticated. Please configure OAuth from the Configuration page.', 'wolves-avis-google');
        }
    }

    // Charger le template
    require_once WOLVES_GMB_PLUGIN_DIR . 'templates/manage-reviews-page.php';
}

/**
 * Page de gestion des catégories
 */
function wgmbr_categories_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'wolves-avis-google'));
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
function wgmbr_refresh_locations_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'wolves-avis-google')));
        return;
    }

    wgmbr_auto_fetch_accounts_and_locations();
    wp_send_json_success();
}
add_action('wp_ajax_wgmbr_refresh_locations', 'wgmbr_refresh_locations_ajax');

/**
 * Action AJAX pour vider le cache
 */
function wgmbr_clear_cache_ajax() {
    delete_transient('wgmbr_reviews_cache');
    wp_send_json_success();
}
add_action('wp_ajax_wgmbr_clear_cache', 'wgmbr_clear_cache_ajax');

/**
 * Action AJAX pour tester la connexion
 */
function wgmbr_test_connection_ajax() {
    $data = wgmbr_fetch_reviews();

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
add_action('wp_ajax_wgmbr_test_connection', 'wgmbr_test_connection_ajax');

// ============================================================================
// ACTIONS POST
// ============================================================================

/**
 * Action pour sauvegarder les identifiants API
 */
function wgmbr_save_credentials() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions.', 'wolves-avis-google'));
    }

    check_admin_referer('wgmbr_save_credentials', 'wgmbr_credentials_nonce');

    if (!isset($_POST['wgmbr_client_id']) || !isset($_POST['wgmbr_client_secret']) || !isset($_POST['wgmbr_redirect_uri'])) {
        wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=error'));
        exit;
    }

    $client_id = sanitize_text_field(wp_unslash($_POST['wgmbr_client_id']));
    $client_secret = sanitize_text_field(wp_unslash($_POST['wgmbr_client_secret']));
    $redirect_uri = esc_url_raw(wp_unslash($_POST['wgmbr_redirect_uri']));

    // Sauvegarder en base de données de manière sécurisée
    update_option('wgmbr_client_id', $client_id);
    update_option('wgmbr_client_secret', $client_secret);
    update_option('wgmbr_redirect_uri', $redirect_uri);

    wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=credentials_saved'));
    exit;
}
add_action('admin_post_wgmbr_save_credentials', 'wgmbr_save_credentials');

/**
 * Action pour sauvegarder la location sélectionnée
 */
function wgmbr_save_location() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions.', 'wolves-avis-google'));
    }

    check_admin_referer('wgmbr_save_location', 'wgmbr_location_nonce');

    if (!isset($_POST['wgmbr_location']) || empty($_POST['wgmbr_location'])) {
        wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=error'));
        exit;
    }

    $location_data = sanitize_text_field(wp_unslash($_POST['wgmbr_location']));
    $parts = explode('|', $location_data);

    if (count($parts) !== 2) {
        wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=error'));
        exit;
    }

    $account_id = $parts[0];
    $location_id = $parts[1];

    // Sauvegarder en base de données
    update_option('wgmbr_account_id', $account_id);
    update_option('wgmbr_location_id', $location_id);

    // Vider le cache des avis pour forcer le rafraîchissement
    delete_transient('wgmbr_reviews_cache');

    wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=location_saved'));
    exit;
}
add_action('admin_post_wgmbr_save_location', 'wgmbr_save_location');

/**
 * Révoque l'accès OAuth
 */
function wgmbr_revoke_access() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions.', 'wolves-avis-google'));
    }

    check_admin_referer('wgmbr_revoke_action');

    delete_option('wgmbr_access_token');
    delete_option('wgmbr_refresh_token');
    delete_option('wgmbr_token_expires');
    delete_transient('wgmbr_reviews_cache');

    wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=revoked'));
    exit;
}
add_action('admin_post_wgmbr_revoke', 'wgmbr_revoke_access');

/**
 * Action pour sauvegarder la personnalisation
 */
function wgmbr_save_customization() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions.', 'wolves-avis-google'));
    }

    check_admin_referer('wgmbr_save_customization', 'wgmbr_customization_nonce');

    // Sauvegarder l'affichage du résumé (checkbox)
    $show_summary = isset($_POST['wgmbr_show_summary']) ? '1' : '0';
    update_option('wgmbr_show_summary', $show_summary);

    // Sauvegarder la personnalisation
    if (isset($_POST['wgmbr_resume_text_color'])) {
        update_option('wgmbr_resume_text_color', sanitize_hex_color($_POST['wgmbr_resume_text_color']));
    }
    if (isset($_POST['wgmbr_card_bg_color'])) {
        update_option('wgmbr_card_bg_color', sanitize_hex_color($_POST['wgmbr_card_bg_color']));
    }
    if (isset($_POST['wgmbr_card_border_radius'])) {
        update_option('wgmbr_card_border_radius', absint($_POST['wgmbr_card_border_radius']));
    }
    if (isset($_POST['wgmbr_star_color'])) {
        update_option('wgmbr_star_color', sanitize_hex_color($_POST['wgmbr_star_color']));
    }
    if (isset($_POST['wgmbr_text_color'])) {
        update_option('wgmbr_text_color', sanitize_hex_color($_POST['wgmbr_text_color']));
    }
    if (isset($_POST['gmb-accent-color'])) {
        update_option('gmb-accent-color', sanitize_hex_color($_POST['gmb-accent-color']));
    }
    if (isset($_POST['wgmbr_text_color_name'])) {
        update_option('wgmbr_text_color_name', sanitize_hex_color($_POST['wgmbr_text_color_name']));
    }

    wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=customization_saved') . '#customization');
    exit;
}
add_action('admin_post_wgmbr_save_customization', 'wgmbr_save_customization');

/**
 * Action AJAX pour réinitialiser la personnalisation
 */
function wgmbr_reset_customization_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'wolves-avis-google')));
        return;
    }

    // Supprimer toutes les options de personnalisation
    delete_option('wgmbr_show_summary');
    delete_option('wgmbr_resume_text_color');
    delete_option('wgmbr_card_bg_color');
    delete_option('wgmbr_card_border_radius');
    delete_option('wgmbr_star_color');
    delete_option('wgmbr_text_color');
    delete_option('wgmbr_text_color_name');
    delete_option('wgmbr_modal_bg_color');
    delete_option('gmb-accent-color');

    wp_send_json_success();
}
add_action('wp_ajax_wgmbr_reset_customization', 'wgmbr_reset_customization_ajax');

/**
 * Action AJAX pour synchroniser les avis depuis l'API vers les CPT
 */
function wgmbr_sync_reviews_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'wolves-avis-google')));
        return;
    }

    // Vider le cache pour forcer la récupération depuis l'API
    delete_transient('wgmbr_reviews_cache');

    // Récupérer les avis depuis l'API
    $data = wgmbr_fetch_reviews();

    if (isset($data['error']) && $data['error']) {
        wp_send_json_error(array(
            'message' => $data['message'],
            'details' => isset($data['api_response']) ? $data['api_response'] : null
        ));
        return;
    }

    // Compter les avis synchronisés
    $synced_count = count($data['reviews']);

    wp_send_json_success(array(
        'message' => sprintf(
            __('%d reviews successfully synchronized', 'wolves-avis-google'),
            $synced_count
        ),
        'count' => $synced_count
    ));
}
add_action('wp_ajax_wgmbr_sync_reviews', 'wgmbr_sync_reviews_ajax');

/**
 * Action AJAX pour créer une nouvelle catégorie
 */
function wgmbr_create_category_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'wolves-avis-google')));
        return;
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wgmbr_categories')) {
        wp_send_json_error(array('message' => __('Security check failed', 'wolves-avis-google')));
        return;
    }

    // Vérifier le nom de catégorie
    if (!isset($_POST['category_name']) || empty(trim($_POST['category_name']))) {
        wp_send_json_error(array('message' => __('Category name is required', 'wolves-avis-google')));
        return;
    }

    $category_name = sanitize_text_field(wp_unslash($_POST['category_name']));

    // Créer la catégorie avec wp_insert_term
    $result = wp_insert_term(
        $category_name,
        'gmb_category',
        array(
            'slug' => sanitize_title($category_name)
        )
    );

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    wp_send_json_success(array(
        'message' => sprintf(__('Category "%s" created successfully', 'wolves-avis-google'), $category_name),
        'term_id' => $result['term_id']
    ));
}
add_action('wp_ajax_wgmbr_create_category', 'wgmbr_create_category_ajax');

/**
 * Action AJAX pour supprimer une catégorie
 */
function wgmbr_delete_category_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'wolves-avis-google')));
        return;
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wgmbr_categories')) {
        wp_send_json_error(array('message' => __('Security check failed', 'wolves-avis-google')));
        return;
    }

    // Vérifier l'ID de catégorie
    if (!isset($_POST['category_id']) || empty($_POST['category_id'])) {
        wp_send_json_error(array('message' => __('Category ID is required', 'wolves-avis-google')));
        return;
    }

    $category_id = absint($_POST['category_id']);

    // Supprimer la catégorie avec wp_delete_term
    $result = wp_delete_term($category_id, 'gmb_category');

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    if ($result === false) {
        wp_send_json_error(array('message' => __('Category not found', 'wolves-avis-google')));
        return;
    }

    wp_send_json_success(array(
        'message' => __('Category deleted successfully', 'wolves-avis-google')
    ));
}
add_action('wp_ajax_wgmbr_delete_category', 'wgmbr_delete_category_ajax');

/**
 * Action AJAX pour sauvegarder un avis
 */
function wgmbr_save_review_ajax() {
    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'wolves-avis-google')));
        return;
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wgmbr_save_review_job')) {
        wp_send_json_error(array('message' => __('Security check failed', 'wolves-avis-google')));
        return;
    }

    // Vérifier les données requises
    if (!isset($_POST['post_id'])) {
        wp_send_json_error(array('message' => __('Post ID is required', 'wolves-avis-google')));
        return;
    }

    $post_id = absint($_POST['post_id']);
    $job = isset($_POST['job']) ? sanitize_text_field(wp_unslash($_POST['job'])) : '';

    // Récupérer les catégories sélectionnées (tableau)
    $category_ids = isset($_POST['category_ids']) && is_array($_POST['category_ids'])
        ? array_map('absint', $_POST['category_ids'])
        : array();

    // Mettre à jour le job
    $job_updated = wgmbr_update_review_job($post_id, $job);

    // Mettre à jour les catégories
    $cats_updated = wgmbr_set_review_categories($post_id, $category_ids);

    // Vérifier si les mises à jour ont réussi
    if (is_wp_error($cats_updated)) {
        wp_send_json_error(array('message' => $cats_updated->get_error_message()));
        return;
    }

    wp_send_json_success(array(
        'message' => __('Review updated successfully', 'wolves-avis-google')
    ));
}
add_action('wp_ajax_wgmbr_save_review', 'wgmbr_save_review_ajax');
