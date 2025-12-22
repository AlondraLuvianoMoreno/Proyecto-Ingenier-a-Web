<?php
include("include/connect.php");

if (!isset($_GET['username'])) {
    echo "invalid";
    exit();
}

$username = $_GET['username'];

// Buscar si el username ya existe
$query = mysqli_query($con, "SELECT aid FROM accounts WHERE username = '$username'");
$row = mysqli_fetch_assoc($query);

if (!empty($row)) {
    echo "exists"; // el usuario ya existe
} else {
    echo "ok"; // disponible
}
