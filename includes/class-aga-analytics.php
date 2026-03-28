<?php
/**
 * Handles usage analytics tracking and reporting (Pro feature).
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 * @since      5.1.0
 */

defined( 'ABSPATH' ) || exit;

class AGA_Analytics {

    /**
     * The database table name (without prefix).
     *
     * @var string
     */
    const TABLE_NAME = 'aga_analytics';

    /**
     * Constructor.
     */
    public function __construct() {
        // AJAX handlers for tracking events (logged-in and guest users).
        add_action( 'wp_ajax_aga_track_event', array( $this, 'record_event' ) );
        add_action( 'wp_ajax_nopriv_aga_track_event', array( $this, 'record_event' ) );

        // AJAX handler for fetching stats in the admin dashboard.
        add_action( 'wp_ajax_aga_get_analytics', array( $this, 'ajax_get_stats' ) );
    }

    /**
     * Creates the analytics database table.
     *
     * Called during plugin activation.
     *
     * @since 5.1.0
     */
    public static function create_table() {
        global $wpdb;

        $table_name      = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_type VARCHAR(20) NOT NULL,
            form_id INT(11) DEFAULT 0,
            country VARCHAR(10) DEFAULT '',
            city VARCHAR(100) DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY event_type (event_type),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * AJAX handler to record an analytics event.
     *
     * Fires on wp_ajax_aga_track_event and wp_ajax_nopriv_aga_track_event.
     *
     * @since 5.1.0
     */
    public function record_event() {
        // Verify nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'aga_frontend_nonce' ) ) {
            wp_send_json_error( 'Invalid nonce', 403 );
        }

        // Only works for paying users.
        if ( ! function_exists( 'google_autocomplete' ) || ! google_autocomplete()->is_paying() ) {
            wp_send_json_error( 'Pro feature', 403 );
        }

        $event_type = isset( $_POST['event_type'] ) ? sanitize_text_field( $_POST['event_type'] ) : '';
        if ( ! in_array( $event_type, array( 'search', 'selection', 'abandonment' ), true ) ) {
            wp_send_json_error( 'Invalid event type', 400 );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $wpdb->insert(
            $table_name,
            array(
                'event_type' => $event_type,
                'form_id'    => isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0,
                'country'    => isset( $_POST['country'] ) ? sanitize_text_field( substr( $_POST['country'], 0, 10 ) ) : '',
                'city'       => isset( $_POST['city'] ) ? sanitize_text_field( substr( $_POST['city'], 0, 100 ) ) : '',
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%s', '%d', '%s', '%s', '%s' )
        );

        wp_send_json_success();
    }

    /**
     * AJAX handler to return analytics stats for the admin dashboard.
     *
     * @since 5.1.0
     */
    public function ajax_get_stats() {
        check_ajax_referer( 'aga_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized', 403 );
        }

        if ( ! function_exists( 'google_autocomplete' ) || ! google_autocomplete()->is_paying() ) {
            wp_send_json_error( 'Pro feature', 403 );
        }

        $days = isset( $_POST['days'] ) ? absint( $_POST['days'] ) : 30;
        if ( ! in_array( $days, array( 7, 30, 90 ), true ) ) {
            $days = 30;
        }

        wp_send_json_success( $this->get_stats( $days ) );
    }

    /**
     * Returns analytics stats for the given number of days.
     *
     * @since 5.1.0
     * @param int $days Number of days to look back.
     * @return array
     */
    public function get_stats( $days = 30 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $date_from  = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );

        // Validate date format to guard against unexpected values.
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_from ) ) {
            $date_from = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
        }

        // Total searches.
        $total_searches = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE event_type = 'search' AND created_at >= %s",
            $date_from . ' 00:00:00'
        ) );

        // Total selections.
        $total_selections = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE event_type = 'selection' AND created_at >= %s",
            $date_from . ' 00:00:00'
        ) );

        // Conversion rate.
        $conversion_rate = $total_searches > 0 ? round( ( $total_selections / $total_searches ) * 100, 1 ) : 0;

        // Daily data for chart.
        $daily_rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT DATE(created_at) as date, event_type, COUNT(*) as count
             FROM {$table_name}
             WHERE created_at >= %s
             GROUP BY DATE(created_at), event_type
             ORDER BY DATE(created_at) ASC",
            $date_from . ' 00:00:00'
        ) );

        $daily_data = array();
        // Initialize all days in range.
        for ( $i = $days; $i >= 0; $i-- ) {
            $date = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
            $daily_data[ $date ] = array(
                'searches'   => 0,
                'selections' => 0,
            );
        }
        foreach ( $daily_rows as $row ) {
            if ( isset( $daily_data[ $row->date ] ) ) {
                if ( 'search' === $row->event_type ) {
                    $daily_data[ $row->date ]['searches'] = (int) $row->count;
                } else {
                    $daily_data[ $row->date ]['selections'] = (int) $row->count;
                }
            }
        }

        // Top 10 countries by selection count.
        $top_countries = $wpdb->get_results( $wpdb->prepare(
            "SELECT country, COUNT(*) as count
             FROM {$table_name}
             WHERE event_type = 'selection' AND country != '' AND created_at >= %s
             GROUP BY country
             ORDER BY count DESC
             LIMIT 10",
            $date_from . ' 00:00:00'
        ) );

        // Top 10 cities by selection count.
        $top_cities = $wpdb->get_results( $wpdb->prepare(
            "SELECT city, COUNT(*) as count
             FROM {$table_name}
             WHERE event_type = 'selection' AND city != '' AND created_at >= %s
             GROUP BY city
             ORDER BY count DESC
             LIMIT 10",
            $date_from . ' 00:00:00'
        ) );

        return array(
            'total_searches'   => $total_searches,
            'total_selections' => $total_selections,
            'conversion_rate'  => $conversion_rate,
            'daily_data'       => $daily_data,
            'top_countries'    => $top_countries,
            'top_cities'       => $top_cities,
        );
    }
}
