<?php
/**
 * Reviews for Google My Business - Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

// API Credentials and Configuration - Stored in WordPress Database
define('WGMBR_CLIENT_ID', get_option('wgmbr_client_id', ''));

// Get and decrypt client secret
$wgmbr_stored_secret = get_option('wgmbr_client_secret', '');
$wgmbr_decrypted_secret = '';
if (!empty($wgmbr_stored_secret)) {
    // Check if it's already encrypted
    if (wgmbr_is_encrypted($wgmbr_stored_secret)) {
        $wgmbr_decrypted_secret = wgmbr_decrypt($wgmbr_stored_secret);
    } else {
        // Legacy plain text - decrypt will fail but we'll use it as-is
        // Auto-migrate: encrypt it for next time
        $wgmbr_decrypted_secret = $wgmbr_stored_secret;
        $wgmbr_encrypted = wgmbr_encrypt($wgmbr_stored_secret);
        if (!empty($wgmbr_encrypted)) {
            update_option('wgmbr_client_secret', $wgmbr_encrypted);
        }
    }
}
define('WGMBR_CLIENT_SECRET', $wgmbr_decrypted_secret);

define('WGMBR_REDIRECT_URI', get_option('wgmbr_redirect_uri', admin_url('admin.php?page=wgmbr-settings&wgmbr_auth=1')));
define('WGMBR_ACCOUNT_ID', get_option('wgmbr_account_id', ''));
define('WGMBR_LOCATION_ID', get_option('wgmbr_location_id', ''));

// OAuth Scopes Required for My Business API
// Documentation: https://developers.google.com/my-business/content/prereqs
define('WGMBR_SCOPES', implode(' ', array(
    'https://www.googleapis.com/auth/business.manage',
    'https://www.googleapis.com/auth/plus.business.manage'
)));
