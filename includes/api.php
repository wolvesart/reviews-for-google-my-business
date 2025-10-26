<?php
/**
 * Google My Business Reviews - API Functions
 * Fonctions OAuth 2.0 et appels API
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// FONCTIONS OAUTH 2.0
// ============================================================================

/**
 * Génère l'URL d'autorisation OAuth
 */
function wgmbr_get_auth_url() {
    $params = array(
        'client_id' => GMB_CLIENT_ID,
        'redirect_uri' => GMB_REDIRECT_URI,
        'scope' => GMB_SCOPES,
        'response_type' => 'code',
        'access_type' => 'offline',
        'prompt' => 'consent'
    );

    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

/**
 * Échange le code d'autorisation contre un access token
 */
function wgmbr_exchange_code_for_token($code) {
    $token_url = 'https://oauth2.googleapis.com/token';

    $params = array(
        'code' => $code,
        'client_id' => GMB_CLIENT_ID,
        'client_secret' => GMB_CLIENT_SECRET,
        'redirect_uri' => GMB_REDIRECT_URI,
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
        // Sauvegarder les tokens
        update_option('wgmbr_access_token', $body['access_token']);
        update_option('wgmbr_refresh_token', $body['refresh_token']);
        update_option('wgmbr_token_expires', time() + $body['expires_in']);

        return true;
    }

    return false;
}

/**
 * Rafraîchit l'access token avec le refresh token
 */
function wgmbr_refresh_access_token() {
    $refresh_token = get_option('wgmbr_refresh_token');

    if (!$refresh_token) {
        return false;
    }

    $token_url = 'https://oauth2.googleapis.com/token';

    $params = array(
        'client_id' => GMB_CLIENT_ID,
        'client_secret' => GMB_CLIENT_SECRET,
        'refresh_token' => $refresh_token,
        'grant_type' => 'refresh_token'
    );

    $response = wp_remote_post($token_url, array(
        'body' => $params
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['access_token'])) {
        update_option('wgmbr_access_token', $body['access_token']);
        update_option('wgmbr_token_expires', time() + $body['expires_in']);
        return true;
    }

    return false;
}

/**
 * Récupère un access token valide (rafraîchit si nécessaire)
 */
function wgmbr_get_valid_access_token() {
    $access_token = get_option('wgmbr_access_token');
    $expires = get_option('wgmbr_token_expires', 0);

    // Si le token expire dans moins de 5 minutes, le rafraîchir
    if (time() >= ($expires - 300)) {
        if (!wgmbr_refresh_access_token()) {
            return false;
        }
        $access_token = get_option('wgmbr_access_token');
    }

    return $access_token;
}

// ============================================================================
// RÉCUPÉRATION DES COMPTES ET LOCATIONS
// ============================================================================

/**
 * Liste tous les comptes GMB accessibles
 * Documentation: https://developers.google.com/my-business/reference/accountmanagement/rest/v1/accounts/list
 */
function wgmbr_list_accounts() {
    $access_token = wgmbr_get_valid_access_token();

    if (!$access_token) {
        return array('error' => true, 'message' => 'Token non disponible');
    }

    $api_url = 'https://mybusinessaccountmanagement.googleapis.com/v1/accounts';

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        ),
        'timeout' => 15
    ));

    if (is_wp_error($response)) {
        return array('error' => true, 'message' => $response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    return $body;
}

/**
 * Liste toutes les locations pour un compte donné
 * Documentation: https://developers.google.com/my-business/reference/businessinformation/rest/v1/accounts.locations/list
 */
function wgmbr_list_locations($account_id) {
    $access_token = wgmbr_get_valid_access_token();

    if (!$access_token) {
        return array('error' => true, 'message' => 'Token non disponible');
    }

    // readMask est obligatoire - utiliser uniquement les champs de premier niveau
    // Documentation: https://developers.google.com/my-business/reference/businessinformation/rest/v1/accounts.locations#Location
    // On demande juste name et title, les champs essentiels
    $read_mask = 'name,title';

    $api_url = add_query_arg(array(
        'readMask' => $read_mask,
        'pageSize' => 100  // Maximum de locations à récupérer
    ), 'https://mybusinessbusinessinformation.googleapis.com/v1/' . $account_id . '/locations');

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        ),
        'timeout' => 15
    ));

    if (is_wp_error($response)) {
        return array('error' => true, 'message' => $response->get_error_message());
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    // Ajouter le code de statut pour le debug
    if ($status_code !== 200) {
        $body['http_status'] = $status_code;
    }

    return $body;
}

// ============================================================================
// RÉCUPÉRATION DES AVIS
// ============================================================================

/**
 * Récupère les avis depuis l'API Google My Business v4
 * Documentation: https://developers.google.com/my-business/content/review-data
 */
function wgmbr_fetch_reviews() {
    // Vérifier le cache (valide 1 heure)
    $cache_key = 'wgmbr_reviews_cache';
    $cached_data = get_transient($cache_key);

    if ($cached_data !== false) {
        return $cached_data;
    }

    // My Business API v4 avec OAuth
    // Documentation: https://developers.google.com/my-business/reference/rest/v4/accounts.locations.reviews/list
    $access_token = wgmbr_get_valid_access_token();

    if ($access_token && GMB_ACCOUNT_ID && GMB_LOCATION_ID) {
        // Format exact selon la doc: {parent=accounts/*/locations/*}/reviews
        $parent = GMB_ACCOUNT_ID . '/' . GMB_LOCATION_ID;

        $api_url = add_query_arg(array(
            'pageSize' => 50,  // Maximum autorisé
            'orderBy' => 'updateTime desc'
        ), 'https://mybusiness.googleapis.com/v4/' . $parent . '/reviews');

        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 15
        ));

        if (!is_wp_error($response)) {
            $status_code = wp_remote_retrieve_response_code($response);
            $body = json_decode(wp_remote_retrieve_body($response), true);

            // Succès
            if ($status_code === 200) {
                $reviews_data = array(
                    'error' => false,
                    'source' => 'My Business API v4',
                    'reviews' => isset($body['reviews']) ? $body['reviews'] : array(),
                    'total' => isset($body['totalReviewCount']) ? $body['totalReviewCount'] : 0,
                    'average_rating' => isset($body['averageRating']) ? $body['averageRating'] : 0,
                    'next_page_token' => isset($body['nextPageToken']) ? $body['nextPageToken'] : null
                );

                // Synchroniser les avis vers les CPT
                wgmbr_sync_reviews_to_cpt($reviews_data['reviews']);

                set_transient($cache_key, $reviews_data, HOUR_IN_SECONDS);
                return $reviews_data;
            } else {
                // Erreur API avec détails
                return array(
                    'error' => true,
                    'message' => 'Erreur API Google My Business (Code ' . $status_code . ')',
                    'api_response' => $body,
                    'status_code' => $status_code
                );
            }
        } else {
            // Erreur de requête
            return array(
                'error' => true,
                'message' => 'Erreur de connexion à l\'API: ' . $response->get_error_message()
            );
        }
    }

    // Si l'authentification n'est pas configurée
    return array(
        'error' => true,
        'message' => 'L\'API Google My Business n\'est pas authentifiée. Veuillez configurer OAuth depuis la page GMB Reviews dans l\'admin.'
    );
}

// ============================================================================
// CALLBACK OAUTH
// ============================================================================

/**
 * Gère le callback OAuth de Google
 */
function wgmbr_handle_oauth_callback() {
    if (!isset($_GET['wgmbr_auth'])) {
        return;
    }

    if (isset($_GET['code'])) {
        $code = sanitize_text_field(wp_unslash($_GET['code']));
        $success = wgmbr_exchange_code_for_token($code);

        if ($success) {
            // Récupérer automatiquement les comptes et locations après authentification
            wgmbr_auto_fetch_accounts_and_locations();
            wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=success&auto_fetch=1'));
        } else {
            wp_safe_redirect(admin_url('admin.php?page=gmb-settings&status=error'));
        }
        exit;
    }
}
add_action('init', 'wgmbr_handle_oauth_callback');

/**
 * Récupère automatiquement les comptes et locations après OAuth
 */
function wgmbr_auto_fetch_accounts_and_locations() {
    $accounts_response = wgmbr_list_accounts();

    if (isset($accounts_response['error'])) {
        return;
    }

    $locations_data = array();

    if (isset($accounts_response['accounts']) && is_array($accounts_response['accounts'])) {
        foreach ($accounts_response['accounts'] as $account) {
            $account_id = $account['name'];
            $account_name = isset($account['accountName']) ? $account['accountName'] : $account['name'];

            // Récupérer les locations pour ce compte
            $locations_response = wgmbr_list_locations($account_id);

            if (isset($locations_response['locations']) && is_array($locations_response['locations'])) {
                foreach ($locations_response['locations'] as $location) {
                    $locations_data[] = array(
                        'account_id' => $account_id,
                        'account_name' => $account_name,
                        'location_id' => $location['name'],
                        'location_title' => isset($location['title']) ? $location['title'] : 'Sans nom'
                    );
                }
            }
        }
    }

    // Sauvegarder les locations disponibles
    update_option('wgmbr_available_locations', $locations_data);
}

// ============================================================================
// SYNCHRONISATION DES AVIS VERS CPT
// ============================================================================

/**
 * Synchronise les avis de l'API vers les Custom Post Types
 *
 * @param array $reviews Tableau d'avis depuis l'API Google
 * @return array Résultats de la synchronisation
 */
function wgmbr_sync_reviews_to_cpt($reviews) {
    if (empty($reviews) || !is_array($reviews)) {
        return array(
            'success' => false,
            'message' => __('No reviews to synchronize', 'google-my-business-reviews')
        );
    }

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
