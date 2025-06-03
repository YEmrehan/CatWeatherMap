<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


// Desteklenen diller
$supportedLanguages = ['tr', 'en'];

// Dil seçimi GET parametresi veya tarayıcı dili
$lang = 'tr'; // default
if (isset($_GET['lang']) && in_array($_GET['lang'], $supportedLanguages)) {
    $lang = $_GET['lang'];
} else {
    $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (in_array($browserLang, $supportedLanguages)) {
        $lang = $browserLang;
    }
}

// Dil dosyasını yükle
$translations = require __DIR__ . "/lang/{$lang}.php";

// API anahtarı
$apikey = $_ENV['API_KEY'] ?? null;
if (empty($apikey)) {
    echo json_encode(['error' => $translations['api_key_missing']]);
    exit;
}

// AJAX isteği ile yeni kedi bilgisi veya hava durumu talebi
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    if ($_GET['ajax'] === 'catfact') {
        $catFactJson = @file_get_contents('https://catfact.ninja/fact');
        if ($catFactJson === false) {
            echo json_encode(['error' => $translations['cat_fact_error']]);
        } else {
            echo $catFactJson;
        }
        exit;
    } elseif ($_GET['ajax'] === 'weather' && !empty($_GET['city'])) {
        $city = urlencode($_GET['city']);
        $params = http_build_query([
            'q' => $city,
            'appid' => $apikey,
            'units' => 'metric',
            'lang' => $lang,
        ]);
        $url = "https://api.openweathermap.org/data/2.5/weather?" . $params;
        $weatherJson = @file_get_contents($url);
        if ($weatherJson === false) {
            echo json_encode(['error' => $translations['weather_error']]);
        } else {
            echo $weatherJson;
        }
        exit;
    }

    // Geçersiz AJAX isteği
    echo json_encode(['error' => $translations['invalid_request']]);
    exit;
}

// Varsayılan şehir
$defaultCity = 'London';

// İlk sayfa yüklemesi için kedi bilgisi ve hava durumu verileri
$catFactJson = @file_get_contents('https://catfact.ninja/fact');
$catFact = $catFactJson ? json_decode($catFactJson, true) : null;

$weather = null;
    $params = http_build_query([
        'q' => $defaultCity,
        'appid' => $apikey,
        'units' => 'metric',
        'lang' => $lang,
    ]);
    $url = "https://api.openweathermap.org/data/2.5/weather?" . $params;
    $weatherJson = @file_get_contents($url);
    $weather = $weatherJson ? json_decode($weatherJson, true) : null;

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($translations['title']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />

</head>

<body>
    <button class="theme-toggle" aria-label="<?= htmlspecialchars($translations['theme_toggle']) ?>"
        onclick="toggleTheme()">🌙 / ☀️</button>

    <div class="container">
        <h1>🐾 <?= htmlspecialchars($translations['title']) ?></h1>

        <section>
            <h2>
                🐱 <?= htmlspecialchars($translations['cat_fact']) ?>
                <button id="newCatFactBtn" class="refresh-btn"
                    title="<?= htmlspecialchars($translations['new_cat_fact']) ?>">🔄
                    <?= htmlspecialchars($translations['new_cat_fact']) ?></button>
            </h2>
            <p id="catFactText"><?= htmlspecialchars($catFact['fact'] ?? $translations['catfact_error']) ?></p>
        </section>

        <section>
            <h2>
                🐱 <?= htmlspecialchars($translations['cat_image']) ?>
                <button id="newCatImageBtn" class="refresh-btn"
                    title="<?= htmlspecialchars($translations['new_cat_image']) ?>">🔄
                    <?= htmlspecialchars($translations['new_cat_image']) ?></button>
            </h2>
            <img id="catImage" src="https://cataas.com/cat?ts=<?= time() ?>" alt="Random Cat"
                style="max-width: 100%; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);" />
        </section>

        <section>
            <h2>
                ☁️ <?= htmlspecialchars($translations['weather']) ?>
                <input type="text" id="cityInput" name="cityInput"
                    placeholder="<?= htmlspecialchars($translations['enter_city']) ?>"
                    value="<?= htmlspecialchars($defaultCity) ?>" />
                <button id="updateWeatherBtn" class="refresh-btn"
                    title="<?= htmlspecialchars($translations['update_weather']) ?>">🔄
                    <?= htmlspecialchars($translations['update_weather']) ?></button>
            </h2>
            <p id="weatherText">
                <?php if (!empty($weather) && empty($weather['error'])): ?>
                    <?= htmlspecialchars($weather['name']) ?>, <?= htmlspecialchars($weather['sys']['country']) ?><br />
                    <?= htmlspecialchars($translations['weather_condition'] . ": " . $weather['weather'][0]['description']) ?><br />
                    <?= htmlspecialchars($translations['temperature'] . ": " . $weather['main']['temp'] . " °C") ?><br />
                    <?= htmlspecialchars($translations['humidity'] . ": " . $weather['main']['humidity'] . " %") ?><br />
                    <?= htmlspecialchars($translations['wind'] . ": " . $weather['wind']['speed'] . " m/s") ?>
                <?php else: ?>
                    <?= htmlspecialchars($translations['weather_error']) ?>
                <?php endif; ?>
            </p>
        </section>
        <section>
            <h2>🗺️ <?= htmlspecialchars($translations['location_map']) ?></h2>
            <div id="osm-map"></div>
            <p id="locationStatus"></p>
        </section>

    </div>

    <div class="footer">
        <?= htmlspecialchars($translations['copyright']) ?>
    </div>

    <script src="script.js"></script>
    <script>
        const i18n = {
            loading: <?= json_encode($translations['loading']) ?>,
            catfact_error: <?= json_encode($translations['catfact_error']) ?>,
            weather_error: <?= json_encode($translations['weather_error']) ?>,
            invalid_request: <?= json_encode($translations['invalid_request']) ?>,
            apikey_missing: <?= json_encode($translations['apikey_missing']) ?>,
            enter_city: <?= json_encode($translations['enter_city']) ?>,
            update_weather: <?= json_encode($translations['update_weather']) ?>,
            new_cat_fact: <?= json_encode($translations['new_cat_fact']) ?>,
            new_cat_image: <?= json_encode($translations['new_cat_image']) ?>,
            location_loading: <?= json_encode($translations['location_loading']) ?>,
            location_found: <?= json_encode($translations['location_found']) ?>,
            location_error: <?= json_encode($translations['location_error']) ?>,
            location_not_supported: <?= json_encode($translations['location_not_supported']) ?>,
            your_location: <?= json_encode($translations['your_location']) ?>,
        };
    </script>

</body>

</html>