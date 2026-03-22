<?php
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce zero-config integration.
 *
 * Adds Google Places autocomplete to WooCommerce checkout address fields
 * automatically when WooCommerce is active and the integration is enabled.
 * Auto-detects classic vs block checkout — no user configuration needed.
 */
class AGA_WooCommerce {

	/**
	 * Whether the checkout page uses the block-based checkout.
	 *
	 * @var bool|null Null means not yet detected.
	 */
	private $is_block_checkout = null;

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

		$is_block = $this->detect_block_checkout();

		if ( $is_block ) {
			// Block checkout: inject BOTH classic and block selectors.
			// Some themes/plugins render classic fields as fallback inside block checkout.
			$configs = array_merge( $configs, $this->get_block_checkout_configs() );
		} else {
			$configs = array_merge( $configs, $this->get_classic_checkout_configs() );
		}

		return $configs;
	}

	/**
	 * Auto-detect whether the checkout page uses the block-based checkout.
	 *
	 * Checks if the WooCommerce checkout page content contains the
	 * `woocommerce/checkout` block. Falls back to classic checkout.
	 *
	 * @return bool
	 */
	private function detect_block_checkout() {
		if ( null !== $this->is_block_checkout ) {
			return $this->is_block_checkout;
		}

		$this->is_block_checkout = false;

		// Method 1: Check if the checkout page post content contains the checkout block.
		$checkout_page_id = wc_get_page_id( 'checkout' );
		if ( $checkout_page_id > 0 ) {
			$post = get_post( $checkout_page_id );
			if ( $post && has_block( 'woocommerce/checkout', $post ) ) {
				$this->is_block_checkout = true;
				return true;
			}
		}

		// Method 2: Check if the WooCommerce Blocks checkout is registered.
		if ( class_exists( '\Automattic\WooCommerce\Blocks\BlockTypes\Checkout' ) ) {
			// The block class exists, but the page might not use it.
			// We already checked post content above, so this is a secondary signal.
		}

		return $this->is_block_checkout;
	}

	/**
	 * Configs for the classic WooCommerce checkout.
	 *
	 * @return array
	 */
	private function get_classic_checkout_configs() {
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
				'formats'                => array(
					'state'   => 'short',
					'country' => 'short',
				),
				'component_restrictions' => array(),
				'place_types'            => '',
				'show_map_preview'       => false,
				'geolocation'            => false,
				'address_validation'     => false,
				'saved_addresses'        => false,
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
				'formats'                => array(
					'state'   => 'short',
					'country' => 'short',
				),
				'component_restrictions' => array(),
				'place_types'            => '',
				'show_map_preview'       => false,
				'geolocation'            => false,
				'address_validation'     => false,
				'saved_addresses'        => false,
			),
		);
	}

	/**
	 * Configs for the WooCommerce block-based checkout.
	 *
	 * @return array
	 */
	private function get_block_checkout_configs() {
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
				'formats'                => array(
					'state'   => 'short',
					'country' => 'short',
				),
				'component_restrictions' => array(),
				'place_types'            => '',
				'show_map_preview'       => false,
				'geolocation'            => false,
				'address_validation'     => false,
				'saved_addresses'        => false,
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
				'formats'                => array(
					'state'   => 'short',
					'country' => 'short',
				),
				'component_restrictions' => array(),
				'place_types'            => '',
				'show_map_preview'       => false,
				'geolocation'            => false,
				'address_validation'     => false,
				'saved_addresses'        => false,
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
