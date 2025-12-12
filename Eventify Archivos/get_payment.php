<?php
include("include/connect.php");
session_start();

$id = intval($_GET['id']);
$aid = $_SESSION['aid'];

$query = mysqli_query($con, "SELECT * FROM payment_methods WHERE id = $id AND aid = $aid");

echo json_encode(mysqli_fetch_assoc($query));
