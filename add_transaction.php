<?php
include 'config/database.php';

$message = ''; // Variabel untuk menampilkan pesan

// Array untuk mengonversi bulan teks menjadi angka
$bulan_angka = [
    'Januari' => 1,
    'Februari' => 2,
    'Maret' => 3,
    'April' => 4,
    'Mei' => 5,
    'Juni' => 6,
    'Juli' => 7,
    'Agustus' => 8,
    'September' => 9,
    'Oktober' => 10,
    'November' => 11,
    'Desember' => 12
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id = $_POST['member_id'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    
    // Cek apakah bulan valid dan konversikan ke angka
    if (array_key_exists($month, $bulan_angka)) {
        $month_number = $bulan_angka[$month];
    } else {
        $message = "Bulan yang dimasukkan tidak valid!";
        exit;
    }

    // Proses upload file
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
        $file_name = $_FILES['receipt']['name'];
        $file_tmp = $_FILES['receipt']['tmp_name'];
        $file_size = $_FILES['receipt']['size'];
        $file_type = $_FILES['receipt']['type'];
        
        // Tentukan direktori penyimpanan file
        $upload_dir = __DIR__ . '/uploads/';
        
        // Cek apakah direktori uploads ada, jika tidak, buat direktori tersebut
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Membuat nama file unik
        $new_file_name = uniqid() . '-' . $file_name;
        $file_path = $upload_dir . $new_file_name;
        
        // Tentukan jenis file yang diizinkan
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        
        // Cek tipe file
        if (in_array($file_type, $allowed_types)) {
            if ($file_size <= 5000000) { // Maksimal 5MB
                if (move_uploaded_file($file_tmp, $file_path)) {
                    $sql = "INSERT INTO transactions (member_id, month, year, amount, type, description, receipt, updated_at) 
        VALUES ('$member_id', '$month', '$year', '$amount', '$type', '$description', '$file_path', NOW())";

                    if ($conn->query($sql)) {
                        $message = "Transaksi berhasil ditambahkan!";
                        header("Location: index.php");
                        exit;
                    } else {
                        $message = "Gagal menambahkan transaksi: " . $conn->error;
                    }
                } else {
                    $message = "Gagal meng-upload bukti transaksi!";
                }
            } else {
                $message = "Ukuran file terlalu besar, maksimal 5MB!";
            }
        } else {
            $message = "Tipe file tidak diizinkan. Harus berupa gambar (JPEG/PNG).";
        }
    } else {
        $message = "File bukti transaksi tidak diunggah!";
    }
}

// Ambil daftar anggota
$members = $conn->query("SELECT * FROM members");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Transaksi</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
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
        .message {
            margin: 10px 0;
            padding: 10px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        label {
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
        a:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tambah Transaksi</h1>
        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'Gagal') !== false ? 'error' : 'success' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <label for="member_id">Nama Anggota:</label>
            <select name="member_id" id="member_id" required>
                <option value="">Pilih Anggota</option>
                <?php while ($row = $members->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                <?php endwhile; ?>
            </select><br>
            
            <label for="month">Bulan:</label>
            <input type="text" name="month" id="month" placeholder="Contoh: Januari" required><br>
            
            <label for="year">Tahun:</label>
            <input type="number" name="year" id="year" value="<?= date('Y') ?>" required><br>
            
            <label for="amount">Nominal:</label>
            <input type="number" name="amount" id="amount" required><br>
            
            <label for="type">Jenis:</label>
            <select name="type" id="type" required>
                <option value="in">Uang Masuk</option>
                <option value="out">Uang Keluar</option>
            </select><br>
            
            <label for="description">Keterangan:</label>
            <input type="text" name="description" id="description"><br>
            
            <label for="receipt">Bukti Transaksi (gambar):</label>
            <input type="file" name="receipt" id="receipt"><br>
            
            <button type="submit">Simpan Transaksi</button>
        </form>
        <a href="index.php">Kembali ke Dashboard</a>
    </div>
</body>
</html>
