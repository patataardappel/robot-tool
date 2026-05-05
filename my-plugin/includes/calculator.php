<?php

/**
 * Simple calculation/display helper for the plugin.
 *
 * @param float $meters    Size in square meters.
 * @param float $minutes   Time in minutes.
 * @return string          HTML output for the calculated result.
 */
function my_plugin_calculate_display( $meters, $minutes ) {
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
        $rate_per_hour = ( $numericResult * floatval( $minutes ) ) / floatval( $minutes );
        // actually multiplying then dividing by same value simplifies to just meters,
        // but assuming original intent: (meters / minutes) * 60 for m²/h
        $rate_per_hour = ( $numericResult / floatval( $minutes ) ) * 60;
    }

    $selected_item = null;
    if ( $rate_per_hour > 0 ) {
        foreach ( $options as $item ) {
            if ( ! isset( $item['meters'] ) ) {
                continue;
            }

            if ( $rate_per_hour < floatval( $item['meters'] ) ) {
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
                <p>Rate (m²/h): <?php echo esc_html( round( $rate_per_hour, 2 ) ); ?></p>
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
