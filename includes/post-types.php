<?php
/**
 * Google My Business Reviews - Custom Post Types & Taxonomies
 * Enregistrement du CPT pour les avis et de la taxonomie pour les catégories
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// CUSTOM POST TYPE : GMB_REVIEW
// ============================================================================

/**
 * Enregistre le Custom Post Type pour les avis Google
 */
function wgmbr_register_review_post_type() {
    $labels = array(
        'name'                  => __('Google Reviews', 'wolves-avis-google'),
        'singular_name'         => __('Google Review', 'wolves-avis-google'),
        'menu_name'             => __('Google Reviews', 'wolves-avis-google'),
        'name_admin_bar'        => __('Google Review', 'wolves-avis-google'),
        'add_new'               => __('Add New', 'wolves-avis-google'),
        'add_new_item'          => __('Add New Review', 'wolves-avis-google'),
        'new_item'              => __('New Review', 'wolves-avis-google'),
        'edit_item'             => __('Edit Review', 'wolves-avis-google'),
        'view_item'             => __('View Review', 'wolves-avis-google'),
        'all_items'             => __('All Reviews', 'wolves-avis-google'),
        'search_items'          => __('Search Reviews', 'wolves-avis-google'),
        'not_found'             => __('No reviews found', 'wolves-avis-google'),
        'not_found_in_trash'    => __('No reviews found in Trash', 'wolves-avis-google'),
    );

    $args = array(
        'labels'                => $labels,
        'public'                => false,  // Pas accessible en frontend directement
        'publicly_queryable'    => false,
        'show_ui'               => false,  // Ne pas afficher l'interface native WordPress
        'show_in_menu'          => false,  // Ne pas créer de menu automatique
        'query_var'             => true,
        'rewrite'               => false,  // Pas de réécriture d'URL
        'capability_type'       => 'post',
        'has_archive'           => false,
        'hierarchical'          => false,
        'menu_position'         => null,
        'menu_icon'             => 'dashicons-star-filled',
        'supports'              => array('title', 'editor', 'custom-fields'),
        'show_in_rest'          => true,   // Support de l'éditeur Gutenberg et REST API
    );

    register_post_type('gmb_review', $args);
}
add_action('init', 'wgmbr_register_review_post_type');

// ============================================================================
// TAXONOMIE : GMB_CATEGORY
// ============================================================================

/**
 * Enregistre la taxonomie pour les catégories d'avis
 */
function wgmbr_register_category_taxonomy() {
    $labels = array(
        'name'                       => __('Review Categories', 'wolves-avis-google'),
        'singular_name'              => __('Review Category', 'wolves-avis-google'),
        'search_items'               => __('Search Categories', 'wolves-avis-google'),
        'popular_items'              => __('Popular Categories', 'wolves-avis-google'),
        'all_items'                  => __('All Categories', 'wolves-avis-google'),
        'edit_item'                  => __('Edit Category', 'wolves-avis-google'),
        'update_item'                => __('Update Category', 'wolves-avis-google'),
        'add_new_item'               => __('Add New Category', 'wolves-avis-google'),
        'new_item_name'              => __('New Category Name', 'wolves-avis-google'),
        'separate_items_with_commas' => __('Separate categories with commas', 'wolves-avis-google'),
        'add_or_remove_items'        => __('Add or remove categories', 'wolves-avis-google'),
        'choose_from_most_used'      => __('Choose from most used categories', 'wolves-avis-google'),
        'not_found'                  => __('No categories found', 'wolves-avis-google'),
        'menu_name'                  => __('Categories', 'wolves-avis-google'),
    );

    $args = array(
        'labels'                => $labels,
        'hierarchical'          => false,  // Comme les tags (pas de hiérarchie)
        'public'                => false,
        'show_ui'               => false,  // Ne pas afficher l'interface native
        'show_admin_column'     => false,  // Ne pas afficher dans la liste des posts
        'show_in_nav_menus'     => false,
        'show_tagcloud'         => false,
        'show_in_rest'          => true,   // Support REST API
        'rewrite'               => false,
    );

    register_taxonomy('gmb_category', array('gmb_review'), $args);
}
add_action('init', 'wgmbr_register_category_taxonomy');

// ============================================================================
// FONCTIONS HELPER POUR LE CPT
// ============================================================================

/**
 * Sauvegarde ou met à jour un avis Google en tant que CPT
 *
 * @param array $review_data Données de l'avis depuis l'API GMB
 * @return int|WP_Error ID du post créé/mis à jour ou WP_Error
 */
function wgmbr_save_review_as_post($review_data) {
    // Extraire les données
    $review_id = isset($review_data['reviewId']) ? $review_data['reviewId'] :
                 (isset($review_data['name']) ? $review_data['name'] : '');

    if (empty($review_id)) {
        return new WP_Error('missing_review_id', __('Review ID is missing', 'wolves-avis-google'));
    }

    // Vérifier si l'avis existe déjà
    $existing_post = wgmbr_get_review_by_review_id($review_id);

    // Données du reviewer
    $reviewer = isset($review_data['reviewer']) ? $review_data['reviewer'] : array();
    $reviewer_name = isset($reviewer['displayName']) ? $reviewer['displayName'] : __('Anonymous', 'wolves-avis-google');
    $reviewer_photo = isset($reviewer['profilePhotoUrl']) ? $reviewer['profilePhotoUrl'] : '';

    // Note
    $star_rating = isset($review_data['starRating']) ? $review_data['starRating'] : 'STAR_RATING_UNSPECIFIED';
    $rating = wgmbr_convert_star_rating($star_rating);

    // Commentaire
    $comment = isset($review_data['comment']) ? $review_data['comment'] : '';

    // Nettoyer la traduction Google
    if (strpos($comment, '(Original)') !== false) {
        if (preg_match('/\(Original\)\s*(.+)$/s', $comment, $matches)) {
            $comment = trim($matches[1]);
        }
    }

    // Date
    $review_date = isset($review_data['createTime']) ? $review_data['createTime'] : current_time('mysql');

    // Préparer les données du post
    $post_data = array(
        'post_title'    => sprintf(__('Review by %s', 'wolves-avis-google'), $reviewer_name),
        'post_content'  => $comment,
        'post_status'   => 'publish',
        'post_type'     => 'gmb_review',
        'post_date'     => date('Y-m-d H:i:s', strtotime($review_date)),
    );

    // Si le post existe, mettre à jour
    if ($existing_post) {
        $post_data['ID'] = $existing_post->ID;
        $post_id = wp_update_post($post_data);
    } else {
        $post_id = wp_insert_post($post_data);
    }

    if (is_wp_error($post_id)) {
        return $post_id;
    }

    // Sauvegarder les meta données
    update_post_meta($post_id, '_gmb_review_id', $review_id);
    update_post_meta($post_id, '_gmb_reviewer_name', $reviewer_name);
    update_post_meta($post_id, '_gmb_reviewer_photo', $reviewer_photo);
    update_post_meta($post_id, '_gmb_rating', $rating);
    update_post_meta($post_id, '_gmb_job', ''); // Sera rempli manuellement dans l'admin

    return $post_id;
}

/**
 * Récupère un avis par son review_id Google
 *
 * @param string $review_id ID de l'avis Google
 * @return WP_Post|null Post trouvé ou null
 */
function wgmbr_get_review_by_review_id($review_id) {
    $args = array(
        'post_type'      => 'gmb_review',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'meta_query'     => array(
            array(
                'key'     => '_gmb_review_id',
                'value'   => $review_id,
                'compare' => '='
            )
        ),
        'fields'         => 'ids'
    );

    $posts = get_posts($args);

    if (empty($posts)) {
        return null;
    }

    return get_post($posts[0]);
}

/**
 * Récupère tous les avis avec filtrage optionnel par catégorie
 *
 * @param array $args Arguments de WP_Query
 * @return WP_Query
 */
function wgmbr_get_reviews($args = array()) {
    $defaults = array(
        'post_type'      => 'gmb_review',
        'post_status'    => 'publish',
        'posts_per_page' => 50,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $args = wp_parse_args($args, $defaults);

    return new WP_Query($args);
}

/**
 * Convertit le format de notation GMB en numérique
 *
 * @param string $star_rating Format API Google (ONE, TWO, THREE, FOUR, FIVE)
 * @return int Note de 0 à 5
 */
function wgmbr_convert_star_rating($star_rating) {
    $ratings = array(
        'STAR_RATING_UNSPECIFIED' => 0,
        'ONE' => 1,
        'TWO' => 2,
        'THREE' => 3,
        'FOUR' => 4,
        'FIVE' => 5
    );

    return isset($ratings[$star_rating]) ? $ratings[$star_rating] : 0;
}
