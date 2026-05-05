<?php
/**
 * Results shortcode for the plugin.
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function my_plugin_results_shortcode( $atts ) {
    $key = isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '';

    if ( ! empty( $key ) ) {
        $data = get_transient( 'my_plugin_result_' . $key );
        if ( $data ) {
            return $data;
        }
    }

    if ( isset( $_POST['meters'] ) ) {
        $meters = floatval( $_POST['meters'] );
        $minutes = floatval( $_POST['minutes'] );
        $floor_type = sanitize_text_field( $_POST['floor_type'] );

        $result_html = my_plugin_calculate_display( $meters, $minutes, $floor_type );

        // Store in transient for 1 hour
        $key = wp_generate_password( 12, false );
        set_transient( 'my_plugin_result_' . $key, $result_html, HOUR_IN_SECONDS );

        // Redirect to add key to URL
        $current_url = add_query_arg( 'key', $key );
        wp_redirect( $current_url );
        exit;
    }

    return '<p>No results to display.</p>';
}