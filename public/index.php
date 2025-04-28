<?php

// Your normal bootstrap
require_once __DIR__ . '/../vendor/autoload.php';

use WorkflowManager\Routes\ApiRouter;

$router = new ApiRouter();
$router->dispatch();
