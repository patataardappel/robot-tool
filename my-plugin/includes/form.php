<?php
/**
 * Form shortcode for the plugin.
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function my_plugin_form_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'results_url' => '',
    ), $atts );

    $results_url = esc_url( $atts['results_url'] );

    ob_start();
    ?>
    <form action="<?php echo $results_url; ?>" method="post">
        <label>groote om schoon te maken in m2:
            <input type="number" name="meters" value="" required />
        </label><br />
        <label>gewenste inzet tijd minuten:
            <input type="number" name="minutes" value="" required />
        </label><br />
        <label>Type of floor:
            <select name="floor_type">
                <option value="hard">Hard floor</option>
                <option value="carpet">Carpet</option>
                <option value="tile">Tile</option>
            </select>
        </label><br />
        <button type="submit">Calculate</button>
    </form>
    <?php
    return ob_get_clean();
}