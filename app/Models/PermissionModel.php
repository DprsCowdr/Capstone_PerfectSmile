<?php
namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['role_id', 'module', 'action', 'created_at'];
    protected $useTimestamps = false;
}
