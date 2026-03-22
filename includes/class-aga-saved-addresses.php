<?php
/**
 * Handles saving and retrieving user address history (Pro feature).
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 */

defined( 'ABSPATH' ) || exit;

class AGA_Saved_Addresses {

    /**
     * Maximum number of addresses to store per user.
     */
    const MAX_ADDRESSES = 5;

    /**
     * User meta key for stored addresses.
     */
    const META_KEY = 'aga_saved_addresses';

    /**
     * Register AJAX hooks.
     */
    public function __construct() {
        add_action( 'wp_ajax_aga_save_address', array( $this, 'save_address' ) );
        add_action( 'wp_ajax_aga_get_addresses', array( $this, 'get_addresses' ) );
    }

    /**
     * AJAX handler: save an address to the current user's address book.
     */
    public function save_address() {
        check_ajax_referer( 'aga_frontend_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Not logged in.' ) );
        }

        if ( ! function_exists( 'google_autocomplete' ) || ! google_autocomplete()->is_paying() ) {
            wp_send_json_error( array( 'message' => 'Pro feature.' ) );
        }

        $address    = isset( $_POST['address'] )    ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '';
        $lat        = isset( $_POST['lat'] )         ? floatval( $_POST['lat'] ) : 0;
        $lng        = isset( $_POST['lng'] )         ? floatval( $_POST['lng'] ) : 0;
        $place_id   = isset( $_POST['place_id'] )    ? sanitize_text_field( wp_unslash( $_POST['place_id'] ) ) : '';
        $components = isset( $_POST['components'] )  ? $this->sanitize_components( wp_unslash( $_POST['components'] ) ) : array();

        if ( empty( $address ) ) {
            wp_send_json_error( array( 'message' => 'No address provided.' ) );
        }

        $user_id   = get_current_user_id();
        $addresses = get_user_meta( $user_id, self::META_KEY, true );
        if ( ! is_array( $addresses ) ) {
            $addresses = array();
        }

        // Remove duplicate if the same place_id already exists.
        if ( $place_id ) {
            $addresses = array_values( array_filter( $addresses, function ( $entry ) use ( $place_id ) {
                return ! isset( $entry['place_id'] ) || $entry['place_id'] !== $place_id;
            } ) );
        }

        // Prepend new entry.
        array_unshift( $addresses, array(
            'address'    => $address,
            'lat'        => $lat,
            'lng'        => $lng,
            'place_id'   => $place_id,
            'components' => $components,
            'timestamp'  => time(),
        ) );

        // Keep only the most recent entries.
        $addresses = array_slice( $addresses, 0, self::MAX_ADDRESSES );

        update_user_meta( $user_id, self::META_KEY, $addresses );

        wp_send_json_success( array( 'saved' => true ) );
    }

    /**
     * AJAX handler: return the current user's saved addresses.
     */
    public function get_addresses() {
        check_ajax_referer( 'aga_frontend_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Not logged in.' ) );
        }

        if ( ! function_exists( 'google_autocomplete' ) || ! google_autocomplete()->is_paying() ) {
            wp_send_json_error( array( 'message' => 'Pro feature.' ) );
        }

        $user_id   = get_current_user_id();
        $addresses = get_user_meta( $user_id, self::META_KEY, true );
        if ( ! is_array( $addresses ) ) {
            $addresses = array();
        }

        wp_send_json_success( array( 'addresses' => $addresses ) );
    }

    /**
     * Sanitize address components array from POST data.
     *
     * @param mixed $raw Raw components data.
     * @return array Sanitized components.
     */
    private function sanitize_components( $raw ) {
        if ( ! is_array( $raw ) ) {
            return array();
        }

        $sanitized = array();
        foreach ( $raw as $component ) {
            if ( ! is_array( $component ) ) {
                continue;
            }
            $entry = array();
            if ( isset( $component['types'] ) && is_array( $component['types'] ) ) {
                $entry['types'] = array_map( 'sanitize_text_field', $component['types'] );
            }
            if ( isset( $component['longText'] ) ) {
                $entry['longText'] = sanitize_text_field( $component['longText'] );
            }
            if ( isset( $component['shortText'] ) ) {
                $entry['shortText'] = sanitize_text_field( $component['shortText'] );
            }
            $sanitized[] = $entry;
        }

        return $sanitized;
    }
}
