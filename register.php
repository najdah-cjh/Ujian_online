<?php
include 'config.php';

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama = $_POST['nama'];
    $nis = $_POST['nis'];
    $email = $_POST['email'];
    $kelas = $_POST['kelas'];

    // Insert ke tabel user
    $queryUser = mysqli_query($conn, "INSERT INTO users (username, password, nama_lengkap, role)
                 VALUES ('$username', '$password', '$nama', 'siswa')");
    $userId = mysqli_insert_id($conn);

    // Insert ke tabel siswa
    $querySiswa = mysqli_query($conn, "INSERT INTO siswa (user_id, nis, email, kelas)
                  VALUES ('$userId', '$nis', '$email', '$kelas')");

    echo "Registrasi berhasil. <a href='login.php'>Login sekarang</a>";
}
?>

<form method=\"POST\">
    <input type=\"text\" name=\"username\" placeholder=\"Username\" required><br>
    <input type=\"password\" name=\"password\" placeholder=\"Password\" required><br>
    <input type=\"text\" name=\"nama\" placeholder=\"Nama Lengkap\" required><br>
    <input type=\"text\" name=\"nis\" placeholder=\"NIS\" required><br>
    <input type=\"email\" name=\"email\" placeholder=\"Email\" required><br>
    <input type=\"text\" name=\"kelas\" placeholder=\"Kelas\" required><br>
    <button type=\"submit\" name=\"submit\">Daftar</button>
</form>
