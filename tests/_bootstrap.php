<?php

/*
 * Bootstrap for PHPUnit tests
 */

ini_set('memory_limit', '512M');

// Prevent CodeIgniter from trying to load a .env file
putenv('CI_ENVIRONMENT=testing');

// Load the CodeIgniter 4 autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Set up the application paths
defined('APPPATH') || define('APPPATH', realpath(__DIR__ . '/../app/') . DIRECTORY_SEPARATOR);
defined('ROOTPATH') || define('ROOTPATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);
defined('FCPATH') || define('FCPATH', realpath(__DIR__ . '/../public/') . DIRECTORY_SEPARATOR);
// If the CodeIgniter test bootstrap is available, load it first so helper functions
// like config() are available when this file is executed directly (useful for
// lightweight runners and manual script execution).
$ciTestBootstrap = __DIR__ . '/../vendor/codeigniter4/framework/system/Test/bootstrap.php';
if (is_file($ciTestBootstrap)) {
    require_once $ciTestBootstrap;
}
defined('SYSTEMPATH') || define('SYSTEMPATH', realpath(__DIR__ . '/../vendor/codeigniter4/framework/system/') . DIRECTORY_SEPARATOR);
defined('WRITEPATH') || define('WRITEPATH', realpath(__DIR__ . '/../writable/') . DIRECTORY_SEPARATOR);

// Load CodeIgniter
require_once SYSTEMPATH . 'Config/DotEnv.php';
require_once APPPATH . 'Config/Constants.php';

// Initialize services
$config = config('App');
$request = \Config\Services::request($config);
\Config\Services::injectMock('request', $request);

// Create test database tables if needed
if (defined('PHPUNIT_COMPOSER_INSTALL')) {
    try {
        $db = \Config\Database::connect();
        
        // Create minimal tables required by tests if they don't exist
        if (!$db->tableExists('availability')) {
            $db->query("
                CREATE TABLE IF NOT EXISTS availability (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    type TEXT,
                    start_datetime TEXT,
                    end_datetime TEXT,
                    is_recurring INTEGER DEFAULT 0,
                    day_of_week TEXT,
                    start_time TEXT,
                    end_time TEXT,
                    notes TEXT,
                    created_by INTEGER,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
                );
            ");
        }
        
    } catch (Exception $e) {
        // Silently continue if database setup fails
        error_log("Test bootstrap DB setup failed: " . $e->getMessage());
    }
}
