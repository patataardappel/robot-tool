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
    <div class="container-box">
        <h2 class="selection-title">Type soort ruimte</h2>

        <div class="room-selection-grid">
            <label class="room-option">
                <input type="radio" name="room_type" value="kantoor" class="room-radio" onchange="enableNextButton()">
                <div class="room-card-content">
                    <div class="image-container">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/bd/Test.svg/960px-Test.svg.png?utm_source=commons.wikimedia.org&utm_campaign=index&utm_content=thumbnail"
                            alt="Supermarkt">
                    </div>
                    <p class="room-label">Kantoor</p>
                </div>
            </label>

            <label class="room-option">
                <input type="radio" name="room_type" value="sportzaal" class="room-radio" onchange="enableNextButton()">
                <div class="room-card-content">
                    <div class="image-container">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/bd/Test.svg/960px-Test.svg.png?utm_source=commons.wikimedia.org&utm_campaign=index&utm_content=thumbnail"
                            alt="Sportzaal">
                    </div>
                    <p class="room-label">Sportzaal</p>
                </div>
            </label>

            <label class="room-option">
                <input type="radio" name="room_type" value="supermarkt" class="room-radio"
                    onchange="enableNextButton()">
                <div class="room-card-content">
                    <div class="image-container">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/bd/Test.svg/960px-Test.svg.png?utm_source=commons.wikimedia.org&utm_campaign=index&utm_content=thumbnail"
                            alt="Supermarkt">
                    </div>
                    <p class="room-label">Supermarkt</p>
                </div>
            </label>

            <label class="room-option">
                <input type="radio" name="room_type" value="hotel" class="room-radio" onchange="enableNextButton()">
                <div class="room-card-content">
                    <div class="image-container">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/bd/Test.svg/960px-Test.svg.png?utm_source=commons.wikimedia.org&utm_campaign=index&utm_content=thumbnail"
                            alt="Supermarkt">
                    </div>
                    <p class="room-label">Hotel</p>
                </div>
            </label>

            <label class="room-option">
                <input type="radio" name="room_type" value="gym" class="room-radio" onchange="enableNextButton()">
                <div class="room-card-content">
                    <div class="image-container">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/bd/Test.svg/960px-Test.svg.png?utm_source=commons.wikimedia.org&utm_campaign=index&utm_content=thumbnail"
                            alt="Supermarkt">
                    </div>
                    <p class="room-label">Gym</p>
                </div>
            </label>
        </div>
    </div>
    <form action="<?php echo $results_url; ?>" method="post" class="space-y-8">
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