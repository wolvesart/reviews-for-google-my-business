<?php
/**
 * Google My Business Reviews - Template de gestion des avis
 *
 * Variables disponibles :
 * - $data (array) : Données des avis depuis l'API GMB
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Traiter la sauvegarde du formulaire
if (isset($_POST['gmb_save_review_job']) && check_admin_referer('gmb_save_review_job', 'gmb_review_nonce')) {
    $review_id = sanitize_text_field(wp_unslash($_POST['review_id']));
    $reviewer_name = sanitize_text_field(wp_unslash($_POST['reviewer_name']));
    $job = sanitize_text_field(wp_unslash($_POST['job']));

    // Récupérer les catégories sélectionnées (tableau)
    $category_ids = isset($_POST['category_ids']) && is_array($_POST['category_ids'])
            ? array_map('absint', $_POST['category_ids'])
            : array();

    $result = gmb_save_custom_review_data($review_id, $reviewer_name, $job, $category_ids);

    if ($result['success']) {
        echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
    } else {
        $error_msg = $result['message'];
        if (isset($result['error']) && $result['error']) {
            $error_msg .= ' - ' . $result['error'];
        }
        echo '<div class="notice notice-error"><p>' . esc_html($error_msg) . '</p></div>';
    }

    // Vider le cache pour forcer le rafraîchissement
    delete_transient('gmb_reviews_cache');

    // Recharger les données
    $data = gmb_fetch_reviews();
}

// Récupérer toutes les catégories
$categories = gmb_get_all_categories();
?>

<div class="wrap gmb-admin-wrap">
    <?php include_once(WOLVES_GMB_PLUGIN_DIR . 'template-parts/header.php'); ?>

    <div class="gmb-admin-container full-screen">
        <?php if (isset($data['error']) && $data['error']): ?>
        <div class="title">
            <h2><?php _e('Before start', 'wolves-avis-google'); ?></h2>
            <p><?php _e('Before start, go to the Configuration page to connect your account Google Cloud Console', 'wolves-avis-google'); ?></p>
        </div>
            <div class="card">
                <p><?php echo esc_html($data['message']); ?></p>
                <?php if (isset($data['api_response'])): ?>
                    <details>
                        <summary>Détails de l'erreur</summary>
                        <pre><?php echo esc_html(print_r($data['api_response'], true)); ?></pre>
                    </details>
                <?php endif; ?>
                <p><a href="<?php echo esc_url(admin_url('admin.php?page=gmb-settings')); ?>" class="button button-primary">
                        <?php _e('Configure authentification', 'wolves-avis-google'); ?>
                    </a>
                </p>
            </div>
        <?php elseif (empty($data['reviews'])): ?>
            <div>
                <p>Aucun avis trouvé. Vérifiez votre configuration.</p>
                <p><a href="<?php echo esc_url(admin_url('admin.php?page=gmb-settings')); ?>" class="button">Vérifier la
                        configuration</a></p>
            </div>
        <?php else: ?>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                <tr>
                    <th style="width: 50px;">Photo</th>
                    <th style="width: 100px;">Nom</th>
                    <th style="width: 100px;">Note</th>
                    <th style="width: 100px;">Date</th>
                    <th style="width: 200px;">Poste</th>
                    <th style="width: 150px;">Catégorie</th>
                    <th style="width: 80px;">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data['reviews'] as $review):
                    // Parser les données de l'avis
                    $parsed = gmb_parse_review_data($review);

                    // ID unique pour le formulaire
                    $form_id = 'review-form-' . md5($parsed->review_id);
                    ?>
                    <tr id="<?php echo esc_attr($form_id); ?>">
                        <td>
                            <?php if ($parsed->photo): ?>
                                <img src="<?php echo esc_url($parsed->photo); ?>"
                                     alt="<?php echo esc_attr($parsed->name); ?>"
                                     style="width: 40px; height: 40px; border-radius: 50%;">
                            <?php else: ?>
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #ccc; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                    <?php echo esc_html(substr($parsed->name, 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo esc_html($parsed->name); ?></strong></td>
                        <td>
                            <?php echo str_repeat('⭐', $parsed->rating); ?>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n('d/m/Y', $parsed->date)); ?>
                        </td>
                        <td>
                            <form method="post" style="display: flex; gap: 5px;">
                                <?php wp_nonce_field('gmb_save_review_job', 'gmb_review_nonce'); ?>
                                <input type="hidden" name="review_id"
                                       value="<?php echo esc_attr($parsed->review_id); ?>">
                                <input type="hidden" name="reviewer_name"
                                       value="<?php echo esc_attr($parsed->name); ?>">
                                <input type="text"
                                       name="job"
                                       value="<?php echo esc_attr($parsed->job); ?>"
                                       placeholder="Ex: Développeur web"
                                       style="width: 100%;">
                        </td>
                        <td>
                            <div class="gmb-categories-checkboxes">
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <label class="gmb-category-checkbox">
                                            <input type="checkbox"
                                                   name="category_ids[]"
                                                   value="<?php echo esc_attr($cat->id); ?>"
                                                    <?php echo in_array($cat->id, $parsed->category_ids) ? 'checked' : ''; ?>>
                                            <span class="gmb-category-label"><?php echo esc_html($cat->name); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span style="color: #999; font-size: 12px;">Aucune catégorie</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <button type="submit" name="gmb_save_review_job" class="button button-small button-primary">
                                Enregistrer
                            </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </div>
</div>
