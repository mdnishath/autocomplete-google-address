<?php
/**
 * Handles Address Validation via Google Address Validation API.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 */

defined( 'ABSPATH' ) || exit;

class AGA_Validation {

    public function __construct() {
        add_action( 'wp_ajax_aga_validate_address', array( $this, 'handle_validate_address' ) );
        add_action( 'wp_ajax_nopriv_aga_validate_address', array( $this, 'handle_validate_address' ) );
    }

    /**
     * AJAX handler for address validation.
     */
    public function handle_validate_address() {
        check_ajax_referer( 'aga_frontend_nonce', 'nonce' );

        // Only allow if user is paying (Pro feature).
        if ( ! function_exists( 'google_autocomplete' ) || ! google_autocomplete()->is_paying() ) {
            wp_send_json_error( array( 'message' => __( 'This feature requires a Pro license.', 'autocomplete-google-address' ) ), 403 );
        }

        $address  = isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '';
        $place_id = isset( $_POST['place_id'] ) ? sanitize_text_field( wp_unslash( $_POST['place_id'] ) ) : '';

        if ( empty( $address ) ) {
            wp_send_json_error( array( 'message' => __( 'No address provided.', 'autocomplete-google-address' ) ) );
        }

        $settings = get_option( 'Nish_aga_settings' );
        $api_key  = ! empty( $settings['api_key'] ) ? $settings['api_key'] : '';

        if ( empty( $api_key ) ) {
            wp_send_json_error( array( 'message' => __( 'API key not configured.', 'autocomplete-google-address' ) ) );
        }

        $api_url = 'https://addressvalidation.googleapis.com/v1:validateAddress?key=' . $api_key;

        $body = array(
            'address' => array(
                'addressLines' => array( $address ),
            ),
        );

        $response = wp_remote_post( $api_url, array(
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body'    => wp_json_encode( $body ),
            'timeout' => 15,
        ) );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array(
                'message' => __( 'Validation request failed.', 'autocomplete-google-address' ),
            ) );
        }

        $status_code   = wp_remote_retrieve_response_code( $response );
        $response_body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status_code !== 200 ) {
            // Check if Address Validation API is not enabled.
            $error_msg = '';
            if ( ! empty( $response_body['error']['message'] ) ) {
                $error_msg = $response_body['error']['message'];
            }

            if ( strpos( $error_msg, 'not been used' ) !== false || strpos( $error_msg, 'not enabled' ) !== false || $status_code === 403 ) {
                wp_send_json_error( array(
                    'message' => __( 'Address Validation API is not enabled. Enable it in Google Cloud Console.', 'autocomplete-google-address' ),
                ) );
            }

            wp_send_json_error( array(
                'message' => __( 'Unable to validate the address.', 'autocomplete-google-address' ),
            ) );
        }

        if ( empty( $response_body['result'] ) ) {
            wp_send_json_error( array(
                'message' => __( 'No validation result returned.', 'autocomplete-google-address' ),
            ) );
        }

        $result  = $response_body['result'];
        $verdict = isset( $result['verdict'] ) ? $result['verdict'] : array();

        $address_complete       = ! empty( $verdict['addressComplete'] );
        $validation_granularity = isset( $verdict['validationGranularity'] ) ? $verdict['validationGranularity'] : '';

        // Determine validation level — be generous with Google-selected addresses.
        if ( $address_complete ) {
            $level   = 'valid';
            $message = __( 'Address verified.', 'autocomplete-google-address' );
            $valid   = true;
        } elseif ( ! empty( $validation_granularity ) && 'OTHER' !== $validation_granularity ) {
            $level   = 'warning';
            $message = __( 'Address found but may be imprecise.', 'autocomplete-google-address' );
            $valid   = true;
        } else {
            $level   = 'invalid';
            $message = __( 'Address could not be verified.', 'autocomplete-google-address' );
            $valid   = false;
        }

        // Fire webhook for invalid or warning addresses.
        if ( 'invalid' === $level || 'warning' === $level ) {
            $webhook_url = aga_get_setting( 'webhook_url' );
            if ( ! empty( $webhook_url ) ) {
                wp_remote_post( $webhook_url, array(
                    'headers' => array( 'Content-Type' => 'application/json' ),
                    'body'    => wp_json_encode( array(
                        'event'   => 'address_validation',
                        'level'   => $level,
                        'address' => $address,
                        'message' => $message,
                        'site'    => get_site_url(),
                        'time'    => current_time( 'mysql' ),
                    ) ),
                    'timeout'  => 5,
                    'blocking' => false,
                ) );
            }
        }

        wp_send_json_success( array(
            'valid'   => $valid,
            'level'   => $level,
            'message' => $message,
        ) );
    }
}
