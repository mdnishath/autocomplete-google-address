<?php
/**
 * Provides the admin area view for the settings page.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/admin/views
 */

defined( 'ABSPATH' ) || exit;

$options       = get_option( 'Nish_aga_settings' );
$is_paying     = function_exists( 'google_autocomplete' ) && google_autocomplete()->is_paying();
$woo_active    = class_exists( 'WooCommerce' );
$checkout_url  = function_exists( 'google_autocomplete' ) ? google_autocomplete()->checkout_url() : '#';

// Masked API key display.
$current_api_key = $options['api_key'] ?? '';
$masked_api_key  = '';
if ( ! empty( $current_api_key ) ) {
	$length         = strlen( $current_api_key );
	$masked_api_key = str_repeat( "\xE2\x80\xA2", max( 0, $length - 4 ) ) . substr( $current_api_key, -4 );
}
?>
<div class="wrap" id="aga-settings-page">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="post" action="options.php">
		<?php settings_fields( 'Nish_aga_settings_group' ); ?>
		<input type="hidden" name="Nish_aga_settings[_aga_tab]" id="aga-active-tab" value="general">

		<!-- Tab Navigation -->
		<nav class="aga-tabs">
			<button type="button" class="aga-tab active" data-tab="general">
				<span class="dashicons dashicons-admin-generic"></span>
				<?php esc_html_e( 'General', 'autocomplete-google-address' ); ?>
			</button>
			<button type="button" class="aga-tab" data-tab="woocommerce">
				<span class="dashicons dashicons-cart"></span>
				<?php esc_html_e( 'WooCommerce', 'autocomplete-google-address' ); ?>
				<span class="aga-badge-pro"><?php esc_html_e( 'Pro', 'autocomplete-google-address' ); ?></span>
			</button>
			<button type="button" class="aga-tab" data-tab="appearance">
				<span class="dashicons dashicons-art"></span>
				<?php esc_html_e( 'Appearance', 'autocomplete-google-address' ); ?>
				<span class="aga-badge-pro"><?php esc_html_e( 'Pro', 'autocomplete-google-address' ); ?></span>
			</button>
			<button type="button" class="aga-tab" data-tab="advanced">
				<span class="dashicons dashicons-admin-tools"></span>
				<?php esc_html_e( 'Advanced', 'autocomplete-google-address' ); ?>
			</button>
		</nav>

		<!-- Tab: General -->
		<div class="aga-tab-panel active" data-tab="general">
			<div class="aga-card">
				<div class="aga-card-header">
					<h2><?php esc_html_e( 'Google Maps API Settings', 'autocomplete-google-address' ); ?></h2>
				</div>
				<div class="aga-card-body">
					<div class="aga-field-group">
						<label for="api_key"><strong><?php esc_html_e( 'Google Maps API Key', 'autocomplete-google-address' ); ?></strong></label>
						<div class="aga-api-key-row">
							<input type="text" id="api_key" name="Nish_aga_settings[api_key]" value="" class="regular-text" placeholder="<?php echo esc_attr( ! empty( $masked_api_key ) ? $masked_api_key : __( 'Enter your Google Maps API Key', 'autocomplete-google-address' ) ); ?>" />
							<button type="button" id="aga-validate-key-btn" class="button button-secondary">
								<span class="dashicons dashicons-yes-alt aga-icon-inline"></span>
								<?php esc_html_e( 'Validate Key', 'autocomplete-google-address' ); ?>
							</button>
							<span id="aga-key-status"></span>
						</div>
						<p class="description">
							<?php echo wp_kses_post( __( 'You must enable the <strong>Places API (New)</strong> and <strong>Maps JavaScript API</strong> in your Google Cloud console.', 'autocomplete-google-address' ) ); ?><br/>
							<?php esc_html_e( 'Leave this field blank to keep the existing API key. Enter a new key to update it.', 'autocomplete-google-address' ); ?>
						</p>
					</div>

					<hr />

					<div class="aga-field-group">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=aga-wizard' ) ); ?>" class="button button-secondary">
							<span class="dashicons dashicons-admin-settings aga-icon-inline"></span>
							<?php esc_html_e( 'Run Setup Wizard', 'autocomplete-google-address' ); ?>
						</a>
						<p class="description">
							<?php esc_html_e( 'Launch the guided setup wizard to configure the plugin step by step.', 'autocomplete-google-address' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>

		<!-- Tab: WooCommerce -->
		<div class="aga-tab-panel" data-tab="woocommerce">
			<div class="aga-card">
				<div class="aga-card-header">
					<h2><?php esc_html_e( 'WooCommerce Integration', 'autocomplete-google-address' ); ?></h2>
				</div>
				<div class="aga-card-body">
					<?php if ( ! $woo_active ) : ?>
						<div class="notice notice-warning inline aga-mb-md">
							<p>
								<span class="dashicons dashicons-warning aga-icon-inline"></span>
								<?php esc_html_e( 'WooCommerce is not installed or active.', 'autocomplete-google-address' ); ?>
							</p>
						</div>
					<?php endif; ?>

					<?php if ( ! $is_paying ) : ?>
						<!-- Pro upgrade banner -->
						<div class="aga-premium-gated-content">
							<div class="aga-premium-overlay">
								<div class="aga-premium-message">
									<span class="dashicons dashicons-lock aga-icon-inline"></span>
									<h3><?php esc_html_e( 'Pro Feature', 'autocomplete-google-address' ); ?></h3>
									<p><?php esc_html_e( 'WooCommerce auto-integration is available in the Pro plan. Upgrade to automatically add address autocomplete to your checkout.', 'autocomplete-google-address' ); ?></p>
									<a href="<?php echo esc_url( $checkout_url ); ?>" class="button button-primary" target="_blank">
										<?php esc_html_e( 'Upgrade to Pro', 'autocomplete-google-address' ); ?>
									</a>
								</div>
							</div>

							<!-- Blurred / disabled preview of the toggle -->
							<div class="aga-pro-gate-content">
								<div class="aga-field-group">
									<label>
										<strong><?php esc_html_e( 'Enable WooCommerce Auto-Integration', 'autocomplete-google-address' ); ?></strong>
									</label>
									<label class="aga-switch">
										<input type="checkbox" disabled checked>
										<span class="aga-slider"></span>
									</label>
								</div>
							</div>
						</div>
					<?php else : ?>
						<!-- WooCommerce Auto-Integration Toggle -->
						<div class="aga-field-group">
							<label for="woocommerce_enabled_toggle">
								<strong><?php esc_html_e( 'Enable WooCommerce Auto-Integration', 'autocomplete-google-address' ); ?></strong>
							</label>
							<label class="aga-switch">
								<input type="checkbox" id="woocommerce_enabled_toggle" name="Nish_aga_settings[woocommerce_enabled]" value="1" <?php checked( $options['woocommerce_enabled'] ?? 1, 1 ); ?>>
								<span class="aga-slider"></span>
							</label>
							<label for="woocommerce_enabled_toggle">
								<?php esc_html_e( 'Enable WooCommerce Auto-Integration', 'autocomplete-google-address' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Automatically add address autocomplete to WooCommerce billing and shipping fields. No form configuration needed.', 'autocomplete-google-address' ); ?>
							</p>
						</div>

						<div class="notice notice-info inline aga-mb-md" style="margin-top: 15px;">
							<p>
								<span class="dashicons dashicons-info aga-icon-inline"></span>
								<?php esc_html_e( 'Autocomplete will automatically work on your checkout page. Both classic and block-based checkouts are detected and supported. No additional configuration needed.', 'autocomplete-google-address' ); ?>
							</p>
						</div>

						<?php
						$checkout_page_url = '';
						if ( function_exists( 'wc_get_checkout_url' ) ) {
							$checkout_page_url = wc_get_checkout_url();
						}
						if ( $woo_active && ! empty( $checkout_page_url ) ) : ?>
							<div class="aga-field-group" style="margin-top: 10px;">
								<a href="<?php echo esc_url( $checkout_page_url ); ?>" class="button button-secondary" target="_blank">
									<span class="dashicons dashicons-visibility aga-icon-inline"></span>
									<?php esc_html_e( 'Test Checkout', 'autocomplete-google-address' ); ?>
								</a>
								<p class="description">
									<?php esc_html_e( 'Open your checkout page to verify address autocomplete is working.', 'autocomplete-google-address' ); ?>
								</p>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Tab: Appearance -->
		<div class="aga-tab-panel" data-tab="appearance">
			<div class="aga-card">
				<div class="aga-card-header">
					<h2><?php esc_html_e( 'Dropdown Appearance', 'autocomplete-google-address' ); ?></h2>
				</div>
				<div class="aga-card-body">
					<?php if ( ! $is_paying ) : ?>
						<!-- Pro upgrade banner -->
						<div class="aga-premium-gated-content">
							<div class="aga-premium-overlay">
								<div class="aga-premium-message">
									<span class="dashicons dashicons-lock aga-icon-inline"></span>
									<h3><?php esc_html_e( 'Pro Feature', 'autocomplete-google-address' ); ?></h3>
									<p><?php esc_html_e( 'Custom dropdown styling is available in the Pro plan. Upgrade to customize colors, borders, font size, and more.', 'autocomplete-google-address' ); ?></p>
									<a href="<?php echo esc_url( $checkout_url ); ?>" class="button button-primary" target="_blank">
										<?php esc_html_e( 'Upgrade to Pro', 'autocomplete-google-address' ); ?>
									</a>
								</div>
							</div>

							<!-- Blurred / disabled preview of the settings -->
							<div class="aga-pro-gate-content">
								<div class="aga-appearance-grid">
									<div class="aga-field-group">
										<label><strong><?php esc_html_e( 'Dropdown Background Color', 'autocomplete-google-address' ); ?></strong></label>
										<input type="color" value="#ffffff" disabled />
									</div>
									<div class="aga-field-group">
										<label><strong><?php esc_html_e( 'Dropdown Text Color', 'autocomplete-google-address' ); ?></strong></label>
										<input type="color" value="#333333" disabled />
									</div>
									<div class="aga-field-group">
										<label><strong><?php esc_html_e( 'Dropdown Hover Color', 'autocomplete-google-address' ); ?></strong></label>
										<input type="color" value="#f0f0f0" disabled />
									</div>
									<div class="aga-field-group">
										<label><strong><?php esc_html_e( 'Dropdown Border Color', 'autocomplete-google-address' ); ?></strong></label>
										<input type="color" value="#dddddd" disabled />
									</div>
									<div class="aga-field-group">
										<label><strong><?php esc_html_e( 'Border Radius (px)', 'autocomplete-google-address' ); ?></strong></label>
										<input type="number" value="4" disabled />
									</div>
									<div class="aga-field-group">
										<label><strong><?php esc_html_e( 'Font Size (px)', 'autocomplete-google-address' ); ?></strong></label>
										<input type="number" value="14" disabled />
									</div>
									<div class="aga-field-group">
										<label><strong><?php esc_html_e( 'Max Height (px)', 'autocomplete-google-address' ); ?></strong></label>
										<input type="number" value="250" disabled />
									</div>
								</div>
							</div>
						</div>
					<?php else : ?>
						<div class="aga-appearance-grid">
							<div class="aga-field-group">
								<label for="aga_dropdown_bg_color"><strong><?php esc_html_e( 'Dropdown Background Color', 'autocomplete-google-address' ); ?></strong></label>
								<input type="color" id="aga_dropdown_bg_color" name="Nish_aga_settings[dropdown_bg_color]" value="<?php echo esc_attr( $options['dropdown_bg_color'] ?? '#ffffff' ); ?>" class="aga-appearance-input" />
							</div>
							<div class="aga-field-group">
								<label for="aga_dropdown_text_color"><strong><?php esc_html_e( 'Dropdown Text Color', 'autocomplete-google-address' ); ?></strong></label>
								<input type="color" id="aga_dropdown_text_color" name="Nish_aga_settings[dropdown_text_color]" value="<?php echo esc_attr( $options['dropdown_text_color'] ?? '#333333' ); ?>" class="aga-appearance-input" />
							</div>
							<div class="aga-field-group">
								<label for="aga_dropdown_hover_color"><strong><?php esc_html_e( 'Dropdown Hover Color', 'autocomplete-google-address' ); ?></strong></label>
								<input type="color" id="aga_dropdown_hover_color" name="Nish_aga_settings[dropdown_hover_color]" value="<?php echo esc_attr( $options['dropdown_hover_color'] ?? '#f0f0f0' ); ?>" class="aga-appearance-input" />
							</div>
							<div class="aga-field-group">
								<label for="aga_dropdown_border_color"><strong><?php esc_html_e( 'Dropdown Border Color', 'autocomplete-google-address' ); ?></strong></label>
								<input type="color" id="aga_dropdown_border_color" name="Nish_aga_settings[dropdown_border_color]" value="<?php echo esc_attr( $options['dropdown_border_color'] ?? '#dddddd' ); ?>" class="aga-appearance-input" />
							</div>
							<div class="aga-field-group">
								<label for="aga_dropdown_border_radius"><strong><?php esc_html_e( 'Border Radius (px)', 'autocomplete-google-address' ); ?></strong></label>
								<input type="number" id="aga_dropdown_border_radius" name="Nish_aga_settings[dropdown_border_radius]" value="<?php echo esc_attr( $options['dropdown_border_radius'] ?? '4' ); ?>" min="0" max="50" class="small-text aga-appearance-input" />
							</div>
							<div class="aga-field-group">
								<label for="aga_dropdown_font_size"><strong><?php esc_html_e( 'Font Size (px)', 'autocomplete-google-address' ); ?></strong></label>
								<input type="number" id="aga_dropdown_font_size" name="Nish_aga_settings[dropdown_font_size]" value="<?php echo esc_attr( $options['dropdown_font_size'] ?? '14' ); ?>" min="10" max="30" class="small-text aga-appearance-input" />
							</div>
							<div class="aga-field-group">
								<label for="aga_dropdown_max_height"><strong><?php esc_html_e( 'Max Height (px)', 'autocomplete-google-address' ); ?></strong></label>
								<input type="number" id="aga_dropdown_max_height" name="Nish_aga_settings[dropdown_max_height]" value="<?php echo esc_attr( $options['dropdown_max_height'] ?? '250' ); ?>" min="100" max="600" class="small-text aga-appearance-input" />
							</div>
						</div>

						<hr />

						<!-- Attribution Customization -->
						<h3 class="aga-mt-md"><?php esc_html_e( 'Attribution Text', 'autocomplete-google-address' ); ?></h3>
						<p class="description">
							<?php esc_html_e( 'Customize the attribution shown at the bottom of the dropdown. Note: Google requires attribution when using their API.', 'autocomplete-google-address' ); ?>
						</p>

						<div class="aga-appearance-grid">
							<div class="aga-field-group">
								<label for="aga_attribution_text"><strong><?php esc_html_e( 'Attribution Text', 'autocomplete-google-address' ); ?></strong></label>
								<input type="text" id="aga_attribution_text" name="Nish_aga_settings[attribution_text]" value="<?php echo esc_attr( $options['attribution_text'] ?? '' ); ?>" placeholder="Powered by Google" class="regular-text aga-appearance-input" />
								<p class="description"><?php esc_html_e( 'Leave blank for default Google logo. Enter custom text to replace it.', 'autocomplete-google-address' ); ?></p>
							</div>
							<div class="aga-field-group">
								<label for="aga_attribution_text_color"><strong><?php esc_html_e( 'Attribution Text Color', 'autocomplete-google-address' ); ?></strong></label>
								<input type="color" id="aga_attribution_text_color" name="Nish_aga_settings[attribution_text_color]" value="<?php echo esc_attr( $options['attribution_text_color'] ?? '#999999' ); ?>" class="aga-appearance-input" />
							</div>
							<div class="aga-field-group">
								<label for="aga_attribution_bg_color"><strong><?php esc_html_e( 'Attribution Background', 'autocomplete-google-address' ); ?></strong></label>
								<input type="color" id="aga_attribution_bg_color" name="Nish_aga_settings[attribution_bg_color]" value="<?php echo esc_attr( $options['attribution_bg_color'] ?? '#f9f9f9' ); ?>" class="aga-appearance-input" />
							</div>
							<div class="aga-field-group">
								<label for="aga_attribution_font_size"><strong><?php esc_html_e( 'Attribution Font Size (px)', 'autocomplete-google-address' ); ?></strong></label>
								<input type="number" id="aga_attribution_font_size" name="Nish_aga_settings[attribution_font_size]" value="<?php echo esc_attr( $options['attribution_font_size'] ?? '11' ); ?>" min="6" max="48" class="small-text aga-appearance-input" />
							</div>
						</div>

						<hr />

						<!-- Live Preview -->
						<div class="aga-field-group">
							<label><strong><?php esc_html_e( 'Live Preview', 'autocomplete-google-address' ); ?></strong></label>
							<div id="aga-appearance-preview" class="aga-mt-sm">
								<div class="aga-preview-dropdown">
									<div class="aga-preview-item">123 Main Street, New York, NY</div>
									<div class="aga-preview-item">456 Oak Avenue, Los Angeles, CA</div>
									<div class="aga-preview-item">789 Pine Road, Chicago, IL</div>
									<div class="aga-preview-attribution" id="aga-preview-attribution"><?php echo esc_html( $options['attribution_text'] ?? 'Powered by Google' ); ?></div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Tab: Advanced -->
		<div class="aga-tab-panel" data-tab="advanced">
			<div class="aga-card">
				<div class="aga-card-header">
					<h2><?php esc_html_e( 'Advanced Settings', 'autocomplete-google-address' ); ?></h2>
				</div>
				<div class="aga-card-body">
					<div class="aga-field-group">
						<label for="do_not_load_gmaps_api_toggle">
							<strong><?php esc_html_e( 'Conflict Handling', 'autocomplete-google-address' ); ?></strong>
						</label>
						<label class="aga-switch">
							<input type="checkbox" id="do_not_load_gmaps_api_toggle" name="Nish_aga_settings[do_not_load_gmaps_api]" value="1" <?php checked( $options['do_not_load_gmaps_api'] ?? 0, 1 ); ?>>
							<span class="aga-slider"></span>
						</label>
						<label for="do_not_load_gmaps_api_toggle">
							<?php esc_html_e( 'Do not load Google Maps JS API', 'autocomplete-google-address' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Enable this if your theme or another plugin already loads the Google Maps API. This will prevent conflicts.', 'autocomplete-google-address' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>

		<?php submit_button(); ?>
	</form>
</div>

<script>
(function() {
	document.addEventListener('DOMContentLoaded', function() {
		var inputs = document.querySelectorAll('.aga-appearance-input');
		if (!inputs.length) return;

		function updatePreview() {
			var preview = document.getElementById('aga-appearance-preview');
			if (!preview) return;

			var dropdown = preview.querySelector('.aga-preview-dropdown');
			var items = preview.querySelectorAll('.aga-preview-item');

			var bgColor = document.getElementById('aga_dropdown_bg_color');
			var textColor = document.getElementById('aga_dropdown_text_color');
			var hoverColor = document.getElementById('aga_dropdown_hover_color');
			var borderColor = document.getElementById('aga_dropdown_border_color');
			var borderRadius = document.getElementById('aga_dropdown_border_radius');
			var fontSize = document.getElementById('aga_dropdown_font_size');
			var maxHeight = document.getElementById('aga_dropdown_max_height');

			if (dropdown) {
				dropdown.style.backgroundColor = bgColor ? bgColor.value : '#ffffff';
				dropdown.style.borderColor = borderColor ? borderColor.value : '#dddddd';
				dropdown.style.borderRadius = (borderRadius ? borderRadius.value : '4') + 'px';
				dropdown.style.maxHeight = (maxHeight ? maxHeight.value : '250') + 'px';
			}

			items.forEach(function(item) {
				item.style.color = textColor ? textColor.value : '#333333';
				item.style.fontSize = (fontSize ? fontSize.value : '14') + 'px';

				item.onmouseenter = function() {
					item.style.backgroundColor = hoverColor ? hoverColor.value : '#f0f0f0';
				};
				item.onmouseleave = function() {
					item.style.backgroundColor = 'transparent';
				};
			});
		}

		// Attribution preview updates
		var attrEl = document.getElementById('aga-preview-attribution');
		var attrTextInput = document.getElementById('aga_attribution_text');
		var attrTextColor = document.getElementById('aga_attribution_text_color');
		var attrBgColor = document.getElementById('aga_attribution_bg_color');
		var attrFontSize = document.getElementById('aga_attribution_font_size');

		function updateAttribution() {
			if (!attrEl) return;
			if (attrTextInput) attrEl.textContent = attrTextInput.value || 'Powered by Google';
			if (attrTextColor) attrEl.style.color = attrTextColor.value;
			if (attrBgColor) attrEl.style.backgroundColor = attrBgColor.value;
			if (attrFontSize) attrEl.style.fontSize = attrFontSize.value + 'px';
		}

		inputs.forEach(function(input) {
			input.addEventListener('input', function() { updatePreview(); updateAttribution(); });
			input.addEventListener('change', function() { updatePreview(); updateAttribution(); });
		});

		updatePreview();
		updateAttribution();
	});
})();
</script>
