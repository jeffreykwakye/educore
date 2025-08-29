<?php

use FastRoute\RouteCollector;

// Define your routes here
// This file will be included by the Router class.

$r->addRoute('GET', '/', 'HomeController@index');
$r->addRoute('GET', '/register', 'SchoolController@showRegistrationForm');
$r->addRoute('POST', '/register', 'SchoolController@processRegistration');