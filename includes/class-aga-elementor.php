<?php
/**
 * Elementor integration loader.
 *
 * Checks if Elementor is active, registers a custom widget category,
 * and registers all AGA Elementor widgets.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 * @since      5.2.0
 */

defined( 'ABSPATH' ) || exit;

class AGA_Elementor {

    /**
     * Constructor — hook into Elementor if it is active.
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
     * Initialize Elementor integration after all plugins are loaded.
     */
    public function init() {
        // Bail if Elementor is not active.
        if ( ! did_action( 'elementor/loaded' ) ) {
            return;
        }

        // Register custom widget category.
        add_action( 'elementor/elements/categories_registered', array( $this, 'register_categories' ) );

        // Register widgets.
        add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );

        // Register Elementor Pro form field (if Elementor Pro is active).
        add_action( 'elementor_pro/forms/fields/register', array( $this, 'register_form_fields' ) );

        // Ensure frontend assets are available when Elementor renders widgets.
        add_action( 'elementor/frontend/after_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

        // Add editor JS to handle our custom field type in the editor preview.
        add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ) );
    }

    /**
     * Register the "Google Address" widget category.
     *
     * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
     */
    public function register_categories( $elements_manager ) {
        $elements_manager->add_category(
            'google-address',
            array(
                'title' => esc_html__( 'Google Address', 'autocomplete-google-address' ),
                'icon'  => 'eicon-map-pin',
            )
        );
    }

    /**
     * Register AGA widgets with Elementor.
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
     */
    public function register_widgets( $widgets_manager ) {
        require_once AGA_PLUGIN_DIR . 'includes/widgets/class-aga-elementor-widget.php';

        $widgets_manager->register( new AGA_Elementor_Widget() );
    }

    /**
     * Register the Address Autocomplete form field for Elementor Pro Forms.
     *
     * @param \ElementorPro\Modules\Forms\Registrars\Form_Fields_Registrar $registrar
     */
    public function register_form_fields( $registrar ) {
        require_once AGA_PLUGIN_DIR . 'includes/widgets/class-aga-elementor-form-field.php';

        $registrar->register( new AGA_Elementor_Form_Field() );
    }

    /**
     * Enqueue frontend scripts and styles for the Elementor editor and preview.
     */
    public function enqueue_frontend_assets() {
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

            wp_localize_script( 'autocomplete-google-address', 'aga_frontend_data', array(
                'ajax_url'     => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce( 'aga_frontend_nonce' ),
                'is_logged_in' => is_user_logged_in(),
            ) );
        }
    }

    /**
     * Enqueue editor-only JS that tells Elementor how to render our
     * custom form field type in the editor preview.
     */
    public function enqueue_editor_scripts() {
        wp_add_inline_script( 'elementor-editor', $this->get_editor_inline_js() );
    }

    /**
     * JS that hooks into Elementor's editor to make our aga_address field
     * type render as a text input in the live preview.
     */
    private function get_editor_inline_js() {
        return "
        (function() {
            function registerAGAField() {
                if (typeof elementor === 'undefined' || !elementor.hooks) {
                    setTimeout(registerAGAField, 200);
                    return;
                }
                elementor.hooks.addFilter('elementor_pro/forms/content_template/field/aga_address', function(inputField, item, i, settings) {
                    var placeholder = _.escape(item.placeholder) || 'Start typing an address...';
                    var inputSize = settings.input_size || 'sm';
                    var itemClasses = _.escape(item.css_classes) || '';
                    var required = item.required ? 'required' : '';
                    var mode = item.aga_mode || 'single_line';

                    var html = '<input size=\"1\" type=\"text\" class=\"elementor-field elementor-field-textual elementor-size-' + inputSize + ' ' + itemClasses + '\" name=\"form_field_' + i + '\" id=\"form_field_' + i + '\" ' + required + ' placeholder=\"' + placeholder + '\" autocomplete=\"off\">';

                    if (mode === 'smart_mapping') {
                        var fields = item.aga_smart_fields;
                        if (!fields || !fields.length) fields = ['city','state','zip','country'];
                        var layout = item.aga_sub_layout || 'half';
                        var colMap = {full:'elementor-col-100',half:'elementor-col-50',third:'elementor-col-33'};
                        var colClass = colMap[layout] || 'elementor-col-50';
                        var labels = {street:'Street',city:'City',state:'State / Region',zip:'Zip / Postal Code',country:'Country'};

                        html += '<div style=\"display:flex;flex-wrap:wrap;width:100%;margin-top:10px;\">';
                        for (var j = 0; j < fields.length; j++) {
                            var sf = fields[j];
                            var label = labels[sf] || sf;
                            html += '<div class=\"elementor-field-group elementor-column ' + colClass + '\" style=\"padding:0 5px;box-sizing:border-box;margin-bottom:10px;\">';
                            html += '<label class=\"elementor-field-label\">' + label + '</label>';
                            html += '<input type=\"text\" class=\"elementor-field elementor-field-textual elementor-size-' + inputSize + '\" placeholder=\"' + label + '\">';
                            html += '</div>';
                        }
                        html += '</div>';
                    }

                    return html;
                }, 10, 4);
            }
            registerAGAField();
        })();
        ";
    }
}
