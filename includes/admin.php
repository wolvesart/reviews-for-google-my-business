<?php
/**
 * Google My Business Reviews - Interface Admin
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// MENU ADMIN
// ============================================================================

function wgmbr_add_admin_menu()
{

    add_menu_page(
        __('Google Reviews', 'google-my-business-reviews'),
        __('Google Reviews', 'google-my-business-reviews'),
        'manage_options',
        'gmb-manage-reviews',
        'wgmbr_manage_reviews_page',
        'dashicons-star-filled',
        30
    );

    add_submenu_page(
        'gmb-manage-reviews',
        __('Categories', 'google-my-business-reviews'),
        __('Categories', 'google-my-business-reviews'),
        'manage_options',
        'gmb-categories',
        'wgmbr_categories_page'
    );

    add_submenu_page(
        'gmb-manage-reviews',
        __('Configuration', 'google-my-business-reviews'),
        __('Configuration', 'google-my-business-reviews'),
        'manage_options',
        'gmb-settings',
        'wgmbr_settings_page'
    );
}

add_action('admin_menu', 'wgmbr_add_admin_menu');

/**
 * Save styles and admin scripts
 */
function wgmbr_enqueue_admin_assets($hook)
{
    if ('toplevel_page_gmb-manage-reviews' !== $hook && 'google-reviews_page_gmb-settings' !== $hook && 'google-reviews_page_gmb-categories' !== $hook) {
        return;
    }

    wp_enqueue_style(
        'gmb-admin-styles',
        WOLVES_GMB_PLUGIN_URL . 'assets/css/admin.css',
        array(),
        WOLVES_GMB_VERSION
    );

    $custom_css = '.gmb-header { background-image: url("' . WOLVES_GMB_PLUGIN_URL . 'assets/images/gmb-pattern.png") !important; }';
    wp_add_inline_style('gmb-admin-styles', $custom_css);

    wp_enqueue_script(
        'gmb-admin-scripts',
        WOLVES_GMB_PLUGIN_URL . 'assets/js/admin.js',
        array(),
        WOLVES_GMB_VERSION,
        true
    );

    wp_localize_script('gmb-admin-scripts', 'wgmbrAdmin', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'settingsUrl' => admin_url('admin.php?page=gmb-settings'),
        'i18n' => array(
            'loading' => __('Loading...', 'google-my-business-reviews'),
            'errorFetchingLocations' => __('Error fetching locations:', 'google-my-business-reviews'),
            'unknownError' => __('Unknown error', 'google-my-business-reviews'),
            'networkError' => __('Network error:', 'google-my-business-reviews'),
            'clearingCache' => __('Clearing cache...', 'google-my-business-reviews'),
            'cacheCleared' => __('Cache cleared successfully!', 'google-my-business-reviews'),
            'errorClearingCache' => __('Error clearing cache', 'google-my-business-reviews'),
            'connectionSuccessful' => __('Connection successful!', 'google-my-business-reviews'),
            'reviewsFetched' => __('reviews fetched.', 'google-my-business-reviews'),
            'error' => __('Error:', 'google-my-business-reviews'),
            'confirmReset' => __('Are you sure you want to reset customization to default values?', 'google-my-business-reviews'),
            'errorResetting' => __('Error resetting customization:', 'google-my-business-reviews'),
            'copied' => __('Copied!', 'google-my-business-reviews'),
        )
    ));
}

add_action('admin_enqueue_scripts', 'wgmbr_enqueue_admin_assets');

// ============================================================================
// CONFIGURATION PAGE
// ============================================================================

function wgmbr_settings_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'google-my-business-reviews'));
    }

    $has_token = get_option('wgmbr_access_token') ? true : false;
    $available_locations = get_option('wgmbr_available_locations', array());
    $current_account_id = get_option('wgmbr_account_id');
    $current_location_id = get_option('wgmbr_location_id');
    $has_credentials = GMB_CLIENT_ID && GMB_CLIENT_SECRET;

    require_once WOLVES_GMB_PLUGIN_DIR . 'templates/admin-page.php';
}

function wgmbr_manage_reviews_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'google-my-business-reviews'));
    }

    $posts_per_page = 20;

    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;

    $result = wgmbr_get_all_reviews_with_query(array(
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
    ));

    $reviews = $result['reviews'];
    $query = $result['query'];

    $categories = get_terms(array(
        'taxonomy' => 'gmb_category',
        'hide_empty' => false,
    ));

    $data = array(
        'error' => false,
        'reviews' => $reviews,
        'total' => $query->found_posts,
        'query' => $query,
        'paged' => $paged,
        'posts_per_page' => $posts_per_page,
    );

    if (empty($reviews)) {
        $has_token = get_option('wgmbr_access_token');
        if (!$has_token) {
            $data['error'] = true;
            $data['message'] = __('API not authenticated. Please configure OAuth from the Configuration page.', 'google-my-business-reviews');
        }
    }

    require_once WOLVES_GMB_PLUGIN_DIR . 'templates/manage-reviews-page.php';
}

/**
 * Categories page
 */
function wgmbr_categories_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'google-my-business-reviews'));
    }

    require_once WOLVES_GMB_PLUGIN_DIR . 'templates/categories-page.php';
}

// ============================================================================
// ACTIONS AJAX
// ============================================================================

/**
 * Refresh locations
 */
function wgmbr_refresh_locations_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'google-my-business-reviews')));
        return;
    }

    wgmbr_auto_fetch_accounts_and_locations();
    wp_send_json_success();
}

add_action('wp_ajax_wgmbr_refresh_locations', 'wgmbr_refresh_locations_ajax');

/**
 * Clear cache
 */
function wgmbr_clear_cache_ajax()
{
    delete_transient('wgmbr_reviews_cache');
    wp_send_json_success();
}

add_action('wp_ajax_wgmbr_clear_cache', 'wgmbr_clear_cache_ajax');

/**
 * Test connection
 */
function wgmbr_test_connection_ajax()
{
    $data = wgmbr_fetch_reviews();

    if (isset($data['error']) && $data['error']) {
        $error_data = array(
            'message' => $data['message']
        );

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
 * Save credentials
 */
function wgmbr_save_credentials()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions.', 'google-my-business-reviews'));
    }

    check_admin_referer('wgmbr_save_credentials', 'wgmbr_credentials_nonce');

    if (!isset($_POST['wgmbr_client_id']) || !isset($_POST['wgmbr_client_secret']) || !isset($_POST['wgmbr_redirect_uri'])) {
        wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=error'));
        exit;
    }

    $client_id = sanitize_text_field(wp_unslash($_POST['wgmbr_client_id']));
    $client_secret = sanitize_text_field(wp_unslash($_POST['wgmbr_client_secret']));
    $redirect_uri = esc_url_raw(wp_unslash($_POST['wgmbr_redirect_uri']));

    update_option('wgmbr_client_id', $client_id);
    update_option('wgmbr_client_secret', $client_secret);
    update_option('wgmbr_redirect_uri', $redirect_uri);

    wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=credentials_saved'));
    exit;
}

add_action('admin_post_wgmbr_save_credentials', 'wgmbr_save_credentials');

/**
 * Save location
 */
function wgmbr_save_location()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions.', 'google-my-business-reviews'));
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

    update_option('wgmbr_account_id', $account_id);
    update_option('wgmbr_location_id', $location_id);

    delete_transient('wgmbr_reviews_cache');

    wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=location_saved'));
    exit;
}

add_action('admin_post_wgmbr_save_location', 'wgmbr_save_location');

/**
 * Revoke OAuth access
 */
function wgmbr_revoke_access()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions.', 'google-my-business-reviews'));
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
 * Save customization
 */
function wgmbr_save_customization()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions.', 'google-my-business-reviews'));
    }

    check_admin_referer('wgmbr_save_customization', 'wgmbr_customization_nonce');


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
 * Save customization (AJAX version)
 */
function wgmbr_save_customization_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'google-my-business-reviews')));
        return;
    }

    check_ajax_referer('wgmbr_save_customization', 'wgmbr_customization_nonce');

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

    wp_send_json_success(array('message' => __('Customization saved successfully', 'google-my-business-reviews')));
}

add_action('wp_ajax_wgmbr_save_customization', 'wgmbr_save_customization_ajax');

/**
 * Reset customisation
 */
function wgmbr_reset_customization_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'google-my-business-reviews')));
        return;
    }

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
 * Synchronized reviews from API to CPT
 */
function wgmbr_sync_reviews_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'google-my-business-reviews')));
        return;
    }

    // Delete cache
    delete_transient('wgmbr_reviews_cache');

    // Retrieve reviews from the API
    $data = wgmbr_fetch_reviews();

    if (isset($data['error']) && $data['error']) {
        wp_send_json_error(array(
            'message' => $data['message'],
            'details' => isset($data['api_response']) ? $data['api_response'] : null
        ));
        return;
    }

    // Count synchronized reviews
    $synced_count = count($data['reviews']);

    wp_send_json_success(array(
        'message' => sprintf(
        /* translators: 1: Display numbers of reviews successfully synchronized */
            __('%d reviews successfully synchronized', 'google-my-business-reviews'),
            $synced_count
        ),
        'count' => $synced_count
    ));
}

add_action('wp_ajax_wgmbr_sync_reviews', 'wgmbr_sync_reviews_ajax');

/**
 * Create new category
 */
function wgmbr_create_category_ajax()
{
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'google-my-business-reviews')));
        return;
    }

    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wgmbr_categories')) {
        wp_send_json_error(array('message' => __('Security check failed', 'google-my-business-reviews')));
        return;
    }

    // Check category name
    if (!isset($_POST['category_name']) || empty(trim($_POST['category_name']))) {
        wp_send_json_error(array('message' => __('Category name is required', 'google-my-business-reviews')));
        return;
    }

    $category_name = sanitize_text_field(wp_unslash($_POST['category_name']));

    // Create category with wp_insert_term
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
        'message' => sprintf(__('Category "%s" created successfully', 'google-my-business-reviews'), $category_name),
        'term_id' => $result['term_id']
    ));
}

add_action('wp_ajax_wgmbr_create_category', 'wgmbr_create_category_ajax');

/**
 * Delete category
 */
function wgmbr_delete_category_ajax()
{
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'google-my-business-reviews')));
        return;
    }

    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wgmbr_categories')) {
        wp_send_json_error(array('message' => __('Security check failed', 'google-my-business-reviews')));
        return;
    }

    // Check category ID
    if (!isset($_POST['category_id']) || empty($_POST['category_id'])) {
        wp_send_json_error(array('message' => __('Category ID is required', 'google-my-business-reviews')));
        return;
    }

    $category_id = absint($_POST['category_id']);

    // Delete category with wp_delete_term
    $result = wp_delete_term($category_id, 'gmb_category');

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    if ($result === false) {
        wp_send_json_error(array('message' => __('Category not found', 'google-my-business-reviews')));
        return;
    }

    wp_send_json_success(array(
        'message' => __('Category deleted successfully', 'google-my-business-reviews')
    ));
}

add_action('wp_ajax_wgmbr_delete_category', 'wgmbr_delete_category_ajax');

/**
 * Save review
 */
function wgmbr_save_review_ajax()
{
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Insufficient permissions', 'google-my-business-reviews')));
        return;
    }

    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wgmbr_save_review_job')) {
        wp_send_json_error(array('message' => __('Security check failed', 'google-my-business-reviews')));
        return;
    }

    // Check the required data
    if (!isset($_POST['post_id'])) {
        wp_send_json_error(array('message' => __('Post ID is required', 'google-my-business-reviews')));
        return;
    }

    $post_id = absint($_POST['post_id']);
    $job = isset($_POST['job']) ? sanitize_text_field(wp_unslash($_POST['job'])) : '';

    // Retrieve selected categories (table)
    $category_ids = isset($_POST['category_ids']) && is_array($_POST['category_ids'])
        ? array_map('absint', $_POST['category_ids'])
        : array();

    // Update job
    $job_updated = wgmbr_update_review_job($post_id, $job);

    // Update categories
    $cats_updated = wgmbr_set_review_categories($post_id, $category_ids);

    // Check if the updates were successful
    if (is_wp_error($cats_updated)) {
        wp_send_json_error(array('message' => $cats_updated->get_error_message()));
        return;
    }

    wp_send_json_success(array(
        'message' => __('Review updated successfully', 'google-my-business-reviews')
    ));
}

add_action('wp_ajax_wgmbr_save_review', 'wgmbr_save_review_ajax');
