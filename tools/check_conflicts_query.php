<?php
$mysqli = new mysqli('127.0.0.1','root','','perfectsmile_db-v1',3306);
if ($mysqli->connect_errno){ echo 'DB connect failed: '.$mysqli->connect_error; exit(1); }
$date = date('Y-m-d', strtotime('+1 day'));
$start = $date . ' 09:30:00';
$end = $date . ' 11:30:00';
$sql = "SELECT a.id, a.appointment_datetime, u.name as patient_name\n"
    . "FROM appointments a JOIN user u ON u.id = a.user_id\n"
    . "LEFT JOIN (SELECT appointment_id, SUM(COALESCE(s.duration_max_minutes, s.duration_minutes, 0)) AS total_service_minutes FROM appointment_service aps JOIN services s ON s.id = aps.service_id GROUP BY appointment_id) svc ON svc.appointment_id = a.id\n"
    . "WHERE a.dentist_id = 30 AND a.appointment_datetime < '" . $end . "' AND ( (svc.total_service_minutes IS NOT NULL AND DATE_ADD(a.appointment_datetime, INTERVAL svc.total_service_minutes MINUTE) > '" . $start . "') OR (a.procedure_duration IS NOT NULL AND DATE_ADD(a.appointment_datetime, INTERVAL a.procedure_duration MINUTE) > '" . $start . "') ) AND a.status IN ('confirmed','checked_in','ongoing') AND a.approval_status IN ('approved','auto_approved')";
$res = $mysqli->query($sql);
if (!$res){ echo 'Query failed: '.$mysqli->error; exit(2); }
while ($row = $res->fetch_assoc()){
    echo json_encode($row) . PHP_EOL;
}
echo "Done\n";
