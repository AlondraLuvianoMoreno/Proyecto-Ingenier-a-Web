<?php
session_start();
include("include/connect.php");
    $aid = $_SESSION['aid'] ?? -1;

if ($aid > 0) {
   header("Location: index.php");
     exit();
}

if (isset($_SESSION['login_message'])) {
    echo "
    <div style=' background:#f8d7da; color:#721c24; padding:12px; margin-bottom:15px; border-radius:6px; border:1px solid #f5c6cb;'>
        {$_SESSION['login_message']}
    </div>
    ";
    unset($_SESSION['login_message']);
}

if (isset($_POST['submit'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Buscar por username
    $query = "SELECT * FROM accounts WHERE username='$username'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {

        $row = mysqli_fetch_assoc($result);

        if (!password_verify($password, $row['password'])) {
            echo "<script>alert('Contraseña incorrecta.');window.location.href='login.php';</script>";
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
        echo "<script>alert('Usuario no encontrado.'); window.location.href='login.php';</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<script src="https://accounts.google.com/gsi/client" async defer></script>

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Eventify</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />

    <link rel="stylesheet" href="style.css" />

        <style>

.password-wrapper {
    position: relative;
    width: 30%;
    margin: 40px auto;
    min-height:50px;
}

.password-wrapper input {
    width: 100%;
    padding-right: 35px;  
}

.toggle-eye {
    position: absolute;
    right: 5px;
    top: 13px;
    cursor: pointer;
    color: #555;
    font-size: 16px;
}
    
    </style>

</head>

<body>
    <section id="header">
        <a href="#"><img src="img/logo.png" class="logo" alt="" /></a>

        <div>
            <ul id="navbar">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="shop.php">Eventos</a></li>
                <li><a href="about.php">Acerca de nosotros</a></li>
                <li><a class="active" href="login.php">Iniciar sesión</a></li>
                <li><a href="signup.php">Registrarse</a></li>
                <li id="lg-bag">
                    <a href="cart.php"><i class="far fa-shopping-cart"></i></a>
                </li>
                <a href="#" id="close"><i class="far fa-times"></i></a>
            </ul>
        </div>
    </section>


    <form method="post" id="form">
        <h2 style="color: blue; margin: auto">Iniciar sesión</h2>
        <input class="input1" id="user" name="username" type="text" placeholder="Usuario *">
        <div class="password-wrapper">
        <input class="input1" id="pass" name="password" type="password" placeholder="Contraseña *" required='required'>
        <i class="far fa-eye toggle-eye" id="togglePass"></i>
        </div>
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
    <script>
document.addEventListener("DOMContentLoaded", () => {
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (!input || !icon) return;

        icon.addEventListener("click", () => {
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        });
    }

    togglePassword("pass", "togglePass");
    togglePassword("cpass", "toggleCPass");
});
</script>
</body>

</html>
