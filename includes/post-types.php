<?php
/**
 * Reviews for Google My Business - Custom Post Types & Taxonomies
 * Registration of CPT for reviews and taxonomy for categories
 */

// Prevent direct access
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
        'name'                  => esc_html__('Google Reviews', 'reviews-for-google-my-business'),
        'singular_name'         => esc_html__('Google Review', 'reviews-for-google-my-business'),
        'menu_name'             => esc_html__('Google Reviews', 'reviews-for-google-my-business'),
        'name_admin_bar'        => esc_html__('Google Review', 'reviews-for-google-my-business'),
        'add_new'               => esc_html__('Add New', 'reviews-for-google-my-business'),
        'add_new_item'          => esc_html__('Add New Review', 'reviews-for-google-my-business'),
        'new_item'              => esc_html__('New Review', 'reviews-for-google-my-business'),
        'edit_item'             => esc_html__('Edit Review', 'reviews-for-google-my-business'),
        'view_item'             => esc_html__('View Review', 'reviews-for-google-my-business'),
        'all_items'             => esc_html__('All Reviews', 'reviews-for-google-my-business'),
        'search_items'          => esc_html__('Search Reviews', 'reviews-for-google-my-business'),
        'not_found'             => esc_html__('No reviews found', 'reviews-for-google-my-business'),
        'not_found_in_trash'    => esc_html__('No reviews found in Trash', 'reviews-for-google-my-business'),
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
add_action('init', 'wgmbr_register_review_post_type', 0); // Priority 0: Register CPT very early

// ============================================================================
// TAXONOMIE : GMB_CATEGORY
// ============================================================================

/**
 * Enregistre la taxonomie pour les catégories d'avis
 */
function wgmbr_register_category_taxonomy() {
    $labels = array(
        'name'                       => esc_html__('Review Categories', 'reviews-for-google-my-business'),
        'singular_name'              => esc_html__('Review Category', 'reviews-for-google-my-business'),
        'search_items'               => esc_html__('Search Categories', 'reviews-for-google-my-business'),
        'popular_items'              => esc_html__('Popular Categories', 'reviews-for-google-my-business'),
        'all_items'                  => esc_html__('All Categories', 'reviews-for-google-my-business'),
        'edit_item'                  => esc_html__('Edit Category', 'reviews-for-google-my-business'),
        'update_item'                => esc_html__('Update Category', 'reviews-for-google-my-business'),
        'add_new_item'               => esc_html__('Add New Category', 'reviews-for-google-my-business'),
        'new_item_name'              => esc_html__('New Category Name', 'reviews-for-google-my-business'),
        'separate_items_with_commas' => esc_html__('Separate categories with commas', 'reviews-for-google-my-business'),
        'add_or_remove_items'        => esc_html__('Add or remove categories', 'reviews-for-google-my-business'),
        'choose_from_most_used'      => esc_html__('Choose from most used categories', 'reviews-for-google-my-business'),
        'not_found'                  => esc_html__('No categories found', 'reviews-for-google-my-business'),
        'menu_name'                  => esc_html__('Categories', 'reviews-for-google-my-business'),
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
add_action('init', 'wgmbr_register_category_taxonomy', 1); // Priority 1: Register taxonomy after CPT

// ============================================================================
// FONCTIONS HELPER POUR LE CPT
// ============================================================================

/**
 * Save or update a Google review as CPT
 *
 * @param array $review_data Review data from GMB API
 * @return int|WP_Error Created/updated post ID or WP_Error
 */
function wgmbr_save_review_as_post($review_data) {
    // Extract data
    $review_id = isset($review_data['reviewId']) ? $review_data['reviewId'] :
                 (isset($review_data['name']) ? $review_data['name'] : '');

    if (empty($review_id)) {
        return new WP_Error('missing_review_id', esc_html__('Review ID is missing', 'reviews-for-google-my-business'));
    }

    // Check if review already exists
    $existing_post = wgmbr_get_review_by_review_id($review_id);

    // Reviewer data
    $reviewer = isset($review_data['reviewer']) ? $review_data['reviewer'] : array();
    $reviewer_name = isset($reviewer['displayName']) ? $reviewer['displayName'] : esc_html__('Anonymous', 'reviews-for-google-my-business');
    $reviewer_photo = isset($reviewer['profilePhotoUrl']) ? $reviewer['profilePhotoUrl'] : '';

    // Rating
    $star_rating = isset($review_data['starRating']) ? $review_data['starRating'] : 'STAR_RATING_UNSPECIFIED';
    $rating = wgmbr_convert_star_rating($star_rating);

    // Comment
    $comment = isset($review_data['comment']) ? $review_data['comment'] : '';

    // Clean Google translation
    if (strpos($comment, '(Original)') !== false) {
        if (preg_match('/\(Original\)\s*(.+)$/s', $comment, $matches)) {
            $comment = trim($matches[1]);
        }
    }

    // Date
    $review_date = isset($review_data['createTime']) ? $review_data['createTime'] : current_time('mysql');

    // Prepare post data
    $post_data = array(
        /* translators: %s: Reviewer name */
        'post_title'    => sprintf(esc_html__('Review by %s', 'reviews-for-google-my-business'), $reviewer_name),
        'post_content'  => $comment,
        'post_status'   => 'publish',
        'post_type'     => 'gmb_review',
        'post_date'     => wp_date('Y-m-d H:i:s', strtotime($review_date)),
    );

    // If post exists, update it
    if ($existing_post) {
        $post_data['ID'] = $existing_post->ID;
        $post_id = wp_update_post($post_data);
    } else {
        $post_id = wp_insert_post($post_data);
    }

    if (is_wp_error($post_id)) {
        return $post_id;
    }

    // Save meta data
    update_post_meta($post_id, '_gmb_review_id', $review_id);
    update_post_meta($post_id, '_gmb_reviewer_name', $reviewer_name);
    update_post_meta($post_id, '_gmb_reviewer_photo', $reviewer_photo);
    update_post_meta($post_id, '_gmb_rating', $rating);
    update_post_meta($post_id, '_gmb_job', ''); // Will be filled manually in admin

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
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary to find existing review by unique Google review ID to prevent duplicates during sync
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
