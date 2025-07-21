<?php
$host = "localhost"; 
$user = "root";           // user default XAMPP
$pass = "";               
$db   = "ujian_online";   
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>

<!-- ===== File: register.php ===== -->
<?php
include 'config.php';

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama = $_POST['nama'];
    $nis = $_POST['nis'];
    $email = $_POST['email'];
    $kelas = $_POST['kelas'];

    $queryUser = mysqli_query($conn, "INSERT INTO users (username, password, nama_lengkap, role)
                 VALUES ('$username', '$password', '$nama', 'siswa')");
    $userId = mysqli_insert_id($conn);

    $querySiswa = mysqli_query($conn, "INSERT INTO siswa (user_id, nis, email, kelas)
                  VALUES ('$userId', '$nis', '$email', '$kelas')");

    echo "Registrasi berhasil. <a href='login.php'>Login sekarang</a>";
}
?>
<form method="POST">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <input type="text" name="nama" placeholder="Nama Lengkap" required><br>
    <input type="text" name="nis" placeholder="NIS" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="text" name="kelas" placeholder="Kelas" required><br>
    <button type="submit" name="submit">Daftar</button>
</form>

<!-- ===== File: login.php ===== -->
<?php
session_start();
include 'config.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    $data = mysqli_fetch_assoc($q);

    if (password_verify($password, $data['password'])) {
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['nama'] = $data['nama_lengkap'];

        header("Location: dashboard.php");
    } else {
        echo "Login gagal!";
    }
}
?>
<form method="POST">
    <input type="text" name="username" placeholder="Username"><br>
    <input type="password" name="password" placeholder="Password"><br>
    <button type="submit" name="login">Login</button>
</form>

<!-- ===== File: logout.php ===== -->
<?php
session_start();
session_destroy();
header("Location: login.php");
exit;
?>

<!-- ===== File: dashboard.php ===== -->
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<h2>Selamat datang, <?= $_SESSION['nama'] ?>!</h2>
<p>Role: <?= $_SESSION['role'] ?></p>
<ul>
    <?php if ($_SESSION['role'] === 'siswa'): ?>
        <li><a href="siswa/mulai_ujian.php">Mulai Ujian</a></li>
        <li><a href="hasil/lihat.php">Lihat Nilai</a></li>
    <?php elseif ($_SESSION['role'] === 'admin'): ?>
        <li><a href="admin/kelola_soal.php">Kelola Soal</a></li>
        <li><a href="admin/daftar_user.php">Daftar User</a></li>
        <li><a href="admin/pengaturan_ujian.php">Pengaturan Ujian</a></li>
    <?php endif; ?>
    <li><a href="logout.php">Logout</a></li>
</ul>

<!-- ===== File: siswa/mulai_ujian.php ===== -->
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$durasi = 3600;
if (!isset($_SESSION['waktu_mulai'])) {
    $_SESSION['waktu_mulai'] = time();
}
$waktu_mulai = $_SESSION['waktu_mulai'];
$waktu_sekarang = time();
$sisa_waktu = $durasi - ($waktu_sekarang - $waktu_mulai);

if ($sisa_waktu <= 0) {
    header("Location: ../selesai.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ujian</title>
    <script>
        var sisaDetik = <?= $sisa_waktu ?>;
        function startTimer() {
            var timer = setInterval(function() {
                var menit = Math.floor(sisaDetik / 60);
                var detik = sisaDetik % 60;
                document.getElementById("timer").innerHTML = menit + "m " + detik + "s ";
                if (sisaDetik <= 0) {
                    clearInterval(timer);
                    alert("Waktu habis! Jawaban dikirim otomatis.");
                    document.getElementById("formUjian").submit();
                }
                sisaDetik--;
            }, 1000);
        }
    </script>
</head>
<body onload="startTimer()">
    <h2>Ujian Online</h2>
    <p>Waktu Tersisa: <span id="timer" style="color:red"></span></p>
    <form id="formUjian" method="POST" action="../simpan_jawaban.php">
        <p>1. Apa ibu kota Indonesia?</p>
        <input type="radio" name="jawaban[1]" value="A"> A. Surabaya<br>
        <input type="radio" name="jawaban[1]" value="B"> B. Bandung<br>
        <input type="radio" name="jawaban[1]" value="C"> C. Jakarta<br>
        <input type="radio" name="jawaban[1]" value="D"> D. Medan<br>
        <br>
        <button type="submit">Selesai</button>
    </form>
</body>
</html>

<!-- ===== File: simpan_jawaban.php ===== -->
<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$jawaban = $_POST['jawaban'];
$benar = 0;
$total = count($jawaban);

foreach ($jawaban as $id_soal => $jawab) {
    $cek = mysqli_query($conn, "SELECT * FROM soal WHERE id=$id_soal");
    $soal = mysqli_fetch_assoc($cek);
    if ($jawab == $soal['jawaban_benar']) {
        $benar++;
    }
}

$nilai = round(($benar / $total) * 100);
$user_id = $_SESSION['user_id'];
$ujian_id = 1; // Sementara satu ujian

mysqli_query($conn, "INSERT INTO hasil (user_id, ujian_id, nilai) VALUES ('$user_id','$ujian_id','$nilai')");
unset($_SESSION['waktu_mulai']);
header("Location: selesai.php");
?>

<!-- ===== File: hasil/lihat.php ===== -->
<?php
session_start();
include '../config.php';
if ($_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$q = mysqli_query($conn, "SELECT * FROM hasil WHERE user_id='$user_id'");
?>
<h2>Hasil Ujian Anda</h2>
<table border="1">
    <tr><th>No</th><th>Ujian ID</th><th>Nilai</th></tr>
    <?php $no=1; while ($row = mysqli_fetch_assoc($q)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['ujian_id'] ?></td>
            <td><?= $row['nilai'] ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<!-- ===== File: admin/kelola_soal.php ===== -->
<?php
session_start();
include '../config.php';
if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
if (isset($_POST['submit'])) {
    $mapel = $_POST['mapel'];
    $tanya = $_POST['pertanyaan'];
    $a = $_POST['a'];
    $b = $_POST['b'];
    $c = $_POST['c'];
    $d = $_POST['d'];
    $benar = $_POST['jawaban_benar'];
    mysqli_query($conn, "INSERT INTO soal VALUES(NULL,'$mapel','$tanya','$a','$b','$c','$d','$benar')");
}
?>
<h2>Input Soal</h2>
<form method="POST">
    <input name="mapel" placeholder="Mata Pelajaran"><br>
    <textarea name="pertanyaan" placeholder="Pertanyaan"></textarea><br>
    A: <input name="a"><br>
    B: <input name="b"><br>
    C: <input name="c"><br>
    D: <input name="d"><br>
    Jawaban Benar (A/B/C/D): <input name="jawaban_benar"><br>
    <button name="submit">Simpan</button>
</form>

<h2>Daftar Soal</h2>
<table border="1">
    <tr><th>No</th><th>Mapel</th><th>Pertanyaan</th><th>Jawaban</th></tr>
    <?php $q = mysqli_query($conn, "SELECT * FROM soal"); $no = 1;
    while ($row = mysqli_fetch_assoc($q)):
    ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['mapel'] ?></td>
        <td><?= $row['pertanyaan'] ?></td>
        <td><?= $row['jawaban_benar'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
