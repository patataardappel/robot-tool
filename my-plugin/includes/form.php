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
    <form action="<?php echo $results_url; ?>" method="post" class="space-y-8" onsubmit="return validateRoomType()">
        <div class="intro-container form-step step-0 active">
            <div class="intro-image">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT_biwzHVUGosm3pg1rpUtrF_NmppNeEdXppA&s" alt="">
            </div>
            <div class="intro-content">
                <div class="step-indicator">Introductie</div>
                <h2 class="selection-title">De juiste robot voor u</h2>

                <p style="font-size: 16px; line-height: 1.6; color: #555; margin: 20px 0;">
                    Niet alle gebouwen zijn gelijk. Deze tool analyseert uw specifieke omgeving. Van drukbezochte
                    lobby's
                    tot industriële magazijnen, om een ​​schoonmaakrobot aan te bevelen die het rendement op uw
                    investering
                    maximaliseert en de hoogste hygiënenormen handhaaft.
                </p>
                <div class="step-indicator">Geschatte tijd 2 minuten</div>
                <div class="pt-8 flex justify-end items-center">
                    <button type="button" class="btn-verder btn-active" onclick="goToStep1()">
                        Start
                    </button>
                </div>
            </div>
        </div>

        <div class="form-step step-1">
            <div class="step-indicator">Stap 1 van 2</div>
            <h2 class="selection-title">Type soort ruimte</h2>
            <?php
            $room_types = get_option( 'my_plugin_rooms', array() );
            if ( ! is_array( $room_types ) || empty( $room_types ) ) {
                $room_types = array(
                    array(
                        'name'  => 'Kantoor',
                        'value' => 'kantoor',
                        'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS2_RX-GRKs6NZn0J5uxHHliUmvc0WQdbz4yQ&s',
                    ),
                    array(
                        'name'  => 'Sportzaal',
                        'value' => 'sportzaal',
                        'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS2_RX-GRKs6NZn0J5uxHHliUmvc0WQdbz4yQ&s',
                    ),
                    array(
                        'name'  => 'Supermarkt',
                        'value' => 'supermarkt',
                        'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS2_RX-GRKs6NZn0J5uxHHliUmvc0WQdbz4yQ&s',
                    ),
                    array(
                        'name'  => 'Hotel',
                        'value' => 'hotel',
                        'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS2_RX-GRKs6NZn0J5uxHHliUmvc0WQdbz4yQ&s',
                    ),
                    array(
                        'name'  => 'Gym',
                        'value' => 'gym',
                        'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS2_RX-GRKs6NZn0J5uxHHliUmvc0WQdbz4yQ&s',
                    ),
                );
            }
            ?>

    <div class="room-selection-grid">
        <?php foreach ( $room_types as $room ) : ?>
        <label class="room-option">
            <input type="radio" name="room_type"
                value="<?php echo esc_attr( $room['value'] ?? sanitize_title( $room['name'] ?? '' ) ); ?>"
                class="room-radio" onchange="enableNextButton()">
            <div class="room-card-content">
                <div class="image-container">
                    <img src="<?php echo esc_url( $room['image'] ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS2_RX-GRKs6NZn0J5uxHHliUmvc0WQdbz4yQ&s' ); ?>"
                        alt="<?php echo esc_attr( $room['name'] ?? 'Room' ); ?>">
                </div>
                <p class="room-label"><?php echo esc_html( $room['name'] ?? '' ); ?></p>
            </div>
        </label>
        <?php endforeach; ?>
    </div>

    <div class="pt-8 flex justify-between items-center">
        <button type="button" class="btn-verder"
            style="border: 1px solid #007bb6; background: transparent; color: #007bb6;" onclick="goToStep0()">
            Terug
        </button>
        <button id="next-step-button" type="button" class="btn-verder btn-active button-disabled" disabled
            onclick="goToStep2()">
            Volgende
        </button>
    </div>
</div>

<div class="form-step step-2">
    <div class="step-indicator">Stap 2 van 2</div>
    <h2 class="selection-title">Extra informatie</h2>

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
        <button type="button" onclick="goToStep1()" class="btn-terug">
            Terug
        </button>
        <button class="btn-verder btn-active" type="submit">
            Verder
        </button>
    </div>
</div>
</form>
</div>

<script>
function setStep(step) {
    document.querySelectorAll('.form-step').forEach(function(element) {
        element.classList.remove('active');
    });
    var activeStep = document.querySelector('.form-step.step-' + step);
    if (activeStep) {
        activeStep.classList.add('active');
    }
}

function enableNextButton() {
    var nextButton = document.getElementById('next-step-button');
    if (!nextButton) {
        return;
    }
    var selectedRoom = document.querySelector('input[name="room_type"]:checked');
    var enabled = Boolean(selectedRoom);
    nextButton.disabled = !enabled;
    nextButton.classList.toggle('button-disabled', !enabled);
}

function goToStep2() {
    if (!document.querySelector('input[name="room_type"]:checked')) {
        return;
    }
    setStep(2);
}

function goToStep1() {
    setStep(1);
}

function goToStep0() {
    setStep(0);
}

function validateRoomType() {
    if (!document.querySelector('input[name="room_type"]:checked')) {
        alert('Kies eerst een type ruimte.');
        setStep(1);
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    enableNextButton();
    setStep(0);
});
</script>
<?php
    return ob_get_clean();
}