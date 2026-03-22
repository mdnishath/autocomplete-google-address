<?php
/**
 * Admin View: Analytics Dashboard
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/admin/views
 * @since      5.1.0
 */

defined( 'ABSPATH' ) || exit;

$is_paying = function_exists( 'google_autocomplete' ) && google_autocomplete()->is_paying();
?>

<div class="wrap">
    <h1><?php esc_html_e( 'Usage Analytics', 'autocomplete-google-address' ); ?></h1>

    <?php if ( ! $is_paying ) : ?>
        <?php $checkout_url = function_exists( 'google_autocomplete' ) ? google_autocomplete()->checkout_url() : '#'; ?>
        <div class="aga-analytics-upgrade">
            <div class="aga-analytics-upgrade-icon">&#128202;</div>
            <h2>
                <?php esc_html_e( 'Usage Analytics is a Pro Feature', 'autocomplete-google-address' ); ?>
            </h2>
            <p>
                <?php esc_html_e( 'Track autocomplete searches, selections, and conversion rates. See which countries and cities your users are selecting the most.', 'autocomplete-google-address' ); ?>
            </p>
            <a href="<?php echo esc_url( $checkout_url ); ?>" class="button button-primary button-hero">
                <?php esc_html_e( 'Upgrade to Pro', 'autocomplete-google-address' ); ?>
            </a>
        </div>
    <?php else : ?>

        <!-- Date range filter -->
        <div class="aga-analytics-filters">
            <button type="button" class="button aga-analytics-range" data-days="7"><?php esc_html_e( 'Last 7 days', 'autocomplete-google-address' ); ?></button>
            <button type="button" class="button button-primary aga-analytics-range aga-analytics-range--active" data-days="30"><?php esc_html_e( 'Last 30 days', 'autocomplete-google-address' ); ?></button>
            <button type="button" class="button aga-analytics-range" data-days="90"><?php esc_html_e( 'Last 90 days', 'autocomplete-google-address' ); ?></button>
        </div>

        <!-- Stats cards -->
        <div class="aga-analytics-cards">
            <div class="aga-analytics-card">
                <div class="aga-analytics-card-label"><?php esc_html_e( 'Total Searches', 'autocomplete-google-address' ); ?></div>
                <div class="aga-analytics-card-value" id="aga-stat-searches">&mdash;</div>
            </div>
            <div class="aga-analytics-card">
                <div class="aga-analytics-card-label"><?php esc_html_e( 'Total Selections', 'autocomplete-google-address' ); ?></div>
                <div class="aga-analytics-card-value" id="aga-stat-selections">&mdash;</div>
            </div>
            <div class="aga-analytics-card">
                <div class="aga-analytics-card-label"><?php esc_html_e( 'Conversion Rate', 'autocomplete-google-address' ); ?></div>
                <div class="aga-analytics-card-value" id="aga-stat-conversion">&mdash;</div>
            </div>
        </div>

        <!-- Daily usage chart -->
        <div class="aga-analytics-chart-wrap">
            <h3><?php esc_html_e( 'Daily Usage', 'autocomplete-google-address' ); ?></h3>
            <p class="aga-analytics-chart-legend">
                <span class="aga-analytics-legend-dot aga-analytics-legend-dot--searches"></span> <?php esc_html_e( 'Searches', 'autocomplete-google-address' ); ?>
                &nbsp;&nbsp;
                <span class="aga-analytics-legend-dot aga-analytics-legend-dot--selections"></span> <?php esc_html_e( 'Selections', 'autocomplete-google-address' ); ?>
            </p>
            <div id="aga-analytics-chart" class="aga-analytics-chart">
                <div class="aga-analytics-chart-placeholder"><?php esc_html_e( 'Loading...', 'autocomplete-google-address' ); ?></div>
            </div>
        </div>

        <!-- Two-column layout: Top Countries | Top Cities -->
        <div class="aga-analytics-tables">
            <div class="aga-analytics-table-wrap">
                <h3><?php esc_html_e( 'Top Countries', 'autocomplete-google-address' ); ?></h3>
                <table class="widefat striped" id="aga-top-countries">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Country', 'autocomplete-google-address' ); ?></th>
                            <th class="aga-analytics-table-num"><?php esc_html_e( 'Selections', 'autocomplete-google-address' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="2" class="aga-analytics-table-empty"><?php esc_html_e( 'Loading...', 'autocomplete-google-address' ); ?></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="aga-analytics-table-wrap">
                <h3><?php esc_html_e( 'Top Cities', 'autocomplete-google-address' ); ?></h3>
                <table class="widefat striped" id="aga-top-cities">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'City', 'autocomplete-google-address' ); ?></th>
                            <th class="aga-analytics-table-num"><?php esc_html_e( 'Selections', 'autocomplete-google-address' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="2" class="aga-analytics-table-empty"><?php esc_html_e( 'Loading...', 'autocomplete-google-address' ); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
        (function($) {
            var currentDays = 30;

            function loadAnalytics(days) {
                currentDays = days;

                // Update active button.
                $('.aga-analytics-range').removeClass('button-primary aga-analytics-range--active');
                $('.aga-analytics-range[data-days="' + days + '"]').addClass('button-primary aga-analytics-range--active');

                $.post(aga_admin_data.ajax_url, {
                    action: 'aga_get_analytics',
                    nonce: aga_admin_data.nonce,
                    days: days
                }, function(response) {
                    if (!response.success) return;

                    var data = response.data;

                    // Update stat cards.
                    $('#aga-stat-searches').text(data.total_searches.toLocaleString());
                    $('#aga-stat-selections').text(data.total_selections.toLocaleString());
                    $('#aga-stat-conversion').text(data.conversion_rate + '%');

                    // Render chart.
                    renderChart(data.daily_data);

                    // Render tables.
                    renderTable('#aga-top-countries', data.top_countries, 'country');
                    renderTable('#aga-top-cities', data.top_cities, 'city');
                });
            }

            function renderChart(dailyData) {
                var $chart = $('#aga-analytics-chart');
                $chart.empty();

                var dates = Object.keys(dailyData);
                if (!dates.length) {
                    $chart.html('<div class="aga-analytics-chart-placeholder"><?php echo esc_js( __( 'No data yet', 'autocomplete-google-address' ) ); ?></div>');
                    return;
                }

                // Find max value for scaling.
                var maxVal = 1;
                dates.forEach(function(date) {
                    var s = dailyData[date].searches || 0;
                    var sel = dailyData[date].selections || 0;
                    if (s > maxVal) maxVal = s;
                    if (sel > maxVal) maxVal = sel;
                });

                var barWidth = Math.max(Math.floor(($chart.width() - dates.length * 2) / dates.length), 6);

                dates.forEach(function(date) {
                    var searches = dailyData[date].searches || 0;
                    var selections = dailyData[date].selections || 0;
                    var searchH = Math.max(Math.round((searches / maxVal) * 180), searches > 0 ? 2 : 0);
                    var selH = Math.max(Math.round((selections / maxVal) * 180), selections > 0 ? 2 : 0);

                    var dayLabel = date.substring(5); // MM-DD

                    var $col = $('<div class="aga-analytics-chart-col" title="' + date + ': ' + searches + ' searches, ' + selections + ' selections"></div>').css('min-width', barWidth + 'px');

                    var $bars = $('<div class="aga-analytics-chart-bars"></div>');
                    $bars.append('<div class="aga-analytics-bar aga-analytics-bar--searches" style="width:' + Math.max(barWidth / 2 - 1, 3) + 'px;height:' + searchH + 'px;"></div>');
                    $bars.append('<div class="aga-analytics-bar aga-analytics-bar--selections" style="width:' + Math.max(barWidth / 2 - 1, 3) + 'px;height:' + selH + 'px;"></div>');

                    $col.append($bars);

                    // Only show date labels for some bars to avoid crowding.
                    if (dates.length <= 14 || dates.indexOf(date) % Math.ceil(dates.length / 14) === 0) {
                        $col.append('<div class="aga-analytics-chart-date">' + dayLabel + '</div>');
                    }

                    $chart.append($col);
                });
            }

            function renderTable(selector, rows, field) {
                var $tbody = $(selector).find('tbody');
                $tbody.empty();

                if (!rows || !rows.length) {
                    $tbody.append('<tr><td colspan="2" class="aga-analytics-table-empty"><?php echo esc_js( __( 'No data yet', 'autocomplete-google-address' ) ); ?></td></tr>');
                    return;
                }

                rows.forEach(function(row) {
                    $tbody.append(
                        '<tr><td>' + $('<span>').text(row[field]).html() + '</td>' +
                        '<td class="aga-analytics-table-num">' + parseInt(row.count, 10).toLocaleString() + '</td></tr>'
                    );
                });
            }

            // Button click handlers.
            $(document).on('click', '.aga-analytics-range', function() {
                loadAnalytics(parseInt($(this).data('days'), 10));
            });

            // Initial load.
            loadAnalytics(30);

        })(jQuery);
        </script>
    <?php endif; ?>
</div>
