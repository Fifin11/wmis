# Smart Municipal Waste Logistics & Citizen Engagement System (WMIS)

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

An automated web-based platform designed to streamline municipal waste collection schedules and enhance real-time communication between local authorities and residents.

## 🎓 Project Details
- **Course:** HNDIT4052 Programming Individual Project
- **Institution:** SLIATE - Advanced Technological Institute Rathnapura, Department of Information Technology
- **Developer:** D.G.D.D. Dasanayaka (RAT/IT/2324/F/0017)
- **Supervisor:** Mr. G.S.T. Bandara

## 🌟 About The Project

Urbanization and population growth in Sri Lanka have led to a significant increase in municipal solid waste. Currently, most municipal councils operate on a fixed weekly schedule. However, the lack of real-time visibility between the "Town Hall" and the residents causes waste to be left on the streets for extended periods.

This project introduces a centralized, web-based platform built with the Laravel MVC architecture to address these logistical challenges. It digitizes the communication loop between municipal employees and the public, replacing the current "guesswork" of waste collection with data-driven transparency.

## 🚀 Key Features

*   **Administration Module:** Allows town hall officials to digitize the weekly waste schedule, manage the fleet of drivers, moderate recycling submissions, and post real-time announcements.
*   **Driver & Employee Portal:** A mobile-responsive interface where drivers can "check-in" to a route, mark departure times, and update the status of specific pickup points as they are completed.
*   **Citizen Engagement Dashboard:** A user-facing portal where residents can view the live status of the collection truck (powered by Leaflet.js).
*   **Bilingual Support (i18n):** The system interface is fully supported in both English and Sinhala to ensure the platform is accessible to all members of the local community.
*   **Gamified Recycling Module:** Citizens can submit recycling claims to earn eco-points on a leaderboard, motivating sustainable behavior.

## 🛠️ Built With

*   **Backend:** PHP 8.3+, Laravel (MVC Architecture)
*   **Database:** MySQL / SQLite
*   **Frontend:** HTML5, CSS3, Blade Templating
*   **Mapping API:** Leaflet.js (for real-time route visualization)
*   **Environment:** Local development using XAMPP (Apache/MariaDB)

## 📦 Setup & Installation

1. Clone the repository and navigate to the project root (`wmis`).
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install JavaScript dependencies and compile assets:
   ```bash
   npm install
   npm run build
   ```
4. Copy the environment file and generate an app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
5. Configure your database in the `.env` file (e.g., set `DB_CONNECTION=mysql` or use `sqlite`).
6. Run the database migrations and seeders (populates dummy data for the presentation!):
   ```bash
   php artisan migrate --seed
   ```
7. Start the local development server:
   ```bash
   php artisan serve
   ```
8. Visit `http://localhost:8000` in your browser.

## 🔒 User Roles & Access
The application uses role-based access control. Refer to the seeders to log into the different dashboards:
- **Admin:** Has full access to route management and driver assignments.
- **Driver:** Accesses the driver portal to start/complete routes.
- **Citizen:** Accesses the public dashboard to view live trucks and submit reports.

## 📄 License
This project was developed for educational purposes as part of the HNDIT curriculum.
