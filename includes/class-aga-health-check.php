<?php
/**
 * Health Check / Diagnostics for the plugin.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 */

defined( 'ABSPATH' ) || exit;

class AGA_Health_Check {

    /**
     * Constructor. Register AJAX actions.
     */
    public function __construct() {
        add_action( 'wp_ajax_aga_health_check', array( $this, 'run_health_check' ) );
    }

    /**
     * AJAX handler for running all diagnostic checks.
     */
    public function run_health_check() {
        check_ajax_referer( 'aga_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        // Return cached results if available (5-minute TTL).
        $cached = get_transient( 'aga_health_check_results' );
        if ( $cached !== false ) {
            wp_send_json_success( $cached );
        }

        $results = array();

        $results[] = $this->check_api_key();
        $results[] = $this->check_places_api();
        $results[] = $this->check_maps_js_api();
        $results[] = $this->check_geocoding_api();
        $results[] = $this->check_address_validation_api();
        $results[] = $this->check_form_configurations();
        $results[] = $this->check_woocommerce();
        $results[] = $this->check_php_version();
        $results[] = $this->check_wp_version();
        $results[] = $this->check_plugin_version();

        set_transient( 'aga_health_check_results', $results, 5 * MINUTE_IN_SECONDS );

        wp_send_json_success( $results );
    }

    /**
     * Get the saved API key.
     *
     * @return string
     */
    private function get_api_key() {
        $options = get_option( 'Nish_aga_settings', array() );
        return isset( $options['api_key'] ) ? $options['api_key'] : '';
    }

    /**
     * Check 1: API Key Status.
     */
    private function check_api_key() {
        $api_key = $this->get_api_key();

        if ( empty( $api_key ) ) {
            return array(
                'name'    => 'API Key Status',
                'status'  => 'error',
                'details' => 'No API key saved. Go to Settings to add one.',
            );
        }

        $masked = str_repeat( '*', max( 0, strlen( $api_key ) - 4 ) ) . substr( $api_key, -4 );

        return array(
            'name'    => 'API Key Status',
            'status'  => 'success',
            'details' => 'API key is saved. Key: ' . $masked,
        );
    }

    /**
     * Check 2: Places API (New).
     */
    private function check_places_api() {
        $api_key = $this->get_api_key();

        if ( empty( $api_key ) ) {
            return array(
                'name'    => 'Places API (New)',
                'status'  => 'warning',
                'details' => 'Cannot test — no API key configured.',
            );
        }

        $url      = 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json?input=test&inputtype=textquery&key=' . $api_key;
        $response = wp_remote_get( $url, array( 'timeout' => 10 ) );

        if ( is_wp_error( $response ) ) {
            return array(
                'name'    => 'Places API (New)',
                'status'  => 'error',
                'details' => 'Request failed: ' . $response->get_error_message(),
            );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 === $code && isset( $body['status'] ) && 'REQUEST_DENIED' !== $body['status'] ) {
            return array(
                'name'    => 'Places API (New)',
                'status'  => 'success',
                'details' => 'Working. Response status: ' . $body['status'],
            );
        }

        $error_msg = isset( $body['error_message'] ) ? $body['error_message'] : ( isset( $body['status'] ) ? $body['status'] : 'Unknown error' );

        return array(
            'name'    => 'Places API (New)',
            'status'  => 'error',
            'details' => 'API error: ' . $error_msg,
        );
    }

    /**
     * Check 3: Maps JavaScript API.
     */
    private function check_maps_js_api() {
        $api_key = $this->get_api_key();

        if ( empty( $api_key ) ) {
            return array(
                'name'    => 'Maps JavaScript API',
                'status'  => 'warning',
                'details' => 'Cannot verify — no API key configured.',
            );
        }

        return array(
            'name'    => 'Maps JavaScript API',
            'status'  => 'success',
            'details' => 'API key is present. The Maps JS script will be enqueued on the frontend.',
        );
    }

    /**
     * Check 4: Geocoding API.
     */
    private function check_geocoding_api() {
        $api_key = $this->get_api_key();

        if ( empty( $api_key ) ) {
            return array(
                'name'    => 'Geocoding API',
                'status'  => 'warning',
                'details' => 'Cannot test — no API key configured.',
            );
        }

        $url      = 'https://maps.googleapis.com/maps/api/geocode/json?address=test&key=' . $api_key;
        $response = wp_remote_get( $url, array( 'timeout' => 10 ) );

        if ( is_wp_error( $response ) ) {
            return array(
                'name'    => 'Geocoding API',
                'status'  => 'error',
                'details' => 'Request failed: ' . $response->get_error_message(),
            );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 === $code && isset( $body['status'] ) && 'REQUEST_DENIED' !== $body['status'] ) {
            return array(
                'name'    => 'Geocoding API',
                'status'  => 'success',
                'details' => 'Working. Response status: ' . $body['status'],
            );
        }

        $error_msg = isset( $body['error_message'] ) ? $body['error_message'] : ( isset( $body['status'] ) ? $body['status'] : 'Unknown error' );

        return array(
            'name'    => 'Geocoding API',
            'status'  => 'error',
            'details' => 'API error: ' . $error_msg,
        );
    }

    /**
     * Check 5: Address Validation API.
     */
    private function check_address_validation_api() {
        $api_key = $this->get_api_key();

        if ( empty( $api_key ) ) {
            return array(
                'name'    => 'Address Validation API',
                'status'  => 'warning',
                'details' => 'Cannot test — no API key configured.',
            );
        }

        $url  = 'https://addressvalidation.googleapis.com/v1:validateAddress?key=' . $api_key;
        $body = wp_json_encode( array(
            'address' => array(
                'addressLines' => array( '1600 Amphitheatre Parkway' ),
            ),
        ) );

        $response = wp_remote_post( $url, array(
            'timeout' => 10,
            'headers' => array( 'Content-Type' => 'application/json' ),
            'body'    => $body,
        ) );

        if ( is_wp_error( $response ) ) {
            return array(
                'name'    => 'Address Validation API',
                'status'  => 'error',
                'details' => 'Request failed: ' . $response->get_error_message(),
            );
        }

        $code         = wp_remote_retrieve_response_code( $response );
        $response_body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 === $code && isset( $response_body['result'] ) ) {
            return array(
                'name'    => 'Address Validation API',
                'status'  => 'success',
                'details' => 'Working. Address validation returned a result.',
            );
        }

        $error_msg = 'Unknown error';
        if ( isset( $response_body['error']['message'] ) ) {
            $error_msg = $response_body['error']['message'];
        } elseif ( isset( $response_body['error']['status'] ) ) {
            $error_msg = $response_body['error']['status'];
        }

        return array(
            'name'    => 'Address Validation API',
            'status'  => 'error',
            'details' => 'API error (HTTP ' . $code . '): ' . $error_msg,
        );
    }

    /**
     * Check 6: Form Configurations.
     */
    private function check_form_configurations() {
        $count = wp_count_posts( 'aga_form' );
        $published = isset( $count->publish ) ? (int) $count->publish : 0;
        $total     = 0;

        if ( $count ) {
            foreach ( $count as $status => $num ) {
                $total += (int) $num;
            }
        }

        if ( 0 === $published ) {
            return array(
                'name'    => 'Form Configurations',
                'status'  => 'warning',
                'details' => 'No published form configurations found (' . $total . ' total). Create one under Google Address > Add New.',
            );
        }

        return array(
            'name'    => 'Form Configurations',
            'status'  => 'success',
            'details' => $published . ' published configuration(s) (' . $total . ' total).',
        );
    }

    /**
     * Check 7: WooCommerce Status.
     */
    private function check_woocommerce() {
        $wc_active = class_exists( 'WooCommerce' );
        $options   = get_option( 'Nish_aga_settings', array() );
        $wc_enabled = ! empty( $options['woocommerce_enabled'] );

        if ( ! $wc_active ) {
            return array(
                'name'    => 'WooCommerce Status',
                'status'  => 'warning',
                'details' => 'WooCommerce is not active. Install and activate it to use the WooCommerce integration.',
            );
        }

        if ( ! $wc_enabled ) {
            return array(
                'name'    => 'WooCommerce Status',
                'status'  => 'warning',
                'details' => 'WooCommerce is active but the integration is disabled. Enable it in Settings.',
            );
        }

        return array(
            'name'    => 'WooCommerce Status',
            'status'  => 'success',
            'details' => 'WooCommerce is active and integration is enabled.',
        );
    }

    /**
     * Check 8: PHP Version.
     */
    private function check_php_version() {
        $version = phpversion();
        $status  = version_compare( $version, '7.4', '>=' ) ? 'success' : 'warning';
        $details = 'PHP ' . $version;

        if ( 'warning' === $status ) {
            $details .= ' — PHP 7.4 or higher is recommended.';
        }

        return array(
            'name'    => 'PHP Version',
            'status'  => $status,
            'details' => $details,
        );
    }

    /**
     * Check 9: WordPress Version.
     */
    private function check_wp_version() {
        global $wp_version;

        $status  = version_compare( $wp_version, '5.8', '>=' ) ? 'success' : 'warning';
        $details = 'WordPress ' . $wp_version;

        if ( 'warning' === $status ) {
            $details .= ' — WordPress 5.8 or higher is recommended.';
        }

        return array(
            'name'    => 'WordPress Version',
            'status'  => $status,
            'details' => $details,
        );
    }

    /**
     * Check 10: Plugin Version.
     */
    private function check_plugin_version() {
        $version = defined( 'AGA_VERSION' ) ? AGA_VERSION : 'Unknown';

        return array(
            'name'    => 'Plugin Version',
            'status'  => 'success',
            'details' => 'Autocomplete Google Address v' . $version,
        );
    }
}
