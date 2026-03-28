<?php
/**
 * Import/Export functionality for form configurations.
 *
 * Allows users to export their aga_form configurations as JSON
 * and import them on another site.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

class AGA_Import_Export {

    /**
     * Constructor. Registers AJAX hooks for export and import.
     */
    public function __construct() {
        add_action( 'wp_ajax_aga_export_configs', array( $this, 'export_configs' ) );
        add_action( 'wp_ajax_aga_import_configs', array( $this, 'import_configs' ) );
    }

    /**
     * Export all published aga_form posts as a JSON download.
     */
    public function export_configs() {
        // Verify nonce.
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'aga_import_export_nonce' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'autocomplete-google-address' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to export configurations.', 'autocomplete-google-address' ) );
        }

        $posts = get_posts( array(
            'post_type'      => 'aga_form',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'ASC',
        ) );

        $export_data = array();

        foreach ( $posts as $post ) {
            $all_meta = get_post_meta( $post->ID );
            $meta     = array();

            foreach ( $all_meta as $key => $values ) {
                if ( strpos( $key, 'Nish_aga_' ) === 0 ) {
                    // get_post_meta returns arrays; use the first value.
                    $meta[ $key ] = maybe_unserialize( $values[0] );
                }
            }

            $export_data[] = array(
                'title' => $post->post_title,
                'meta'  => $meta,
            );
        }

        // Send JSON download headers.
        nocache_headers();
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="aga-configs-export.json"' );

        echo wp_json_encode( $export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
        die();
    }

    /**
     * Import aga_form posts from an uploaded JSON file.
     */
    public function import_configs() {
        // Verify nonce.
        check_ajax_referer( 'aga_import_export_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to import configurations.', 'autocomplete-google-address' ) ) );
        }

        // Check for uploaded file.
        if ( empty( $_FILES['aga_import_file'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No file was uploaded.', 'autocomplete-google-address' ) ) );
        }

        $file = $_FILES['aga_import_file'];

        // Basic file validation.
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            wp_send_json_error( array( 'message' => __( 'File upload error.', 'autocomplete-google-address' ) ) );
        }

        // Read and decode JSON.
        $json_content = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $configs      = json_decode( $json_content, true );

        if ( ! is_array( $configs ) || json_last_error() !== JSON_ERROR_NONE ) {
            wp_send_json_error( array( 'message' => __( 'Invalid JSON file.', 'autocomplete-google-address' ) ) );
        }

        $imported_count = 0;

        foreach ( $configs as $config ) {
            if ( empty( $config['title'] ) || ! isset( $config['meta'] ) || ! is_array( $config['meta'] ) ) {
                continue;
            }

            $post_id = wp_insert_post( array(
                'post_type'   => 'aga_form',
                'post_title'  => sanitize_text_field( $config['title'] ),
                'post_status' => 'publish',
            ) );

            if ( is_wp_error( $post_id ) || ! $post_id ) {
                continue;
            }

            foreach ( $config['meta'] as $key => $value ) {
                // Only allow meta keys that start with Nish_aga_.
                if ( strpos( $key, 'Nish_aga_' ) !== 0 ) {
                    continue;
                }
                update_post_meta( $post_id, sanitize_key( $key ), $value );
            }

            $imported_count++;
        }

        wp_send_json_success( array(
            'message' => sprintf(
                /* translators: %d: number of configurations imported */
                __( 'Successfully imported %d configuration(s).', 'autocomplete-google-address' ),
                $imported_count
            ),
            'count'   => $imported_count,
        ) );
    }
}
