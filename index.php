<?php
include 'config/database.php';

// Ambil total uang masuk, keluar, dan saldo akhir
$sql = "SELECT 
            SUM(CASE WHEN type = 'in' THEN amount ELSE 0 END) AS total_in,
            SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END) AS total_out,
            (SUM(CASE WHEN type = 'in' THEN amount ELSE 0 END) - 
             SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END)) AS balance
        FROM transactions";
$result = $conn->query($sql);
$data = $result->fetch_assoc();
$total_in = $data['total_in'] ?? 0;
$total_out = $data['total_out'] ?? 0;
$balance = $data['balance'] ?? 0;

// Ambil 5 transaksi terakhir
$sql_transactions = "SELECT t.*, m.name 
                     FROM transactions t 
                     JOIN members m ON t.member_id = m.id 
                     ORDER BY t.created_at DESC LIMIT 5";
$transactions = $conn->query($sql_transactions);

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Cek apakah transaksi dengan ID tersebut ada
    $check_sql = "SELECT * FROM transactions WHERE id = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("i", $delete_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Jika ada, lakukan penghapusan
        $delete_sql = "DELETE FROM transactions WHERE id = ?";
        $stmt_delete = $conn->prepare($delete_sql);
        $stmt_delete->bind_param("i", $delete_id);
        if ($stmt_delete->execute()) {
            header("Location: index.php"); // Redirect after successful delete
            exit;
        } else {
            echo "Gagal menghapus transaksi!";
        }
    } else {
        echo "Transaksi tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Uang Kas</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Styling for the dashboard */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 30%;
        }
        .card img {
            width: 40px;
            height: 40px;
            margin-bottom: 10px;
        }
        .card strong {
            display: block;
            margin: 10px 0;
            font-size: 18px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        .actions a {
            margin: 0 10px;
            text-decoration: none;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
        }
        .delete-btn {
            color: red;
            text-decoration: none;
        }
        .delete-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Dashboard Uang Kas</h1>
        
        <div class="cards">
            <div class="card">
                <img src="https://img.icons8.com/ios/452/money.png" alt="Uang Masuk">
                <strong>Total Uang Masuk</strong>
                <p>IDR <?= number_format($total_in, 2) ?></p>
            </div>
            <div class="card">
                <img src="https://img.icons8.com/ios/452/expense.png" alt="Uang Keluar">
                <strong>Total Uang Keluar</strong>
                <p>IDR <?= number_format($total_out, 2) ?></p>
            </div>
            <div class="card">
                <img src="https://img.icons8.com/ios/452/balance.png" alt="Saldo Akhir">
                <strong>Saldo Akhir</strong>
                <p>IDR <?= number_format($balance, 2) ?></p>
            </div>
        </div>

        <h2>Transaksi Terbaru</h2>
        <table>
            <thead>
                <tr>
                    <th>Nama Anggota</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Nominal</th>
                    <th>Jenis</th>
                    <th>Keterangan</th>
                    <th>Tanggal Dibuat</th>
                    <th>Aksi</th>
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
                            <a href="index.php?delete_id=<?= $row['id'] ?>" 
                               onclick="return confirm('Anda yakin ingin menghapus transaksi ini?')" class="delete-btn">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="actions">
            <a href="add_transaction.php">Tambah Transaksi</a>
            <a href="view_transactions.php">Lihat Semua Transaksi</a>
        </div>
    </div>
</body>
</html>
