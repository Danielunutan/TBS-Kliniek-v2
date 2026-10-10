# TBS Kliniek

> **Respect verdien je. Frikandelbroodjes moet je bevechten.**

Een complete Nederlandstalige multiplayer-browsergame gebouwd met Laravel. Geïnspireerd door klassieke Nederlandse webgames zoals Baddies.nl en CrimeClub, met een geheel eigen identiteit, Nederlandse humor, absurde personages, gangs, economie, missies en misdaad.

## Tech Stack
- **Backend:** Laravel 12 / PHP 8.3+
- **Database:** SQLite (lokaal) / MySQL & MariaDB (productie / DirectAdmin compatibel)
- **Frontend:** Blade, Tailwind CSS, Alpine.js, Font Awesome Free
- **Architectuur:** Service-layer pattern (`LevelService`, `EconomyService`, `MissionService`, `TheftService`, `PrisonService`, etc.)

## Lokale Installatie & Start
1. Installeer dependencies:
   ```bash
   composer install
   npm install
   ```
2. Kopieer environment & genereer key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Migraties en Seeders draaien:
   ```bash
   php artisan migrate --seed
   ```
4. Start de ontwikkelserver:
   ```bash
   php artisan serve --port=8001
   ```
