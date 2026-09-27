<?php

/**
 * Plugin Name: Autocomplete Google Address (Premium)
 * Plugin URI:        https://wordpress.org/plugins/autocomplete-google-address/
 * Description:       Add Google Places address autocomplete to any existing form in WordPress using a selector-based mapping builder.
 * Version:           5.5.0
 * Author:            Md Nishath Khandakar
 * Author URI:        https://profiles.wordpress.org/nishatbd31/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       autocomplete-google-address
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
/**
 * License client for keys bought on mdnishath.com (EPS). Also defines
 * aga_is_pro() and aga_checkout_url(), which every premium check uses.
 */
require_once dirname( __FILE__ ) . '/includes/class-aga-license.php';
AGA_License::init( __FILE__ );
// Re-validate our key online at most once a day (hourly after a failure) on admin
// loads, before deciding below whether Freemius is needed. WP-Cron covers sites
// whose admin is rarely opened.
if ( is_admin() && !wp_doing_cron() ) {
    AGA_License::maybe_refresh();
}
// Customers with a valid mdnishath.com license don't use Freemius at all: skip its
// init so none of its UI (opt-in, account, pricing, upgrade prompts) is shown.
// google_autocomplete() is then undefined; always go through aga_is_pro() /
// aga_checkout_url() instead of calling it directly.
if ( !AGA_License::is_valid() && !function_exists( 'google_autocomplete' ) ) {
    // Create a helper function for easy SDK access.
    function google_autocomplete() {
        global $google_autocomplete;
        if ( !isset( $google_autocomplete ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';
            $google_autocomplete = fs_dynamic_init( array(
                'id'               => '6886',
                'slug'             => 'form-autocomplete-nish',
                'type'             => 'plugin',
                'public_key'       => 'pk_f939b69fc6977108e74fa9e7e3136',
                'is_premium'       => true,
                'is_premium_only'  => false,
                'has_addons'       => false,
                'has_paid_plans'   => true,
                'trial'            => array(
                    'days'               => 3,
                    'is_require_payment' => true,
                ),
                'has_affiliation'  => 'all',
                'menu'             => array(
                    'slug'       => 'edit.php?post_type=aga_form',
                    'first-path' => 'admin.php?page=aga-settings',
                    'support'    => false,
                ),
                'is_live'          => true,
                'is_org_compliant' => true,
            ) );
        }
        return $google_autocomplete;
    }

    // Init Freemius.
    google_autocomplete();
    // Signal that SDK was initiated.
    do_action( 'google_autocomplete_loaded' );
}
/**
 * Currently plugin version.
 */
define( 'AGA_VERSION', '5.5.0' );
/**
 * Plugin directory path.
 */
define( 'AGA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
/**
 * Plugin directory URL.
 */
define( 'AGA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require AGA_PLUGIN_DIR . 'includes/class-aga-plugin.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_aga_plugin() {
    $plugin = new AGA_Plugin();
    $plugin->run();
}

run_aga_plugin();