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

<form method=\"POST\">
    <input type=\"text\" name=\"username\" placeholder=\"Username\"><br>
    <input type=\"password\" name=\"password\" placeholder=\"Password\"><br>
    <button type=\"submit\" name=\"login\">Login</button>
</form>
