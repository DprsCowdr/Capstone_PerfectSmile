<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBranchHours extends Migration
{
    public function up()
    {
        $fields = [
            'start_time' => [
                'type' => 'TIME',
                'null' => true,
                'default' => '08:00:00'
            ],
            'end_time' => [
                'type' => 'TIME',
                'null' => true,
                'default' => '20:00:00'
            ]
        ];
        $this->forge->addColumn('branches', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('branches', 'start_time');
        $this->forge->dropColumn('branches', 'end_time');
    }
}
