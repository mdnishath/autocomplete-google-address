<?php
/**
 * Handles the plugin's settings page.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 * @author     Md Nishath Khandakar <https://profiles.wordpress.org/nishatbd31/>
 */

defined( 'ABSPATH' ) || exit;

class AGA_Settings {

    /**
     * The single instance of the class.
     *
     * @var AGA_Settings
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main AGA_Settings Instance.
     *
     * Ensures only one instance of AGA_Settings is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @return AGA_Settings - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Register settings, sections, and fields.
     */
    public function register_settings() {
        register_setting(
            'Nish_aga_settings_group', // Option group
            'Nish_aga_settings',       // Option name
            array( $this, 'sanitize' ) // Sanitize callback
        );




    }

    /**
     * Sanitize each setting field as needed.
     *
     * @param array $input Contains all settings fields as array keys
     * @return array
     */
    public function sanitize( $input ) {
        $options = get_option( 'Nish_aga_settings', array() );
        if ( ! is_array( $options ) ) {
            $options = array();
        }

        // Start with existing options to preserve settings from other tabs.
        $new_input = $options;

        // API Key — only update if a new non-empty key is provided.
        if ( isset( $input['api_key'] ) ) {
            $new_api_key = sanitize_text_field( $input['api_key'] );
            if ( ! empty( $new_api_key ) ) {
                $new_input['api_key'] = $new_api_key;
            }
            // If empty, keep existing key (already in $new_input from $options).
        }

        // Toggle/checkbox fields — when unchecked, browsers don't send any value.
        // We use a hidden _aga_tab field to know which tab submitted, so we only
        // reset checkboxes for the active tab (not clobber other tabs).
        $active_tab = isset( $input['_aga_tab'] ) ? sanitize_text_field( $input['_aga_tab'] ) : '';
        unset( $new_input['_aga_tab'] ); // Don't persist the tab tracker.

        // Advanced tab toggles.
        if ( 'advanced' === $active_tab || empty( $active_tab ) ) {
            $new_input['do_not_load_gmaps_api'] = ! empty( $input['do_not_load_gmaps_api'] ) ? 1 : 0;
        }

        // WooCommerce tab toggles.
        if ( 'woocommerce' === $active_tab || empty( $active_tab ) ) {
            $new_input['woocommerce_enabled'] = ! empty( $input['woocommerce_enabled'] ) ? 1 : 0;
        }

        // Appearance settings.
        $appearance_keys = array(
            'dropdown_bg_color', 'dropdown_text_color', 'dropdown_hover_color',
            'dropdown_border_color', 'dropdown_border_radius', 'dropdown_font_size',
            'dropdown_max_height',
            'attribution_text', 'attribution_text_color', 'attribution_bg_color',
            'attribution_font_size',
        );
        foreach ( $appearance_keys as $key ) {
            if ( isset( $input[ $key ] ) ) {
                $new_input[ $key ] = sanitize_text_field( $input[ $key ] );
            }
        }

        return $new_input;
    }



}
