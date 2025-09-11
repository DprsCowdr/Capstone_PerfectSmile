<?php
namespace App\Models;

use CodeIgniter\Model;

class AuditModel extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = ['actor_id', 'actor_name', 'role_id', 'action', 'changes', 'created_at'];
    protected $useTimestamps = false;
}
