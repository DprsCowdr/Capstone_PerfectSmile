<?php
$mysqli = new mysqli('127.0.0.1','root','','perfectsmile_db-v1');
if ($mysqli->connect_errno) {
    die('DB connect failed: ' . $mysqli->connect_error . "\n");
}
$res = $mysqli->query("SELECT id,name,email,user_type FROM user WHERE user_type IN ('admin','staff','dentist') LIMIT 10");
while ($r = $res->fetch_assoc()) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Email: {$r['email']} | Type: {$r['user_type']}\n";
}
$mysqli->close();
