<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=perfectsmile_db', 'root', '');
    $stmt = $pdo->query('SELECT password FROM user WHERE email = "admin@perfectsmile.com"');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row) {
        echo "Password hash found\n";
        $passwords = ['password', 'admin', '123456', 'password123', 'admin123'];
        foreach($passwords as $pass) {
            if(password_verify($pass, $row['password'])) {
                echo "Password '$pass' works!\n";
                break;
            }
        }
    } else {
        echo "Admin user not found\n";
    }
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
