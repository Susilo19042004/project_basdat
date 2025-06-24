<?php
include 'koneksi.php';

$id = $_GET['id'];

// Ambil data proyek
$proyek = $conn->query("SELECT * FROM proyek WHERE id = $id")->fetch_assoc();

// Ambil data penilaian
$penilaian = $conn->query("SELECT * FROM penilaian WHERE proyek_id = $id");

$total_kesalahan = 0;
$penilaian_data = [];

while ($row = $penilaian->fetch_assoc()) {
    $penilaian_data[] = $row;
    $total_kesalahan += $row['jumlah_kesalahan'];
}

$total_nilai = 90 - $total_kesalahan;

// Hitung predikat
if ($total_nilai >= 86) $predikat = "Istimewa";
else if ($total_nilai >= 78) $predikat = "Sangat Baik";
else if ($total_nilai >= 65) $predikat = "Baik";
else $predikat = "Cukup";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Penilaian</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: center; }
        th { background-color: #f0f0f0; }
        a { text-decoration: none; color: blue; }
    </style>
</head>
<body>
    <h2>Detail Penilaian</h2>
    <a href="riwayat.php">&larr; Kembali ke Riwayat</a>

    <p><strong>Nama Project:</strong> <?= $proyek['nama_project'] ?></p>
    <p><strong>Nama Siswa:</strong> <?= $proyek['nama_siswa'] ?></p>
    <p><strong>Tanggal:</strong> <?= $proyek['tanggal'] ?></p>

    <table>
        <thead>
            <tr>
                <th>Aspek</th>
                <th>Sub-Aspek</th>
                <th>Jumlah Kesalahan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($penilaian_data as $data): ?>
                <tr>
                    <td><?= $data['aspek'] ?></td>
                    <td><?= $data['sub_aspek'] ?></td>
                    <td><?= $data['jumlah_kesalahan'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><strong>Total Kesalahan:</strong> <?= $total_kesalahan ?></p>
    <p><strong>Total Nilai:</strong> <?= $total_nilai ?></p>
    <p><strong>Predikat:</strong> <?= $predikat ?></p>
    <p><strong>Catatan Penguji:</strong> <?= $proyek['catatan'] ?></p>
</body>
</html>