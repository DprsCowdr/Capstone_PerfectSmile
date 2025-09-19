<?php
require __DIR__ . '/../../preload.php';

$service = new \App\Services\AppointmentService();
$ref = new ReflectionClass($service);
$method = $ref->getMethod('buildCreatedMessage');
$method->setAccessible(true);

echo "Patient message:\n";
echo $method->invokeArgs($service, ['patient', '2025-09-17 14:30:00', 15, null]) . "\n\n";

echo "Staff message:\n";
echo $method->invokeArgs($service, ['staff', '2025-09-17 14:30:00', 20, null]) . "\n\n";

echo "Admin message:\n";
echo $method->invokeArgs($service, ['admin', '2025-09-17 14:30:00', 10, null]) . "\n";
