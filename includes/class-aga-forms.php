<?php
defined( 'ABSPATH' ) || exit;

class AGA_Forms {

    private $post_type = 'aga_form';

public function __construct() {
    add_action( 'init', array( $this, 'register_post_type' ), 20 );
    // add_action( 'admin_menu', array( $this, 'remove_duplicate_add_new_menu' ), 999 );
    

    add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
    add_action( 'save_post_' . $this->post_type, array( $this, 'save_meta_box_data' ) );
    add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'set_custom_edit_columns' ) );
    add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );
}


    /**
     * Register Custom Post Type
     */
    public function register_post_type() {

        $labels = array(
            'name'               => 'Address Forms',
            'singular_name'      => 'Address Form',
            'menu_name'          => 'Google Address',
            'name_admin_bar'     => 'Address Form',
            'add_new'            => 'Add New Form Config',
            'add_new_item'       => 'Add New',
            'new_item'           => 'New Form Config',
            'edit_item'          => 'Edit Form Config',
            'view_item'          => 'View Form Config',
            'all_items'          => 'All Configs',
            'search_items'       => 'Search Form Configs',
            'not_found'          => 'No form configs found.',
            'not_found_in_trash' => 'No form configs found in Trash.',
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_icon'          => 'dashicons-location-alt',
            'menu_position'      => 13,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'supports'           => array( 'title' ),
            'show_in_rest'       => true,
        );

        register_post_type( $this->post_type, $args );
    }

    public function remove_duplicate_add_new_menu() {
    remove_submenu_page(
        'edit.php?post_type=' . $this->post_type,
        'post-new.php?post_type=' . $this->post_type
    );
}

public function add_custom_add_new_menu() {

    add_submenu_page(
        'edit.php?post_type=' . $this->post_type,
        'Add New Form Config',
        'Add New Form Config',
        'edit_posts',
        'post-new.php?post_type=' . $this->post_type
    );
}



    /**
     * Meta Box
     */
    public function add_meta_boxes() {
        add_meta_box(
            'aga_form_config_metabox',
            'Form Configuration',
            array( $this, 'render_meta_box' ),
            $this->post_type,
            'normal',
            'high'
        );
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( 'aga_save_meta_box_data', 'aga_meta_box_nonce' );

        require_once AGA_PLUGIN_DIR . 'admin/views/html-admin-page-form-edit.php';
    }

    /**
     * Save Meta
     */
    public function save_meta_box_data( $post_id ) {

        if (
            ! isset( $_POST['aga_meta_box_nonce'] ) ||
            ! wp_verify_nonce( $_POST['aga_meta_box_nonce'], 'aga_save_meta_box_data' )
        ) {
            return;
        }

        if (
            defined( 'DOING_AUTOSAVE' ) ||
            wp_is_post_revision( $post_id ) ||
            get_post_type( $post_id ) !== $this->post_type ||
            ! current_user_can( 'edit_post', $post_id )
        ) {
            return;
        }

        $fields = array(
            'Nish_aga_mode',
            'Nish_aga_main_selector',
            'Nish_aga_language_override',
            'Nish_aga_lat_selector',
            'Nish_aga_lng_selector',
            'Nish_aga_place_id_selector',
            'Nish_aga_street_selector',
            'Nish_aga_city_selector',
            'Nish_aga_state_selector',
            'Nish_aga_zip_selector',
            'Nish_aga_country_selector',
            'Nish_aga_map_lat_selector',
            'Nish_aga_map_lng_selector',
            'Nish_aga_smart_place_id_selector',
            'Nish_aga_state_format',
            'Nish_aga_country_format',
            'Nish_aga_place_types',
            'Nish_aga_form_preset',
            'Nish_aga_map_container_selector',
        );

        // Country restriction — multi-select array to comma-separated string.
        if ( isset( $_POST['Nish_aga_country_restriction'] ) && is_array( $_POST['Nish_aga_country_restriction'] ) ) {
            $countries = array_map( 'sanitize_text_field', $_POST['Nish_aga_country_restriction'] );
            $countries = array_slice( $countries, 0, 5 );
            update_post_meta( $post_id, 'Nish_aga_country_restriction', implode( ',', $countries ) );
        } else {
            update_post_meta( $post_id, 'Nish_aga_country_restriction', '' );
        }

        foreach ( $fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta(
                    $post_id,
                    $field,
                    sanitize_text_field( wp_unslash( $_POST[ $field ] ) )
                );
            } else {
                delete_post_meta( $post_id, $field );
            }
        }

        // Checkbox fields
        update_post_meta(
            $post_id,
            'Nish_aga_activate_globally',
            isset( $_POST['Nish_aga_activate_globally'] ) ? '1' : ''
        );

        update_post_meta(
            $post_id,
            'Nish_aga_show_map_preview',
            isset( $_POST['Nish_aga_show_map_preview'] ) ? '1' : ''
        );

        update_post_meta(
            $post_id,
            'Nish_aga_address_validation',
            isset( $_POST['Nish_aga_address_validation'] ) ? '1' : ''
        );

        update_post_meta(
            $post_id,
            'Nish_aga_geolocation',
            isset( $_POST['Nish_aga_geolocation'] ) ? '1' : ''
        );

        update_post_meta(
            $post_id,
            'Nish_aga_saved_addresses',
            isset( $_POST['Nish_aga_saved_addresses'] ) ? '1' : ''
        );

        if ( isset( $_POST['Nish_aga_load_on_pages'] ) && is_array( $_POST['Nish_aga_load_on_pages'] ) ) {
            update_post_meta(
                $post_id,
                'Nish_aga_load_on_pages',
                array_map( 'absint', $_POST['Nish_aga_load_on_pages'] )
            );
        } else {
            delete_post_meta( $post_id, 'Nish_aga_load_on_pages' );
        }
    }

    /**
     * Add "Duplicate" link to row actions for aga_form posts.
     */
    public function add_duplicate_row_action( $actions, $post ) {
        if ( $post->post_type === $this->post_type && current_user_can( 'edit_posts' ) ) {
            $url = wp_nonce_url(
                admin_url( 'admin.php?action=aga_duplicate_form&post=' . $post->ID ),
                'aga_duplicate_form_' . $post->ID
            );
            $actions['duplicate'] = '<a href="' . esc_url( $url ) . '" title="' . esc_attr__( 'Duplicate this form config' ) . '">Duplicate</a>';
        }
        return $actions;
    }

    /**
     * Handle the duplicate form action.
     */
    public function handle_duplicate_form() {
        if ( ! isset( $_GET['post'] ) || ! isset( $_GET['_wpnonce'] ) ) {
            wp_die( 'Missing parameters.' );
        }

        $post_id = absint( $_GET['post'] );

        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'aga_duplicate_form_' . $post_id ) ) {
            wp_die( 'Security check failed.' );
        }

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( 'You do not have permission to do this.' );
        }

        $original = get_post( $post_id );

        if ( ! $original || $original->post_type !== $this->post_type ) {
            wp_die( 'Original form config not found.' );
        }

        $new_post_id = wp_insert_post( array(
            'post_title'  => $original->post_title . ' (Copy)',
            'post_content' => $original->post_content,
            'post_status' => 'draft',
            'post_type'   => $this->post_type,
        ) );

        if ( is_wp_error( $new_post_id ) ) {
            wp_die( 'Could not duplicate the form config.' );
        }

        // Copy all meta keys starting with Nish_aga_
        $all_meta = get_post_meta( $post_id );
        foreach ( $all_meta as $meta_key => $meta_values ) {
            if ( strpos( $meta_key, 'Nish_aga_' ) === 0 ) {
                foreach ( $meta_values as $meta_value ) {
                    update_post_meta( $new_post_id, $meta_key, maybe_unserialize( $meta_value ) );
                }
            }
        }

        wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id . '&aga_duplicated=1' ) );
        exit;
    }

    /**
     * Admin Columns
     */
    public function set_custom_edit_columns( $columns ) {
        return array(
            'cb'         => $columns['cb'],
            'title'      => $columns['title'],
            'aga_mode'   => 'Mode',
            'aga_global' => 'Global',
            'date'       => $columns['date'],
        );
    }

    public function custom_column_content( $column, $post_id ) {

        if ( 'aga_mode' === $column ) {
            $mode = get_post_meta( $post_id, 'Nish_aga_mode', true );
            echo esc_html(
                $mode === 'single_line'
                    ? 'Single Line'
                    : ( $mode === 'smart_mapping' ? 'Smart Mapping' : 'None' )
            );
        }

        if ( 'aga_global' === $column ) {
            echo get_post_meta( $post_id, 'Nish_aga_activate_globally', true )
                ? '<span style="color:green;">✓</span>'
                : '<span style="color:red;">✗</span>';
        }
    }
}

new AGA_Forms();
