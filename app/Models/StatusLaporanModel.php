<?php

namespace App\Models;

use CodeIgniter\Model;

class StatusLaporanModel extends Model
{
    protected $table            = 'status_laporan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nama',
        'urutan',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil semua status terurut berdasarkan kolom urutan.
     */
    public function getOrdered(): array
    {
        return $this->orderBy('urutan', 'ASC')->findAll();
    }

    /**
     * Cari status berdasarkan nama (misal: 'Menunggu', 'Diproses', 'Selesai').
     */
    public function findByNama(string $nama): ?array
    {
        return $this->where('nama', $nama)->first();
    }
}
