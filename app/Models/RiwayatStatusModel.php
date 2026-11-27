<?php

namespace App\Models;

use CodeIgniter\Model;

class RiwayatStatusModel extends Model
{
    protected $table            = 'riwayat_status';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'laporan_id',
        'status_id',
        'diubah_oleh',
        'keterangan',
        'created_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Ambil riwayat perubahan status untuk satu laporan beserta nama status dan pengubah.
     */
    public function getByLaporanId(int $laporanId): array
    {
        return $this->select(
            'riwayat_status.*, '
            . 'status_laporan.nama AS status_nama, '
            . 'status_laporan.urutan AS status_urutan, '
            . 'users.name AS pengubah_nama, '
            . 'users.role AS pengubah_role'
        )
            ->join('status_laporan', 'status_laporan.id = riwayat_status.status_id', 'left')
            ->join('users', 'users.id = riwayat_status.diubah_oleh', 'left')
            ->where('riwayat_status.laporan_id', $laporanId)
            ->orderBy('riwayat_status.created_at', 'ASC')
            ->orderBy('riwayat_status.id', 'ASC')
            ->findAll();
    }
}
