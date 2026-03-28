<?php
/**
 * Admin area view for the form edit page.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/admin/views
 */

defined( 'ABSPATH' ) || exit;

global $post;

$is_paying = function_exists( 'google_autocomplete' ) && google_autocomplete()->is_paying();
$checkout_url = function_exists( 'google_autocomplete' ) ? google_autocomplete()->checkout_url() : '#';

// Get saved values
$mode              = get_post_meta( $post->ID, 'Nish_aga_mode', true ) ?: 'single_line';
$activate_globally = metadata_exists( 'post', $post->ID, 'Nish_aga_activate_globally' ) ? get_post_meta( $post->ID, 'Nish_aga_activate_globally', true ) : '1';
$main_selector     = get_post_meta( $post->ID, 'Nish_aga_main_selector', true );
$country_restriction = get_post_meta( $post->ID, 'Nish_aga_country_restriction', true );
$place_types         = get_post_meta( $post->ID, 'Nish_aga_place_types', true );
$language_override = get_post_meta( $post->ID, 'Nish_aga_language_override', true );
$form_preset       = get_post_meta( $post->ID, 'Nish_aga_form_preset', true );
$address_validation = get_post_meta( $post->ID, 'Nish_aga_address_validation', true );
$geolocation       = get_post_meta( $post->ID, 'Nish_aga_geolocation', true );
$saved_addresses   = get_post_meta( $post->ID, 'Nish_aga_saved_addresses', true );
$map_picker        = get_post_meta( $post->ID, 'Nish_aga_map_picker', true );

// Single Line
$lat_selector      = get_post_meta( $post->ID, 'Nish_aga_lat_selector', true );
$lng_selector      = get_post_meta( $post->ID, 'Nish_aga_lng_selector', true );
$place_id_selector = get_post_meta( $post->ID, 'Nish_aga_place_id_selector', true );

// Smart Mapping
$street_selector         = get_post_meta( $post->ID, 'Nish_aga_street_selector', true );
$city_selector           = get_post_meta( $post->ID, 'Nish_aga_city_selector', true );
$state_selector          = get_post_meta( $post->ID, 'Nish_aga_state_selector', true );
$zip_selector            = get_post_meta( $post->ID, 'Nish_aga_zip_selector', true );
$country_selector        = get_post_meta( $post->ID, 'Nish_aga_country_selector', true );
$map_lat_selector        = get_post_meta( $post->ID, 'Nish_aga_map_lat_selector', true );
$map_lng_selector        = get_post_meta( $post->ID, 'Nish_aga_map_lng_selector', true );
$smart_place_id_selector = get_post_meta( $post->ID, 'Nish_aga_smart_place_id_selector', true );
$state_format            = get_post_meta( $post->ID, 'Nish_aga_state_format', true ) ?: 'long';
$country_format          = get_post_meta( $post->ID, 'Nish_aga_country_format', true ) ?: 'long';

// Pages
$all_pages      = get_pages();
$selected_pages = get_post_meta( $post->ID, 'Nish_aga_load_on_pages', true );
if ( ! is_array( $selected_pages ) ) {
	$selected_pages = array();
}

// Presets
$presets = class_exists( 'AGA_Presets' ) ? AGA_Presets::get_presets() : array();
$preset_options = class_exists( 'AGA_Presets' ) ? AGA_Presets::get_preset_options() : array( '' => 'None (Manual)' );

// Helper to render pro badge
function aga_pro_label( $checkout_url, $is_paying ) {
	if ( ! $is_paying ) {
		return sprintf( ' <a href="%s" target="_blank"><span class="aga-pro-badge">Pro</span></a>', esc_url( $checkout_url ) );
	}
	return '';
}
?>
<?php if ( ! empty( $presets ) ) : ?>
<script type="text/javascript">window.aga_presets = <?php echo wp_json_encode( $presets ); ?>;</script>
<?php endif; ?>

<div class="aga-form-config-wrapper">
	<?php if ( $is_paying && ! empty( $preset_options ) ) : ?>
	<div class="aga-preset-selector">
		<label for="aga_form_preset"><?php esc_html_e( 'Quick Setup: Form Plugin Preset', 'autocomplete-google-address' ); ?></label>
		<div class="aga-flex-row">
			<select id="aga_form_preset" name="Nish_aga_form_preset" class="aga-flex-1">
				<?php foreach ( $preset_options as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $form_preset, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="button" id="aga-apply-preset-btn" class="button button-secondary"><?php esc_html_e( 'Apply Preset', 'autocomplete-google-address' ); ?></button>
		</div>
		<p class="description"><?php esc_html_e( 'Select a form plugin and click "Apply Preset" to auto-fill selectors. You can customize them after.', 'autocomplete-google-address' ); ?></p>
	</div>
	<?php elseif ( ! $is_paying ) : ?>
	<div class="aga-preset-selector">
		<label><?php esc_html_e( 'Quick Setup: Form Plugin Preset', 'autocomplete-google-address' ); ?> <?php echo aga_pro_label( $checkout_url, $is_paying ); ?></label>
		<select disabled>
			<option><?php esc_html_e( 'None (Manual)', 'autocomplete-google-address' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'Upgrade to Pro for one-click presets for CF7, WPForms, Gravity Forms, and Elementor.', 'autocomplete-google-address' ); ?></p>
	</div>
	<?php endif; ?>

	<div class="aga-config-columns">
		<!-- Left Column -->
		<div class="aga-config-col">
			<div class="aga-card">
				<div class="aga-card-header">
					<span class="dashicons dashicons-admin-settings"></span>
					<h2><?php esc_html_e( 'Core Setup', 'autocomplete-google-address' ); ?></h2>
				</div>
				<div class="aga-card-body">
					<div class="aga-field-group">
						<label for="aga_main_selector"><?php esc_html_e( 'Trigger Field Selector', 'autocomplete-google-address' ); ?>
							<span class="aga-tooltip"><span class="dashicons dashicons-editor-help"></span>
								<span class="aga-tooltip-text"><?php esc_html_e( 'The CSS selector of the input field where users type their address. Right-click the field and choose "Inspect" to find it.', 'autocomplete-google-address' ); ?></span>
							</span>
						</label>
						<input type="text" id="aga_main_selector" name="Nish_aga_main_selector" value="<?php echo esc_attr( $main_selector ); ?>" class="widefat" placeholder="#id, .class, [name='field_name']" />
						<p class="description">
							<code>#billing_address</code> <code>.address-field</code> <code>[name="address"]</code>
						</p>
					</div>
					</div>
			</div>
			<div class="aga-card">
				<div class="aga-card-header">
					<span class="dashicons dashicons-filter"></span>
					<h2><?php esc_html_e( 'Filters & Locale', 'autocomplete-google-address' ); ?></h2>
				</div>
				<div class="aga-card-body">
					<div class="aga-mapping-grid">
						<div class="aga-field-group">
							<label><?php esc_html_e( 'Country Restriction', 'autocomplete-google-address' ); ?><?php echo aga_pro_label( $checkout_url, $is_paying ); ?></label>
							<?php
							$countries = aga_get_countries();
							$selected_countries = array_filter( array_map( 'trim', explode( ',', $country_restriction ) ) );
							?>
							<select id="aga_country_restriction" name="Nish_aga_country_restriction[]" class="widefat aga-select2-countries" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select countries (max 5)...', 'autocomplete-google-address' ); ?>" <?php echo ! $is_paying ? 'disabled' : ''; ?>>
								<?php foreach ( $countries as $code => $name ) : ?>
									<option value="<?php echo esc_attr( $code ); ?>" <?php echo in_array( $code, $selected_countries, true ) ? 'selected' : ''; ?>><?php echo esc_html( $name . ' (' . $code . ')' ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="aga-field-group">
							<label><?php esc_html_e( 'Place Types', 'autocomplete-google-address' ); ?><?php echo aga_pro_label( $checkout_url, $is_paying ); ?></label>
							<select name="Nish_aga_place_types" class="widefat" <?php echo ! $is_paying ? 'disabled' : ''; ?>>
								<option value="" <?php selected( $place_types, '' ); ?>><?php esc_html_e( 'All Types', 'autocomplete-google-address' ); ?></option>
								<option value="address" <?php selected( $place_types, 'address' ); ?>><?php esc_html_e( 'Addresses only', 'autocomplete-google-address' ); ?></option>
								<option value="geocode" <?php selected( $place_types, 'geocode' ); ?>><?php esc_html_e( 'Geocoded', 'autocomplete-google-address' ); ?></option>
								<option value="establishment" <?php selected( $place_types, 'establishment' ); ?>><?php esc_html_e( 'Businesses', 'autocomplete-google-address' ); ?></option>
								<option value="(regions)" <?php selected( $place_types, '(regions)' ); ?>><?php esc_html_e( 'Regions', 'autocomplete-google-address' ); ?></option>
								<option value="(cities)" <?php selected( $place_types, '(cities)' ); ?>><?php esc_html_e( 'Cities', 'autocomplete-google-address' ); ?></option>
							</select>
						</div>
					</div>
					<hr/>
					<div class="aga-mapping-grid">
						<div class="aga-field-group">
							<label><?php esc_html_e( 'Language', 'autocomplete-google-address' ); ?><?php echo aga_pro_label( $checkout_url, $is_paying ); ?></label>
							<?php $languages = aga_get_languages(); ?>
							<select name="Nish_aga_language_override" class="widefat aga-select2-language" data-placeholder="<?php esc_attr_e( 'Select language...', 'autocomplete-google-address' ); ?>" <?php echo ! $is_paying ? 'disabled' : ''; ?>>
								<option value=""><?php esc_html_e( 'Default (site language)', 'autocomplete-google-address' ); ?></option>
								<?php foreach ( $languages as $code => $name ) : ?>
									<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $language_override, $code ); ?>><?php echo esc_html( $name . ' (' . $code . ')' ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="aga-card">
				<div class="aga-card-header">
					<span class="dashicons dashicons-admin-site-alt3"></span>
					<h2><?php esc_html_e( 'Activation', 'autocomplete-google-address' ); ?></h2>
				</div>
				<div class="aga-card-body">
					<div class="aga-field-group">
						<label for="Nish_aga_activate_globally"><?php esc_html_e( 'Global Activation', 'autocomplete-google-address' ); ?></label>
						<label class="aga-switch">
							<input type="checkbox" id="Nish_aga_activate_globally" name="Nish_aga_activate_globally" value="1" <?php checked( $activate_globally, '1' ); ?>>
							<span class="aga-slider"></span>
						</label>
						<span class="aga-switch-label"><?php esc_html_e( 'Activate on all pages', 'autocomplete-google-address' ); ?></span>
					</div>
					<hr/>
					<div class="aga-field-group">
						<label for="Nish_aga_load_on_pages"><?php esc_html_e( 'Or Specific Pages', 'autocomplete-google-address' ); ?><?php echo aga_pro_label( $checkout_url, $is_paying ); ?></label>
						<select id="Nish_aga_load_on_pages" name="Nish_aga_load_on_pages[]" multiple="multiple" class="widefat select2" data-placeholder="<?php esc_attr_e( 'Search pages...', 'autocomplete-google-address' ); ?>" <?php echo ! $is_paying ? 'disabled' : ''; ?>>
							<?php foreach ( $all_pages as $page ) : ?>
								<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( in_array( $page->ID, $selected_pages, true ) ); ?>>
									<?php echo esc_html( $page->post_title ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
			<div class="aga-card">
				<div class="aga-card-header">
					<span class="dashicons dashicons-visibility"></span>
					<h2><?php esc_html_e( 'Live Preview', 'autocomplete-google-address' ); ?></h2>
				</div>
				<div class="aga-card-body">
					<p class="description"><?php esc_html_e( 'Test your autocomplete setup:', 'autocomplete-google-address' ); ?></p>
					<div id="aga-live-preview-container">
						<input type="text" id="aga-live-preview-input" class="widefat" placeholder="<?php esc_attr_e( 'Start typing an address...', 'autocomplete-google-address' ); ?>" autocomplete="off" />
					</div>
						<div id="aga-preview-result" class="aga-mt-sm" style="display: none;">
						<table class="widefat striped" id="aga-preview-result-table">
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<!-- Right Column -->
		<div class="aga-config-col">
			<div class="aga-card">
				<div class="aga-card-header">
					<span class="dashicons dashicons-forms"></span>
					<h2><?php esc_html_e( 'Mapping Mode', 'autocomplete-google-address' ); ?></h2>
				</div>
				<div class="aga-card-body">
					<div class="aga-field-group">
						<div class="aga-mode-selector">
							<input type="radio" id="mode_single_line" name="Nish_aga_mode" value="single_line" <?php checked( $mode, 'single_line' ); ?>>
							<label for="mode_single_line"><?php esc_html_e( 'Single Line', 'autocomplete-google-address' ); ?></label>
							<input type="radio" id="mode_smart_mapping" name="Nish_aga_mode" value="smart_mapping" <?php checked( $mode, 'smart_mapping' ); ?>>
							<label for="mode_smart_mapping"><?php esc_html_e( 'Smart Mapping', 'autocomplete-google-address' ); ?><?php if ( ! $is_paying ) echo ' <small>(Pro)</small>'; ?></label>
						</div>
					</div>
					<!-- Single Line Panel -->
					<div id="aga-panel-single_line" class="aga-mode-panel <?php echo 'single_line' === $mode ? 'active' : ''; ?>">
						<p><?php esc_html_e( 'The full address goes into the trigger field. Optionally capture extra data.', 'autocomplete-google-address' ); ?></p>
						<div class="aga-field-group">
							<label for="aga_lat_selector"><?php esc_html_e( 'Latitude Selector', 'autocomplete-google-address' ); ?></label>
							<input type="text" id="aga_lat_selector" name="Nish_aga_lat_selector" value="<?php echo esc_attr( $lat_selector ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'Optional - e.g., #lat_field', 'autocomplete-google-address' ); ?>" />
						</div>
						<div class="aga-field-group">
							<label for="aga_lng_selector"><?php esc_html_e( 'Longitude Selector', 'autocomplete-google-address' ); ?></label>
							<input type="text" id="aga_lng_selector" name="Nish_aga_lng_selector" value="<?php echo esc_attr( $lng_selector ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'Optional - e.g., #lng_field', 'autocomplete-google-address' ); ?>" />
						</div>
						<div class="aga-field-group">
							<label for="aga_place_id_selector"><?php esc_html_e( 'Place ID Selector', 'autocomplete-google-address' ); ?></label>
							<input type="text" id="aga_place_id_selector" name="Nish_aga_place_id_selector" value="<?php echo esc_attr( $place_id_selector ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'Optional - e.g., #place_id_field', 'autocomplete-google-address' ); ?>" />
						</div>
					</div>
					<!-- Smart Mapping Panel -->
					<div id="aga-panel-smart_mapping" class="aga-mode-panel <?php echo 'smart_mapping' === $mode ? 'active' : ''; ?>">
						<?php if ( ! $is_paying ) : ?>
						<div class="aga-pro-banner">
							<h3><?php esc_html_e( 'Unlock Smart Mapping', 'autocomplete-google-address' ); ?></h3>
							<p><?php esc_html_e( 'Split addresses into Street, City, State, Zip, and Country fields automatically.', 'autocomplete-google-address' ); ?></p>
							<a href="<?php echo esc_url( $checkout_url ); ?>" target="_blank" class="button"><?php esc_html_e( 'Upgrade to Pro', 'autocomplete-google-address' ); ?></a>
						</div>
						<?php else : ?>
						<p><?php esc_html_e( 'Map each address component to your form fields.', 'autocomplete-google-address' ); ?></p>
						<div class="aga-mapping-grid">
							<?php
							$smart_fields = array(
								'street'   => array( 'label' => __( 'Street', 'autocomplete-google-address' ), 'id' => 'aga_street_selector', 'name' => 'Nish_aga_street_selector', 'value' => $street_selector ),
								'city'     => array( 'label' => __( 'City', 'autocomplete-google-address' ), 'id' => 'aga_city_selector', 'name' => 'Nish_aga_city_selector', 'value' => $city_selector ),
								'state'    => array( 'label' => __( 'State/Region', 'autocomplete-google-address' ), 'id' => 'aga_state_selector', 'name' => 'Nish_aga_state_selector', 'value' => $state_selector ),
								'zip'      => array( 'label' => __( 'Zip/Postal Code', 'autocomplete-google-address' ), 'id' => 'aga_zip_selector', 'name' => 'Nish_aga_zip_selector', 'value' => $zip_selector ),
								'country'  => array( 'label' => __( 'Country', 'autocomplete-google-address' ), 'id' => 'aga_country_selector', 'name' => 'Nish_aga_country_selector', 'value' => $country_selector ),
								'place_id' => array( 'label' => __( 'Place ID', 'autocomplete-google-address' ), 'id' => 'aga_smart_place_id_selector', 'name' => 'Nish_aga_smart_place_id_selector', 'value' => $smart_place_id_selector ),
								'lat'      => array( 'label' => __( 'Latitude', 'autocomplete-google-address' ), 'id' => 'aga_map_lat_selector', 'name' => 'Nish_aga_map_lat_selector', 'value' => $map_lat_selector ),
								'lng'      => array( 'label' => __( 'Longitude', 'autocomplete-google-address' ), 'id' => 'aga_map_lng_selector', 'name' => 'Nish_aga_map_lng_selector', 'value' => $map_lng_selector ),
							);
							foreach ( $smart_fields as $field ) : ?>
							<div class="aga-field-group">
								<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
								<input type="text" id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" class="widefat" placeholder="#id, .class, [name='field']" />
							</div>
							<?php endforeach; ?>
						</div>
						<hr/>
						<h4><?php esc_html_e( 'Formatting Options', 'autocomplete-google-address' ); ?></h4>
						<div class="aga-mapping-grid">
							<div class="aga-field-group">
								<label for="aga_state_format"><?php esc_html_e( 'State Format', 'autocomplete-google-address' ); ?></label>
								<select id="aga_state_format" name="Nish_aga_state_format">
									<option value="long" <?php selected( $state_format, 'long' ); ?>><?php esc_html_e( 'Long Name (e.g., California)', 'autocomplete-google-address' ); ?></option>
									<option value="short" <?php selected( $state_format, 'short' ); ?>><?php esc_html_e( 'Short Name (e.g., CA)', 'autocomplete-google-address' ); ?></option>
								</select>
							</div>
							<div class="aga-field-group">
								<label for="aga_country_format"><?php esc_html_e( 'Country Format', 'autocomplete-google-address' ); ?></label>
								<select id="aga_country_format" name="Nish_aga_country_format">
									<option value="long" <?php selected( $country_format, 'long' ); ?>><?php esc_html_e( 'Long Name (e.g., United States)', 'autocomplete-google-address' ); ?></option>
									<option value="short" <?php selected( $country_format, 'short' ); ?>><?php esc_html_e( 'Short Name (e.g., US)', 'autocomplete-google-address' ); ?></option>
								</select>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="aga-card">
				<div class="aga-card-header">
					<span class="dashicons dashicons-shield"></span>
					<h2><?php esc_html_e( 'Pro Features', 'autocomplete-google-address' ); ?></h2>
				</div>
				<div class="aga-card-body">
					<div class="aga-toggle-grid aga-toggle-grid--vertical">
						<div class="aga-toggle-row">
							<div class="aga-toggle-row-info">
								<strong><?php esc_html_e( 'Address Validation', 'autocomplete-google-address' ); ?></strong>
								<span class="aga-toggle-desc"><?php esc_html_e( 'Verify with badge', 'autocomplete-google-address' ); ?></span>
							</div>
							<label class="aga-switch">
								<input type="checkbox" name="Nish_aga_address_validation" value="1" <?php checked( $address_validation, '1' ); ?> <?php echo ! $is_paying ? 'disabled' : ''; ?>>
								<span class="aga-slider"></span>
							</label>
						</div>
						<div class="aga-toggle-row">
							<div class="aga-toggle-row-info">
								<strong><?php esc_html_e( 'Geolocation', 'autocomplete-google-address' ); ?></strong>
								<span class="aga-toggle-desc"><?php esc_html_e( 'GPS auto-fill button', 'autocomplete-google-address' ); ?></span>
							</div>
							<label class="aga-switch">
								<input type="checkbox" name="Nish_aga_geolocation" value="1" <?php checked( $geolocation, '1' ); ?> <?php echo ! $is_paying ? 'disabled' : ''; ?>>
								<span class="aga-slider"></span>
							</label>
						</div>
						<div class="aga-toggle-row">
							<div class="aga-toggle-row-info">
								<strong><?php esc_html_e( 'Saved Addresses', 'autocomplete-google-address' ); ?></strong>
								<span class="aga-toggle-desc"><?php esc_html_e( 'Recent addresses for users', 'autocomplete-google-address' ); ?></span>
							</div>
							<label class="aga-switch">
								<input type="checkbox" name="Nish_aga_saved_addresses" value="1" <?php checked( $saved_addresses, '1' ); ?> <?php echo ! $is_paying ? 'disabled' : ''; ?>>
								<span class="aga-slider"></span>
							</label>
						</div>
						<div class="aga-toggle-row">
							<div class="aga-toggle-row-info">
								<strong><?php esc_html_e( 'Map Picker', 'autocomplete-google-address' ); ?></strong>
								<span class="aga-toggle-desc"><?php esc_html_e( 'Pick address from map by clicking or dragging', 'autocomplete-google-address' ); ?></span>
							</div>
							<label class="aga-switch">
								<input type="checkbox" name="Nish_aga_map_picker" value="1" <?php checked( $map_picker, '1' ); ?> <?php echo ! $is_paying ? 'disabled' : ''; ?>>
								<span class="aga-slider"></span>
							</label>
						</div>
					</div>
					<?php if ( ! $is_paying ) : ?>
					<div class="aga-mt-sm">
						<a href="<?php echo esc_url( $checkout_url ); ?>" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'autocomplete-google-address' ); ?></a>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

