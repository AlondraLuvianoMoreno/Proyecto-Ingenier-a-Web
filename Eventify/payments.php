<?php
session_start();

// no pasa si no esta loggeado
if (isset($_GET['lo'])) {
  $_SESSION['aid'] = -1;
  header("Location: index.php");
  exit();

}

include("include/connect.php");
$aid = $_SESSION['aid'];
$roleQuery = mysqli_query($con, "SELECT role FROM accounts WHERE aid = $aid");
$roleRow = mysqli_fetch_assoc($roleQuery);

if ($roleRow['role'] !== 'cliente') {
    header("Location: index.php");
    exit();
}

// INSERTAR NUEVA tarjeta
if (isset($_POST['guardar'])) {

    // Sanitizar
    $titular = mysqli_real_escape_string($con, $_POST['titular']);
    $numero = preg_replace('/\D/', '', $_POST['numero_tarjeta']); // 16 dígitos
    $vencimiento = trim($_POST['vencimiento']);
    $cvv = preg_replace('/\D/', '', $_POST['cvv']); // Solo para validar, NO se guardará

    if (!preg_match('/^\d{16}$/', $numero)) {
        echo "<script>alert('Número de tarjeta inválido'); window.location='payments.php';</script>";
        exit();
    }

    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $vencimiento)) {
        echo "<script>alert('Vencimiento inválido. Usa MM/YY'); window.location='payments.php';</script>";
        exit();
    }

    if (!preg_match('/^\d{3,4}$/', $cvv)) {
        echo "<script>alert('CVV inválido'); window.location='payments.php';</script>";
        exit();
    }

    // ===== INSERT SIN CVV =====
    $query = "INSERT INTO payment_methods 
        (aid, titular, numero_tarjeta, vencimiento)
        VALUES ($aid, '$titular', '$numero', '$vencimiento')";

    mysqli_query($con, $query);

    echo "<script>alert('Método agregado correctamente'); window.location='payments.php';</script>";
    exit();
}

// ACTUALIZAR TARJETA EXISTENTE
if (isset($_POST['actualizar'])) {
    $edit_id = intval($_POST['edit_id']);

    // Sanitizar
    $titular = mysqli_real_escape_string($con, $_POST['titular']);
    $numero = preg_replace('/\D/', '', $_POST['numero_tarjeta']); 
    $vencimiento = trim($_POST['vencimiento']);
    $cvv = preg_replace('/\D/', '', $_POST['cvv']);

    if (!preg_match('/^\d{16}$/', $numero)) {
        echo "<script>alert('Número de tarjeta inválido'); window.location='payments.php';</script>";
        exit();
    }

    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $vencimiento)) {
        echo "<script>alert('Vencimiento inválido. Usa MM/YY'); window.location='payments.php';</script>";
        exit();
    }

    if (!preg_match('/^\d{3,4}$/', $cvv)) {
        echo "<script>alert('CVV inválido'); window.location='payments.php';</script>";
        exit();
    }

    $query = "UPDATE payment_methods SET titular='$titular', numero_tarjeta='$numero', vencimiento='$vencimiento'
              WHERE id = $edit_id AND aid = $aid";

    mysqli_query($con, $query);

    echo "<script>alert('Método actualizado correctamente'); window.location='payments.php';</script>";
    exit();
}

if (isset($_GET['set_main'])) {
    $id = intval($_GET['set_main']);

    mysqli_query($con, "UPDATE payment_methods SET es_principal = 0 WHERE aid = $aid");
    mysqli_query($con, "UPDATE payment_methods SET es_principal = 1 WHERE id = $id AND aid = $aid");

    header("Location: payments.php");
    exit();
}

// Eliminar tarjeta
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $aid = $_SESSION['aid'];
    // Borrar solo si pertenece al usuario
    mysqli_query($con, "DELETE FROM payment_methods WHERE id = $delete_id AND aid = $aid");
    echo "<script>alert('Método eliminado correctamente.'); window.location='payments.php';</script>";

    exit();
}

// Obtener métodos de pago
$cards = mysqli_query($con, "SELECT * FROM payment_methods WHERE aid = $aid");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Eventify</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />

    <link rel="stylesheet" href="style.css" />

    <style>
    .tb {
        max-height: 400px;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0;
        margin: 0;
    }
    .tb tr {
        height: auto;
        margin: 0;
        padding: 0;
    }
    .tb td {
        text-align: center;
        margin: 0;
        padding: 8px 12px; 
        vertical-align: middle;
        font-size: 15px;
    }

    .insert-btn {
        display: inline-block;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 5px;
        border: none;
        color: #fff;
        background-color: #088178;
        cursor: pointer;
        margin-right: 20px;
        margin-top: 20px;
        margin-bottom: 20px;
        margin-left: 20px;
    }

    input[type="text"] {
        display: block;
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        font-size: 16px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    input[type="date"] {
        display: block;
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        font-size: 16px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .logup {
        width: auto;
    }

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.65);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.modal-box {
    background: #fff;
    width: 420px;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
    animation: fadeIn 0.25s ease;
}

@keyframes fadeIn {
    from { transform: scale(0.9); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}

    </style>

    <style>
    .rating {
        display: inline-block;
        font-size: 0;
        line-height: 0;
        border: none;
        border-style: none;

        padding-left: 80px;
    }

    .rating label {
        display: inline-block;
        font-size: 24px;
        color: #ddd;
        cursor: pointer;
    }

    .rating label:before {
        content: '\2606';
    }

    .rating label.checked:before,
    .rating label:hover:before {
        content: '\2605';
        color: #ffc107;
    }

    input[type="radio"] {
        display: none;
    }
    </style>

</head>

<body>

<div class="sidenav">
    <div class="profile">
        <img src="img/usuario.png" alt="" width="100" height="100">

        <?php
        include("include/connect.php");

        $aid = $_SESSION['aid'];
        $query = "SELECT * FROM ACCOUNTS WHERE aid = $aid";
        $result = mysqli_query($con, $query);
        $row = mysqli_fetch_assoc($result);

        $afname = $row['afname'];
        $alname = $row['alname'];
        $role = $row['role'];

        echo "
        <div class='name'>$afname $alname</div>
        <div class='job'>".ucfirst($role)."</div>
        ";
        ?>
    </div>

    <div class="sidenav-url">

        <div class="url">
            <a href='profile.php' class="btn logup">Información del perfil</a>
            <hr align="center">
        </div>

    </div>
</div>

<section id="header">
        <a href="index.php"><img src="img/logo.png" class="logo" alt="" /></a>

        <div>
            <ul id="navbar">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="shop.php">Eventos</a></li>
                <li><a href="about.php">Acerca de nosotros</a></li>
                <li><a href='profile.php'>Perfil</a></li>
                <li><a href='profile.php?lo=1'>Cerrar sesión</a></li>
                <li id="lg-bag">
                    <a href="cart.php"><i class="far fa-shopping-cart"></i></a>
                </li>
                <a href="#" id="close"><i class="far fa-times"></i></a>
            </ul>
        </div>
    </section>

    <div class="navbar-top">
        <div class="title">
            <h1>Métodos de pago</h1>
        </div>
    </div>

<div class="main">

    <br> <br>

        <h2>Mis tarjetas</h2>

<div class="card">
  <div class="card-body tb">
    <table class="addresses-table">
        <thead>
            <tr>
            <th>Principal</th>
            <th>Tarjeta</th>
            <th>Editar</th>
            <th>Eliminar</th>
            </tr>
        </thead>

        <tbody>
            <?php
            while ($d = mysqli_fetch_assoc($cards)) {

                $principal = $d['es_principal'] == 1
                    ? "<strong class='principal-tag'>PRINCIPAL</strong>"
                    : "<a href='payments.php?set_main={$d['id']}' class='btn small'>Cambiar</a>";

                $metodo = "{$d['titular']} — ****" . substr($d['numero_tarjeta'], -4) . " (Vence {$d['vencimiento']})";

                $editar = "<button type='button' class='btn small' onclick='openEditPaymentModal({$d['id']})'>Editar</button>";
                $eliminar = "<a href='payments.php?delete={$d['id']}' class='btn small danger' onclick=\"return confirm('¿Eliminar este método?');\">Eliminar</a>";

                echo "<tr>
                    <td class='cell-principal'>$principal</td>
                    <td class='cell-direccion'>$metodo</td>
                    <td class='cell-editar'>$editar</td>
                    <td class='cell-eliminar'>$eliminar</td>
                  </tr>";
            }
            ?>
        </tbody>
        </table>

  </div>
</div>

<button class="btn logup" style="margin-top:15px;" onclick="openAddPaymentModal()">Agregar tarjeta</button>


</div>

<!-- modal para editar pago -->
<div id="modal-edit-payment" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h3>Editar tarjeta</h3>

        <form method="post" id="editPaymentForm" onsubmit="return validarTarjeta('editPaymentForm')">
            <input type="hidden" name="edit_id" id="edit_id_payment">

            <input type="text" name="titular" id="titular" placeholder="Nombre del titular" required>
            <input type="text" name="numero_tarjeta" id="numero_tarjeta" placeholder="Número de tarjeta" required maxlength="19" inputmode="numeric" autocomplete="cc-number">
            <input type="text" name="vencimiento" id="vencimiento" placeholder="MM/YY" required maxlength="5" inputmode="numeric" autocomplete="cc-exp">
            <input type="text" name="cvv" id="cvv" placeholder="CVV" required maxlength="4" inputmode="numeric" autocomplete="cc-csc">

            <button type="submit" name="actualizar" class='btn small'>Actualizar</button>
            <button type="button" onclick="closeEditPaymentModal()" class="btn small danger">Cancelar</button>
        </form>
    </div>
</div>

<!-- modal para agregar tarjeta -->
<div id="modal-add-payment" class="modal-overlay" style="display:none;">
  <div class="modal-box">
    <h3>Agregar tarjeta</h3>

   <form method="post" id="addPaymentForm" onsubmit="return validarTarjeta('addPaymentForm')">
      <input type="text" name="titular" id="titular_add" placeholder="Nombre del titular" required>
      <input type="text" name="numero_tarjeta" id="numero_tarjeta_add" placeholder="Número de tarjeta" required maxlength="19" inputmode="numeric" autocomplete="cc-number">
        <input type="text" name="vencimiento" id="vencimiento_add" placeholder="MM/YY" required maxlength="5" inputmode="numeric" autocomplete="cc-exp">
    <input type="text" name="cvv" id="cvv_add" placeholder="CVV" required maxlength="4" inputmode="numeric" autocomplete="cc-csc">

      <button type="submit" name="guardar" class="btn small">Guardar</button>
      <button type="button" onclick="closeAddPaymentModal()" class="btn small danger">Cancelar</button>
    </form>
  </div>
</div>


<script src="script.js"></script>

<br> <br> <br> <br>
    <footer class="section-p1">
        <div class="col">
            <img class="logo" src="img/logo.png" />
            <h4>Contacto</h4>
            <p>
                <strong>Dirección </strong> Avenida Instituto Politécnico Nacional No. 2580, Colonia Barrio la Laguna Ticomán, C.P. 07340, Alcaldía Gustavo A. Madero, Ciudad de México.

            </p>
            <p>
                <strong>Teléfono: </strong> 55 3423 8890
            </p>
            <p>
                <strong>Correo: </strong> soporteupiita@eventify.com
            </p>

        </div>

        <div class="col install">
            <p>Secured Payment Gateways</p>
            <img src="img/pay/pay.png" />
        </div>
        <div class="copyright">
            <p>2025. Eventify. HTML CSS PHP. </p>
        </div>
    </footer>

</body>
</html>
