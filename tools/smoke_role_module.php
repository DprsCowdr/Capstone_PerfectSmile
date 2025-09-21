<?php
// Simple smoke script to exercise RoleController flows: create, update, assign, show, remove_user
require __DIR__ . '/../vendor/autoload.php';
if (is_file(__DIR__ . '/../tests/_bootstrap.php')) require_once __DIR__ . '/../tests/_bootstrap.php';

// bootstrap services
$request = \Config\Services::request();
$response = \Config\Services::response();
$logger = \Config\Services::logger();

// DB
try {
    $db = \Config\Database::connect();
} catch (Exception $e) {
    echo "DB connect failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Ensure minimal tables exist for the smoke script (handles DB prefix)
$prefix = '';
if (method_exists($db, 'getPrefix')) $prefix = $db->getPrefix();
elseif (property_exists($db, 'DBPrefix')) $prefix = $db->DBPrefix;

$tables = [
    'user' => "CREATE TABLE IF NOT EXISTS {$prefix}user (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, password TEXT, user_type TEXT, status TEXT, created_at TEXT, updated_at TEXT)",
    'roles' => "CREATE TABLE IF NOT EXISTS {$prefix}roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, description TEXT, created_at TEXT, updated_at TEXT)",
    'permissions' => "CREATE TABLE IF NOT EXISTS {$prefix}permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, role_id INTEGER, module TEXT, action TEXT, created_at TEXT, updated_at TEXT)",
    'user_role' => "CREATE TABLE IF NOT EXISTS {$prefix}user_role (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, role_id INTEGER, assigned_at TEXT, created_at TEXT, updated_at TEXT)",
    'audit' => "CREATE TABLE IF NOT EXISTS {$prefix}audit (id INTEGER PRIMARY KEY AUTOINCREMENT, actor_id INTEGER, actor_name TEXT, role_id INTEGER, action TEXT, changes TEXT, created_at TEXT, updated_at TEXT)"
];
foreach ($tables as $name => $sql) {
    try { $db->query($sql); } catch (Throwable $e) { /* ignore failures in restrictive environments */ }
}

// Ensure session available
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
try { 
    session()->set('isLoggedIn', true);
} catch (Throwable $e) { $_SESSION['isLoggedIn'] = true; }

// Create a test admin user for actor
$db->table('user')->insert(['name' => 'Role Smoke Admin', 'email' => 'rolesmoke_admin+' . time() . '@test', 'password' => password_hash('x', PASSWORD_DEFAULT), 'user_type' => 'admin', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
$actorId = $db->insertID();
$actor = ['id' => $actorId, 'name' => 'Role Smoke Admin', 'user_type' => 'admin'];

// Set CodeIgniter session user if available
try { session()->set('user', $actor); } catch (Throwable $e) { $_SESSION['user'] = $actor; }

$controller = new \App\Controllers\RoleController();
$controller->initController($request, $response, $logger);

echo "=== RoleController smoke start ===\n";

// 1) Create role via controller->store
$_POST = [];
$_POST['name'] = 'Smoke Role ' . time();
$_POST['description'] = 'Created by smoke script';
// emulate permissions nested array
$_POST['permissions'] = ['module_alpha' => ['view' => '1', 'edit' => '1'], 'module_beta' => ['list' => '1']];
try {
    $res = $controller->store();
    echo "store() returned: ";
    if (is_object($res)) {
        if (method_exists($res, 'getBody')) echo substr((string)$res->getBody(), 0, 400) . "\n";
        else echo get_class($res) . "\n";
    } else echo "(non-object)\n";
} catch (Throwable $e) { echo "store() exception: " . $e->getMessage() . "\n"; }

// Find created role
$role = $db->table('roles')->where('name', $_POST['name'])->get()->getRowArray();
if (!$role) { echo "Failed to locate created role in DB\n"; exit(1); }
$roleId = $role['id'];
echo "Created role id: {$roleId}\n";

// 2) Update role
$_POST = [];
$_POST['name'] = $role['name'] . ' (updated)';
$_POST['description'] = 'Updated description';
// change permissions (remove edit, add delete)
$_POST['permissions'] = ['module_alpha' => ['view' => '1', 'delete' => '1']];
try {
    $res = $controller->update($roleId);
    echo "update() returned: ";
    if (is_object($res)) {
        if (method_exists($res, 'getBody')) echo substr((string)$res->getBody(), 0, 400) . "\n";
        else echo get_class($res) . "\n";
    } else echo "(non-object)\n";
} catch (Throwable $e) { echo "update() exception: " . $e->getMessage() . "\n"; }

// verify permissions changed
$perms = $db->table('permissions')->where('role_id', $roleId)->get()->getResultArray();
echo "Permissions for role ({$roleId}):\n";
foreach ($perms as $p) echo " - {$p['module']}:{$p['action']}\n";

// 3) Assign user(s)
// create a user to assign
$db->table('user')->insert(['name' => 'Role Assignee', 'email' => 'rolesmoke_user+' . time() . '@test', 'password' => password_hash('x', PASSWORD_DEFAULT), 'user_type' => 'staff', 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]);
$assignee = $db->insertID();

$_POST = [];
// controller expects comma separated string
$_POST['user_ids'] = (string)$assignee;
try {
    $res = $controller->assign($roleId);
    echo "assign() returned: ";
    if (is_object($res)) {
        if (method_exists($res, 'getBody')) echo substr((string)$res->getBody(), 0, 400) . "\n";
        else echo get_class($res) . "\n";
    } else echo "(non-object)\n";
} catch (Throwable $e) { echo "assign() exception: " . $e->getMessage() . "\n"; }

// verify user_role row
$ur = $db->table('user_role')->where(['role_id' => $roleId, 'user_id' => $assignee])->get()->getRowArray();
if ($ur) echo "UserRole created id exists\n"; else echo "UserRole not found\n";

// 4) Show role (should render view)
try {
    $out = $controller->show($roleId);
    echo "show() output (truncated):\n";
    if (is_string($out)) echo substr($out, 0, 800) . "\n";
    elseif (is_object($out) && method_exists($out, 'getBody')) echo substr((string)$out->getBody(), 0, 800) . "\n";
    else echo gettype($out) . "\n";
} catch (Throwable $e) { echo "show() exception: " . $e->getMessage() . "\n"; }

// 5) Remove user via remove_user
// This endpoint expects POST
try {
    // call remove_user(role, user)
    $_POST = [];
    $res = $controller->remove_user($roleId, $assignee);
    echo "remove_user() returned: ";
    if (is_object($res)) {
        if (method_exists($res, 'getBody')) echo substr((string)$res->getBody(), 0, 400) . "\n";
        else echo get_class($res) . "\n";
    } else echo "(non-object)\n";
} catch (Throwable $e) { echo "remove_user() exception: " . $e->getMessage() . "\n"; }

// verify removal
$ur2 = $db->table('user_role')->where(['role_id' => $roleId, 'user_id' => $assignee])->get()->getRowArray();
if (!$ur2) echo "UserRole removed OK\n"; else echo "UserRole still present\n";

// Cleanup: delete created permissions, role, users, audit rows
try {
    $db->table('permissions')->where('role_id', $roleId)->delete();
    $db->table('user_role')->where('role_id', $roleId)->delete();
    $db->table('roles')->where('id', $roleId)->delete();
    $db->table('user')->whereIn('id', [$actorId, $assignee])->delete();
    $db->table('audit')->where('role_id', $roleId)->delete();
} catch (Throwable $e) { echo "Cleanup note: " . $e->getMessage() . "\n"; }

echo "=== RoleController smoke end ===\n";
