<?php
/**
 * Reviews for Google My Business - Helper Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parse review CPT data and return a standardized object
 *
 * @param WP_Post|int $post Post object or post ID
 * @return object|null Object containing parsed review data or null
 */
function wgmbr_parse_review_from_post($post)
{
    if (is_numeric($post)) {
        $post = get_post($post);
    }

    if (!$post || $post->post_type !== 'gmb_review') {
        return null;
    }

    $parsed = new stdClass();

    // Reviewer data
    $parsed->name = get_post_meta($post->ID, '_gmb_reviewer_name', true);
    if (empty($parsed->name)) {
        $parsed->name = esc_html__('Anonymous', 'reviews-for-google-my-business');
    }

    $parsed->photo = get_post_meta($post->ID, '_gmb_reviewer_photo', true);

    // Rating
    $parsed->rating = (float)get_post_meta($post->ID, '_gmb_rating', true);

    // Comment
    $parsed->comment = $post->post_content;

    // Date
    $parsed->date = strtotime($post->post_date);

    // Google review ID
    $parsed->review_id = get_post_meta($post->ID, '_gmb_review_id', true);

    // Custom data (job)
    $parsed->job = get_post_meta($post->ID, '_gmb_job', true);

    // Categories (taxonomy)
    $terms = wp_get_post_terms($post->ID, 'gmb_category');
    $parsed->categories = !is_wp_error($terms) ? $terms : array();
    $parsed->category_ids = array_map(function ($term) {
        return $term->term_id;
    }, $parsed->categories);
    $parsed->category_names = array_map(function ($term) {
        return $term->name;
    }, $parsed->categories);
    $parsed->category_slugs = array_map(function ($term) {
        return $term->slug;
    }, $parsed->categories);

    // WordPress post ID
    $parsed->post_id = $post->ID;

    return $parsed;
}

/**
 * Get all reviews with optional filtering
 *
 * @param array $args Custom arguments
 * @return array Array of parsed objects
 */
function wgmbr_get_all_reviews($args = array())
{
    $defaults = array(
        'post_type' => 'gmb_review',
        'post_status' => 'publish',
        'posts_per_page' => WGMBR_DEFAULT_REVIEW_LIMIT,
        'orderby' => 'date',
        'order' => 'DESC',
    );

    $args = wp_parse_args($args, $defaults);

    $query = new WP_Query($args);
    $parsed_reviews = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $parsed = wgmbr_parse_review_from_post(get_post());
            if ($parsed) {
                $parsed_reviews[] = $parsed;
            }
        }
        wp_reset_postdata();
    }

    return $parsed_reviews;
}

/**
 * Get all reviews with optional filtering and return WP_Query object
 * Used for pagination in admin
 *
 * @param array $args Custom arguments
 * @return array ['query' => WP_Query, 'reviews' => array]
 */
function wgmbr_get_all_reviews_with_query($args = array())
{
    $defaults = array(
        'post_type' => 'gmb_review',
        'post_status' => 'publish',
        'posts_per_page' => 20,
        'orderby' => 'date',
        'order' => 'DESC',
        'paged' => 1,
    );

    $args = wp_parse_args($args, $defaults);

    $query = new WP_Query($args);
    $parsed_reviews = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $parsed = wgmbr_parse_review_from_post(get_post());
            if ($parsed) {
                $parsed_reviews[] = $parsed;
            }
        }
        wp_reset_postdata();
    }

    return array(
        'query' => $query,
        'reviews' => $parsed_reviews,
    );
}

/**
 * Get reviews filtered by category
 *
 * @param string|array $category_slug Category slug (empty = reviews without category, array = multiple categories)
 * @param int $limit Number of reviews to retrieve
 * @return array Array of parsed objects
 */
function wgmbr_get_reviews_by_category($category_slug, $limit = 50)
{
    $args = array(
        'post_type' => 'gmb_review',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
    );

    // If category is empty string, find reviews without category
    if ($category_slug === '') {
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary to filter reviews by taxonomy, standard WordPress method
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'gmb_category',
                'operator' => 'NOT EXISTS',
            ),
        );
    } else {
        // Filter by category slug (supports string or array)
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary to filter reviews by category, standard WordPress method
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'gmb_category',
                'field' => 'slug',
                'terms' => $category_slug, // WordPress accepts string or array
                'operator' => 'IN', // IN = at least one of the categories
            ),
        );
    }

    return wgmbr_get_all_reviews($args);
}

/**
 * Calculate average rating of all reviews
 * Optimized version with caching and single query
 *
 * @return float Average rating
 */
function wgmbr_get_average_rating()
{
    // Check cache first
    $cache_key = 'wgmbr_avg_rating_cache';
    $cached = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }

    global $wpdb;

    // Single SQL query to calculate average directly
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Optimized aggregate query, result is cached with transients
    $average = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT AVG(CAST(pm.meta_value AS DECIMAL(3,2)))
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = %s
            AND p.post_type = %s
            AND p.post_status = %s
            AND pm.meta_value != ''",
            '_gmb_rating',
            'gmb_review',
            'publish'
        )
    );

    $result = $average ? (float) $average : 0;

    // Cache for 1 hour
    set_transient($cache_key, $result, HOUR_IN_SECONDS);

    return $result;
}

/**
 * Count total number of reviews
 *
 * @return int Total number of reviews
 */
function wgmbr_get_total_reviews_count()
{
    $count = wp_count_posts('gmb_review');
    return isset($count->publish) ? (int)$count->publish : 0;
}

/**
 * Update review job field
 *
 * @param int $post_id Post ID
 * @param string $job Person's job title
 * @return bool True on success
 */
function wgmbr_update_review_job($post_id, $job)
{
    return update_post_meta($post_id, '_gmb_job', sanitize_text_field($job));
}

/**
 * Assign categories to a review
 *
 * @param int $post_id Post ID
 * @param array $category_ids Array of category IDs (term_ids)
 * @return array|WP_Error Array of term taxonomy IDs or WP_Error
 */
function wgmbr_set_review_categories($post_id, $category_ids = array())
{
    if (empty($category_ids)) {
        // Remove all categories
        return wp_set_post_terms($post_id, array(), 'gmb_category');
    }

    // Assign categories
    return wp_set_post_terms($post_id, $category_ids, 'gmb_category');
}

/**
 * Get parsed review by Google review_id
 * Note: uses wgmbr_get_review_post_by_review_id() from post-types.php
 *
 * @param string $review_id Google review ID
 * @return object|null Parsed review object or null
 */
function wgmbr_get_parsed_review_by_review_id($review_id)
{
    // Use post-types.php function that returns WP_Post
    $args = array(
        'post_type' => 'gmb_review',
        'post_status' => 'any',
        'posts_per_page' => 1,
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary to find existing review by unique Google review ID to prevent duplicates
        'meta_query' => array(
            array(
                'key' => '_gmb_review_id',
                'value' => $review_id,
                'compare' => '='
            )
        ),
    );

    $posts = get_posts($args);

    if (empty($posts)) {
        return null;
    }

    return wgmbr_parse_review_from_post($posts[0]);
}

// Create PHP component
function wgmbr_get_template_parts($path, $params = [])
{
    include WOLVES_GMB_PLUGIN_DIR . $path . '.php';
}

// ============================================================================
// ENCRYPTION / DECRYPTION FOR SENSITIVE DATA
// ============================================================================

/**
 * Encrypt sensitive data using WordPress salts
 *
 * @param string $data Data to encrypt
 * @return string Encrypted data (base64 encoded)
 */
function wgmbr_encrypt($data) {
    if (empty($data)) {
        return '';
    }

    // Check if OpenSSL is available
    if (!function_exists('openssl_encrypt')) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional error logging for security debugging
        error_log('[GMB Reviews] OpenSSL not available for encryption');
        return $data; // Fallback: return plain text
    }

    // Generate encryption key from WordPress salts
    // Use constants directly instead of wp_hash() which might not be loaded yet
    if (!defined('AUTH_KEY') || !defined('SECURE_AUTH_KEY')) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional error logging for security debugging
        error_log('[GMB Reviews] WordPress salts not defined, cannot encrypt');
        return $data; // Fallback: return plain text
    }

    $salt_combo = AUTH_KEY . SECURE_AUTH_KEY . LOGGED_IN_KEY . NONCE_KEY;
    $key = substr(hash('sha256', $salt_combo, true), 0, 32); // 256-bit key

    // Generate IV (Initialization Vector)
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($iv_length);

    // Encrypt
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    if ($encrypted === false) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional error logging for security debugging
        error_log('[GMB Reviews] Failed to encrypt data');
        return $data; // Fallback: return plain text
    }

    // Combine IV and encrypted data, then base64 encode
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt sensitive data
 *
 * @param string $encrypted_data Encrypted data (base64 encoded)
 * @return string Decrypted data
 */
function wgmbr_decrypt($encrypted_data) {
    if (empty($encrypted_data)) {
        return '';
    }

    // Check if OpenSSL is available
    if (!function_exists('openssl_decrypt')) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional error logging for security debugging
        error_log('[GMB Reviews] OpenSSL not available for decryption');
        return $encrypted_data; // Fallback: return as-is (might be plain text)
    }

    // Generate encryption key from WordPress salts (same as encryption)
    if (!defined('AUTH_KEY') || !defined('SECURE_AUTH_KEY')) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional error logging for security debugging
        error_log('[GMB Reviews] WordPress salts not defined, cannot decrypt');
        return $encrypted_data; // Fallback: return as-is
    }

    $salt_combo = AUTH_KEY . SECURE_AUTH_KEY . LOGGED_IN_KEY . NONCE_KEY;
    $key = substr(hash('sha256', $salt_combo, true), 0, 32);

    // Decode base64
    $data = base64_decode($encrypted_data, true);

    if ($data === false) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional error logging for security debugging
        error_log('[GMB Reviews] Failed to decode encrypted data');
        return ''; // Invalid data
    }

    // Extract IV and encrypted content
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');

    // Check if data is long enough to contain IV
    if (strlen($data) <= $iv_length) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional error logging for security debugging
        error_log('[GMB Reviews] Encrypted data too short');
        return ''; // Invalid data
    }

    $iv = substr($data, 0, $iv_length);
    $encrypted = substr($data, $iv_length);

    // Decrypt
    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    if ($decrypted === false) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional error logging for security debugging
        error_log('[GMB Reviews] Failed to decrypt data');
        return '';
    }

    return $decrypted;
}

/**
 * Check if client secret is stored in encrypted format
 * Helper for migration from plain text to encrypted
 *
 * @param string $value Value to check
 * @return bool True if encrypted, false if plain text
 */
function wgmbr_is_encrypted($value) {
    if (empty($value)) {
        return false;
    }

    // Encrypted values are base64 encoded and have a minimum length
    $decoded = base64_decode($value, true);

    // Check if it's valid base64 and has minimum length for IV + data
    return ($decoded !== false && strlen($decoded) > openssl_cipher_iv_length('aes-256-cbc'));
}

// ============================================================================
// CENTRALIZED ERROR LOGGING SYSTEM
// ============================================================================

/**
 * Centralized error logging function
 * Logs to WordPress error log and stores in database for admin display
 *
 * @param string $context Context of the error (e.g., 'oauth', 'api', 'sync')
 * @param string $message Error message
 * @param array $data Additional data for debugging
 * @param string $level Error level: 'error', 'warning', 'info'
 */
function wgmbr_log_error($context, $message, $data = array(), $level = 'error') {
    $timestamp = current_time('mysql');

    $log_entry = array(
        'timestamp' => $timestamp,
        'context' => $context,
        'message' => $message,
        'level' => $level,
        'user_id' => get_current_user_id(),
        'data' => $data
    );

    // Log to WordPress error log
    $log_message = sprintf(
        '[GMB Reviews] [%s] %s: %s',
        strtoupper($level),
        $context,
        $message
    );

    if (!empty($data)) {
        $log_message .= ' | Data: ' . wp_json_encode($data);
    }

    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional centralized error logging system
    error_log($log_message);

    // Save to database for admin dashboard (keep last 100 entries)
    $logs = get_option('wgmbr_error_logs', array());
    array_unshift($logs, $log_entry); // Add to beginning
    $logs = array_slice($logs, 0, 100); // Keep last 100
    update_option('wgmbr_error_logs', $logs, false); // false = don't autoload

    // Send email notification for critical errors
    if ($level === 'error' && in_array($context, array('oauth', 'token_refresh', 'api_error'), true)) {
        wgmbr_send_error_notification($context, $message, $data);
    }
}

/**
 * Send email notification for critical errors
 *
 * @param string $context Error context
 * @param string $message Error message
 * @param array $data Additional data
 */
function wgmbr_send_error_notification($context, $message, $data = array()) {
    // Check if we've sent an email recently (throttle to 1 per hour per context)
    $throttle_key = 'wgmbr_email_sent_' . $context;
    if (get_transient($throttle_key)) {
        return; // Already sent recently
    }

    set_transient($throttle_key, true, HOUR_IN_SECONDS);

    $admin_email = get_option('admin_email');
    $site_name = get_bloginfo('name');

    /* translators: 1: Site name, 2: Error context */
    $subject = sprintf(__('[%1$s] GMB Reviews Error: %2$s', 'reviews-for-google-my-business'), $site_name, $context);

    $body = sprintf(
        /* translators: 1: Error context, 2: Error message, 3: Additional data */
        __("An error occurred in the Google My Business Reviews plugin.\n\nContext: %1\$s\nMessage: %2\$s\n\nAdditional data:\n%3\$s\n\nPlease check the plugin settings.", 'reviews-for-google-my-business'),
        $context,
        $message,
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Used for email body formatting, not debug output
        !empty($data) ? print_r($data, true) : 'None'
    );

    wp_mail($admin_email, $subject, $body);
}

/**
 * Get recent error logs for admin display
 *
 * @param int $limit Number of logs to retrieve
 * @param string $level Filter by error level (optional)
 * @return array Error logs
 */
function wgmbr_get_error_logs($limit = 50, $level = null) {
    $logs = get_option('wgmbr_error_logs', array());

    if ($level) {
        $logs = array_filter($logs, function($log) use ($level) {
            return isset($log['level']) && $log['level'] === $level;
        });
    }

    return array_slice($logs, 0, $limit);
}

/**
 * Clear all error logs
 */
function wgmbr_clear_error_logs() {
    delete_option('wgmbr_error_logs');
}

// ============================================================================
// API RESPONSE FORMAT STANDARDIZATION
// ============================================================================

/**
 * Format success API response with standard structure
 *
 * @param array $data Response data
 * @param string $message Optional success message
 * @return array Standardized success response
 */
function wgmbr_api_success_response($data = array(), $message = '') {
    return array(
        'error' => false,
        'success' => true,
        'message' => $message,
        'data' => $data,
        'timestamp' => current_time('mysql')
    );
}

/**
 * Format error API response with standard structure
 *
 * @param string $message Error message
 * @param int|null $status_code HTTP status code
 * @param array $details Additional error details
 * @param string $error_code Optional error code for programmatic handling
 * @return array Standardized error response
 */
function wgmbr_api_error_response($message, $status_code = null, $details = array(), $error_code = null) {
    $response = array(
        'error' => true,
        'success' => false,
        'message' => $message,
        'timestamp' => current_time('mysql')
    );

    if ($status_code !== null) {
        $response['status_code'] = $status_code;
    }

    if (!empty($details)) {
        $response['details'] = $details;
    }

    if ($error_code !== null) {
        $response['error_code'] = $error_code;
    }

    return $response;
}