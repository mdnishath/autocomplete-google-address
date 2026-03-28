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

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Don't redirect if already on the wizard page.
        if ( isset( $_GET['page'] ) && 'aga-wizard' === $_GET['page'] ) {
            delete_transient( 'aga_show_wizard' );
            return;
        }

        delete_transient( 'aga_show_wizard' );

        wp_safe_redirect( admin_url( 'admin.php?page=aga-wizard' ) );
        exit;
    }

    public function register_page() {
        // Register as a hidden top-level page (not under CPT) so it's always accessible.
        add_submenu_page(
            null, // Hidden — no parent menu
            __( 'Setup Wizard', 'autocomplete-google-address' ),
            '',
            'manage_options',
            'aga-wizard',
            array( $this, 'render_page' )
        );

        // Visible submenu under the Autocomplete menu.
        add_submenu_page(
            'edit.php?post_type=aga_form',
            __( 'Setup Wizard', 'autocomplete-google-address' ),
            __( 'Setup Wizard', 'autocomplete-google-address' ),
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

        $allowed_types = array( 'woocommerce', 'cf7', 'wpforms', 'gravity', 'elementor', 'fluent_forms', 'ninja_forms', 'manual' );
        if ( ! in_array( $form_type, $allowed_types, true ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid form type.', 'autocomplete-google-address' ) ) );
        }

        // Save the API key.
        $settings            = get_option( 'Nish_aga_settings', array() );
        $settings['api_key'] = $api_key;

        $redirect_url = admin_url( 'edit.php?post_type=aga_form' );

        if ( 'woocommerce' === $form_type ) {
            $settings['woocommerce_enabled'] = 1;
            update_option( 'Nish_aga_settings', $settings );

            // Detect block vs classic checkout.
            $is_block  = false;
            $is_classic = true; // Assume classic by default.
            $checkout_page_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'checkout' ) : 0;
            if ( $checkout_page_id > 0 ) {
                $checkout_post = get_post( $checkout_page_id );
                if ( $checkout_post && has_block( 'woocommerce/checkout', $checkout_post ) ) {
                    $is_block   = true;
                    $is_classic = false; // Block checkout replaces classic.
                }
            }

            // Delete any existing WooCommerce form configs to prevent duplicates on re-run.
            $existing_woo = get_posts( array(
                'post_type'      => 'aga_form',
                'posts_per_page' => -1,
                'meta_key'       => 'Nish_aga_form_preset',
                'meta_value'     => 'woocommerce',
                'fields'         => 'ids',
            ) );
            foreach ( $existing_woo as $old_id ) {
                wp_delete_post( $old_id, true );
            }

            // Build config sets based on detected checkout type.
            $configs = array();

            if ( $is_block ) {
                $configs[] = array(
                    'title'   => __( 'WooCommerce Billing (React Block Checkout)', 'autocomplete-google-address' ),
                    'main'    => '#billing-address_1',
                    'street'  => '#billing-address_1',
                    'city'    => '#billing-city',
                    'state'   => '#billing-state',
                    'zip'     => '#billing-postcode',
                    'country' => '#billing-country',
                );
                $configs[] = array(
                    'title'   => __( 'WooCommerce Shipping (React Block Checkout)', 'autocomplete-google-address' ),
                    'main'    => '#shipping-address_1',
                    'street'  => '#shipping-address_1',
                    'city'    => '#shipping-city',
                    'state'   => '#shipping-state',
                    'zip'     => '#shipping-postcode',
                    'country' => '#shipping-country',
                );
            }

            if ( $is_classic ) {
                $configs[] = array(
                    'title'   => __( 'WooCommerce Billing (Classic Checkout)', 'autocomplete-google-address' ),
                    'main'    => '#billing_address_1',
                    'street'  => '#billing_address_1',
                    'city'    => '#billing_city',
                    'state'   => '#billing_state',
                    'zip'     => '#billing_postcode',
                    'country' => '#billing_country',
                );
                $configs[] = array(
                    'title'   => __( 'WooCommerce Shipping (Classic Checkout)', 'autocomplete-google-address' ),
                    'main'    => '#shipping_address_1',
                    'street'  => '#shipping_address_1',
                    'city'    => '#shipping_city',
                    'state'   => '#shipping_state',
                    'zip'     => '#shipping_postcode',
                    'country' => '#shipping_country',
                );
            }

            $first_post_id = 0;
            foreach ( $configs as $cfg ) {
                $post_id = wp_insert_post( array(
                    'post_title'  => $cfg['title'],
                    'post_type'   => 'aga_form',
                    'post_status' => 'publish',
                ) );

                if ( $post_id && ! is_wp_error( $post_id ) ) {
                    update_post_meta( $post_id, 'Nish_aga_form_preset', 'woocommerce' );
                    update_post_meta( $post_id, 'Nish_aga_mode', 'smart_mapping' );
                    update_post_meta( $post_id, 'Nish_aga_main_selector', $cfg['main'] );
                    update_post_meta( $post_id, 'Nish_aga_street_selector', $cfg['street'] );
                    update_post_meta( $post_id, 'Nish_aga_city_selector', $cfg['city'] );
                    update_post_meta( $post_id, 'Nish_aga_state_selector', $cfg['state'] );
                    update_post_meta( $post_id, 'Nish_aga_zip_selector', $cfg['zip'] );
                    update_post_meta( $post_id, 'Nish_aga_country_selector', $cfg['country'] );
                    update_post_meta( $post_id, 'Nish_aga_state_format', 'short' );
                    update_post_meta( $post_id, 'Nish_aga_country_format', 'short' );
                    update_post_meta( $post_id, 'Nish_aga_activate_globally', '1' );

                    // Pro feature toggles from wizard step 3.
                    $address_validation = isset( $_POST['address_validation'] ) ? sanitize_text_field( wp_unslash( $_POST['address_validation'] ) ) : '0';
                    $geolocation        = isset( $_POST['geolocation'] ) ? sanitize_text_field( wp_unslash( $_POST['geolocation'] ) ) : '0';
                    $saved_addresses    = isset( $_POST['saved_addresses'] ) ? sanitize_text_field( wp_unslash( $_POST['saved_addresses'] ) ) : '0';
                    $map_picker_val     = isset( $_POST['map_picker'] ) ? sanitize_text_field( wp_unslash( $_POST['map_picker'] ) ) : '0';
                    $country_restrict   = isset( $_POST['country_restriction'] ) ? sanitize_text_field( wp_unslash( $_POST['country_restriction'] ) ) : '';
                    $place_types        = isset( $_POST['place_types'] ) ? sanitize_text_field( wp_unslash( $_POST['place_types'] ) ) : '';
                    $language           = isset( $_POST['language'] ) ? sanitize_text_field( wp_unslash( $_POST['language'] ) ) : '';

                    if ( '1' === $address_validation ) {
                        update_post_meta( $post_id, 'Nish_aga_address_validation', '1' );
                    }
                    if ( '1' === $geolocation ) {
                        update_post_meta( $post_id, 'Nish_aga_geolocation', '1' );
                    }
                    if ( '1' === $saved_addresses ) {
                        update_post_meta( $post_id, 'Nish_aga_saved_addresses', '1' );
                    }
                    if ( '1' === $map_picker_val ) {
                        update_post_meta( $post_id, 'Nish_aga_map_picker', '1' );
                    }
                    if ( ! empty( $country_restrict ) ) {
                        update_post_meta( $post_id, 'Nish_aga_country_restriction', $country_restrict );
                    }
                    if ( ! empty( $place_types ) ) {
                        update_post_meta( $post_id, 'Nish_aga_place_types', $place_types );
                    }
                    if ( ! empty( $language ) ) {
                        update_post_meta( $post_id, 'Nish_aga_language_override', $language );
                    }

                    if ( ! $first_post_id ) {
                        $first_post_id = $post_id;
                    }
                }
            }

            $redirect_url = $first_post_id
                ? admin_url( 'post.php?post=' . $first_post_id . '&action=edit' )
                : admin_url( 'edit.php?post_type=aga_form' );

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
                'elementor'     => 'Elementor Forms Setup',
                'fluent_forms'  => 'Fluent Forms Setup',
                'ninja_forms'   => 'Ninja Forms Setup',
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
