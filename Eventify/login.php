<?php
session_start();
include("include/connect.php");

if (isset($_POST['submit'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Buscar por username
    $query = "SELECT * FROM accounts WHERE username='$username'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {

        $row = mysqli_fetch_assoc($result);

        if (!password_verify($password, $row['password'])) {
            echo "<script>alert('Contraseña incorrecta.');</script>";
            exit();
        }

        $_SESSION['aid'] = $row['aid'];
        $_SESSION['role'] = $row['role'];

        if ($row['role'] === 'admin') {
            header("Location: profile-admin.php");
            exit();
        }

        if ($row['role'] === 'artista') {
            header("Location: profile-artista.php");
            exit();
        }

        header("Location: profile.php");
        exit();

    } else {
        echo "<script>alert('Usuario no encontrado.');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script src="script.js"></script>

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Eventify</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />

    <link rel="stylesheet" href="style.css" />

</head>

<body>
    <section id="header">
        <a href="#"><img src="img/logo.png" class="logo" alt="" /></a>

        <div>
            <ul id="navbar">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="shop.php">Conciertos</a></li>
                <li><a href="about.php">Festivales</a></li>
                <li><a href="signup.php">Registrarse</a></li>
                <li id="lg-bag">
                    <a href="cart.php"><i class="far fa-shopping-bag"></i></a>
                </li>
                <a href="#" id="close"><i class="far fa-times"></i></a>
            </ul>
        </div>
    </section>


    <form method="post" id="form">
        <h2 style="color: blue; margin: auto">Iniciar sesión</h2>
        <input class="input1" id="user" name="username" type="text" placeholder="Usuario *">
        <input class="input1" id="pass" name="password" type="password" placeholder="Contraseña *">
        <button type="submit" class="btn" name="submit">Ingresar</button>

    </form>

    <div class="google-signin-container">
                <div id="g_id_onload"
                     data-client_id="409713456368-p6curvh7klet34iod602t52t6og8uoha.apps.googleusercontent.com"
                     data-callback="handleGoogleLogin"
                     data-auto_prompt="false">
                </div>
                <div class="g_id_signin"
                     data-type="standard"
                     data-size="large"
                     data-theme="outline"
                     data-text="signin_with"
                     data-shape="rectangular"
                     data-logo_alignment="left"
                     data-width="400">
                </div>
    </div>

    

    <div class="sign">
        <a href="signup.php" class="signn">¿No tienes cuenta? Regístrate.</a>
    </div>

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

    <script src="script.js"></script>
</body>

</html>

<script>
window.addEventListener("unload", function() {
  // Call a PHP script to log out the user
  var xhr = new XMLHttpRequest();
  xhr.open("GET", "logout.php", false);
  xhr.send();
});
</script>