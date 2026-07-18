<?php
/**
 * Reviews for Google My Business - Uninstall cleanup
 *
 * Removes all plugin data on uninstall: options (including OAuth credentials
 * and tokens), transients, review posts and their meta, categories taxonomy
 * terms, and locally stored reviewer profile photos.
 */

// Only run in the context of a real WordPress uninstall
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// ============================================================================
// OPTIONS
// ============================================================================

$wgmbr_options = array(
    // API credentials and OAuth
    'wgmbr_client_id',
    'wgmbr_client_secret',
    'wgmbr_redirect_uri',
    'wgmbr_access_token',
    'wgmbr_refresh_token',
    'wgmbr_token_expires',
    // Account / location selection
    'wgmbr_account_id',
    'wgmbr_location_id',
    'wgmbr_available_locations',
    // Errors and logs
    'wgmbr_last_error',
    'wgmbr_last_token_error',
    'wgmbr_error_logs',
    // Customization
    'wgmbr_color_card_bg',
    'wgmbr_color_star',
    'wgmbr_color_text_primary',
    'wgmbr_color_text_resume',
    'wgmbr_color_accent',
    'wgmbr_color_show_more',
    'wgmbr_radius_card',
    'wgmbr_layout',
);

foreach ($wgmbr_options as $wgmbr_option) {
    delete_option($wgmbr_option);
}

// ============================================================================
// TRANSIENTS
// ============================================================================

delete_transient('wgmbr_reviews_cache');
delete_transient('wgmbr_avg_rating_cache');
delete_transient('wgmbr_accounts_cache');
delete_transient('wgmbr_oauth_state');
delete_transient('wgmbr_last_sync_time');

// Dynamic transients (wgmbr_locations_cache_{hash}, wgmbr_email_sent_{context})
global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Wildcard delete of plugin transients on uninstall, no WP function available for this pattern
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
        $wpdb->esc_like('_transient_wgmbr_') . '%',
        $wpdb->esc_like('_transient_timeout_wgmbr_') . '%'
    )
);

// ============================================================================
// REVIEW POSTS AND CATEGORY TERMS
// ============================================================================

// The CPT and taxonomy are not registered during uninstall: register the
// taxonomy minimally so wp_delete_term() can operate
register_taxonomy('wgmbr_category', array());

$wgmbr_terms = get_terms(array(
    'taxonomy'   => 'wgmbr_category',
    'hide_empty' => false,
    'fields'     => 'ids',
));

if (!is_wp_error($wgmbr_terms)) {
    foreach ($wgmbr_terms as $wgmbr_term_id) {
        wp_delete_term($wgmbr_term_id, 'wgmbr_category');
    }
}

// Delete all review posts (post meta is removed by wp_delete_post)
$wgmbr_review_ids = get_posts(array(
    'post_type'      => 'wgmbr_review',
    'post_status'    => 'any',
    'posts_per_page' => -1,
    'fields'         => 'ids',
));

foreach ($wgmbr_review_ids as $wgmbr_review_id) {
    wp_delete_post($wgmbr_review_id, true);
}

// ============================================================================
// LOCAL PROFILE PHOTOS (uploads/gmb-reviews)
// ============================================================================

$wgmbr_upload_dir = wp_upload_dir();
$wgmbr_photos_dir = $wgmbr_upload_dir['basedir'] . '/gmb-reviews';

if (is_dir($wgmbr_photos_dir)) {
    $wgmbr_files = glob($wgmbr_photos_dir . '/*');

    if (is_array($wgmbr_files)) {
        foreach ($wgmbr_files as $wgmbr_file) {
            if (is_file($wgmbr_file)) {
                // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- Direct file operation needed for uninstall cleanup
                unlink($wgmbr_file);
            }
        }
    }

    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Direct file operation needed for uninstall cleanup
    rmdir($wgmbr_photos_dir);
}