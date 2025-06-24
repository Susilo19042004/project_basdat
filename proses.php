<?php
include 'koneksi.php';

$nama_project = $_POST['nama_project'];
$nama_siswa = $_POST['nama_siswa'];
$catatan = $_POST['catatan'];
$tanggal = date("Y-m-d");

// Simpan ke tabel proyek
$stmt = $conn->prepare("INSERT INTO proyek (nama_project, nama_siswa, tanggal, catatan) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nama_project, $nama_siswa, $tanggal, $catatan);
$stmt->execute();
$proyek_id = $stmt->insert_id;

// Simpan semua penilaian
$parameters = $_POST['parameter'];
$sub_aspeks = $_POST['sub_aspek'];
$jumlahs = $_POST['jumlah'];

for ($i = 0; $i < count($parameters); $i++) {
    if ($parameters[$i] != "" && $sub_aspeks[$i] != "") {
        $stmt = $conn->prepare("INSERT INTO penilaian (proyek_id, parameter, sub_aspek, jumlah_kesalahan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $proyek_id, $parameters[$i], $sub_aspeks[$i], $jumlahs[$i]);
        $stmt->execute();
    }
}

header("Location: index.php?status=sukses");
?>