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
}

function my_plugin_sanitize_options( $input ) {
    $options = array();

    // Expect an array of items, each with name, meters and image.
    if ( ! is_array( $input ) ) {
        return $options;
    }

    foreach ( $input as $item ) {
        if ( ! is_array( $item ) ) {
            continue;
        }

        $name   = isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '';
        $meters = isset( $item['meters'] ) ? floatval( $item['meters'] ) : 0;
        $image  = isset( $item['image'] ) ? esc_url_raw( $item['image'] ) : '';

        if ( $name === '' ) {
            continue;
        }

        $options[] = array(
            'name'   => $name,
            'meters' => $meters,
            'image'  => $image,
        );
    }

    return $options;
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

            <div id="my-plugin-items">
                <?php foreach ( $options as $index => $item ) : ?>
                    <div class="my-plugin-item">
                        <h4>Item <?php echo ( $index + 1 ); ?></h4>
                        <p>
                            <label>Name:
                                <input type="text" name="my_plugin_options[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $item['name'] ?? '' ); ?>" />
                            </label>
                        </p>
                        <p>
                            <label>Meters:
                                <input type="number" step="any" name="my_plugin_options[<?php echo $index; ?>][meters]" value="<?php echo esc_attr( $item['meters'] ?? '' ); ?>" />
                            </label>
                        </p>
                        <p>
                            <label>Image URL:
                                <input type="text" class="my-plugin-image-url" name="my_plugin_options[<?php echo $index; ?>][image]" value="<?php echo esc_attr( $item['image'] ?? '' ); ?>" />
                            </label>
                            <button type="button" class="button my-plugin-select-image">Select Image</button>
                        </p>
                        <p>
                            <button type="button" class="button my-plugin-remove-item">Remove item</button>
                        </p>
                        <hr />
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="button button-primary" id="my-plugin-add-item">Add item</button>

            <script>
            (function(){
                var container = document.getElementById('my-plugin-items');
                var addButton = document.getElementById('my-plugin-add-item');

                function reIndexItems() {
                    var items = container.querySelectorAll('.my-plugin-item');
                    items.forEach(function(item, idx){
                        item.querySelector('h4').textContent = 'Item ' + (idx + 1);
                        item.querySelectorAll('input').forEach(function(input){
                            if ( input.name.indexOf('[name]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][name]';
                            } else if ( input.name.indexOf('[meters]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][meters]';
                            } else if ( input.name.indexOf('[image]') !== -1 ) {
                                input.name = 'my_plugin_options[' + idx + '][image]';
                            }
                        });
                    });
                }

                function bindItemEvents( item ) {
                    var removeBtn = item.querySelector('.my-plugin-remove-item');
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
                        '<div class="my-plugin-item">' +
                            '<h4>Item ' + (index + 1) + '</h4>' +
                            '<p><label>Name: <input type="text" name="my_plugin_options[' + index + '][name]" value="" /></label></p>' +
                            '<p><label>Meters: <input type="number" step="any" name="my_plugin_options[' + index + '][meters]" value="" /></label></p>' +
                            '<p><label>Image URL: <input type="text" class="my-plugin-image-url" name="my_plugin_options[' + index + '][image]" value="" /></label>' +
                            ' <button type="button" class="button my-plugin-select-image">Select Image</button></p>' +
                            '<p><button type="button" class="button my-plugin-remove-item">Remove item</button></p>' +
                            '<hr />' +
                        '</div>'
                    );
                }

                container.querySelectorAll('.my-plugin-item').forEach(bindItemEvents);

                addButton.addEventListener('click', function(){
                    var idx = container.querySelectorAll('.my-plugin-item').length;
                    var temp = document.createElement('div');
                    temp.innerHTML = buildItemHtml( idx );
                    var newItem = temp.firstElementChild;
                    container.appendChild( newItem );
                    bindItemEvents( newItem );
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
    echo my_plugin_calculate_display( $meters, $minutes );
    wp_die();
}

function my_plugin_render_block( $attributes ) {
    // output form plus container for results
    ob_start();
    ?>
    <div class="my-plugin-block">
        <label>groote om schoon te maken in m2:
            <input type="number" id="my-plugin-input-meters" value="" />
        </label><br />
        <label>gewenste inzet tijd minuten:
            <input type="number" id="my-plugin-input-minutes" value="" />
        </label><br />
        <button type="button" id="my-plugin-calc-btn">Calculate</button>
        <div id="my-plugin-result"></div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var btn = document.getElementById('my-plugin-calc-btn');
        if (btn) {
            btn.addEventListener('click', function(){
                var meters = document.getElementById('my-plugin-input-meters').value;
                var minutes = document.getElementById('my-plugin-input-minutes').value;
                var data = new FormData();
                data.append('action','my_plugin_calculate');
                data.append('meters', meters);
                data.append('minutes', minutes);
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
