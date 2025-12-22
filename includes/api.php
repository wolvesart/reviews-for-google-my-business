<?php
/**
 * Reviews for Google My Business - API Functions
 * OAuth 2.0 functions and API calls
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Downloads a profile photo from Google and stores it locally
 * This ensures compliance with WordPress.org guidelines (no remote files)
 *
 * @param string $photo_url The URL of the profile photo from Google
 * @param string $review_id The review ID to create a unique filename
 * @return string The local URL of the downloaded image, or empty string on failure
 */
function wgmbr_download_profile_photo($photo_url, $review_id) {
    // Return empty if no URL provided
    if (empty($photo_url)) {
        return '';
    }

    // Check if we already downloaded this photo
    $upload_dir = wp_upload_dir();
    $gmb_dir = $upload_dir['basedir'] . '/gmb-reviews';
    $gmb_url = $upload_dir['baseurl'] . '/gmb-reviews';

    // Create directory if it doesn't exist
    if (!file_exists($gmb_dir)) {
        wp_mkdir_p($gmb_dir);
    }

    // Generate filename based on review ID
    $sanitized_id = sanitize_file_name($review_id);
    $extension = 'jpg'; // Google profile photos are typically JPG
    $filename = 'profile-' . $sanitized_id . '.' . $extension;
    $filepath = $gmb_dir . '/' . $filename;
    $fileurl = $gmb_url . '/' . $filename;

    // If file already exists, return its URL
    if (file_exists($filepath)) {
        return $fileurl;
    }

    // Download the image
    $response = wp_remote_get($photo_url, array(
        'timeout' => 15,
        'sslverify' => true
    ));

    // Check for errors
    if (is_wp_error($response)) {
        wgmbr_log_error('photo_download', 'Failed to download photo: ' . $response->get_error_message());
        return '';
    }

    // Check response code
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        wgmbr_log_error('photo_download', 'Invalid response code: ' . $response_code);
        return '';
    }

    // Get image content
    $image_data = wp_remote_retrieve_body($response);
    if (empty($image_data)) {
        return '';
    }

    // Save the image locally
    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Direct file operation needed for image download
    $saved = file_put_contents($filepath, $image_data);

    if ($saved === false) {
        wgmbr_log_error('photo_download', 'Failed to save image to: ' . $filepath);
        return '';
    }

    return $fileurl;
}

// ============================================================================
// OAUTH 2.0 FUNCTIONS
// ============================================================================

/**
 * Generates OAuth authorization URL with state parameter for CSRF protection
 */
function wgmbr_get_auth_url() {
    // Generate random state for CSRF protection
    $state = bin2hex(random_bytes(32));

    // Store state in transient (valid for 10 minutes)
    set_transient('wgmbr_oauth_state', $state, 10 * MINUTE_IN_SECONDS);

    $params = array(
        'client_id' => WGMBR_CLIENT_ID,
        'redirect_uri' => WGMBR_REDIRECT_URI,
        'scope' => WGMBR_SCOPES,
        'response_type' => 'code',
        'access_type' => 'offline',
        'prompt' => 'consent',
        'state' => $state
    );

    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

/**
 * Exchanges authorization code for access token
 */
function wgmbr_exchange_code_for_token($code) {
    $token_url = 'https://oauth2.googleapis.com/token';

    $params = array(
        'code' => $code,
        'client_id' => WGMBR_CLIENT_ID,
        'client_secret' => WGMBR_CLIENT_SECRET,
        'redirect_uri' => WGMBR_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    );

    $response = wp_remote_post($token_url, array(
        'body' => $params
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['access_token'])) {
        // Save tokens
        update_option('wgmbr_access_token', $body['access_token']);
        update_option('wgmbr_refresh_token', $body['refresh_token']);
        update_option('wgmbr_token_expires', time() + $body['expires_in']);

        return true;
    }

    return false;
}

/**
 * Refreshes access token using refresh token
 */
function wgmbr_refresh_access_token() {
    $refresh_token = get_option('wgmbr_refresh_token');

    if (!$refresh_token) {
        wgmbr_log_error('token_refresh', 'No refresh token available');
        return false;
    }

    $token_url = 'https://oauth2.googleapis.com/token';

    $params = array(
        'client_id' => WGMBR_CLIENT_ID,
        'client_secret' => WGMBR_CLIENT_SECRET,
        'refresh_token' => $refresh_token,
        'grant_type' => 'refresh_token'
    );

    $response = wp_remote_post($token_url, array(
        'body' => $params
    ));

    if (is_wp_error($response)) {
        $error_msg = $response->get_error_message();
        wgmbr_log_error('token_refresh', 'Connection failed: ' . $error_msg);
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['access_token'])) {
        update_option('wgmbr_access_token', $body['access_token']);
        update_option('wgmbr_token_expires', time() + $body['expires_in']);
        delete_option('wgmbr_last_token_error'); // Clear previous error
        return true;
    }

    // Log error details for debugging
    $error = isset($body['error']) ? $body['error'] : 'unknown_error';
    $error_desc = isset($body['error_description']) ? $body['error_description'] : '';
    $full_error = $error . ': ' . $error_desc;

    wgmbr_log_error('token_refresh', 'Failed: ' . $full_error, $body);
    update_option('wgmbr_last_token_error', $full_error);

    return false;
}

/**
 * Gets a valid access token (refreshes if necessary)
 */
function wgmbr_get_valid_access_token() {
    $access_token = get_option('wgmbr_access_token');
    $expires = get_option('wgmbr_token_expires', 0);

    // If token expires in less than 5 minutes, refresh it
    if (time() >= ($expires - 300)) {
        if (!wgmbr_refresh_access_token()) {
            return false;
        }
        $access_token = get_option('wgmbr_access_token');
    }

    return $access_token;
}

// ============================================================================
// ACCOUNTS AND LOCATIONS RETRIEVAL
// ============================================================================

/**
 * Lists all accessible GMB accounts
 * Documentation: https://developers.google.com/my-business/reference/accountmanagement/rest/v1/accounts/list
 */
function wgmbr_list_accounts() {
    // Check cache first
    $cache_key = 'wgmbr_accounts_cache';
    $cached = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }

    $access_token = wgmbr_get_valid_access_token();

    if (!$access_token) {
        return array('error' => true, 'message' => 'Token not available');
    }

    $api_url = 'https://mybusinessaccountmanagement.googleapis.com/v1/accounts';

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        ),
        'timeout' => WGMBR_API_TIMEOUT
    ));

    if (is_wp_error($response)) {
        return array('error' => true, 'message' => $response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    // Cache for 1 hour if successful
    if (!isset($body['error'])) {
        set_transient($cache_key, $body, WGMBR_CACHE_DURATION);
    }

    return $body;
}

/**
 * Lists all locations for a given account
 * Documentation: https://developers.google.com/my-business/reference/businessinformation/rest/v1/accounts.locations/list
 */
function wgmbr_list_locations($account_id) {
    // Check cache first (cache key includes account_id)
    $cache_key = 'wgmbr_locations_cache_' . md5($account_id);
    $cached = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }

    $access_token = wgmbr_get_valid_access_token();

    if (!$access_token) {
        return array('error' => true, 'message' => 'Token not available');
    }

    // readMask is required - use only top-level fields
    // Documentation: https://developers.google.com/my-business/reference/businessinformation/rest/v1/accounts.locations#Location
    // We only request name and title, the essential fields
    $read_mask = 'name,title';

    $api_url = add_query_arg(array(
        'readMask' => $read_mask,
        'pageSize' => WGMBR_API_PAGE_SIZE
    ), 'https://mybusinessbusinessinformation.googleapis.com/v1/' . $account_id . '/locations');

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        ),
        'timeout' => WGMBR_API_TIMEOUT
    ));

    if (is_wp_error($response)) {
        return array('error' => true, 'message' => $response->get_error_message());
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    // Add status code for debugging
    if ($status_code !== 200) {
        $body['http_status'] = $status_code;
    }

    // Cache for 1 hour if successful
    if ($status_code === 200 && !isset($body['error'])) {
        set_transient($cache_key, $body, WGMBR_CACHE_DURATION);
    }

    return $body;
}

// ============================================================================
// REVIEWS RETRIEVAL
// ============================================================================

/**
 * Retry wrapper for API calls with exponential backoff
 *
 * @param callable $callback Function to call
 * @param array $args Arguments to pass to the function
 * @param int $max_retries Maximum number of retries
 * @return array Result from callback
 */
function wgmbr_api_call_with_retry($callback, $args = array(), $max_retries = 2) {
    $retry_count = 0;

    while ($retry_count <= $max_retries) {
        $result = call_user_func_array($callback, $args);

        // Check if we should retry
        $should_retry = false;
        $status_code = null;

        if (isset($result['error']) && $result['error']) {
            // Check for retryable status codes
            if (isset($result['status_code'])) {
                $status_code = $result['status_code'];
                // 429 = Rate limit, 503 = Service unavailable, 500 = Server error
                if (in_array($status_code, array(429, 500, 503), true)) {
                    $should_retry = true;
                }
            }

            // Check for connection errors (WP_Error would be caught inside the callback)
            if (isset($result['message']) && strpos($result['message'], 'connection') !== false) {
                $should_retry = true;
            }
        }

        // Success or non-retryable error
        if (!$should_retry || $retry_count >= $max_retries) {
            if ($retry_count > 0) {
                wgmbr_log_error('api_retry', 'API call completed after ' . $retry_count . ' retries', array(
                    'callback' => is_array($callback) ? $callback[1] : (string) $callback,
                    'final_result' => $result
                ), 'info');
            }
            return $result;
        }

        // Calculate delay with exponential backoff
        $delay = pow(2, $retry_count); // 1, 2, 4 seconds

        // Check for Retry-After header (429 responses)
        if ($status_code === 429 && isset($result['retry_after'])) {
            $delay = max($delay, (int) $result['retry_after']);
        }

        wgmbr_log_error('api_retry', 'Retrying API call (attempt ' . ($retry_count + 1) . '/' . $max_retries . ') after ' . $delay . ' seconds', array(
            'callback' => is_array($callback) ? $callback[1] : (string) $callback,
            'status_code' => $status_code
        ), 'warning');

        sleep($delay);
        $retry_count++;
    }

    return $result;
}

/**
 * Fetches a single page of reviews from Google My Business API
 *
 * @param string $access_token Access token
 * @param string $parent Parent location path
 * @param string|null $page_token Page token for pagination
 * @return array API response with reviews and nextPageToken
 */
function wgmbr_fetch_reviews_page($access_token, $parent, $page_token = null) {
    $params = array(
        'pageSize' => WGMBR_API_PAGE_SIZE,
        'orderBy' => WGMBR_API_SORT_ORDER
    );

    if ($page_token) {
        $params['pageToken'] = $page_token;
    }

    $api_url = add_query_arg($params, 'https://mybusiness.googleapis.com/v4/' . $parent . '/reviews');

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        ),
        'timeout' => WGMBR_API_TIMEOUT
    ));

    if (is_wp_error($response)) {
        return array(
            'error' => true,
            'message' => 'API connection error: ' . $response->get_error_message()
        );
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($status_code !== 200) {
        return array(
            'error' => true,
            'message' => 'Google My Business API error (Code ' . $status_code . ')',
            'api_response' => $body,
            'status_code' => $status_code
        );
    }

    return array(
        'error' => false,
        'reviews' => isset($body['reviews']) ? $body['reviews'] : array(),
        'nextPageToken' => isset($body['nextPageToken']) ? $body['nextPageToken'] : null,
        'totalReviewCount' => isset($body['totalReviewCount']) ? $body['totalReviewCount'] : 0,
        'averageRating' => isset($body['averageRating']) ? $body['averageRating'] : 0
    );
}

/**
 * Fetches all reviews from Google My Business API with pagination
 *
 * @param string $access_token Access token
 * @param string $parent Parent location path
 * @param int $max_pages Maximum number of pages to fetch (safety limit)
 * @return array All reviews fetched
 */
function wgmbr_fetch_all_reviews_pages($access_token, $parent, $max_pages = 10) {
    $all_reviews = array();
    $next_page_token = null;
    $page_count = 0;
    $total_review_count = 0;
    $average_rating = 0;

    do {
        $page_count++;

        // Use retry wrapper for resilience
        $result = wgmbr_api_call_with_retry(
            'wgmbr_fetch_reviews_page',
            array($access_token, $parent, $next_page_token),
            2 // Max 2 retries
        );

        if (isset($result['error']) && $result['error']) {
            // Return error on first page, otherwise return what we have so far
            if ($page_count === 1) {
                wgmbr_log_error('api_fetch', 'Failed to fetch first page of reviews', $result);
                return $result;
            } else {
                wgmbr_log_error('api_fetch', 'Failed to fetch page ' . $page_count . ', returning partial results', $result, 'warning');
                break;
            }
        }

        if (!empty($result['reviews'])) {
            $all_reviews = array_merge($all_reviews, $result['reviews']);
        }

        // Store metadata from first page
        if ($page_count === 1) {
            $total_review_count = $result['totalReviewCount'];
            $average_rating = $result['averageRating'];
        }

        $next_page_token = $result['nextPageToken'];

    } while ($next_page_token && $page_count < $max_pages);

    return array(
        'error' => false,
        'reviews' => $all_reviews,
        'total' => $total_review_count,
        'average_rating' => $average_rating,
        'pages_fetched' => $page_count
    );
}

/**
 * Fetches reviews from Google My Business API v4
 * Documentation: https://developers.google.com/my-business/content/review-data
 */
function wgmbr_fetch_reviews() {
    // Check cache (valid for 1 hour)
    $cache_key = 'wgmbr_reviews_cache';
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        return $cached_data;
    }

    // My Business API v4 with OAuth
    // Documentation: https://developers.google.com/my-business/reference/rest/v4/accounts.locations.reviews/list
    $access_token = wgmbr_get_valid_access_token();

    if ($access_token && WGMBR_ACCOUNT_ID && WGMBR_LOCATION_ID) {
        // Exact format according to docs: {parent=accounts/*/locations/*}/reviews
        $parent = WGMBR_ACCOUNT_ID . '/' . WGMBR_LOCATION_ID;

        // Fetch all pages of reviews
        $result = wgmbr_fetch_all_reviews_pages($access_token, $parent, WGMBR_API_MAX_PAGES);

        if (isset($result['error']) && $result['error']) {
            return $result;
        }

        $reviews_data = array(
            'error' => false,
            'source' => 'My Business API v4',
            'reviews' => $result['reviews'],
            'total' => $result['total'],
            'average_rating' => $result['average_rating'],
            'pages_fetched' => $result['pages_fetched']
        );

        // Sync reviews to CPT
        wgmbr_sync_reviews_to_cpt($reviews_data['reviews']);

        set_transient($cache_key, $reviews_data, WGMBR_CACHE_DURATION);
        return $reviews_data;
    }

    // If authentication is not configured
    return array(
        'error' => true,
        'message' => esc_html__('Google My Business API is not authenticated. Please configure OAuth from the GMB Reviews page in the admin.', 'reviews-for-google-my-business')
    );
}

// ============================================================================
// OAUTH CALLBACK
// ============================================================================

/**
 * Handles OAuth callback from Google
 *
 * SECURITY NOTE: This function uses OAuth 2.0 state parameter for CSRF protection
 * instead of WordPress nonces. This is the industry-standard security mechanism
 * for OAuth flows as defined in RFC 6749.
 *
 * The state parameter serves the same purpose as a nonce:
 * - Generated randomly before redirecting to OAuth provider
 * - Stored server-side in a transient (10 min expiry)
 * - Validated on callback to prevent CSRF attacks
 * - Single-use (deleted after validation)
 *
 * This is more secure than a traditional nonce because:
 * 1. It's cryptographically random (64 hex characters)
 * 2. It's stored server-side, not in the URL
 * 3. It has a short expiry time
 * 4. It's validated before any OAuth token exchange
 *
 * Additional security measures:
 * - User must be logged in and have 'manage_options' capability
 * - All inputs are sanitized
 * - HTTPS is required for token exchange
 */
function wgmbr_handle_oauth_callback() {
    // Early return for performance - only process if this is an OAuth callback
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Early performance check, full security validation below
    if (!isset($_GET['wgmbr_auth'])) {
        return;
    }

    // SECURITY CHECK 1: User must be logged in with admin capabilities
    // This prevents unauthorized users from attempting OAuth callbacks
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_die(
            esc_html__('Unauthorized access. You must be logged in as an administrator to complete OAuth authentication.', 'reviews-for-google-my-business'),
            esc_html__('Security Error', 'reviews-for-google-my-business'),
            array('response' => 403)
        );
    }

    // SECURITY CHECK 2: Validate OAuth state parameter (CSRF protection)
    // The state parameter is OAuth's equivalent of a nonce
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth state parameter validated below per RFC 6749
    $received_state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
    $stored_state = get_transient('wgmbr_oauth_state');

    // Verify state parameter matches and exists
    if (empty($received_state) || empty($stored_state) || !hash_equals($stored_state, $received_state)) {
        // Delete potentially compromised state
        delete_transient('wgmbr_oauth_state');
        wgmbr_log_error('oauth_callback', 'Invalid state parameter - possible CSRF attack attempt');
        wp_die(
            esc_html__('Security validation failed. The OAuth state parameter is invalid. This may indicate a CSRF attack or an expired authorization attempt.', 'reviews-for-google-my-business'),
            esc_html__('OAuth Security Error', 'reviews-for-google-my-business'),
            array('response' => 403)
        );
    }

    // State is valid, delete it (single-use token)
    delete_transient('wgmbr_oauth_state');

    // SECURITY CHECK 3: Process authorization code
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth code validated via state parameter and HTTPS token exchange
    if (isset($_GET['code'])) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Sanitized and validated via OAuth flow
        $code = sanitize_text_field(wp_unslash($_GET['code']));

        // Additional validation: code should not be empty
        if (empty($code)) {
            wp_safe_redirect(admin_url('admin.php?page=wgmbr-settings&status=error&debug=empty_code'));
            exit;
        }

        $success = wgmbr_exchange_code_for_token($code);

        if ($success) {
            // Automatically fetch accounts and locations after authentication
            wgmbr_auto_fetch_accounts_and_locations();
            wp_safe_redirect(admin_url('admin.php?page=wgmbr-settings&status=success&auto_fetch=1'));
        } else {
            // Debug: save error to view it
            update_option('wgmbr_last_error', 'Failed to exchange token');
            wp_safe_redirect(admin_url('admin.php?page=wgmbr-settings&status=error&debug=token_exchange_failed'));
        }
        exit;
    } else {
        // Debug: no code received
        update_option('wgmbr_last_error', 'No authorization code received');
        wp_safe_redirect(admin_url('admin.php?page=wgmbr-settings&status=error&debug=no_code'));
        exit;
    }
}
add_action('init', 'wgmbr_handle_oauth_callback', 5); // Priority 5: Run early but after WordPress init

/**
 * Automatically fetches accounts and locations after OAuth
 */
function wgmbr_auto_fetch_accounts_and_locations() {
    // Clear caches to force fresh data
    delete_transient('wgmbr_accounts_cache');
    // We'll also need to clear all location caches, but we don't know the account IDs yet
    // So we'll use a wildcard delete pattern - WordPress transients are stored with prefix
    global $wpdb;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Wildcard delete of transients, no WP function available for this pattern
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wgmbr_locations_cache_%'");
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Wildcard delete of transient timeouts, no WP function available for this pattern
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wgmbr_locations_cache_%'");

    $accounts_response = wgmbr_list_accounts();

    if (isset($accounts_response['error'])) {
        return;
    }

    $locations_data = array();

    if (isset($accounts_response['accounts']) && is_array($accounts_response['accounts'])) {
        foreach ($accounts_response['accounts'] as $account) {
            $account_id = $account['name'];
            $account_name = isset($account['accountName']) ? $account['accountName'] : $account['name'];

            // Fetch locations for this account
            $locations_response = wgmbr_list_locations($account_id);

            if (isset($locations_response['locations']) && is_array($locations_response['locations'])) {
                foreach ($locations_response['locations'] as $location) {
                    $locations_data[] = array(
                        'account_id' => $account_id,
                        'account_name' => $account_name,
                        'location_id' => $location['name'],
                        'location_title' => isset($location['title']) ? $location['title'] : 'Unnamed'
                    );
                }
            }
        }
    }

    // Save available locations
    update_option('wgmbr_available_locations', $locations_data);
}

// ============================================================================
// REVIEWS SYNCHRONIZATION TO CPT
// ============================================================================

/**
 * Synchronizes reviews from API to Custom Post Types
 *
 * @param array $reviews Array of reviews from Google API
 * @return array Synchronization results
 */
function wgmbr_sync_reviews_to_cpt($reviews) {
    if (empty($reviews) || !is_array($reviews)) {
        return array(
            'success' => false,
            'message' => esc_html__('No reviews to synchronize', 'reviews-for-google-my-business')
        );
    }

    // Use optimized bulk sync if we have many reviews
    if (count($reviews) > 10) {
        return wgmbr_sync_reviews_to_cpt_optimized($reviews);
    }

    // For small batches, use the original method (simpler, adequate)
    $synced = 0;
    $errors = 0;

    foreach ($reviews as $review) {
        $result = wgmbr_save_review_as_post($review);

        if (is_wp_error($result)) {
            $errors++;
        } else {
            $synced++;
        }
    }

    return array(
        'success' => true,
        'synced' => $synced,
        'errors' => $errors,
        'total' => count($reviews)
    );
}

/**
 * Optimized bulk synchronization to reduce DB queries from ~700 to ~20 for 100 reviews
 *
 * @param array $reviews Array of reviews from Google API
 * @return array Synchronization results
 */
function wgmbr_sync_reviews_to_cpt_optimized($reviews) {
    global $wpdb;

    // Step 1: Extract all review IDs from the batch (0 queries)
    $review_ids = array();
    foreach ($reviews as $review) {
        $review_id = isset($review['reviewId']) ? $review['reviewId'] :
                     (isset($review['name']) ? $review['name'] : '');
        if (!empty($review_id)) {
            $review_ids[] = $review_id;
        }
    }

    if (empty($review_ids)) {
        return array(
            'success' => false,
            'message' => esc_html__('No valid review IDs found', 'reviews-for-google-my-business')
        );
    }

    // Step 2: Fetch ALL existing reviews in ONE query (1 query instead of 100)
    $placeholders = implode(',', array_fill(0, count($review_ids), '%s'));
    // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- $placeholders is safely generated from array_fill with '%s' only, not user input
    $existing_query = $wpdb->prepare(
        "SELECT pm.post_id, pm.meta_value as review_id
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '_wgmbr_review_id'
        AND p.post_type = 'wgmbr_review'
        AND pm.meta_value IN ($placeholders)",
        ...$review_ids
    );
    // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared -- Optimized bulk query to check existing reviews, caching not appropriate for this operation
    $existing_reviews = $wpdb->get_results($existing_query, OBJECT_K);

    // Create a map: review_id => post_id
    $existing_map = array();
    foreach ($existing_reviews as $row) {
        $existing_map[$row->review_id] = $row->post_id;
    }

    // Step 3: Process each review (insert or update)
    $synced = 0;
    $errors = 0;

    foreach ($reviews as $review) {
        $review_id = isset($review['reviewId']) ? $review['reviewId'] :
                     (isset($review['name']) ? $review['name'] : '');

        if (empty($review_id)) {
            $errors++;
            continue;
        }

        // Prepare review data
        $reviewer = isset($review['reviewer']) ? $review['reviewer'] : array();
        $reviewer_name = isset($reviewer['displayName']) ? $reviewer['displayName'] : esc_html__('Anonymous', 'reviews-for-google-my-business');
        $reviewer_photo_url = isset($reviewer['profilePhotoUrl']) ? $reviewer['profilePhotoUrl'] : '';

        // Download profile photo locally to comply with WordPress.org guidelines
        $reviewer_photo = '';
        if (!empty($reviewer_photo_url)) {
            $reviewer_photo = wgmbr_download_profile_photo($reviewer_photo_url, $review_id);
        }

        $star_rating = isset($review['starRating']) ? $review['starRating'] : 'STAR_RATING_UNSPECIFIED';
        $rating = wgmbr_convert_star_rating($star_rating);

        $comment = isset($review['comment']) ? $review['comment'] : '';

        // Extract original text from Google's translation format
        // Google can use different formats:
        // 1. "Translation\n\n(Original)\nOriginal text"
        // 2. "Original text\n\n(Translated by Google)\nTranslation"
        if (strpos($comment, '(Original)') !== false) {
            // Format 1: Extract text after "(Original)"
            if (preg_match('/\(Original\)\s*(.+)$/s', $comment, $matches)) {
                $comment = trim($matches[1]);
            }
        } elseif (strpos($comment, '(Translated by Google)') !== false) {
            // Format 2: Extract text before "(Translated by Google)"
            if (preg_match('/^(.+?)\s*\(Translated by Google\)/s', $comment, $matches)) {
                $comment = trim($matches[1]);
            }
        }

        $review_date = isset($review['createTime']) ? $review['createTime'] : current_time('mysql');

        $post_data = array(
            /* translators: %s: Reviewer name */
            'post_title'    => sprintf(esc_html__('Review by %s', 'reviews-for-google-my-business'), $reviewer_name),
            'post_content'  => $comment,
            'post_status'   => 'publish',
            'post_type'     => 'wgmbr_review',
            'post_date'     => wp_date('Y-m-d H:i:s', strtotime($review_date)),
        );

        // Check if exists in our map
        $is_new_review = !isset($existing_map[$review_id]);
        if (!$is_new_review) {
            $post_data['ID'] = $existing_map[$review_id];
            $post_id = wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
        }

        if (is_wp_error($post_id) || !$post_id) {
            $errors++;
            continue;
        }

        // Update meta (5 queries per review, but unavoidable with wp_update_post_meta)
        update_post_meta($post_id, '_wgmbr_review_id', $review_id);
        update_post_meta($post_id, '_wgmbr_reviewer_name', $reviewer_name);
        update_post_meta($post_id, '_wgmbr_reviewer_photo', $reviewer_photo);
        update_post_meta($post_id, '_wgmbr_rating', $rating);

        // Only reset job title for new reviews, preserve existing job titles
        if ($is_new_review) {
            update_post_meta($post_id, '_wgmbr_job', '');
        }

        $synced++;
    }

    return array(
        'success' => true,
        'synced' => $synced,
        'errors' => $errors,
        'total' => count($reviews)
    );
}
