<?php
/**
 * Reviews for Google My Business - Review management template
 *
 * Variables disponibles :
 * - $data (array) : Review data from the GMB API
 */

// Block direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap gmb-wrap">
    <?php include_once(WOLVES_GMB_PLUGIN_DIR . 'template-parts/header.php'); ?>

    <div class="gmb-container full-screen">
        <?php if (isset($data['error']) && $data['error']): ?>
            <div class="title">
                <h2><?php esc_html_e('Before start', 'reviews-for-google-my-business'); ?></h2>
                <p><?php esc_html_e('Before start, go to the Configuration page to connect your account Google Cloud Console', 'reviews-for-google-my-business'); ?></p>
            </div>
            <div class="card">
                <p><?php echo esc_html($data['message']); ?></p>
                <?php if (isset($data['api_response'])): ?>
                    <details>
                        <summary><?php esc_html_e('Error details', 'reviews-for-google-my-business'); ?></summary>
                        <pre><?php echo esc_html($data['api_response']); ?></pre>
                    </details>
                <?php endif; ?>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-settings')); ?>"
                       class="button button-primary">
                        <?php esc_html_e('Configure authentification', 'reviews-for-google-my-business'); ?>
                    </a>
                </p>
            </div>
        <?php elseif (empty($data['reviews'])): ?>
            <div class="card">
                <h2><?php esc_html_e('No reviews found', 'reviews-for-google-my-business'); ?></h2>
                <p><?php esc_html_e('No reviews have been synchronized yet. Click the button below to fetch and sync reviews from Google My Business API.', 'reviews-for-google-my-business'); ?></p>
                <div class="button-wrapper">
                    <button type="button" class="button button-primary" onclick="wgmbr_syncReviewsFromAPI()">
                        <?php esc_html_e('Sync Reviews from API', 'reviews-for-google-my-business'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gmb-settings')); ?>"
                       class="button button-secondary">
                        <?php esc_html_e('Check configuration', 'reviews-for-google-my-business'); ?>
                    </a>
                </div>
                <div id="sync-result"></div>
            </div>
        <?php else: ?>
            <div class="top-bar">
                <div class="head">
                    <h2>
                        <?php
                        printf(
                        /* translators: %s: Number of reviews */
                                esc_html(_n('Review found (%s)', 'Reviews found (%s)', $data['total'], 'reviews-for-google-my-business')),
                                esc_html(number_format_i18n($data['total']))
                        );
                        ?>
                    </h2>

                    <button type="button" class="button button-primary" onclick="wgmbr_syncReviewsFromAPI()">
                        <?php esc_html_e('Sync Reviews from API', 'reviews-for-google-my-business'); ?>
                    </button>
                </div>
                <div id="sync-result"></div>
            </div>


            <table class="wp-list-table widefat fixed striped">
                <thead>
                <tr>
                    <th style="width: 5%;"><?php esc_html_e('Photo', 'reviews-for-google-my-business'); ?></th>
                    <th style="width: 10%;"><?php esc_html_e('Name', 'reviews-for-google-my-business'); ?></th>
                    <th style="width: auto;"><?php esc_html_e('Rating', 'reviews-for-google-my-business'); ?></th>
                    <th style="width: auto;"><?php esc_html_e('Date', 'reviews-for-google-my-business'); ?></th>
                    <th style="width: 25%;"><?php esc_html_e('Job Title', 'reviews-for-google-my-business'); ?></th>
                    <th style="width: 20%;"><?php esc_html_e('Category', 'reviews-for-google-my-business'); ?></th>
                    <th style="width: auto;"><?php esc_html_e('Action', 'reviews-for-google-my-business'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data['reviews'] as $parsed):
                    // $parsed is already a parsed object from the CPT

                    // Unique ID for the form
                    $form_id = 'review-form-' . $parsed->post_id;
                    ?>
                    <tr id="<?php echo esc_attr($form_id); ?>">
                        <td>
                            <?php if ($parsed->photo): ?>
                                <img class="profil-picture" src="<?php echo esc_url($parsed->photo); ?>"
                                     alt="<?php echo esc_attr($parsed->name); ?>">
                            <?php else: ?>
                                <div class="profil-letters">
                                    <?php echo esc_html(substr($parsed->name, 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo esc_html($parsed->name); ?></strong></td>
                        <td>
                            <?php echo esc_html(str_repeat('★', $parsed->rating)); ?>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n('d/m/Y', $parsed->date)); ?>
                        </td>
                        <td>
                            <label>
                                <input type="text"
                                       class="gmb-job-input"
                                       data-post-id="<?php echo esc_attr($parsed->post_id); ?>"
                                       value="<?php echo esc_attr($parsed->job); ?>"
                                       placeholder="Ex: Web Developer">
                            </label>
                        </td>
                        <td>
                            <div class="gmb-categories-checkboxes"
                                 data-post-id="<?php echo esc_attr($parsed->post_id); ?>">
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
                                    <span class="gmb-categories-empty"><?php esc_html_e('No categories', 'reviews-for-google-my-business'); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <button type="button"
                                    class="button button-small button-primary gmb-save-review-btn"
                                    data-post-id="<?php echo esc_attr($parsed->post_id); ?>">
                                <?php esc_html_e('Save', 'reviews-for-google-my-business'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php
            // Native WordPress pagination
            if ($data['query']->max_num_pages > 1):
                $pagination_args = array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'current' => max(1, $data['paged']),
                        'total' => $data['query']->max_num_pages,
                        'prev_text' => '&laquo; ' . esc_html__('Previous', 'reviews-for-google-my-business'),
                        'next_text' => esc_html__('Next', 'reviews-for-google-my-business') . ' &raquo;',
                        'type' => 'list',
                        'end_size' => 3,
                        'mid_size' => 2,
                );
                ?>
                <div class="gmb-pagination">
                    <?php wp_kses_post(paginate_links($pagination_args)); ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<script>
    function wgmbr_syncReviewsFromAPI() {
        const resultDiv = document.getElementById('sync-result');
        const button = event.target;
        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = '<?php esc_html_e('Syncing...', 'reviews-for-google-my-business'); ?>';

        const formData = new FormData();
        formData.append('action', 'wgmbr_sync_reviews');
        formData.append('nonce', '<?php echo esc_attr(wp_create_nonce('wgmbr_admin_actions')); ?>');

        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.textContent = '<?php esc_html_e('✓ Synchronization complete', 'reviews-for-google-my-business'); ?>';
                    // Reload the page after 2 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    resultDiv.innerHTML = '<div class="gmb-notice error"><p> ' + (data.data?.message || '<?php esc_html_e('Error syncing reviews', 'reviews-for-google-my-business'); ?>') + '</p></div>';
                    button.disabled = false;
                    button.textContent = originalText;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="gmb-notice error"><p><?php esc_html_e('Network error:', 'reviews-for-google-my-business'); ?> ' + error.message + '</p></div>';
                button.disabled = false;
                button.textContent = originalText;
            });
    }

    // Simple AJAX backup
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
        formData.append('nonce', '<?php echo esc_attr(wp_create_nonce('wgmbr_save_review_job')); ?>');
        categories.forEach(id => formData.append('category_ids[]', id));

        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(d => {
                btn.textContent = d.success ? '<?php esc_html_e('Updated', 'reviews-for-google-my-business'); ?>' : '<?php esc_html_e('Error', 'reviews-for-google-my-business'); ?>';
                btn.className = d.success ? 'button button-small is-success' : 'button button-small is-error';
                setTimeout(() => {
                    btn.textContent = original;
                    btn.className = 'button button-small button-primary';
                    btn.disabled = false;
                }, 2000);
            })
            .catch(err => {
                console.error('Erreur:', err);
                btn.textContent = '<?php esc_html_e('Error', 'reviews-for-google-my-business'); ?>';
                btn.className = 'button button-small is-error';
                setTimeout(() => {
                    btn.textContent = original;
                    btn.className = 'button button-small button-primary';
                    btn.disabled = false;
                }, 2000);
            });
    });
</script>
