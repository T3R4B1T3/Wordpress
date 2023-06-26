<?php
/*
Plugin Name: Kalkulator Usług Kominiarskich
Description: Prosty kalkulator do obliczania kosztu usług kominiarskich.
*/

add_shortcode('kalkulator_uslug_kominiarskich', 'kuk_shortcode');

function kuk_shortcode()
{
    ob_start();
    kuk_handle_form_submission();
    kuk_render_form();
    return ob_get_clean();
}

function kuk_render_form()
{
    $errors = kuk_get_form_errors();
    ?>
    <form id="kuk_calculator" method="POST">
        <label for="kuk_service">Wybierz usługę:</label>
        <select name="kuk_service" id="kuk_service" required>
            <option value="">Wybierz usługę</option>
            <option value="czyszczenie">Czyszczenie</option>
            <option value="kontrola">Kontrola</option>
            <option value="udraznianie">Udrażnianie</option>
            <option value="usuwanie_ptasich_gniazd">Usuwanie ptasich gniazd</option>
        </select>
        <br>
        <label for="kuk_area">Powierzchnia komina (m²):</label>
        <input type="text" name="kuk_area" id="kuk_area" required>
        <br>
        <label for="kuk_date">Wybierz termin:</label>
        <input type="date" name="kuk_date" id="kuk_date" required>
        <br>
        <input type="submit" value="Oblicz" name="kuk_submit">
    </form>

    <?php
    if (!empty($errors)) {
        echo '<div class="kuk_error">';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
}

function kuk_handle_form_submission()
{
    if (isset($_POST['kuk_submit'])) {
        $service = $_POST['kuk_service'];
        $area = floatval($_POST['kuk_area']);
        $date = $_POST['kuk_date'];

        $validation_result = kuk_validate_form($service, $area, $date);
        if ($validation_result === true) {
            $price = kuk_calculate_price($service, $area, $date);

            echo '<div class="kuk_result">';
            echo '<p><strong>Wybrana usługa:</strong><br>' . $service . '</p>';
            echo '<p><strong>Powierzchnia komina:</strong><br>' . $area . ' m²</p>';
            echo '<p><strong>Wybrany termin:</strong><br>' . $date . '</p>';
            echo '<p><strong>Cena:</strong><br>' . $price . ' zł</p>';
            echo '</div>';
        } else {
            kuk_set_form_errors($validation_result);
        }
    }
}

function kuk_validate_form($service, $area, $date)
{
    $errors = [];

    if (empty($service)) {
        $errors[] = 'Wybierz usługę.';
    }
    if (empty($area)) {
        $errors[] = 'Wprowadź powierzchnię komina.';
    }
    if (empty($date)) {
        $errors[] = 'Wybierz termin.';
    }

    if (!is_numeric($area) || $area <= 0) {
        $errors[] = 'Podaj poprawną wartość powierzchni komina.';
    }

    $current_date = new DateTime();
    $selected_date = new DateTime($date);
    if ($selected_date <= $current_date) {
        $errors[] = 'Wybierz przyszły termin.';
    }

    return empty($errors) ? true : $errors;
}

function kuk_calculate_price($service, $area, $date)
{
    $base_price = 100;
    $days_diff = kuk_get_days_difference($date);
    $price = $base_price + ($days_diff * 5);

    switch ($service) {
        case 'czyszczenie':
            $price += ($area * 10);
            break;
        case 'kontrola':
            $price += ($area * 5);
            break;
        case 'udraznianie':
            $price += ($area * 15);
            break;
        case 'usuwanie_ptasich_gniazd':
            $price += ($area * 20);
            break;
    }

    return $price;
}

function kuk_get_days_difference($date)
{
    $current_date = new DateTime();
    $selected_date = new DateTime($date);
    $interval = $selected_date->diff($current_date);
    return $interval->days;
}

function kuk_set_form_errors($errors)
{
    $_SESSION['kuk_form_errors'] = $errors;
}

function kuk_get_form_errors()
{
    $errors = isset($_SESSION['kuk_form_errors']) ? $_SESSION['kuk_form_errors'] : [];
    unset($_SESSION['kuk_form_errors']);
    return $errors;
}
