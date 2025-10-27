<?php
/**
 * Reviews for Google My Business - Helper Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parse les données d'un avis CPT et retourne un objet standardisé
 *
 * @param WP_Post|int $post Post object ou ID du post
 * @return object|null Objet contenant les données parsées de l'avis ou null
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

    // Données du reviewer
    $parsed->name = get_post_meta($post->ID, '_gmb_reviewer_name', true);
    if (empty($parsed->name)) {
        $parsed->name = esc_html__('Anonymous', 'reviews-for-google-my-business');
    }

    $parsed->photo = get_post_meta($post->ID, '_gmb_reviewer_photo', true);

    // Note
    $parsed->rating = (float)get_post_meta($post->ID, '_gmb_rating', true);

    // Commentaire
    $parsed->comment = $post->post_content;

    // Date
    $parsed->date = strtotime($post->post_date);

    // ID de l'avis Google
    $parsed->review_id = get_post_meta($post->ID, '_gmb_review_id', true);

    // Données personnalisées (job)
    $parsed->job = get_post_meta($post->ID, '_gmb_job', true);

    // Catégories (taxonomie)
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

    // ID du post WordPress
    $parsed->post_id = $post->ID;

    return $parsed;
}

/**
 * Récupère tous les avis avec filtrage optionnel
 *
 * @param array $args Arguments personnalisés
 * @return array Tableau d'objets parsés
 */
function wgmbr_get_all_reviews($args = array())
{
    $defaults = array(
        'post_type' => 'gmb_review',
        'post_status' => 'publish',
        'posts_per_page' => 50,
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
 * Récupère tous les avis avec filtrage optionnel et retourne l'objet WP_Query
 * Utilisé pour la pagination dans l'admin
 *
 * @param array $args Arguments personnalisés
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
 * Récupère les avis filtrés par catégorie
 *
 * @param string|array $category_slug Slug de la catégorie (vide = avis sans catégorie, tableau = plusieurs catégories)
 * @param int $limit Nombre d'avis à récupérer
 * @return array Tableau d'objets parsés
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

    // Si la catégorie est une chaîne vide, chercher les avis sans catégorie
    if ($category_slug === '') {
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary to filter reviews by taxonomy, standard WordPress method
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'gmb_category',
                'operator' => 'NOT EXISTS',
            ),
        );
    } else {
        // Filtrer par slug de catégorie (supporte string ou array)
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary to filter reviews by category, standard WordPress method
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'gmb_category',
                'field' => 'slug',
                'terms' => $category_slug, // WordPress accepte string ou array
                'operator' => 'IN', // IN = au moins une des catégories
            ),
        );
    }

    return wgmbr_get_all_reviews($args);
}

/**
 * Calcule la note moyenne de tous les avis
 *
 * @return float Note moyenne
 */
function wgmbr_get_average_rating()
{
    $args = array(
        'post_type' => 'gmb_review',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
    );

    $posts = get_posts($args);

    if (empty($posts)) {
        return 0;
    }

    $total = 0;
    $count = 0;

    foreach ($posts as $post_id) {
        $rating = get_post_meta($post_id, '_gmb_rating', true);
        if ($rating) {
            $total += (float)$rating;
            $count++;
        }
    }

    return $count > 0 ? ($total / $count) : 0;
}

/**
 * Compte le nombre total d'avis
 *
 * @return int Nombre total d'avis
 */
function wgmbr_get_total_reviews_count()
{
    $count = wp_count_posts('gmb_review');
    return isset($count->publish) ? (int)$count->publish : 0;
}

/**
 * Met à jour le job d'un avis
 *
 * @param int $post_id ID du post
 * @param string $job Poste de la personne
 * @return bool True si succès
 */
function wgmbr_update_review_job($post_id, $job)
{
    return update_post_meta($post_id, '_gmb_job', sanitize_text_field($job));
}

/**
 * Assigne des catégories à un avis
 *
 * @param int $post_id ID du post
 * @param array $category_ids Tableau des IDs de catégories (term_ids)
 * @return array|WP_Error Array of term taxonomy IDs ou WP_Error
 */
function wgmbr_set_review_categories($post_id, $category_ids = array())
{
    if (empty($category_ids)) {
        // Retirer toutes les catégories
        return wp_set_post_terms($post_id, array(), 'gmb_category');
    }

    // Assigner les catégories
    return wp_set_post_terms($post_id, $category_ids, 'gmb_category');
}

/**
 * Récupère un avis parsé par son review_id Google
 * Note: utilise wgmbr_get_review_post_by_review_id() de post-types.php
 *
 * @param string $review_id ID de l'avis Google
 * @return object|null Objet parsé de l'avis ou null
 */
function wgmbr_get_parsed_review_by_review_id($review_id)
{
    // Utiliser la fonction de post-types.php qui retourne le WP_Post
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

//Create php component
function wgmbr_get_template_parts($path, $params = [])
{
    include WOLVES_GMB_PLUGIN_DIR . $path . '.php';
}