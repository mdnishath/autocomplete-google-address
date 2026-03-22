<?php
/**
 * Elementor Pro Forms - Address Autocomplete field type.
 *
 * When "Address Autocomplete" is selected as a field type in an Elementor Pro
 * Form, the field renders a text input with Google Places autocomplete.
 *
 * In Single Line mode: just the autocomplete input.
 * In Smart Mapping mode: the autocomplete input PLUS hidden/visible sub-fields
 * for street, city, state, zip, country that auto-fill on selection.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes/widgets
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\ElementorPro\Modules\Forms\Fields\Field_Base' ) ) {
	return;
}

class AGA_Elementor_Form_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base {

	public function get_type() {
		return 'aga_address';
	}

	public function get_name() {
		return esc_html__( 'Address Autocomplete', 'autocomplete-google-address' );
	}

	public function update_controls( $widget ) {
		$elementor    = \Elementor\Plugin::instance();
		$control_data = $elementor->controls_manager->get_control_from_stack(
			$widget->get_unique_name(),
			'form_fields'
		);

		if ( is_wp_error( $control_data ) ) {
			return;
		}

		$is_paying = function_exists( 'google_autocomplete' ) && google_autocomplete()->is_paying();
		$condition = array( 'field_type' => $this->get_type() );
		$tab_args  = array(
			'tab'          => 'content',
			'inner_tab'    => 'form_fields_content_tab',
			'tabs_wrapper' => 'form_fields_tabs',
		);

		$field_controls = array(
			'aga_mode' => array_merge( $tab_args, array(
				'name'      => 'aga_mode',
				'label'     => esc_html__( 'Mode', 'autocomplete-google-address' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'single_line',
				'options'   => array(
					'single_line'   => esc_html__( 'Single Line', 'autocomplete-google-address' ),
					'smart_mapping' => $is_paying
						? esc_html__( 'Smart Mapping', 'autocomplete-google-address' )
						: esc_html__( 'Smart Mapping (Pro)', 'autocomplete-google-address' ),
				),
				'condition' => $condition,
			) ),
			'aga_smart_fields' => array_merge( $tab_args, array(
				'name'      => 'aga_smart_fields',
				'label'     => esc_html__( 'Auto-fill Fields', 'autocomplete-google-address' ),
				'type'      => \Elementor\Controls_Manager::SELECT2,
				'multiple'  => true,
				'default'   => array( 'city', 'state', 'zip', 'country' ),
				'options'   => array(
					'street'  => esc_html__( 'Street', 'autocomplete-google-address' ),
					'city'    => esc_html__( 'City', 'autocomplete-google-address' ),
					'state'   => esc_html__( 'State / Region', 'autocomplete-google-address' ),
					'zip'     => esc_html__( 'Zip / Postal Code', 'autocomplete-google-address' ),
					'country' => esc_html__( 'Country', 'autocomplete-google-address' ),
				),
				'description' => esc_html__( 'These fields will be auto-created below the address input and filled automatically.', 'autocomplete-google-address' ),
				'condition' => array(
					'field_type' => $this->get_type(),
					'aga_mode'   => 'smart_mapping',
				),
			) ),
			'aga_sub_layout' => array_merge( $tab_args, array(
				'name'      => 'aga_sub_layout',
				'label'     => esc_html__( 'Sub-fields Layout', 'autocomplete-google-address' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'half',
				'options'   => array(
					'full'  => esc_html__( 'Full Width (1 column)', 'autocomplete-google-address' ),
					'half'  => esc_html__( 'Half Width (2 columns)', 'autocomplete-google-address' ),
					'third' => esc_html__( 'Third Width (3 columns)', 'autocomplete-google-address' ),
				),
				'condition' => array(
					'field_type' => $this->get_type(),
					'aga_mode'   => 'smart_mapping',
				),
			) ),
			'aga_country_restriction' => array_merge( $tab_args, array(
				'name'        => 'aga_country_restriction',
				'label'       => esc_html__( 'Country Restriction', 'autocomplete-google-address' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => function_exists( 'aga_get_countries' ) ? aga_get_countries() : array(),
				'default'     => array(),
				'description' => esc_html__( 'Limit results to specific countries (max 5).', 'autocomplete-google-address' ),
				'condition'   => $condition,
			) ),
			'aga_place_types' => array_merge( $tab_args, array(
				'name'      => 'aga_place_types',
				'label'     => esc_html__( 'Place Types', 'autocomplete-google-address' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''              => esc_html__( 'All Types', 'autocomplete-google-address' ),
					'address'       => esc_html__( 'Addresses', 'autocomplete-google-address' ),
					'geocode'       => esc_html__( 'Geocoded', 'autocomplete-google-address' ),
					'establishment' => esc_html__( 'Businesses', 'autocomplete-google-address' ),
					'(regions)'     => esc_html__( 'Regions', 'autocomplete-google-address' ),
					'(cities)'      => esc_html__( 'Cities', 'autocomplete-google-address' ),
				),
				'condition' => $condition,
			) ),
			'aga_show_map' => array_merge( $tab_args, array(
				'name'      => 'aga_show_map',
				'label'     => esc_html__( 'Show Map Preview', 'autocomplete-google-address' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => '',
				'condition' => $condition,
			) ),
		);

		$control_data['fields'] = $this->inject_field_controls( $control_data['fields'], $field_controls );
		$widget->update_control( 'form_fields', $control_data );
	}

	public function render( $item, $item_index, $form ) {
		$form_id   = $form->get_id();
		$field_id  = $item['custom_id'];
		$is_paying = function_exists( 'google_autocomplete' ) && google_autocomplete()->is_paying();

		$mode = ! empty( $item['aga_mode'] ) ? $item['aga_mode'] : 'single_line';
		if ( 'smart_mapping' === $mode && ! $is_paying ) {
			$mode = 'single_line';
		}

		$input_size = ! empty( $item['input_size'] ) ? $item['input_size'] : 'sm';

		// Pre-build smart mapping selectors so we can include them in the config
		$smart_fields_config = array();
		$selected_fields = array();
		$sub_layout = 'half';

		if ( 'smart_mapping' === $mode ) {
			$selected_fields = ! empty( $item['aga_smart_fields'] ) && is_array( $item['aga_smart_fields'] ) ? $item['aga_smart_fields'] : array( 'city', 'state', 'zip', 'country' );
			$sub_layout = ! empty( $item['aga_sub_layout'] ) ? $item['aga_sub_layout'] : 'half';

			foreach ( $selected_fields as $sf ) {
				$smart_fields_config[ $sf ] = '#form-field-' . $field_id . '_' . $sf;
			}
		}

		// Build JS config
		$config = array(
			'form_id'                => 'ef_' . $form_id . '_' . $field_id,
			'mode'                   => $mode,
			'main_selector'          => '#form-field-' . $field_id,
			'language'               => '',
			'selectors'              => $smart_fields_config,
			'formats'                => array( 'state' => 'long', 'country' => 'long' ),
			'component_restrictions' => array(),
			'place_types'            => '',
			'show_map_preview'       => false,
			'map_container_selector' => '',
			'geolocation'            => false,
			'address_validation'     => false,
			'saved_addresses'        => false,
		);

		if ( ! empty( $item['aga_country_restriction'] ) && is_array( $item['aga_country_restriction'] ) ) {
			$countries = array_slice( $item['aga_country_restriction'], 0, 5 );
			$config['component_restrictions']['country'] = count( $countries ) === 1 ? $countries[0] : $countries;
		}
		if ( $is_paying && ! empty( $item['aga_place_types'] ) ) {
			$config['place_types'] = $item['aga_place_types'];
		}
		if ( $is_paying && ! empty( $item['aga_show_map'] ) && 'yes' === $item['aga_show_map'] ) {
			$config['show_map_preview'] = true;
		}

		// Store config as a data attribute on the input — NO script tags.
		// frontend.js scans for data-aga-config on inputs directly.
		$form->add_render_attribute( 'input' . $item_index, 'class', 'elementor-field-textual' );
		$form->add_render_attribute( 'input' . $item_index, 'autocomplete', 'off' );
		$form->add_render_attribute( 'input' . $item_index, 'data-aga-config', wp_json_encode( $config ) );

		echo '<input size="1" ' . $form->get_render_attribute_string( 'input' . $item_index ) . '>';

		// Smart mapping: render sub-fields
		if ( 'smart_mapping' === $mode && ! empty( $selected_fields ) ) {
			$field_labels = array(
				'street'  => esc_html__( 'Street', 'autocomplete-google-address' ),
				'city'    => esc_html__( 'City', 'autocomplete-google-address' ),
				'state'   => esc_html__( 'State / Region', 'autocomplete-google-address' ),
				'zip'     => esc_html__( 'Zip / Postal Code', 'autocomplete-google-address' ),
				'country' => esc_html__( 'Country', 'autocomplete-google-address' ),
			);

			$col_class_map = array( 'full' => 'elementor-col-100', 'half' => 'elementor-col-50', 'third' => 'elementor-col-33' );
			$col_class = isset( $col_class_map[ $sub_layout ] ) ? $col_class_map[ $sub_layout ] : 'elementor-col-50';

			echo '<div class="aga-elementor-smart-fields" style="display:flex;flex-wrap:wrap;width:100%;margin-top:10px;">';
			foreach ( $selected_fields as $sf ) {
				if ( ! isset( $field_labels[ $sf ] ) ) continue;
				$sub_id   = $field_id . '_' . $sf;
				$sub_name = 'form_fields[' . $sub_id . ']';

				echo '<div class="elementor-field-group elementor-column ' . esc_attr( $col_class ) . '" style="padding:0 5px;box-sizing:border-box;margin-bottom:10px;">';
				echo '<label for="form-field-' . esc_attr( $sub_id ) . '" class="elementor-field-label">' . esc_html( $field_labels[ $sf ] ) . '</label>';
				echo '<input type="text" name="' . esc_attr( $sub_name ) . '" id="form-field-' . esc_attr( $sub_id ) . '" class="elementor-field elementor-field-textual elementor-size-' . esc_attr( $input_size ) . '" placeholder="' . esc_attr( $field_labels[ $sf ] ) . '" />';
				echo '</div>';
			}
			echo '</div>';
		}
	}

	public function validation( $field, $record, $ajax_handler ) {
		// No special validation — Elementor handles required check.
	}

	public function process_field( $field, \ElementorPro\Modules\Forms\Classes\Form_Record $record, \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler ) {
		// No special processing — address is plain text.
	}

	public function inject_field_controls( $fields, $controls ) {
		$new_fields = array();
		foreach ( $fields as $key => $field ) {
			$new_fields[ $key ] = $field;
			if ( 'field_type' === $key ) {
				foreach ( $controls as $ck => $cv ) {
					$new_fields[ $ck ] = $cv;
				}
			}
		}
		return $new_fields;
	}
}
