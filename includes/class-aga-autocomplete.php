<?php
/**
 * Handles building the JS configuration object for the frontend.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 * @author     Md Nishath Khandakar <https://profiles.wordpress.org/nishatbd31/>
 */

defined( 'ABSPATH' ) || exit;

class AGA_Autocomplete {

    /**
     * Generates the configuration object for a specific form ID.
     * This object will be localized and used by the frontend JavaScript.
     *
     * @param int $form_id The ID of the aga_form post.
     * @return array The configuration array.
     */
    public static function get_js_config( $form_id ) {
        $form_id = absint( $form_id );
        if ( ! $form_id || 'aga_form' !== get_post_type( $form_id ) ) {
            return array();
        }

        $mode = get_post_meta( $form_id, 'Nish_aga_mode', true );
        $main_selector = get_post_meta( $form_id, 'Nish_aga_main_selector', true );

        $config = array(
            'form_id'       => $form_id,
            'mode'          => $mode,
            'main_selector' => $main_selector,
            'language'      => get_post_meta( $form_id, 'Nish_aga_language_override', true ),
            'selectors'     => array(),
        );

        $is_paying = function_exists( 'google_autocomplete' ) && google_autocomplete()->is_paying();

        if ( 'single_line' === $mode ) {
            $config['selectors']['lat'] = get_post_meta( $form_id, 'Nish_aga_lat_selector', true );
            $config['selectors']['lng'] = get_post_meta( $form_id, 'Nish_aga_lng_selector', true );
            $config['selectors']['place_id'] = get_post_meta( $form_id, 'Nish_aga_place_id_selector', true );
        } elseif ( 'smart_mapping' === $mode && $is_paying ) {
            $config['selectors']['street'] = get_post_meta( $form_id, 'Nish_aga_street_selector', true );
            $config['selectors']['city'] = get_post_meta( $form_id, 'Nish_aga_city_selector', true );
            $config['selectors']['state'] = get_post_meta( $form_id, 'Nish_aga_state_selector', true );
            $config['selectors']['zip'] = get_post_meta( $form_id, 'Nish_aga_zip_selector', true );
            $config['selectors']['country'] = get_post_meta( $form_id, 'Nish_aga_country_selector', true );
            $config['selectors']['lat'] = get_post_meta( $form_id, 'Nish_aga_map_lat_selector', true );
            $config['selectors']['lng'] = get_post_meta( $form_id, 'Nish_aga_map_lng_selector', true );
            $config['selectors']['place_id'] = get_post_meta( $form_id, 'Nish_aga_smart_place_id_selector', true );
            
            $config['formats'] = array(
                'state'   => get_post_meta( $form_id, 'Nish_aga_state_format', true ) ?: 'long',
                'country' => get_post_meta( $form_id, 'Nish_aga_country_format', true ) ?: 'long',
            );
        }

        // Add per-config settings for autocomplete restrictions (Pro feature)
        $config['component_restrictions'] = array();
        if ( $is_paying ) {
            $country_restriction = get_post_meta( $form_id, 'Nish_aga_country_restriction', true );
            if ( ! empty( $country_restriction ) ) {
                $countries = array_filter( array_map( 'trim', explode( ',', $country_restriction ) ) );
                // Google supports single string or array of up to 5
                $config['component_restrictions']['country'] = count( $countries ) === 1 ? $countries[0] : array_values( $countries );
            }
        }

        // Place types filter (Pro feature)
        $config['place_types'] = '';
        if ( $is_paying ) {
            $place_types = get_post_meta( $form_id, 'Nish_aga_place_types', true );
            if ( ! empty( $place_types ) ) {
                $config['place_types'] = $place_types;
            }
        }

        // Map preview (Pro feature)
        $config['show_map_preview'] = false;
        if ( $is_paying ) {
            $show_map = get_post_meta( $form_id, 'Nish_aga_show_map_preview', true );
            $config['show_map_preview'] = ( '1' === $show_map );
            $config['map_container_selector'] = get_post_meta( $form_id, 'Nish_aga_map_container_selector', true );
        }

        // Geolocation auto-detect (Pro feature)
        $config['geolocation'] = false;
        if ( $is_paying ) {
            $geolocation = get_post_meta( $form_id, 'Nish_aga_geolocation', true );
            $config['geolocation'] = ( '1' === $geolocation );
        }

        // Address Validation (Pro feature)
        $config['address_validation'] = false;
        if ( $is_paying ) {
            $address_validation = get_post_meta( $form_id, 'Nish_aga_address_validation', true );
            $config['address_validation'] = ( '1' === $address_validation );
        }

        // Saved Addresses / Address Book (Pro feature)
        $config['saved_addresses'] = false;
        if ( $is_paying ) {
            $saved_addresses = get_post_meta( $form_id, 'Nish_aga_saved_addresses', true );
            $config['saved_addresses'] = ( '1' === $saved_addresses );
        }

        // Clean up empty selectors
        $config['selectors'] = array_filter( $config['selectors'] );

        return $config;
    }
}
