# StudentHub — Student & Event Management Portal
CHARUSAT · FTE · Web Development Frameworks (ITUE203) · Semester 3, 2026-27 (Odd)

A full-stack portal built in HTML5, CSS3 (Grid/Flexbox), JavaScript ES6, PHP and MySQL.

## Setup (XAMPP)
1. Copy the `studenthub` folder to `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open phpMyAdmin → *Import* → choose `database/studenthub.sql` (creates DB `studenthub` with seed data).
4. Check `includes/config.php` (default: user `root`, empty password).
5. Visit <http://localhost/studenthub/>.

**Demo logins**
| Role | Email | Password |
|---|---|---|
| Admin | admin@studenthub.local | Admin@123 |
| Student | student@studenthub.local | Student@123 |

Optional virtual host (Practical 15): add to `httpd-vhosts.conf`
```
<VirtualHost *:80>
  ServerName studenthub.local
  DocumentRoot "C:/xampp/htdocs/studenthub"
</VirtualHost>
```
and map `127.0.0.1 studenthub.local` in the Windows hosts file.

## Static HTML version
`static/` contains 11 standalone `.html` pages (Home, About, Events, FAQ, Contact, Feedback, Login, Register, Dashboard, Profile, Admin) for Practicals 2–6. The `.php` pages in the root are the full dynamic site.

## Practical → file mapping
| # | Practical | Where |
|---|---|---|
| 1 | Planning, sitemap, wireframe, GitHub | `docs/requirements.md`, `docs/sitemap.md`, this README |
| 2 | Semantic HTML5 + accessibility | `includes/header.php`, all pages (header/nav/main/footer, skip link, aria, breadcrumb) |
| 3 | Responsive CSS Grid/Flexbox | `assets/css/style.css` (mobile-first, media queries) |
| 4 | DOM, events, localStorage | `assets/js/main.js` (FAQ, modal, slider, banner, hamburger, theme) |
| 5 | Registration validation | `register.php`, `assets/js/validation.js` (regex, strength meter, canvas CAPTCHA) |
| 6 | Fetch JSON, search/filter/sort/paginate | `events.php` (events.json), `directory.php` (students.json), `assets/js/data-render.js`, `data/*.json` |
| 7 | PHP form + CSV/JSON storage | `contact.php` (CSV), `feedback.php` (JSON), CSRF tokens |
| 8 | MySQL schema, ER, PDO | `database/studenthub.sql` (incl. stored procedures), `includes/db.php` |
| 9 | Secure registration | `register.php` (MySQLi prepared, `password_hash`, duplicate check, audit) |
| 10 | Login/logout, sessions, RBAC | `login.php`, `logout.php`, `includes/functions.php` (timeout, remember-me) |
| 11 | Student CRUD | `students.php` (MySQLi, search, filter, pagination, CSV export, soft delete) |
| 12 | Event CRUD + poster upload | `manage_events.php` |
| 13 | JSON API + AJAX CRUD | `api/events.php`, `api-demo.php`, `assets/js/api-crud.js` |
| 14 | Admin dashboard, RBAC, audit | `admin/dashboard.php` (incl. monthly chart via stored procedure), `admin/audit.php` |
| 15 | Integration, testing, deployment | `docs/test-report.md`, this README |

## Security notes
Prepared statements everywhere, `password_hash/password_verify`, session ID regeneration, 15-minute timeout,
hashed remember-me tokens (selector + SHA-256 validator), CSRF tokens on all POST forms and the API,
output escaping via `e()`, upload validation by real MIME type, size limit and random filenames, PHP execution blocked in `uploads/`.

## ER summary
`users 1—0..1 students`, `students 1—* registrations *—1 events`, `users 1—* audit_logs`, `users 1—* remember_tokens`.
