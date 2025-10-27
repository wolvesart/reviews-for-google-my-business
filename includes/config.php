<?php
/**
 * Reviews for Google My Business - Configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

// API Credentials and Configuration - Stored in WordPress Database
define('GMB_CLIENT_ID', get_option('wgmbr_client_id', ''));
define('GMB_CLIENT_SECRET', get_option('wgmbr_client_secret', ''));
define('GMB_REDIRECT_URI', get_option('wgmbr_redirect_uri', admin_url('admin.php?page=gmb-settings&wgmbr_auth=1')));
define('GMB_ACCOUNT_ID', get_option('wgmbr_account_id', ''));
define('GMB_LOCATION_ID', get_option('wgmbr_location_id', ''));

// OAuth Scopes Required for My Business API
// Documentation: https://developers.google.com/my-business/content/prereqs
define('GMB_SCOPES', implode(' ', array(
    'https://www.googleapis.com/auth/business.manage',
    'https://www.googleapis.com/auth/plus.business.manage'
)));
