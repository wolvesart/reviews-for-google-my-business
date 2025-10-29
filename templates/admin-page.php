<?php
/**
 * Reviews for Google My Business - Admin page template
 *
 * Available variables:
 * - $has_token
 * - $available_locations
 * - $current_account_id
 * - $current_location_id
 * - $has_credentials
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap gmb-wrap">
    <?php include_once(WOLVES_GMB_PLUGIN_DIR . 'template-parts/header.php'); ?>

    <div class="gmb-container">
        <!-- Sidebar with tabs -->
        <div class="gmb-sidebar">
            <nav class="gmb-tabs-nav">

                <button class="gmb-tab-button active" data-tab="configuration">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e('Configuration', 'reviews-for-google-my-business'); ?>
                </button>
                <button class="gmb-tab-button" data-tab="usage">
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php esc_html_e('Shortcode', 'reviews-for-google-my-business'); ?>
                </button>
                <button class="gmb-tab-button" data-tab="customization">
                    <span class="dashicons dashicons-admin-customizer"></span>
                    <?php esc_html_e('Customization', 'reviews-for-google-my-business'); ?>
                </button>
                <button class="gmb-tab-button" data-tab="help">
                    <span class="dashicons dashicons-admin-page"></span>
                    <?php esc_html_e('Documentation', 'reviews-for-google-my-business'); ?>
                </button>
            </nav>
        </div>

        <!-- Main content -->
        <div class="gmb-content">

            <!-- Tab: Configuration -->
            <div class="gmb-tab-content active" data-tab-content="configuration">
                <div class="section row">
                    <div class="card-list">
                        <div class="card">
                            <h2><?php esc_html_e('1. Google API Configuration', 'reviews-for-google-my-business'); ?></h2>

                            <?php if (!is_ssl() && !defined('WP_DEBUG') && (!isset($_SERVER['REMOTE_ADDR']) || !in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'), true))): ?>
                                <div class="gmb-notice error">
                                    <p>
                                        <strong>⚠️ <?php esc_html_e('HTTPS Required', 'reviews-for-google-my-business'); ?></strong><br>
                                        <?php esc_html_e('For security reasons, HTTPS is required to save API credentials. Please enable SSL/TLS on your site.', 'reviews-for-google-my-business'); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <?php if ($has_credentials): ?>
                                <div class="gmb-notice success">
                                    <p>
                                        <?php esc_html_e('API credentials configured', 'reviews-for-google-my-business'); ?>
                                        <br>
                                        <small><?php esc_html_e('Client secret is encrypted and secured', 'reviews-for-google-my-business'); ?></small>
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="gmb-notice warning">
                                    <strong><?php esc_html_e('API credentials not configured', 'reviews-for-google-my-business'); ?></strong><br>
                                    <?php esc_html_e('You must first configure your Google Cloud credentials to use this feature.', 'reviews-for-google-my-business'); ?>
                                </div>
                            <?php endif; ?>

                            <details class="accordion-details" <?php echo !$has_credentials ? 'open' : ''; ?>>
                                <summary>
                                    <?php echo $has_credentials ? esc_html__('Edit API credentials', 'reviews-for-google-my-business') : esc_html__('Add API credentials', 'reviews-for-google-my-business'); ?>
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
                                                    <?php esc_html_e('Your Google Cloud Client ID must end with', 'reviews-for-google-my-business'); ?>
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
                                                       value=""
                                                       placeholder="<?php echo GMB_CLIENT_SECRET ? esc_attr__('••••••••••••••••', 'reviews-for-google-my-business') : esc_attr__('GOCSPX-xxxxxxxxxxxx', 'reviews-for-google-my-business'); ?>"
                                                       <?php echo GMB_CLIENT_SECRET ? '' : 'required'; ?>>
                                                <p class="description">
                                                    <?php
                                                    if (GMB_CLIENT_SECRET) {
                                                        esc_html_e('Leave blank to keep current secret, or enter new secret to update', 'reviews-for-google-my-business');
                                                    } else {
                                                        esc_html_e('Your Google Cloud Client Secret', 'reviews-for-google-my-business');
                                                    }
                                                    ?>
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="wgmbr_redirect_uri">Redirect URI</label>
                                            </th>
                                            <td>
                                                <input type="text"
                                                       name="wgmbr_redirect_uri"
                                                       id="wgmbr_redirect_uri"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr(GMB_REDIRECT_URI); ?>"
                                                       placeholder="<?php echo esc_attr(admin_url('admin.php?page=gmb-settings&wgmbr_auth=1')); ?>"
                                                       required>
                                                <p class="description">
                                                    <?php esc_html_e('Default:', 'reviews-for-google-my-business'); ?>
                                                    <code><?php echo esc_html(admin_url('admin.php?page=gmb-settings&wgmbr_auth=1')); ?></code><br>
                                                    ⚠️ <?php esc_html_e('This URI must match the one configured in Google Cloud', 'reviews-for-google-my-business'); ?>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>

                                    <button type="submit" class="button button-primary">
                                        <?php esc_html_e('Save credentials', 'reviews-for-google-my-business'); ?>
                                    </button>

                                </form>
                            </details>
                        </div>

                        <div class="card">
                            <h2><?php esc_html_e('2. Google My Business OAuth Authentication', 'reviews-for-google-my-business'); ?></h2>

                            <?php if (!$has_credentials): ?>
                                <div class="gmb-notice warning">
                                    <strong><?php esc_html_e('Please configure your API credentials above before authenticating.', 'reviews-for-google-my-business'); ?></strong>
                                </div>
                            <?php elseif ($has_token): ?>
                                <div class="gmb-notice success">
                                    <p><?php esc_html_e('Authenticated', 'reviews-for-google-my-business'); ?></p></div>
                                <div class="button-wrapper">
                                    <a href="<?php echo esc_url(wgmbr_get_auth_url()); ?>"
                                       class="button button-primary">
                                        <?php esc_html_e('Re-authenticate', 'reviews-for-google-my-business'); ?>
                                    </a>
                                    <button type="button" class="button button-secondary"
                                            onclick="if(confirm('<?php echo esc_js(__('Delete tokens?', 'reviews-for-google-my-business')); ?>')) location.href='<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=wgmbr_revoke'), 'wgmbr_revoke_action')); ?>'">
                                        ⨉ <?php esc_html_e('Revoke access', 'reviews-for-google-my-business'); ?>
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="gmb-notice warning">
                                    <p><?php esc_html_e('Not authenticated', 'reviews-for-google-my-business'); ?></p>
                                </div>
                                <div class="button-wrapper">
                                    <a href="<?php echo esc_url(wgmbr_get_auth_url()); ?>"
                                       class="button button-primary">
                                        <?php esc_html_e('Connect with Google', 'reviews-for-google-my-business'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card">
                            <h2><?php esc_html_e('3. Select your location', 'reviews-for-google-my-business'); ?></h2>
                            <?php if ($has_token && !empty($available_locations)): ?>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                    <?php wp_nonce_field('wgmbr_save_location', 'wgmbr_location_nonce'); ?>
                                    <input type="hidden" name="action" value="wgmbr_save_location">

                                    <table class="form-table">
                                        <tr>
                                            <th scope="row">
                                                <label for="wgmbr_location_select"><?php esc_html_e('Location Google My Business', 'reviews-for-google-my-business'); ?></label>
                                            </th>
                                            <td>
                                                <select name="wgmbr_location" id="wgmbr_location_select"
                                                        class="regular-text"
                                                        required>
                                                    <option value=""><?php esc_html_e('-- Select a location --', 'reviews-for-google-my-business'); ?></option>
                                                    <?php foreach ($available_locations as $location): ?>
                                                        <?php
                                                        $value = $location['account_id'] . '|' . $location['location_id'];
                                                        $current_value = $current_account_id . '|' . $current_location_id;
                                                        ?>
                                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($value, $current_value); ?>>
                                                            <?php echo esc_html($location['location_title']); ?>
                                                            (<?php echo esc_html($location['account_name']); ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <p class="description">
                                                    <?php esc_html_e('Select the Google My Business location whose reviews you want to display.', 'reviews-for-google-my-business'); ?>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>

                                    <div class="button-wrapper">
                                        <button type="submit" class="button button-primary">
                                            <?php esc_html_e('Save location', 'reviews-for-google-my-business'); ?>
                                        </button>
                                        <button type="button" class="button button-secondary"
                                                onclick="refreshLocations()">
                                            <?php esc_html_e('Refresh list', 'reviews-for-google-my-business'); ?>
                                        </button>
                                    </div>
                                </form>
                            <?php elseif ($has_token && empty($available_locations)): ?>
                                <p><?php esc_html_e('No locations were found. Click the button below to search for your Google My Business locations.', 'reviews-for-google-my-business'); ?></p>
                                <p>
                                    <button type="button" class="button button-primary" onclick="refreshLocations()">
                                        <?php esc_html_e('Search my locations', 'reviews-for-google-my-business'); ?>
                                    </button>
                                </p>
                            <?php else: ?>
                                <p class="gmb-notice warning">
                                    <strong><?php esc_html_e('You must first authenticate yourself', 'reviews-for-google-my-business'); ?></strong>
                                    <?php esc_html_e('Go back to the second step to connect your Google account.', 'reviews-for-google-my-business'); ?>
                                </p>
                            <?php endif; ?>
                        </div>


                        <div class="card">
                            <h2><?php esc_html_e('4. Test connection', 'reviews-for-google-my-business'); ?></h2>
                            <div class="button-wrapper">
                                <button type="button" class="button button-primary" onclick="testGMBConnection()">
                                    <?php esc_html_e('Test reviews retrieval', 'reviews-for-google-my-business'); ?>
                                </button>
                                <button type="button" class="button button-secondary" onclick="if(confirm('<?php echo esc_js(__('This will delete the cache and ALL reviews. Are you sure?', 'reviews-for-google-my-business')); ?>')) clearGMBCache()">
                                    <?php esc_html_e('Reset', 'reviews-for-google-my-business'); ?>
                                </button>
                            </div>
                            <div id="gmb-test-result"></div>
                        </div>
                    </div>
                    <?php require WOLVES_GMB_PLUGIN_DIR . 'template-parts/notice-configuration.php'; ?>
                </div>
            </div>

            <!-- Tab: Usage -->
            <div class="gmb-tab-content" data-tab-content="usage">
                <div class="section row">
                    <div class="card-list">
                        <div class="card">
                            <h2><?php esc_html_e('Shortcode usage', 'reviews-for-google-my-business'); ?></h2>
                            <p><?php esc_html_e('To display Google My Business reviews on your site, use the following shortcode in your pages, posts or widgets:', 'reviews-for-google-my-business'); ?></p>

                            <div class="gmb-shortcode-generator">

                                <div class="gmb-main-shortcode">
                                    <code id="gmb-generated-shortcode">[gmb_reviews]</code>
                                    <button type="button" class="button button-primary"
                                            onclick="wgmbrCopyGeneratedShortcode(this)">
                                        <span class="dashicons dashicons-admin-page"></span>
                                        <?php esc_html_e('Copy', 'reviews-for-google-my-business'); ?>
                                    </button>
                                </div>

                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="gmb-gen-limit"><?php esc_html_e('Number of reviews', 'reviews-for-google-my-business'); ?></label>
                                        </th>
                                        <td>
                                            <input type="number"
                                                   id="gmb-gen-limit"
                                                   class="small-text"
                                                   min="1"
                                                   max="100"
                                                   value=""
                                                   placeholder="10"
                                                   onchange="wgmbrGenerateShortcode()">
                                            <p class="description"><?php esc_html_e('Number of reviews to display. Leave blank if you want to display the maximum number of reviews', 'reviews-for-google-my-business'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="gmb-gen-categories"><?php esc_html_e('Categories', 'reviews-for-google-my-business'); ?></label>
                                        </th>
                                        <td>
                                            <div class="gmb-categories-checkboxes" id="gmb-gen-categories">
                                                <?php
                                                $categories = get_terms(array(
                                                        'taxonomy' => 'gmb_category',
                                                        'hide_empty' => false,
                                                ));
                                                if (!empty($categories) && !is_wp_error($categories)):
                                                    foreach ($categories as $cat): ?>
                                                        <label class="gmb-category-checkbox">
                                                            <input type="checkbox"
                                                                   name="gen_category_slugs[]"
                                                                   value="<?php echo esc_attr($cat->slug); ?>"
                                                                   onchange="wgmbrGenerateShortcode()">
                                                            <span class="gmb-category-label"><?php echo esc_html($cat->name); ?></span>
                                                        </label>
                                                    <?php endforeach;
                                                else: ?>
                                                    <span class="gmb-categories-empty"><?php esc_html_e('No categories', 'reviews-for-google-my-business'); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="description"><?php esc_html_e('Select one or multiple categories to filter reviews', 'reviews-for-google-my-business'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="gmb-gen-summary"><?php esc_html_e('Show summary', 'reviews-for-google-my-business'); ?></label>
                                        </th>
                                        <td>
                                            <label class="switch">
                                                <input type="checkbox"
                                                       id="gmb-gen-summary"
                                                       checked
                                                       onchange="wgmbrGenerateShortcode()">
                                                <span class="slider"></span>
                                            </label>
                                            <p class="description"><?php esc_html_e('Display average rating and total reviews', 'reviews-for-google-my-business'); ?></p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <h3><?php esc_html_e('Available parameters', 'reviews-for-google-my-business'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th><code>limit</code></th>
                                    <td><?php esc_html_e('Number of reviews to display (maximum 100)', 'reviews-for-google-my-business'); ?></td>
                                </tr>
                                <tr>
                                    <th><code>category</code></th>
                                    <td><?php esc_html_e('Filter by category (use the category slug)', 'reviews-for-google-my-business'); ?></td>
                                </tr>
                                <tr>
                                    <th><code>show_summary</code></th>
                                    <td><?php esc_html_e('Display the average rating summary (default: true). Set to "false" to hide it.', 'reviews-for-google-my-business'); ?></td>
                                </tr>
                            </table>

                            <h3><?php esc_html_e('Basic usage examples', 'reviews-for-google-my-business'); ?></h3>

                            <ul class="gmb-shortcode-examples">
                                <li>
                                    <code>[gmb_reviews]</code>
                                    <p><?php esc_html_e('Display all reviews (maximum 50)', 'reviews-for-google-my-business'); ?></p>
                                </li>
                                <li>
                                    <code>[gmb_reviews limit="20"]</code>
                                    <p><?php esc_html_e('Display 20 reviews', 'reviews-for-google-my-business'); ?></p>
                                </li>
                                <li>
                                    <code>[gmb_reviews category="formation"]</code>
                                    <p><?php esc_html_e('Display only reviews from the "formation" category', 'reviews-for-google-my-business'); ?></p>
                                </li>
                                <li>
                                    <code>[gmb_reviews category="formation,coaching" limit="10"]</code>
                                    <p><?php esc_html_e('Display 10 reviews from "formation" OR "coaching"', 'reviews-for-google-my-business'); ?></p>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <?php require WOLVES_GMB_PLUGIN_DIR . 'template-parts/notice-utilisation.php'; ?>
                </div>
            </div>

            <!-- Tab: Customization -->
            <div class="gmb-tab-content" data-tab-content="customization">
                <form method="post" id="gmb-customization-form"
                      action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('wgmbr_save_customization', 'wgmbr_customization_nonce'); ?>
                    <input type="hidden" name="action" value="wgmbr_save_customization">
                    <div class="section row">
                        <div class="card-list">
                            <div class="card">
                                <h3><?php esc_html_e('Review card', 'reviews-for-google-my-business'); ?></h3>
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_color_text_resume"><?php esc_html_e('Text resume', 'reviews-for-google-my-business'); ?></label>
                                            <p class="description"><?php esc_html_e('Color of text and overall rating in summary', 'reviews-for-google-my-business'); ?></p>
                                        </th>
                                        <td>
                                            <div class="input-color">
                                                <input type="color"
                                                       id="wgmbr_color_text_resume_picker"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_text_resume', '#222222')); ?>"
                                                >
                                                <input type="text"
                                                       id="wgmbr_color_text_resume_hex"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_text_resume', '#222222')); ?>"
                                                       pattern="^#[0-9A-Fa-f]{6}$"
                                                       maxlength="7"
                                                       placeholder="#000000"
                                                >
                                                <input type="hidden"
                                                       name="wgmbr_color_text_resume"
                                                       id="wgmbr_color_text_resume"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_text_resume', '#222222')); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_color_card_bg"><?php esc_html_e('Background color', 'reviews-for-google-my-business'); ?></label>
                                        </th>
                                        <td>
                                            <div class="input-color">
                                                <input type="color"
                                                       id="wgmbr_color_card_bg_picker"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_card_bg', '#F3F5F7')); ?>"
                                                >
                                                <input type="text"
                                                       id="wgmbr_color_card_bg_hex"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_card_bg', '#F3F5F7')); ?>"
                                                       pattern="^#[0-9A-Fa-f]{6}$"
                                                       maxlength="7"
                                                       placeholder="#000000"
                                                >
                                                <input type="hidden"
                                                       name="wgmbr_color_card_bg"
                                                       id="wgmbr_color_card_bg"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_card_bg', '#F3F5F7')); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_radius_card"><?php esc_html_e('Border radius', 'reviews-for-google-my-business'); ?></label>
                                        </th>
                                        <td>
                                            <input type="number"
                                                   name="wgmbr_radius_card"
                                                   id="wgmbr_radius_card"
                                                   value="<?php echo esc_attr(get_option('wgmbr_radius_card', '16')); ?>"
                                                   min="0"
                                                   max="50"
                                                   class="small-text"> px
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_color_star"><?php esc_html_e('Star color', 'reviews-for-google-my-business'); ?></label>
                                        </th>
                                        <td>
                                            <div class="input-color">
                                                <input type="color"
                                                       id="wgmbr_color_star_picker"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_star', '#FFC83E')); ?>"
                                                >
                                                <input type="text"
                                                       id="wgmbr_color_star_hex"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_star', '#FFC83E')); ?>"
                                                       pattern="^#[0-9A-Fa-f]{6}$"
                                                       maxlength="7"
                                                       placeholder="#000000"
                                                >
                                                <input type="hidden"
                                                       name="wgmbr_color_star"
                                                       id="wgmbr_color_star"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_star', '#FFC83E')); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_color_text_primary"><?php esc_html_e('Text color', 'reviews-for-google-my-business'); ?></label>
                                        </th>
                                        <td>
                                            <div class="input-color">
                                                <input type="color"
                                                       id="wgmbr_color_text_primary_picker"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_text_primary', '#222222')); ?>"
                                                >
                                                <input type="text"
                                                       id="wgmbr_color_text_primary_hex"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_text_primary', '#222222')); ?>"
                                                       pattern="^#[0-9A-Fa-f]{6}$"
                                                       maxlength="7"
                                                       placeholder="#000000"
                                                >
                                                <input type="hidden"
                                                       name="wgmbr_color_text_primary"
                                                       id="wgmbr_color_text_primary"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_text_primary', '#222222')); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_color_accent"><?php esc_html_e('Accent color', 'reviews-for-google-my-business'); ?></label>
                                            <p class="description"><?php esc_html_e('Color of the slider arrows and pagination points. Color of the "Read more" button on hover.', 'reviews-for-google-my-business'); ?></p>
                                        </th>
                                        <td>
                                            <div class="input-color">
                                                <input type="color"
                                                       id="wgmbr_color_accent_picker"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_accent', '#0F68DD')); ?>"
                                                       style="width: 50px; height: 35px; cursor: pointer;">
                                                <input type="text"
                                                       id="wgmbr_color_accent_hex"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_accent', '#0F68DD')); ?>"
                                                       pattern="^#[0-9A-Fa-f]{6}$"
                                                       maxlength="7"
                                                       placeholder="#000000"
                                                       style="width: 100px; font-family: monospace; text-transform: uppercase;">
                                                <input type="hidden"
                                                       name="wgmbr_color_accent"
                                                       id="wgmbr_color_accent"
                                                       value="<?php echo esc_attr(get_option('wgmbr_color_accent', '#0F68DD')); ?>">
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                <div class="button-wrapper">
                                    <button type="submit" class="button button-primary">
                                        <?php esc_html_e('Save customization', 'reviews-for-google-my-business'); ?>
                                    </button>
                                    <button type="button" class="button" onclick="resetGMBCustomization(this)">
                                        <?php esc_html_e('Reset to default values', 'reviews-for-google-my-business'); ?>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab: Documentation -->
        <div class="gmb-tab-content" data-tab-content="help">
            <?php require WOLVES_GMB_PLUGIN_DIR . 'template-parts/documentation.php'; ?>
        </div>

    </div>
</div>
