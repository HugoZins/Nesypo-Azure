<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Force APP_ENV=test après le chargement des .env
$_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = 'test';

if ($_SERVER['APP_DEBUG'] ?? false) {
    umask(0000);
}
