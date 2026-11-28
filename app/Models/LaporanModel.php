<?php

namespace App\Models;

use CodeIgniter\Model;
use Throwable;

class LaporanModel extends Model
{
    protected $table            = 'laporan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kode',
        'user_id',
        'fasilitas_id',
        'kategori_id',
        'status_id',
        'judul',
        'lokasi',
        'deskripsi',
        'foto',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Menghasilkan kode laporan unik berikutnya ('FF-001', 'FF-002', dst.)
     * di dalam transaksi database agar aman dari race condition.
     */
    public function generateNextKode(): string
    {
        $db          = $this->db;
        $startedHere = $db->transDepth === 0;

        if ($startedHere) {
            $db->transStart();
        }

        try {
            $sql = 'SELECT kode FROM ' . $db->prefixTable($this->table)
                . " WHERE kode LIKE 'FF-%' ORDER BY id DESC";

            if (in_array($db->DBDriver, ['MySQLi', 'Postgre'], true)) {
                $sql .= ' FOR UPDATE';
            }

            $rows = $db->query($sql)->getResultArray();

            $maxNumber = 0;
            foreach ($rows as $row) {
                if (preg_match('/^FF-(\d+)$/', (string) ($row['kode'] ?? ''), $matches)) {
                    $num = (int) $matches[1];
                    if ($num > $maxNumber) {
                        $maxNumber = $num;
                    }
                }
            }

            $nextKode = sprintf('FF-%03d', $maxNumber + 1);

            if ($startedHere) {
                $db->transComplete();
            }

            return $nextKode;
        } catch (Throwable $e) {
            if ($startedHere) {
                $db->transRollback();
            }

            throw $e;
        }
    }

    /**
     * Helper query builder dengan JOIN ke users, fasilitas, kategori, dan status_laporan.
     * Mengembalikan instance model agar dapat dirantai dengan where(), orderBy(), findAll(), atau paginate().
     */
    public function getWithRelations(): static
    {
        return $this->select(
            'laporan.*, '
            . 'users.name AS pelapor_nama, '
            . 'users.nim_nip AS pelapor_nim, '
            . 'users.email AS pelapor_email, '
            . 'fasilitas.nama AS fasilitas_nama, '
            . 'kategori.nama AS kategori_nama, '
            . 'status_laporan.nama AS status_nama, '
            . 'status_laporan.urutan AS status_urutan'
        )
            ->join('users', 'users.id = laporan.user_id', 'left')
            ->join('fasilitas', 'fasilitas.id = laporan.fasilitas_id', 'left')
            ->join('kategori', 'kategori.id = laporan.kategori_id', 'left')
            ->join('status_laporan', 'status_laporan.id = laporan.status_id', 'left');
    }

    /**
     * Cari satu laporan beserta seluruh relasinya berdasarkan kode laporan.
     */
    public function findByKodeWithRelations(string $kode): ?array
    {
        return $this->getWithRelations()
            ->where('laporan.kode', $kode)
            ->first();
    }
}
