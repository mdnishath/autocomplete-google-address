<?php
/**
 * Elementor Address Autocomplete Widget.
 *
 * A standalone widget that provides Google Places address autocomplete
 * directly in the Elementor editor without requiring a pre-built form config.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes/widgets
 * @since      5.2.0
 */

defined( 'ABSPATH' ) || exit;

class AGA_Elementor_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * @return string
     */
    public function get_name() {
        return 'aga_address_autocomplete';
    }

    /**
     * Get widget title.
     *
     * @return string
     */
    public function get_title() {
        return esc_html__( 'Address Autocomplete', 'autocomplete-google-address' );
    }

    /**
     * Get widget icon.
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-map-pin';
    }

    /**
     * Get widget categories.
     *
     * @return array
     */
    public function get_categories() {
        return array( 'google-address' );
    }

    /**
     * Get widget keywords.
     *
     * @return array
     */
    public function get_keywords() {
        return array( 'address', 'autocomplete', 'google', 'places', 'map', 'location' );
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    /**
     * Register content tab controls.
     */
    private function register_content_controls() {
        $is_paying = function_exists( 'google_autocomplete' ) && google_autocomplete()->is_paying();

        // --- Section: General ---
        $this->start_controls_section(
            'section_general',
            array(
                'label' => esc_html__( 'General', 'autocomplete-google-address' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'placeholder',
            array(
                'label'   => esc_html__( 'Placeholder', 'autocomplete-google-address' ),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Start typing an address...', 'autocomplete-google-address' ),
            )
        );

        $mode_options = array(
            'single_line' => esc_html__( 'Single Line', 'autocomplete-google-address' ),
        );
        if ( $is_paying ) {
            $mode_options['smart_mapping'] = esc_html__( 'Smart Mapping', 'autocomplete-google-address' );
        } else {
            $mode_options['smart_mapping'] = esc_html__( 'Smart Mapping (Pro)', 'autocomplete-google-address' );
        }

        $this->add_control(
            'mode',
            array(
                'label'   => esc_html__( 'Mode', 'autocomplete-google-address' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'single_line',
                'options' => $mode_options,
            )
        );

        $this->end_controls_section();

        // --- Section: Restrictions (Pro) ---
        $this->start_controls_section(
            'section_restrictions',
            array(
                'label' => esc_html__( 'Restrictions', 'autocomplete-google-address' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $countries = aga_get_countries();
        $country_options = array( '' => esc_html__( 'All Countries', 'autocomplete-google-address' ) );
        foreach ( $countries as $code => $name ) {
            $country_options[ strtolower( $code ) ] = $name;
        }

        $this->add_control(
            'country_restriction',
            array(
                'label'       => $is_paying
                    ? esc_html__( 'Country Restriction', 'autocomplete-google-address' )
                    : esc_html__( 'Country Restriction (Pro)', 'autocomplete-google-address' ),
                'description' => esc_html__( 'Limit results to up to 5 countries.', 'autocomplete-google-address' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'options'     => $country_options,
                'default'     => array(),
            )
        );

        $languages = aga_get_languages();
        $language_options = array( '' => esc_html__( 'Default (auto-detect)', 'autocomplete-google-address' ) );
        foreach ( $languages as $code => $name ) {
            $language_options[ $code ] = $name;
        }

        $this->add_control(
            'language',
            array(
                'label'   => $is_paying
                    ? esc_html__( 'Language', 'autocomplete-google-address' )
                    : esc_html__( 'Language (Pro)', 'autocomplete-google-address' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $language_options,
            )
        );

        $place_type_options = array(
            ''              => esc_html__( 'All Types', 'autocomplete-google-address' ),
            'address'       => esc_html__( 'Address', 'autocomplete-google-address' ),
            'geocode'       => esc_html__( 'Geocode', 'autocomplete-google-address' ),
            'establishment' => esc_html__( 'Establishment', 'autocomplete-google-address' ),
            '(regions)'     => esc_html__( 'Regions', 'autocomplete-google-address' ),
            '(cities)'      => esc_html__( 'Cities', 'autocomplete-google-address' ),
        );

        $this->add_control(
            'place_types',
            array(
                'label'   => $is_paying
                    ? esc_html__( 'Place Types', 'autocomplete-google-address' )
                    : esc_html__( 'Place Types (Pro)', 'autocomplete-google-address' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $place_type_options,
            )
        );

        $this->end_controls_section();

        // --- Section: Features (Pro) ---
        $this->start_controls_section(
            'section_features',
            array(
                'label' => esc_html__( 'Features', 'autocomplete-google-address' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_map',
            array(
                'label'        => $is_paying
                    ? esc_html__( 'Map Picker', 'autocomplete-google-address' )
                    : esc_html__( 'Map Picker (Pro)', 'autocomplete-google-address' ),
                'description'  => esc_html__( 'Show an interactive map below the input. Users can click or drag the pin to pick an address.', 'autocomplete-google-address' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'autocomplete-google-address' ),
                'label_off'    => esc_html__( 'No', 'autocomplete-google-address' ),
                'return_value' => 'yes',
                'default'      => '',
            )
        );

        $this->add_control(
            'show_geolocation',
            array(
                'label'        => $is_paying
                    ? esc_html__( 'Geolocation Auto-detect', 'autocomplete-google-address' )
                    : esc_html__( 'Geolocation Auto-detect (Pro)', 'autocomplete-google-address' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'autocomplete-google-address' ),
                'label_off'    => esc_html__( 'No', 'autocomplete-google-address' ),
                'return_value' => 'yes',
                'default'      => '',
            )
        );

        $this->add_control(
            'show_address_validation',
            array(
                'label'        => $is_paying
                    ? esc_html__( 'Address Validation', 'autocomplete-google-address' )
                    : esc_html__( 'Address Validation (Pro)', 'autocomplete-google-address' ),
                'description'  => esc_html__( 'Verify addresses with a visual badge (Verified / Partial / Not verified).', 'autocomplete-google-address' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'autocomplete-google-address' ),
                'label_off'    => esc_html__( 'No', 'autocomplete-google-address' ),
                'return_value' => 'yes',
                'default'      => '',
            )
        );

        $this->add_control(
            'show_saved_addresses',
            array(
                'label'        => $is_paying
                    ? esc_html__( 'Saved Addresses', 'autocomplete-google-address' )
                    : esc_html__( 'Saved Addresses (Pro)', 'autocomplete-google-address' ),
                'description'  => esc_html__( 'Show recently used addresses for logged-in users.', 'autocomplete-google-address' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'autocomplete-google-address' ),
                'label_off'    => esc_html__( 'No', 'autocomplete-google-address' ),
                'return_value' => 'yes',
                'default'      => '',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Register style tab controls.
     */
    private function register_style_controls() {

        $this->start_controls_section(
            'section_input_style',
            array(
                'label' => esc_html__( 'Input Field', 'autocomplete-google-address' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'input_width',
            array(
                'label'      => esc_html__( 'Width', 'autocomplete-google-address' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( '%', 'px' ),
                'range'      => array(
                    '%'  => array( 'min' => 10, 'max' => 100 ),
                    'px' => array( 'min' => 100, 'max' => 1200 ),
                ),
                'default'    => array(
                    'unit' => '%',
                    'size' => 100,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .aga-elementor-input' => 'width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'input_padding',
            array(
                'label'      => esc_html__( 'Padding', 'autocomplete-google-address' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em' ),
                'selectors'  => array(
                    '{{WRAPPER}} .aga-elementor-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'input_border_radius',
            array(
                'label'      => esc_html__( 'Border Radius', 'autocomplete-google-address' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .aga-elementor-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'input_typography',
                'selector' => '{{WRAPPER}} .aga-elementor-input',
            )
        );

        $this->add_control(
            'input_text_color',
            array(
                'label'     => esc_html__( 'Text Color', 'autocomplete-google-address' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .aga-elementor-input' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'input_bg_color',
            array(
                'label'     => esc_html__( 'Background Color', 'autocomplete-google-address' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .aga-elementor-input' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'input_border_color',
            array(
                'label'     => esc_html__( 'Border Color', 'autocomplete-google-address' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .aga-elementor-input' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'input_focus_border_color',
            array(
                'label'     => esc_html__( 'Focus Border Color', 'autocomplete-google-address' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .aga-elementor-input:focus' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     */
    protected function render() {
        $settings  = $this->get_settings_for_display();
        $is_paying = function_exists( 'google_autocomplete' ) && google_autocomplete()->is_paying();
        $widget_id = $this->get_id();
        $instance_id = 'aga-el-' . $widget_id;

        // Determine effective mode — fall back to single_line for free users.
        $mode = $settings['mode'];
        if ( 'smart_mapping' === $mode && ! $is_paying ) {
            $mode = 'single_line';
        }

        $show_map              = $is_paying && 'yes' === ( $settings['show_map'] ?? '' );
        $show_geolocation      = $is_paying && 'yes' === ( $settings['show_geolocation'] ?? '' );
        $show_validation       = $is_paying && 'yes' === ( $settings['show_address_validation'] ?? '' );
        $show_saved_addresses  = $is_paying && 'yes' === ( $settings['show_saved_addresses'] ?? '' );

        // Build HTML.
        ?>
        <div id="<?php echo esc_attr( $instance_id ); ?>" class="aga-elementor-wrapper">
            <div class="aga-elementor-input-wrapper">
                <input
                    type="text"
                    id="<?php echo esc_attr( $instance_id ); ?>-input"
                    class="aga-elementor-input"
                    placeholder="<?php echo esc_attr( $settings['placeholder'] ); ?>"
                    autocomplete="off"
                />
            </div>
            <?php if ( 'smart_mapping' === $mode ) : ?>
                <?php
                $hidden_fields = array( 'street', 'city', 'state', 'zip', 'country', 'lat', 'lng' );
                foreach ( $hidden_fields as $field ) :
                ?>
                    <input
                        type="hidden"
                        id="<?php echo esc_attr( $instance_id ); ?>-<?php echo esc_attr( $field ); ?>"
                        name="aga_<?php echo esc_attr( $field ); ?>"
                        class="aga-elementor-hidden-field"
                    />
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if ( $show_map ) : ?>
                <div id="<?php echo esc_attr( $instance_id ); ?>-map" class="aga-map-picker-container aga-elementor-map"></div>
            <?php endif; ?>
        </div>
        <?php

        // Build JS config matching the aga_form_configs format.
        $config = array(
            'form_id'                => $instance_id,
            'mode'                   => $mode,
            'main_selector'          => '#' . $instance_id . '-input',
            'language'               => '',
            'selectors'              => array(),
            'component_restrictions' => array(),
            'place_types'            => '',
            'map_picker'             => $show_map,
            'geolocation'            => $show_geolocation,
            'address_validation'     => $show_validation,
            'saved_addresses'        => $show_saved_addresses,
        );

        // Smart mapping selectors.
        if ( 'smart_mapping' === $mode ) {
            $config['selectors'] = array(
                'street'  => '#' . $instance_id . '-street',
                'city'    => '#' . $instance_id . '-city',
                'state'   => '#' . $instance_id . '-state',
                'zip'     => '#' . $instance_id . '-zip',
                'country' => '#' . $instance_id . '-country',
                'lat'     => '#' . $instance_id . '-lat',
                'lng'     => '#' . $instance_id . '-lng',
            );
            $config['formats'] = array(
                'state'   => 'long',
                'country' => 'short',
            );
        }

        // Pro features — country restriction.
        if ( $is_paying && ! empty( $settings['country_restriction'] ) ) {
            $countries = array_filter( $settings['country_restriction'] );
            $countries = array_slice( $countries, 0, 5 ); // Google supports max 5.
            if ( count( $countries ) === 1 ) {
                $config['component_restrictions']['country'] = $countries[0];
            } elseif ( ! empty( $countries ) ) {
                $config['component_restrictions']['country'] = array_values( $countries );
            }
        }

        // Pro features — language.
        if ( $is_paying && ! empty( $settings['language'] ) ) {
            $config['language'] = $settings['language'];
        }

        // Pro features — place types.
        if ( $is_paying && ! empty( $settings['place_types'] ) ) {
            $config['place_types'] = $settings['place_types'];
        }

        // Skip inline scripts in Elementor editor to prevent preview crashes.
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode()
                  || \Elementor\Plugin::$instance->preview->is_preview_mode();

        if ( ! $is_editor ) :
        ?>
        <script>
            (function() {
                var cfg = <?php echo wp_json_encode( $config ); ?>;
                function agaPushConfig() {
                    window.aga_form_configs = window.aga_form_configs || [];
                    window.aga_form_configs.push(cfg);
                    if (typeof window.aga_reinit === 'function') {
                        window.aga_reinit();
                    }
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        setTimeout(agaPushConfig, 100);
                    });
                } else {
                    setTimeout(agaPushConfig, 100);
                }
            })();
        </script>
        <?php
        endif;
    }
}
