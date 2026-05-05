<?php

/**
 * Simple calculation/display helper for the plugin.
 *
 * @param float $meters       Size in square meters.
 * @param float $minutes      Time in minutes.
 * @param string $floor_type  Type of floor.
 * @return string             HTML output for the calculated result.
 */
function my_plugin_calculate_display( $meters, $minutes, $floor_type = '' ) {
    // dynamic items loaded from settings; fallback to defaults if none provided
    $options = get_option( 'my_plugin_options', array() );
    if ( ! is_array( $options ) || empty( $options ) ) {
        $options = array(
            array( 'name' => 'Default Item A', 'meters' => 1100, 'image' => '' ),
            array( 'name' => 'Default Item B', 'meters' => 2400, 'image' => '' ),
        );
    }

    // compute base numeric value and derive rate per hour
    $numericResult = floatval( $meters );
    $rate_per_hour = 0;
    if ( $numericResult > 0 && floatval( $minutes ) > 0 ) {
        // multiply meters by minutes and convert to meters-per-hour
        $rate_per_hour = ( $numericResult / floatval( $minutes ) ) * 60;
    }

    // Apply environmental factors
    $floor_multiplier = 1.0;
    switch ( $floor_type ) {
        case 'carpet':
            $floor_multiplier = 1.2;
            break;
        case 'tile':
            $floor_multiplier = 1.1;
            break;
        case 'hard':
        default:
            $floor_multiplier = 1.0;
            break;
    }

    $adjusted_rate = $rate_per_hour * $floor_multiplier;

    $selected_item = null;
    if ( $adjusted_rate > 0 ) {
        foreach ( $options as $item ) {
            if ( ! isset( $item['meters'] ) ) {
                continue;
            }

            if ( $adjusted_rate < floatval( $item['meters'] ) ) {
                $selected_item = $item;
                break;
            }
        }
    }

    ob_start();
    ?>
    <div class="result-display">
        <?php if ( $selected_item ) : ?>
            <div class="result-text">
                <p>Selected: <?php echo esc_html( $selected_item['name'] ); ?></p>
                <p>Base Rate (m²/h): <?php echo esc_html( round( $rate_per_hour, 2 ) ); ?></p>
                <p>Adjusted Rate (m²/h): <?php echo esc_html( round( $adjusted_rate, 2 ) ); ?></p>
                <p>Item threshold (m²/h): <?php echo esc_html( round( floatval( $selected_item['meters'] ), 2 ) ); ?></p>
                <?php if ( ! empty( $selected_item['image'] ) ) : ?>
                    <p><img src="<?php echo esc_url( $selected_item['image'] ); ?>" alt="<?php echo esc_attr( $selected_item['name'] ); ?>" style="max-width:100%;height:auto;" /></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
