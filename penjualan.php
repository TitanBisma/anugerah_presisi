<?php
session_start();
require_once __DIR__ . '/config/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

/* ======================
   QUERY DATA PENJUALAN
====================== */
$query = "
    SELECT 
        p.*,
        b.nama_brg
    FROM tb_penjualan p
    LEFT JOIN tb_barang b ON p.id_brg = b.id_brg
    ORDER BY p.id_jual DESC
";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<head>
    <meta charset="UTF-8">
    <title>Manajemen Penjualan</title>

    <style>
        /* ======================
                NAVBAR
        ====================== */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #020617;
            padding: 14px 25px;
            border-bottom: 1px solid #1e293b;
        }

        .nav-title {
            color: #93c5fd;
            font-size: 25px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* Tombol kembali */
        .btn-back {
            text-decoration: none;
            background: #1e40af;
            color: #e5e7eb;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            transition: all 0.25s ease;
            border: 1px solid #2563eb;
        }

        /* Hover effect */
        .btn-back:hover {
            background: #2563eb;
            transform: translateX(-4px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.4);
        }

        body {
            background: #0f172a;
            color: #e5e7eb;
            font-family: Arial, sans-serif;
        }

        h2 {
            margin-bottom: 15px;
            color: #93c5fd;
        }

        .container {
            padding: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #020617;
            font-size: 14px;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #1e293b;
            text-align: center;
        }

        th {
            background: #020617;
            color: #93c5fd;
        }

        tr:hover {
            background: #020617;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-belum {
            background: #334155;
        }

        .badge-wait {
            background: #1e40af;
        }

        .badge-lunas {
            background: #065f46;
        }

        .badge-tolak {
            background: #7f1d1d;
        }

        .aksi a {
            display: inline-block;
            margin: 2px;
            padding: 6px 8px;
            font-size: 12px;
            border-radius: 5px;
            text-decoration: none;
            color: #fff;
        }

        .btn-update {
            background: #2563eb;
        }

        .btn-hapus {
            background: #7f1d1d;
        }

        .btn-terima {
            background: #047857;
        }

        .btn-tolak {
            background: #b91c1c;
        }

        .btn-email {
            background: #0ea5e9;
        }

        .btn-konfirmasi {
            background: #16a34a;
        }

        .btn-selesai {
            background: #4ade80;
            color: #000;
        }

        /* =========================
   EDIT MODAL BACKDROP
========================= */
        .modal-edit {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(2, 6, 23, 0.8);
        }

        /* =========================
   EDIT MODAL BOX
========================= */
        .modal-edit-content {
            background: #020617;
            width: 800px;
            margin: 8% auto;
            padding: 20px;
            border-radius: 8px;
            position: relative;
            color: #e5e7eb;
            border: 1px solid #1e293b;
            max-height: 80vh;
            overflow-y: auto;
        }

        /* TITLE */
        .modal-edit-content h3 {
            margin-bottom: 15px;
            color: #93c5fd;
        }

        /* =========================
   CLOSE BUTTON
========================= */
        .modal-edit .close {
            position: absolute;
            right: 12px;
            top: 10px;
            font-size: 22px;
            cursor: pointer;
            color: #94a3b8;
        }

        .modal-edit .close:hover {
            color: #fff;
        }

        /* =========================
   FORM STYLE
========================= */
        .modal-edit-content label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            color: #93c5fd;
        }

        .modal-edit-content input,
        .modal-edit-content textarea,
        .modal-edit-content select {
            width: 98%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #1e293b;
            border-radius: 4px;
            background: #020617;
            color: #e5e7eb;
        }

        /* TEXTAREA KHUSUS */
        .modal-edit-content textarea {
            resize: vertical;
            min-height: 120px;
            max-height: 300px;
            overflow-y: auto;
        }

        /* FOCUS */
        .modal-edit-content input:focus,
        .modal-edit-content textarea:focus,
        .modal-edit-content select:focus {
            outline: none;
            border-color: #2563eb;
        }

        /* =========================
   BUTTON
========================= */
        .modal-edit-content button {
            margin-top: 15px;
            width: 100%;
            padding: 10px;
            background: #1e40af;
            border: none;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.3s;
        }

        .modal-edit-content button:hover {
            background: #2563eb;
        }

        .btn-proses-pesanan {
            background-color: #2563eb;
            /* biru */
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
        }

        .btn-proses-pesanan:hover {
            background-color: #1e40af;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="nav-left">
            <span class="nav-title">Manajemen Penjualan</span>
        </div>

        <div class="nav-right">
            <a href="admin.php" class="btn-back">
                ← Kembali ke Dashboard
            </a>
        </div>
    </nav>


    <div class="container">
        <h2>Tabel Penjualan</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Barang</th>
                    <th>Customer</th>
                    <th>Kontak</th>
                    <th>Kota</th>
                    <th>Tgl Beli</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Jenis Bayar</th>
                    <th>Status Bayar</th>
                    <th>Status Order</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['id_jual']; ?></td>
                        <td><?= htmlspecialchars($row['nama_brg']); ?></td>
                        <td><?= htmlspecialchars($row['nama_cust']); ?></td>
                        <td>
                            <?= htmlspecialchars($row['email']); ?><br>
                            <?= htmlspecialchars($row['no_telp']); ?>
                        </td>
                        <td><?= htmlspecialchars($row['kota']); ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tgl_beli'])); ?></td>
                        <td><?= $row['qty']; ?></td>
                        <td>Rp <?= number_format($row['harga_total'], 0, ',', '.'); ?></td>
                        <td><?= $row['jenis_bayar']; ?></td>

                        <td>
                            <?php if ($row['status_bayar'] === 'Belum Bayar'): ?>
                                <span class="badge badge-belum">Belum Bayar</span>
                            <?php elseif ($row['status_bayar'] === 'Lunas DP1'): ?>
                                <span class="badge badge-wait">Lunas DP1</span>
                            <?php else: ?>
                                <span class="badge badge-lunas">Lunas Full</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <span class="badge badge-wait"><?= $row['status_order']; ?></span>
                        </td>

                        <td class="aksi">

                            <!-- UPDATE (SELALU ADA) -->
                            <a href="javascript:void(0)"
                                class="btn-update"
                                onclick="openEditModal(this)"
                                data-id="<?= $row['id_jual']; ?>"
                                data-nama="<?= htmlspecialchars($row['nama_cust']); ?>"
                                data-email="<?= htmlspecialchars($row['email']); ?>"
                                data-telp="<?= htmlspecialchars($row['no_telp']); ?>"
                                data-alamat="<?= htmlspecialchars($row['alamat']); ?>"
                                data-kota="<?= htmlspecialchars($row['kota']); ?>"
                                data-qty="<?= $row['qty']; ?>"
                                data-harga="<?= $row['harga_satuan']; ?>"
                                data-bayar="<?= $row['jenis_bayar']; ?>"
                                data-status-order="<?= $row['status_order']; ?>"
                                data-status-bayar="<?= $row['status_bayar']; ?>">
                                Update
                            </a>

                            <!-- HAPUS (HANYA JIKA SELESAI) -->
                            <?php if ($row['status_order'] === 'Selesai'): ?>
                                <a href="javascript:void(0)"
                                    class="btn-hapus"
                                    onclick="hapusPesanan(<?= $row['id_jual']; ?>)">
                                    Hapus
                                </a>

                            <?php endif; ?>

                            <?php if (in_array($row['status_order'], [
                                'Lunas Pembayaran',
                                'Dalam Pengerjaan',
                                'Barang Sudah Siap',
                                'Menunggu Dikirim'
                            ])): ?>
                                <a href="logic_admin/proses_pesanan.php?id=<?= $row['id_jual'] ?>"
                                    class="btn btn-proses-pesanan"
                                    data-id="<?= $row['id_jual'] ?>"
                                    data-nama="<?= htmlspecialchars($row['nama_cust']) ?>"
                                    data-email="<?= htmlspecialchars($row['email']) ?>"
                                    data-status="<?= $row['status_order'] ?>">
                                    🔄 Proses Pesanan
                                </a>

                            <?php endif; ?>

                            <?php
                            /* ======================
       LOGIKA AKSI BERDASARKAN STATUS ORDER
    ====================== */

                            /* ======================
       BELUM DITERIMA
    ====================== */
                            if ($row['status_order'] === 'Belum Diterima'): ?>

                                <a href="#" class="btn-terima"
                                    onclick="accOrder(<?= $row['id_jual']; ?>); return false;">
                                    Terima
                                </a>

                                <a href="#" class="btn-tolak"
                                    onclick="rejectOrder(<?= $row['id_jual']; ?>); return false;">
                                    Tolak
                                </a>

                            <?php
                            /* ======================
       MENUNGGU PEMBAYARAN
    ====================== */
                            elseif ($row['status_order'] === 'Menunggu Pembayaran'):

                                // Label konfirmasi
                                $labelKonfirmasi = 'Konfirmasi Pembayaran';

                                if ($row['jenis_bayar'] === 'DP') {
                                    if ($row['status_bayar'] === 'Lunas DP1') {
                                        $labelKonfirmasi = 'Konfirmasi Pelunasan';
                                    }
                                }
                            ?>

                                <!-- KIRIM EMAIL -->
                                <a href="javascript:void(0)"
                                    class="btn-email"
                                    onclick="confirmSendEmail(<?= $row['id_jual']; ?>)">
                                    Kirim Email
                                </a>

                                <!-- KONFIRMASI -->
                                <a href="#"
                                    class="btn-konfirmasi"
                                    onclick="confirmPayment(<?= $row['id_jual']; ?>); return false;">
                                    <?= $labelKonfirmasi; ?>
                                </a>

                                <!-- TOLAK -->
                                <a href="#"
                                    class="btn-tolak"
                                    onclick="rejectOrder(<?= $row['id_jual']; ?>); return false;">
                                    Tolak
                                </a>

                            <?php
                            /* ======================
       LUNAS PEMBAYARAN
    ====================== */
                            elseif ($row['status_order'] === 'Lunas Pembayaran'): ?>

                                <!-- KIRIM EMAIL -->
                                <a href="javascript:void(0)"
                                    class="btn-email"
                                    onclick="confirmSendEmail(<?= $row['id_jual']; ?>)">
                                    Kirim Email
                                </a>

                            <?php

                            /* ======================
       MENUNGGU PENGIRIMAN
    ====================== */
                            elseif ($row['status_order'] === 'Dalam Pengiriman'):

                            ?>
                                <a href="#" class="btn-selesai" onclick="selesaikanPesanan(<?= $row['id_jual']; ?>)"> Selesaikan </a>
                            <?php
                            /* ======================
       DITOLAK
    ====================== */
                            elseif ($row['status_order'] === 'Ditolak'): ?>

                                <a href="javascript:void(0)"
                                    class="btn-hapus"
                                    onclick="hapusPesanan(<?= $row['id_jual']; ?>)">
                                    Hapus
                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="modal-edit" id="editModal">
        <div class="modal-edit-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3>Edit Pesanan</h3>

            <form id="editForm">
                <input type="hidden" name="id_jual" id="edit_id">

                <label>Nama:</label>
                <input type="text" name="nama_cust" id="edit_nama" placeholder="Nama Customer" required>

                <label>Email:</label>
                <input type="email" name="email" id="edit_email" placeholder="Email" required>

                <label>Nomor Telp:</label>
                <input type="text" name="no_telp" id="edit_telp" placeholder="No Telp" required>

                <label>Alamat:</label>
                <textarea name="alamat" id="edit_alamat" placeholder="Alamat"></textarea>
                <input type="text" name="kota" id="edit_kota" placeholder="Kota" required>

                <label>Jumlah Barang:</label>
                <input type="number" name="qty" id="edit_qty" min="10" required>

                <label>Harga Satuan:</label>
                <input type="number" name="harga_satuan" id="edit_harga" required>

                <label>Jenis Pembayaran:</label>
                <select name="jenis_bayar" id="edit_bayar">
                    <option value="Full">Full</option>
                    <option value="DP">DP</option>
                </select>

                <label>Status Bayar:</label>
                <select name="status_bayar" id="edit_status_bayar">
                    <option value="">- Pilih Status Bayar -</option>
                    <option value="Belum Bayar">Belum Bayar</option>
                    <option value="Lunas DP1">Lunas DP1</option>
                    <option value="Lunas Full">Lunas Full</option>

                </select>

                <label>Status Order:</label>
                <select name="status_order" id="edit_status_order" required>
                    <option value="Belum Diterima">Belum Diterima</option>
                    <option value="Menunggu Pembayaran">Menunggu Pembayaran</option>
                    <option value="Lunas Pembayaran">Lunas Pembayaran</option>
                    <option value="Dalam Pengerjaan">Dalam Pengerjaan</option>
                    <option value="Barang Sudah Siap">Barang Sudah SIap</option>
                    <option value="Menunggu Dikirim">Menunggu Dikirim</option>
                    <option value="Dalam Pengiriman">Dalam Pengiriman</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Ditolak">Ditolak</option>
                </select>

                <div class="modal-actions">
                    <button type="button" onclick="closeEditModal()">Batal</button>
                    <button type="submit" style="background:#2563eb;color:#fff">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.btn-proses-pesanan').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                const idJual = this.dataset.id;
                const nama = this.dataset.nama;
                const email = this.dataset.email;
                const status = this.dataset.status;
                const url = this.getAttribute('href');

                // ===============================
                // JIKA STATUS = MENUNGGU DIKIRIM
                // ===============================
                if (status === 'Menunggu Dikirim') {

                    Swal.fire({
                        title: 'Input Data Pengiriman',
                        html: `
          <input id="kurir" class="swal2-input" placeholder="Nama Kurir">
          <input id="tgl_kirim" type="date" class="swal2-input">
          <input id="no_resi" class="swal2-input" placeholder="Nomor Resi">
          <input id="ongkir" type="number" class="swal2-input" placeholder="Ongkir">
        `,
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'Simpan & Kirim',
                        preConfirm: () => {
                            return {
                                id_jual: idJual,
                                kurir: document.getElementById('kurir').value,
                                tgl_kirim: document.getElementById('tgl_kirim').value,
                                no_resi: document.getElementById('no_resi').value,
                                ongkir: document.getElementById('ongkir').value
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {

                            fetch('logic_admin/pengiriman_store.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify(result.value)
                                })
                                .then(res => res.json())
                                .then(res => {
                                    if (res.ok) {
                                        Swal.fire(
                                            'Berhasil',
                                            'Data pengiriman disimpan & email terkirim ke customer',
                                            'success'
                                        ).then(() => location.reload());
                                    } else {
                                        Swal.fire('Gagal', res.message, 'error');
                                    }
                                });

                        }
                    });

                }
                // ===============================
                // STATUS LAIN → PROSES BIASA
                // ===============================
                else {

                    Swal.fire({
                        title: 'Proses Pesanan?',
                        html: `Pesanan atas nama <b>${nama}</b> akan diproses ke tahap berikutnya.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Proses',
                        cancelButtonText: 'Batal'
                    }).then(result => {
                        if (result.isConfirmed) {
                            window.location.href = url;
                        }
                    });

                }

            });
        });
    </script>



    <!-- Script Acc Pesanan -->
    <script>
        function accOrder(id) {
            Swal.fire({
                title: 'Terima Pesanan?',
                text: 'Pesanan akan diproses dan email invoice dikirim',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Terima',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengirim Email...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            window.location.href = 'logic_admin/resend_email.php?id=' + idJual;
                        }
                    });
                    window.location.href = 'logic_admin/jual_acc.php?id=' + id;
                }
            });
        }
    </script>

    <!-- Script Tolak Pesanan -->
    <script>
        function rejectOrder(id) {
            Swal.fire({
                title: 'Tolak Pesanan',
                input: 'textarea',
                inputLabel: 'Alasan penolakan',
                inputPlaceholder: 'Masukkan alasan...',
                showCancelButton: true,
                confirmButtonText: 'Kirim',
                cancelButtonText: 'Batal',
                preConfirm: (alasan) => {
                    if (!alasan) {
                        Swal.showValidationMessage('Alasan wajib diisi');
                    }
                    return alasan;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'logic_admin/jual_reject.php';

                    form.innerHTML = `
                <input type="hidden" name="id_jual" value="${id}">
                <input type="hidden" name="alasan" value="${result.value}">
            `;
                    document.body.appendChild(form);
                    form.submit();
                    Swal.fire({
                        title: 'Mengirim Email...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            window.location.href = 'logic_admin/resend_email.php?id=' + idJual;
                        }
                    });
                }
            });
        }
    </script>

    <!-- Script Update -->
    <script>
        const editModal = document.getElementById('editModal');
        const editForm = document.getElementById('editForm');
        const edit_harga = document.getElementById('edit_harga');


        const edit_id = document.getElementById('edit_id');
        const edit_nama = document.getElementById('edit_nama');
        const edit_email = document.getElementById('edit_email');
        const edit_telp = document.getElementById('edit_telp');
        const edit_alamat = document.getElementById('edit_alamat');
        const edit_kota = document.getElementById('edit_kota');
        const edit_qty = document.getElementById('edit_qty');
        const edit_status_bayar = document.getElementById('edit_status_bayar');
        const edit_status_order = document.getElementById('edit_status_order');

        function openEditModal(btn) {
            editModal.style.display = 'flex';

            edit_id.value = btn.dataset.id ?? '';
            edit_nama.value = btn.dataset.nama ?? '';
            edit_email.value = btn.dataset.email ?? '';
            edit_telp.value = btn.dataset.telp ?? '';
            edit_alamat.value = btn.dataset.alamat ?? '';
            edit_kota.value = btn.dataset.kota ?? '';
            edit_qty.value = btn.dataset.qty ?? '';
            edit_harga.value = btn.dataset.harga ?? '';
            edit_bayar.value = btn.dataset.bayar ?? '';
            edit_status_bayar.value = btn.dataset.statusBayar ?? '';
            edit_status_order.value = btn.dataset.statusOrder ?? '';

        }

        function closeEditModal() {
            editModal.style.display = 'none';
        }

        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();

                fetch('logic_admin/jual_update.php', {
                        method: 'POST',
                        body: new FormData(this)
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.ok) {
                            Swal.fire('Berhasil', 'Data berhasil diperbarui', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Gagal', res.message || 'Update gagal', 'error');
                        }
                    });
            });
        }
    </script>

    <!-- Script Konfirmasi Pembayaran -->
    <script>
        function confirmPayment(id) {
            Swal.fire({
                title: 'Konfirmasi Pembayaran?',
                text: 'Pembayaran akan dikonfirmasi dan email dikirim ke customer',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Konfirmasi',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengirim Email...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            window.location.href = 'logic_admin/resend_email.php?id=' + idJual;
                        }
                    });
                    window.location.href = 'logic_admin/jual_payment.php?id=' + id;
                }
            });
        }
    </script>

    <!-- Script Konfirmasi Kirim Email -->
    <script>
        function confirmSendEmail(idJual) {
            Swal.fire({
                title: 'Kirim Email?',
                text: 'Email akan dikirim ke customer terkait pesanan ini.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Kirim',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengirim Email...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                            window.location.href = 'logic_admin/resend_email.php?id=' + idJual;
                        }
                    });
                    window.location.href = 'logic_admin/resend_email.php?id=' + idJual;
                }
            });
        }
    </script>

    <!-- Script Selesai Penjualan -->

    <script>
        function selesaikanPesanan(idJual) {
            Swal.fire({
                title: 'Selesaikan Pesanan?',
                text: 'Pesanan akan ditandai sebagai SELESAI',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, selesaikan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('logic_admin/penjualan_selesai.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `id_jual=${idJual}`
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Pesanan telah diselesaikan'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Gagal', data.message, 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Gagal Menyelesaikan Penjualan', 'Status pengiriman barang ini belum diselesaikan', 'error');
                        });
                }
            });
        }
    </script>

    <!-- Script Hapus Pesanan -->
    <script>
        function hapusPesanan(idJual) {
            Swal.fire({
                title: 'Hapus Pesanan?',
                text: 'Pesanan yang sudah selesai akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logic_admin/penjualan_delete.php?id=' + encodeURIComponent(idJual);
                }
            });
        }
    </script>


</body>

</html>