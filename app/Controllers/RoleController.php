<?php
namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\AuditModel;

class RoleController extends BaseController
{
    protected $roleModel;
    protected $permissionModel;
    protected $userModel;
    protected $userRoleModel;
    protected $auditModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
        $this->userModel = new UserModel();
        $this->userRoleModel = new UserRoleModel();
    $this->auditModel = new AuditModel();
    }

    public function index()
    {
        $roles = $this->roleModel->findAll();

        // get user counts
        foreach ($roles as &$r) {
            $r['user_count'] = $this->userRoleModel->where('role_id', $r['id'])->countAllResults();
        }
        // render fragment then wrap in admin layout so the global sidebar is present
        $content = view('roles/index', ['roles' => $roles, 'user' => session('user')]);
        $content = '<div data-sidebar-offset>' . $content . '</div>';
        return view('templates/admin_layout', [
            'title' => 'Roles & Permissions - Perfect Smile',
            'content' => $content,
            'user' => session('user')
        ]);
    }

    public function create()
    {
        $content = view('roles/create', ['user' => session('user')]);
        $content = '<div data-sidebar-offset>' . $content . '</div>';
        return view('templates/admin_layout', [
            'title' => 'Create Role - Perfect Smile',
            'content' => $content,
            'user' => session('user')
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        $roleId = $this->roleModel->insert([
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? ''
        ]);

        // save permissions
        if (!empty($data['permissions']) && $roleId) {
            $changes = [];
            // sanitize posted permission keys and avoid duplicates
            foreach ($data['permissions'] as $module => $acts) {
                $moduleKey = trim((string)$module);
                foreach ($acts as $action => $v) {
                    $actionKey = trim((string)$action);
                    if ($moduleKey === '' || $actionKey === '') continue;
                    // prevent duplicate inserts by checking existing in-memory set
                    $key = $moduleKey . ':' . $actionKey;
                    if (in_array($key, $changes, true)) continue;
                    $this->permissionModel->insert([
                        'role_id' => $roleId,
                        'module' => $moduleKey,
                        'action' => $actionKey,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $changes[] = $key;
                }
            }
            // write audit log for permission creation
            $actor = session('user') ?? null;
            $this->auditModel->insert([
                'actor_id' => $actor['id'] ?? null,
                'actor_name' => $actor['name'] ?? null,
                'role_id' => $roleId,
                'action' => 'permissions_created',
                'changes' => implode(',', $changes),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

    return redirect()->to(site_url('admin/roles'));
    }

    public function edit($id)
    {
        $role = $this->roleModel->find($id);
        $perms = $this->permissionModel->where('role_id', $id)->findAll();
        $permissions = [];
        foreach ($perms as $p) {
            $permissions[$p['module']][$p['action']] = true;
        }
        $content = view('roles/edit', ['role' => $role, 'permissions' => $permissions, 'user' => session('user')]);
        $content = '<div data-sidebar-offset>' . $content . '</div>';
        return view('templates/admin_layout', [
            'title' => 'Edit Role - ' . (isset($role['name']) ? esc($role['name']) : 'Role'),
            'content' => $content,
            'user' => session('user')
        ]);
    }

    public function update($id)
    {
        $data = $this->request->getPost();
        $this->roleModel->update($id, ['name' => $data['name'] ?? '', 'description' => $data['description'] ?? '']);

        // replace permissions
        $changes = [];
        // Use DB transaction where possible to avoid partial state
    $db = \Config\Database::connect();
        $db->transStart();
        $this->permissionModel->where('role_id', $id)->delete();
        if (!empty($data['permissions'])) {
            foreach ($data['permissions'] as $module => $acts) {
                $moduleKey = trim((string)$module);
                foreach ($acts as $action => $v) {
                    $actionKey = trim((string)$action);
                    if ($moduleKey === '' || $actionKey === '') continue;
                    $key = $moduleKey . ':' . $actionKey;
                    if (in_array($key, $changes, true)) continue;
                    $this->permissionModel->insert(['role_id' => $id, 'module' => $moduleKey, 'action' => $actionKey, 'created_at' => date('Y-m-d H:i:s')]);
                    $changes[] = $key;
                }
            }
        }
        $db->transComplete();

        // audit
        $actor = session('user') ?? null;
        $this->auditModel->insert([
            'actor_id' => $actor['id'] ?? null,
            'actor_name' => $actor['name'] ?? null,
            'role_id' => $id,
            'action' => 'permissions_updated',
            'changes' => implode(',', $changes),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to(site_url('admin/roles'));
    }

    public function show($id)
    {
        $role = $this->roleModel->find($id);
        $perms = $this->permissionModel->where('role_id', $id)->findAll();
        $permissions = [];
        foreach ($perms as $p) {
            $permissions[$p['module']][$p['action']] = true;
        }
        $assigned = $this->userRoleModel->where('role_id', $id)->findAll();
        $users = [];
        foreach ($assigned as $a) {
            $u = $this->userModel->find($a['user_id']);
            if ($u) $users[] = $u;
        }
        // Load audit logs for this role
        $logs = $this->auditModel->where('role_id', $id)->orderBy('created_at', 'DESC')->limit(10)->findAll();
        $content = view('roles/show', ['role' => $role, 'permissions' => $permissions, 'assignedUsers' => $users, 'logs' => $logs, 'user' => session('user')]);
        $content = '<div data-sidebar-offset>' . $content . '</div>';
        return view('templates/admin_layout', [
            'title' => 'Role: ' . (isset($role['name']) ? esc($role['name']) : 'Role'),
            'content' => $content,
            'user' => session('user')
        ]);
    }

    public function delete($id)
    {
        // Expect POST (CSRF) — handle soft delete by id
        if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }
        $this->permissionModel->where('role_id', $id)->delete();
        $this->userRoleModel->where('role_id', $id)->delete();
        $this->roleModel->delete($id);
    return redirect()->to(site_url('admin/roles'));
    }

    public function assign($id)
    {
        // Normal execution; no persistent request dump logging in production.

    if ($this->request->getMethod(true) === 'POST') {
            $userIds = $this->request->getPost('user_ids');
            $rawDebugRequested = $this->request->getPost('raw_debug');
            // sanitize posted user ids and only keep positive integers
            $ids = [];
            if (!empty($userIds)) {
                $raw = array_filter(array_map('trim', explode(',', $userIds)));
                foreach ($raw as $r) {
                    if ($r === '') continue;
                    // accept numeric ids only
                    if (is_numeric($r)) {
                        $n = (int)$r;
                        if ($n > 0) $ids[] = $n;
                    }
                }
                // dedupe and reindex
                $ids = array_values(array_unique($ids));
            }

            // Perform delete + insert in transaction to avoid partial state
            $db = \Config\Database::connect();
            $db->transStart();
            $this->userRoleModel->where('role_id', $id)->delete();
            $inserted = [];
            $dbError = null;
            if (!empty($ids)) {
                foreach ($ids as $uid) {
                    try {
                        $res = $this->userRoleModel->insert(['user_id' => $uid, 'role_id' => $id, 'assigned_at' => date('Y-m-d H:i:s')]);
                        // insert() may return inserted id or boolean depending on model config
                        $inserted[] = $res;
                    } catch (\Exception $e) {
                        // capture DB error for debug output
                        $dbError = $e->getMessage();
                    }
                }
            }
            $db->transComplete();

            // Additional DB diagnostics: simple ping and last error info
            $dbPing = false;
            try {
                // simpleQuery returns result on success (true) or false on failure
                $dbPing = $db->simpleQuery('SELECT 1');
            } catch (\Exception $e) {
                $dbPing = 'ping_error:' . $e->getMessage();
            }

            // capture DB driver error info if available
            $dbErrorInfo = null;
            try {
                if (method_exists($db, 'error')) {
                    $dbErrorInfo = $db->error();
                }
            } catch (\Exception $e) {
                $dbErrorInfo = ['exception' => $e->getMessage()];
            }

            // Attempt to capture the last query executed (if supported) for extra insight
            $lastQuery = null;
            try {
                if (method_exists($db, 'getLastQuery')) {
                    $q = $db->getLastQuery();
                    // getLastQuery may return an object or string
                    $lastQuery = is_object($q) ? (string)$q : $q;
                } elseif (method_exists($this->userRoleModel, 'getLastQuery')) {
                    $q = $this->userRoleModel->getLastQuery();
                    $lastQuery = is_object($q) ? (string)$q : $q;
                }
            } catch (\Exception $e) {
                $lastQuery = 'last_query_error:' . $e->getMessage();
            }

            // Normal flow: redirect to the role show page after audit logging.

            if (!empty($ids)) {
                // audit assignment
                $actor = session('user') ?? null;
                $this->auditModel->insert([
                    'actor_id' => $actor['id'] ?? null,
                    'actor_name' => $actor['name'] ?? null,
                    'role_id' => $id,
                    'action' => 'users_assigned',
                    'changes' => implode(',', $ids),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            return redirect()->to(site_url('admin/roles/show/' . $id));
        }

        $role = $this->roleModel->find($id);
        $assigned = $this->userRoleModel->where('role_id', $id)->findAll();
        $assignedUsers = [];
        foreach ($assigned as $a) {
            $u = $this->userModel->find($a['user_id']);
            if ($u) $assignedUsers[] = $u;
        }

        // No GET-load diagnostic logging in production.

        // For initial load, send a small set of users; long lists should use the search endpoint
        $users = $this->userModel->whereNotIn('user_type', ['patient'])->limit(200)->findAll();

        $content = view('roles/assign', ['role' => $role, 'assignedUsers' => $assignedUsers, 'users' => $users, 'user' => session('user')]);
        $content = '<div data-sidebar-offset>' . $content . '</div>';
        return view('templates/admin_layout', [
            'title' => 'Assign Users - ' . (isset($role['name']) ? esc($role['name']) : 'Role'),
            'content' => $content,
            'user' => session('user')
        ]);
    }

    public function searchUsers()
    {
        $q = $this->request->getGet('q');
        if (empty($q)) return $this->response->setJSON(['success' => true, 'results' => []]);
        $results = $this->userModel->like('name', $q)->orLike('email', $q)->whereNotIn('user_type', ['patient'])->limit(50)->findAll();
        return $this->response->setJSON(['success' => true, 'results' => $results]);
    }

    public function remove_user($roleId, $userId)
    {
        if ($this->request->getMethod() !== 'post') return redirect()->back();
        $this->userRoleModel->where('role_id', $roleId)->where('user_id', $userId)->delete();
        // audit removal
        $actor = session('user') ?? null;
        $this->auditModel->insert([
            'actor_id' => $actor['id'] ?? null,
            'actor_name' => $actor['name'] ?? null,
            'role_id' => $roleId,
            'action' => 'user_removed',
            'changes' => (string)$userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return redirect()->back();
    }
}
