<?php
session_start();
include("include/connect.php"); 
if (empty($_SESSION['aid']))
    $_SESSION['aid'] = -1;

// --- LÓGICA DEL CARRITO ---
if (isset($_POST['submit'])) {
  $pid = $_GET['pid'];
  $aid = $_SESSION['aid'];
  $qty = $_POST['qty'];

  if ($aid < 0) {
    header("Location: login.php");
    exit();
  }

  $query = "select * from `cart`  where aid = $aid and pid = $pid";
  $result = mysqli_query($con, $query);
  $row = mysqli_fetch_assoc($result);

  if ($row) {
    echo "<script> alert('Item already added to cart') </script>";
    // Redirigimos al mismo producto para no perder la página
    echo "<script> window.location.href='sproduct.php?pid=$pid'; </script>"; 
    exit();
  } else {
    $query = "INSERT INTO `cart` (aid, pid, cqty) values ($aid, $pid, $qty)";
    $result = mysqli_query($con, $query);
    header("Location: shop.php");
    exit();
  }
}

// --- LÓGICA DE WISHLIST (Agregar) ---
if (isset($_GET['w'])) {
  $aid = $_SESSION['aid'];
  if ($aid < 0) {
    header("Location: login.php");
    exit();
  }
  $pid = $_GET['w'];
  $query = "INSERT INTO `WISHLIST` (aid, pid) values ($aid, $pid)";
  $result = mysqli_query($con, $query);
  header("Location: sproduct.php?pid=$pid");
  exit();
}

// --- LÓGICA DE WISHLIST (Eliminar) ---
if (isset($_GET['nw'])) {
  $aid = $_SESSION['aid'];
  $pid = $_GET['nw'];
  $query = "DELETE from `WISHLIST` where aid = $aid and pid = $pid";
  $result = mysqli_query($con, $query);
  header("Location: sproduct.php?pid=$pid");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Eventify</title>
  
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
  <link rel="stylesheet" href="style.css" />

  <style>
    /* Estilos internos específicos para esta página */
    .heart {
      margin-left: 25px;
      display: inline-flex;
      justify-content: center;
      align-items: center;
    }
    .star i {
      font-size: 12px;
      color: rgb(243, 181, 25);
    }
    
    /* Estilos de la tabla de reviews */
    .rev { margin: 70px; }
    .tb { max-height: 400px; overflow-x: auto; overflow-y: auto; }
    .tb tr { height: 60px; margin: 10px; }
    .tb td { text-align: center; margin: 10px; padding: 0 40px; }

    /* Asegurar estilos de galería por si falla el CSS externo */
    .single-pro-image { width: 100%; margin-right: 50px; }
    .small-img-group { display: flex; justify-content: flex-start; gap: 10px; margin-top: 15px; flex-wrap: wrap;}
    .small-img-col { flex-basis: 24%; cursor: pointer; }
    /* Efecto hover en miniaturas */
    .small-img:hover { opacity: 0.7; border: 1px solid #088178; }

    /* Agrega esto en la sección de estilos dentro del <style> */
.cart-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
    padding: 15px 25px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideIn 0.3s ease-out;
    max-width: 350px;
}

.cart-notification i {
    font-size: 20px;
    color: #fff;
}

.cart-notification.hide {
    animation: slideOut 0.3s ease-out forwards;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

/* Contador del carrito en el header */
.cart-count {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #f5576c;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

  </style>
</head>

<body>
  
  <section id="header">
    <a href="index.php"><img src="img/logo.png" class="logo" alt="" /></a>
    <div>
            <ul id="navbar">
                <li><a href="index.php">Inicio</a></li>
                <li><a class="active" href="shop.php">Eventos</a></li>
                <li><a href="about.php">Acerca de nosotros</a></li>
                <?php
            if (!isset($_SESSION['aid']) || $_SESSION['aid'] < 0) {
                echo "<li><a href='login.php'>Iniciar sesión</a></li>
                      <li><a href='signup.php'>Registrarse</a></li>";
            } else {
                // Traer rol del usuario
                $roleQuery = mysqli_query($con, "SELECT role FROM accounts WHERE aid = ".$_SESSION['aid']);
                $roleRow = mysqli_fetch_assoc($roleQuery);
                $role = $roleRow['role'];

                // Perfil según rol
                if ($role === 'artista') {
                    echo "<li><a href='profile-artista.php'>Panel Artista</a></li>";
                } elseif ($role === 'admin') {
                    echo "<li><a href='profile-admin.php'>Panel Administrador</a></li>";
                } else {
                    echo "<li><a href='profile.php'>Perfil</a></li>";
                }
                echo "<li><a href='profile.php?lo=1'>Cerrar sesión</a></li>";
            }
            ?>  
                <li id="lg-bag">
                    <a href="cart.php"><i class="far fa-shopping-cart"></i></a>
                </li>
                <a href="#" id="close"><i class="far fa-times"></i></a>
            </ul>
        </div>
  </section>

  <?php
  // --- CONSULTA DEL PRODUCTO PRINCIPAL ---
  if (isset($_GET['pid'])) {
    $pid = $_GET['pid'];
    
    // 1. Datos Generales del Producto
    $query = "SELECT * FROM PRODUCTS WHERE pid = $pid";
    $result = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($result);
    if (!$row) {
      echo "<h2>Producto no encontrado</h2>";
      exit();
    }


    // Variables del producto principal
    $pname = $row['pname'];
    $desc = $row['description'];
    $qty_avail = $row['qtyavail'];
    $price = $row['price'];
    $cat = $row['category'];
    $img_main = $row['img']; // Imagen de portada
    $brand = $row['brand'];

    // 2. Consulta de la GALERÍA (Tabla product_images)
    // Aquí es donde sucede la magia: traemos las fotos extra vinculadas a este ID
    $query_gallery = "SELECT * FROM product_images WHERE product_id = $pid ORDER BY display_order ASC";
    $result_gallery = mysqli_query($con, $query_gallery);

    // 3. Consulta de Wishlist
    $aid = $_SESSION['aid'];
    $query_w = "select * from wishlist where aid = $aid and pid = $pid";
    $result_w = mysqli_query($con, $query_w);
    $row_w = mysqli_fetch_assoc($result_w);
  ?>

    <section id='prodetails' class='section-p1'>
      
      <div class='single-pro-image'>
        
        <img src='product_images/<?php echo $img_main; ?>' width='100%' id='MainImg' alt='Main Product Image' />

        <div class='small-img-group'>
            
            <div class='small-img-col'>
                <img src='product_images/<?php echo $img_main; ?>' width='100%' class='small-img' alt='Main'>
            </div>

            <?php 
            if (mysqli_num_rows($result_gallery) > 0) {
                while($row_img = mysqli_fetch_assoc($result_gallery)) {
                    $ruta_extra = $row_img['image_path'];
                    echo "
                    <div class='small-img-col'>
                        <img src='product_images/$ruta_extra' width='100%' class='small-img' alt='Gallery Image'>
                    </div>";
                }
            }
            ?>
        </div>
      </div>

      <div class='single-pro-details'>
        <h6>Eventos / <?php echo $cat; ?></h6>
        <h2><?php echo $pname; ?></h2>
        <h4><?php echo $brand; ?></h4>
        <h2>$<?php echo $price; ?></h2>
        
        <!-- FORMULARIO ACTUALIZADO CON AJAX -->
        <form id="addToCartForm" method='post'>
            <input type='number' name='qty' id='productQty' value='1' min='1' max='<?php echo $qty_avail; ?>'/>
            <button type='button' class='normal' id='addToCartBtn' 
                    data-pid='<?php echo $pid; ?>' 
                    data-pname='<?php echo htmlspecialchars($pname, ENT_QUOTES, 'UTF-8'); ?>'>
                Añadir al carrito
            </button>
            
        </form>

        <h4>Detalle del evento</h4>
        <span><?php echo $desc; ?></span>
      </div>
    </section>

  <?php
  } // Fin del IF principal (pid existe)

  // --- SECCIÓN DE REVIEWS ---
  $query_rev = "select * from reviews join orders on reviews.oid = orders.oid join accounts on orders.aid = accounts.aid where reviews.pid = $pid";
  $result_rev = mysqli_query($con, $query_rev);

  if (mysqli_num_rows($result_rev) > 0) {
      echo "
      <div class='rev'>
      <h2>Reseñas</h2>
      <div class='tb'>
      <table><thead><tr><th>Username</th><th>Calificación</th><th>Reseña</th></thead><tbody>";

      while ($row_rev = mysqli_fetch_assoc($result_rev)) {
        $user = $row_rev['username'];
        $rtext = $row_rev['rtext'];
        $stars = $row_rev['rating'];
        $empty = 5 - $stars;

        echo "<tr>
                <td>$user</td>
                <td style='min-width: 150px;'><div class='star'>";
        
        for ($i = 1; $i <= $stars; $i++) echo "<i class='fas fa-star'></i>";
        for ($i = 1; $i <= $empty; $i++) echo "<i class='far fa-star'></i>";
        
        echo "</div></td>
                <td><span>$rtext</span></td>
              </tr>";
      }
      echo "</tbody></table></div></div>";
  }
  ?>

    <footer class="section-p1">
        <div class="col">
            <img class="logo" src="img/logo.png" alt="Eventify" />
            <h4>Contacto</h4>
            <p>
                <strong>Dirección: </strong> Avenida Instituto Politécnico Nacional No. 2580, Colonia Barrio la Laguna Ticomán, C.P. 07340, Alcaldía Gustavo A. Madero, Ciudad de México.
            </p>
            <p>
                <strong>Teléfono: </strong> 55 3423 8890
            </p>
            <p>
                <strong>Correo: </strong> soporteupiita@eventify.com
            </p>
        </div>

        <div class="col">
            <h4>Mi cuenta</h4>
            <a href="cart.php">Ver carrito</a>
            <a href="wishlist.php">Mi lista de deseos</a>
            <a href="profile.php">Mi perfil</a>
            <a href="shop.php">Eventos</a>
        </div>
        
        <div class="col">
            <h4>Eventify</h4>
            <a href="index.php">Inicio</a>
            <a href="shop.php">Eventos</a>
            <a href="about.php">Festivales y Conciertos</a>
            <a href="contact.php">Contacto</a>
        </div>
        
        <div class="col install">
            <p>Métodos de pago seguros</p>
            <img src="img/pay/pay.png" alt="Métodos de pago" />
        </div>
        <div class="copyright">
            <p>2025. Eventify. HTML CSS PHP.</p>
        </div>
    </footer>

  <script>
    // Seleccionamos la imagen grande
    var MainImg = document.getElementById("MainImg");
    // Seleccionamos TODAS las miniaturas
    var smallimg = document.getElementsByClassName("small-img");

    // Usamos un bucle para asignar el evento click a todas las miniaturas
    // Esto es mejor ingeniería que hacerlo 1 por 1, porque funciona si hay 2 fotos o 100 fotos.
    for (let i = 0; i < smallimg.length; i++) {
        smallimg[i].onclick = function() {
            MainImg.src = smallimg[i].src;
        }
    }
  </script>

  <script src="script.js"></script>


</body>
</html>
