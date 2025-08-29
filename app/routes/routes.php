<?php
declare(strict_types=1);

use FastRoute\RouteCollector;
use Jeffrey\Educore\Middleware\School\SchoolValidationMiddleware;

// Define your routes here
// This file will be included by the Router class.

$r->addRoute('GET', '/', 'HomeController@index');
$r->addRoute('GET', '/register', 'SchoolController@showRegistrationForm');
$r->addRoute('POST', '/register', [
    'handler' => 'SchoolController@processRegistration',
    'middleware' => [SchoolValidationMiddleware::class]
]);