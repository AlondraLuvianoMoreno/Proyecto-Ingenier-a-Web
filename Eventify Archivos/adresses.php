<?php
session_start();

// SI NO ESTÁ LOGEADO, FUERA
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

// INSERTAR NUEVA DIRECCIÓN
if (isset($_POST['guardar'])) {
    include("include/connect.php");
    $aid = $_SESSION['aid'];   

    $calle = $_POST['calle'];
    $num_ext = $_POST['numero_ext'];
    $num_int = $_POST['numero_int'];
    $colonia = $_POST['colonia'];
    $cp = $_POST['cp'];
    $municipio = $_POST['municipio'];
    $ciudad = $_POST['ciudad'];
    $pais = $_POST['pais'];

    $query = "INSERT INTO addresses 
    (aid, calle, numero_ext, numero_int, colonia, cp, municipio, ciudad, pais)
    VALUES ($aid, '$calle', '$num_ext', '$num_int', '$colonia', '$cp', '$municipio', '$ciudad', '$pais')";

    mysqli_query($con, $query);


    echo "<script>alert('Dirección guardada correctamente'); window.location='adresses.php';</script>";
    exit();
}

// ACTUALIZAR DIRECCIÓN EXISTENTE
if (isset($_POST['actualizar'])) {
    $edit_id = intval($_POST['edit_id']);

    $calle = $_POST['calle'];
    $num_ext = $_POST['numero_ext'];
    $num_int = $_POST['numero_int'];
    $colonia = $_POST['colonia'];
    $cp = $_POST['cp'];
    $municipio = $_POST['municipio'];
    $ciudad = $_POST['ciudad'];
    $pais = $_POST['pais'];

    $query = "UPDATE addresses SET 
                calle='$calle',
                numero_ext='$num_ext',
                numero_int='$num_int',
                colonia='$colonia',
                cp='$cp',
                municipio='$municipio',
                ciudad='$ciudad',
                pais='$pais'
              WHERE id = $edit_id AND aid = $aid";

    mysqli_query($con, $query);

    echo "<script>alert('Dirección actualizada correctamente'); window.location='adresses.php';</script>";
    exit();
}


if (isset($_GET['set_main'])) {
    $id = intval($_GET['set_main']);
    $aid = $_SESSION['aid'];

    // Poner TODAS las direcciones como no principales
    mysqli_query($con, "UPDATE addresses SET es_principal = 0 WHERE aid = $aid");

    // Marcar esta como principal
    mysqli_query($con, "UPDATE addresses SET es_principal = 1 WHERE id = $id AND aid = $aid");

    header("Location: adresses.php");
    exit();
}

// ELIMINAR DIRECCIÓN
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $aid = $_SESSION['aid'];
    // Borrar solo si pertenece al usuario
    mysqli_query($con, "DELETE FROM addresses WHERE id = $delete_id AND aid = $aid");

    echo "<script>alert('Dirección eliminada correctamente.'); window.location='adresses.php';</script>";
    exit();
}


// OBTENER DIRECCIONES
$dirs = mysqli_query($con, "SELECT * FROM addresses WHERE aid = $aid");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Eventify</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />

    <link rel="stylesheet" href="style.css" />

    <style>
    /* Contenedor de tabla */
    .tb {
        max-height: 400px;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0;
        margin: 0;
    }

    /* Filas de tabla: altura normal */
    .tb tr {
        height: auto;
        margin: 0;
        padding: 0;
    }

    /* Celdas: MUCHO MENOS padding */
    .tb td {
        text-align: center;
        margin: 0;
        padding: 8px 12px; /* Antes eran 40px 😭 */
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

    /* Fondo oscuro */
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

/* Caja del modal */
.modal-box {
    background: #fff;
    width: 420px;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
    animation: fadeIn 0.25s ease;
}

/* Animación */
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

    /* .asd {} */
    </style>

    <style>
    </style>
    <script>
    window.addEventListener("unload", function() {
        // Call a PHP script to log out the user
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "logout.php", false);
        xhr.send();
    });
    </script>

</head>

<body>


    <!-- Sidenav -->
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
            <a href='profile.php?lo=1' class="btn logup">Cerrar sesión</a>
            <hr align="center">
        </div>

        <div class="url">
            <a href='profile.php' class="btn logup">Información del perfil</a>
            <hr align="center">
        </div>

    </div>
</div>
<!-- End Sidenav -->


<section id="header">
        <a href="index.php"><img src="img/logo.png" class="logo" alt="" /></a>

        <div>
            <ul id="navbar">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="shop.php">Conciertos</a></li>
                <li><a href="about.php">Festivales</a></li>
                <li><a href='profile.php'>Perfil</a></li>
                <li id="lg-bag">
                    <a href="cart.php"><i class="far fa-shopping-bag"></i></a>
                </li>
                <a href="#" id="close"><i class="far fa-times"></i></a>
            </ul>
        </div>
        <div id="mobile">
            <a href="cart.php"><i class="far fa-shopping-bag"></i></a>
            <i id="bar" class="fas fa-outdent"></i>
        </div>
    </section>

    <div class="navbar-top">
        <div class="title">
            <h1>Direcciones</h1>
        </div>
        <!-- End -->
    </div>

<div class="main">

    <br> <br>

        <h2>Mis direcciones</h2>

<div class="card">
  <div class="card-body tb">
    <table class="addresses-table">
      <thead>
        <tr>
          <th class="col-principal">Principal</th>
          <th class="col-direccion">Dirección</th>
          <th class="col-editar">Editar</th>
          <th class="col-eliminar">Eliminar</th>
        </tr>
      </thead>

      <tbody >
        <?php
        while ($d = mysqli_fetch_assoc($dirs)) {

            $principal = $d['es_principal'] == 1
                ? "<strong class='principal-tag'>PRINCIPAL</strong>"
                : "<a href='adresses.php?set_main={$d['id']}' class='btn small'>Cambiar</a>";

            $direccion = "{$d['calle']} #{$d['numero_ext']}";
            if (!empty($d['numero_int'])) {
                $direccion .= ", Int {$d['numero_int']}";
            }
            $direccion .= ", {$d['colonia']}, CP {$d['cp']}, {$d['municipio']}, {$d['ciudad']}, {$d['pais']}";

            $editar = "<button type='button' class='btn small' onclick='openEditModal({$d['id']})'>Editar</button>";
            $eliminar = "<a href='adresses.php?delete={$d['id']}' class='btn small danger' onclick=\"return confirm('¿Eliminar esta dirección?');\">Eliminar</a>";

            echo "<tr>
                    <td class='cell-principal'>$principal</td>
                    <td class='cell-direccion'>$direccion</td>
                    <td class='cell-editar'>$editar</td>
                    <td class='cell-eliminar'>$eliminar</td>
                  </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<button class="btn logup" style="margin-top:15px;" onclick="openAddModal()">Agregar dirección</button>


</div>

<!-- MODAL PARA EDITAR DIRECCIÓN -->
<div id="modal-edit" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h3>Editar dirección</h3>

        <form method="post" id="editForm">
            <input type="hidden" name="edit_id" id="edit_id">

            <input type="text" name="calle" id="calle" placeholder="Calle" required>
            <input type="text" name="numero_ext" id="numero_ext" placeholder="Número exterior" required>
            <input type="text" name="numero_int" id="numero_int" placeholder="Número interior">
            <input type="text" name="colonia" id="colonia" placeholder="Colonia" required>
            <input type="text" name="cp" id="cp" placeholder="Código postal" required>
            <input type="text" name="municipio" id="municipio" placeholder="Municipio" required>
            <input type="text" name="ciudad" id="ciudad" placeholder="Ciudad" required>
            <input type="text" name="pais" id="pais" placeholder="País" required>

            <button type="submit" name="actualizar" class='btn small'>Actualizar</button>
            <button type="button" onclick="closeModal()" class="btn small danger">Cancelar</button>
        </form>
    </div>
</div>

<!-- MODAL PARA AGREGAR DIRECCIÓN -->
<div id="modal-add" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h3>Agregar dirección</h3>

        <form method="post" id="editForm">
            <input type="text" name="calle" placeholder="Calle" required>
            <input type="text" name="numero_ext" placeholder="Número exterior" required>
            <input type="text" name="numero_int" placeholder="Número interior">
            <input type="text" name="colonia" placeholder="Colonia" required>
            <input type="text" name="cp" placeholder="Código postal" required>
            <input type="text" name="municipio" placeholder="Municipio" required>
            <input type="text" name="ciudad" placeholder="Ciudad" required>
            <input type="text" name="pais" placeholder="País" required>

            <button type="submit" name="guardar" class="btn small">Guardar</button>
            <button type="button" onclick="closeAddModal()" class="btn small danger">Cancelar</button>
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
