<?php
/**
 * Plugin Name: Reviews for Google My Business
 * Description: Display your Google My Business reviews on your website for free. Improve your credibility and gain trust. Category system, full customization, and flexible shortcode.
 * Version: 1.1.2
 * Requires at least: 6.8
 * Requires PHP: 8.0
 * Author: Wolvesart
 * Author URI: https://wolvesart.fr
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: reviews-for-google-my-business
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
// Using WGMBR prefix (5+ characters) for WordPress.org compliance
define('WGMBR_VERSION', '1.1.2');
define('WGMBR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WGMBR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WGMBR_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Admin page slugs (using wgmbr- prefix for compliance)
define('WGMBR_MANAGE_PAGE_SLUG', 'wgmbr-manage-reviews');
define('WGMBR_SETTINGS_PAGE_SLUG', 'wgmbr-settings');
define('WGMBR_CATEGORIES_PAGE_SLUG', 'wgmbr-categories');

// Note: les hooks des pages admin sont capturés dynamiquement depuis les valeurs
// de retour de add_menu_page()/add_submenu_page() (voir wgmbr_admin_page_hooks()),
// car WordPress les construit à partir du titre *traduit* du menu parent.

// API Configuration
define('WGMBR_API_PAGE_SIZE', 100);
define('WGMBR_API_SORT_ORDER', 'updateTime desc');
define('WGMBR_API_MAX_PAGES', 10);
define('WGMBR_API_TIMEOUT', 15);

// Default limits
define('WGMBR_DEFAULT_REVIEW_LIMIT', 100);
define('WGMBR_ADMIN_REVIEWS_PER_PAGE', 20);

// Layouts
define('WGMBR_DEFAULT_LAYOUT', 'slider');
define('WGMBR_ALLOWED_LAYOUTS', array('slider', 'masonry'));
define('WGMBR_MASONRY_INITIAL', 9);
define('WGMBR_MASONRY_BATCH', 9);

// Cache durations
define('WGMBR_CACHE_DURATION', HOUR_IN_SECONDS);

// Default colors
define('WGMBR_DEFAULT_COLORS', array(
    'card_bg' => '#F3F5F7',
    'star' => '#FFC83E',
    'text_primary' => '#222222',
    'accent' => '#0F68DD',      // Slider navigation (arrows + pagination dots)
    'text_resume' => '#222222',
    'show_more' => '#0F68DD',   // Masonry "Show more" button
));

// Default card radius (must match the --gmb-radius-card fallback in frontend CSS)
define('WGMBR_DEFAULT_CARD_RADIUS', 16);


if (!class_exists('WGMBR_Plugin')) {

class WGMBR_Plugin {

    // Single instance of the plugin (Singleton)
    private static $instance = null;

    // Retrieves the single instance of the plugin
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Private Builder (Singleton)
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    // Loads plugin dependencies
    private function load_dependencies() {
        // Load the different modules
        // IMPORTANT: helpers.php must be loaded first as it contains encryption functions used by config.php
        require_once WGMBR_PLUGIN_DIR . 'includes/helpers.php';
        require_once WGMBR_PLUGIN_DIR . 'includes/config.php';
        require_once WGMBR_PLUGIN_DIR . 'includes/post-types.php';
        require_once WGMBR_PLUGIN_DIR . 'includes/api.php';
        require_once WGMBR_PLUGIN_DIR . 'includes/shortcode.php';
        require_once WGMBR_PLUGIN_DIR . 'includes/admin.php';
    }

    // Initializes WordPress hooks
    private function init_hooks() {
        // Load translations
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Plugin activation
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Plugin deactivation
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Add the configuration link to the plugins list
        add_filter('plugin_action_links_' . WGMBR_PLUGIN_BASENAME, array($this, 'add_action_links'));

        // Clean up local profile photo when review is deleted
        add_action('before_delete_post', array($this, 'cleanup_review_photo'), 10, 2);
    }

    // Load plugin translations
    public function load_textdomain() {
        load_plugin_textdomain(
            'reviews-for-google-my-business',
            false,
            dirname(WGMBR_PLUGIN_BASENAME) . '/languages'
        );
    }

    /**
     * Cleanup local profile photo when a review is deleted
     *
     * @param int $post_id Post ID
     * @param WP_Post $post Post object
     */
    public function cleanup_review_photo($post_id, $post) {
        // Only process for gmb_review post type
        if ($post->post_type !== 'wgmbr_review') {
            return;
        }

        // Get review ID
        $review_id = get_post_meta($post_id, '_wgmbr_review_id', true);
        if (empty($review_id)) {
            return;
        }

        // Build photo path
        $upload_dir = wp_upload_dir();
        $gmb_dir = $upload_dir['basedir'] . '/gmb-reviews';
        $sanitized_id = sanitize_file_name($review_id);
        $filename = 'profile-' . $sanitized_id . '.jpg';
        $filepath = $gmb_dir . '/' . $filename;

        // Delete photo file if it exists
        if (file_exists($filepath)) {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- Direct file operation needed for cleanup
            unlink($filepath);
        }
    }

    // Actions when activating the plugin
    public function activate() {
        // Register CPT and taxonomy
        wgmbr_register_review_post_type();
        wgmbr_register_category_taxonomy();

        flush_rewrite_rules();
    }

    // Actions when deactivating the plugin
    public function deactivate() {
        flush_rewrite_rules();
    }

    // Add links to the plugins list
    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wgmbr-settings') . '">' . esc_html__('Configuration', 'reviews-for-google-my-business') . '</a>';
        array_unshift($links, $settings_link);

        return $links;
    }
}

} // end class_exists('WGMBR_Plugin')

// Global plugin access function
function wgmbr_google_reviews_init() {
    return WGMBR_Plugin::get_instance();
}

wgmbr_google_reviews_init();
