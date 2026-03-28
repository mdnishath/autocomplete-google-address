<?php
defined( 'ABSPATH' ) || exit;

class AGA_REST_API {

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        register_rest_route( 'aga/v1', '/config', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_config' ),
            'permission_callback' => '__return_true', // Public endpoint
        ) );

        register_rest_route( 'aga/v1', '/validate', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'validate_address' ),
            'permission_callback' => function () {
                return is_user_logged_in();
            },
            'args' => array(
                'address' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ) );
    }

    public function get_config( $request ) {
        $settings = get_option( 'Nish_aga_settings', array() );
        $api_key  = isset( $settings['api_key'] ) ? $settings['api_key'] : '';

        if ( empty( $api_key ) ) {
            return new WP_REST_Response( array( 'error' => 'API key not configured' ), 400 );
        }

        // Get all published form configs
        $forms = get_posts( array(
            'post_type'      => 'aga_form',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
        ) );

        $configs = array();
        foreach ( $forms as $form_id ) {
            if ( class_exists( 'AGA_Autocomplete' ) ) {
                $config = AGA_Autocomplete::get_js_config( $form_id );
                if ( ! empty( $config ) ) {
                    $configs[] = $config;
                }
            }
        }

        return new WP_REST_Response( array(
            'api_key' => $api_key,
            'configs' => $configs,
        ), 200 );
    }

    public function validate_address( $request ) {
        if ( ! function_exists( 'google_autocomplete' ) || ! google_autocomplete()->is_paying() ) {
            return new WP_REST_Response( array( 'error' => 'Pro feature' ), 403 );
        }

        $address  = $request->get_param( 'address' );
        $settings = get_option( 'Nish_aga_settings', array() );
        $api_key  = isset( $settings['api_key'] ) ? $settings['api_key'] : '';

        if ( empty( $api_key ) ) {
            return new WP_REST_Response( array( 'error' => 'API key not configured' ), 400 );
        }

        $response = wp_remote_post(
            'https://addressvalidation.googleapis.com/v1:validateAddress?key=' . $api_key,
            array(
                'headers' => array( 'Content-Type' => 'application/json' ),
                'body'    => wp_json_encode( array(
                    'address' => array( 'addressLines' => array( $address ) ),
                ) ),
                'timeout' => 10,
            )
        );

        if ( is_wp_error( $response ) ) {
            return new WP_REST_Response( array( 'error' => $response->get_error_message() ), 500 );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        $result = isset( $body['result'] ) ? $body['result'] : array();

        $level = 'invalid';
        if ( isset( $result['verdict']['addressComplete'] ) && $result['verdict']['addressComplete'] ) {
            $level = 'valid';
        } elseif ( isset( $result['verdict']['validationGranularity'] ) && 'OTHER' !== $result['verdict']['validationGranularity'] ) {
            $level = 'warning';
        }

        return new WP_REST_Response( array(
            'level'   => $level,
            'address' => isset( $result['address']['formattedAddress'] ) ? $result['address']['formattedAddress'] : $address,
        ), 200 );
    }
}
