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



<div class="container-box">
    <form action="<?php echo $results_url; ?>" method="post" class="space-y-8">
        <div>
            <input type="radio" id="choice1" name="contact" value="email" />
            <label for="choice1">sporthal</label>

            <input type="radio" id="choice2" name="contact" value="phone" />
            <label for="choice2">supermarkt</label>

            <input type="radio" id="choice3" name="contact" value="mail" />
            <label for="choice3">kantoor</label>
        </div>
        <div>
            <label class="label-text">type ruimte:</label>
            <div class="select-wrapper">
                <select id="room_type" name="room" class="custom-input">
                    <option value="sporthal">sporthal</option>
                    <option value="supermarkt">supermarkt</option>
                    <option value="kantoor">kantoor</option>
                    <option value="hotel">hotel</option>
                </select>
            </div>
        </div>

        <div>
            <label class="label-text">Vloer type:</label>
            <div class="select-wrapper">
                <select id="floor_type" name="floor_type" class="custom-input">
                    <option value="hardvloer">hardvloer</option>
                    <option value="tapijt">tapijt</option>
                </select>
            </div>
        </div>

        <div>
            <label class="label-text">groote om schoon te maken in m²:</label>
            <input type="number" name="meters" class="custom-input" required />
        </div>

        <div>
            <label class="label-text">gewenste inzet tijd minuten:</label>
            <input type="number" name="minutes" class="custom-input" required />
        </div>

        <div>
            <label class="label-text">Hoeveel uur per week wordt er schoongemaakt?</label>
            <input type="number" step="0.1" name="cleaning_hours_per_week" class="custom-input" required />
        </div>

        <div>
            <label class="label-text">Wat is het uurloon</label>
            <input type="number" step="0.01" name="hourly_wage" class="custom-input" required />
        </div>

        <div>
            <label class="label-text">Hoeveel schoonmakers zijn er gemiddeld aanwezig?</label>
            <input type="number" step="1" min="1" name="employees" class="custom-input" required />
        </div>

        <div class="pt-8 flex justify-between items-center">
            <button type="button" onclick="goToStep1()"
                class="text-gray-400 font-medium hover:text-gray-600 transition-colors">Terug</button>
            <button class="btn-verder btn-active" type="submit">
                Verder
            </button>
        </div>
    </form>
</div>
<?php
    return ob_get_clean();
}