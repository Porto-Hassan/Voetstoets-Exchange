# Voetstoots Exchange — Setup Instructions

## Step 1: Database
1. Log into InfinityFree cPanel → MySQL Databases
2. Create a new database (note the full name, e.g. epiz_XXXXX_voetstoots)
3. Create a database user and assign it to the database with ALL privileges
4. Open phpMyAdmin, select your database, click the SQL tab
5. Paste the full contents of config/schema.sql and click Go

## Step 2: Configuration
1. Open config/db.php
2. Replace DB_HOST, DB_NAME, DB_USER, DB_PASS with your InfinityFree database credentials
3. Replace SITE_URL with your actual InfinityFree domain (e.g. https://yourusername.infinityfreeapp.com/voetstoots)

## Step 3: Upload Files
1. In cPanel File Manager, navigate to /htdocs
2. Upload the entire voetstoots/ folder
3. Make sure uploads/listings/ folder has write permissions (chmod 755)

## Step 4: Test
- Visit your SITE_URL — the homepage should load
- Register a seller account and post a listing
- Log in as admin: admin@voetstoots.co.za / Admin@1234
  (IMPORTANT: Change the admin password immediately after first login)

## Admin Panel
- URL: yourdomain.com/voetstoots/admin/login.php
- Default email: admin@voetstoots.co.za
- Default password: Admin@1234

## Tech Stack
- HTML5, CSS3, Bootstrap 5.3
- Vanilla JavaScript
- PHP 8
- MySQL (via PDO)
