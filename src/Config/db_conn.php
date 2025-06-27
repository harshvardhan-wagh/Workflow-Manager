<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use RedBeanPHP\R;

// DB credentials
$dbHost = '127.0.0.1';
$dbName = 'workflow';
$dbUser = 'root';
$dbPass = '273413'; // your DB password

// DSN format: mysql:host=HOST;dbname=DB
R::setup("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);

// Optional: freeze schema in production to avoid unwanted column changes
R::freeze(false);

// Optional: turn on debug to log queries
// R::debug(true);