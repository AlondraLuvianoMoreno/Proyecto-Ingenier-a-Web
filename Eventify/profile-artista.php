<?php
session_start();


if (isset($_GET['lo'])) {
  $_SESSION['aid'] = -1;
  header("Location: index.php");
  exit();

}

include("include/connect.php");
$aid = $_SESSION['aid'];
$roleQuery = mysqli_query($con, "SELECT role FROM accounts WHERE aid = $aid");
$roleRow = mysqli_fetch_assoc($roleQuery);

if ($roleRow['role'] !== 'artista') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['submit'])) {
  include("include/connect.php");
  $aid = $_SESSION['aid'];

  $firstname = $_POST['a1'];
  $lastname = $_POST['a2'];
  $email = $_POST['a3'];
  $phone = $_POST['a4'];
  $dob = $_POST['a5'];

  $query = "select * from accounts where (phone='$phone' or email='$email') and aid != $aid ";

  $result = mysqli_query($con, $query);
  $row = mysqli_fetch_assoc($result);
  if (!empty($row['aid'])) {
    echo "<script> alert('Credentials already exists'); setTimeout(function(){ window.location.href = 'profile-artista.php'; }, 10); </script>";
    exit();
  }
  if (strtotime($dob) > time()) {
    echo "<script> alert('invalid date'); setTimeout(function(){ window.location.href = 'profile-artista.php'; }, 10); </script>";
    exit();
  }

  if (preg_match('/\D/', $phone) || strlen($phone) != 10) {
    echo "<script> alert('invalid number'); setTimeout(function(){ window.location.href = 'profile-artista.php'; }, 10); </script>";
    exit();
  }

  $query = "UPDATE ACCOUNTS SET afname = '$firstname', alname='$lastname', email='$email', phone='$phone', dob='$dob' WHERE aid = $aid";

  $result = mysqli_query($con, $query);
  header("Location: profile-artista.php");
  exit();
}


if (isset($_POST['abc'])) {
  include("include/connect.php");

  $oid = $_GET['odd'];

  $query = "select * from `order-details` where oid = $oid";
  $result = mysqli_query($con, $query);

  while ($row = mysqli_fetch_assoc($result)) {
    include("include/connect.php");

    $pid = $row['pid'];

    $text = $_POST["$pid-te"];
    $star = $_POST["$pid-rating"];
    $query;
    if (empty($text))
      $query = "insert into `reviews` (oid, pid, rtext, rating) values ($oid, $pid, NULL, $star)";
    else
      $query = "insert into `reviews` (oid, pid, rtext, rating) values ($oid, $pid, '$text', $star)";


    $result2 = mysqli_query($con, $query);
  }

  header("Location: profile-artista.php");
  exit();
}

if (isset($_GET['c'])) {
  header("Location: profile-artista.php");
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
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />

    <link rel="stylesheet" href="style.css" />

    <style>
    .tb {
        max-height: 400px;
        overflow-x: auto;
        overflow-y: auto;
    }



    .tb tr {
        height: 60px;
        margin: 10px;
    }

    .tb td {
        text-align: center;
        margin: 10px;
        padding-left: 40px;
        padding-right: 40px;
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
    <section id="header">
        <a href="index.php"><img src="img/logo.png" class="logo" alt="" /></a>

        <div>
            <ul id="navbar">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="shop.php">Conciertos</a></li>
                <li><a href="about.php">Festivales</a></li>
                <li><a href='profile-admin.php'>Perfil</a></li>
        </div>
    </section>

    <div class="navbar-top">
        <div class="title">
            <h1>Perfil</h1>
        </div>
        <!-- End -->
    </div>
    <!-- End -->

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
      $phone = $row['phone'];
      $email = $row['email'];
      $dob = $row['dob'];
      $user = $row['username'];
      $name = $afname . " " . $alname;
      $role = $row['role'];

      echo "
      <div class='name'>
        $name
      </div>
      <div class='job'>
        " . ucfirst($role) . "
      </div>
    </div>
    ";

        ?>

            <div class="sidenav-url">
                <div class="url">
                    <a href='profile-artista.php?lo=1' class="btn logup">Cerrar sesión</a>
                    <hr allign="center">
                </div>
                <div class="url">
                    <a href='profile-artista.php?upd=1' class="btn logup">Actualizar</a>
                    <hr allign="center">
                </div>
              
                <div class="url">
                    <a href='profile-artista.php?upd=1' class="btn logup">Agregar dirección</a>
                    <hr allign="center">
                </div>
                <div class="url">
                    <a href='profile-artista.php?upd=1' class="btn logup">Agregar método de pago</a>
                    <hr allign="center">
                </div>
                <?php
        if (isset($_GET['odd'])) {
          echo "
                    <div class='url'>
                    <a href='profile-artista.php' class='btn logup'>Cancel</a>
                    <hr allign='center'>
                    </div>
                    ";
        }
        ?>
            </div>
        </div>
        <!-- End -->

        <!-- Main -->
        <div class="main">
            <h2>Información</h2>
            <div class="card">
                <div class="card-body">
                    <i class="fa fa-pen fa-xs edit"></i>
                    <table>
                        <tbody>
                            <?php


              if (isset($_GET['upd'])) {
                include("include/connect.php");

                $aid = $_SESSION['aid'];

                $query = "SELECT * FROM ACCOUNTS WHERE aid = $aid";

                $result = mysqli_query($con, $query);

                $row = mysqli_fetch_assoc($result);

                $afname = $row['afname'];
                $alname = $row['alname'];
                $phone = $row['phone'];
                $email = $row['email'];
                $dob = $row['dob'];
                $user = $row['username'];

                echo "
              <form class='form1' method='post'>
              <tr>
                <td>Nombre</td>
                <td>:</td>
                <td><input name='a1' type='text' value='$afname'></td>
              </tr>
              <tr>
                <td>Apellidos</td>
                <td>:</td>
                <td><input name='a2' type='text' value='$alname'></td>
              </tr>
              <tr>
                <td>Correo</td>
                <td>:</td>
                <td><input name='a3' type='text' value='$email'></td>
              </tr>
              <tr>
              <td>Teléfono</td>
              <td>:</td>
              <td><input name='a4' type='text' value='$phone'></td>
              </tr>
              <tr>
              <td>Fecha de nacimiento</td>
              <td>:</td>
              <td><input name='a5' type='date' value='$dob'></td>
              </tr>

              <tr>
              <td><button name='submit' type='submit' class='btn' style='width: 50%;'>Submit</button></td>

              </tr>
              </form>
              ";



              } else {
                include("include/connect.php");

                $aid = $_SESSION['aid'];
                $query = "SELECT * FROM ACCOUNTS WHERE aid = $aid";

                $result = mysqli_query($con, $query);

                $row = mysqli_fetch_assoc($result);

                $afname = $row['afname'];
                $alname = $row['alname'];
                $phone = $row['phone'];
                $email = $row['email'];
                $dob = $row['dob'];
                $user = $row['username'];
                $name = $afname . " " . $alname;

                echo "
              <tr>
                <td>Nombre</td>
                <td>:</td>
                <td>$afname</td>
              </tr>
              <tr>
                <td>Apellido</td>
                <td>:</td>
                <td>$alname</td>
              </tr>
              <tr>
                <td>Correo</td>
                <td>:</td>
                <td>$email</td>
              </tr>
              <tr>
              <td>Teléfono</td>
              <td>:</td>
              <td>$phone</td>
              </tr>
              <tr>
              <td>Fecha de nacimiento</td>
              <td>:</td>
              <td>$dob</td>
              </tr>
              <tr>
              <td>Username</td>
              <td>:</td>
              <td>$user</td>
              </tr>
              ";
              }
              ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php

      if (isset($_GET['odd'])) {
        include("include/connect.php");

        $oid = $_GET['odd'];

        $query = "select * from `order-details` where oid = $oid";
        $result = mysqli_query($con, $query);

        echo "<h2>Review</h2>
                  <div class='card'>
                  <div class='card-body'>
                      <i class='fa fa-pen fa-xs edit'></i>
                      <div class='tb' style: 'height: 700px; max-height: 700px;'>
                      <form method='post'> <table style='display:table; max-height: 700px;' class='tb'><thead>
                <tr>
                  <th>Name</th>
                  <th>Image</th>
                  <th>Price</th>
                  <th>Review</th>
                  <th>Rating</th>
                </tr>
                </thead><tbody>";

        while ($row = mysqli_fetch_assoc($result)) {
          include("include/connect.php");

          $pid = $row['pid'];
          $query = "select * from products where pid = $pid";

          $result2 = mysqli_query($con, $query);

          $row2 = mysqli_fetch_assoc($result2);

          $img = $row2['img'];
          $pname = $row2['pname'];
          $price = $row2['price'];

          echo " <tr>
                    <td>$pname</td>
                    <td><img src='product_images/$img' width='50px' height='50px' alt='Product 1'></td>
                    <td>$price</td>
                    <td><textarea name='$pid-te'> </textarea></td>
                    <td>
                      <fieldset class='rating' style='width: 300px; padding: 0;' id = 'a-$pid-rating'>
                        <input type='radio' onclick='bruh(`$pid`)' id='$pid-rating1' name='$pid-rating' value='1' required><label for='$pid-rating1' style='padding: 10px;'></label>
                        <input type='radio' onclick='bruh(`$pid`)' id='$pid-rating2' name='$pid-rating' value='2' ><label for='$pid-rating2' style='padding: 10px;'></label>
                        <input type='radio' onclick='bruh(`$pid`)' id='$pid-rating3' name='$pid-rating' value='3' ><label for='$pid-rating3' style='padding: 10px;'></label>
                        <input type='radio' onclick='bruh(`$pid`)' id='$pid-rating4' name='$pid-rating' value='4' ><label for='$pid-rating4' style='padding: 10px;'></label>
                        <input type='radio' onclick='bruh(`$pid`)' id='$pid-rating5' name='$pid-rating' value='5' ><label for='$pid-rating5' style='padding: 10px;'></label>
                      </fieldset>
                    </td>
                  </tr><script>bruh(`$pid`);</script>";
        }
        echo "</tbody></table><div class='asd'><button type='submit' name='abc' class = 'btn' >Submit</button></div>
                </form></tbody>
                  </table>
              </div>
          </div>
         
     ";
      } else {
        echo "<h2>INFORMACIÓN DE ÓRDENES</h2>
                <div class='card'>
                <div class='card-body'>
                    <i class='fa fa-pen fa-xs edit'></i>
                    <div class='tb'>
                        <table style='display:table;' class='tb'>
                            <thead>
                                <tr>
                                    <th>Orden ID</th>
                                    <th>Fecha de orden</th>
                                    <th>Fecha de entrega</th>
                                    <th>Costo total</th>
                                    <th>Dirección</th>
                                    <th>Calificación y reseña</th>
                                </tr>
                            </thead>
                            <tbody>";

        include("include/connect.php");

        $aid = $_SESSION['aid'];

        $query = "SELECT * FROM orders join accounts on orders.aid = accounts.aid where orders.aid = $aid";


        $result = mysqli_query($con, $query);

        while ($row = mysqli_fetch_assoc($result)) {
          $oid = $row['oid'];
          $dateod = $row['dateod'];
          $datedel = $row['datedel'];
          $add = $row['address'];
          $pri = $row['total'];
          if (empty($datedel))
            $datedel = "No enviado aún.";
          echo "


                <tr>
                <td>$oid</td>
                    <td>$dateod</td>
                    <td>$datedel</td>
                    <td>$pri</td>
                <td style='max-width: 300px; max-height: 100px; overflow-x: auto; overflow-y: auto;'>$add</td>
                ";
          if ($datedel != "No enviado aún.") {

            $query1 = "select* from reviews where oid = $oid";
            $r = mysqli_query($con, $query1);
            $w = mysqli_fetch_assoc($r);
            if (empty($w))
              echo "<td><a href='profile-artista.php?odd=$oid'><button class='insert-btn'>Reseña</button></a></td>";
            else
              echo "<td>Calificado</td>";
          }
          echo "</tr>";
        }

        echo "</tbody>
                  </table>
              </div>
          </div>
      </div>";
      }
      ?>



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
        // Get all the rating fields on the page
        function bruh(param) {
            console.log(param);
            const ratingFields = document.querySelectorAll('#a-' + param + '-rating');

            // Loop through each rating field
            ratingFields.forEach(ratingField => {
                // Get all the stars in this rating field
                const stars = ratingField.querySelectorAll('input[type="radio"]');

                // Loop through each star
                stars.forEach(star => {
                    // Listen for click events on this star
                    star.addEventListener('click', function() {
                        // Set the clicked star and all the stars before it to be checked and filled


                        for (let i = 0; i < star.value; i++) {
                            console.log('hello');
                            stars[i].checked = true;
                            stars[i].nextElementSibling.classList.add('checked');
                        }

                        // Set all the stars after the clicked star to be unchecked and empty
                        for (let i = star.value; i < stars.length; i++) {
                            stars[i].checked = false;
                            console.log('hello');

                            stars[i].nextElementSibling.classList.remove('checked');
                        }
                    });
                });
            });
        }
        </script>



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
