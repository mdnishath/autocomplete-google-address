<?php

/**
 * Plugin Name: Autocomplete Google Address (Premium)
 * Plugin URI:        https://wordpress.org/plugins/autocomplete-google-address/
 * Description:       Add Google Places address autocomplete to any existing form in WordPress using a selector-based mapping builder.
 * Version:           5.6.2
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
// Re-validate our key online every 15 minutes on admin loads (hourly after a
// failure), before deciding below whether Freemius is needed; the License page
// always asks the server, so a revoked key shows as revoked there immediately.
// WP-Cron covers sites whose admin is rarely opened.
if ( is_admin() && !wp_doing_cron() ) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page check.
    $aga_on_license_page = isset( $_GET['page'] ) && 'aga-license' === $_GET['page'];
    AGA_License::maybe_refresh( false, ( $aga_on_license_page ? 10 : null ) );
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
            $aga_fs_config = array(
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
            );
            // While Freemius sales are off (AGA_License::FREEMIUS_SALES), Freemius only keeps
            // existing customers licensed: no trial offer, no upgrade / pricing menu.
            if ( !aga_freemius_sales_enabled() ) {
                unset($aga_fs_config['trial']);
                $aga_fs_config['menu']['pricing'] = false;
            }
            $google_autocomplete = fs_dynamic_init( $aga_fs_config );
            if ( !aga_freemius_sales_enabled() ) {
                $google_autocomplete->add_filter(
                    'is_submenu_visible',
                    function ( $is_visible, $submenu_id ) {
                        return ( in_array( $submenu_id, array('pricing', 'upgrade'), true ) ? false : $is_visible );
                    },
                    10,
                    2
                );
                $google_autocomplete->add_filter( 'show_trial', '__return_false' );
            }
        }
        return $google_autocomplete;
    }

    // Init Freemius.
    google_autocomplete();
    // Signal that SDK was initiated.
    do_action( 'google_autocomplete_loaded' );
}

/**
 * Don't let Freemius's connect / license-key screen take over the plugin. A site that isn't
 * connected to Freemius lands in the plugin itself (free features work), where upgrade prompts
 * offer both ways to buy side by side. Freemius stays available: its checkout, and "Opt In" /
 * "Activate License" on the Plugins screen. Runs once — Freemius remembers the choice.
 */
function aga_skip_freemius_takeover() {
	if ( ! is_admin() || ! function_exists( 'google_autocomplete' ) || AGA_License::is_valid() ) {
		return;
	}
	$fs = google_autocomplete();
	if ( ! is_object( $fs ) || ! $fs->is_activation_mode() || $fs->is_registered() || $fs->is_pending_activation() ) {
		return;
	}

	// The premium build sets a "require license activation" flag on plugin activation, which
	// forces the license-key screen. Clear it for THIS product only (Freemius's own
	// "Activate Free Version" link does the same via a site-wide request parameter).
	$clear = function () {
		if ( isset( $this->_storage ) && true === $this->_storage->require_license_activation ) {
			$this->_storage->require_license_activation = false;
		}
	};
	\Closure::bind( $clear, $fs, get_class( $fs ) )();

	// Same as pressing "Skip" on the opt-in screen: anonymous mode, no forced connect page.
	if ( $fs->is_enable_anonymous() && ! $fs->is_anonymous() ) {
		$fs->skip_connection();
	}
}
add_action( 'init', 'aga_skip_freemius_takeover', 1 );
/**
 * Currently plugin version.
 */
define( 'AGA_VERSION', '5.6.2' );
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