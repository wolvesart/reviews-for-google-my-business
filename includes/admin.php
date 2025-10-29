<?php
/**
 * Reviews for Google My Business - Interface Admin
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
        esc_html__('Google Reviews', 'reviews-for-google-my-business'),
        esc_html__('Google Reviews', 'reviews-for-google-my-business'),
        'manage_options',
        'gmb-manage-reviews',
        'wgmbr_manage_reviews_page',
        'dashicons-star-filled',
        30
    );

    add_submenu_page(
        'gmb-manage-reviews',
        esc_html__('Categories', 'reviews-for-google-my-business'),
        esc_html__('Categories', 'reviews-for-google-my-business'),
        'manage_options',
        'gmb-categories',
        'wgmbr_categories_page'
    );

    add_submenu_page(
        'gmb-manage-reviews',
        esc_html__('Configuration', 'reviews-for-google-my-business'),
        esc_html__('Configuration', 'reviews-for-google-my-business'),
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
    if (!in_array($hook, array(WGMBR_MANAGE_PAGE_HOOK, WGMBR_SETTINGS_PAGE_HOOK, WGMBR_CATEGORIES_PAGE_HOOK), true)) {
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
        'nonce' => wp_create_nonce('wgmbr_admin_actions'),
        'i18n' => array(
            'loading' => esc_html__('Loading...', 'reviews-for-google-my-business'),
            'errorFetchingLocations' => esc_html__('Error fetching locations:', 'reviews-for-google-my-business'),
            'unknownError' => esc_html__('Unknown error', 'reviews-for-google-my-business'),
            'networkError' => esc_html__('Network error:', 'reviews-for-google-my-business'),
            'resetting' => esc_html__('Resetting...', 'reviews-for-google-my-business'),
            'resetSuccess' => esc_html__('Reset successful!', 'reviews-for-google-my-business'),
            'errorResettingReviews' => esc_html__('Error resetting reviews', 'reviews-for-google-my-business'),
            'connectionSuccessful' => esc_html__('Connection successful!', 'reviews-for-google-my-business'),
            'reviewsFetched' => esc_html__('reviews fetched.', 'reviews-for-google-my-business'),
            'error' => esc_html__('Error:', 'reviews-for-google-my-business'),
            'confirmReset' => esc_html__('Are you sure you want to reset customization to default values?', 'reviews-for-google-my-business'),
            'errorResetting' => esc_html__('Error resetting customization:', 'reviews-for-google-my-business'),
            'copied' => esc_html__('Copied!', 'reviews-for-google-my-business'),
        )
    ));

    // Script pour la gestion des avis et catégories
    if (in_array($hook, array(WGMBR_MANAGE_PAGE_HOOK, WGMBR_CATEGORIES_PAGE_HOOK), true)) {
        wp_enqueue_script(
            'gmb-manage-reviews-scripts',
            WOLVES_GMB_PLUGIN_URL . 'assets/js/manage-reviews.js',
            array(),
            WOLVES_GMB_VERSION,
            true
        );

        wp_localize_script('gmb-manage-reviews-scripts', 'wgmbrManage', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wgmbr_categories'),
            'i18n' => array(
                'enterCategoryName' => esc_html__('Please enter a category name', 'reviews-for-google-my-business'),
                'creating' => esc_html__('Creating...', 'reviews-for-google-my-business'),
                'error' => esc_html__('Error:', 'reviews-for-google-my-business'),
                'unknownError' => esc_html__('Unknown error', 'reviews-for-google-my-business'),
                'networkError' => esc_html__('Network error:', 'reviews-for-google-my-business'),
                'createCategory' => esc_html__('Create category', 'reviews-for-google-my-business'),
                'confirmDeleteCategory' => esc_html__('Are you sure you want to delete this category? It will be removed from all reviews that use it.', 'reviews-for-google-my-business'),
                'deleting' => esc_html__('Deleting...', 'reviews-for-google-my-business'),
                'delete' => esc_html__('Delete', 'reviews-for-google-my-business'),
            )
        ));
    }
}

add_action('admin_enqueue_scripts', 'wgmbr_enqueue_admin_assets');

// ============================================================================
// CONFIGURATION PAGE
// ============================================================================

function wgmbr_settings_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'reviews-for-google-my-business'));
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
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'reviews-for-google-my-business'));
    }

    $posts_per_page = WGMBR_ADMIN_REVIEWS_PER_PAGE;

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Pagination parameter, nonce not required
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
            $data['message'] = esc_html__('API not authenticated. Please configure OAuth from the Configuration page.', 'reviews-for-google-my-business');
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
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'reviews-for-google-my-business'));
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
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'reviews-for-google-my-business')));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wgmbr_admin_actions')) {
        wp_send_json_error(array('message' => esc_html__('Security check failed', 'reviews-for-google-my-business')));
        return;
    }

    wgmbr_auto_fetch_accounts_and_locations();
    wp_send_json_success();
}

add_action('wp_ajax_wgmbr_refresh_locations', 'wgmbr_refresh_locations_ajax');

/**
 * Reset: Clear cache and delete all review CPTs
 */
function wgmbr_clear_cache_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'reviews-for-google-my-business')));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wgmbr_admin_actions')) {
        wp_send_json_error(array('message' => esc_html__('Security check failed', 'reviews-for-google-my-business')));
        return;
    }

    // Delete cache transient
    delete_transient('wgmbr_reviews_cache');
    delete_transient('wgmbr_avg_rating_cache');

    // Delete all review CPTs
    $args = array(
        'post_type' => 'gmb_review',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'fields' => 'ids'
    );

    $review_ids = get_posts($args);

    foreach ($review_ids as $review_id) {
        wp_delete_post($review_id, true); // true = force delete (skip trash)
    }

    wp_send_json_success(array(
        'message' => sprintf(
            /* translators: %d: number of reviews deleted */
            esc_html__('%d reviews deleted', 'reviews-for-google-my-business'),
            count($review_ids)
        ),
        'deleted_count' => count($review_ids)
    ));
}

add_action('wp_ajax_wgmbr_clear_cache', 'wgmbr_clear_cache_ajax');

/**
 * Test connection
 */
function wgmbr_test_connection_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'reviews-for-google-my-business')));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wgmbr_admin_actions')) {
        wp_send_json_error(array('message' => esc_html__('Security check failed', 'reviews-for-google-my-business')));
        return;
    }

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
        wp_die(esc_html__('You do not have sufficient permissions.', 'reviews-for-google-my-business'));
    }

    // Verify HTTPS for security (allow localhost without HTTPS)
    $is_localhost = (
        isset($_SERVER['REMOTE_ADDR']) &&
        in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'), true)
    ) || (
        isset($_SERVER['HTTP_HOST']) &&
        (strpos(sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])), 'localhost') !== false || strpos(sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])), '127.0.0.1') !== false)
    );

    if (!is_ssl() && !$is_localhost && !defined('WP_DEBUG')) {
        wp_die(
            esc_html__('HTTPS is required to save API credentials. Please enable SSL/TLS on your site.', 'reviews-for-google-my-business'),
            esc_html__('Security Error', 'reviews-for-google-my-business'),
            array('response' => 403)
        );
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

    // Only update secret if a new one is provided
    if (!empty($client_secret)) {
        // Encrypt the client secret before storing
        $encrypted_secret = wgmbr_encrypt($client_secret);
        update_option('wgmbr_client_secret', $encrypted_secret);
    }

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
        wp_die(esc_html__('You do not have sufficient permissions.', 'reviews-for-google-my-business'));
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
    delete_transient('wgmbr_avg_rating_cache');

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
        wp_die(esc_html__('You do not have sufficient permissions.', 'reviews-for-google-my-business'));
    }

    check_admin_referer('wgmbr_revoke_action');

    delete_option('wgmbr_access_token');
    delete_option('wgmbr_refresh_token');
    delete_option('wgmbr_token_expires');
    delete_transient('wgmbr_reviews_cache');
    delete_transient('wgmbr_avg_rating_cache');

    wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=revoked'));
    exit;
}

add_action('admin_post_wgmbr_revoke', 'wgmbr_revoke_access');

/**
 * Process customization options save (helper function)
 * Used by both POST and AJAX handlers to avoid code duplication
 *
 * Note: Nonce verification is performed by the calling functions:
 * - wgmbr_save_customization() uses check_admin_referer()
 * - wgmbr_save_customization_ajax() uses check_ajax_referer()
 */
function wgmbr_process_customization_save()
{
    $color_options = array(
        'wgmbr_color_card_bg' => 'sanitize_hex_color',
        'wgmbr_color_star' => 'sanitize_hex_color',
        'wgmbr_color_text_primary' => 'sanitize_hex_color',
        'wgmbr_color_accent' => 'sanitize_hex_color',
        'wgmbr_color_text_resume' => 'sanitize_hex_color',
    );

    $int_options = array(
        'wgmbr_radius_card' => 'absint',
    );

    // Process color options
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by calling function
    foreach ($color_options as $key => $sanitizer) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified by calling function, sanitization done by $sanitizer callback
        if (isset($_POST[$key])) {
            update_option($key, $sanitizer(wp_unslash($_POST[$key])));
        }
    }

    // Process integer options
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by calling function
    foreach ($int_options as $key => $sanitizer) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified by calling function, sanitization done by $sanitizer callback
        if (isset($_POST[$key])) {
            update_option($key, $sanitizer(wp_unslash($_POST[$key])));
        }
    }
}

/**
 * Save customization
 */
function wgmbr_save_customization()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions.', 'reviews-for-google-my-business'));
    }

    check_admin_referer('wgmbr_save_customization', 'wgmbr_customization_nonce');

    wgmbr_process_customization_save();

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
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'reviews-for-google-my-business')));
        return;
    }

    check_ajax_referer('wgmbr_save_customization', 'wgmbr_customization_nonce');

    wgmbr_process_customization_save();

    wp_send_json_success(array('message' => esc_html__('Customization saved successfully', 'reviews-for-google-my-business')));
}

add_action('wp_ajax_wgmbr_save_customization', 'wgmbr_save_customization_ajax');

/**
 * Reset customisation
 */
function wgmbr_reset_customization_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'reviews-for-google-my-business')));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wgmbr_admin_actions')) {
        wp_send_json_error(array('message' => esc_html__('Security check failed', 'reviews-for-google-my-business')));
        return;
    }

    delete_option('wgmbr_color_card_bg');
    delete_option('wgmbr_radius_card');
    delete_option('wgmbr_color_star');
    delete_option('wgmbr_color_text_primary');
    delete_option('wgmbr_color_text_resume');
    delete_option('wgmbr_color_accent');

    wp_send_json_success();
}

add_action('wp_ajax_wgmbr_reset_customization', 'wgmbr_reset_customization_ajax');

/**
 * Synchronized reviews from API to CPT
 */
function wgmbr_sync_reviews_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'reviews-for-google-my-business')));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wgmbr_admin_actions')) {
        wp_send_json_error(array('message' => esc_html__('Security check failed', 'reviews-for-google-my-business')));
        return;
    }

    // Rate limiting: Check if synced recently (prevents API abuse)
    $last_sync = get_transient('wgmbr_last_sync_time');
    if ($last_sync !== false) {
        $time_since_sync = time() - $last_sync;
        $cooldown = 60; // 60 seconds cooldown

        if ($time_since_sync < $cooldown) {
            $retry_after = $cooldown - $time_since_sync;
            wp_send_json_error(array(
                /* translators: %d: number of seconds to wait */
                'message' => sprintf(esc_html__('Please wait %d seconds before syncing again', 'reviews-for-google-my-business'), $retry_after),
                'retry_after' => $retry_after
            ));
            return;
        }
    }

    // Set rate limit timestamp
    set_transient('wgmbr_last_sync_time', time(), 120); // 2 minutes expiry

    // Delete cache
    delete_transient('wgmbr_reviews_cache');
    delete_transient('wgmbr_avg_rating_cache');

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
            esc_html__('%d reviews successfully synchronized', 'reviews-for-google-my-business'),
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
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'reviews-for-google-my-business')));
        return;
    }

    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wgmbr_categories')) {
        wp_send_json_error(array('message' => esc_html__('Security check failed', 'reviews-for-google-my-business')));
        return;
    }

    // Check category name
    if (!isset($_POST['category_name']) || empty(trim(sanitize_text_field(wp_unslash($_POST['category_name']))))) {
        wp_send_json_error(array('message' => esc_html__('Category name is required', 'reviews-for-google-my-business')));
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
        /* translators: %s: Category name */
        'message' => sprintf(esc_html__('Category "%s" created successfully', 'reviews-for-google-my-business'), $category_name),
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
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'reviews-for-google-my-business')));
        return;
    }

    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wgmbr_categories')) {
        wp_send_json_error(array('message' => esc_html__('Security check failed', 'reviews-for-google-my-business')));
        return;
    }

    // Check category ID
    if (!isset($_POST['category_id']) || empty(absint(wp_unslash($_POST['category_id'])))) {
        wp_send_json_error(array('message' => esc_html__('Category ID is required', 'reviews-for-google-my-business')));
        return;
    }

    $category_id = absint(wp_unslash($_POST['category_id']));

    // Delete category with wp_delete_term
    $result = wp_delete_term($category_id, 'gmb_category');

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
        return;
    }

    if ($result === false) {
        wp_send_json_error(array('message' =>esc_html__('Category not found', 'reviews-for-google-my-business')));
        return;
    }

    wp_send_json_success(array(
        'message' => esc_html__('Category deleted successfully', 'reviews-for-google-my-business')
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
        wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'reviews-for-google-my-business')));
        return;
    }

    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wgmbr_save_review_job')) {
        wp_send_json_error(array('message' => esc_html__('Security check failed', 'reviews-for-google-my-business')));
        return;
    }

    // Check the required data
    if (!isset($_POST['post_id'])) {
        wp_send_json_error(array('message' => esc_html__('Post ID is required', 'reviews-for-google-my-business')));
        return;
    }

    $post_id = absint(wp_unslash($_POST['post_id']));
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
        'message' => esc_html__('Review updated successfully', 'reviews-for-google-my-business')
    ));
}

add_action('wp_ajax_wgmbr_save_review', 'wgmbr_save_review_ajax');
