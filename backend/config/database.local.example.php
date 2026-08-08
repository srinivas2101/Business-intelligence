<?php
/*
 * Copy this file to "database.local.php" (same folder) and fill in your
 * REAL credentials there. database.local.php is in .gitignore — it will
 * NEVER be committed to git, so your password stays private.
 *
 * For local testing (XAMPP):
 *   DB_HOST = '127.0.0.1', DB_USER = 'root', DB_PASS = ''
 *
 * For live hosting (InfinityFree):
 *   Copy the values from your InfinityFree "MySQL Databases" panel.
 *   Upload database.local.php to backend/config/ on the server via
 *   File Manager or FTP — do NOT put it in git.
 */
define('DB_HOST', 'sql104.infinityfree.com');
define('DB_USER', 'if0_XXXXXXXX');
define('DB_PASS', 'your_real_password_here');
define('DB_NAME', 'if0_XXXXXXXX_your_db_name');