<?php
include("include/connect.php");
session_start();

$id = intval($_GET['id']);
$aid = $_SESSION['aid'];

$q = mysqli_query($con, "SELECT * FROM addresses WHERE id = $id AND aid = $aid");
echo json_encode(mysqli_fetch_assoc($q));
