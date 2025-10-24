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
?>

<div class="wrap gmb-wrap">
    <?php include_once(WOLVES_GMB_PLUGIN_DIR . 'template-parts/header.php'); ?>

    <div class="gmb-container full-screen">
        <?php if (isset($data['error']) && $data['error']): ?>
            <div class="title">
                <h2><?php _e('Before start', 'wolves-avis-google'); ?></h2>
                <p><?php _e('Before start, go to the Configuration page to connect your account Google Cloud Console', 'wolves-avis-google'); ?></p>
            </div>
            <div class="card">
                <p><?php echo esc_html($data['message']); ?></p>
                <?php if (isset($data['api_response'])): ?>
                    <details>
                        <summary><?php _e('Error details', 'wolves-avis-google'); ?></summary>
                        <pre><?php echo esc_html(print_r($data['api_response'], true)); ?></pre>
                    </details>
                <?php endif; ?>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-settings')); ?>"
                       class="button button-primary">
                        <?php _e('Configure authentication', 'wolves-avis-google'); ?>
                    </a>
                </p>
            </div>
        <?php elseif (empty($data['reviews'])): ?>
            <div class="card">
                <h2><?php _e('No reviews found', 'wolves-avis-google'); ?></h2>
                <p><?php _e('No reviews have been synchronized yet. Click the button below to fetch and sync reviews from Google My Business API.', 'wolves-avis-google'); ?></p>
                <div class="button-wrapper">
                    <button type="button" class="button button-primary" onclick="wgmbr_syncReviewsFromAPI()">
                        <?php _e('Sync Reviews from API', 'wolves-avis-google'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-settings')); ?>"
                       class="button button-secondary">
                        <?php _e('Check configuration', 'wolves-avis-google'); ?>
                    </a>
                </div>
                <div id="sync-result"></div>
            </div>
        <?php else: ?>
            <div class="top-bar">
                <div>
                    <p>
                        <strong><?php echo count($data['reviews']); ?></strong> <?php _e('Reviews found', 'wolves-avis-google'); ?>
                    </p>
                </div>
                <div id="sync-result"></div>
                <button type="button" class="button button-primary" onclick="wgmbr_syncReviewsFromAPI()">
                    <?php _e('Sync Reviews from API', 'wolves-avis-google'); ?>
                </button>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                <tr>
                    <th style="width: 5%;"><?php _e('Photo', 'wolves-avis-google'); ?></th>
                    <th style="width: 10%;"><?php _e('Name', 'wolves-avis-google'); ?></th>
                    <th style="width: auto;"><?php _e('Rating', 'wolves-avis-google'); ?></th>
                    <th style="width: auto;"><?php _e('Date', 'wolves-avis-google'); ?></th>
                    <th style="width: 25%;"><?php _e('Job Title', 'wolves-avis-google'); ?></th>
                    <th style="width: 20%;"><?php _e('Category', 'wolves-avis-google'); ?></th>
                    <th style="width: auto;"><?php _e('Action', 'wolves-avis-google'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data['reviews'] as $parsed):
                    // $parsed est déjà un objet parsé depuis le CPT

                    // ID unique pour le formulaire
                    $form_id = 'review-form-' . $parsed->post_id;
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
                            <input type="text"
                                   class="gmb-job-input"
                                   data-post-id="<?php echo esc_attr($parsed->post_id); ?>"
                                   value="<?php echo esc_attr($parsed->job); ?>"
                                   placeholder="Ex: Web Developer"
                                   style="width: 100%;">
                        </td>
                        <td>
                            <div class="gmb-categories-checkboxes" data-post-id="<?php echo esc_attr($parsed->post_id); ?>">
                                <?php if (!empty($categories) && !is_wp_error($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <label class="gmb-category-checkbox">
                                            <input type="checkbox"
                                                   name="category_ids[]"
                                                   value="<?php echo esc_attr($cat->term_id); ?>"
                                                    <?php echo in_array($cat->term_id, $parsed->category_ids) ? 'checked' : ''; ?>>
                                            <span class="gmb-category-label"><?php echo esc_html($cat->name); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span style="color: #999; font-size: 12px;"><?php _e('No categories', 'wolves-avis-google'); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <button type="button"
                                    class="button button-small button-primary gmb-save-review-btn"
                                    data-post-id="<?php echo esc_attr($parsed->post_id); ?>">
                                <?php _e('Save', 'wolves-avis-google'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </div>
</div>

<script>
    function wgmbr_syncReviewsFromAPI() {
        const resultDiv = document.getElementById('sync-result');
        const button = event.target;
        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = '<?php _e('Syncing...', 'wolves-avis-google'); ?>';

        fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=wgmbr_sync_reviews')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.textContent = '<?php _e('✓ Synchronization complete', 'wolves-avis-google'); ?>';
                    // Recharger la page après 2 secondes
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    resultDiv.innerHTML = '<div class="gmb_notice error"><p> ' + (data.data?.message || '<?php _e('Error syncing reviews', 'wolves-avis-google'); ?>') + '</p></div>';
                    button.disabled = false;
                    button.textContent = originalText;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="gmb_notice error"><p><?php _e('Network error:', 'wolves-avis-google'); ?> ' + error.message + '</p></div>';
                button.disabled = false;
                button.textContent = originalText;
            });
    }

    // Sauvegarde AJAX simple
    document.addEventListener('click', (e) => {
        if (!e.target.matches('.gmb-save-review-btn')) return;

        const btn = e.target;
        const postId = btn.dataset.postId;
        const row = btn.closest('tr');
        const job = row.querySelector('.gmb-job-input').value;
        const categories = Array.from(row.querySelectorAll('input[name="category_ids[]"]:checked')).map(cb => cb.value);
        const original = btn.textContent;

        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'wgmbr_save_review');
        formData.append('post_id', postId);
        formData.append('job', job);
        formData.append('nonce', '<?php echo wp_create_nonce('wgmbr_save_review_job'); ?>');
        categories.forEach(id => formData.append('category_ids[]', id));

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(d => {
            btn.textContent = d.success ? '<?php _e('Updated', 'wolves-avis-google'); ?>' : '<?php _e('Error', 'wolves-avis-google'); ?>';
            btn.className = d.success ? 'button button-small is-success' : 'button button-small is-error';
            setTimeout(() => {
                btn.textContent = original;
                btn.className = 'button button-small button-primary';
                btn.disabled = false;
            }, 2000);
        })
        .catch(err => {
            console.error('Erreur:', err);
            btn.textContent = '<?php _e('Error', 'wolves-avis-google'); ?>';
            btn.className = 'button button-small is-error';
            setTimeout(() => {
                btn.textContent = original;
                btn.className = 'button button-small button-primary';
                btn.disabled = false;
            }, 2000);
        });
    });
</script>
