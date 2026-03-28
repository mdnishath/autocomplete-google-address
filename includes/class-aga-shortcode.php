<?php
/**
 * Handles the [aga_autocomplete] shortcode for rendering standalone autocomplete forms.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 * @since      5.1.0
 */

defined( 'ABSPATH' ) || exit;

class AGA_Shortcode {

    /**
     * Counter for generating unique instance IDs.
     *
     * @var int
     */
    private static $instance_count = 0;

    /**
     * Collected configs from all shortcode instances on the page.
     *
     * @var array
     */
    private static $shortcode_configs = array();

    /**
     * Whether the enqueue hooks have been registered.
     *
     * @var bool
     */
    private static $hooks_registered = false;

    /**
     * Register the shortcode.
     */
    public function __construct() {
        add_shortcode( 'aga_autocomplete', array( $this, 'render' ) );
    }

    /**
     * Render the [aga_autocomplete] shortcode.
     *
     * @param array|string $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render( $atts ) {
        $atts = shortcode_atts(
            array(
                'id'          => 0,
                'placeholder' => 'Start typing an address...',
                'label'       => '',
                'mode'        => 'single_line',
                'country'     => '',
                'show_map'    => 'false',
                'class'       => '',
            ),
            $atts,
            'aga_autocomplete'
        );

        self::$instance_count++;
        $instance_id = 'aga-sc-' . self::$instance_count;
        $form_id     = absint( $atts['id'] );

        // If a form config ID is provided, load it via the existing frontend mechanism.
        if ( $form_id ) {
            return $this->render_from_config( $form_id, $instance_id, $atts );
        }

        // Otherwise, build a standalone autocomplete form from shortcode attributes.
        return $this->render_standalone( $instance_id, $atts );
    }

    /**
     * Render the shortcode using an existing aga_form config.
     *
     * @param int    $form_id     The aga_form post ID.
     * @param string $instance_id Unique instance identifier.
     * @param array  $atts        Shortcode attributes.
     * @return string HTML output.
     */
    private function render_from_config( $form_id, $instance_id, $atts ) {
        $form_post = get_post( $form_id );
        if ( ! $form_post || 'aga_form' !== $form_post->post_type ) {
            return '<!-- AGA: Invalid form config ID -->';
        }

        $config = AGA_Autocomplete::get_js_config( $form_id );
        if ( empty( $config ) ) {
            return '<!-- AGA: Empty form config -->';
        }

        // Override the main_selector to point to our generated input.
        $input_selector = '#' . $instance_id . '-input';
        $config['main_selector'] = $input_selector;

        // Build the HTML.
        $mode       = ! empty( $config['mode'] ) ? $config['mode'] : 'single_line';
        $show_map   = ! empty( $config['map_picker'] );
        $wrapper_class = 'aga-shortcode-wrapper';
        if ( ! empty( $atts['class'] ) ) {
            $wrapper_class .= ' ' . sanitize_html_class( $atts['class'] );
        }

        $html = $this->build_html( $instance_id, $atts, $mode, $show_map, $wrapper_class );

        // Ensure map_picker is set if config has it enabled.
        if ( $show_map ) {
            $config['map_picker'] = true;
        }

        // Store the config and ensure scripts will be enqueued.
        self::$shortcode_configs[] = $config;
        $this->ensure_scripts_enqueued();

        return $html;
    }

    /**
     * Render a standalone shortcode (no existing form config).
     *
     * @param string $instance_id Unique instance identifier.
     * @param array  $atts        Shortcode attributes.
     * @return string HTML output.
     */
    private function render_standalone( $instance_id, $atts ) {
        $mode     = in_array( $atts['mode'], array( 'single_line', 'smart_mapping' ), true ) ? $atts['mode'] : 'single_line';
        $show_map = 'true' === strtolower( $atts['show_map'] );

        $wrapper_class = 'aga-shortcode-wrapper';
        if ( ! empty( $atts['class'] ) ) {
            $wrapper_class .= ' ' . sanitize_html_class( $atts['class'] );
        }

        $html = $this->build_html( $instance_id, $atts, $mode, $show_map, $wrapper_class );

        // Build the JS config object.
        $config = array(
            'form_id'                => $instance_id,
            'mode'                   => $mode,
            'main_selector'          => '#' . $instance_id . '-input',
            'selectors'              => array(),
            'component_restrictions' => array(),
            'map_picker'             => $show_map,
            'geolocation'            => false,
            'address_validation'     => false,
        );

        // Country restriction.
        if ( ! empty( $atts['country'] ) ) {
            $countries = array_map( 'trim', explode( ',', $atts['country'] ) );
            $countries = array_filter( $countries );
            if ( count( $countries ) === 1 ) {
                $config['component_restrictions']['country'] = $countries[0];
            } else {
                $config['component_restrictions']['country'] = $countries;
            }
        }

        // Smart mapping selectors pointing to the hidden fields.
        if ( 'smart_mapping' === $mode ) {
            $config['selectors'] = array(
                'street'   => '#' . $instance_id . '-street',
                'city'     => '#' . $instance_id . '-city',
                'state'    => '#' . $instance_id . '-state',
                'zip'      => '#' . $instance_id . '-zip',
                'country'  => '#' . $instance_id . '-country',
                'lat'      => '#' . $instance_id . '-lat',
                'lng'      => '#' . $instance_id . '-lng',
            );
            $config['formats'] = array(
                'state'   => 'long',
                'country' => 'short',
            );
        }

        // Store the config and ensure scripts will be enqueued.
        self::$shortcode_configs[] = $config;
        $this->ensure_scripts_enqueued();

        return $html;
    }

    /**
     * Build the HTML markup for the shortcode.
     *
     * @param string $instance_id   Unique instance identifier.
     * @param array  $atts          Shortcode attributes.
     * @param string $mode          Autocomplete mode.
     * @param bool   $show_map      Whether to show a map container.
     * @param string $wrapper_class CSS class for the wrapper.
     * @return string HTML markup.
     */
    private function build_html( $instance_id, $atts, $mode, $show_map, $wrapper_class ) {
        $html = '<div id="' . esc_attr( $instance_id ) . '" class="' . esc_attr( $wrapper_class ) . '">';

        // Label.
        if ( ! empty( $atts['label'] ) ) {
            $html .= '<label for="' . esc_attr( $instance_id ) . '-input" class="aga-shortcode-label">'
                    . esc_html( $atts['label'] )
                    . '</label>';
        }

        // Input wrapper (needed for dropdown positioning).
        $html .= '<div class="aga-shortcode-input-wrapper">';
        $html .= '<input type="text"'
                . ' id="' . esc_attr( $instance_id ) . '-input"'
                . ' class="aga-shortcode-input"'
                . ' placeholder="' . esc_attr( $atts['placeholder'] ) . '"'
                . ' autocomplete="off"'
                . ' />';
        $html .= '</div>';

        // Hidden fields for smart_mapping mode.
        if ( 'smart_mapping' === $mode ) {
            $hidden_fields = array( 'street', 'city', 'state', 'zip', 'country', 'lat', 'lng' );
            foreach ( $hidden_fields as $field ) {
                $html .= '<input type="hidden"'
                        . ' id="' . esc_attr( $instance_id ) . '-' . esc_attr( $field ) . '"'
                        . ' name="aga_' . esc_attr( $field ) . '"'
                        . ' class="aga-shortcode-hidden-field"'
                        . ' />';
            }
        }

        // Map container.
        if ( $show_map ) {
            $html .= '<div id="' . esc_attr( $instance_id ) . '-map" class="aga-map-picker-container aga-shortcode-map"></div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Register the enqueue hooks (once).
     */
    private function ensure_scripts_enqueued() {
        if ( self::$hooks_registered ) {
            return;
        }

        // Hook into the aga_form_configs filter to merge shortcode configs
        // with any configs from AGA_Frontend (automatic/page-specific forms).
        add_filter( 'aga_form_configs', array( __CLASS__, 'merge_shortcode_configs' ) );

        // Enqueue assets and localize data in wp_footer.
        // Priority 5 runs before AGA_Frontend's hooks (default priority 10).
        add_action( 'wp_footer', array( __CLASS__, 'enqueue_shortcode_assets' ), 5 );

        // Priority 15 runs after AGA_Frontend's enqueue hooks (priority 10)
        // but before wp_print_footer_scripts (priority 20). This localizes
        // shortcode configs only if AGA_Frontend did not already handle them.
        add_action( 'wp_footer', array( __CLASS__, 'localize_shortcode_configs' ), 15 );

        self::$hooks_registered = true;
    }

    /**
     * Merge shortcode configs into the aga_form_configs array via filter.
     * This is called by AGA_Frontend::localize_script_data() when it has forms to load.
     *
     * @param array $configs Existing form configs.
     * @return array Merged configs.
     */
    public static function merge_shortcode_configs( $configs ) {
        if ( ! empty( self::$shortcode_configs ) ) {
            $configs = array_merge( $configs, self::$shortcode_configs );
            // Mark configs as merged so localize fallback does not double-output.
            self::$shortcode_configs = array();
        }
        return $configs;
    }

    /**
     * Enqueue scripts and styles needed by the shortcode.
     * Fires in wp_footer at priority 5.
     */
    public static function enqueue_shortcode_assets() {
        // Enqueue the main frontend CSS.
        if ( ! wp_style_is( 'autocomplete-google-address', 'enqueued' ) ) {
            wp_enqueue_style(
                'autocomplete-google-address',
                AGA_PLUGIN_URL . 'public/css/frontend.css',
                array(),
                filemtime( AGA_PLUGIN_DIR . 'public/css/frontend.css' ),
                'all'
            );
        }

        // Enqueue Google Maps API if not already loaded.
        if ( ! wp_script_is( 'aga-google-maps', 'enqueued' ) && ! aga_get_setting( 'do_not_load_gmaps_api' ) ) {
            $api_key = aga_get_setting( 'api_key' );
            if ( ! empty( $api_key ) ) {
                $api_url = add_query_arg(
                    array(
                        'key'       => $api_key,
                        'libraries' => 'places',
                        'language'  => get_locale(),
                        'v'         => 'weekly',
                        'loading'   => 'async',
                    ),
                    'https://maps.googleapis.com/maps/api/js'
                );
                wp_enqueue_script( 'aga-google-maps', esc_url( $api_url ), array(), null, true );
            }
        }

        // Enqueue the main frontend JS.
        if ( ! wp_script_is( 'autocomplete-google-address', 'enqueued' ) ) {
            wp_enqueue_script(
                'autocomplete-google-address',
                AGA_PLUGIN_URL . 'public/js/frontend.js',
                array( 'jquery' ),
                filemtime( AGA_PLUGIN_DIR . 'public/js/frontend.js' ),
                true
            );

            // Localize the frontend data (ajax URL and nonce).
            wp_localize_script( 'autocomplete-google-address', 'aga_frontend_data', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'aga_frontend_nonce' ),
            ) );
        }
    }

    /**
     * Localize shortcode configs if they were not already merged via the
     * aga_form_configs filter (i.e. AGA_Frontend had no automatic forms).
     *
     * Fires in wp_footer at priority 15, after AGA_Frontend's enqueue (priority 10)
     * but before wp_print_footer_scripts (priority 20).
     */
    public static function localize_shortcode_configs() {
        if ( empty( self::$shortcode_configs ) ) {
            // Configs were already merged via the filter, nothing to do.
            return;
        }

        // AGA_Frontend did not run its localize, so we localize the configs ourselves.
        // Use the same handle that enqueue_shortcode_assets registered.
        wp_localize_script(
            'autocomplete-google-address',
            'aga_form_configs',
            array_values( self::$shortcode_configs )
        );

        // Clear after localizing.
        self::$shortcode_configs = array();
    }

    /**
     * Get the collected shortcode configs (for testing/external access).
     *
     * @return array
     */
    public static function get_shortcode_configs() {
        return self::$shortcode_configs;
    }
}
