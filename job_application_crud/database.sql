CREATE DATABASE IF NOT EXISTS job_application_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE job_application_db;

CREATE TABLE IF NOT EXISTS applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    position_applied VARCHAR(120) NOT NULL DEFAULT 'Software Engineer',
    education VARCHAR(150) NOT NULL,
    years_experience TINYINT UNSIGNED NOT NULL DEFAULT 0,
    portfolio_url VARCHAR(255) NULL,
    date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
