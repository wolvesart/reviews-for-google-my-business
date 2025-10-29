<?php
/**
 * Plugin Name: Reviews for Google My Business
 * Plugin URI: https://wolvesart.fr
 * Description: Display your Google My Business reviews on your website for free. Improve your credibility and gain trust. Category system, full customization, and flexible shortcode.
 * Version: 1.0.0
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
define('WOLVES_GMB_VERSION', '1.0.0');
define('WOLVES_GMB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOLVES_GMB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WOLVES_GMB_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Admin page slugs
define('WGMBR_MANAGE_PAGE_SLUG', 'gmb-manage-reviews');
define('WGMBR_SETTINGS_PAGE_SLUG', 'gmb-settings');
define('WGMBR_CATEGORIES_PAGE_SLUG', 'gmb-categories');

// Admin page hooks (for enqueue scripts)
define('WGMBR_MANAGE_PAGE_HOOK', 'toplevel_page_gmb-manage-reviews');
define('WGMBR_SETTINGS_PAGE_HOOK', 'google-reviews_page_gmb-settings');
define('WGMBR_CATEGORIES_PAGE_HOOK', 'google-reviews_page_gmb-categories');

// API Configuration
define('WGMBR_API_PAGE_SIZE', 100);
define('WGMBR_API_SORT_ORDER', 'updateTime desc');
define('WGMBR_API_MAX_PAGES', 10);
define('WGMBR_API_TIMEOUT', 15);

// Default limits
define('WGMBR_DEFAULT_REVIEW_LIMIT', 50);
define('WGMBR_ADMIN_REVIEWS_PER_PAGE', 20);

// Cache durations
define('WGMBR_CACHE_DURATION', HOUR_IN_SECONDS);

// Default colors
define('WGMBR_DEFAULT_COLORS', array(
    'card_bg' => '#F3F5F7',
    'star' => '#FFC83E',
    'text_primary' => '#222222',
    'accent' => '#0F68DD',
    'text_resume' => '#222222',
));

// Default card radius
define('WGMBR_DEFAULT_CARD_RADIUS', 8);


class reviews_for_google_my_business {

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
        require_once WOLVES_GMB_PLUGIN_DIR . 'includes/helpers.php';
        require_once WOLVES_GMB_PLUGIN_DIR . 'includes/config.php';
        require_once WOLVES_GMB_PLUGIN_DIR . 'includes/post-types.php';
        require_once WOLVES_GMB_PLUGIN_DIR . 'includes/api.php';
        require_once WOLVES_GMB_PLUGIN_DIR . 'includes/shortcode.php';
        require_once WOLVES_GMB_PLUGIN_DIR . 'includes/admin.php';
    }

    // Initializes WordPress hooks
    private function init_hooks() {
        // Plugin activation
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Plugin deactivation
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Add the configuration link to the plugins list
        add_filter('plugin_action_links_' . WOLVES_GMB_PLUGIN_BASENAME, array($this, 'add_action_links'));
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
        $settings_link = '<a href="' . admin_url('admin.php?page=gmb-settings') . '">' . esc_html__('Configuration', 'reviews-for-google-my-business') . '</a>';
        array_unshift($links, $settings_link);

        return $links;
    }
}

// Global plugin access function
function wgmbr_google_reviews_init() {
    return reviews_for_google_my_business::get_instance();
}

wgmbr_google_reviews_init();
