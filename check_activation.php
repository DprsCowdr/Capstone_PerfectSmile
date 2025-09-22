<?php

/**
 * Simple database check to see what happened with the patient activation
 */

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $envContent = file_get_contents(__DIR__ . '/.env');
    $envLines = explode("\n", $envContent);
    
    foreach ($envLines as $line) {
        $line = trim($line);
        if (!empty($line) && strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

function getEnvValue($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

echo "=== Checking Patient Database Records ===\n\n";

try {
    // Connect to database
    $host = getEnvValue('database.default.hostname', '127.0.0.1');
    $database = getEnvValue('database.default.database', 'perfectsmile_datab');
    $username = getEnvValue('database.default.username', 'root');
    $password = getEnvValue('database.default.password', 'root');
    $port = getEnvValue('database.default.port', 3306);
    
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected successfully\n\n";
    
    // Check recently activated patients (last 1 hour)
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, status, updated_at 
        FROM user 
        WHERE status = 'active' 
        AND updated_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ORDER BY updated_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recentlyActivated = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($recentlyActivated)) {
        echo "ℹ️  No patients were activated in the last hour\n";
        
        // Check all active patients
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, email, status, updated_at 
            FROM user 
            WHERE status = 'active' 
            ORDER BY updated_at DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $allActive = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Recent active patients:\n";
        foreach ($allActive as $patient) {
            echo "  ID: {$patient['id']} | {$patient['first_name']} {$patient['last_name']} | {$patient['email']} | {$patient['updated_at']}\n";
        }
    } else {
        echo "📧 Recently activated patients (last hour):\n";
        foreach ($recentlyActivated as $patient) {
            echo "  ✅ ID: {$patient['id']} | {$patient['first_name']} {$patient['last_name']} | 📧 {$patient['email']} | ⏰ {$patient['updated_at']}\n";
            
            // This should be the email that received the activation email
            if ($patient['email']) {
                echo "     👆 This email should have received the activation email!\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n=== Check Complete ===\n";
echo "\nNext steps:\n";
echo "1. Check the email address shown above\n";
echo "2. Look in that email's inbox AND spam folder\n";
echo "3. The email subject is: 'Your Perfect Smile Account Has Been Activated'\n";
