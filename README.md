# CampusHub

CampusHub is a PHP and MySQL web application for campus events, student activities, announcements, and media sharing. It includes student accounts and an administration dashboard.

## Features

- Student registration, login, and profile management.
- Event listings, event details, and registration with capacity checks.
- Image gallery, media page, and file uploads.
- Announcements stored in XML.
- Contact form with messages stored in the database.
- Admin tools for students, events, registrations, announcements, messages, and bulk media uploads.

## Technology

- PHP with MySQLi, SimpleXML, Fileinfo, and session support.
- MySQL database; MySQLi needs mysqlnd support for `get_result()`.
- HTML, CSS, JavaScript, and Tailwind CSS loaded through a CDN.
- Google Fonts loaded externally.

No Composer or npm installation is currently required. Internet access is needed for the externally hosted styles and fonts.

## Local setup

1. Place the project in your PHP web server's document root under a folder named `campushub`. For example, with XAMPP on Windows:

   ```text
   C:\xampp\htdocs\campushub
   ```

2. Start Apache and MySQL, or the equivalent services in your local PHP environment.

3. Create a database named `campushub` and import the project's database schema and any required seed data.

   **The repository currently contains no SQL schema, database dump, or migrations.** Obtain a compatible database export from the project maintainer before running the application. Creating an empty database alone is insufficient. The PHP code references these tables:

   - `students`
   - `events`
   - `event_registrations`
   - `media_files`
   - `contact_messages`

4. Edit the connection settings in `config/db.php`, keeping the existing connection and helper code:

   ```php
   $db_host = 'localhost';
   $db_user = 'your_database_user';
   $db_pass = 'your_database_password';
   $db_name = 'campushub';
   ```

   The application reads these PHP variables directly; it does not currently load `.env` files.

5. Allow the web server to write to `uploads/` and `xml/announcements.xml`. Upload handlers create subdirectories such as `photos/`, `documents/`, and `media/` when needed.

6. Open [CampusHub locally](http://localhost/campushub/).

The application uses `/campushub/` in links and redirects. If you serve it under another path, update those references throughout the PHP files.

## First administrator account

After setting up the database, open [Initial Admin Setup](http://localhost/campushub/admin/initial_setup.php) and create the first administrator account. This page redirects to the admin login once an administrator exists. Remove or restrict access to the setup page after use.

Alternatively, run this command from the project directory, replacing the example values:

```sh
php scripts/create_admin_cli.php "Campus Admin" "admin@example.com" "replace-with-a-unique-password"
```

Sign in at [Admin Login](http://localhost/campushub/admin/login.php). There are no built-in default administrator credentials in the setup flow.

## Project structure

```text
campushub/
|-- admin/       # Administration pages and initial account setup
|-- config/      # Database connection configuration
|-- css/         # Custom styles
|-- includes/    # Shared header, footer, and PHP helpers
|-- pages/       # Student and public pages
|-- scripts/     # Command-line and maintenance helpers
|-- uploads/     # Uploaded images, documents, and media
|-- xml/         # XML announcement storage
|-- index.php    # Home page
`-- .gitignore
```

## Media and uploads

The student upload page accepts JPG, JPEG, PNG, and GIF images up to 5 MB, and PDF, DOC, and DOCX documents up to 10 MB. PHP's `upload_max_filesize` and `post_max_size` settings must also accommodate the uploads.

The media page references `uploads/media/promo-video.mp4`, `uploads/media/intro-audio.mp3`, and `uploads/photos/event2.jpg`. These files are not currently included; provide them locally or update the page to use your own assets.

## Git configuration

`.gitignore` excludes local database configuration, environment files, new uploads, dependencies, logs, temporary files, generated backups, and editor settings. Files already tracked by Git remain tracked even when they match an ignore rule.

## Manual verification

After configuring the database, check the home page, register and sign in as a student, register for an event, upload a supported file, and submit a contact message. Sign in as an administrator to check event management and announcement publishing. No automated test suite is currently included.
