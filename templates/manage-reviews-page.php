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
    <?php include_once(WGMBR_PLUGIN_DIR . 'template-parts/header.php'); ?>

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
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wgmbr-settings')); ?>"
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
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wgmbr-settings')); ?>"
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
                <?php foreach ($data['reviews'] as $wgmbr_parsed):
                    // $wgmbr_parsed is already a parsed object from the CPT

                    // Unique ID for the form
                    $wgmbr_form_id = 'review-form-' . $wgmbr_parsed->post_id;
                    ?>
                    <tr id="<?php echo esc_attr($wgmbr_form_id); ?>">
                        <td>
                            <?php if ($wgmbr_parsed->photo): ?>
                                <img class="profil-picture" src="<?php echo esc_url($wgmbr_parsed->photo); ?>"
                                     alt="<?php echo esc_attr($wgmbr_parsed->name); ?>">
                            <?php else: ?>
                                <div class="profil-letters">
                                    <?php echo esc_html(substr($wgmbr_parsed->name, 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo esc_html($wgmbr_parsed->name); ?></strong></td>
                        <td>
                            <?php echo esc_html(str_repeat('★', $wgmbr_parsed->rating)); ?>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n('d/m/Y', $wgmbr_parsed->date)); ?>
                        </td>
                        <td>
                            <label>
                                <input type="text"
                                       class="gmb-job-input"
                                       data-post-id="<?php echo esc_attr($wgmbr_parsed->post_id); ?>"
                                       value="<?php echo esc_attr($wgmbr_parsed->job); ?>"
                                       placeholder="Ex: Web Developer">
                            </label>
                        </td>
                        <td>
                            <div class="gmb-categories-checkboxes"
                                 data-post-id="<?php echo esc_attr($wgmbr_parsed->post_id); ?>">
                                <?php if (!empty($categories) && !is_wp_error($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <label class="gmb-category-checkbox">
                                            <input type="checkbox"
                                                   name="category_ids[]"
                                                   value="<?php echo esc_attr($cat->term_id); ?>"
                                                    <?php echo in_array($cat->term_id, $wgmbr_parsed->category_ids) ? 'checked' : ''; ?>>
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
                                    data-post-id="<?php echo esc_attr($wgmbr_parsed->post_id); ?>">
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
                $wgmbr_pagination_args = array(
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
                    <?php echo wp_kses_post(paginate_links($wgmbr_pagination_args)); ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>
