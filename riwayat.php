<?php
include 'koneksi.php';

// Ambil data proyek dan total kesalahan
$sql = "SELECT p.id, p.nama_project, p.nama_siswa, p.tanggal, p.catatan, 
            SUM(jumlah_kesalahan) as total_kesalahan 
        FROM proyek p 
        JOIN penilaian n ON p.id = n.proyek_id 
        GROUP BY p.id 
        ORDER BY p.tanggal DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Penilaian</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: center; }
        th { background-color: #f0f0f0; }
        a { text-decoration: none; color: blue; }
    </style>
</head>
<body>

    <h2>Riwayat Penilaian Proyek</h2>
    <a href="index.php">+ Tambah Penilaian Baru</a>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Project</th>
                <th>Nama Siswa</th>
                <th>Tanggal</th>
                <th>Total Kesalahan</th>
                <th>Catatan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        while ($row = $result->fetch_assoc()) {
            $nilai = 90 - $row['total_kesalahan'];
            echo "<tr>
                <td>{$no}</td>
                <td>{$row['nama_project']}</td>
                <td>{$row['nama_siswa']}</td>
                <td>{$row['tanggal']}</td>
                <td>{$row['total_kesalahan']}</td>
                <td>{$row['catatan']}</td>
                <td>
                    <a href='detail.php?id={$row['id']}'>Lihat</a> | 
                    <a href='hapus.php?id={$row['id']}' onclick=\"return confirm('Hapus data ini?')\">Hapus</a>
                </td>
            </tr>";
            $no++;
        }
        ?>
        </tbody>
    </table>

</body>
</html>