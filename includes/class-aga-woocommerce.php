<?php
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce zero-config integration.
 *
 * Adds Google Places autocomplete to WooCommerce checkout address fields
 * automatically when WooCommerce is active and the integration is enabled.
 * Registers both classic and block checkout field sets; whichever exists on
 * the page wins — no user configuration needed.
 */
class AGA_WooCommerce {

	/**
	 * Initialize hooks.
	 */
	public function __construct() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( ! function_exists( 'google_autocomplete' ) || ! google_autocomplete()->is_paying() ) {
			return;
		}

		if ( ! aga_get_setting( 'woocommerce_enabled' ) ) {
			return;
		}

		add_filter( 'aga_form_configs', array( $this, 'add_checkout_configs' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
		add_action( 'wp_footer', array( $this, 'output_woo_helper_script' ) );
		add_action( 'admin_notices', array( $this, 'forms_list_notice' ) );
	}

	/**
	 * Show an info notice on the Forms list page when WooCommerce integration is active.
	 */
	public function forms_list_notice() {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-aga_form' !== $screen->id ) {
			return;
		}

		$settings_url = admin_url( 'edit.php?post_type=aga_form&page=aga-settings&tab=woocommerce' );
		?>
		<div class="notice notice-info is-dismissible">
			<p>
				<span class="dashicons dashicons-cart" style="color: #7f54b3; margin-right: 4px;"></span>
				<strong><?php esc_html_e( 'WooCommerce Checkout Autocomplete is active', 'autocomplete-google-address' ); ?></strong>
				&mdash;
				<?php esc_html_e( 'Billing and shipping address fields are auto-configured. No form entry needed.', 'autocomplete-google-address' ); ?>
				<a href="<?php echo esc_url( $settings_url ); ?>"><?php esc_html_e( 'Manage in Settings', 'autocomplete-google-address' ); ?> &rarr;</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Force-load frontend assets on the WooCommerce checkout page.
	 */
	public function maybe_enqueue() {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}

		add_filter( 'aga_should_load_frontend', '__return_true' );
	}

	/**
	 * Add WooCommerce checkout configs to the frontend config array.
	 *
	 * @param array $configs Existing form configs.
	 * @return array
	 */
	public function add_checkout_configs( $configs ) {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return $configs;
		}

		// Skip dynamic injection if WooCommerce form configs were created by the wizard.
		$existing = get_posts( array(
			'post_type'      => 'aga_form',
			'posts_per_page' => 1,
			'meta_key'       => 'Nish_aga_form_preset',
			'meta_value'     => 'woocommerce',
			'fields'         => 'ids',
		) );
		if ( ! empty( $existing ) ) {
			return $configs;
		}

		// Register the classic AND block field sets. Sniffing the checkout page
		// content for the `woocommerce/checkout` block misses every setup that
		// renders checkout some other way — a template part, a shortcode inside
		// a block theme, a page builder, or a plugin that swaps the template —
		// and guessing wrong means no autocomplete at all. Configs whose main
		// selector is absent from the DOM are skipped on the frontend, so
		// shipping both is free and always matches.
		$configs = array_merge(
			$configs,
			$this->get_classic_checkout_configs(),
			$this->get_block_checkout_configs()
		);

		return $configs;
	}

	/**
	 * State and country name formats for the checkout fields.
	 *
	 * WooCommerce stores ISO codes (`US`, `CA`), so `short` is the correct
	 * default. The setting exists for stores whose theme or a third-party
	 * plugin has replaced the state/country selects with plain text inputs,
	 * where a customer would rather see "California" than "CA".
	 *
	 * @return array
	 */
	private function get_formats() {
		$state   = aga_get_setting( 'woocommerce_state_format', 'short' );
		$country = aga_get_setting( 'woocommerce_country_format', 'short' );

		return array(
			'state'   => ( 'long' === $state ) ? 'long' : 'short',
			'country' => ( 'long' === $country ) ? 'long' : 'short',
		);
	}

	/**
	 * Configs for the classic WooCommerce checkout.
	 *
	 * @return array
	 */
	private function get_classic_checkout_configs() {
		$formats = $this->get_formats();

		return array(
			array(
				'form_id'                => 'woo_billing',
				'mode'                   => 'smart_mapping',
				'main_selector'          => '#billing_address_1',
				'selectors'              => array(
					'street'  => '#billing_address_1',
					'city'    => '#billing_city',
					'state'   => '#billing_state',
					'zip'     => '#billing_postcode',
					'country' => '#billing_country',
				),
				'formats'                => $formats,
				'component_restrictions' => array(),
				'place_types'            => '',
				'geolocation'            => false,
				'address_validation'     => false,
				'saved_addresses'        => false,
				'map_picker'             => false,
			),
			array(
				'form_id'                => 'woo_shipping',
				'mode'                   => 'smart_mapping',
				'main_selector'          => '#shipping_address_1',
				'selectors'              => array(
					'street'  => '#shipping_address_1',
					'city'    => '#shipping_city',
					'state'   => '#shipping_state',
					'zip'     => '#shipping_postcode',
					'country' => '#shipping_country',
				),
				'formats'                => $formats,
				'component_restrictions' => array(),
				'place_types'            => '',
				'geolocation'            => false,
				'address_validation'     => false,
				'saved_addresses'        => false,
				'map_picker'             => false,
			),
		);
	}

	/**
	 * Configs for the WooCommerce block-based checkout.
	 *
	 * @return array
	 */
	private function get_block_checkout_configs() {
		$formats = $this->get_formats();

		return array(
			array(
				'form_id'                => 'woo_block_billing',
				'mode'                   => 'smart_mapping',
				'main_selector'          => '#billing-address_1',
				'selectors'              => array(
					'street'  => '#billing-address_1',
					'city'    => '#billing-city',
					'state'   => '#billing-state',
					'zip'     => '#billing-postcode',
					'country' => '#billing-country',
				),
				'formats'                => $formats,
				'component_restrictions' => array(),
				'place_types'            => '',
				'geolocation'            => false,
				'address_validation'     => false,
				'saved_addresses'        => false,
				'map_picker'             => false,
			),
			array(
				'form_id'                => 'woo_block_shipping',
				'mode'                   => 'smart_mapping',
				'main_selector'          => '#shipping-address_1',
				'selectors'              => array(
					'street'  => '#shipping-address_1',
					'city'    => '#shipping-city',
					'state'   => '#shipping-state',
					'zip'     => '#shipping-postcode',
					'country' => '#shipping-country',
				),
				'formats'                => $formats,
				'component_restrictions' => array(),
				'place_types'            => '',
				'geolocation'            => false,
				'address_validation'     => false,
				'saved_addresses'        => false,
				'map_picker'             => false,
			),
		);
	}

	/**
	 * Output a small helper script for WooCommerce checkout compatibility.
	 *
	 * Handles:
	 * - Classic checkout: Re-initializes autocomplete on shipping fields when
	 *   "Ship to a different address" is toggled (fields become visible).
	 * - Block checkout: Uses MutationObserver to detect when shipping fields
	 *   are rendered dynamically by React.
	 */
	public function output_woo_helper_script() {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}
		?>
		<script id="aga-woo-helper">
		(function() {
			'use strict';

			/**
			 * Re-run autocomplete setup for configs whose main_selector
			 * now exists in the DOM but wasn't found on initial run.
			 * Uses the global aga_reinit() exposed by frontend.js.
			 */
			function agaReinitConfigs() {
				if (typeof window.aga_reinit === 'function') {
					window.aga_reinit();
				}
			}

			// Classic checkout: WooCommerce fires updated_checkout after AJAX updates.
			if (typeof jQuery !== 'undefined') {
				jQuery(document.body).on('updated_checkout', function() {
					agaReinitConfigs();
				});

				// Also listen for the shipping toggle checkbox.
				jQuery(document.body).on('change', '#ship-to-different-address-checkbox', function() {
					// Small delay to let WooCommerce show/hide shipping fields.
					setTimeout(agaReinitConfigs, 300);
				});
			}

			// Block checkout: Use MutationObserver to detect dynamically rendered fields.
			var reinitTimer = null;
			var observer = new MutationObserver(function(mutations) {
				var shouldCheck = false;
				for (var i = 0; i < mutations.length; i++) {
					if (mutations[i].addedNodes.length > 0) {
						shouldCheck = true;
						break;
					}
				}
				if (shouldCheck) {
					// Debounce to avoid rapid-fire calls during React renders.
					clearTimeout(reinitTimer);
					reinitTimer = setTimeout(agaReinitConfigs, 250);
				}
			});

			// Start observing once the DOM is ready.
			function startObserver() {
				var checkout = document.querySelector('.wc-block-checkout') ||
				               document.querySelector('.woocommerce-checkout') ||
				               document.querySelector('#customer_details');
				if (checkout) {
					observer.observe(checkout, { childList: true, subtree: true });
				}
			}

			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', startObserver);
			} else {
				startObserver();
			}
		})();
		</script>
		<?php
	}
}
