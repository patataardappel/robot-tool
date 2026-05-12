<?php
/**
 * Simple calculation/display helper for the plugin.
 */
function my_plugin_calculate_display( $meters, $minutes, $floor_type = '', $weekly_hours = 0, $hourly_wage = 0, $robot_price_month = 0, $robot_name = '' ) {
    $options = get_option( 'my_plugin_options', array() );
    if ( ! is_array( $options ) || empty( $options ) ) {
        $options = array(
            array( 'name' => 'Default Item A', 'meters' => 1100, 'price_month' => 500 ),
            array( 'name' => 'Default Item B', 'meters' => 2400, 'price_month' => 800 ),
        );
    }

    $numericResult = floatval( $meters );
    $rate_per_hour = 0;
    if ( $numericResult > 0 && floatval( $minutes ) > 0 ) {
        $rate_per_hour = ( $numericResult / floatval( $minutes ) ) * 60;
    }

    $floor_multiplier = 1.0;
    switch ( $floor_type ) {
        case 'tapijt': case 'carpet': $floor_multiplier = 1.2; break;
        case 'tile': $floor_multiplier = 1.1; break;
        default: $floor_multiplier = 1.0; break;
    }

    $adjusted_rate = $rate_per_hour * $floor_multiplier;

    $selected_item = null;
    if ( $adjusted_rate > 0 ) {
        foreach ( $options as $item ) {
            if ( isset( $item['meters'] ) && $adjusted_rate < floatval( $item['meters'] ) ) {
                $selected_item = $item;
                break;
            }
        }
    }

    $weekly_cost = floatval( $weekly_hours ) * floatval( $hourly_wage );
    $annual_cost = $weekly_cost * 52;
    $ratio = 0.5;

    if ( $selected_item ) {
        $robot_price_month = floatval( $selected_item['price_month'] ?? $robot_price_month );
        $robot_name = sanitize_text_field( $selected_item['name'] ?? $robot_name );
    }

    $robot_annual_cost = floatval( $robot_price_month ) * 12;
    $manual_annual_cost_after = $annual_cost * ( 1 - $ratio );
    $total_cost_with_robot = $robot_annual_cost + $manual_annual_cost_after;
    $annual_savings = $annual_cost - $total_cost_with_robot;
    $payback_months = $annual_savings > 0 ? (($robot_price_month * 12) / $annual_savings) * 12 : 0;

    // Comparison metrics for chart
    $availability_robot = 24;
    $availability_manual = round( min( 24, ( $weekly_hours / 7 ) ), 1 );
    $cleaning_per_hour_robot = floatval( $selected_item['meters'] ?? 1000 );
    $cleaning_per_hour_manual = round( $adjusted_rate, 0 );
    $robot_absence = 1; // days per year for maintenance/downtime
    $manual_absence = max( 5, round( $weekly_hours / 10 ) );

    ob_start();
    ?>

    
    <div class="dashboard-container">

    <!-- Main Component Card -->
    <div class="robot-recommendation-card">
        <!-- Left Side: Visual -->
        <div class="robot-image-section">
            <div class="robot-container">
                <!-- Dynamic image based on robot, fallback to placeholder -->
                <img src="<?php echo esc_url( $selected_item['image'] ?? 'https://placehold.co/400x400/e2e8f0/64748b?text=' . urlencode($robot_name) ); ?>" 
                     alt="<?php echo esc_attr($robot_name); ?> Robot" 
                     onerror="this.onerror=null; this.src='https://placehold.co/400x400/e2e8f0/64748b?text=Robot';">
            </div>
        </div>

        <!-- Right Side: Content -->
        <div class="robot-content-section">
            <div>
                <h1 class="robot-title">De <?php echo esc_html($robot_name); ?></h1>
                <p class="robot-subtitle">Is de juiste robot voor u</p>
            </div>

            <!-- Features List -->
            <ul class="robot-features">
                <li>
                    <div class="check-icon-wrapper">
                        <svg class="check-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <span>Geschikt voor uw vloer</span>
                </li>
                <li>
                    <div class="check-icon-wrapper">
                        <svg class="check-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <span><?php echo esc_html($selected_item['cleaning_functions'] ?? 'Vegen'); ?></span>
                </li>
                <li>
                    <div class="check-icon-wrapper">
                        <svg class="check-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <span><?php echo esc_html($selected_item['meters'] ?? 1000); ?> m² per uur</span>
                </li>
            </ul>

            <!-- Button -->
            <div>
                <button class="btn-verder btn-active">
                    Offerte aanvragen
                </button>
            </div>
        </div>

</div>
        <!-- Top Stats Row -->
        <div class="stats-row mb-8">
            <div class="stat-card stat-card-blue">
                <p class="text-sm opacity-90">Totale kosten besparing</p>
                <h2 class="text-xl font-bold mt-1">€<?php echo number_format($annual_savings, 0, ',', '.'); ?></h2>
                <p class="text-xs mt-2 opacity-75">Per jaar</p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">kosten robot</p>
                <h2 class="text-xl font-bold mt-1 text-gray-800">€<?php echo number_format($total_cost_with_robot, 0, ',', '.'); ?></h2>
                <p class="text-xs mt-2 text-gray-400">Per jaar (incl. assistentie)</p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">Kosten handmatig</p>
                <h2 class="text-xl font-bold mt-1 text-gray-800">€<?php echo number_format($annual_cost, 0, ',', '.'); ?></h2>
                <p class="text-xs mt-2 text-gray-400">Per jaar</p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">terugverdientijd</p>
                <h2 class="text-xl font-bold mt-1 text-gray-800"><?php echo round($payback_months); ?></h2>
                <p class="text-xs mt-2 text-gray-400">maanden</p>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="chart-container">
            <div class="flex justify-between items-center mb-6">
                <div class="flex gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <span class="text-xs text-gray-500">Robot</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-indigo-600"></div>
                        <span class="text-xs text-gray-500">Handmatige schoonmaak</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="updateChart('line')" class="toggle-btn">
                        <svg width="20" height="20" fill="none" stroke="#007bb6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </button>
                    <button onclick="updateChart('bar')" class="toggle-btn">
                        <svg width="20" height="20" fill="none" stroke="#007bb6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </button>
                </div>
            </div>

            <div id="lineChartContainer" class="h-64">
                <canvas id="roiChart"></canvas>
            </div>

            <div id="barChartsContainer" class="hidden flex flex-row flex-nowrap w-full gap-4 overflow-x-auto pb-4">
                <div class="sub-chart flex-1 min-w-[100px]">
                    <h4 class="text-center mb-2 text-sm font-medium">Beschikbaarheid</h4>
                    <div class="h-32">
                        <canvas id="chartAvail"></canvas>
                    </div>
                </div>
                <div class="sub-chart flex-1 min-w-[100px]">
                    <h4 class="text-center mb-2 text-sm font-medium">Kosten/jaar</h4>
                    <div class="h-32">
                        <canvas id="chartCosts"></canvas>
                    </div>
                </div>
                <div class="sub-chart flex-1 min-w-[100px]">
                    <h4 class="text-center mb-2 text-sm font-medium">m² per uur</h4>
                    <div class="h-32">
                        <canvas id="chartCleaning"></canvas>
                    </div>
                </div>
                <div class="sub-chart flex-1 min-w-[100px]">
                    <h4 class="text-center mb-2 text-sm font-medium">Verzuim/jaar</h4>
                    <div class="h-32">
                        <canvas id="chartAbsence"></canvas>
                    </div>
                    
                </div>
            </div>
        </div>

    <style>
        /* Force horizontal layout even if Tailwind is overridden by theme CSS */
        #barChartsContainer:not(.hidden) {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: flex-start;
        }
        .sub-chart {
            flex: 1 1 200px; /* Grow, Shrink, Basis */
        }
    </style>

    <script>
        let myChart;
        let chartAvail, chartCosts, chartCleaning, chartAbsence;

        function initChart(type = 'line') {
            if (myChart) myChart.destroy();
            if (chartAvail) chartAvail.destroy();
            if (chartCosts) chartCosts.destroy();
            if (chartCleaning) chartCleaning.destroy();
            if (chartAbsence) chartAbsence.destroy();

            const lineContainer = document.getElementById('lineChartContainer');
            const barContainer = document.getElementById('barChartsContainer');

            if (type === 'line') {
                lineContainer.classList.remove('hidden');
                barContainer.classList.add('hidden');

                const ctx = document.getElementById('roiChart').getContext('2d');
                const annualManual = <?php echo $annual_cost; ?>;
                const annualRobot = <?php echo $total_cost_with_robot; ?>;
                const labels = ['M0', 'M2', 'M4', 'M6', 'M8', 'M10', 'M12', 'M14', 'M16', 'M18', 'M20', 'M22', 'M24'];
                const months = [0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24];

                myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            { label: 'Robot', data: months.map(m => (annualRobot/12)*m), borderColor: '#f87171', backgroundColor: '#f8717122', borderWidth: 3, fill: true },
                            { label: 'Handmatig', data: months.map(m => (annualManual/12)*m), borderColor: '#4f46e5', backgroundColor: '#4f46e522', borderWidth: 3, fill: true }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            } else {
                lineContainer.classList.add('hidden');
                barContainer.classList.remove('hidden');

                // Standard Chart.js bar config
                const barOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } },
                    barPercentage: 0.4
                };

                // Init all 4 bar charts
                chartAvail = new Chart(document.getElementById('chartAvail'), {
                    type: 'bar',
                    data: { labels: ['Robot', 'Handm.'], datasets: [{ data: [<?php echo $availability_robot; ?>, <?php echo $availability_manual; ?>], backgroundColor: ['#f87171', '#4f46e5'] }] },
                    options: barOptions
                });

                chartCosts = new Chart(document.getElementById('chartCosts'), {
                    type: 'bar',
                    data: { labels: ['Robot', 'Handm.'], datasets: [{ data: [<?php echo $total_cost_with_robot; ?>, <?php echo $annual_cost; ?>], backgroundColor: ['#f87171', '#4f46e5'] }] },
                    options: barOptions
                });

                chartCleaning = new Chart(document.getElementById('chartCleaning'), {
                    type: 'bar',
                    data: { labels: ['Robot', 'Handm.'], datasets: [{ data: [<?php echo $cleaning_per_hour_robot; ?>, <?php echo $cleaning_per_hour_manual; ?>], backgroundColor: ['#f87171', '#4f46e5'] }] },
                    options: barOptions
                });

                chartAbsence = new Chart(document.getElementById('chartAbsence'), {
                    type: 'bar',
                    data: { labels: ['Robot', 'Handm.'], datasets: [{ data: [<?php echo $robot_absence; ?>, <?php echo $manual_absence; ?>], backgroundColor: ['#f87171', '#4f46e5'] }] },
                    options: barOptions
                });
            }
        }

        function updateChart(type) { initChart(type); }
        window.onload = () => initChart('line');
    </script>
    <?php
    return ob_get_clean();
}
?>