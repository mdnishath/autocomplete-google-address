<?php
/**
 * Setup Wizard for first-time plugin activation.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 */

defined( 'ABSPATH' ) || exit;

class AGA_Wizard {

    public function __construct() {
        add_action( 'admin_init', array( $this, 'maybe_redirect' ) );
        add_action( 'admin_menu', array( $this, 'register_page' ) );
        add_action( 'wp_ajax_aga_save_wizard', array( $this, 'ajax_save_wizard' ) );
    }

    public function maybe_redirect() {
        if ( ! get_transient( 'aga_show_wizard' ) ) {
            return;
        }

        if ( wp_doing_ajax() || defined( 'WP_CLI' ) || isset( $_GET['activate-multi'] ) ) {
            return;
        }

        delete_transient( 'aga_show_wizard' );

        wp_safe_redirect( admin_url( 'admin.php?page=aga-wizard' ) );
        exit;
    }

    public function register_page() {
        add_submenu_page(
            'edit.php?post_type=aga_form',
            __( 'Setup Wizard', 'autocomplete-google-address' ),
            '',
            'manage_options',
            'aga-wizard',
            array( $this, 'render_page' )
        );
    }

    public function render_page() {
        require_once AGA_PLUGIN_DIR . 'admin/views/html-admin-page-wizard.php';
    }

    public function ajax_save_wizard() {
        check_ajax_referer( 'aga_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized', 'autocomplete-google-address' ) ) );
        }

        $api_key   = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
        $form_type = isset( $_POST['form_type'] ) ? sanitize_text_field( wp_unslash( $_POST['form_type'] ) ) : '';

        if ( empty( $api_key ) ) {
            wp_send_json_error( array( 'message' => __( 'API key is required.', 'autocomplete-google-address' ) ) );
        }

        $allowed_types = array( 'woocommerce', 'cf7', 'wpforms', 'gravity', 'elementor', 'manual' );
        if ( ! in_array( $form_type, $allowed_types, true ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid form type.', 'autocomplete-google-address' ) ) );
        }

        // Save the API key.
        $settings            = get_option( 'Nish_aga_settings', array() );
        $settings['api_key'] = $api_key;

        $redirect_url = admin_url( 'edit.php?post_type=aga_form' );

        if ( 'woocommerce' === $form_type ) {
            // Zero-config: just enable the WooCommerce setting.
            // AGA_WooCommerce handles everything automatically (classic + block checkout).
            $settings['woocommerce_enabled'] = 1;
            update_option( 'Nish_aga_settings', $settings );

        } elseif ( 'manual' === $form_type ) {
            update_option( 'Nish_aga_settings', $settings );
            $redirect_url = admin_url( 'post-new.php?post_type=aga_form' );

        } else {
            // Form plugin preset (cf7, wpforms, gravity, elementor).
            update_option( 'Nish_aga_settings', $settings );

            $titles = array(
                'cf7'       => 'Contact Form 7 Setup',
                'wpforms'   => 'WPForms Setup',
                'gravity'   => 'Gravity Forms Setup',
                'elementor' => 'Elementor Forms Setup',
            );

            $post_id = wp_insert_post( array(
                'post_title'  => isset( $titles[ $form_type ] ) ? $titles[ $form_type ] : ucfirst( $form_type ) . ' Setup',
                'post_type'   => 'aga_form',
                'post_status' => 'publish',
            ) );

            if ( $post_id && ! is_wp_error( $post_id ) ) {
                update_post_meta( $post_id, 'Nish_aga_form_preset', $form_type );
                update_post_meta( $post_id, 'Nish_aga_activate_globally', '1' );
                update_post_meta( $post_id, 'Nish_aga_mode', 'smart_mapping' );

                // Fill in the actual preset selectors.
                if ( class_exists( 'AGA_Presets' ) ) {
                    $preset = AGA_Presets::get_preset( $form_type );
                    if ( $preset && ! empty( $preset['selectors'] ) ) {
                        $sel = $preset['selectors'];
                        if ( ! empty( $sel['main_selector'] ) ) {
                            update_post_meta( $post_id, 'Nish_aga_main_selector', $sel['main_selector'] );
                        }
                        if ( ! empty( $sel['street'] ) ) {
                            update_post_meta( $post_id, 'Nish_aga_street_selector', $sel['street'] );
                        }
                        if ( ! empty( $sel['city'] ) ) {
                            update_post_meta( $post_id, 'Nish_aga_city_selector', $sel['city'] );
                        }
                        if ( ! empty( $sel['state'] ) ) {
                            update_post_meta( $post_id, 'Nish_aga_state_selector', $sel['state'] );
                        }
                        if ( ! empty( $sel['zip'] ) ) {
                            update_post_meta( $post_id, 'Nish_aga_zip_selector', $sel['zip'] );
                        }
                        if ( ! empty( $sel['country'] ) ) {
                            update_post_meta( $post_id, 'Nish_aga_country_selector', $sel['country'] );
                        }
                    }
                }

                $redirect_url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
            }
        }

        wp_send_json_success( array(
            'redirect' => $redirect_url,
        ) );
    }
}
