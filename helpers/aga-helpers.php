<?php
/**
 * Helper functions for the Autocomplete Google Address plugin.
 *
 * @link       https://profiles.wordpress.org/nishatbd31/
 * @since      1.0.0
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/helpers
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get a specific setting from the main settings array.
 *
 * @since 1.0.0
 * @param string $setting_name The name of the setting to retrieve.
 * @param mixed  $default      The default value to return if the setting is not found.
 * @return mixed The value of the setting or the default value.
 */
function aga_get_setting( $setting_name, $default = '' ) {
    $options = get_option( 'Nish_aga_settings', array() );
    return isset( $options[ $setting_name ] ) ? $options[ $setting_name ] : $default;
}

/**
 * Sanitize an array of settings.
 *
 * @since 1.0.0
 * @param array $input The input array to sanitize.
 * @return array The sanitized array.
 */
function aga_sanitize_settings( $input ) {
    $new_input = array();
    if ( isset( $input['api_key'] ) ) {
        $new_input['api_key'] = sanitize_text_field( $input['api_key'] );
    }
    if ( isset( $input['default_language'] ) ) {
        $new_input['default_language'] = sanitize_text_field( $input['default_language'] );
    }
    if ( isset( $input['default_country'] ) ) {
        $new_input['default_country'] = sanitize_text_field( $input['default_country'] );
    }
    if ( isset( $input['do_not_load_gmaps_api'] ) ) {
        $new_input['do_not_load_gmaps_api'] = absint( $input['do_not_load_gmaps_api'] );
    }
    return $new_input;
}

/**
 * Renders a form configuration by its ID.
 *
 * This is the programmatic equivalent of the [aga_form] shortcode.
 *
 * @since 1.0.0
 * @param int|string $id The ID of the aga_form post.
 */
function aga_render_form_config( $id ) {
    $id = absint( $id );
    if ( ! $id ) {
        return;
    }

    // Use the frontend class to enqueue scripts and localize data for this specific form ID.
    $frontend = new AGA_Frontend( 'autocomplete-google-address', AGA_VERSION );
    $frontend->prepare_scripts_for_form( $id );
}
