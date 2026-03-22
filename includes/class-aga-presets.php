<?php
defined( 'ABSPATH' ) || exit;

/**
 * Form plugin preset definitions.
 *
 * Provides ready-made selector templates for popular form plugins:
 * Contact Form 7, WPForms, Gravity Forms, and Elementor Pro Forms.
 */
class AGA_Presets {

	/**
	 * Get all preset definitions.
	 *
	 * @return array
	 */
	public static function get_presets() {
		return array(
			'cf7'       => array(
				'label'       => 'Contact Form 7',
				'description' => 'Uses [name="field-name"] selectors. Replace field-name with your CF7 field names.',
				'selectors'   => array(
					'main_selector' => '[name="your-address"]',
					'street'        => '[name="your-street"]',
					'city'          => '[name="your-city"]',
					'state'         => '[name="your-state"]',
					'zip'           => '[name="your-zip"]',
					'country'       => '[name="your-country"]',
				),
			),
			'wpforms'   => array(
				'label'       => 'WPForms',
				'description' => 'Uses #wpforms-FORMID-field_FIELDID pattern. Replace FORMID and FIELDID with your form and field IDs.',
				'selectors'   => array(
					'main_selector' => '#wpforms-FORMID-field_FIELDID',
					'street'        => '#wpforms-FORMID-field_FIELDID-address1',
					'city'          => '#wpforms-FORMID-field_FIELDID-city',
					'state'         => '#wpforms-FORMID-field_FIELDID-state',
					'zip'           => '#wpforms-FORMID-field_FIELDID-postal',
					'country'       => '#wpforms-FORMID-field_FIELDID-country',
				),
			),
			'gravity'   => array(
				'label'       => 'Gravity Forms',
				'description' => 'Uses #input_FORMID_FIELDID pattern. Replace FORMID and FIELDID with your Gravity Forms IDs.',
				'selectors'   => array(
					'main_selector' => '#input_FORMID_FIELDID',
					'street'        => '#input_FORMID_FIELDID_1',
					'city'          => '#input_FORMID_FIELDID_3',
					'state'         => '#input_FORMID_FIELDID_4',
					'zip'           => '#input_FORMID_FIELDID_5',
					'country'       => '#input_FORMID_FIELDID_6',
				),
			),
			'elementor' => array(
				'label'       => 'Elementor Pro Forms',
				'description' => 'Uses #form-field-FIELDID pattern. Replace FIELDID with your Elementor field IDs.',
				'selectors'   => array(
					'main_selector' => '#form-field-address',
					'street'        => '#form-field-street',
					'city'          => '#form-field-city',
					'state'         => '#form-field-state',
					'zip'           => '#form-field-zip',
					'country'       => '#form-field-country',
				),
			),
		);
	}

	/**
	 * Get a single preset by key.
	 *
	 * @param string $key Preset key (e.g. 'cf7', 'wpforms').
	 * @return array|null Preset definition or null if not found.
	 */
	public static function get_preset( $key ) {
		$presets = self::get_presets();

		return isset( $presets[ $key ] ) ? $presets[ $key ] : null;
	}

	/**
	 * Get preset options for a dropdown select.
	 *
	 * @return array Associative array of key => label pairs.
	 */
	public static function get_preset_options() {
		$options = array(
			'' => 'None (Manual)',
		);

		foreach ( self::get_presets() as $key => $preset ) {
			$options[ $key ] = $preset['label'];
		}

		return $options;
	}
}
