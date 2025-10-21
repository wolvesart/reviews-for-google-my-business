<?php
/**
 * Google My Business Reviews - Categories Management
 * Gestion des catégories pour les avis GMB
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// CRÉATION DE LA TABLE DES CATÉGORIES
// ============================================================================

/**
 * Crée la table pour stocker les catégories d'avis
 */
function gmb_create_categories_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gmb_review_categories';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        slug varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY slug (slug)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Créer la table lors de l'activation du thème
add_action('after_switch_theme', 'gmb_create_categories_table');

// Créer la table lors de l'initialisation de l'admin si elle n'existe pas
add_action('admin_init', 'gmb_check_categories_table');

/**
 * Vérifie si la table existe et la crée si nécessaire
 */
function gmb_check_categories_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gmb_review_categories';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        gmb_create_categories_table();
    }
}

// ============================================================================
// TABLE DE RELATION MANY-TO-MANY (AVIS <-> CATÉGORIES)
// ============================================================================

/**
 * Crée la table de relation entre avis et catégories
 */
function gmb_create_review_category_relation_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gmb_review_category_relations';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        review_id varchar(255) NOT NULL,
        category_id bigint(20) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY review_category (review_id, category_id),
        KEY category_id (category_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

add_action('after_switch_theme', 'gmb_create_review_category_relation_table');
add_action('admin_init', 'gmb_check_review_category_relation_table');

/**
 * Vérifie si la table de relation existe
 */
function gmb_check_review_category_relation_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gmb_review_category_relations';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        gmb_create_review_category_relation_table();
    }
}

/**
 * Migration : Déplacer les anciennes données category_id vers la table de relation
 */
function gmb_migrate_category_data() {
    global $wpdb;
    $reviews_table = $wpdb->prefix . 'gmb_reviews_custom';
    $relation_table = $wpdb->prefix . 'gmb_review_category_relations';

    // Vérifier si la colonne category_id existe encore
    $column = $wpdb->get_results("SHOW COLUMNS FROM $reviews_table LIKE 'category_id'");

    if (!empty($column)) {
        // Migrer les données
        $wpdb->query("
            INSERT IGNORE INTO $relation_table (review_id, category_id)
            SELECT review_id, category_id
            FROM $reviews_table
            WHERE category_id IS NOT NULL
        ");

        // Supprimer l'ancienne colonne
        $wpdb->query("ALTER TABLE $reviews_table DROP COLUMN category_id");
    }
}

add_action('admin_init', 'gmb_migrate_category_data');

// ============================================================================
// FONCTIONS CRUD POUR LES CATÉGORIES
// ============================================================================

/**
 * Récupère toutes les catégories
 *
 * @return array Liste des catégories
 */
function gmb_get_all_categories() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gmb_review_categories';

    return $wpdb->get_results("SELECT * FROM $table_name ORDER BY name ASC");
}

/**
 * Récupère une catégorie par son ID
 *
 * @param int $category_id ID de la catégorie
 * @return object|null Catégorie ou null
 */
function gmb_get_category($category_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gmb_review_categories';

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $category_id
    ));
}

/**
 * Récupère une catégorie par son slug
 *
 * @param string $slug Slug de la catégorie
 * @return object|null Catégorie ou null
 */
function gmb_get_category_by_slug($slug) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gmb_review_categories';

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE slug = %s",
        $slug
    ));
}

/**
 * Crée une nouvelle catégorie
 *
 * @param string $name Nom de la catégorie
 * @return array ['success' => bool, 'message' => string, 'id' => int]
 */
function gmb_create_category($name) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gmb_review_categories';

    // Générer le slug
    $slug = sanitize_title($name);

    // Vérifier si le slug existe déjà
    if (gmb_get_category_by_slug($slug)) {
        return array(
            'success' => false,
            'message' => 'Une catégorie avec ce nom existe déjà'
        );
    }

    $result = $wpdb->insert(
        $table_name,
        array(
            'name' => $name,
            'slug' => $slug
        ),
        array('%s', '%s')
    );

    if ($result !== false) {
        return array(
            'success' => true,
            'message' => 'Catégorie créée avec succès',
            'id' => $wpdb->insert_id
        );
    } else {
        return array(
            'success' => false,
            'message' => 'Erreur lors de la création de la catégorie',
            'error' => $wpdb->last_error
        );
    }
}

/**
 * Supprime une catégorie
 *
 * @param int $category_id ID de la catégorie
 * @return bool True si succès, false sinon
 */
function gmb_delete_category($category_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gmb_review_categories';
    $relation_table = $wpdb->prefix . 'gmb_review_category_relations';

    // Retirer la catégorie de tous les avis qui l'utilisent
    $wpdb->delete(
        $relation_table,
        array('category_id' => $category_id),
        array('%d')
    );

    return $wpdb->delete(
        $table_name,
        array('id' => $category_id),
        array('%d')
    ) !== false;
}

// ============================================================================
// FONCTIONS DE GESTION DES RELATIONS AVIS <-> CATÉGORIES
// ============================================================================

/**
 * Récupère les catégories d'un avis
 *
 * @param string $review_id ID de l'avis
 * @return array Liste des catégories
 */
function gmb_get_review_categories($review_id) {
    global $wpdb;
    $relation_table = $wpdb->prefix . 'gmb_review_category_relations';
    $categories_table = $wpdb->prefix . 'gmb_review_categories';

    return $wpdb->get_results($wpdb->prepare(
        "SELECT c.* FROM $categories_table c
         INNER JOIN $relation_table r ON c.id = r.category_id
         WHERE r.review_id = %s
         ORDER BY c.name ASC",
        $review_id
    ));
}

/**
 * Assigne des catégories à un avis
 *
 * @param string $review_id ID de l'avis
 * @param array $category_ids Tableau des IDs de catégories
 * @return bool True si succès
 */
function gmb_set_review_categories($review_id, $category_ids = array()) {
    global $wpdb;
    $relation_table = $wpdb->prefix . 'gmb_review_category_relations';

    // Supprimer toutes les anciennes relations
    $wpdb->delete(
        $relation_table,
        array('review_id' => $review_id),
        array('%s')
    );

    // Ajouter les nouvelles relations
    if (!empty($category_ids)) {
        foreach ($category_ids as $category_id) {
            if (!empty($category_id)) {
                $wpdb->insert(
                    $relation_table,
                    array(
                        'review_id' => $review_id,
                        'category_id' => absint($category_id)
                    ),
                    array('%s', '%d')
                );
            }
        }
    }

    return true;
}

/**
 * Ajoute une catégorie à un avis
 *
 * @param string $review_id ID de l'avis
 * @param int $category_id ID de la catégorie
 * @return bool True si succès
 */
function gmb_add_review_category($review_id, $category_id) {
    global $wpdb;
    $relation_table = $wpdb->prefix . 'gmb_review_category_relations';

    $result = $wpdb->insert(
        $relation_table,
        array(
            'review_id' => $review_id,
            'category_id' => absint($category_id)
        ),
        array('%s', '%d')
    );

    return $result !== false;
}

/**
 * Retire une catégorie d'un avis
 *
 * @param string $review_id ID de l'avis
 * @param int $category_id ID de la catégorie
 * @return bool True si succès
 */
function gmb_remove_review_category($review_id, $category_id) {
    global $wpdb;
    $relation_table = $wpdb->prefix . 'gmb_review_category_relations';

    return $wpdb->delete(
        $relation_table,
        array(
            'review_id' => $review_id,
            'category_id' => absint($category_id)
        ),
        array('%s', '%d')
    ) !== false;
}

// ============================================================================
// ACTIONS AJAX POUR LES CATÉGORIES
// ============================================================================

/**
 * Action AJAX pour créer une nouvelle catégorie
 */
function gmb_create_category_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permissions insuffisantes'));
        return;
    }

    check_ajax_referer('gmb_categories', 'nonce');

    if (!isset($_POST['category_name']) || empty($_POST['category_name'])) {
        wp_send_json_error(array('message' => 'Nom de catégorie requis'));
        return;
    }

    $category_name = sanitize_text_field(wp_unslash($_POST['category_name']));
    $result = gmb_create_category($category_name);

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_gmb_create_category', 'gmb_create_category_ajax');

/**
 * Action AJAX pour supprimer une catégorie
 */
function gmb_delete_category_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Permissions insuffisantes'));
        return;
    }

    check_ajax_referer('gmb_categories', 'nonce');

    if (!isset($_POST['category_id'])) {
        wp_send_json_error(array('message' => 'ID de catégorie requis'));
        return;
    }

    $category_id = absint($_POST['category_id']);
    $success = gmb_delete_category($category_id);

    if ($success) {
        wp_send_json_success(array('message' => 'Catégorie supprimée avec succès'));
    } else {
        wp_send_json_error(array('message' => 'Erreur lors de la suppression'));
    }
}
add_action('wp_ajax_gmb_delete_category', 'gmb_delete_category_ajax');
