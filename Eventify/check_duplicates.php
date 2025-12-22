<?php
include("include/connect.php");

if (!isset($_GET['field']) || !isset($_GET['value'])) {
    echo "invalid";
    exit();
}

$field = $_GET['field'];
$value = $_GET['value'];

// seguridad: permitir solo estos campos
$permitidos = ['username','email','phone'];
if (!in_array($field, $permitidos)) {
    echo "invalid";
    exit();
}

// escapar
$field_safe = mysqli_real_escape_string($con, $field);
$value_safe = mysqli_real_escape_string($con, $value);

// consulta
$sql = "SELECT aid FROM accounts WHERE $field_safe = '$value_safe' LIMIT 1";
$result = mysqli_query($con, $sql);

echo (mysqli_num_rows($result) > 0) ? "existe" : "ok";
