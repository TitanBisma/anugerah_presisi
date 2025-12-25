<?php
session_start();
require_once __DIR__ . '/config/config.php';


// Ambil data barang
$query = "SELECT * FROM tb_barang ORDER BY id_brg DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <title>Manajemen Katalog Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #0f172a;
            /* dark blue */
            margin: 0;
            padding: 0;
            color: #e5e7eb;
        }

        /* HEADER */
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

        .navbar a:hover {
            background: #2563eb;
        }

        .navbar a.danger {
            background: #b91c1c;
        }

        .navbar a.danger:hover {
            background: #dc2626;
        }

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


        .header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-left h2 {
            margin: 0;
            margin-right: 10px;
            color: #e5e7eb;
        }

        /* BUTTON */
        .header a {
            text-decoration: none;
            padding: 8px 14px;
            background: #1e40af;
            color: #fff;
            border-radius: 4px;
            font-size: 14px;
            transition: 0.3s;
        }

        .header a:hover {
            background: #2563eb;
        }

        .header a.danger {
            background: #b91c1c;
        }

        .header a.danger:hover {
            background: #dc2626;
        }

        /* BACK BUTTON */



        /* CONTAINER */
        .container {
            padding: 20px;
        }

        h2 {
            color: #e5e7eb;
            margin-bottom: 15px;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #020617;
            color: #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #1e293b;
            vertical-align: top;
        }

        th {
            background: #020617;
            color: #93c5fd;
            text-align: left;
        }

        tr:nth-child(even) {
            background: #020617;
        }

        tr:hover {
            background: #020617;
        }

        img {
            max-width: 80px;
            border-radius: 4px;
            border: 1px solid #1e293b;
        }

        /* AKSI */
        .aksi a {
            margin-right: 5px;
            padding: 5px 10px;
            font-size: 12px;
            color: #fff;
            border-radius: 3px;
            text-decoration: none;
            transition: 0.3s;
        }

        .aksi .edit {
            background: #ca8a04;
        }

        .aksi .edit:hover {
            background: #eab308;
        }

        .aksi .hapus {
            background: #b91c1c;
        }

        .aksi .hapus:hover {
            background: #dc2626;
        }

        /* MODAL BACKDROP */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(2, 6, 23, 0.8);
        }

        /* MODAL BOX */
        .modal-content {
            background: #020617;
            width: 800px;
            margin: 8% auto;
            padding: 20px;
            border-radius: 8px;
            position: relative;
            color: #e5e7eb;
            border: 1px solid #1e293b;
            align-items: center;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-content h3 {
            text-justify: inter-word;
        }

        /* CLOSE BUTTON */
        .modal .close {
            position: absolute;
            right: 12px;
            top: 10px;
            font-size: 22px;
            cursor: pointer;
            color: #94a3b8;
        }

        .modal .close:hover {
            color: #fff;
        }

        /* FORM */
        .modal-content label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            color: #93c5fd;
        }

        .modal-content input,
        .modal-content textarea {
            width: 98%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #1e293b;
            border-radius: 4px;
            background: #020617;
            color: #e5e7eb;
        }

        /* KHUSUS TEXTAREA DESKRIPSI */
        .textarea-deskripsi {
            resize: vertical;
            /* hanya bisa ke bawah */
            min-height: 120px;
            max-height: 300px;
            /* tidak tembus modal */
            overflow-y: auto;
        }


        .modal-content input:focus,
        .modal-content textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        .modal-content button {
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

        .modal-content button:hover {
            background: #2563eb;
        }

        /* IMAGE PREVIEW */
        .img-preview {
            cursor: pointer;
            transition: 0.3s;
        }

        .img-preview:hover {
            transform: scale(1.05);
            border-color: #2563eb;
        }

        /* MODAL IMAGE KHUSUS */
        .modal-image {
            width: auto;
            max-width: 90%;
            text-align: center;
        }

        .modal-image img {
            max-width: 100%;
            max-height: 80vh;
            border-radius: 6px;
            border: 1px solid #1e293b;
        }

        /* HEADER DI ATAS TABEL */
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        .table-actions a {
            text-decoration: none;
            padding: 8px 14px;
            background: #1e40af;
            color: #fff;
            border-radius: 4px;
            font-size: 14px;
            transition: 0.3s;
        }

        .table-actions a:hover {
            background: #2563eb;
        }

        .table-actions a.danger {
            background: #b91c1c;
        }

        .table-actions a.danger:hover {
            background: #dc2626;
        }
    </style>

</head>

<body>

    <!-- Top Navbar -->

    <!-- HEADER -->
    <nav class="navbar">
        <div class="nav-left">
            <span class="nav-title">Manajemen Katalog Barang</span>
        </div>

        <div class="nav-right">
            <a href="admin.php" class="btn-back">
                ← Kembali ke Dashboard
            </a>
        </div>
    </nav>





    </div>

    <div class="container">

        <div class="modal" id="modalTambah">
            <div class="modal-content">
                <span class="close">&times;</span>

                <h3>Tambah Barang</h3>

                <form action="/presisi/logic_admin/crupdate.php" method="POST" enctype="multipart/form-data">

                    <input type="hidden" name="action" value="create">

                    <label>Nama Barang</label>
                    <input type="text" name="nama_brg" required>

                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="textarea-deskripsi"></textarea>


                    <label>Harga</label>
                    <input type="number" name="harga" required>

                    <label>Foto</label>
                    <input type="file" name="foto">

                    <button type="submit">Simpan</button>
                </form>
            </div>
        </div>

        <div class="modal" id="modalEdit">
            <div class="modal-content">
                <span class="close" id="closeEdit">&times;</span>

                <h3>Update Barang</h3>

                <form action="/presisi/logic_admin/crupdate.php" method="POST" enctype="multipart/form-data">

                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_brg" id="edit_id">
                    <input type="hidden" name="foto_lama" id="edit_foto_lama">

                    <label>Nama Barang</label>
                    <input type="text" name="nama_brg" id="edit_nama" required>

                    <label>Deskripsi</label>
                    <textarea name="deskripsi" id="edit_deskripsi" class="textarea-deskripsi"></textarea>

                    <label>Harga</label>
                    <input type="number" name="harga" id="edit_harga" required>

                    <label>Foto (opsional)</label>
                    <input type="file" name="foto">

                    <button type="submit">Update</button>
                </form>
            </div>
        </div>


        <div class="container">

            <div class="table-header">
                <h2>Tabel Katalog Barang</h2>

                <div class="table-actions">
                    <a href="#" id="btnTambah">➕ Tambah Data</a>
                    <a href="javascript:void(0)"
                        class="danger"
                        onclick="hapusSemua()">
                        🗑 Hapus Semua
                    </a>

                </div>
            </div>


            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th class="col-foto">Foto</th>
                        <th>Deskripsi</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $row['id_brg']; ?></td>
                                <td><?= htmlspecialchars($row['nama_brg']); ?></td>
                                <td>
                                    <img
                                        src="<?= htmlspecialchars($row['urlfoto']); ?>"
                                        alt="foto"
                                        class="img-preview"
                                        data-src="<?= htmlspecialchars($row['urlfoto']); ?>">
                                </td>
                                <td><?= htmlspecialchars($row['deskripsi']); ?></td>
                                <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                                <td class="aksi">
                                    <a href="#"
                                        class="edit btnEdit"
                                        data-id="<?= $row['id_brg']; ?>"
                                        data-nama="<?= htmlspecialchars($row['nama_brg']); ?>"
                                        data-deskripsi="<?= htmlspecialchars($row['deskripsi']); ?>"
                                        data-harga="<?= $row['harga']; ?>"
                                        data-foto="<?= htmlspecialchars($row['urlfoto']); ?>">
                                        Update
                                    </a>
                                    <a href="/presisi/logic_admin/crupdate.php?action=delete&id=<?= $row['id_brg']; ?>"
                                        class="hapus"
                                        onclick="return confirm('Yakin ingin menghapus data ini?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" align="center">Data katalog masih kosong</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- MODAL PREVIEW IMAGE -->
        <div class="modal" id="modalImage">
            <div class="modal-content modal-image">
                <span class="close" id="closeImage">&times;</span>
                <img id="previewImage" src="" alt="Preview">
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <?php if (isset($_SESSION['flash'])): ?>
            <script>
                Swal.fire({
                    icon: '<?= $_SESSION['flash']['type'] ?>',
                    title: '<?= $_SESSION['flash']['type'] === 'success' ? 'Berhasil' : 'Gagal' ?>',
                    text: '<?= $_SESSION['flash']['msg'] ?>',
                    timer: 2500,
                    showConfirmButton: false
                });
            </script>
        <?php unset($_SESSION['flash']);
        endif; ?>

        <!-- Sweet alert Modal -->
        <script>
            const modal = document.getElementById('modalTambah');
            const btn = document.getElementById('btnTambah');
            const closeBtn = document.querySelector('.close');

            btn.onclick = function(e) {
                e.preventDefault();
                modal.style.display = 'block';
            }

            closeBtn.onclick = function() {
                modal.style.display = 'none';
            }

            window.onclick = function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            }
        </script>

        <!-- Sweet alert Tambah data -->
        <?php if (isset($_SESSION['flash'])): ?>
            <script>
                Swal.fire({
                    icon: '<?= $_SESSION['flash']['type']; ?>',
                    title: '<?= $_SESSION['flash']['type'] === 'success' ? 'Berhasil' : 'Gagal'; ?>',
                    text: '<?= $_SESSION['flash']['msg']; ?>',
                    timer: 2000,
                    showConfirmButton: false
                });
            </script>
        <?php unset($_SESSION['flash']);
        endif; ?>

        <!-- Script Preview Image -->
        <script>
            const imgModal = document.getElementById('modalImage');
            const previewImg = document.getElementById('previewImage');
            const closeImg = document.getElementById('closeImage');

            document.querySelectorAll('.img-preview').forEach(img => {
                img.addEventListener('click', () => {
                    previewImg.src = img.dataset.src;
                    imgModal.style.display = 'block';
                });
            });

            closeImg.onclick = () => {
                imgModal.style.display = 'none';
                previewImg.src = '';
            };

            imgModal.onclick = (e) => {
                if (e.target === imgModal) {
                    imgModal.style.display = 'none';
                    previewImg.src = '';
                }
            };
        </script>

        <!-- Script Edit -->
        <script>
            const modalEdit = document.getElementById('modalEdit');
            const closeEdit = document.getElementById('closeEdit');

            document.querySelectorAll('.btnEdit').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();

                    document.getElementById('edit_id').value = this.dataset.id;
                    document.getElementById('edit_nama').value = this.dataset.nama;
                    document.getElementById('edit_deskripsi').value = this.dataset.deskripsi;
                    document.getElementById('edit_harga').value = this.dataset.harga;
                    document.getElementById('edit_foto_lama').value = this.dataset.foto;

                    modalEdit.style.display = 'block';
                });
            });

            closeEdit.onclick = () => {
                modalEdit.style.display = 'none';
            };

            window.onclick = function(e) {
                if (e.target === modalEdit) {
                    modalEdit.style.display = 'none';
                }
            };
        </script>

        <!-- Script Hapus per Item -->
        <script>
            function hapusBarang(id) {
                Swal.fire({
                    title: 'Hapus Barang?',
                    text: 'Barang hanya bisa dihapus jika tidak ada pesanan aktif',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#2563eb',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.location = 'logic_admin/crupdate.php?action=delete&id=' + id;
                    }
                });
            }
        </script>

        <!-- Script Hapus Semua -->
        <script>
            function hapusSemua() {
                Swal.fire({
                    title: 'Hapus Semua Barang?',
                    text: 'Semua barang akan dihapus permanen',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#2563eb',
                    confirmButtonText: 'Ya, hapus semua',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.location = 'logic_admin/crupdate.php?action=delete_all';
                    }
                });
            }
        </script>



</body>

</html>