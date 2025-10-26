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
                    <?php _e('Configuration', 'google-my-business-reviews'); ?>
                </button>
                <button class="gmb-tab-button" data-tab="usage">
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php _e('Shortcode', 'google-my-business-reviews'); ?>
                </button>
                <button class="gmb-tab-button" data-tab="customization">
                    <span class="dashicons dashicons-admin-customizer"></span>
                    <?php _e('Customization', 'google-my-business-reviews'); ?>
                </button>
                <button class="gmb-tab-button" data-tab="help">
                    <span class="dashicons dashicons-admin-page"></span>
                    <?php _e('Documentation', 'google-my-business-reviews'); ?>
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
                            <h2><?php _e('1. Google API Configuration', 'google-my-business-reviews'); ?></h2>

                            <?php if ($has_credentials): ?>
                                <div class="gmb-notice success">
                                    <p><?php _e('API credentials configured', 'google-my-business-reviews'); ?></p>
                                </div>
                            <?php else: ?>
                                <div class="gmb-notice warning">
                                    <strong><?php _e('API credentials not configured', 'google-my-business-reviews'); ?></strong><br>
                                    <?php _e('You must first configure your Google Cloud credentials to use this feature.', 'google-my-business-reviews'); ?>
                                </div>
                            <?php endif; ?>

                            <details <?php echo !$has_credentials ? 'open' : ''; ?>>
                                <summary style="cursor: pointer; font-weight: bold; padding: 10px 0;">
                                    <?php echo $has_credentials ? __('Edit API credentials', 'google-my-business-reviews') : __('Add API credentials', 'google-my-business-reviews'); ?>
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
                                                    <?php _e('Your Google Cloud Client ID must end with', 'google-my-business-reviews'); ?>
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
                                                    <?php _e('Your Google Cloud Client Secret', 'google-my-business-reviews'); ?>
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
                                                    <?php _e('Default:', 'google-my-business-reviews'); ?>
                                                    <code><?php echo esc_html(admin_url('admin.php?page=gmb-settings&wgmbr_auth=1')); ?></code><br>
                                                    ⚠️ <?php _e('This URI must match the one configured in Google Cloud', 'google-my-business-reviews'); ?>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>

                                    <p class="submit">
                                        <button type="submit" class="button button-primary">
                                            <?php _e('Save credentials', 'google-my-business-reviews'); ?>
                                        </button>
                                    </p>
                                </form>
                            </details>
                        </div>

                        <div class="card">
                            <h2><?php _e('2. Google My Business OAuth Authentication', 'google-my-business-reviews'); ?></h2>

                            <?php if (!$has_credentials): ?>
                                <div class="gmb-notice warning">
                                    <strong><?php _e('Please configure your API credentials above before authenticating.', 'google-my-business-reviews'); ?></strong>
                                </div>
                            <?php elseif ($has_token): ?>
                                <div class="gmb-notice success">
                                    <p><?php _e('Authenticated', 'google-my-business-reviews'); ?></p></div>
                                <div class="button-wrapper">
                                    <a href="<?php echo esc_url(wgmbr_get_auth_url()); ?>"
                                       class="button button-primary">
                                        <?php _e('Re-authenticate', 'google-my-business-reviews'); ?>
                                    </a>
                                    <button type="button" class="button button-secondary"
                                            onclick="if(confirm('<?php echo esc_js(__('Delete tokens?', 'google-my-business-reviews')); ?>')) location.href='<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=wgmbr_revoke'), 'wgmbr_revoke_action')); ?>'">
                                        ⨉ <?php _e('Revoke access', 'google-my-business-reviews'); ?>
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="gmb-notice warning">
                                    <p><?php _e('Not authenticated', 'google-my-business-reviews'); ?></p></div>
                                <div class="button-wrapper">
                                    <a href="<?php echo esc_url(wgmbr_get_auth_url()); ?>"
                                       class="button button-primary">
                                        <?php _e('Connect with Google', 'google-my-business-reviews'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card">
                            <h2><?php _e('3. Select your location', 'google-my-business-reviews'); ?></h2>
                            <?php if ($has_token && !empty($available_locations)): ?>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                    <?php wp_nonce_field('wgmbr_save_location', 'wgmbr_location_nonce'); ?>
                                    <input type="hidden" name="action" value="wgmbr_save_location">

                                    <table class="form-table">
                                        <tr>
                                            <th scope="row">
                                                <label for="wgmbr_location_select"><?php _e('Location Google My Business', 'google-my-business-reviews'); ?></label>
                                            </th>
                                            <td>
                                                <select name="wgmbr_location" id="wgmbr_location_select"
                                                        class="regular-text"
                                                        required>
                                                    <option value=""><?php _e('-- Select a location --', 'google-my-business-reviews'); ?></option>
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
                                                    <?php _e('Select the Google My Business location whose reviews you want to display.', 'google-my-business-reviews'); ?>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>

                                    <div class="button-wrapper">
                                        <button type="submit" class="button button-primary">
                                            <?php _e('Save location', 'google-my-business-reviews'); ?>
                                        </button>
                                        <button type="button" class="button button-secondary"
                                                onclick="refreshLocations()">
                                            <?php _e('Refresh list', 'google-my-business-reviews'); ?>
                                        </button>
                                    </div>
                                </form>
                            <?php elseif ($has_token && empty($available_locations)): ?>
                                <p><?php _e('No locations were found. Click the button below to search for your Google My Business locations.', 'google-my-business-reviews'); ?></p>
                                <p>
                                    <button type="button" class="button button-primary" onclick="refreshLocations()">
                                        <?php _e('Search my locations', 'google-my-business-reviews'); ?>
                                    </button>
                                </p>
                            <?php else: ?>
                                <p class="gmb-notice warning">
                                    <strong><?php _e('You must first authenticate yourself', 'google-my-business-reviews'); ?></strong><br>
                                    <?php _e('Go to the "Authentication" tab to connect your Google account.', 'google-my-business-reviews'); ?>
                                </p>
                            <?php endif; ?>
                        </div>


                        <div class="card">
                            <h2><?php _e('4. Test connection', 'google-my-business-reviews'); ?></h2>
                            <div class="button-wrapper">
                                <button type="button" class="button button-primary" onclick="testGMBConnection()">
                                    <?php _e('Test reviews retrieval', 'google-my-business-reviews'); ?>
                                </button>
                                <button type="button" class="button button-secondary" onclick="clearGMBCache()">
                                    <?php _e('Clear cache', 'google-my-business-reviews'); ?>
                                </button>
                            </div>
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
                            <h2><?php _e('Shortcode usage', 'google-my-business-reviews'); ?></h2>
                            <p><?php _e('To display Google My Business reviews on your site, use the following shortcode in your pages, posts or widgets:', 'google-my-business-reviews'); ?></p>

                            <div class="gmb-shortcode-generator">

                                    <div class="gmb-main-shortcode">
                                        <code id="gmb-generated-shortcode">[gmb_reviews limit="10"]</code>
                                        <button type="button" class="button button-primary" onclick="wgmbrCopyGeneratedShortcode(this)">
                                            <span class="dashicons dashicons-admin-page"></span>
                                            <?php _e('Copy', 'google-my-business-reviews'); ?>
                                        </button>
                                    </div>

                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="gmb-gen-limit"><?php _e('Number of reviews', 'google-my-business-reviews'); ?></label>
                                        </th>
                                        <td>
                                            <input type="number"
                                                   id="gmb-gen-limit"
                                                   class="small-text"
                                                   min="1"
                                                   max="100"
                                                   value="10"
                                                   onchange="wgmbrGenerateShortcode()">
                                            <p class="description"><?php _e('Number of reviews to display (1-100)', 'google-my-business-reviews'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="gmb-gen-categories"><?php _e('Categories', 'google-my-business-reviews'); ?></label>
                                        </th>
                                        <td>
                                            <select id="gmb-gen-categories"
                                                    multiple
                                                    style="min-height: 120px; width: 300px;"
                                                    onchange="wgmbrGenerateShortcode()">
                                                <option value=""><?php _e('-- All categories --', 'google-my-business-reviews'); ?></option>
                                                <?php
                                                $categories = get_terms(array(
                                                    'taxonomy' => 'gmb_category',
                                                    'hide_empty' => false,
                                                ));
                                                if (!empty($categories) && !is_wp_error($categories)):
                                                    foreach ($categories as $cat): ?>
                                                        <option value="<?php echo esc_attr($cat->slug); ?>">
                                                            <?php echo esc_html($cat->name); ?> (<?php echo esc_html($cat->slug); ?>)
                                                        </option>
                                                    <?php endforeach;
                                                endif;
                                                ?>
                                            </select>
                                            <p class="description"><?php _e('Hold Ctrl/Cmd to select multiple categories', 'google-my-business-reviews'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="gmb-gen-summary"><?php _e('Show summary', 'google-my-business-reviews'); ?></label>
                                        </th>
                                        <td>
                                            <label>
                                                <input type="checkbox"
                                                       id="gmb-gen-summary"
                                                       checked
                                                       onchange="wgmbrGenerateShortcode()">
                                                <?php _e('Display average rating and total reviews', 'google-my-business-reviews'); ?>
                                            </label>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <h3 style="margin-top: 40px;"><?php _e('Available parameters', 'google-my-business-reviews'); ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th><code>limit</code></th>
                                    <td><?php _e('Number of reviews to display (maximum 50)', 'google-my-business-reviews'); ?></td>
                                </tr>
                                <tr>
                                    <th><code>category</code></th>
                                    <td><?php _e('Filter by category (use the category slug)', 'google-my-business-reviews'); ?></td>
                                </tr>
                                <tr>
                                    <th><code>show_summary</code></th>
                                    <td><?php _e('Display the average rating summary (default: true). Set to "false" to hide it.', 'google-my-business-reviews'); ?></td>
                                </tr>
                                <tr>
                                    <th><code>type (<?php _e('coming soon', 'google-my-business-reviews'); ?>)</code></th>
                                    <td><?php _e('Slider or Masonry grid', 'google-my-business-reviews'); ?></td>
                                </tr>
                            </table>

                            <h3><?php _e('Basic usage examples', 'google-my-business-reviews'); ?></h3>

                            <ul class="gmb-shortcode-examples">
                                <li>
                                    <code>[gmb_reviews]</code>
                                    <p><?php _e('Display all reviews (maximum 50)', 'google-my-business-reviews'); ?></p>
                                </li>
                                <li>
                                    <code>[gmb_reviews limit="20"]</code>
                                    <p><?php _e('Display 20 reviews', 'google-my-business-reviews'); ?></p>
                                </li>
                                <li>
                                    <code>[gmb_reviews category="formation"]</code>
                                    <p><?php _e('Display only reviews from the "formation" category', 'google-my-business-reviews'); ?></p>
                                </li>
                                <li>
                                    <code>[gmb_reviews category="formation,coaching" limit="10"]</code>
                                    <p><?php _e('Display 10 reviews from "formation" OR "coaching"', 'google-my-business-reviews'); ?></p>
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
                                <h3><?php _e('Review card', 'google-my-business-reviews'); ?></h3>
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="wgmbr_card_bg_color"><?php _e('Background color', 'google-my-business-reviews'); ?></label>
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
                                            <label for="wgmbr_card_border_radius"><?php _e('Border radius', 'google-my-business-reviews'); ?></label>
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
                                            <label for="wgmbr_star_color"><?php _e('Star color', 'google-my-business-reviews'); ?></label>
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
                                            <label for="wgmbr_text_color"><?php _e('Text color', 'google-my-business-reviews'); ?></label>
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
                                            <label for="wgmbr_text_color"><?php _e('Accent color', 'google-my-business-reviews'); ?></label>
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
                                            <label for="wgmbr_text_color"><?php _e('Name color', 'google-my-business-reviews'); ?></label>
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

                                <div class="button-wrapper">
                                    <button type="submit" class="button button-primary">
                                        <?php _e('Save customization', 'google-my-business-reviews'); ?>
                                    </button>
                                    <button type="button" class="button" onclick="resetGMBCustomization()">
                                        <?php _e('Reset to default values', 'google-my-business-reviews'); ?>
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
            <div class="card">
                <h2><?php _e('Google API Configuration', 'google-my-business-reviews'); ?></h2>

                <h4 style="margin: 15px 0 10px 0;"><?php _e('1. Create the project', 'google-my-business-reviews'); ?></h4>
                <ol style="margin: 0 0 0 20px;">
                    <li><?php printf(__('Go to <a href="%s" target="_blank">Google Cloud Console</a>', 'google-my-business-reviews'), 'https://console.cloud.google.com/'); ?></li>
                    <li><?php _e('Create a new project or select an existing one', 'google-my-business-reviews'); ?></li>
                </ol>

                <h4 style="margin: 15px 0 10px 0;"><?php _e('2. Enable required APIs', 'google-my-business-reviews'); ?></h4>
                <ol style="margin: 0 0 0 20px;">
                    <li><?php _e('In the menu, go to "APIs & Services" → "Library"', 'google-my-business-reviews'); ?></li>
                    <li><?php _e('Search and enable: <strong>"Google My Business API"</strong>', 'google-my-business-reviews'); ?></li>
                    <li><?php _e('Search and enable: <strong>"My Business Account Management API"</strong>', 'google-my-business-reviews'); ?></li>
                    <li><?php _e('Search and enable: <strong>"My Business Business Information API"</strong>', 'google-my-business-reviews'); ?></li>
                </ol>

                <h4 style="margin: 15px 0 10px 0;"><?php _e('3. Configure OAuth consent screen', 'google-my-business-reviews'); ?></h4>
                <ol style="margin: 0 0 0 20px;">
                    <li><?php _e('Go to "APIs & Services" → "OAuth consent screen"', 'google-my-business-reviews'); ?></li>
                    <li><?php _e('Choose <strong>"External"</strong> as user type', 'google-my-business-reviews'); ?></li>
                    <li><?php _e('Fill in the required information:', 'google-my-business-reviews'); ?>
                        <ul style="margin: 5px 0 0 20px;">
                            <li><?php _e('Application name', 'google-my-business-reviews'); ?></li>
                            <li><?php _e('User support email', 'google-my-business-reviews'); ?></li>
                            <li><?php _e('Developer contact email', 'google-my-business-reviews'); ?></li>
                        </ul>
                    </li>
                    <li><strong><?php _e('IMPORTANT', 'google-my-business-reviews'); ?></strong> : <?php _e('In "Scopes", add these OAuth scopes:', 'google-my-business-reviews'); ?>
                        <ul style="margin: 5px 0 0 20px;">
                            <li><code>https://www.googleapis.com/auth/business.manage</code></li>
                            <li><code>https://www.googleapis.com/auth/plus.business.manage</code></li>
                        </ul>
                    </li>
                    <li><?php _e('In "Test users", add your Gmail address', 'google-my-business-reviews'); ?></li>
                    <li><?php _e('Save', 'google-my-business-reviews'); ?></li>
                </ol>

                <h4 style="margin: 15px 0 10px 0;"><?php _e('4. Create OAuth 2.0 credentials', 'google-my-business-reviews'); ?></h4>
                <ol style="margin: 0 0 0 20px;">
                    <li><?php _e('Go to "APIs & Services" → "Credentials"', 'google-my-business-reviews'); ?></li>
                    <li><?php _e('Click on "+ CREATE CREDENTIALS" → "OAuth client ID"', 'google-my-business-reviews'); ?></li>
                    <li><?php _e('Application type: <strong>"Web Application"</strong>', 'google-my-business-reviews'); ?></li>
                    <li><?php _e('Name: give it a name (eg: "WordPress GMB Reviews")', 'google-my-business-reviews'); ?></li>
                    <li><strong><?php _e('Authorized redirect URIs', 'google-my-business-reviews'); ?></strong> : <?php _e('Add EXACTLY the URI above', 'google-my-business-reviews'); ?></li>
                    <li><?php _e('Click "Create"', 'google-my-business-reviews'); ?></li>
                    <li><?php _e('Copy the Client ID and Client Secret into the fields above', 'google-my-business-reviews'); ?></li>
                </ol>
            </div>
        </div>

    </div>
</div>
