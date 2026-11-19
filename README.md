# FarmRex – Farm Records Management System

A Laravel web application for **Butiiti Youth Farmers** (Butiiti, Kyenjojo District, Uganda) to record, store,
update, retrieve and report on farm information – built from the project proposal.

## What's inside

| Proposal requirement | Where it lives |
| --- | --- |
| Register / log in, access control | `/register`, `/login`, roles: administrator, farmer |
| Crop records & planting schedules | **Plantings**, **Crops**, **Fields & Plots** |
| Farm activities & input tracking | **Activities**, **Farm Inputs** |
| Crop observations (growth stage, pests, diseases) | **Observations** (a new growth stage updates the planting) |
| Harvest records | **Harvests** |
| Date-based reminders | **Reminders** (+ auto "harvest due" reminder), **Calendar**, bell in the top bar |
| Labour, expenses, income / sales | **Labour**, **Expenses**, **Income** |
| Search & retrieve records | Search box on every list |
| Basic reports | **Reports** – income/expense summaries, profit per planting, harvest totals, print + CSV |
| Weather & farming tips | **Home** dashboard weather card (current conditions, 5-day forecast, rain/heat advice) |
| Validation & data quality | Required fields and validation rules on every form |

Out of scope, as in the proposal: livestock, IoT/sensors, irrigation automation, AI diagnosis, market linkage.

Design: dashboard modelled on the supplied "Fern" screenshot (dark-green welcome banner, income/expense donut
summaries, recent entries), with a **light / dark mode** toggle (remembers your choice, follows your device
setting the first time). No Node/npm build is needed – plain CSS/JS, and charts are drawn without external libraries,
so the app works with poor connectivity.

## Requirements

PHP 8.2+ (Laravel 12), Composer, MySQL (XAMPP is fine). Enable the `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` extensions.

## Setup

```bash
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate

# create an empty MySQL database called  farmrex  (phpMyAdmin), then check DB_* in .env
php artisan migrate --seed    # --seed adds demo data; omit it for an empty system
php artisan serve             # http://localhost:8000
```

Demo logins (password `password`): `admin@farmrex.test` (administrator), `farmer@farmrex.test` (Moses, has demo data) and `grace@farmrex.test` (empty farmer account).
If you do **not** seed, the first person to register becomes the administrator.
After registering, you are taken to the sign-in page. The first sign-in says "Welcome", later ones say "Welcome back".

Run the tests: `php artisan test`  (uses in-memory SQLite, needs `pdo_sqlite`).

## Weather

The Home dashboard shows current weather and a 5-day forecast from [Open-Meteo](https://open-meteo.com) (free, no API key),
plus short tips such as "heavy rain expected – hold off on spraying". It defaults to Butiiti, Kyenjojo; change
`WEATHER_LOCATION`, `WEATHER_LAT` and `WEATHER_LON` in `.env` to use another spot, or set `WEATHER_ENABLED=false` to hide it.
Results are cached for 30 minutes, and if the internet is down the card is simply hidden.

## Roles

* **Administrator** – creates and manages farmer accounts (add, edit, delete, reset password, activate/deactivate),
  adds/edits/deletes any farm record, views all farmers' records (with a *Recorded by* column), generates the
  Reports (and CSV export), and controls system access.
* **Farmer** – signs in to their own account, records crops, planting dates and activities, inputs and expenses,
  labour and harvests, and views/updates **only their own** records. Farmers cannot see other farmers' data,
  the Users page or the Reports page.

The separation is enforced in one place: `app/Models/Concerns/OwnedRecords.php` (a global scope that limits a
farmer to rows where `user_id` is theirs).

## Notes

* Currency is shown as UGX. Seeds, fertiliser and labour are entered in their own sections and are counted under
  Expenses automatically (so nothing is entered twice).
* Project structure: `app/Http/Controllers/CrudController.php` powers all list/add/edit screens; each resource
  controller just declares its fields and columns, so adding a new record type is quick.
* Images are in `public/images`; the logo is `public/images/logo.png`.
