<?php
require 'vendor/autoload.php';

use Jeffrey\Educore\Services\Dashboard\DashboardService;

$service = new DashboardService();
$type = $service->getDashboardType(1);

echo "Dashboard type: " . $type;
