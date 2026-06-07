<?php

define('DB_NAME', 'wp_ellene_local');
define('DB_USER', 'ellene');
define('DB_PASSWORD', 'ellene');
define('DB_HOST', 'ellene-local-db:3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

define('AUTH_KEY', 'ellene-local-auth-key');
define('SECURE_AUTH_KEY', 'ellene-local-secure-auth-key');
define('LOGGED_IN_KEY', 'ellene-local-logged-in-key');
define('NONCE_KEY', 'ellene-local-nonce-key');
define('AUTH_SALT', 'ellene-local-auth-salt');
define('SECURE_AUTH_SALT', 'ellene-local-secure-auth-salt');
define('LOGGED_IN_SALT', 'ellene-local-logged-in-salt');
define('NONCE_SALT', 'ellene-local-nonce-salt');

$table_prefix = 'wp_';

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
define('SCRIPT_DEBUG', true);
define('WP_ENVIRONMENT_TYPE', 'local');

define('WP_HOME', 'http://localhost:8090');
define('WP_SITEURL', 'http://localhost:8090');

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
