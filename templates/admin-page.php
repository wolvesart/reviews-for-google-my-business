<?php
/**
 * Google My Business Reviews - Template de la page admin
 *
 * Variables disponibles :
 * - $has_token
 * - $available_locations
 * - $current_account_id
 * - $current_location_id
 * - $has_credentials
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap gmb-wrap">
    <?php include_once(WOLVES_GMB_PLUGIN_DIR . 'template-parts/header.php'); ?>

    <div class="gmb-container">
        <!-- Sidebar avec tabs -->
        <div class="gmb-sidebar">
            <nav class="gmb-tabs-nav">

                <button class="gmb-tab-button active" data-tab="configuration">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php _e('Configuration', 'wolves-avis-google'); ?>
                </button>
                <button class="gmb-tab-button" data-tab="usage">
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php _e('Usage', 'wolves-avis-google'); ?>
                </button>
                <button class="gmb-tab-button" data-tab="customization">
                    <span class="dashicons dashicons-admin-customizer"></span>
                    <?php _e('Customization', 'wolves-avis-google'); ?>
                </button>
                <button class="gmb-tab-button" data-tab="help">
                    <span class="dashicons dashicons-admin-page"></span>
                    <?php _e('Documentation', 'wolves-avis-google'); ?>
                </button>
            </nav>
        </div>

        <!-- Contenu principal -->
        <div class="gmb-content">

            <!-- Tab: Configuration -->
            <div class="gmb-tab-content active" data-tab-content="configuration">
                <div class="section row">
                    <div class="card-list">
                        <div class="card">
                            <h2><?php _e('1. Google API Configuration', 'wolves-avis-google'); ?></h2>

                            <?php if ($has_credentials): ?>
                                <div class="gmb_notice success">
                                    <p><?php _e('API credentials configured', 'wolves-avis-google'); ?></p>
                                </div>
                            <?php else: ?>
                                <div class="gmb_notice warning">
                                    <strong><?php _e('API credentials not configured', 'wolves-avis-google'); ?></strong><br>
                                    <?php _e('You must first configure your Google Cloud credentials to use this feature.', 'wolves-avis-google'); ?>
                                </div>
                            <?php endif; ?>

                            <details <?php echo !$has_credentials ? 'open' : ''; ?>>
                                <summary style="cursor: pointer; font-weight: bold; padding: 10px 0;">
                                    <?php echo $has_credentials ? __('Edit API credentials', 'wolves-avis-google') : __('Add API credentials', 'wolves-avis-google'); ?>
                                </summary>

                                <form method="post" class="wgmbr_form"
                                      action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                    <?php wp_nonce_field('wgmbr_save_credentials', 'wgmbr_credentials_nonce'); ?>
                                    <input type="hidden" name="action" value="wgmbr_save_credentials">

                                    <table class="form-table">
                                        <tr>
                                            <th scope="row">
                                                <label for="wgmbr_client_id">Client ID</label>
                                            </th>
                                            <td>
                                                <input type="text"
                                                       name="wgmbr_client_id"
                                                       id="wgmbr_client_id"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr(GMB_CLIENT_ID); ?>"
                                                       placeholder="123456789.apps.googleusercontent.com"
                                                       required>
                                                <p class="description">
                                                    <?php _e('Your Google Cloud Client ID must end with', 'wolves-avis-google'); ?>
                                                    <code>.apps.googleusercontent.com</code>
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="wgmbr_client_secret">Client Secret</label>
                                            </th>
                                            <td>
                                                <input type="password"
                                                       name="wgmbr_client_secret"
                                                       id="wgmbr_client_secret"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr(GMB_CLIENT_SECRET); ?>"
                                                       placeholder="GOCSPX-xxxxxxxxxxxx"
                                                       required>
                                                <p class="description">
                                                    <?php _e('Your Google Cloud Client Secret', 'wolves-avis-google'); ?>
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="wgmbr_redirect_uri">Redirect URI</label>
                                            </th>
                                            <td>
                                                <input type="url"
                                                       name="wgmbr_redirect_uri"
                                                       id="wgmbr_redirect_uri"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr(GMB_REDIRECT_URI); ?>"
                                                       placeholder="<?php echo esc_attr(admin_url('admin.php?page=gmb-settings&wgmbr_auth=1')); ?>"
                                                       required>
                                                <p class="description">
                                                    <?php _e('Default:', 'wolves-avis-google'); ?>
                                                    <code><?php echo esc_html(admin_url('admin.php?page=gmb-settings&wgmbr_auth=1')); ?></code><br>
                                                    ⚠️ <?php _e('This URI must match the one configured in Google Cloud', 'wolves-avis-google'); ?>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>

                                    <p class="submit">
                                        <button type="submit" class="button button-primary">
                                            <?php _e('Save credentials', 'wolves-avis-google'); ?>
                                        </button>
                                    </p>
                                </form>
                            </details>
                        </div>

                        <div class="card">
                            <h2><?php _e('2. Google My Business OAuth Authentication', 'wolves-avis-google'); ?></h2>

                            <?php if (!$has_credentials): ?>
                                <div class="gmb_notice warning">
                                    <strong><?php _e('Please configure your API credentials above before authenticating.', 'wolves-avis-google'); ?></strong>
                                </div>
                            <?php elseif ($has_token): ?>
                                <div class="gmb_notice success">
                                    <p><?php _e('Authenticated', 'wolves-avis-google'); ?></p></div>
                                <p>
                                    <a href="<?php echo esc_url(wgmbr_get_auth_url()); ?>"
                                       class="button button-primary">
                                        <?php _e('Re-authenticate', 'wolves-avis-google'); ?>
                                    </a>
                                    <button type="button" class="button button-secondary"
                                            onclick="if(confirm('<?php echo esc_js(__('Delete tokens?', 'wolves-avis-google')); ?>')) location.href='<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=wgmbr_revoke'), 'wgmbr_revoke_action')); ?>'">
                                        ⨉ <?php _e('Revoke access', 'wolves-avis-google'); ?>
                                    </button>
                                </p>
                            <?php else: ?>
                                <div class="gmb_notice warning">
                                    <p><?php _e('Not authenticated', 'wolves-avis-google'); ?></p></div>
                                <p>
                                    <a href="<?php echo esc_url(wgmbr_get_auth_url()); ?>"
                                       class="button button-primary">
                                        <?php _e('Connect with Google', 'wolves-avis-google'); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="card">
                            <h2><?php _e('3. Select your location', 'wolves-avis-google'); ?></h2>
                            <?php if ($has_token && !empty($available_locations)): ?>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                    <?php wp_nonce_field('wgmbr_save_location', 'wgmbr_location_nonce'); ?>
                                    <input type="hidden" name="action" value="wgmbr_save_location">

                                    <table class="form-table">
                                        <tr>
                                            <th scope="row">
                                                <label for="wgmbr_location_select"><?php _e('Location Google My Business', 'wolves-avis-google'); ?></label>
                                            </th>
                                            <td>
                                                <select name="wgmbr_location" id="wgmbr_location_select"
                                                        class="regular-text"
                                                        required>
                                                    <option value=""><?php _e('-- Select a location --', 'wolves-avis-google'); ?></option>
                                                    <?php foreach ($available_locations as $location): ?>
                                                        <?php
                                                        $value = $location['account_id'] . '|' . $location['location_id'];
                                                        $selected = ($location['account_id'] === $current_account_id && $location['location_id'] === $current_location_id) ? 'selected' : '';
                                                        ?>
                                                        <option value="<?php echo esc_attr($value); ?>" <?php echo $selected; ?>>
                                                            <?php echo esc_html($location['location_title']); ?>
                                                            (<?php echo esc_html($location['account_name']); ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <p class="description">
                                                    <?php _e('Select the Google My Business location whose reviews you want to display.', 'wolves-avis-google'); ?>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>

                                    <p class="submit">
                                        <button type="submit" class="button button-primary">
                                            <?php _e('Save location', 'wolves-avis-google'); ?>
                                        </button>
                                        <button type="button" class="button button-secondary"
                                                onclick="refreshLocations()">
                                            <?php _e('Refresh list', 'wolves-avis-google'); ?>
                                        </button>
                                    </p>
                                </form>
                            <?php elseif ($has_token && empty($available_locations)): ?>
                                <p><?php _e('No locations were found. Click the button below to search for your Google My Business locations.', 'wolves-avis-google'); ?></p>
                                <p>
                                    <button type="button" class="button button-primary" onclick="refreshLocations()">
                                        <?php _e('Search my locations', 'wolves-avis-google'); ?>
                                    </button>
                                </p>
                            <?php else: ?>
                                <p class="gmb_notice warning">
                                    <strong><?php _e('You must first authenticate yourself', 'wolves-avis-google'); ?></strong><br>
                                    <?php _e('Go to the "Authentication" tab to connect your Google account.', 'wolves-avis-google'); ?>
                                </p>
                            <?php endif; ?>
                        </div>


                        <div class="card">
                            <h2><?php _e('4. Test connection', 'wolves-avis-google'); ?></h2>
                            <p>
                                <button type="button" class="button button-primary" onclick="testGMBConnection()">
                                    <?php _e('Test reviews retrieval', 'wolves-avis-google'); ?>
                                </button>
                                <button type="button" class="button button-secondary" onclick="clearGMBCache()">
                                    <?php _e('Clear cache', 'wolves-avis-google'); ?>
                                </button>
                            </p>
                            <div id="gmb-test-result"></div>
                        </div>
                    </div>
                    <?php require WOLVES_GMB_PLUGIN_DIR . 'template-parts/notice-configuration.php'; ?>
                </div>
            </div>

            <!-- Tab: Utilisation -->
            <div class="gmb-tab-content" data-tab-content="usage">
                <div class="section row">
                    <div class="card-list">
                        <div class="card">
                            <h2><?php _e('Shortcode usage', 'wolves-avis-google'); ?></h2>
                            <p><?php _e('To display Google My Business reviews on your site, use the following shortcode in your pages, posts or widgets:', 'wolves-avis-google'); ?></p>

                            <div style="background: #f0f0f1; padding: 15px; border-radius: 4px; margin: 15px 0;">
                                <code style="font-size: 14px; color: #d63638;">[gmb_reviews limit="10"]</code>
                            </div>

                            <h3><?php _e('Available parameters', 'wolves-avis-google'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th><code>limit</code></th>
                                    <td><?php _e('Number of reviews to display (default: 50)', 'wolves-avis-google'); ?></td>
                                </tr>
                                <tr>
                                    <th><code>category</code></th>
                                    <td><?php _e('Filter by category (use the category slug)', 'wolves-avis-google'); ?></td>
                                </tr>
                                <tr>
                                    <th><code>type (<?php _e('coming soon', 'wolves-avis-google'); ?>)</code></th>
                                    <td><?php _e('Slider or Masonry grid', 'wolves-avis-google'); ?></td>
                                </tr>
                            </table>

                            <h3><?php _e('Basic usage examples', 'wolves-avis-google'); ?></h3>
                            <ul style="list-style: none; padding: 0; margin: 15px 0;">
                                <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
                                    <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews]</code>
                                    <br><span style="color: #666; font-size: 13px; margin-left: 8px;">→ <?php _e('Display all reviews (maximum 50)', 'wolves-avis-google'); ?></span>
                                </li>
                                <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
                                    <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews
                                        limit="5"]</code>
                                    <br><span
                                            style="color: #666; font-size: 13px; margin-left: 8px;">→ <?php _e('Display 5 reviews', 'wolves-avis-google'); ?></span>
                                </li>
                                <li style="padding: 8px 0;">
                                    <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews
                                        limit="20"]</code>
                                    <br><span
                                            style="color: #666; font-size: 13px; margin-left: 8px;">→ <?php _e('Display 20 reviews', 'wolves-avis-google'); ?></span>
                                </li>
                            </ul>

                            <h3 style="margin-top: 30px;"><?php _e('Filter by category', 'wolves-avis-google'); ?></h3>
                            <p><?php printf(__('You can filter reviews by category. Use the category <strong>slug</strong> (visible in the <a href="%s">reviews list</a>).', 'wolves-avis-google'), esc_url(admin_url('admin.php?page=gmb-manage-reviews'))); ?></p>
                            <p><strong><?php _e('Note:', 'wolves-avis-google'); ?></strong> <?php _e('If a review has multiple categories, it will appear in the filtering of each of these categories.', 'wolves-avis-google'); ?></p>

                            <ul style="list-style: none; padding: 0; margin: 15px 0;">
                                <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
                                    <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews
                                        category="formation"]</code>
                                    <br><span style="color: #666; font-size: 13px; margin-left: 8px;">→ <?php _e('Display only reviews from the "formation" category', 'wolves-avis-google'); ?></span>
                                </li>
                                <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
                                    <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews
                                        category="formation" limit="5"]</code>
                                    <br><span style="color: #666; font-size: 13px; margin-left: 8px;">→ <?php _e('Display 5 reviews from the "formation" category', 'wolves-avis-google'); ?></span>
                                </li>
                                <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f1;">
                                    <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews
                                        category="coaching" limit="3"]</code>
                                    <br><span style="color: #666; font-size: 13px; margin-left: 8px;">→ <?php _e('Display 3 reviews from the "coaching" category', 'wolves-avis-google'); ?></span>
                                </li>
                                <li style="padding: 8px 0;">
                                    <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">[gmb_reviews
                                        category=""]</code>
                                    <br><span style="color: #666; font-size: 13px; margin-left: 8px;">→ <?php _e('Display only reviews <strong>without category</strong>', 'wolves-avis-google'); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <?php require WOLVES_GMB_PLUGIN_DIR . 'template-parts/notice-utilisation.php'; ?>
                </div>
            </div>

            <!-- Tab: Personnalisation -->
            <div class="gmb-tab-content" data-tab-content="customization">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('wgmbr_save_customization', 'wgmbr_customization_nonce'); ?>
                    <input type="hidden" name="action" value="wgmbr_save_customization">
                    <div class="section row">
                        <div class="card-list">
                            <div class="card">
                                <h3><?php _e('Summary', 'wolves-avis-google'); ?></h3>
                                <p><?php _e('Display the average rating and total number of reviews', 'wolves-avis-google'); ?></p>
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_show_summary"><?php _e('Display review summary', 'wolves-avis-google'); ?></label>
                                        </th>
                                        <td>
                                            <label class="gmb-toggle-switch">
                                                <input type="checkbox"
                                                       name="wgmbr_show_summary"
                                                       id="wgmbr_show_summary"
                                                       value="1"
                                                        <?php checked(get_option('wgmbr_show_summary', '1'), '1'); ?>>
                                                <span class="gmb-toggle-slider"></span>
                                            </label>
                                        </td>
                                    </tr>
                                    <tr id="wgmbr_resume_text_color_row">
                                        <th scope="row">
                                            <label for="wgmbr_resume_text_color"><?php _e('Rating and text color', 'wolves-avis-google'); ?></label>
                                        </th>
                                        <td>
                                            <div style="display: flex; gap: 8px; align-items: center;">
                                                <input type="color"
                                                       id="wgmbr_resume_text_color_picker"
                                                       value="<?php echo esc_attr(get_option('wgmbr_resume_text_color', '#FFFFFF')); ?>"
                                                       style="width: 50px; height: 35px; cursor: pointer;">
                                                <input type="text"
                                                       id="wgmbr_resume_text_color_hex"
                                                       value="<?php echo esc_attr(get_option('wgmbr_resume_text_color', '#FFFFFF')); ?>"
                                                       pattern="^#[0-9A-Fa-f]{6}$"
                                                       maxlength="7"
                                                       placeholder="#000000"
                                                       style="width: 100px; font-family: monospace; text-transform: uppercase;">
                                                <input type="hidden"
                                                       name="wgmbr_resume_text_color"
                                                       id="wgmbr_resume_text_color"
                                                       value="<?php echo esc_attr(get_option('wgmbr_resume_text_color', '#FFFFFF')); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                            </div>
                            <div class="card">
                                <h3><?php _e('Review card', 'wolves-avis-google'); ?></h3>
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_card_bg_color"><?php _e('Background color', 'wolves-avis-google'); ?></label>
                                        </th>
                                        <td>
                                            <div style="display: flex; gap: 8px; align-items: center;">
                                                <input type="color"
                                                       id="wgmbr_card_bg_color_picker"
                                                       value="<?php echo esc_attr(get_option('wgmbr_card_bg_color', '#17171A')); ?>"
                                                       style="width: 50px; height: 35px; cursor: pointer;">
                                                <input type="text"
                                                       id="wgmbr_card_bg_color_hex"
                                                       value="<?php echo esc_attr(get_option('wgmbr_card_bg_color', '#17171A')); ?>"
                                                       pattern="^#[0-9A-Fa-f]{6}$"
                                                       maxlength="7"
                                                       placeholder="#000000"
                                                       style="width: 100px; font-family: monospace; text-transform: uppercase;">
                                                <input type="hidden"
                                                       name="wgmbr_card_bg_color"
                                                       id="wgmbr_card_bg_color"
                                                       value="<?php echo esc_attr(get_option('wgmbr_card_bg_color', '#17171A')); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_card_border_radius"><?php _e('Border radius', 'wolves-avis-google'); ?></label>
                                        </th>
                                        <td>
                                            <input type="number"
                                                   name="wgmbr_card_border_radius"
                                                   id="wgmbr_card_border_radius"
                                                   value="<?php echo esc_attr(get_option('wgmbr_card_border_radius', '32')); ?>"
                                                   min="0"
                                                   max="50"
                                                   class="small-text"> px
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_star_color"><?php _e('Star color', 'wolves-avis-google'); ?></label>
                                        </th>
                                        <td>
                                            <div style="display: flex; gap: 8px; align-items: center;">
                                                <input type="color"
                                                       id="wgmbr_star_color_picker"
                                                       value="<?php echo esc_attr(get_option('wgmbr_star_color', '#F85430')); ?>"
                                                       style="width: 50px; height: 35px; cursor: pointer;">
                                                <input type="text"
                                                       id="wgmbr_star_color_hex"
                                                       value="<?php echo esc_attr(get_option('wgmbr_star_color', '#F85430')); ?>"
                                                       pattern="^#[0-9A-Fa-f]{6}$"
                                                       maxlength="7"
                                                       placeholder="#000000"
                                                       style="width: 100px; font-family: monospace; text-transform: uppercase;">
                                                <input type="hidden"
                                                       name="wgmbr_star_color"
                                                       id="wgmbr_star_color"
                                                       value="<?php echo esc_attr(get_option('wgmbr_star_color', '#F85430')); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_text_color"><?php _e('Text color', 'wolves-avis-google'); ?></label>
                                        </th>
                                        <td>
                                            <div style="display: flex; gap: 8px; align-items: center;">
                                                <input type="color"
                                                       id="wgmbr_text_color_picker"
                                                       value="<?php echo esc_attr(get_option('wgmbr_text_color', '#AEAEAE')); ?>"
                                                       style="width: 50px; height: 35px; cursor: pointer;">
                                                <input type="text"
                                                       id="wgmbr_text_color_hex"
                                                       value="<?php echo esc_attr(get_option('wgmbr_text_color', '#AEAEAE')); ?>"
                                                       pattern="^#[0-9A-Fa-f]{6}$"
                                                       maxlength="7"
                                                       placeholder="#000000"
                                                       style="width: 100px; font-family: monospace; text-transform: uppercase;">
                                                <input type="hidden"
                                                       name="wgmbr_text_color"
                                                       id="wgmbr_text_color"
                                                       value="<?php echo esc_attr(get_option('wgmbr_text_color', '#AEAEAE')); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_text_color"><?php _e('Accent color', 'wolves-avis-google'); ?></label>
                                        </th>
                                        <td>
                                            <div style="display: flex; gap: 8px; align-items: center;">
                                                <input type="color"
                                                       id="gmb-accent-color_picker"
                                                       value="<?php echo esc_attr(get_option('gmb-accent-color', '#FFFFFF')); ?>"
                                                       style="width: 50px; height: 35px; cursor: pointer;">
                                                <input type="text"
                                                       id="gmb-accent-color_hex"
                                                       value="<?php echo esc_attr(get_option('gmb-accent-color', '#FFFFFF')); ?>"
                                                       pattern="^#[0-9A-Fa-f]{6}$"
                                                       maxlength="7"
                                                       placeholder="#000000"
                                                       style="width: 100px; font-family: monospace; text-transform: uppercase;">
                                                <input type="hidden"
                                                       name="gmb-accent-color"
                                                       id="gmb-accent-color"
                                                       value="<?php echo esc_attr(get_option('gmb-accent-color', '#FFFFFF')); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_text_color"><?php _e('Name color', 'wolves-avis-google'); ?></label>
                                        </th>
                                        <td>
                                            <div style="display: flex; gap: 8px; align-items: center;">
                                                <input type="color"
                                                       id="wgmbr_text_color_name_picker"
                                                       value="<?php echo esc_attr(get_option('wgmbr_text_color_name', '#FFFFFF')); ?>"
                                                       style="width: 50px; height: 35px; cursor: pointer;">
                                                <input type="text"
                                                       id="wgmbr_text_color_name_hex"
                                                       value="<?php echo esc_attr(get_option('wgmbr_text_color_name', '#FFFFFF')); ?>"
                                                       pattern="^#[0-9A-Fa-f]{6}$"
                                                       maxlength="7"
                                                       placeholder="#000000"
                                                       style="width: 100px; font-family: monospace; text-transform: uppercase;">
                                                <input type="hidden"
                                                       name="wgmbr_text_color_name"
                                                       id="wgmbr_text_color_name"
                                                       value="<?php echo esc_attr(get_option('wgmbr_text_color_name', '#FFFFFF')); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                <p class="submit">
                                    <button type="submit" class="button button-primary">
                                        <?php _e('Save customization', 'wolves-avis-google'); ?>
                                    </button>
                                    <button type="button" class="button" onclick="resetGMBCustomization()">
                                        <?php _e('Reset to default values', 'wolves-avis-google'); ?>
                                    </button>
                                </p>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab: Documentation -->
        <div class="gmb-tab-content" data-tab-content="help">
            <div class="card">
                <h2><?php _e('Google API Configuration', 'wolves-avis-google'); ?></h2>

                <h4 style="margin: 15px 0 10px 0;"><?php _e('1. Create the project', 'wolves-avis-google'); ?></h4>
                <ol style="margin: 0 0 0 20px;">
                    <li><?php printf(__('Go to <a href="%s" target="_blank">Google Cloud Console</a>', 'wolves-avis-google'), 'https://console.cloud.google.com/'); ?></li>
                    <li><?php _e('Create a new project or select an existing one', 'wolves-avis-google'); ?></li>
                </ol>

                <h4 style="margin: 15px 0 10px 0;"><?php _e('2. Enable required APIs', 'wolves-avis-google'); ?></h4>
                <ol style="margin: 0 0 0 20px;">
                    <li><?php _e('In the menu, go to "APIs & Services" → "Library"', 'wolves-avis-google'); ?></li>
                    <li><?php _e('Search and enable: <strong>"Google My Business API"</strong>', 'wolves-avis-google'); ?></li>
                    <li><?php _e('Search and enable: <strong>"My Business Account Management API"</strong>', 'wolves-avis-google'); ?></li>
                    <li><?php _e('Search and enable: <strong>"My Business Business Information API"</strong>', 'wolves-avis-google'); ?></li>
                </ol>

                <h4 style="margin: 15px 0 10px 0;"><?php _e('3. Configure OAuth consent screen', 'wolves-avis-google'); ?></h4>
                <ol style="margin: 0 0 0 20px;">
                    <li><?php _e('Go to "APIs & Services" → "OAuth consent screen"', 'wolves-avis-google'); ?></li>
                    <li><?php _e('Choose <strong>"External"</strong> as user type', 'wolves-avis-google'); ?></li>
                    <li><?php _e('Fill in the required information:', 'wolves-avis-google'); ?>
                        <ul style="margin: 5px 0 0 20px;">
                            <li><?php _e('Application name', 'wolves-avis-google'); ?></li>
                            <li><?php _e('User support email', 'wolves-avis-google'); ?></li>
                            <li><?php _e('Developer contact email', 'wolves-avis-google'); ?></li>
                        </ul>
                    </li>
                    <li><strong><?php _e('IMPORTANT', 'wolves-avis-google'); ?></strong> : <?php _e('In "Scopes", add these OAuth scopes:', 'wolves-avis-google'); ?>
                        <ul style="margin: 5px 0 0 20px;">
                            <li><code>https://www.googleapis.com/auth/business.manage</code></li>
                            <li><code>https://www.googleapis.com/auth/plus.business.manage</code></li>
                        </ul>
                    </li>
                    <li><?php _e('In "Test users", add your Gmail address', 'wolves-avis-google'); ?></li>
                    <li><?php _e('Save', 'wolves-avis-google'); ?></li>
                </ol>

                <h4 style="margin: 15px 0 10px 0;"><?php _e('4. Create OAuth 2.0 credentials', 'wolves-avis-google'); ?></h4>
                <ol style="margin: 0 0 0 20px;">
                    <li><?php _e('Go to "APIs & Services" → "Credentials"', 'wolves-avis-google'); ?></li>
                    <li><?php _e('Click on "+ CREATE CREDENTIALS" → "OAuth client ID"', 'wolves-avis-google'); ?></li>
                    <li><?php _e('Application type: <strong>"Web Application"</strong>', 'wolves-avis-google'); ?></li>
                    <li><?php _e('Name: give it a name (eg: "WordPress GMB Reviews")', 'wolves-avis-google'); ?></li>
                    <li><strong><?php _e('Authorized redirect URIs', 'wolves-avis-google'); ?></strong> : <?php _e('Add EXACTLY the URI above', 'wolves-avis-google'); ?></li>
                    <li><?php _e('Click "Create"', 'wolves-avis-google'); ?></li>
                    <li><?php _e('Copy the Client ID and Client Secret into the fields above', 'wolves-avis-google'); ?></li>
                </ol>
            </div>
        </div>

    </div>
</div>

<?php require WOLVES_GMB_PLUGIN_DIR . 'templates/admin-scripts.php'; ?>
