# Cat & Weather Map 🐱☁️🗺️

A PHP web application that shows current weather by city, fetches random cat facts and images, and displays the user’s location on an interactive map — all containerized with Docker for easy setup.

---

## 🔍 Features

- Select a city to view current weather (via OpenWeatherMap API)
- Get random cat facts (from catfact.ninja API)
- Display random cat images (from cataas.com)
- Show user’s current location on a Leaflet.js map
- Dark mode toggle for comfortable viewing
- Multi-language support (English and Turkish)
- Easy deployment with Docker and Docker Compose

---

## 🛠 Technologies

- PHP 8.2 with Apache  
- MySQL 5.7  
- Docker & Docker Compose  
- Leaflet.js for maps  
- OpenWeatherMap API  
- catfact.ninja API  
- cataas.com for cat images  

---

## 🚀 Setup & Run

1. Create a `.env` file in the project root with your environment variables:

    ```env
    API_KEY=your_openweathermap_api_key

    MYSQL_ROOT_PASSWORD=xxx
    MYSQL_DATABASE=xxx
    MYSQL_USER=xxx
    MYSQL_PASSWORD=xxx
    ```

    > **Note:** Get your OpenWeatherMap API key from [https://openweathermap.org/api](https://openweathermap.org/api).

2. Open your terminal, navigate to the project directory, and run:

    ```bash
    docker-compose up -d
    ```

3. Access the services in your browser:

    - Application: [http://localhost:8080](http://localhost:8080)
    - phpMyAdmin: [http://localhost:8081](http://localhost:8081)

---

## 📁 Project Structure

```
CatWeatherMap/
├── docker-compose.yml
├── .env
├── src/
│ ├── index.php
│ ├── lang/
│ ├── style.css
│ └── script.js
```
## 🔐 Security

Make sure to add `.env` to your `.gitignore` file to avoid pushing sensitive data to your repository.

---

## 🤝 Contributing

Feel free to fork the project, make changes, and submit pull requests!

---

## 📄 License

This project is licensed under the MIT License – see the [LICENSE](LICENSE) file for details.

---