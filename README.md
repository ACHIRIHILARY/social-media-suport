# Christian Ministry Donation Landing Page

Simple PHP + MySQL landing page for ministry donations and prayer requests.

Setup
1. Create a MySQL database (example name: `ministry_db`).
2. Update database credentials in `inc/config.php`.
3. Import the SQL schema in `sql/schema.sql`.
4. Deploy to a PHP-capable host (PHP 7.4+ recommended).

Files
- `index.php` — main landing page
- `inc/config.php` — DB credentials (edit)
- `inc/db.php` — PDO connection
- `process_prayer.php` — handles prayer request submissions (AJAX)
- `process_donation.php` — creates donation record and returns payment link (AJAX)
- `css/style.css` — styles
- `js/main.js` — client JS (forms, counters)
- `sql/schema.sql` — table creation SQL

Fapshi Integration
The project includes a placeholder payment flow in `process_donation.php` that returns a `payment_url`. Replace the placeholder with your Fapshi API integration using server-side API keys.

Security
- Uses PDO with prepared statements.
- Basic input validation implemented. Update and harden for production.

Images
Place testimonial and hero images inside the `images/` folder. Example names used: `hero.jpg`, `testimonial1.jpg`, `testimonial2.jpg`, `testimonial3.jpg`.
