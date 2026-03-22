<?php
/**
 * Handles plugin activation and deactivation.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 * @author     Md Nishath Khandakar <https://profiles.wordpress.org/nishatbd31/>
 */

defined( 'ABSPATH' ) || exit;

class AGA_Installer {

    /**
     * The single instance of the class.
     *
     * @var AGA_Installer
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main AGA_Installer Instance.
     *
     * Ensures only one instance of AGA_Installer is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @return AGA_Installer - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        // Activation and deactivation hooks.
        register_activation_hook( AGA_PLUGIN_DIR . 'autocomplete-google-address.php', array( $this, 'activate' ) );
        register_deactivation_hook( AGA_PLUGIN_DIR . 'autocomplete-google-address.php', array( $this, 'deactivate' ) );
    }

    /**
     * Plugin activation callback.
     *
     * Creates the custom post type and flushes rewrite rules.
     *
     * @since 1.0.0
     */
    public function activate() {
        // The AGA_Forms class handles the CPT registration.
        // We need to ensure it's loaded before we can use it.
        require_once AGA_PLUGIN_DIR . 'includes/class-aga-forms.php';

        // Register the post type so it's available.
        $forms = new AGA_Forms();
        $forms->register_post_type();

        // Flush rewrite rules to ensure the CPT URLs work correctly.
        flush_rewrite_rules();

        // Set transients for activation notice and setup wizard.
        set_transient( 'aga_activation_notice', true, 5 );
        set_transient( 'aga_show_wizard', true, 30 );

        // Record activation time for review banner (only set once).
        if ( ! get_option( 'aga_activation_time' ) ) {
            update_option( 'aga_activation_time', time() );
        }

        // Create analytics table (Pro feature).
        if ( class_exists( 'AGA_Analytics' ) ) {
            AGA_Analytics::create_table();
        } else {
            require_once AGA_PLUGIN_DIR . 'includes/class-aga-analytics.php';
            AGA_Analytics::create_table();
        }
    }

    /**
     * Plugin deactivation callback.
     *
     * Flushes rewrite rules to remove the CPT's rewrite rules.
     *
     * @since 1.0.0
     */
    public function deactivate() {
        // Unregister the post type is not necessary as it will be gone on the next load.
        // Flush rewrite rules to clean up the permalinks.
        flush_rewrite_rules();
    }
}

// Instantiate the installer class.
AGA_Installer::instance();
