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
                <button class="cta-button text-white text-xl font-medium px-10 py-4 rounded-full shadow-lg">
                    Offerte aanvragen
                </button>
            </div>
        </div>

</div>
        <!-- Top Stats Row -->
        <div class="stats-row mb-8">
            <div class="stat-card stat-card-blue">
                <p class="text-sm opacity-90">Totale kosten besparing</p>
                <h2 class="text-3xl font-bold mt-1">€<?php echo number_format($annual_savings, 0, ',', '.'); ?></h2>
                <p class="text-xs mt-2 opacity-75">Per jaar</p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">kosten robot</p>
                <h2 class="text-3xl font-bold mt-1 text-gray-800">€<?php echo number_format($total_cost_with_robot, 0, ',', '.'); ?></h2>
                <p class="text-xs mt-2 text-gray-400">Per jaar (incl. assistentie)</p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">Kosten handmatig</p>
                <h2 class="text-3xl font-bold mt-1 text-gray-800">€<?php echo number_format($annual_cost, 0, ',', '.'); ?></h2>
                <p class="text-xs mt-2 text-gray-400">Per jaar</p>
            </div>
            <div class="stat-card">
                <p class="text-sm text-gray-500">terugverdientijd</p>
                <h2 class="text-3xl font-bold mt-1 text-gray-800"><?php echo round($payback_months); ?></h2>
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
            <div class="h-64">
                <canvas id="roiChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Sliders (Visual only for now) -->
            <div class="space-y-8">
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Medewerkers</span>
                        <span class="text-sm font-bold text-blue-600"><?php echo esc_html($meters > 2000 ? 4 : 2); ?></span>
                    </div>
                    <input type="range" min="1" max="10" value="4" class="slider-custom">
                </div>
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Uurloon medewerker</span>
                        <span class="text-sm font-bold text-blue-600">€<?php echo esc_html($hourly_wage); ?></span>
                    </div>
                    <input type="range" min="10" max="30" value="<?php echo $hourly_wage; ?>" class="slider-custom">
                </div>
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Schoonmaak per week (uren)</span>
                        <span class="text-sm font-bold text-blue-600"><?php echo esc_html($weekly_hours); ?></span>
                    </div>
                    <input type="range" min="1" max="168" value="<?php echo $weekly_hours; ?>" class="slider-custom">
                </div>
            </div>

            <!-- Right: Comparison Table -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-e5e7eb overflow-hidden">
                <div class="bg-[#007bb6] text-white p-4 font-bold text-lg">
                    Vergelijking
                </div>
                <table class="w-full comparison-table">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th><?php echo esc_html($robot_name ?: 'Robot'); ?></th>
                            <th>Handmatig</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-gray-500 text-sm italic">efficiency (m²/hour)</td>
                            <td><?php echo esc_html($selected_item['meters'] ?? 1000); ?> (m²/h)</td>
                            <td><?php echo esc_html(round($adjusted_rate, 0)); ?> (m²/h)</td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 text-sm italic">maandelijkse kost</td>
                            <td>€<?php echo number_format($robot_price_month, 2); ?></td>
                            <td>€<?php echo number_format($weekly_cost * 4.33, 2); ?></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500 text-sm italic">Besparing per jaar</td>
                            <td class="text-green-600 font-bold">€<?php echo number_format($annual_savings, 0, ',', '.'); ?></td>
                            <td>€0</td>
                        </tr>
                        <tr class="active-row">
                            <td class="text-gray-700">Totale Kosten per jaar</td>
                            <td class="text-blue-600">€<?php echo number_format($total_cost_with_robot, 0, ',', '.'); ?></td>
                            <td class="text-gray-900 font-bold">€<?php echo number_format($annual_cost, 0, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let myChart;
        const ctx = document.getElementById('roiChart').getContext('2d');

        function initChart(type = 'line') {
            if (myChart) myChart.destroy();
            
            // Generate cumulative monthly data based on annual costs
            const annualManual = <?php echo $annual_cost; ?>;
            const annualRobot = <?php echo $total_cost_with_robot; ?>;
            const labels = ['Maand 0', 'Maand 2', 'Maand 4', 'Maand 6', 'Maand 8', 'Maand 10', 'Maand 12'];
            const months = [0, 2, 4, 6, 8, 10, 12];

            const manualData = months.map(month => (annualManual / 12) * month);
            const robotData = months.map(month => (annualRobot / 12) * month);

            const data = {
                labels: labels,
                datasets: [
                    {
                        label: 'Robot',
                        data: robotData,
                        borderColor: '#f87171',
                        backgroundColor: '#f8717122',
                        borderWidth: 3,
                        fill: type === 'line'
                    },
                    {
                        label: 'Handmatig',
                        data: manualData,
                        borderColor: '#4f46e5',
                        backgroundColor: '#4f46e522',
                        borderWidth: 3,
                        fill: type === 'line'
                    }
                ]
            };

            myChart = new Chart(ctx, {
                type: type,
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        function updateChart(type) { initChart(type); }
        window.onload = () => initChart('line');
    </script>
    <?php
    return ob_get_clean();
}
?>