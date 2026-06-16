<?php

require_once __DIR__ . '/env.php';

define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost');
define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? '');

define('SOCIAL_FACEBOOK', $_ENV['SOCIAL_FACEBOOK'] ?? '');
define('SOCIAL_INSTAGRAM', $_ENV['SOCIAL_INSTAGRAM'] ?? '');
define('SOCIAL_TWITTER', $_ENV['SOCIAL_TWITTER'] ?? '');
define('SOCIAL_YOUTUBE', $_ENV['SOCIAL_YOUTUBE'] ?? '');

define('MAIL_FROM', $_ENV['MAIL_FROM'] ?? 'noreply@localhost');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'Hope For The Poor');

// Timezone
date_default_timezone_set('Africa/Douala'); // Common for XAF
