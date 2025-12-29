<?php
session_start();
require_once __DIR__ . '/config/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

/* QUERY DATA PENGIRIMAN + CUSTOMER */
$query = "
    SELECT 
        k.*,
        p.nama_cust
    FROM tb_pengiriman k
    JOIN tb_penjualan p ON k.id_jual = p.id_jual
    ORDER BY k.id_kirim DESC
";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Manajemen Pengiriman</title>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ===== GLOBAL ===== */
        body {
            background: #0f172a;
            /* biru gelap (slate-900) */
            font-family: 'Segoe UI', Tahoma, sans-serif;
            margin: 0;
            padding: 20px;
            color: #e5e7eb;
        }


        h2 {
            color: #ebebebff;
            margin-bottom: 20px;
        }

        /* ===== TABLE ===== */
        .table-wrapper {
            background: #e0f2fe;
            /* biru muda */
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #f8fafc;
            border-radius: 10px;
            overflow: hidden;
        }

        thead {
            background: #2563eb;
            /* biru header */
            color: #ffffff;
        }

        thead th {
            padding: 12px;
            font-size: 14px;
            text-align: left;
        }

        tbody td {
            padding: 12px;
            font-size: 14px;
            color: #1e293b;
            border-bottom: 1px solid #cbd5f5;
        }

        tbody tr:nth-child(even) {
            background: #eff6ff;
        }

        tbody tr:hover {
            background: #dbeafe;
        }


        .table-admin {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .table-admin thead {
            background: linear-gradient(90deg, #1e3a8a, #2563eb);
            color: #fff;
        }

        .table-admin th,
        .table-admin td {
            padding: 12px 14px;
            text-align: center;
            font-size: 14px;
        }

        .table-admin tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        .table-admin tbody tr:hover {
            background: #eff6ff;
        }

        /* ===== BADGE ===== */
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-dikirim {
            background: #2563eb;
            color: #fff;
        }

        .badge-selesai {
            background: #16a34a;
            color: #fff;
        }

        /* ===== BUTTON ===== */
        .btn-selesai {
            background: #16a34a;
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-selesai:hover {
            background: #15803d;
        }

        .btn-hapus {
            background: #dc2626;
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-hapus:hover {
            background: #b91c1c;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .table-admin {
                font-size: 12px;
            }
        }

        /* ===== NAVBAR ===== */
        .navbar-admin {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(90deg, #1e3a8a, #2563eb);
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .navbar-title {
            font-size: 20px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0.5px;
        }

        /* ===== BUTTON BACK ===== */
        .btn-back {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(4px);
        }

        /* HOVER EFFECT */
        .btn-back:hover {
            background: #ffffff;
            color: #1e3a8a;
            transform: translateX(-4px);
            box-shadow: 0 6px 15px rgba(255, 255, 255, 0.35);
        }

        .btn-update {
            background: #2563eb;
            /* biru */
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-update:hover {
            background: #1e40af;
        }
    </style>
</head>

<body>

    <nav class="navbar-admin">
        <div class="navbar-title">
            📦 Manajemen Pengiriman
        </div>

        <a href="admin.php" class="btn-back">
            ← Kembali ke Dashboard
        </a>
    </nav>


    <h2>📦 Tabel Pengiriman</h2>

    <div class="table-wrapper">
        <table class="table-admin">
            <thead>
                <tr>
                    <th>ID Kirim</th>
                    <th>Nama Customer</th>
                    <th>ID Jual</th>
                    <th>Kurir</th>
                    <th>Tgl Kirim</th>
                    <th>Tgl Tiba</th>
                    <th>No Resi</th>
                    <th>Ongkir</th>
                    <th>Status Kirim</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?= $row['id_kirim']; ?></td>
                        <td><?= htmlspecialchars($row['nama_cust']); ?></td>
                        <td><?= $row['id_jual']; ?></td>
                        <td><?= $row['kurir']; ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tgl_kirim'])); ?></td>
                        <td>
                            <?= $row['tgl_tiba']
                                ? date('d-m-Y', strtotime($row['tgl_tiba']))
                                : '-' ?>
                        </td>
                        <td><?= $row['no_resi']; ?></td>
                        <td>Rp <?= number_format($row['ongkir'], 0, ',', '.'); ?></td>
                        <td>
                            <?php if ($row['status_kirim'] === 'Sudah Tiba'): ?>
                                <span class="badge badge-selesai">Sudah Tiba</span>
                            <?php else: ?>
                                <span class="badge badge-dikirim">Dalam Pengiriman</span>
                            <?php endif; ?>
                        <td>
                            <a href="javascript:void(0)"
                                class="btn-update"
                                onclick="openUpdatePengiriman(
            <?= $row['id_kirim']; ?>,
            '<?= $row['kurir']; ?>',
            '<?= $row['tgl_kirim']; ?>',
            '<?= $row['no_resi']; ?>',
            <?= $row['ongkir']; ?>
        )">
                                Update
                            </a>

                            <?php if ($row['status_kirim'] !== 'Sudah Tiba'): ?>
                                <a href="javascript:void(0)"
                                    class="btn-selesai"
                                    onclick="selesaikanPengiriman(<?= $row['id_kirim']; ?>)">
                                    Selesai
                                </a>
                            <?php endif; ?>

                            <a href="javascript:void(0)"
                                class="btn-hapus"
                                onclick="hapusPengiriman(<?= $row['id_kirim']; ?>)">
                                Hapus
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <?php if (!empty($_SESSION['flash'])): ?>
        <script>
            Swal.fire({
                icon: '<?= $_SESSION['flash']['type']; ?>',
                title: '<?= $_SESSION['flash']['type'] === 'success' ? 'Berhasil' : 'Gagal'; ?>',
                text: '<?= $_SESSION['flash']['msg']; ?>',
                timer: 2500,
                showConfirmButton: false
            });
        </script>
    <?php unset($_SESSION['flash']);
    endif; ?>



    <script>
        function openUpdatePengiriman(id, kurir, tglKirim, resi, ongkir) {
            Swal.fire({
                title: '✏️ Update Pengiriman',
                html: `
            <input id="kurir" class="swal2-input" placeholder="Kurir" value="${kurir}">
            <input id="tgl_kirim" type="date" class="swal2-input" value="${tglKirim}">
            <input id="no_resi" class="swal2-input" placeholder="No Resi" value="${resi}">
            <input id="ongkir" type="number" class="swal2-input" placeholder="Ongkir" value="${ongkir}">
        `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: '💾 Simpan',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    const data = {
                        id_kirim: id,
                        kurir: document.getElementById('kurir').value,
                        tgl_kirim: document.getElementById('tgl_kirim').value,
                        no_resi: document.getElementById('no_resi').value,
                        ongkir: document.getElementById('ongkir').value
                    };

                    if (!data.kurir || !data.tgl_kirim || !data.no_resi || !data.ongkir) {
                        Swal.showValidationMessage('Semua field wajib diisi');
                        return false;
                    }

                    return data;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitUpdatePengiriman(result.value);
                }
            });
        }

        function submitUpdatePengiriman(data) {
            fetch('logic_admin/pengiriman_update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data pengiriman berhasil diperbarui',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Terjadi kesalahan server', 'error');
                });
        }

        function selesaikanPengiriman(idKirim) {
            Swal.fire({
                title: 'Selesaikan Pengiriman?',
                text: 'Status pengiriman akan diubah menjadi "Sudah Tiba"',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {

                    Swal.fire({
                        title: 'Masukkan Tanggal Tiba',
                        input: 'date',
                        inputLabel: 'Tanggal paket diterima',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Tanggal tiba wajib diisi!';
                            }
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Simpan'
                    }).then((res) => {
                        if (res.isConfirmed) {
                            updatePengirimanSelesai(idKirim, res.value);
                        }
                    });

                }
            });
        }

        function updatePengirimanSelesai(idKirim, tglTiba) {
            fetch('logic_admin/pengiriman_selesai.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id_kirim=${idKirim}&tgl_tiba=${tglTiba}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Pengiriman telah diselesaikan'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Terjadi kesalahan server', 'error');
                });
        }
    </script>

    <script>
        function hapusPengiriman(id) {
            Swal.fire({
                title: 'Hapus Data?',
                text: 'Data pengiriman akan dihapus',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Hapus'
            }).then((res) => {
                if (res.isConfirmed) {
                    window.location = 'logic_admin/pengiriman_delete.php?id=' + id;
                }
            });
        }
    </script>

</body>

</html>