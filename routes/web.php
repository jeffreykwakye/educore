<?php
declare(strict_types=1);

use Jeffrey\Educore\Controllers\Core\HomeController;

$r->addRoute('GET', '/', [
    'handler' => [HomeController::class, 'index']
]);