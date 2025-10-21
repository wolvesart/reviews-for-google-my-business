<?php
/**
 * Google My Business Reviews - Helper Functions
 * Fonctions utilitaires pour le traitement des données d'avis
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parse les données d'un avis Google et retourne un objet standardisé
 *
 * @param array $review Données brutes de l'avis depuis l'API GMB
 * @return object Objet contenant les données parsées de l'avis
 */
function gmb_parse_review_data($review) {
    $parsed = new stdClass();

    // Données du reviewer
    $reviewer = isset($review['reviewer']) ? $review['reviewer'] : array();
    $parsed->name = isset($reviewer['displayName']) ? $reviewer['displayName'] : 'Anonyme';
    $parsed->photo = isset($reviewer['profilePhotoUrl']) ? $reviewer['profilePhotoUrl'] : '';

    // Note (convertir le format API en nombre)
    $parsed->rating = isset($review['starRating']) ? (float) gmb_convert_star_rating($review['starRating']) : 0;

    // Commentaire (nettoyer la traduction Google)
    $parsed->comment = gmb_clean_review_comment($review);

    // Date
    $parsed->date = isset($review['createTime']) ? strtotime($review['createTime']) : time();

    // ID de l'avis
    $parsed->review_id = isset($review['reviewId']) ? $review['reviewId'] : (isset($review['name']) ? $review['name'] : '');

    // Données personnalisées (job)
    $custom_data = gmb_get_custom_review_data($parsed->review_id);
    $parsed->job = $custom_data ? $custom_data->job : '';

    // Catégories (many-to-many)
    $parsed->categories = gmb_get_review_categories($parsed->review_id);
    $parsed->category_ids = array_map(function($cat) { return $cat->id; }, $parsed->categories);
    $parsed->category_names = array_map(function($cat) { return $cat->name; }, $parsed->categories);

    return $parsed;
}

/**
 * Nettoie le commentaire d'un avis en retirant la traduction Google
 *
 * @param array $review Données de l'avis
 * @return string Commentaire nettoyé
 */
function gmb_clean_review_comment($review) {
    $comment = isset($review['comment']) ? $review['comment'] : '';

    // Retirer la partie "(Translated by Google)" ou "(Traduit par Google)"
    if (strpos($comment, '(Original)') !== false) {
        // Extraire uniquement la partie après "(Original)"
        if (preg_match('/\(Original\)\s*(.+)$/s', $comment, $matches)) {
            $comment = trim($matches[1]);
        }
    }

    return $comment;
}

/**
 * Filtre les avis par catégorie
 *
 * @param array $reviews Liste des avis bruts de l'API
 * @param string $category_slug Slug de la catégorie (vide = avis sans catégorie)
 * @return array Liste filtrée des avis
 */
function gmb_filter_reviews_by_category($reviews, $category_slug) {
    // Si la catégorie est une chaîne vide, on cherche les avis sans catégorie
    if ($category_slug === '') {
        return array_filter($reviews, function($review) {
            $parsed = gmb_parse_review_data($review);
            return empty($parsed->category_ids);
        });
    }

    // Récupérer la catégorie par son slug
    $category = gmb_get_category_by_slug($category_slug);

    if (!$category) {
        return array(); // Catégorie introuvable
    }

    // Filtrer les avis qui ont cette catégorie
    return array_filter($reviews, function($review) use ($category) {
        $parsed = gmb_parse_review_data($review);
        return in_array($category->id, $parsed->category_ids);
    });
}
