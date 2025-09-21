<?php
// Direct test of slot generation logic
$pdo = new PDO('mysql:host=127.0.0.1;dbname=perfectsmile_db-v1', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Testing Slot Generation Logic ===\n";
echo "Date: 2025-09-20\n";
echo "Branch: 1, Service: 2\n\n";

// 1. Check existing appointments that should block slots
$stmt = $pdo->prepare("
    SELECT appointment_datetime, status, approval_status, procedure_duration
    FROM appointments 
    WHERE DATE(appointment_datetime) = ? 
    AND approval_status = 'approved' 
    AND status NOT IN ('cancelled', 'rejected', 'no_show')
    ORDER BY appointment_datetime
");
$stmt->execute(['2025-09-20']);
$blockingAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Appointments that should block slots:\n";
foreach ($blockingAppointments as $apt) {
    $duration = $apt['procedure_duration'] ?: 30; // default 30 min
    $startTime = date('H:i', strtotime($apt['appointment_datetime']));
    $endTime = date('H:i', strtotime($apt['appointment_datetime'] . ' +' . $duration . ' minutes'));
    echo "  {$startTime} - {$endTime} (Duration: {$duration}min)\n";
}
echo "\n";

// 2. Get service duration
$stmt = $pdo->prepare("SELECT duration_minutes, duration_max_minutes FROM services WHERE id = ?");
$stmt->execute([2]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);
$serviceDuration = $service['duration_max_minutes'] ?: $service['duration_minutes'] ?: 30;
echo "Service duration: {$serviceDuration} minutes\n\n";

// 3. Generate slots from 8:00 to 21:00 (30-minute intervals)
echo "Generating 30-minute slots from 08:00 to 21:00:\n";
$slots = [];
$startHour = 8;
$endHour = 21;

for ($hour = $startHour; $hour < $endHour; $hour++) {
    for ($minute = 0; $minute < 60; $minute += 30) {
        $slotTime = sprintf('%02d:%02d', $hour, $minute);
        $slotStart = strtotime("2025-09-20 {$slotTime}:00");
        $slotEnd = strtotime("2025-09-20 {$slotTime}:00 +{$serviceDuration} minutes");
        
        // Check if this slot conflicts with any approved appointment
        $isBlocked = false;
        foreach ($blockingAppointments as $apt) {
            $aptStart = strtotime($apt['appointment_datetime']);
            $aptDuration = $apt['procedure_duration'] ?: 30;
            $aptEnd = strtotime($apt['appointment_datetime'] . ' +' . $aptDuration . ' minutes');
            
            // Check for overlap: slot overlaps with appointment if slot_start < apt_end AND slot_end > apt_start
            if ($slotStart < $aptEnd && $slotEnd > $aptStart) {
                $isBlocked = true;
                break;
            }
        }
        
        $status = $isBlocked ? '❌ BLOCKED' : '✅ Available';
        echo "  {$slotTime} - {$status}\n";
        
        $slots[] = [
            'time' => $slotTime,
            'available' => !$isBlocked,
            'datetime' => "2025-09-20 {$slotTime}:00"
        ];
    }
}

echo "\nSlot Summary:\n";
$available = array_filter($slots, fn($s) => $s['available']);
$blocked = array_filter($slots, fn($s) => !$s['available']);
echo "Total slots: " . count($slots) . "\n";
echo "Available: " . count($available) . "\n";
echo "Blocked: " . count($blocked) . "\n";

if (count($blocked) > 0) {
    echo "\nBlocked slot times:\n";
    foreach ($blocked as $slot) {
        echo "  {$slot['time']}\n";
    }
}