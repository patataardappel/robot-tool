<?php
/**
 * Plugin Name:       My Plugin
 * Plugin URI:        https://example.com/my-plugin
 * Description:       A brief description of what my plugin does.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPLv2 or later
 * Text Domain:       my-plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// load helper logic from separate file
require_once __DIR__ . '/includes/calculator.php';
require_once __DIR__ . '/includes/form.php';
require_once __DIR__ . '/includes/results.php';

// enqueue plugin styles and frontend scripts
add_action( 'wp_enqueue_scripts', 'my_plugin_enqueue_assets' );
function my_plugin_enqueue_assets() {
    wp_enqueue_style(
        'my-plugin-styles',
        plugins_url( 'assets/styling/style.css', __FILE__ ),
        array(),
        filemtime( plugin_dir_path( __FILE__ ) . 'assets/styling/style.css' )
    );

    wp_enqueue_script(
        'my-plugin-chartjs',
        'https://cdn.jsdelivr.net/npm/chart.js',
        array(),
        '4.4.0',
        true
    );
}

// add shortcodes for form and results
add_shortcode( 'my_plugin_form', 'my_plugin_form_shortcode' );
add_shortcode( 'my_plugin_results', 'my_plugin_results_shortcode' );

// register a simple block that renders text and a button
add_action( 'init', 'my_plugin_register_block' );
function my_plugin_register_block() {
    wp_register_script(
        'my-plugin-block',
        plugins_url( 'assets/block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'assets/block.js' )
    );

    register_block_type( 'my-plugin/my-block', array(
        'editor_script'   => 'my-plugin-block',
        'render_callback' => 'my_plugin_render_block',
    ) );
}

// add settings page so user can configure options
add_action( 'admin_menu', 'my_plugin_add_admin_menu' );
add_action( 'admin_init', 'my_plugin_settings_init' );

function my_plugin_add_admin_menu() {
    add_options_page(
        'My Plugin Settings',
        'My Plugin',
        'manage_options',
        'my-plugin',
        'my_plugin_options_page'
    );
}

function my_plugin_settings_init() {
    register_setting( 'my_plugin', 'my_plugin_options', array(
        'sanitize_callback' => 'my_plugin_sanitize_options',
        'default'           => array(),
    ) );

    register_setting( 'my_plugin', 'my_plugin_products', array(
        'sanitize_callback' => 'my_plugin_sanitize_products',
        'default'           => array(),
    ) );
}

function my_plugin_sanitize_options( $input ) {
    $options = array();

    // Expect an array of cleaning robots, each with name, meters, image and additional stats.
    if ( ! is_array( $input ) ) {
        return $options;
    }

    foreach ( $input as $item ) {
        if ( ! is_array( $item ) ) {
            continue;
        }

        $name                        = isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '';
        $meters                      = isset( $item['meters'] ) ? floatval( $item['meters'] ) : 0;
        $price                       = 0;
        if ( isset( $item['price'] ) ) {
            $price = floatval( $item['price'] );
        } elseif ( isset( $item['euro'] ) ) {
            $price = floatval( $item['euro'] );
        }
        $price_month                 = isset( $item['price_month'] ) ? floatval( $item['price_month'] ) : 0;
        $payment_period_years        = isset( $item['payment_period_years'] ) ? floatval( $item['payment_period_years'] ) : 0;
        $image                       = isset( $item['image'] ) ? esc_url_raw( $item['image'] ) : '';
        $cleaning_functions          = isset( $item['cleaning_functions'] ) ? sanitize_text_field( $item['cleaning_functions'] ) : '';
        $dimensions_width            = isset( $item['dimensions_width'] ) ? floatval( $item['dimensions_width'] ) : 0;
        $dimensions_depth            = isset( $item['dimensions_depth'] ) ? floatval( $item['dimensions_depth'] ) : 0;
        $dimensions_height           = isset( $item['dimensions_height'] ) ? floatval( $item['dimensions_height'] ) : 0;
        $weight                      = isset( $item['weight'] ) ? floatval( $item['weight'] ) : 0;
        $battery_voltage             = isset( $item['battery_voltage'] ) ? floatval( $item['battery_voltage'] ) : 0;
        $battery_capacity            = isset( $item['battery_capacity'] ) ? floatval( $item['battery_capacity'] ) : 0;
        $charge_time                 = isset( $item['charge_time'] ) ? floatval( $item['charge_time'] ) : 0;
        $max_run_time                = isset( $item['max_run_time'] ) ? floatval( $item['max_run_time'] ) : 0;
        $cleaning_width              = isset( $item['cleaning_width'] ) ? sanitize_text_field( $item['cleaning_width'] ) : '';
        $total_capacity_per_use      = isset( $item['total_capacity_per_use'] ) ? floatval( $item['total_capacity_per_use'] ) : 0;
        $clean_water_tank_capacity   = isset( $item['clean_water_tank_capacity'] ) ? floatval( $item['clean_water_tank_capacity'] ) : 0;
        $dirty_water_tank_capacity   = isset( $item['dirty_water_tank_capacity'] ) ? floatval( $item['dirty_water_tank_capacity'] ) : 0;
        $dust_bag_capacity           = isset( $item['dust_bag_capacity'] ) ? floatval( $item['dust_bag_capacity'] ) : 0;
        $waste_container_capacity    = isset( $item['waste_container_capacity'] ) ? floatval( $item['waste_container_capacity'] ) : 0;

        if ( $name === '' ) {
            continue;
        }

        $options[] = array(
            'name'                      => $name,
            'meters'                    => $meters,
            'price'                     => $price,
            'price_month'               => $price_month,
            'payment_period_years'      => $payment_period_years,
            'image'                     => $image,
            'cleaning_functions'        => $cleaning_functions,
            'dimensions_width'          => $dimensions_width,
            'dimensions_depth'          => $dimensions_depth,
            'dimensions_height'         => $dimensions_height,
            'weight'                    => $weight,
            'battery_voltage'           => $battery_voltage,
            'battery_capacity'          => $battery_capacity,
            'charge_time'               => $charge_time,
            'max_run_time'              => $max_run_time,
            'cleaning_width'            => $cleaning_width,
            'total_capacity_per_use'    => $total_capacity_per_use,
            'clean_water_tank_capacity' => $clean_water_tank_capacity,
            'dirty_water_tank_capacity' => $dirty_water_tank_capacity,
            'dust_bag_capacity'         => $dust_bag_capacity,
            'waste_container_capacity'  => $waste_container_capacity,
        );
    }

    return $options;
}

function my_plugin_sanitize_products( $input ) {
    $products = array();

    // Expect an array of products, each with name, price and image.
    if ( ! is_array( $input ) ) {
        return $products;
    }

    foreach ( $input as $product ) {
        if ( ! is_array( $product ) ) {
            continue;
        }

        $name   = isset( $product['name'] ) ? sanitize_text_field( $product['name'] ) : '';
        $price  = isset( $product['price'] ) ? floatval( $product['price'] ) : 0;
        $image  = isset( $product['image'] ) ? esc_url_raw( $product['image'] ) : '';

        if ( $name === '' ) {
            continue;
        }

        $products[] = array(
            'name'   => $name,
            'price'  => $price,
            'image'  => $image,
        );
    }

    return $products;
}

function my_plugin_options_page() {
    ?>
    <div class="wrap">
        <h1>My Plugin Settings</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'my_plugin' );
            wp_enqueue_media();

            $options = get_option( 'my_plugin_options', array() );
            if ( ! is_array( $options ) ) {
                $options = array();
            }
            ?>

            <div id="my-plugin-cleaning-robots">
                <?php foreach ( $options as $index => $item ) : ?>
                    <div class="my-plugin-cleaning-robot">
                        <h4>Cleaning robot <?php echo ( $index + 1 ); ?></h4>
                        <p>
                            <label>Name:
                                <input type="text" name="my_plugin_options[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $item['name'] ?? '' ); ?>" />
                            </label>
                        </p>
                        <p>
                            <label>Price:
                                <input type="number" step="any" name="my_plugin_options[<?php echo $index; ?>][price]" value="<?php echo esc_attr( $item['price'] ?? '' ); ?>" />
                            </label>
                        </p>
                        <p>
                            <label>Monthly Price:
                                <input type="number" step="any" name="my_plugin_options[<?php echo $index; ?>][price_month]" value="<?php echo esc_attr( $item['price_month'] ?? '' ); ?>" />
                            </label>
                        </p>
                        <p>
                            <label>Payment period (years):
                                <input type="number" step="1" min="0" name="my_plugin_options[<?php echo $index; ?>][payment_period_years]" value="<?php echo esc_attr( $item['payment_period_years'] ?? '' ); ?>" />
                            </label>
                        </p>
                        <p>
                            <label>Cleaning efficiency (m²/h):
                                <input type="number" step="any" name="my_plugin_options[<?php echo $index; ?>][meters]" value="<?php echo esc_attr( $item['meters'] ?? '' ); ?>" />
                            </label>
                        </p>
                        <p>
                            <label>Cleaning functions:
                                <input type="text" name="my_plugin_options[<?php echo $index; ?>][cleaning_functions]" value="<?php echo esc_attr( $item['cleaning_functions'] ?? '' ); ?>" placeholder="Vegen, Stofzuigen, Dweilen, Stofwissen" />
                            </label>
                        </p>
                        <p>
                            <label>Dimensions (Width x Depth x Height) in mm:
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][dimensions_width]" value="<?php echo esc_attr( $item['dimensions_width'] ?? '' ); ?>" placeholder="616" /> x
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][dimensions_depth]" value="<?php echo esc_attr( $item['dimensions_depth'] ?? '' ); ?>" placeholder="550" /> x
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][dimensions_height]" value="<?php echo esc_attr( $item['dimensions_height'] ?? '' ); ?>" placeholder="690" /> mm
                            </label>
                        </p>
                        <p>
                            <label>Weight (kg):
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][weight]" value="<?php echo esc_attr( $item['weight'] ?? '' ); ?>" placeholder="70" />
                            </label>
                        </p>
                        <p>
                            <label>Battery (Voltage V / Capacity Ah):
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][battery_voltage]" value="<?php echo esc_attr( $item['battery_voltage'] ?? '' ); ?>" placeholder="25.6" /> V /
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][battery_capacity]" value="<?php echo esc_attr( $item['battery_capacity'] ?? '' ); ?>" placeholder="50" /> Ah
                            </label>
                        </p>
                        <p>
                            <label>Charge time (hours):
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][charge_time]" value="<?php echo esc_attr( $item['charge_time'] ?? '' ); ?>" placeholder="2" />
                            </label>
                        </p>
                        <p>
                            <label>Maximum run time (hours):
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][max_run_time]" value="<?php echo esc_attr( $item['max_run_time'] ?? '' ); ?>" placeholder="5" />
                            </label>
                        </p>
                        <p>
                            <label>Cleaning width (m²):
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][cleaning_width]" value="<?php echo esc_attr( $item['cleaning_width'] ?? '' ); ?>" placeholder="560" />
                            </label>
                        </p>
                        <p>
                            <label>Total capacity per use (m²):
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][total_capacity_per_use]" value="<?php echo esc_attr( $item['total_capacity_per_use'] ?? '' ); ?>" placeholder="4500" />
                            </label>
                        </p>
                        <p>
                            <label>Clean water tank capacity (liters):
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][clean_water_tank_capacity]" value="<?php echo esc_attr( $item['clean_water_tank_capacity'] ?? '' ); ?>" placeholder="16" />
                            </label>
                        </p>
                        <p>
                            <label>Dirty water tank capacity (liters):
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][dirty_water_tank_capacity]" value="<?php echo esc_attr( $item['dirty_water_tank_capacity'] ?? '' ); ?>" placeholder="14" />
                            </label>
                        </p>
                        <p>
                            <label>Dust bag capacity (liters):
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][dust_bag_capacity]" value="<?php echo esc_attr( $item['dust_bag_capacity'] ?? '' ); ?>" placeholder="8" />
                            </label>
                        </p>
                        <p>
                            <label>Waste container capacity (liters):
                                <input type="number" step="any" min="0" name="my_plugin_options[<?php echo $index; ?>][waste_container_capacity]" value="<?php echo esc_attr( $item['waste_container_capacity'] ?? '' ); ?>" placeholder="0.7" />
                            </label>
                        </p>
                        <p>
                            <label>Image URL:
                                <input type="text" class="my-plugin-image-url" name="my_plugin_options[<?php echo $index; ?>][image]" value="<?php echo esc_attr( $item['image'] ?? '' ); ?>" />
                            </label>
                            <button type="button" class="button my-plugin-select-image">Select Image</button>
                        </p>
                        <p>
                            <button type="button" class="button my-plugin-remove-cleaning-robot">Remove cleaning robot</button>
                        </p>
                        <hr />
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="button button-primary" id="my-plugin-add-cleaning-robot">Add cleaning robot</button>

            <h2>Products</h2>
            <?php
            $products = get_option( 'my_plugin_products', array() );
            if ( ! is_array( $products ) ) {
                $products = array();
            }
            ?>

            <div id="my-plugin-products">
                <?php foreach ( $products as $index => $product ) : ?>
                    <div class="my-plugin-product">
                        <h4>Product <?php echo ( $index + 1 ); ?></h4>
                        <p>
                            <label>Name:
                                <input type="text" name="my_plugin_products[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $product['name'] ?? '' ); ?>" />
                            </label>
                        </p>
                        <p>
                            <label>Price:
                                <input type="number" step="any" name="my_plugin_products[<?php echo $index; ?>][price]" value="<?php echo esc_attr( $product['price'] ?? '' ); ?>" />
                            </label>
                        </p>
                        <p>
                            <label>Image URL:
                                <input type="text" class="my-plugin-image-url" name="my_plugin_products[<?php echo $index; ?>][image]" value="<?php echo esc_attr( $product['image'] ?? '' ); ?>" />
                            </label>
                            <button type="button" class="button my-plugin-select-image">Select Image</button>
                        </p>
                        <p>
                            <button type="button" class="button my-plugin-remove-product">Remove product</button>
                        </p>
                        <hr />
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="button button-primary" id="my-plugin-add-product">Add product</button>

            <script>
            (function(){
                var container = document.getElementById('my-plugin-cleaning-robots');
                var addButton = document.getElementById('my-plugin-add-cleaning-robot');

                function reIndexItems() {
                    var items = container.querySelectorAll('.my-plugin-cleaning-robot');
                    items.forEach(function(item, idx){
                        item.querySelector('h4').textContent = 'Cleaning robot ' + (idx + 1);
                        item.querySelectorAll('input').forEach(function(input){
                            if ( input.name.indexOf('[name]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][name]';
                            } else if ( input.name.indexOf('[price]') !== -1 || input.name.indexOf('[euro]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][price]';
                            } else if ( input.name.indexOf('[price_month]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][price_month]';
                            } else if ( input.name.indexOf('[payment_period_years]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][payment_period_years]';
                            } else if ( input.name.indexOf('[meters]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][meters]';
                            } else if ( input.name.indexOf('[cleaning_functions]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][cleaning_functions]';
                            } else if ( input.name.indexOf('[dimensions_width]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][dimensions_width]';
                            } else if ( input.name.indexOf('[dimensions_depth]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][dimensions_depth]';
                            } else if ( input.name.indexOf('[dimensions_height]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][dimensions_height]';
                            } else if ( input.name.indexOf('[weight]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][weight]';
                            } else if ( input.name.indexOf('[battery_voltage]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][battery_voltage]';
                            } else if ( input.name.indexOf('[battery_capacity]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][battery_capacity]';
                            } else if ( input.name.indexOf('[charge_time]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][charge_time]';
                            } else if ( input.name.indexOf('[max_run_time]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][max_run_time]';
                            } else if ( input.name.indexOf('[cleaning_width]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][cleaning_width]';
                            } else if ( input.name.indexOf('[total_capacity_per_use]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][total_capacity_per_use]';
                            } else if ( input.name.indexOf('[clean_water_tank_capacity]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][clean_water_tank_capacity]';
                            } else if ( input.name.indexOf('[dirty_water_tank_capacity]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][dirty_water_tank_capacity]';
                            } else if ( input.name.indexOf('[dust_bag_capacity]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][dust_bag_capacity]';
                            } else if ( input.name.indexOf('[waste_container_capacity]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][waste_container_capacity]';
                            } else if ( input.name.indexOf('[image]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][image]';
                            }
                        });
                    });
                }

                function bindItemEvents( item ) {
                    var removeBtn = item.querySelector('.my-plugin-remove-cleaning-robot');
                    var selectBtn = item.querySelector('.my-plugin-select-image');

                    removeBtn.addEventListener('click', function(){
                        item.remove();
                        reIndexItems();
                    });

                    selectBtn.addEventListener('click', function( e ){
                        e.preventDefault();

                        if ( typeof wp !== 'undefined' && wp.media ) {
                            var frame = wp.media({
                                title: 'Select Image',
                                button: { text: 'Use this image' },
                                multiple: false
                            });

                            frame.on('select', function() {
                                var attachment = frame.state().get('selection').first().toJSON();
                                item.querySelector('.my-plugin-image-url').value = attachment.url;
                            });

                            frame.open();
                        } else {
                            alert('Media uploader not available.');
                        }
                    });
                }

                function buildItemHtml( index ) {
                    return (
                        '<div class="my-plugin-cleaning-robot">' +
                            '<h4>Cleaning robot ' + (index + 1) + '</h4>' +
                            '<p><label>Name: <input type="text" name="my_plugin_options[' + index + '][name]" value="" /></label></p>' +
                            '<p><label>Price: <input type="number" step="any" name="my_plugin_options[' + index + '][price]" value="" /></label></p>' +
                            '<p><label>Monthly Price: <input type="number" step="any" name="my_plugin_options[' + index + '][price_month]" value="" /></label></p>' +
                            '<p><label>Payment period (years): <input type="number" step="1" min="0" name="my_plugin_options[' + index + '][payment_period_years]" value="" /></label></p>' +
                            '<p><label>Cleaning efficiency (m²/h): <input type="number" step="any" name="my_plugin_options[' + index + '][meters]" value="" /></label></p>' +
                            '<p><label>Cleaning functions: <input type="text" name="my_plugin_options[' + index + '][cleaning_functions]" value="" placeholder="Vegen, Stofzuigen, Dweilen, Stofwissen" /></label></p>' +
                            '<p><label>Dimensions (Width x Depth x Height) in mm: <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][dimensions_width]" value="" placeholder="616" /> x <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][dimensions_depth]" value="" placeholder="550" /> x <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][dimensions_height]" value="" placeholder="690" /> mm</label></p>' +
                            '<p><label>Weight (kg): <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][weight]" value="" placeholder="70" /></label></p>' +
                            '<p><label>Battery (Voltage V / Capacity Ah): <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][battery_voltage]" value="" placeholder="25.6" /> V / <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][battery_capacity]" value="" placeholder="50" /> Ah</label></p>' +
                            '<p><label>Charge time (hours): <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][charge_time]" value="" placeholder="2" /></label></p>' +
                            '<p><label>Maximum run time (hours): <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][max_run_time]" value="" placeholder="5" /></label></p>' +
                            '<p><label>Cleaning width: <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][cleaning_width]" value="" placeholder="560" /></label></p>' +
                            '<p><label>Total capacity per use (m²): <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][total_capacity_per_use]" value="" placeholder="4500" /></label></p>' +
                            '<p><label>Clean water tank capacity (liters): <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][clean_water_tank_capacity]" value="" placeholder="16" /></label></p>' +
                            '<p><label>Dirty water tank capacity (liters): <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][dirty_water_tank_capacity]" value="" placeholder="14" /></label></p>' +
                            '<p><label>Dust bag capacity (liters): <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][dust_bag_capacity]" value="" placeholder="8" /></label></p>' +
                            '<p><label>Waste container capacity (liters): <input type="number" step="any" min="0" name="my_plugin_options[' + index + '][waste_container_capacity]" value="" placeholder="0.7" /></label></p>' +
                            '<p><label>Image URL: <input type="text" class="my-plugin-image-url" name="my_plugin_options[' + index + '][image]" value="" /></label>' +
                            ' <button type="button" class="button my-plugin-select-image">Select Image</button></p>' +
                            '<p><button type="button" class="button my-plugin-remove-cleaning-robot">Remove cleaning robot</button></p>' +
                            '<hr />' +
                        '</div>'
                    );
                }

                container.querySelectorAll('.my-plugin-cleaning-robot').forEach(bindItemEvents);

                addButton.addEventListener('click', function(){
                    var idx = container.querySelectorAll('.my-plugin-cleaning-robot').length;
                    var temp = document.createElement('div');
                    temp.innerHTML = buildItemHtml( idx );
                    var newItem = temp.firstElementChild;
                    container.appendChild( newItem );
                    bindItemEvents( newItem );
                });

                // Products handling
                var productContainer = document.getElementById('my-plugin-products');
                var addProductButton = document.getElementById('my-plugin-add-product');

                function reIndexProducts() {
                    var products = productContainer.querySelectorAll('.my-plugin-product');
                    products.forEach(function(product, idx){
                        product.querySelector('h4').textContent = 'Product ' + (idx + 1);
                        product.querySelectorAll('input').forEach(function(input){
                            if ( input.name.indexOf('[name]') !== -1 ) {
                                input.name = 'my_plugin_products[' + idx + '][name]';
                            } else if ( input.name.indexOf('[price]') !== -1 ) {
                                input.name = 'my_plugin_products[' + idx + '][price]';
                            } else if ( input.name.indexOf('[image]') !== -1 ) {
                                input.name = 'my_plugin_products[' + idx + '][image]';
                            }
                        });
                    });
                }

                function bindProductEvents( product ) {
                    var removeBtn = product.querySelector('.my-plugin-remove-product');
                    var selectBtn = product.querySelector('.my-plugin-select-image');

                    removeBtn.addEventListener('click', function(){
                        product.remove();
                        reIndexProducts();
                    });

                    selectBtn.addEventListener('click', function( e ){
                        e.preventDefault();

                        if ( typeof wp !== 'undefined' && wp.media ) {
                            var frame = wp.media({
                                title: 'Select Image',
                                button: { text: 'Use this image' },
                                multiple: false
                            });

                            frame.on('select', function() {
                                var attachment = frame.state().get('selection').first().toJSON();
                                product.querySelector('.my-plugin-image-url').value = attachment.url;
                            });

                            frame.open();
                        } else {
                            alert('Media uploader not available.');
                        }
                    });
                }

                function buildProductHtml( index ) {
                    return (
                        '<div class="my-plugin-product">' +
                            '<h4>Product ' + (index + 1) + '</h4>' +
                            '<p><label>Name: <input type="text" name="my_plugin_products[' + index + '][name]" value="" /></label></p>' +
                            '<p><label>Price: <input type="number" step="any" name="my_plugin_products[' + index + '][price]" value="" /></label></p>' +
                            '<p><label>Image URL: <input type="text" class="my-plugin-image-url" name="my_plugin_products[' + index + '][image]" value="" /></label>' +
                            ' <button type="button" class="button my-plugin-select-image">Select Image</button></p>' +
                            '<p><button type="button" class="button my-plugin-remove-product">Remove product</button></p>' +
                            '<hr />' +
                        '</div>'
                    );
                }

                productContainer.querySelectorAll('.my-plugin-product').forEach(bindProductEvents);

                addProductButton.addEventListener('click', function(){
                    var idx = productContainer.querySelectorAll('.my-plugin-product').length;
                    var temp = document.createElement('div');
                    temp.innerHTML = buildProductHtml( idx );
                    var newProduct = temp.firstElementChild;
                    productContainer.appendChild( newProduct );
                    bindProductEvents( newProduct );
                });
            })();
            </script>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// AJAX handler for calculator
add_action( 'wp_ajax_my_plugin_calculate', 'my_plugin_ajax_calculate' );
add_action( 'wp_ajax_nopriv_my_plugin_calculate', 'my_plugin_ajax_calculate' );
function my_plugin_ajax_calculate() {
    $meters  = isset( $_POST['meters'] ) ? floatval( $_POST['meters'] ) : 0;
    $minutes = isset( $_POST['minutes'] ) ? floatval( $_POST['minutes'] ) : 0;
    $floor_type = isset( $_POST['floor_type'] ) ? sanitize_text_field( $_POST['floor_type'] ) : '';
    echo my_plugin_calculate_display( $meters, $minutes, $floor_type );
    wp_die();
}

function my_plugin_render_block( $attributes ) {
    // output form plus container for results
    ob_start();
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var btn = document.getElementById('my-plugin-calc-btn');
        if (btn) {
            btn.addEventListener('click', function(){
                var meters = document.getElementById('my-plugin-input-meters').value;
                var minutes = document.getElementById('my-plugin-input-minutes').value;
                var floorType = document.getElementById('my-plugin-input-floor-type').value;
                var data = new FormData();
                data.append('action','my_plugin_calculate');
                data.append('meters', meters);
                data.append('minutes', minutes);
                data.append('floor_type', floorType);
                fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: data
                }).then(function(r){ return r.text(); })
                .then(function(html){
                    document.getElementById('my-plugin-result').innerHTML = html;
                });
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
