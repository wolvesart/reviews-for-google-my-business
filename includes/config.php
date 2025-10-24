<?php
/**
 * Google My Business Reviews - Configuration
 * Constantes et configuration de base
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Identifiants API et configuration - Stockés en base de données WordPress
define('GMB_CLIENT_ID', get_option('wgmbr_client_id', ''));
define('GMB_CLIENT_SECRET', get_option('wgmbr_client_secret', ''));
define('GMB_REDIRECT_URI', get_option('wgmbr_redirect_uri', admin_url('admin.php?page=gmb-settings&wgmbr_auth=1')));
define('GMB_ACCOUNT_ID', get_option('wgmbr_account_id', ''));
define('GMB_LOCATION_ID', get_option('wgmbr_location_id', ''));

// Scopes OAuth requis pour l'API My Business
// Documentation: https://developers.google.com/my-business/content/prereqs
define('GMB_SCOPES', implode(' ', array(
    'https://www.googleapis.com/auth/business.manage',              // Accès aux avis et gestion du profil
    'https://www.googleapis.com/auth/plus.business.manage'          // Accès aux informations business (requis pour les locations)
)));
