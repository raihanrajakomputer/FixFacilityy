<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // 1. Master Fasilitas
        $fasilitasNames = [
            'Lampu',
            'Kursi / meja',
            'AC',
            'Keran / toilet',
            'Proyektor',
        ];

        $fasilitasMap = [];
        foreach ($fasilitasNames as $nama) {
            $existing = $this->db->table('fasilitas')->where('nama', $nama)->get()->getRowArray();
            if ($existing) {
                $fasilitasMap[$nama] = (int) $existing['id'];
            } else {
                $this->db->table('fasilitas')->insert([
                    'nama'       => $nama,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $fasilitasMap[$nama] = (int) $this->db->insertID();
            }
        }

        // 2. Master Kategori
        $kategoriNames = [
            'Kelistrikan',
            'Peralatan kelas',
            'Sanitasi',
            'Bangunan',
        ];

        $kategoriMap = [];
        foreach ($kategoriNames as $nama) {
            $existing = $this->db->table('kategori')->where('nama', $nama)->get()->getRowArray();
            if ($existing) {
                $kategoriMap[$nama] = (int) $existing['id'];
            } else {
                $this->db->table('kategori')->insert([
                    'nama'       => $nama,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $kategoriMap[$nama] = (int) $this->db->insertID();
            }
        }

        // 3. Master Status Laporan
        $statuses = [
            ['nama' => 'Menunggu', 'urutan' => 1],
            ['nama' => 'Diproses', 'urutan' => 2],
            ['nama' => 'Selesai', 'urutan' => 3],
        ];

        $statusMap = [];
        foreach ($statuses as $status) {
            $existing = $this->db->table('status_laporan')->where('nama', $status['nama'])->get()->getRowArray();
            if ($existing) {
                $statusMap[$status['nama']] = (int) $existing['id'];
            } else {
                $this->db->table('status_laporan')->insert([
                    'nama'       => $status['nama'],
                    'urutan'     => $status['urutan'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $statusMap[$status['nama']] = (int) $this->db->insertID();
            }
        }

        // 4. Users Testing
        $users = [
            [
                'name'     => 'Mahasiswa Pelapor',
                'nim_nip'  => 'F1G125017',
                'email'    => 'f1g125017@fixfacility.test',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role'     => 'pelapor',
            ],
            [
                'name'     => 'Siti Rahmawati',
                'nim_nip'  => 'F1G125018',
                'email'    => 'f1g125018@fixfacility.test',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role'     => 'pelapor',
            ],
            [
                'name'     => 'Admin Sarpras',
                'nim_nip'  => 'admin',
                'email'    => 'admin@fixfacility.test',
                'password' => password_hash('12345678', PASSWORD_DEFAULT),
                'role'     => 'admin',
            ],
        ];

        $userMap = [];
        foreach ($users as $user) {
            $existing = $this->db->table('users')->where('nim_nip', $user['nim_nip'])->get()->getRowArray();
            if ($existing) {
                $userMap[$user['nim_nip']] = (int) $existing['id'];
            } else {
                $this->db->table('users')->insert([
                    'name'       => $user['name'],
                    'nim_nip'    => $user['nim_nip'],
                    'email'      => $user['email'],
                    'password'   => $user['password'],
                    'role'       => $user['role'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $userMap[$user['nim_nip']] = (int) $this->db->insertID();
            }
        }

        // 5. Contoh Laporan Awal + Riwayat Status
        $pelapor1Id = $userMap['F1G125017'];
        $pelapor2Id = $userMap['F1G125018'];
        $adminId    = $userMap['admin'];

        $sampleLaporan = [
            [
                'kode'       => 'FF-001',
                'user_id'    => $pelapor1Id,
                'fasilitas'  => 'Lampu',
                'kategori'   => 'Kelistrikan',
                'status'     => 'Selesai',
                'judul'      => 'Lampu ruang kelas berkedip dan mati total',
                'lokasi'     => 'Gedung A, Lantai 2, Ruang A203',
                'deskripsi'  => 'Dua buah lampu neon di bagian depan kelas mati total sehingga papan tulis kurang terlihat saat kuliah pagi.',
                'foto'       => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-12 days')),
                'riwayat'    => [
                    ['status' => 'Menunggu', 'by' => $pelapor1Id, 'ket' => 'Laporan baru dibuat oleh pelapor.', 'offset' => '-12 days'],
                    ['status' => 'Diproses', 'by' => $adminId, 'ket' => 'Teknisi kelistrikan sedang memeriksa ballast dan lampu.', 'offset' => '-11 days'],
                    ['status' => 'Selesai', 'by' => $adminId, 'ket' => 'Kedua lampu LED telah diganti dan menyala normal.', 'offset' => '-10 days'],
                ],
            ],
            [
                'kode'       => 'FF-002',
                'user_id'    => $pelapor1Id,
                'fasilitas'  => 'AC',
                'kategori'   => 'Kelistrikan',
                'status'     => 'Diproses',
                'judul'      => 'AC tidak dingin dan meneteskan air',
                'lokasi'     => 'Gedung B, Lantai 3, Lab Komputer 1',
                'deskripsi'  => 'Unit AC di sisi kanan ruangan hanya mengeluarkan angin panas dan air menetes ke lantai dekat meja praktikum.',
                'foto'       => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
                'riwayat'    => [
                    ['status' => 'Menunggu', 'by' => $pelapor1Id, 'ket' => 'Laporan baru dibuat oleh pelapor.', 'offset' => '-6 days'],
                    ['status' => 'Diproses', 'by' => $adminId, 'ket' => 'Dijadwalkan pembersihan saluran drainase dan pengecekan freon.', 'offset' => '-5 days'],
                ],
            ],
            [
                'kode'       => 'FF-003',
                'user_id'    => $pelapor1Id,
                'fasilitas'  => 'Proyektor',
                'kategori'   => 'Peralatan kelas',
                'status'     => 'Menunggu',
                'judul'      => 'Proyektor tidak mendeteksi sinyal HDMI',
                'lokasi'     => 'Gedung C, Lantai 1, Ruang C104',
                'deskripsi'  => 'Kabel HDMI sudah dicoba pada beberapa laptop berbeda namun layar proyektor tetap menampilkan No Signal.',
                'foto'       => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'riwayat'    => [
                    ['status' => 'Menunggu', 'by' => $pelapor1Id, 'ket' => 'Laporan baru dibuat oleh pelapor.', 'offset' => '-2 days'],
                ],
            ],
            [
                'kode'       => 'FF-004',
                'user_id'    => $pelapor1Id,
                'fasilitas'  => 'Keran / toilet',
                'kategori'   => 'Sanitasi',
                'status'     => 'Diproses',
                'judul'      => 'Keran wastafel bocor terus-menerus',
                'lokasi'     => 'Gedung A, Lantai 1, Toilet Timur',
                'deskripsi'  => 'Tuas keran wastafel nomor 2 longgar sehingga air terus mengalir meskipun sudah ditutup rapat.',
                'foto'       => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-35 days')),
                'riwayat'    => [
                    ['status' => 'Menunggu', 'by' => $pelapor1Id, 'ket' => 'Laporan baru dibuat oleh pelapor.', 'offset' => '-35 days'],
                    ['status' => 'Diproses', 'by' => $adminId, 'ket' => 'Petugas sanitasi sedang mengganti katup keran.', 'offset' => '-34 days'],
                ],
            ],
            [
                'kode'       => 'FF-005',
                'user_id'    => $pelapor2Id,
                'fasilitas'  => 'Kursi / meja',
                'kategori'   => 'Bangunan',
                'status'     => 'Menunggu',
                'judul'      => 'Engsel meja kuliah patah dan tidak stabil',
                'lokasi'     => 'Gedung D, Lantai 2, Ruang D201',
                'deskripsi'  => 'Tiga kursi kuliah di baris belakang memiliki sandaran meja yang lepas bautnya sehingga sulit digunakan menulis.',
                'foto'       => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'riwayat'    => [
                    ['status' => 'Menunggu', 'by' => $pelapor2Id, 'ket' => 'Laporan baru dibuat oleh pelapor.', 'offset' => '-1 day'],
                ],
            ],
        ];

        foreach ($sampleLaporan as $item) {
            $existing = $this->db->table('laporan')->where('kode', $item['kode'])->get()->getRowArray();
            if ($existing) {
                continue;
            }

            $this->db->table('laporan')->insert([
                'kode'         => $item['kode'],
                'user_id'      => $item['user_id'],
                'fasilitas_id' => $fasilitasMap[$item['fasilitas']],
                'kategori_id'  => $kategoriMap[$item['kategori']],
                'status_id'    => $statusMap[$item['status']],
                'judul'        => $item['judul'],
                'lokasi'       => $item['lokasi'],
                'deskripsi'    => $item['deskripsi'],
                'foto'         => $item['foto'],
                'created_at'   => $item['created_at'],
                'updated_at'   => $item['created_at'],
            ]);

            $laporanId = (int) $this->db->insertID();

            foreach ($item['riwayat'] as $log) {
                $this->db->table('riwayat_status')->insert([
                    'laporan_id'  => $laporanId,
                    'status_id'   => $statusMap[$log['status']],
                    'diubah_oleh' => $log['by'],
                    'keterangan'  => $log['ket'],
                    'created_at'  => date('Y-m-d H:i:s', strtotime($log['offset'])),
                ]);
            }
        }
    }
}
