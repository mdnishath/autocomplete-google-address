<?php
/**
 * Provides the admin area view for the help page with diagnostics dashboard.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/admin/views
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap aga-help-page">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <!-- Quick Links -->
    <div class="aga-quick-links">
        <a href="https://wordpress.org/plugins/autocomplete-google-address/#description" target="_blank" rel="noopener noreferrer" class="aga-quick-link">
            <span class="dashicons dashicons-media-document"></span>
            <?php esc_html_e( 'Documentation', 'autocomplete-google-address' ); ?>
        </a>
        <a href="https://wa.me/8801767591988?text=<?php echo rawurlencode( 'Hi, I need help with the Autocomplete Google Address plugin.' ); ?>" target="_blank" rel="noopener noreferrer" class="aga-quick-link">
            <span class="dashicons dashicons-format-chat"></span>
            <?php esc_html_e( 'WhatsApp Support', 'autocomplete-google-address' ); ?>
        </a>
        <a href="https://wordpress.org/support/plugin/autocomplete-google-address/reviews/#new-post" target="_blank" rel="noopener noreferrer" class="aga-quick-link">
            <span class="dashicons dashicons-star-filled"></span>
            <?php esc_html_e( 'Leave a Review', 'autocomplete-google-address' ); ?>
        </a>
    </div>

    <div class="aga-help-columns">
        <!-- Main content -->
        <div>
            <!-- Diagnostics -->
            <div class="aga-card">
                <div class="aga-card-header">
                    <h2>
                        <span class="dashicons dashicons-heart"></span>
                        <?php esc_html_e( 'Diagnostics', 'autocomplete-google-address' ); ?>
                    </h2>
                    <button type="button" id="aga-run-diagnostics" class="button button-primary aga-run-diagnostics-btn">
                        <span class="dashicons dashicons-update aga-icon-inline"></span>
                        <?php esc_html_e( 'Run Diagnostics', 'autocomplete-google-address' ); ?>
                    </button>
                </div>
                <div class="aga-card-body">
                    <div id="aga-health-results">
                        <p class="aga-text-center aga-text-muted"><?php esc_html_e( 'Click "Run Diagnostics" to check your plugin configuration.', 'autocomplete-google-address' ); ?></p>
                    </div>
                </div>
            </div>

            <!-- How to Use -->
            <div class="aga-card">
                <div class="aga-card-header">
                    <h2><?php esc_html_e( 'How to Use', 'autocomplete-google-address' ); ?></h2>
                </div>
                <div class="aga-card-body">
                    <h3><?php esc_html_e( '1. Configure your Settings', 'autocomplete-google-address' ); ?></h3>
                    <p><?php echo wp_kses_post( __( 'Go to <strong>Google Address &raquo; Settings</strong> and enter your Google Maps API key. This is required for the plugin to work.', 'autocomplete-google-address' ) ); ?></p>

                    <h3><?php esc_html_e( '2. Create a New Form Configuration', 'autocomplete-google-address' ); ?></h3>
                    <p><?php echo wp_kses_post( __( 'Go to <strong>Google Address &raquo; Add New</strong>. Give your configuration a name and choose a mode (Single Line or Smart Mapping).', 'autocomplete-google-address' ) ); ?></p>

                    <h3><?php esc_html_e( '3. Find Your Field Selectors', 'autocomplete-google-address' ); ?></h3>
                    <p><?php esc_html_e( 'Go to the page on your site with the form you want to add autocomplete to. Right-click on the form field and choose "Inspect" from the browser menu. Find the id or class of the input field.', 'autocomplete-google-address' ); ?></p>

                    <h3><?php esc_html_e( '4. Configure the Mapping', 'autocomplete-google-address' ); ?></h3>
                    <p><?php esc_html_e( 'Copy the selector and paste it into the "Main Autocomplete Field Selector" field in your configuration. If you are using Smart Mapping, do the same for all the other address fields.', 'autocomplete-google-address' ); ?></p>

                    <h3><?php esc_html_e( '5. Add the Shortcode to Your Page', 'autocomplete-google-address' ); ?></h3>
                    <p><?php esc_html_e( 'After saving your configuration, a shortcode will be generated (e.g., [aga_form id="123"]). Copy this shortcode and paste it into the content of the page where your form is located.', 'autocomplete-google-address' ); ?></p>

                    <h3><?php esc_html_e( 'For Developers', 'autocomplete-google-address' ); ?></h3>
                    <p><?php esc_html_e( 'You can also use the PHP function aga_render_form_config( $id ) in your theme templates to load a configuration programmatically.', 'autocomplete-google-address' ); ?></p>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="aga-card">
                <div class="aga-card-header">
                    <h3><?php esc_html_e( 'Plugin Resources', 'autocomplete-google-address' ); ?></h3>
                </div>
                <div class="aga-card-body">
                    <ul>
                        <li>
                            <a href="https://wordpress.org/plugins/autocomplete-google-address/#description" target="_blank" rel="noopener noreferrer">
                                <span class="dashicons dashicons-media-document"></span>
                                <?php esc_html_e( 'Documentation', 'autocomplete-google-address' ); ?>
                            </a>
                        </li>
                        <li>
                            <a href="https://wa.me/8801767591988?text=<?php echo rawurlencode( 'Hi, I need help with the Autocomplete Google Address plugin.' ); ?>" target="_blank" rel="noopener noreferrer">
                                <span class="dashicons dashicons-format-chat"></span>
                                <?php esc_html_e( 'WhatsApp Support', 'autocomplete-google-address' ); ?>
                            </a>
                        </li>
                        <li>
                            <a href="https://wordpress.org/support/plugin/autocomplete-google-address/reviews/#new-post" target="_blank" rel="noopener noreferrer">
                                <span class="dashicons dashicons-star-filled"></span>
                                <?php esc_html_e( 'Rate this Plugin', 'autocomplete-google-address' ); ?>
                            </a>
                        </li>
                        <li>
                            <a href="https://wordpress.org/support/plugin/autocomplete-google-address/" target="_blank" rel="noopener noreferrer">
                                <span class="dashicons dashicons-sos"></span>
                                <?php esc_html_e( 'Support Forum', 'autocomplete-google-address' ); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="aga-card">
                <div class="aga-card-header">
                    <h3><?php esc_html_e( 'System Info', 'autocomplete-google-address' ); ?></h3>
                </div>
                <div class="aga-card-body">
                    <p><strong>PHP:</strong> <?php echo esc_html( phpversion() ); ?></p>
                    <p><strong>WordPress:</strong> <?php echo esc_html( get_bloginfo( 'version' ) ); ?></p>
                    <p><strong>Plugin:</strong> <?php echo esc_html( defined( 'AGA_VERSION' ) ? AGA_VERSION : '—' ); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function($) {
    var $btn      = $('#aga-run-diagnostics');
    var $results  = $('#aga-health-results');

    $btn.on('click', function() {
        $btn.prop('disabled', true);
        $results.html(
            '<div class="aga-health-loading">' +
                '<span class="spinner"></span> <?php echo esc_js( __( 'Running diagnostics...', 'autocomplete-google-address' ) ); ?>' +
            '</div>'
        );

        $.post(aga_admin_data.ajax_url, {
            action: 'aga_health_check',
            nonce:  aga_admin_data.nonce
        }, function(response) {
            $btn.prop('disabled', false);

            if ( ! response.success || ! response.data ) {
                $results.html('<p style="color:#d63638;">Diagnostics failed. Please try again.</p>');
                return;
            }

            var html = '<table class="aga-health-table">';
            html += '<thead><tr><th style="width:40px;"><?php echo esc_js( __( 'Status', 'autocomplete-google-address' ) ); ?></th><th><?php echo esc_js( __( 'Check', 'autocomplete-google-address' ) ); ?></th><th><?php echo esc_js( __( 'Details', 'autocomplete-google-address' ) ); ?></th></tr></thead>';
            html += '<tbody>';

            $.each(response.data, function(i, item) {
                var icon = '';
                if ( item.status === 'success' ) {
                    icon = '<span class="aga-status-icon aga-status-success">&#10003;</span>';
                } else if ( item.status === 'error' ) {
                    icon = '<span class="aga-status-icon aga-status-error">&#10007;</span>';
                } else {
                    icon = '<span class="aga-status-icon aga-status-warning">!</span>';
                }

                html += '<tr>';
                html += '<td>' + icon + '</td>';
                html += '<td class="aga-health-name">' + $('<span>').text(item.name).html() + '</td>';
                html += '<td class="aga-health-details">' + $('<span>').text(item.details).html() + '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            $results.html(html);
        }).fail(function() {
            $btn.prop('disabled', false);
            $results.html('<p style="color:#d63638;">Diagnostics request failed. Please check your connection and try again.</p>');
        });
    });
})(jQuery);
</script>
