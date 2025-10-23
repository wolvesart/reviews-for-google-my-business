<?php
/**
 * Plugin Name: Wolves - Avis Google
 * Plugin URI: https://wolvesart.com
 * Description: Affiche et gère vos avis Google My Business avec OAuth 2.0. Système de catégories, personnalisation avancée et shortcode flexible.
 * Version: 1.0.0
 * Author: WolvesArt
 * Author URI: https://wolvesart.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wolves-avis-google
 * Domain Path: /languages
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

// Définir les constantes du plugin
define('WOLVES_GMB_VERSION', '1.0.0');
define('WOLVES_GMB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOLVES_GMB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WOLVES_GMB_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Classe principale du plugin
 */
class Wolves_Avis_Google {

    /**
     * Instance unique du plugin (Singleton)
     */
    private static $instance = null;

    /**
     * Récupère l'instance unique du plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Charge les dépendances du plugin
     */
    private function load_dependencies() {
        // Charger les différents modules
        require_once WOLVES_GMB_PLUGIN_DIR . 'includes/config.php';
        require_once WOLVES_GMB_PLUGIN_DIR . 'includes/api.php';
        require_once WOLVES_GMB_PLUGIN_DIR . 'includes/database.php';
        require_once WOLVES_GMB_PLUGIN_DIR . 'includes/categories.php';
        require_once WOLVES_GMB_PLUGIN_DIR . 'includes/shortcode.php';  // Charger avant helpers.php
        require_once WOLVES_GMB_PLUGIN_DIR . 'includes/helpers.php';
        require_once WOLVES_GMB_PLUGIN_DIR . 'includes/admin.php';
    }

    /**
     * Initialise les hooks WordPress
     */
    private function init_hooks() {
        // Activation du plugin
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Désactivation du plugin
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Charger les traductions
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Ajouter le lien de configuration dans la liste des plugins
        add_filter('plugin_action_links_' . WOLVES_GMB_PLUGIN_BASENAME, array($this, 'add_action_links'));
    }

    /**
     * Actions à l'activation du plugin
     */
    public function activate() {
        // Créer les tables de base de données
        gmb_create_custom_reviews_table();
        gmb_create_categories_table();
        gmb_create_review_category_relation_table();

        // Flush les règles de réécriture
        flush_rewrite_rules();
    }

    /**
     * Actions à la désactivation du plugin
     */
    public function deactivate() {
        // Flush les règles de réécriture
        flush_rewrite_rules();
    }

    /**
     * Charge les traductions du plugin
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wolves-avis-google',
            false,
            dirname(WOLVES_GMB_PLUGIN_BASENAME) . '/languages'
        );
    }

    /**
     * Ajoute des liens dans la liste des plugins
     */
    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=gmb-settings') . '">' . __('Configuration', 'wolves-avis-google') . '</a>';
        $reviews_link = '<a href="' . admin_url('admin.php?page=gmb-manage-reviews') . '">' . __('Avis', 'wolves-avis-google') . '</a>';

        array_unshift($links, $settings_link, $reviews_link);

        return $links;
    }
}

/**
 * Fonction d'accès global au plugin
 */
function wolves_avis_google() {
    return Wolves_Avis_Google::get_instance();
}

// Initialiser le plugin
wolves_avis_google();
