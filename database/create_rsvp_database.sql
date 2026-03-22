-- Run when logged into MySQL as a user that can create databases, e.g.:
--   mysql -u root -p < database/create_rsvp_database.sql

CREATE DATABASE IF NOT EXISTS `rsvp`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
