<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://profiles.wordpress.org/nishatbd31/
 * @since      1.0.0
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 * @author     Md Nishath Khandakar <https://profiles.wordpress.org/nishatbd31/>
 */
class AGA_Plugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      AGA_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'AGA_VERSION' ) ) {
			$this->version = AGA_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'autocomplete-google-address';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - AGA_Loader. Orchestrates the hooks of the plugin.
	 * - AGA_i18n. Defines internationalization functionality.
	 * - AGA_Admin. Defines all hooks for the admin area.
	 * - AGA_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once AGA_PLUGIN_DIR . 'includes/class-aga-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once AGA_PLUGIN_DIR . 'includes/class-aga-i18n.php';
        
        /**
         * The class responsible for handling plugin activation and deactivation.
         */
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-installer.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once AGA_PLUGIN_DIR . 'includes/class-aga-admin.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-settings.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-forms.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-autocomplete.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-presets.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-wizard.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-woocommerce.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-validation.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-health-check.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-import-export.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-saved-addresses.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-analytics.php';

		require_once AGA_PLUGIN_DIR . 'includes/class-aga-frontend.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-shortcode.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-elementor.php';
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-rest-api.php';

        require_once AGA_PLUGIN_DIR . 'helpers/aga-helpers.php';
        require_once AGA_PLUGIN_DIR . 'helpers/aga-languages.php';
        require_once AGA_PLUGIN_DIR . 'helpers/aga-countries.php';


		$this->loader = new AGA_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the AGA_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new AGA_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new AGA_Admin( $this->get_plugin_name(), $this->get_version() );
        $plugin_settings = new AGA_Settings();
        $plugin_forms = new AGA_Forms();
        new AGA_Wizard();
        
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
        $this->loader->add_action( 'admin_footer', $plugin_admin, 'render_whatsapp_chat' );
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'render_review_banner' );
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'check_script_conflicts' );
        $this->loader->add_action( 'wp_ajax_aga_dismiss_review', $plugin_admin, 'dismiss_review_banner' );
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'render_upgrade_banner' );
        $this->loader->add_action( 'wp_ajax_aga_dismiss_upgrade', $plugin_admin, 'dismiss_upgrade_banner' );
        $this->loader->add_action( 'admin_footer', $plugin_admin, 'render_import_export_ui' );

        // Import/Export functionality
        new AGA_Import_Export();

        // Health check diagnostics
        new AGA_Health_Check();

        // Usage Analytics (Pro)
        new AGA_Analytics();

        // Settings page hooks
        $this->loader->add_action( 'admin_init', $plugin_settings, 'register_settings' );

        // CPT hooks
        $this->loader->add_action( 'init', $plugin_forms, 'register_post_type' );
        $this->loader->add_action( 'add_meta_boxes', $plugin_forms, 'add_meta_boxes' );
        $this->loader->add_action( 'save_post_aga_form', $plugin_forms, 'save_meta_box_data' );
        $this->loader->add_filter( 'post_row_actions', $plugin_forms, 'add_duplicate_row_action', 10, 2 );
        $this->loader->add_action( 'admin_action_aga_duplicate_form', $plugin_forms, 'handle_duplicate_form' );
        $this->loader->add_filter( 'manage_aga_form_posts_columns', $plugin_forms, 'set_custom_edit_columns' );
        $this->loader->add_action( 'manage_aga_form_posts_custom_column', $plugin_forms, 'custom_column_content', 10, 2 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new AGA_Frontend( $this->get_plugin_name(), $this->get_version() );
        new AGA_WooCommerce();
        new AGA_Validation();
        new AGA_Shortcode();
        new AGA_Elementor();
        new AGA_Saved_Addresses();
        new AGA_REST_API();

        // Find globally active forms early.
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'load_automatic_forms' );

		// Enqueue scripts in the footer, after shortcodes have been processed.
		$this->loader->add_action( 'wp_footer', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'enqueue_scripts' );
        
        $this->loader->add_filter( 'script_loader_tag', $plugin_public, 'add_async_attribute', 10, 2 );
        
        // Custom dropdown styles (Pro).
        $this->loader->add_action( 'wp_head', $plugin_public, 'output_custom_styles' );

        // Shortcode
        $this->loader->add_shortcode( 'aga_form', $plugin_public, 'render_shortcode' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    AGA_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
