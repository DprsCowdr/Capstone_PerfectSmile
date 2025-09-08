<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchNotificationModel extends Model
{
    protected $table = 'branch_notifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['branch_id','appointment_id','payload','sent','sent_at','created_at','updated_at'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
