<?php
session_start();
include("include/connect.php");

// Verificar si hay sesión activa
if (!isset($_SESSION['aid']) || $_SESSION['aid'] < 0) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para agregar al carrito']);
    exit();
}

$pid = $_GET['pid'] ?? 0;
$qty = $_GET['qty'] ?? 1;
$aid = $_SESSION['aid'];

// Validar que el producto existe y tiene stock
$product_query = mysqli_query($con, "SELECT pname, qtyavail FROM products WHERE pid = $pid");
if (!$product_query || mysqli_num_rows($product_query) == 0) {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    exit();
}

$product = mysqli_fetch_assoc($product_query);

// Verificar stock disponible
if ($product['qtyavail'] < $qty) {
    echo json_encode(['success' => false, 'message' => 'Stock insuficiente. Solo quedan ' . $product['qtyavail'] . ' disponibles.']);
    exit();
}

// Verificar si el producto ya está en el carrito
$check = mysqli_query($con, "SELECT * FROM cart WHERE aid = $aid AND pid = $pid");
if (mysqli_num_rows($check) > 0) {
    // Actualizar cantidad
    mysqli_query($con, "UPDATE cart SET cqty = cqty + $qty WHERE aid = $aid AND pid = $pid");
} else {
    // Agregar nuevo
    mysqli_query($con, "INSERT INTO cart (aid, pid, cqty) VALUES ($aid, $pid, $qty)");
}

// Obtener nuevo conteo del carrito
$count_result = mysqli_query($con, "SELECT SUM(cqty) as total FROM cart WHERE aid = $aid");
$count_row = mysqli_fetch_assoc($count_result);
$cart_count = $count_row['total'] ?? 0;

echo json_encode([
    'success' => true,
    'message' => 'Producto agregado al carrito',
    'cart_count' => $cart_count,
    'product_name' => $product['pname']
]);

mysqli_close($con);
?>