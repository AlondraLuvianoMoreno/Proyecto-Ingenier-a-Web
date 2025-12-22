<?php
session_start();
include("include/connect.php");

    $aid = $_SESSION['aid'] ?? -1;

    if ($aid > 0) {
        header("Location: index.php");
        exit();
    }

if (isset($_POST['submit'])) {
    $firstname = $_POST['firstName'];
    $lastname = $_POST['lastName'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmpassowrd = $_POST['confirmPassword'];
    $dob = $_POST['dob'];
    $contact = $_POST['phone'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $query = "select * from accounts where username = '$username' or phone='$contact' or email='$email'";

    $result = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($result);
    if (!empty($row['aid'])) {
        echo "<script> alert('El usuario ya existe.'); setTimeout(function(){ window.location.href = 'signup.php'; }, 100); </script>";
        exit();
    }
    if ($password != $confirmpassowrd) {
        echo "<script> alert('Las contraseñas no son iguales.'); setTimeout(function(){ window.location.href = 'signup.php'; }, 100); </script>";
        exit();
    }
    // Validación completa de contraseña en un solo if
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W_]/', $password) ) {
        echo "<script>alert('La contraseña no cumple los parámetros'); window.location.href='signup.php';</script>";
        exit();
    }

    if (strtotime($dob) > time()) {
        echo "<script> alert('Fecha inválida.'); setTimeout(function(){ window.location.href = 'signup.php'; }, 100); </script>";
        exit();
    }
    if (preg_match('/\D/', $contact) || strlen($contact) != 10) {
        echo "<script> alert('Teléfono inválido'); setTimeout(function(){ window.location.href = 'signup.php'; }, 100); </script>";
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insertar usuario con contraseña en hash
    $query = "INSERT INTO accounts (afname, alname, phone, email, dob, username, password, role)
              VALUES ('$firstname', '$lastname', '$contact', '$email', '$dob', '$username', '$hashedPassword', '$role')";

    $result = mysqli_query($con, $query);


    if ($result) {
        echo "<script> alert('¡Cuenta creada exitosamente!'); setTimeout(function(){ window.location.href = 'login.php'; }, 100); </script>"; // exit();
    }

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
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />

    <link rel="stylesheet" href="style.css" />

    <style>

.password-wrapper {
    position: relative;
    width: 30%;
    margin: 40px auto;
}

.password-wrapper input {
    width: 100%;
    padding-right: 35px;  /* espacio para el ojo */
}

.toggle-eye {
    position: absolute;
    right: 5px;
    top:13px;
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
                <li><a href="login.php">Iniciar sesión</a></li>
                <li><a class="active" href="signup.php">Registrarse</a></li>
                <li id="lg-bag">
                    <a href="cart.php"><i class="far fa-shopping-cart"></i></a>
                </li>
                <a href="#" id="close"><i class="far fa-times"></i></a>
        </div>

    </section>


    <form method="post" id="form">
        <h2 style="color: blue; margin: auto">Registrarse</h2>
        <input class="input1" id="fn" name="firstName" type="text" placeholder="Nombre *" required="required">
        <input class="input1" id="ln" name="lastName" type="text" placeholder="Apellido *" required="required">
        <input class="input1" id="user" name="username" type="text" placeholder="Username *" required="required">
        <input class="input1" id="email" name="email" type="text" placeholder="Correo electrónico *" required="required">
        <div id="email-msg" style="font-size: 14px; margin-top: -10px; margin-bottom: 10px;"></div>
        <div class="password-wrapper">
            <input class="input1" id="pass" name="password" type="password" placeholder="Contraseña *" required='required'>
            <i class="far fa-eye toggle-eye" id="togglePass"></i>
        </div>

        <div class="password-wrapper">
            <input class="input1" id="cpass" name="confirmPassword" type="password" placeholder="Confirma contraseña *" required='required'>
            <i class="far fa-eye toggle-eye" id="toggleCPass"></i>
        </div>
        <div id="cpass-msg" style="font-size: 14px; margin-top: -10px; margin-bottom: 10px;"></div>
        <label for="dob" style="font-size:14px; color:#555;">Fecha de nacimiento *</label>
        <input class="input1" id="dob" name="dob" type="date" required>
        <input class="input1" id="contact" name="phone" type="number" placeholder="Número *" maxlengrh="0" required="required">
        <div id="phone-msg" style="font-size: 14px; margin-top: -10px; margin-bottom: 10px;"></div>

        <select class="select1" id="role" name="role" required="required">
            <option value="cliente">Cliente</option>
            <option value="admin">Administrador</option>
            <option value="artista">Artista</option>
        </select>
        <button name="submit" type="submit" class="btn">Enviar</button>

    </form>

    <div class="sign">
        <a href="login.php" class="signn">¿Ya tienes una cuenta? Inicia sesión</a>
    </div>


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
