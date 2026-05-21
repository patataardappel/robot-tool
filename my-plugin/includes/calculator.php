<?php
/**
 * Simple calculation/display helper for the plugin.
 */
function my_plugin_calculate_display( $meters, $minutes, $floor_type = '', $weekly_hours = 0, $hourly_wage = 0, $employees = 1, $robot_price_month = 0, $robot_name = '', $robot_workload_share = 50 ) {
    $employees = max( 1, intval( $employees ) );
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
        case 'tile': $floor_multiplier = 1.0; break;
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

    // --- IMPROVED REALISTIC MATHEMATICAL LOGIC ---
    // 1. Baseline Cost: Treat weekly hours as TOTAL collective team man-hours to prevent cost multiplication errors
    $total_manual_hours_yearly = floatval( $weekly_hours ) * 52;
    $annual_cost = $total_manual_hours_yearly * floatval( $hourly_wage );

    // 2. Dynamic Replacement Ratio:
    // The user-controlled slider determines the exact percentage of duties the robot takes over.
    $robot_workload_share_pct = floatval( $robot_workload_share ) / 100;
    $automatable_hours_yearly = $total_manual_hours_yearly * $robot_workload_share_pct;

    // 3. Human Intervention Overhead:
    // Staff takes ~15 minutes (0.25 hours) per day to service/clean/refill the robot
    $days_operated_per_year = 312; // Assuming 6 days a week
    $human_maintenance_overhead_hours = 0.25 * $days_operated_per_year;

    // Net hours saved by deploying the robot
    $net_hours_saved_yearly = max( 0, $automatable_hours_yearly - $human_maintenance_overhead_hours );
    $manual_labor_savings = $net_hours_saved_yearly * floatval( $hourly_wage );

    if ( $selected_item ) {
        $robot_price_month = floatval( $selected_item['price_month'] ?? $robot_price_month );
        $robot_name = sanitize_text_field( $selected_item['name'] ?? $robot_name );
    }

    $robot_annual_cost = floatval( $robot_price_month ) * 12;
    
    // New total cost = Robot subscription + Remaining manual tasks (toilets, dusting, servicing robot)
    $total_cost_with_robot = $robot_annual_cost + ( $annual_cost - $manual_labor_savings );
    $annual_savings = $annual_cost - $total_cost_with_robot;
    $payback_months = $annual_savings > 0 ? ( $robot_annual_cost / $annual_savings ) * 12 : 0;

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

    <div class="robot-recommendation-card">
        <div class="robot-image-section">
            <div class="robot-container">
                <img src="<?php echo esc_url( $selected_item['image'] ?? 'https://placehold.co/400x400/e2e8f0/64748b?text=' . urlencode($robot_name) ); ?>"
                    alt="<?php echo esc_attr($robot_name); ?> Robot"
                    onerror="this.onerror=null; this.src='https://placehold.co/400x400/e2e8f0/64748b?text=Robot';">
            </div>
        </div>

        <div class="robot-content-section">
            <div class="robot-header-group">
                <h1 class="robot-title">De <?php echo esc_html($robot_name); ?></h1>
                <p class="robot-subtitle">Is de juiste robot voor u</p>
            </div>

            <div class="robot-features-list">
                <div class="check-icon-wrapper">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Geschikt voor uw vloer</span>
                </div>

                <div class="check-icon-wrapper">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span><?php echo esc_html($selected_item['cleaning_functions'] ?? 'Vegen'); ?></span>
                </div>

                <div class="check-icon-wrapper">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span><?php echo esc_html($selected_item['meters'] ?? 1000); ?> m² per uur</span>
                </div>
            </div>

            <div class="robot-action-wrapper">
                <button class="btn-verder btn-active">
                    Offerte aanvragen
                </button>
            </div>
        </div>
    </div>

    <div class="layout-grid-wrapper">

        <aside class="sidebar-column">
            <div class="sidebar-controls">
                <button class="btn-verder"
                    style="border: 1px solid #007bb6; background: transparent; color: #007bb6; margin-bottom: 12px;">
                    Download resultaten <span>↓</span>
                </button>
                <button class="btn-verder btn-active" style="margin-bottom: 30px;">
                    Demo aan vragen
                </button>

                <div class="control-group">
                    <label class="label-text">Andere robot vergelijken</label>
                    <div class="select-wrapper">
                        <select id="compareRobotSelect" class="custom-input" name="compare_robot"
                            onchange="handleRobotSelection(this)">
                            <?php foreach ( $options as $option ) : ?>
                            <option value="<?php echo esc_attr( $option['name'] ?? '' ); ?>"
                                <?php selected( $option['name'] ?? '', $robot_name ); ?>>
                                <?php echo esc_html( $option['name'] ?? 'Onbekende robot' ); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </aside>

        <main class="results-column">
            <h2 id="robotTitle" class="results-title">De <?php echo esc_html($robot_name); ?></h2>

            <div class="stats-chart-split">
                <div class="vertical-stat-stack">
                    <div class="stat-card stat-card-blue">
                        <p class="stat-label">Totale kosten besparing</p>
                        <h2 id="annualSavingsValue" class="stat-value">
                            €<?php echo number_format($annual_savings, 0, ',', '.'); ?></h2>
                        <p class="stat-sub">Per jaar</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-label">Kosten incl. robot</p>
                        <h2 id="robotCostValue" class="stat-value">
                            €<?php echo number_format($total_cost_with_robot, 0, ',', '.'); ?></h2>
                        <p class="stat-sub">Per jaar</p>
                    </div>
                    <div class="stat-card">
                        <p class="stat-label">Kosten handmatig</p>
                        <h2 id="manualCostValue" class="stat-value">
                            €<?php echo number_format($annual_cost, 0, ',', '.'); ?></h2>
                        <p class="stat-sub">Per jaar</p>
                    </div>
                </div>

                <div class="chart-container">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex gap-4">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                <span class="text-xs text-gray-500">Robot situatie</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-indigo-600"></div>
                                <span class="text-xs text-gray-500">Handmatige schoonmaak</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="updateChart('line')" class="toggle-btn">📈</button>
                            <button onclick="updateChart('bar')" class="toggle-btn">📊</button>
                        </div>
                    </div>

                    <div id="lineChartContainer" class="h-64">
                        <canvas id="roiChart"></canvas>
                    </div>

                    <div id="barChartsContainer" class="hidden">
                        <div class="sub-chart h-32"><canvas id="chartAvail"></canvas></div>
                        <div class="sub-chart h-32"><canvas id="chartCosts"></canvas></div>
                        <div class="sub-chart h-32"><canvas id="chartCleaning"></canvas></div>
                        <div class="sub-chart h-32"><canvas id="chartAbsence"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="bottom-wrapper">
                <div class="sliders-container">
                    <div class="slider-group">
                        <div class="slider-label-row">
                            <label class="label-text">Medewerkers</label>
                            <span class="slider-val-display"
                                id="sliderEmployeesValue"><?php echo intval( $employees ); ?></span>
                        </div>
                        <input id="sliderEmployees" type="range" class="slider-custom" min="1" max="10"
                            value="<?php echo intval( $employees ); ?>" oninput="updateSliderValue(this)">
                    </div>

                    <div class="slider-group">
                        <div class="slider-label-row">
                            <label class="label-text">Uurloon medewerker</label>
                            <span class="slider-val-display"
                                id="sliderHourlyWageValue">€<?php echo number_format( $hourly_wage, 0, ',', '.' ); ?></span>
                        </div>
                        <input id="sliderHourlyWage" type="range" class="slider-custom" min="0" max="50"
                            value="<?php echo esc_attr( $hourly_wage ); ?>" oninput="updateSliderValue(this)">
                    </div>

                    <div class="slider-group">
                        <div class="slider-label-row">
                            <label class="label-text">Schoonmaak per week (uren)</label>
                            <span class="slider-val-display"
                                id="sliderWeeklyHoursValue"><?php echo intval( $weekly_hours ); ?></span>
                        </div>
                        <input id="sliderWeeklyHours" type="range" class="slider-custom" min="1" max="60"
                            value="<?php echo esc_attr( $weekly_hours ); ?>" oninput="updateSliderValue(this)">
                    </div>

                    <div class="slider-group">
                        <div class="slider-label-row">
                            <label class="label-text">Taken overgenomen door robot (%)</label>
                            <span class="slider-val-display"
                                id="sliderRobotWorkloadValue"><?php echo intval( $robot_workload_share ); ?>%</span>
                        </div>
                        <input id="sliderRobotWorkload" type="range" class="slider-custom" min="0" max="100"
                            value="<?php echo intval( $robot_workload_share ); ?>" oninput="updateSliderValue(this)">
                    </div>
                </div>

                <div class="comparison-table-wrapper" style="margin-top: 30px;">
                    <div class="stat-card" style="padding: 0; overflow: hidden;">
                        <div style="background: #007bb6; color: white; padding: 15px 20px; font-weight: bold;">
                            Vergelijking
                        </div>
                        <table class="comparison-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="text-align: left; padding: 12px 20px;">Parameter</th>
                                    <th style="text-align: left; padding: 12px 20px;">Robot Situatie</th>
                                    <th style="text-align: left; padding: 12px 20px;">Handmatig</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>efficiency (m²/hour)</td>
                                    <td id="comparisonRobotEfficiency"><?php echo intval( $cleaning_per_hour_robot ); ?> (m²/hour)</td>
                                    <td id="comparisonManualEfficiency"><?php echo intval( $cleaning_per_hour_manual ); ?> (m²/hour)</td>
                                </tr>
                                <tr>
                                    <td>Totale kosten robot</td>
                                    <td id="comparisonRobotPrice"><?php echo esc_html( isset($selected_item['price']) ? '€' . number_format($selected_item['price'], 0, ',', '.') : '€0' ); ?></td>
                                    <td>€0</td>
                                </tr>
                                <tr>
                                    <td>kosten per m2</td>
                                    <td id="comparisonRobotCostPerM2"><?php 
                                        $cost_per_m2_robot = $meters > 0 ? $robot_annual_cost / $meters : 0;
                                        echo '€' . number_format($cost_per_m2_robot, 2, ',', '.');
                                    ?></td>
                                    <td id="comparisonManualCostPerM2"><?php 
                                        $cost_per_m2_manual = $meters > 0 ? $annual_cost / $meters : 0;
                                        echo '€' . number_format($cost_per_m2_manual, 2, ',', '.');
                                    ?></td>
                                </tr>
                                <tr>
                                    <td>schoonmaak tijd ruimte</td>
                                    <td id="comparisonRobotCleanTime"><?php 
                                        $robot_clean_time = $cleaning_per_hour_robot > 0 ? round(($meters / $cleaning_per_hour_robot) * 60) : 0;
                                        echo intval($robot_clean_time) . ' minuten';
                                    ?></td>
                                    <td id="comparisonManualCleanTime"><?php 
                                        $manual_clean_time = $cleaning_per_hour_manual > 0 ? round(($meters / $cleaning_per_hour_manual) * 60) : 0;
                                        echo intval($manual_clean_time) . ' minuten';
                                    ?></td>
                                </tr>
                                <tr>
                                    <td>Inzetbaarheid per dag</td>
                                    <td id="comparisonRobotAvailability"><?php echo number_format( $availability_robot, 1, ',', '.' ); ?> uur</td>
                                    <td id="comparisonManualAvailability"><?php echo number_format( $availability_manual, 1, ',', '.' ); ?> uur</td>
                                </tr>
                                <tr>
                                    <td>Foutmarge</td>
                                    <td>3%</td>
                                    <td>12%</td>
                                </tr>
                                <tr class="active-row">
                                    <td style="font-weight: bold;">Kosten per jaar</td>
                                    <td style="color: #10b981; font-weight: bold;" id="comparisonRobotYearCost">
                                        €<?php echo number_format($total_cost_with_robot, 0, ',', '.'); ?></td>
                                    <td style="font-weight: bold;" id="comparisonManualYearCost">
                                        €<?php echo number_format($annual_cost, 0, ',', '.'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
/* Force horizontal layout even if Tailwind is overridden by theme CSS */
#barChartsContainer:not(.hidden) {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: wrap !important;
    align-items: flex-start;
    gap: 14px;
}
.sub-chart {
    flex: 0 0 130px;
    max-width: 130px;
    min-width: 130px;
}
</style>

<script>
let myChart;
let chartAvail, chartCosts, chartCleaning, chartAbsence;
let currentChartType = 'line';

const robotOptions = <?php echo wp_json_encode( array_values( array_filter( array_map( function( $option ) {
        return array(
            'name' => $option['name'] ?? '',
            'price_month' => floatval( $option['price_month'] ?? 0 ),
            'meters' => floatval( $option['meters'] ?? 0 ),
            'cleaning_functions' => $option['cleaning_functions'] ?? '',
        );
    }, $options ) ) ) ); ?>;

const robotsByName = Object.fromEntries(robotOptions.map(robot => [robot.name, robot]));

const liveResultData = {
    robotAnnualCost: <?php echo floatval( $robot_annual_cost ); ?>,
    robotName: '<?php echo esc_js( $robot_name ); ?>',
    robotCostFormatted: '<?php echo number_format( $robot_annual_cost, 0, ',', '.' ); ?>',
    availabilityRobot: <?php echo floatval( $availability_robot ); ?>,
    robotAbsence: <?php echo floatval( $robot_absence ); ?>,
    robotCleaningRate: <?php echo floatval( $cleaning_per_hour_robot ); ?>,
    manualCleaningRate: <?php echo floatval( $cleaning_per_hour_manual ); ?>
};

function getSelectedRobotData() {
    const select = document.getElementById('compareRobotSelect');
    if (!select) {
        return null;
    }
    return robotsByName[select.value] || robotOptions[0] || null;
}

function handleRobotSelection(select) {
    const robot = getSelectedRobotData();
    if (!robot) {
        return;
    }

    liveResultData.robotAnnualCost = parseFloat(robot.price_month) * 12;
    liveResultData.robotCleaningRate = parseFloat(robot.meters) || liveResultData.robotCleaningRate;
    liveResultData.robotName = robot.name;

    const robotTitle = document.getElementById('robotTitle');
    if (robotTitle) {
        robotTitle.textContent = 'De ' + robot.name;
    }

    refreshResults();
}

function formatEuro(value) {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR',
        maximumFractionDigits: 0
    }).format(value);
}

function roundTo(value, digits) {
    const factor = Math.pow(10, digits);
    return Math.round(value * factor) / factor;
}

function getSliderValues() {
    return {
        employees: parseFloat(document.getElementById('sliderEmployees').value),
        hourlyWage: parseFloat(document.getElementById('sliderHourlyWage').value),
        weeklyHours: parseFloat(document.getElementById('sliderWeeklyHours').value),
        robotWorkload: parseFloat(document.getElementById('sliderRobotWorkload').value)
    };
}

function computeLiveMetrics(values) {
    // Treat values.weeklyHours as total combined team hours per week
    const totalManualHoursYearly = values.weeklyHours * 52;
    const manualAnnualCost = totalManualHoursYearly * values.hourlyWage;

    // Match backend dynamic adjustment rules based on user slider input
    const robotWorkloadPct = values.robotWorkload / 100;
    const automatableHoursYearly = totalManualHoursYearly * robotWorkloadPct;

    // Daily maintenance overhead (15 mins/day across roughly 312 operating days a year)
    const humanMaintenanceOverheadHours = 0.25 * 312;
    const netHoursSavedYearly = Math.max(0, automatableHoursYearly - humanMaintenanceOverheadHours);
    const manualLaborSavings = netHoursSavedYearly * values.hourlyWage;

    const totalCostWithRobot = liveResultData.robotAnnualCost + (manualAnnualCost - manualLaborSavings);
    const annualSavings = manualAnnualCost - totalCostWithRobot;
    const availabilityManual = Math.min(24, roundTo(values.weeklyHours / 7, 1));
    const manualAbsence = Math.max(5, Math.round(values.weeklyHours / 10));

    return {
        manualAnnualCost,
        totalCostWithRobot,
        annualSavings,
        availabilityManual,
        manualAbsence
    };
}

function updateSliderValue(slider) {
    let label = slider.value;
    if (slider.id === 'sliderHourlyWage') {
        label = '€' + slider.value;
    } else if (slider.id === 'sliderRobotWorkload') {
        label = slider.value + '%';
    }
    const display = document.getElementById(slider.id + 'Value');
    if (display) {
        display.textContent = label;
    }
    refreshResults();
}

function refreshResults() {
    const values = getSliderValues();
    const metrics = computeLiveMetrics(values);

    const annualSavingsElement = document.getElementById('annualSavingsValue');
    const robotCostElement = document.getElementById('robotCostValue');
    const manualCostElement = document.getElementById('manualCostValue');
    const comparisonManualCost = document.getElementById('comparisonManualYearCost');
    const comparisonRobotCost = document.getElementById('comparisonRobotYearCost');

    if (annualSavingsElement) {
        annualSavingsElement.textContent = formatEuro(metrics.annualSavings);
    }
    if (robotCostElement) {
        robotCostElement.textContent = formatEuro(metrics.totalCostWithRobot);
    }
    if (manualCostElement) {
        manualCostElement.textContent = formatEuro(metrics.manualAnnualCost);
    }
    if (comparisonManualCost) {
        comparisonManualCost.textContent = formatEuro(metrics.manualAnnualCost);
    }
    if (comparisonRobotCost) {
        comparisonRobotCost.textContent = formatEuro(metrics.totalCostWithRobot);
    }

    // Update comparison table cells
    const costPerM2Robot = liveResultData.meters > 0 ? liveResultData.robotAnnualCost / liveResultData.meters : 0;
    const costPerM2Manual = liveResultData.meters > 0 ? metrics.manualAnnualCost / liveResultData.meters : 0;
    const cleanTimeRobot = liveResultData.robotCleaningRate > 0 ? Math.round((liveResultData.meters / liveResultData.robotCleaningRate) * 60) : 0;
    const cleanTimeManual = liveResultData.manualCleaningRate > 0 ? Math.round((liveResultData.meters / liveResultData.manualCleaningRate) * 60) : 0;

    const comparisonRobotCostPerM2 = document.getElementById('comparisonRobotCostPerM2');
    const comparisonManualCostPerM2 = document.getElementById('comparisonManualCostPerM2');
    const comparisonRobotCleanTime = document.getElementById('comparisonRobotCleanTime');
    const comparisonManualCleanTime = document.getElementById('comparisonManualCleanTime');
    const comparisonRobotAvailability = document.getElementById('comparisonRobotAvailability');
    const comparisonManualAvailability = document.getElementById('comparisonManualAvailability');

    if (comparisonRobotCostPerM2) {
        comparisonRobotCostPerM2.textContent = '€' + costPerM2Robot.toFixed(2).replace('.', ',');
    }
    if (comparisonManualCostPerM2) {
        comparisonManualCostPerM2.textContent = '€' + costPerM2Manual.toFixed(2).replace('.', ',');
    }
    if (comparisonRobotCleanTime) {
        comparisonRobotCleanTime.textContent = cleanTimeRobot + ' minuten';
    }
    if (comparisonManualCleanTime) {
        comparisonManualCleanTime.textContent = cleanTimeManual + ' minuten';
    }
    if (comparisonRobotAvailability) {
        comparisonRobotAvailability.textContent = liveResultData.availabilityRobot.toFixed(1).replace('.', ',') + ' uur';
    }
    if (comparisonManualAvailability) {
        comparisonManualAvailability.textContent = metrics.availabilityManual.toFixed(1).replace('.', ',') + ' uur';
    }

    if (currentChartType === 'line' && myChart) {
        const annualManual = metrics.manualAnnualCost;
        const totalWithRobot = metrics.totalCostWithRobot;
        const months = [0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24];
        myChart.data.datasets[0].data = months.map(m => (totalWithRobot / 12) * m);
        myChart.data.datasets[1].data = months.map(m => (annualManual / 12) * m);
        myChart.update();
    }

    if (currentChartType === 'bar') {
        if (chartCosts) {
            chartCosts.data.datasets[0].data = [metrics.totalCostWithRobot, metrics.manualAnnualCost];
            chartCosts.update();
        }
        if (chartAvail) {
            chartAvail.data.datasets[0].data = [liveResultData.availabilityRobot, metrics.availabilityManual];
            chartAvail.update();
        }
        if (chartAbsence) {
            chartAbsence.data.datasets[0].data = [liveResultData.robotAbsence, metrics.manualAbsence];
            chartAbsence.update();
        }
    }
}

function initChart(type = 'line') {
    currentChartType = type;
    if (myChart) myChart.destroy();
    if (chartAvail) chartAvail.destroy();
    if (chartCosts) chartCosts.destroy();
    if (chartCleaning) chartCleaning.destroy();
    if (chartAbsence) chartAbsence.destroy();

    const lineContainer = document.getElementById('lineChartContainer');
    const barContainer = document.getElementById('barChartsContainer');
    const values = getSliderValues();
    const metrics = computeLiveMetrics(values);

    if (type === 'line') {
        lineContainer.classList.remove('hidden');
        barContainer.classList.add('hidden');

        const ctx = document.getElementById('roiChart').getContext('2d');
        const annualManual = metrics.manualAnnualCost;
        const totalWithRobot = metrics.totalCostWithRobot;
        const labels = ['M0', 'M2', 'M4', 'M6', 'M8', 'M10', 'M12', 'M14', 'M16', 'M18', 'M20', 'M22', 'M24'];
        const months = [0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24];

        myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Robot situatie',
                        data: months.map(m => (totalWithRobot / 12) * m),
                        borderColor: '#f87171',
                        backgroundColor: '#f8717122',
                        borderWidth: 3,
                        fill: true
                    },
                    {
                        label: 'Handmatig',
                        data: months.map(m => (annualManual / 12) * m),
                        borderColor: '#4f46e5',
                        backgroundColor: '#4f46e522',
                        borderWidth: 3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    } else {
        lineContainer.classList.add('hidden');
        barContainer.classList.remove('hidden');

        const chartPlugin = {
            afterDatasetsDraw(chart) {
                const {ctx, data, chartArea: {left, top, width, height}} = chart;
                ctx.save();
                data.datasets.forEach((datasetMeta, i) => {
                    const dataset = chart.getDatasetMeta(i);
                    dataset.data.forEach((datapoint, index) => {
                        const {x, y} = datapoint.getProps(['x', 'y'], true);
                        ctx.fillStyle = '#333';
                        ctx.font = 'bold 11px Arial';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                        const value = Math.round(datapoint.$context.raw * 10) / 10;
                        ctx.fillText(value, x, y - 8);
                    });
                });
                ctx.restore();
            }
        };

        const barOptions = {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 25
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        display: false
                    },
                    grid: {
                        display: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            barPercentage: 0.8
        };

        chartAvail = new Chart(document.getElementById('chartAvail'), {
            type: 'bar',
            data: {
                labels: ['Robot', 'Handm.'],
                datasets: [{
                    data: [liveResultData.availabilityRobot, metrics.availabilityManual],
                    backgroundColor: ['#f87171', '#4f46e5']
                }]
            },
            plugins: [chartPlugin],
            options: {
                ...barOptions,
                plugins: {
                    ...barOptions.plugins,
                    title: {
                        display: true,
                        text: 'Beschikbaarheid (uur)',
                        font: { size: 13, weight: 'bold' }
                    }
                }
            }
        });

        chartCosts = new Chart(document.getElementById('chartCosts'), {
            type: 'bar',
            data: {
                labels: ['Robot', 'Handm.'],
                datasets: [{
                    data: [metrics.totalCostWithRobot, metrics.manualAnnualCost],
                    backgroundColor: ['#f87171', '#4f46e5']
                }]
            },
            plugins: [chartPlugin],
            options: {
                ...barOptions,
                plugins: {
                    ...barOptions.plugins,
                    title: {
                        display: true,
                        text: 'Kosten per jaar (€)',
                        font: { size: 13, weight: 'bold' }
                    }
                }
            }
        });

        chartCleaning = new Chart(document.getElementById('chartCleaning'), {
            type: 'bar',
            data: {
                labels: ['Robot', 'Handm.'],
                datasets: [{
                    data: [liveResultData.robotCleaningRate, liveResultData.manualCleaningRate],
                    backgroundColor: ['#f87171', '#4f46e5']
                }]
            },
            plugins: [chartPlugin],
            options: {
                ...barOptions,
                plugins: {
                    ...barOptions.plugins,
                    title: {
                        display: true,
                        text: 'Schoonmaak (m²/uur)',
                        font: { size: 13, weight: 'bold' }
                    }
                }
            }
        });

        chartAbsence = new Chart(document.getElementById('chartAbsence'), {
            type: 'bar',
            data: {
                labels: ['Robot', 'Handm.'],
                datasets: [{
                    data: [liveResultData.robotAbsence, metrics.manualAbsence],
                    backgroundColor: ['#f87171', '#4f46e5']
                }]
            },
            plugins: [chartPlugin],
            options: {
                ...barOptions,
                plugins: {
                    ...barOptions.plugins,
                    title: {
                        display: true,
                        text: 'Afwezigheid (dagen/jaar)',
                        font: { size: 13, weight: 'bold' }
                    }
                }
            }
        });
    }

    refreshResults();
}

function updateChart(type) {
    initChart(type);
}

window.addEventListener('load', function() {
    refreshResults();
    initChart('line');
});
</script>
<?php
    return ob_get_clean();
}
?>