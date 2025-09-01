<?php

use Jeffrey\Educore\Controllers\Core\HomeController;

$r->addRoute('GET', '/', [
    'handler' => [HomeController::class, 'index']
]);