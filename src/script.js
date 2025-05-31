document.addEventListener('DOMContentLoaded', () => {

    const catFactText = document.getElementById('catFactText');
    const newCatFactBtn = document.getElementById('newCatFactBtn');

    const weatherText = document.getElementById('weatherText');
    const updateWeatherBtn = document.getElementById('updateWeatherBtn');
    const cityInput = document.getElementById('cityInput');

    const locationStatus = document.getElementById('locationStatus');
    const mapDiv = document.getElementById('osm-map');

    // Tema yönetimi
    function toggleTheme() {
        document.body.classList.toggle('dark');
        localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
    }
    const themeToggleBtn = document.querySelector('.theme-toggle');
    themeToggleBtn.addEventListener('click', toggleTheme);
    // Kaydetilmiş tema varsa uygula
    (function applySavedTheme() {
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
        }
    })();

    // Yeni kedi bilgisi getirme
    newCatFactBtn.addEventListener('click', async () => {
        newCatFactBtn.disabled = true;
        newCatFactBtn.textContent = i18n.loading;
        try {
            const res = await fetch('?ajax=catfact');
            const data = await res.json();
            if (data.error) {
                catFactText.textContent = data.error;
            } else {
                catFactText.textContent = data.fact;
            }
        } catch {
            catFactText.textContent = i18n.catfact_error;
        } finally {
            newCatFactBtn.disabled = false;
            newCatFactBtn.textContent = `🔄 ${i18n.new_cat_fact}`;
        }
    });

    // Hava durumu getirme fonksiyonu
    async function fetchWeather(city) {
        if (!city) {
            alert(i18n.enter_city);
            return;
        }
        updateWeatherBtn.disabled = true;
        updateWeatherBtn.textContent = i18n.loading;
        weatherText.textContent = '';
        try {
            const res = await fetch(`?ajax=weather&city=${encodeURIComponent(city)}`);
            const data = await res.json();
            if (data.error) {
                weatherText.textContent = data.error;
            } else {
                weatherText.innerHTML = `
                        ${data.name}, ${data.sys.country}<br />
                        Durum: ${data.weather[0].description}<br />
                        Sıcaklık: ${data.main.temp} °C<br />
                        Nem: ${data.main.humidity} %<br />
                        Rüzgar: ${data.wind.speed} m/s
                    `;
                // Haritayı şehir konumuna taşı
                if (data.coord && data.coord.lat && data.coord.lon) {
                    map.flyTo([data.coord.lat, data.coord.lon], 11);
                    if (weatherMarker) {
                        weatherMarker.setLatLng([data.coord.lat, data.coord.lon]);
                    } else {
                        weatherMarker = L.marker([data.coord.lat, data.coord.lon]).addTo(map);
                    }
                }
            }
        } catch {
            weatherText.textContent = i18n.weather_error;
        } finally {
            updateWeatherBtn.disabled = false;
            updateWeatherBtn.textContent = `🔄 ${i18n.update_weather}`;
        }
    }

    updateWeatherBtn.addEventListener('click', () => {
        fetchWeather(cityInput.value.trim());
    });

    cityInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            fetchWeather(cityInput.value.trim());
        }
    });

    // OpenStreetMap ve Leaflet haritası kurulumu
    let map = L.map('osm-map').setView([39.925533, 32.866287], 6); // Türkiye merkezli başlangıç
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map);

    let weatherMarker = null;
    let userMarker = null;

    // Kullanıcının konumunu alma
    if ('geolocation' in navigator) {
        locationStatus.textContent = i18n.location_loading;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const { latitude, longitude } = pos.coords;
                locationStatus.textContent = i18n.location_found;
                map.setView([latitude, longitude], 13);
                userMarker = L.marker([latitude, longitude]).addTo(map)
                    .bindPopup('Buradasınız').openPopup();
            },
            (err) => {
                locationStatus.textContent = 'Konum alınamadı: ' + err.message;
            },
            { timeout: 10000 }
        );
    } else {
        locationStatus.textContent = 'Tarayıcınız konum desteği vermiyor.';
    }

    const newCatImageBtn = document.getElementById('newCatImageBtn');
    const catImage = document.getElementById('catImage');

    newCatImageBtn.addEventListener('click', () => {
        newCatImageBtn.disabled = true;
        newCatImageBtn.textContent = i18n.loading;
        catImage.src = `https://cataas.com/cat?ts=${Date.now()}`;
        catImage.onload = () => {
            newCatImageBtn.disabled = false;
            newCatImageBtn.textContent = `🔄 ${i18n.new_cat_image}`;
        };
        catImage.onerror = () => {
            newCatImageBtn.disabled = false;
            newCatImageBtn.textContent = `🔄 ${i18n.new_cat_image}`;
            alert(i18n.invalid_request);
        };
    });


});