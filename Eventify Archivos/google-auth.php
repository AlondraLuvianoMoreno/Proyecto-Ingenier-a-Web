<?php
session_start();
include("include/connect.php");

// Recibir token enviado desde JS
$id_token = $_POST["id_token"];

// Validar token con Google
$google_api_url = "https://oauth2.googleapis.com/tokeninfo?id_token=".$id_token;
$google_response = json_decode(file_get_contents($google_api_url));

if (isset($google_response->sub)) {

    $google_id = $google_response->sub;
    $email = $google_response->email;
    $name = $google_response->name;

    // 1. Buscar si ya existe en tu base
    $query = "SELECT * FROM accounts WHERE google_id='$google_id'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        // Ya existe → iniciar sesión
        $row = mysqli_fetch_assoc($result);
        $_SESSION['aid'] = $row['aid'];
        echo "success";
        exit();
    }

    // 2. Si no existe → crear cuenta nueva
    $email_safe = mysqli_real_escape_string($con, $email);
    $name_safe = mysqli_real_escape_string($con, $name);

    $insert = "
        INSERT INTO accounts (username, email, google_id, created_at)
        VALUES ('$name_safe', '$email_safe', '$google_id', NOW())
    ";
    mysqli_query($con, $insert);

    // Obtener ID recién creado
    $aid = mysqli_insert_id($con);
    $_SESSION['aid'] = $aid;

    echo "success";
} 
else {
    echo "error";
}
?>
