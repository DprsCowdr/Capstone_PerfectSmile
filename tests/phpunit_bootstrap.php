<?php
// PHPUnit combined bootstrap: set up environment for in-memory SQLite tests then load CodeIgniter test bootstrap.
// Set environment variables for CodeIgniter database tests connection
putenv('database.tests.DBDriver=SQLite3');
putenv('database.tests.database=:memory:');
putenv('database.tests.hostname=localhost');

// First include CodeIgniter test bootstrap to initialize framework testing helpers
require_once __DIR__ . '/../vendor/codeigniter4/framework/system/Test/bootstrap.php';

// Provide a global legacy alias if some tests reference \CIUnitTestCase without namespace
if (!class_exists('CIUnitTestCase') && class_exists('\CodeIgniter\Test\CIUnitTestCase')) {
    class CIUnitTestCase extends \CodeIgniter\Test\CIUnitTestCase {}
}

// Then run our lightweight sqlite table preparation
if (file_exists(__DIR__ . '/_bootstrap.php')) {
    require_once __DIR__ . '/_bootstrap.php';
}

?>
