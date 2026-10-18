# Job Application CRUD System (PHP PDO)

A simple job application management web app for a Software Engineer position. It supports **Create, Read, Update, and Delete (CRUD)** using PHP PDO and MySQL. No login or registration module is included.

## Database table

The `applications` table has 10 columns:
1. `id` — primary key, auto-increment
2. `first_name`
3. `last_name`
4. `email`
5. `phone`
6. `position_applied`
7. `education`
8. `years_experience`
9. `portfolio_url`
10. `date_added` — generated timestamp

## Requirements

- PHP 8.0+ with PDO MySQL enabled
- MySQL or MariaDB
- XAMPP, WAMP, MAMP, or another PHP/MySQL local environment

## Setup with XAMPP

1. Copy the `job_application_crud` folder into `htdocs`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open phpMyAdmin (usually `http://localhost/phpmyadmin`).
4. Import `database.sql`, or paste and execute its SQL statements.
5. Open `config/database.php`. Change the MySQL username/password if your local setup differs. The default XAMPP configuration is often `root` with an empty password.
6. Visit `http://localhost/job_application_crud/`.

## Demonstrate the CRUD operations

- **Create:** Fill out the form and click **Submit application**. Confirm the new row appears in the table.
- **Read:** Show the **All applications** table and the saved details.
- **Update:** Click **Edit** beside a row, change one or more fields, and click **Save changes**.
- **Delete:** Click **Delete** beside a row and confirm the prompt. Show that the row disappears.

Use fictional sample data during the screen recording. Capture your own screenshots after running the app; screenshots are not included because they must show your running local environment.

## Suggested GitHub upload

Create a public or instructor-accessible repository named `job-application-crud-php-pdo`, then upload the project files. Do not upload real applicant personal information, database passwords, or secrets.

## Security / implementation notes

- SQL uses PDO prepared statements.
- Output is escaped with `htmlspecialchars`.
- Basic required-field, email, URL, and experience validation is included.
- A session CSRF token is used for POST actions.
- This is an educational starter project, not a complete production hiring platform.
