<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRiwayatStatusTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'laporan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'diubah_oleh' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('laporan_id');
        $this->forge->addKey('status_id');
        $this->forge->addKey('diubah_oleh');

        $this->forge->addForeignKey('laporan_id', 'laporan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('status_id', 'status_laporan', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('diubah_oleh', 'users', 'id', 'CASCADE', 'RESTRICT');

        $this->forge->createTable('riwayat_status', true);
    }

    public function down()
    {
        $this->forge->dropTable('riwayat_status', true);
    }
}
