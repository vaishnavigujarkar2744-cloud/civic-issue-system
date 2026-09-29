# Smart City Civic Issue Reporting & Resolution System

Laravel 11/12 + MySQL + Bootstrap 5 + JavaScript (fetch/AJAX) + REST API (Sanctum).
This folder is an **overlay**: it contains only the files you add to or replace in a fresh Laravel project.

## Setup

```bash
composer create-project laravel/laravel civic-issue-system
cd civic-issue-system
php artisan install:api          # installs Sanctum + creates routes/api.php (answer "no" if asked to run extra migrations)
```

1. **Copy everything from this overlay into the project root**, overwriting when prompted
   (`app/`, `bootstrap/app.php`, `database/`, `public/css/`, `resources/views/`, `routes/web.php`, `routes/api.php`).
2. Create a MySQL database (e.g. `civic_db`) and edit `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=civic_db
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log      # emails are written to storage/logs/laravel.log; use smtp for real mail
```

3. Run:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open http://127.0.0.1:8000

## Demo accounts (password: `password`)

| Role       | Email                 |
|------------|-----------------------|
| Admin      | admin@example.com     |
| Citizen    | citizen@example.com   |
| Staff (Roads) | staff1@example.com |
| Staff (Water) | staff2@example.com |
| Staff (Sanitation) | staff3@example.com |
| Staff (Electricity) | staff4@example.com |
| Staff (Drainage) | staff5@example.com |

## Workflow

1. Citizen registers and reports an issue (title, category, description, address/GPS, photo).
2. Admin sees it (notified), assigns department + staff + priority. Citizen and staff are notified.
3. Staff opens assigned complaints, sets In Progress / Resolved / Rejected with remarks. Citizen is notified.
4. Admin dashboard shows live department-wise analytics (Chart.js, refreshed via AJAX).

## REST API (Laravel Sanctum, base `/api`)

| Method | Endpoint | Role | Description |
|---|---|---|---|
| POST | /register | public | Register a citizen, returns token |
| POST | /login | public | Returns user + bearer token |
| POST | /logout | any | Revoke current token |
| GET | /complaints?status=&department_id= | any | Role-scoped list (citizen: own, staff: assigned, admin: all) |
| GET | /complaints/{id} | any | Complaint with timeline |
| POST | /complaints | citizen | Create complaint (multipart for `image`) |
| PATCH | /complaints/{id}/status | staff, admin | `status` + `remarks` |
| GET | /analytics | admin | Summary, by department, by status, monthly |

Example:

```bash
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" -d "email=admin@example.com&password=password" | jq -r .token)

curl -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" http://127.0.0.1:8000/api/analytics
```

Always send the `Accept: application/json` header.

## Project structure (key files)

```
app/Http/Controllers/{AuthController,NotificationController}.php
app/Http/Controllers/Citizen/ComplaintController.php
app/Http/Controllers/Admin/{DashboardController,ComplaintController}.php
app/Http/Controllers/Staff/ComplaintController.php
app/Http/Controllers/Api/{AuthApiController,ComplaintApiController}.php
app/Http/Middleware/RoleMiddleware.php        # role:admin / role:staff,admin
app/Models/{User,Department,Complaint,ComplaintUpdate}.php
app/Notifications/ComplaintStatusChanged.php  # database + mail
app/Services/AnalyticsService.php
database/migrations/*  database/seeders/DatabaseSeeder.php
resources/views/{layouts,auth,citizen,admin,staff,partials}
routes/{web,api}.php
```

## Notes

- Analytics use MySQL functions (`TIMESTAMPDIFF`, `DATE_FORMAT`, `FIELD`), so MySQL/MariaDB is required.
- Public registration only creates citizens; admin/staff accounts are created by the seeder (or add an admin user-management screen as an extension).
- Ideas to extend: complaint feedback/rating, map view with Leaflet, SLA/escalation scheduler, PHPUnit feature tests.
