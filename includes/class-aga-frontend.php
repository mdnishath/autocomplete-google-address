<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://profiles.wordpress.org/nishatbd31/
 * @since      1.0.0
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/public
 */

defined( 'ABSPATH' ) || exit;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/public
 * @author     Md Nishath Khandakar <https://profiles.wordpress.org/nishatbd31/>
 */
class AGA_Frontend {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * A list of form config IDs to be loaded on the current page.
     *
     * @since    1.0.0
     * @access   private
     * @var      array
     */
    private $forms_to_load = array();

    /**
     * A flag to ensure Google Maps API is enqueued only once.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean
     */
    private static $gmaps_enqueued = false;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$should_load = apply_filters( 'aga_should_load_frontend', ! empty( $this->forms_to_load ) );
		if ( ! $should_load ) {
            return;
        }

		wp_enqueue_style( $this->plugin_name, AGA_PLUGIN_URL . 'public/css/frontend.css', array(), filemtime( AGA_PLUGIN_DIR . 'public/css/frontend.css' ), 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$should_load = apply_filters( 'aga_should_load_frontend', ! empty( $this->forms_to_load ) );
		if ( ! $should_load ) {
            return;
        }

        $this->enqueue_google_maps_api();

		wp_enqueue_script( $this->plugin_name, AGA_PLUGIN_URL . 'public/js/frontend.js', array( 'jquery' ), filemtime( AGA_PLUGIN_DIR . 'public/js/frontend.js' ), true );

        $this->localize_script_data();
	}
    
    /**
     * Handles the [aga_form] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Empty string. The shortcode's purpose is to enqueue scripts.
     */
    public function render_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'aga_form'
        );

        $id = absint( $atts['id'] );
        if ( $id ) {
            $this->prepare_scripts_for_form( $id );
        }

        return ''; // The shortcode itself outputs nothing.
    }
    
    /**
     * Adds a form ID to the list of forms to be loaded on the page.
     *
     * @param int $form_id The ID of the form config post.
     */
    public function prepare_scripts_for_form( $form_id ) {
        if ( ! in_array( $form_id, $this->forms_to_load, true ) ) {
            $this->forms_to_load[] = $form_id;
        }
    }

    /**
     * Adds the Google Maps bootstrap loader script to the footer.
     * This is the recommended way to load the API asynchronously.
     */
    private function enqueue_google_maps_api() {
        if ( self::$gmaps_enqueued || aga_get_setting( 'do_not_load_gmaps_api' ) ) {
            return;
        }

        $api_key = aga_get_setting( 'api_key' );
        if ( empty( $api_key ) ) {
            return;
        }
        
        $language = '';
        if ( ! empty( $this->forms_to_load ) ) {
            foreach ( $this->forms_to_load as $form_id ) {
                $lang_override = get_post_meta( $form_id, 'Nish_aga_language_override', true );
                if ( ! empty( $lang_override ) ) {
                    $language = $lang_override;
                    break;
                }
            }
        }
        
        if ( empty( $language ) ) {
            $language = get_locale();
        }

        $api_url = add_query_arg(
            array(
                'key'       => $api_key,
                'libraries' => 'places',
                'language'  => $language,
                'v'         => 'weekly',
                'loading'   => 'async',
            ),
            'https://maps.googleapis.com/maps/api/js'
        );

        wp_enqueue_script( 'aga-google-maps', esc_url( $api_url ), array(), null, true );
        
        self::$gmaps_enqueued = true;
    }
    
    /**
     * Localizes the form configuration data for the frontend script.
     */
    private function localize_script_data() {
        $configs = array();
        
        // Remove duplicates and ensure we have valid IDs.
        $this->forms_to_load = array_unique( array_map( 'absint', $this->forms_to_load ) );
        
        foreach ( $this->forms_to_load as $form_id ) {
            $form_post = get_post( $form_id );
            if ( $form_post && 'aga_form' === $form_post->post_type ) {
                $configs[] = AGA_Autocomplete::get_js_config( $form_id );
            }
        }

        // Allow other modules (e.g., WooCommerce) to inject configs.
        $configs = apply_filters( 'aga_form_configs', $configs );

        wp_localize_script( $this->plugin_name, 'aga_form_configs', $configs );

        $settings = get_option( 'Nish_aga_settings' );
        $is_paying = function_exists( 'google_autocomplete' ) && google_autocomplete()->is_paying();

        $frontend_data = array(
            'ajax_url'     => admin_url( 'admin-ajax.php' ),
            'nonce'        => wp_create_nonce( 'aga_frontend_nonce' ),
            'is_logged_in' => is_user_logged_in(),
        );

        // Pass custom attribution text if set (Pro)
        if ( $is_paying && ! empty( $settings['attribution_text'] ) ) {
            $frontend_data['attribution_text'] = sanitize_text_field( $settings['attribution_text'] );
        }

        // White label flag.
        $frontend_data['white_label'] = aga_get_setting( 'white_label' ) === '1';

        // Checkout abandonment tracking flag.
        $frontend_data['track_abandonment'] = aga_get_setting( 'track_abandonment' ) === '1';

        wp_localize_script( $this->plugin_name, 'aga_frontend_data', $frontend_data );
    }

    /**
     * Finds and prepares any globally or page-specifically activated forms.
     * This runs on every page load on the frontend to check for configs that should be active.
     *
     * @since 1.1.0
     */
    public function load_automatic_forms() {
        // Single query: get all forms that are either global or page-specific.
        $all_forms = get_posts( array(
            'post_type'      => 'aga_form',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'   => 'Nish_aga_activate_globally',
                    'value' => '1',
                ),
                array(
                    'key'     => 'Nish_aga_load_on_pages',
                    'compare' => 'EXISTS',
                ),
            ),
            'fields' => 'ids',
        ) );

        if ( empty( $all_forms ) ) {
            return;
        }

        // Filter in PHP: keep global forms and page-specific forms matching the current page.
        $forms_to_load   = array();
        $current_page_id = get_queried_object_id();

        foreach ( $all_forms as $form_id ) {
            $global = get_post_meta( $form_id, 'Nish_aga_activate_globally', true );
            if ( '1' === $global ) {
                $forms_to_load[] = $form_id;
                continue;
            }

            if ( $current_page_id ) {
                $pages = get_post_meta( $form_id, 'Nish_aga_load_on_pages', true );
                if ( is_array( $pages ) && in_array( $current_page_id, $pages, true ) ) {
                    $forms_to_load[] = $form_id;
                }
            }
        }

        foreach ( $forms_to_load as $form_id ) {
            $this->prepare_scripts_for_form( $form_id );
        }
    }
    /**
     * Outputs custom CSS styles for the autocomplete dropdown.
     * Only outputs when the user is on a Pro plan.
     *
     * @since 1.2.0
     */
    public function output_custom_styles() {
        if ( ! function_exists( 'google_autocomplete' ) || ! google_autocomplete()->is_paying() ) {
            return;
        }

        $options = get_option( 'Nish_aga_settings' );

        $bg_color      = ! empty( $options['dropdown_bg_color'] ) ? $options['dropdown_bg_color'] : '#ffffff';
        $text_color    = ! empty( $options['dropdown_text_color'] ) ? $options['dropdown_text_color'] : '#333333';
        $hover_color   = ! empty( $options['dropdown_hover_color'] ) ? $options['dropdown_hover_color'] : '#f0f0f0';
        $border_color  = ! empty( $options['dropdown_border_color'] ) ? $options['dropdown_border_color'] : '#dddddd';
        $border_radius = isset( $options['dropdown_border_radius'] ) && '' !== $options['dropdown_border_radius'] ? absint( $options['dropdown_border_radius'] ) : 4;
        $font_size     = isset( $options['dropdown_font_size'] ) && '' !== $options['dropdown_font_size'] ? absint( $options['dropdown_font_size'] ) : 14;
        $max_height    = isset( $options['dropdown_max_height'] ) && '' !== $options['dropdown_max_height'] ? absint( $options['dropdown_max_height'] ) : 250;

        ?>
        <style id="aga-custom-dropdown-styles">
            .aga-autocomplete-dropdown {
                background-color: <?php echo esc_attr( $bg_color ); ?> !important;
                border-color: <?php echo esc_attr( $border_color ); ?> !important;
                border-radius: <?php echo esc_attr( $border_radius ); ?>px !important;
                max-height: <?php echo esc_attr( $max_height ); ?>px !important;
                font-size: <?php echo esc_attr( $font_size ); ?>px !important;
            }
            .aga-autocomplete-dropdown .aga-autocomplete-item {
                color: <?php echo esc_attr( $text_color ); ?> !important;
                font-size: <?php echo esc_attr( $font_size ); ?>px !important;
                border-bottom-color: <?php echo esc_attr( $border_color ); ?> !important;
            }
            .aga-autocomplete-dropdown .aga-autocomplete-item:hover,
            .aga-autocomplete-dropdown .aga-autocomplete-item--active {
                background-color: <?php echo esc_attr( $hover_color ); ?> !important;
            }
            <?php
            $attr_text_color = ! empty( $options['attribution_text_color'] ) ? $options['attribution_text_color'] : '';
            $attr_bg_color   = ! empty( $options['attribution_bg_color'] ) ? $options['attribution_bg_color'] : '';
            $attr_font_size  = isset( $options['attribution_font_size'] ) && '' !== $options['attribution_font_size'] ? absint( $options['attribution_font_size'] ) : 0;
            if ( $attr_text_color || $attr_bg_color || $attr_font_size ) : ?>
            .aga-autocomplete-attribution {
                <?php if ( $attr_text_color ) : ?>color: <?php echo esc_attr( $attr_text_color ); ?> !important;<?php endif; ?>
                <?php if ( $attr_bg_color ) : ?>background-color: <?php echo esc_attr( $attr_bg_color ); ?> !important;<?php endif; ?>
                <?php if ( $attr_font_size ) : ?>font-size: <?php echo esc_attr( $attr_font_size ); ?>px !important;<?php endif; ?>
            }
            <?php endif; ?>
        </style>
        <?php
    }

    public function add_async_attribute( $tag, $handle ) {
        if ( 'aga-google-maps' !== $handle ) {
            return $tag;
        }
        return str_replace( ' src', ' async src', $tag );
    }

}
