<?php
include 'config/database.php';

$sql = "SELECT t.*, m.name FROM transactions t 
        JOIN members m ON t.member_id = m.id 
        ORDER BY t.created_at DESC";
$transactions = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Transaksi</title>
    <style>
        /* Gaya untuk gambar bukti transaksi */
        .transaction-image {
            width: 200px; /* Menentukan lebar gambar */
            height: auto; /* Membiarkan tinggi gambar beradaptasi dengan lebar */
            max-width: 100%; /* Agar gambar tidak melebihi lebar kontainer */
            display: block; /* Menghindari spasi ekstra di bawah gambar */
            margin: 0 auto; /* Memusatkan gambar */
        }
    </style>
</head>
<body>
    <h1>Lihat Transaksi</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Nama Anggota</th>
                <th>Bulan</th>
                <th>Tahun</th>
                <th>Nominal</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th>Tanggal Dibuat</th>
                <th>Bukti Transaksi</th> <!-- New column for image -->
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $transactions->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['month'] ?></td>
                    <td><?= $row['year'] ?></td>
                    <td>IDR <?= number_format($row['amount'], 2) ?></td>
                    <td><?= $row['type'] == 'in' ? 'Masuk' : 'Keluar' ?></td>
                    <td><?= $row['description'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <?php if (!empty($row['receipt'])): ?>
                            <!-- Menggunakan class CSS untuk menampilkan gambar lebih besar -->
                            <img src="uploads/<?= basename($row['receipt']) ?>" alt="Bukti Transaksi" class="transaction-image">
                        <?php else: ?>
                            Tidak ada bukti
                        <?php endif; ?>
                    </td> <!-- Display the image or show message if no image -->
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
