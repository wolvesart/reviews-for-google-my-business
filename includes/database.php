<?php
/**
 * Google My Business Reviews - Database Functions
 * Gestion de la table pour les données personnalisées des avis
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// CRÉATION DE LA TABLE
// ============================================================================

/**
 * Crée la table pour stocker les données personnalisées des avis
 */
function wgmbr_create_custom_reviews_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wgmbr_reviews_custom';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        review_id varchar(255) NOT NULL,
        reviewer_name varchar(255) DEFAULT NULL,
        job varchar(255) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY review_id (review_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Créer la table lors de l'activation du thème
add_action('after_switch_theme', 'wgmbr_create_custom_reviews_table');

// Créer la table lors de l'initialisation de l'admin si elle n'existe pas
add_action('admin_init', 'wgmbr_check_custom_reviews_table');

/**
 * Vérifie si la table existe et la crée si nécessaire
 */
function wgmbr_check_custom_reviews_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wgmbr_reviews_custom';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        wgmbr_create_custom_reviews_table();
    }
}

// ============================================================================
// FONCTIONS CRUD
// ============================================================================

/**
 * Récupère les données personnalisées d'un avis
 *
 * @param string $review_id ID de l'avis Google
 * @return object|null Données personnalisées ou null
 */
function wgmbr_get_custom_review_data($review_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wgmbr_reviews_custom';

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE review_id = %s",
        $review_id
    ));
}

/**
 * Sauvegarde ou met à jour les données personnalisées d'un avis
 *
 * @param string $review_id ID de l'avis Google
 * @param string $reviewer_name Nom du reviewer
 * @param string $job Poste de la personne
 * @param array $category_ids Tableau des IDs de catégories (optionnel)
 * @return array ['success' => bool, 'message' => string, 'error' => string]
 */
function wgmbr_save_custom_review_data($review_id, $reviewer_name, $job, $category_ids = array()) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wgmbr_reviews_custom';

    // Vérifier que la table existe
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        wgmbr_create_custom_reviews_table();
    }

    $existing = wgmbr_get_custom_review_data($review_id);

    $data = array(
        'reviewer_name' => $reviewer_name,
        'job' => $job
    );

    $format = array('%s', '%s');

    if ($existing) {
        // Mise à jour
        $result = $wpdb->update(
            $table_name,
            $data,
            array('review_id' => $review_id),
            $format,
            array('%s')
        );

        if ($result !== false) {
            // Note: Les catégories sont maintenant gérées via taxonomies WordPress (voir helpers.php)
            return array('success' => true, 'message' => 'Données mises à jour avec succès');
        } else {
            return array('success' => false, 'message' => 'Erreur lors de la mise à jour', 'error' => $wpdb->last_error);
        }
    } else {
        // Insertion
        $data['review_id'] = $review_id;
        $format_insert = array_merge(array('%s'), $format);

        $result = $wpdb->insert(
            $table_name,
            $data,
            $format_insert
        );

        if ($result !== false) {
            // Note: Les catégories sont maintenant gérées via taxonomies WordPress (voir helpers.php)
            return array('success' => true, 'message' => 'Données enregistrées avec succès');
        } else {
            return array('success' => false, 'message' => 'Erreur lors de l\'insertion', 'error' => $wpdb->last_error);
        }
    }
}

/**
 * Supprime les données personnalisées d'un avis
 *
 * @param string $review_id ID de l'avis Google
 * @return bool True si succès, false sinon
 */
function wgmbr_delete_custom_review_data($review_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wgmbr_reviews_custom';

    return $wpdb->delete(
        $table_name,
        array('review_id' => $review_id),
        array('%s')
    ) !== false;
}
