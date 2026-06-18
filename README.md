# Hope For The Poor - Donation & Prayer Platform

A lightweight, devotion-inspired web platform built with PHP 8.1+ and MySQL for ministry donations and prayer requests.

## Setup & Installation

1. **Environment Configuration:**
   Copy the `.env.example` to `.env` and fill in your database credentials and Fapshi API keys.
   *(Note: The current environment is already set up and connected to the `ministry_db` database).*

2. **Web Server Root:**
   Configure your web server (Apache/Nginx/Localhost) to serve the **`public/`** directory as the document root. The `src/` folder and `.env` file should remain outside the public web root for security.

   *To test locally, run this command from the project root:*
   ```bash
   php -S localhost:8000 -t public
   ```

## Admin Dashboard

The platform includes a secure backend to manage donations and prayers.

**Access the dashboard here:**
`http://localhost:8000/admin/login.php` (Adjust the port/domain based on your setup)

**Credentials:**
- **Username:** `admin`
- **Password:** `password123`

You can generate new passwords or accounts using the CLI script: 
`php scripts/seed_admin.php <username> <password>`

## Transaction Logging & Fapshi Integration

Every donation attempt is securely logged in the `donations` database table.
- **Transaction IDs:** When a payment is initiated, the unique Fapshi `transId` is securely recorded in the `reference` column.
- **Audit Trails:** Upon a completed payment or a webhook callback, the full JSON response received from Fapshi is stored in the `fapshi_payload` column. This ensures you have a permanent and auditable record of every transaction exactly as the bank/gateway reported it.

## Architecture

- **`public/`**: Accessible to the web. Contains `index.php`, `donate.php`, `callback.php`, the admin portal, and `assets/` (CSS/Images).
- **`src/`**: Secure application logic. Contains database configs, environment parsers, and security helpers (CSRF, Rate Limiting).
- **`scripts/`**: CLI scripts for database migrations and admin seeding.
