<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLaporanTable extends Migration
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
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'unique'     => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'fasilitas_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'kategori_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'status_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'judul' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'lokasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
            ],
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('fasilitas_id');
        $this->forge->addKey('kategori_id');
        $this->forge->addKey('status_id');

        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('fasilitas_id', 'fasilitas', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('kategori_id', 'kategori', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('status_id', 'status_laporan', 'id', 'CASCADE', 'RESTRICT');

        $this->forge->createTable('laporan', true);
    }

    public function down()
    {
        $this->forge->dropTable('laporan', true);
    }
}
