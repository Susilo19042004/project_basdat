<?php
include 'koneksi.php';

function hitung_predikat($nilai) {
    if ($nilai >= 90) return 'Istimewa';
    if ($nilai >= 86) return 'Sangat Baik';
    if ($nilai >= 78) return 'Baik';
    if ($nilai >= 65) return 'Cukup';
    return 'Kurang';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_siswa = $_POST['nama_siswa'];
    $nama_project = $_POST['nama_project'];
    $tanggal = $_POST['tanggal'];
    $catatan = $_POST['catatan'];
    $is_edit = isset($_POST['edit_id']);

    if ($is_edit) {
        $edit_id = $_POST['edit_id'];
        $conn->query("DELETE FROM penilaian WHERE proyek_id = $edit_id");
        $stmt = $conn->prepare("UPDATE proyek SET nama_siswa=?, nama_project=?, tanggal=?, catatan=? WHERE id=?");
        $stmt->bind_param("ssssi", $nama_siswa, $nama_project, $tanggal, $catatan, $edit_id);
        $stmt->execute();
        $proyek_id = $edit_id;
    } else {
        $stmt = $conn->prepare("INSERT INTO proyek (nama_siswa, nama_project, tanggal, catatan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama_siswa, $nama_project, $tanggal, $catatan);
        $stmt->execute();
        $proyek_id = $stmt->insert_id;
    }

    foreach ($_POST['aspek'] as $i => $aspek) {
        foreach ($_POST['sub_aspek'][$i] as $j => $sub_aspek) {
            $kesalahan = $_POST['kesalahan'][$i][$j];
            $stmt2 = $conn->prepare("INSERT INTO penilaian (proyek_id, aspek, sub_aspek, jumlah_kesalahan) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("issi", $proyek_id, $aspek, $sub_aspek, $kesalahan);
            $stmt2->execute();
        }
    }
    header("Location: index.php?success=1");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM penilaian WHERE proyek_id = $id");
    $conn->query("DELETE FROM proyek WHERE id = $id");
    header("Location: index.php?deleted=1");
    exit;
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM proyek WHERE id = $edit_id");
    $edit_data = $result->fetch_assoc();

    $penilaian = $conn->query("SELECT * FROM penilaian WHERE proyek_id = $edit_id");
    $aspek_data = [];
    while ($row = $penilaian->fetch_assoc()) {
        $aspek_data[$row['aspek']][] = $row;
    }
}

$riwayat = $conn->query("SELECT * FROM proyek ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Form Penilaian Proyek</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    textarea { resize: none; }
    .table td input, .table td textarea { width: 100%; }
  </style>
</head>
<body class="bg-light py-4">
<div class="container">
  <h2 class="mb-4">Form Penilaian Proyek</h2>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Data berhasil disimpan!</div>
  <?php elseif (isset($_GET['deleted'])): ?>
    <div class="alert alert-danger">Data berhasil dihapus.</div>
  <?php endif; ?>

  <form method="POST">
    <?php if ($edit_data): ?>
      <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
    <?php endif; ?>

    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label>Nama Siswa</label>
        <input type="text" name="nama_siswa" class="form-control" required value="<?= $edit_data['nama_siswa'] ?? '' ?>">
      </div>
      <div class="col-md-6">
        <label>Nama Proyek</label>
        <input type="text" name="nama_project" class="form-control" required value="<?= $edit_data['nama_project'] ?? '' ?>">
      </div>
      <div class="col-md-6">
        <label>Tanggal</label>
        <input type="date" name="tanggal" class="form-control" required value="<?= $edit_data['tanggal'] ?? '' ?>">
      </div>
      <div class="col-md-6">
        <label>Catatan Penguji</label>
        <textarea name="catatan" class="form-control"><?= $edit_data['catatan'] ?? '' ?></textarea>
      </div>
    </div>

    <div id="aspek-wrapper"></div>
    <button type="button" class="btn btn-outline-primary mb-3" onclick="tambahAspek()">+ Tambah Aspek</button>

    <div class="mb-3">
      <button type="submit" name="submit_penilaian" class="btn btn-success">Simpan Penilaian</button>
    </div>
  </form>

  <hr class="my-5">

  <h3>Riwayat Penilaian</h3>
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>Nama Siswa</th>
        <th>Proyek</th>
        <th>Tanggal</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($r = $riwayat->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($r['nama_siswa']) ?></td>
          <td><?= htmlspecialchars($r['nama_project']) ?></td>
          <td><?= $r['tanggal'] ?></td>
          <td>
            <a href="?edit=<?= $r['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="?hapus=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</a>
            <a href="detail.php?id=<?= $r['id'] ?>" class="btn btn-info btn-sm">Detail</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
let aspekIndex = 0;

function tambahAspek(aspek = '', subaspek = []) {
  const wrapper = document.getElementById('aspek-wrapper');
  const aspekId = aspekIndex;
  let aspekHTML = `
    <div class="card mb-3" id="aspek-${aspekId}">
      <div class="card-body">
        <div class="mb-2">
          <label>Aspek</label>
          <input type="text" name="aspek[${aspekId}]" class="form-control" value="${aspek}" required>
        </div>
        <div id="subaspek-${aspekId}"></div>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="tambahSubAspek(${aspekId})">+ Tambah Sub-Aspek</button>
      </div>
    </div>`;
  wrapper.insertAdjacentHTML('beforeend', aspekHTML);

  if (subaspek.length > 0) {
    subaspek.forEach(s => tambahSubAspek(aspekId, s.sub_aspek, s.jumlah_kesalahan));
  }

  aspekIndex++;
}

function tambahSubAspek(index, sub = '', kesalahan = '') {
  const container = document.getElementById(`subaspek-${index}`);
  const html = `
    <div class="row g-2 mb-2">
      <div class="col-md-6">
        <input type="text" name="sub_aspek[${index}][]" class="form-control" placeholder="Sub-Aspek" value="${sub}" required>
      </div>
      <div class="col-md-6">
        <input type="number" name="kesalahan[${index}][]" class="form-control" placeholder="Jumlah Kesalahan" value="${kesalahan}" required>
      </div>
    </div>`;
  container.insertAdjacentHTML('beforeend', html);
}

<?php if ($edit_data): ?>
const aspekPreload = <?= json_encode($aspek_data) ?>;
for (let [aspek, subaspeks] of Object.entries(aspekPreload)) {
  tambahAspek(aspek, subaspeks);
}
<?php endif; ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>