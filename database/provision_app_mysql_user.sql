-- Optional: dedicated app user (use if you do not want Laravel to connect as root).
-- Run as MySQL admin: mysql -u root -p < database/provision_app_mysql_user.sql
-- Then set DB_USERNAME=stephen and DB_PASSWORD=stephen_pass in .env.

CREATE DATABASE IF NOT EXISTS `rsvp`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'stephen'@'localhost' IDENTIFIED BY 'stephen_pass';
CREATE USER IF NOT EXISTS 'stephen'@'127.0.0.1' IDENTIFIED BY 'stephen_pass';

ALTER USER 'stephen'@'localhost' IDENTIFIED BY 'stephen_pass';
ALTER USER 'stephen'@'127.0.0.1' IDENTIFIED BY 'stephen_pass';

GRANT ALL PRIVILEGES ON `rsvp`.* TO 'stephen'@'localhost';
GRANT ALL PRIVILEGES ON `rsvp`.* TO 'stephen'@'127.0.0.1';

FLUSH PRIVILEGES;
