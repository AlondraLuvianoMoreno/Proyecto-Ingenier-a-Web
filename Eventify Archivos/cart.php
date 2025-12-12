<?php
session_start();

if ($_SESSION['aid'] < 0) {
    header("Location: login.php");
}

if (isset($_GET['re'])) {
    include("include/connect.php");
    $aid = $_SESSION['aid'];
    $pid = $_GET['re'];
    $query = "DELETE FROM CART WHERE aid = $aid and pid = $pid";

    $result = mysqli_query($con, $query);
    header("Location: cart.php");
    exit();
}

if (isset($_POST['check'])) {
    include("include/connect.php");

    $aid = $_SESSION['aid'];

    if (!isset($_POST['address_id']) || empty($_POST['address_id'])) {
        echo "<script>alert('Debe seleccionar una dirección de envío.'); window.location.href = 'cart.php';</script>";
        exit();
    }
    
    if (!isset($_POST['payment_method_id']) || empty($_POST['payment_method_id'])) {
        echo "<script>alert('Debe seleccionar un método de pago.'); window.location.href = 'cart.php';</script>";
        exit();
    }

    $address_id = $_POST['address_id'];
    $payment_method_id = $_POST['payment_method_id'];

    $query = "SELECT * FROM cart JOIN products ON cart.pid = products.pid WHERE aid = $aid";

    $result = mysqli_query($con, $query);

    $result2 = mysqli_query($con, $query);
    $row2 = mysqli_fetch_assoc($result2);

    if (empty($row2['pid'])) {
        header("Location: shop.php");
        exit();
    }

    $stock_error = false;
    $error_messages = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $pid = $row['pid'];
        $pname = $row['pname'];
        $desc = $row['description'];
        $qty = $row['qtyavail'];
        $price = $row['price'];
        $cat = $row['category'];
        $img = $row['img'];
        $brand = $row['brand'];
        $cqty = $row['cqty'];
        $a = $price * $cqty;

        $newqty = $_POST["$pid-qt"];

        if ($newqty > $qty) {
            $stock_error = true;
            $error_messages[] = "Producto: $pname - Stock disponible: $qty, Cantidad solicitada: $newqty";
            

            $newqty = $qty;
            $_POST["$pid-qt"] = $qty;
        }

        if ($newqty < 1) {
            $newqty = 1;
            $_POST["$pid-qt"] = 1;
        }
    }

    if ($stock_error) {
        echo "<script>";
        echo "alert('Hay problemas con el stock:\\n\\n" . implode("\\n", $error_messages) . "\\n\\nLas cantidades se han ajustado al stock disponible. Revise su carrito.');";
        echo "window.location.href = 'cart.php';";
        echo "</script>";
        exit();
    }

    $check_address = "SHOW TABLES LIKE 'addresses'";
    $address_table_exists = mysqli_query($con, $check_address);
    
    if (mysqli_num_rows($address_table_exists) > 0) {
        $check_address = "SELECT * FROM addresses WHERE address_id = $address_id AND aid = $aid";
        $address_result = mysqli_query($con, $check_address);
        
        if (mysqli_num_rows($address_result) == 0) {
            echo "<script>alert('La dirección seleccionada no es válida.'); window.location.href = 'cart.php';</script>";
            exit();
        }
    }

    $check_payment = "SHOW TABLES LIKE 'payment_methods'";
    $payment_table_exists = mysqli_query($con, $check_payment);
    
    if (mysqli_num_rows($payment_table_exists) > 0) {
        $check_payment = "SELECT * FROM payment_methods WHERE payment_method_id = $payment_method_id AND aid = $aid";
        $payment_result = mysqli_query($con, $check_payment);
        
        if (mysqli_num_rows($payment_result) == 0) {
            echo "<script>alert('El método de pago seleccionado no es válido.'); window.location.href = 'cart.php';</script>";
            exit();
        }
    }

    mysqli_data_seek($result, 0); 

    while ($row = mysqli_fetch_assoc($result)) {
        $pid = $row['pid'];
        $pname = $row['pname'];
        $desc = $row['description'];
        $qty = $row['qtyavail'];
        $price = $row['price'];
        $cat = $row['category'];
        $img = $row['img'];
        $brand = $row['brand'];
        $cqty = $row['cqty'];
        $a = $price * $cqty;

        $newqty = $_POST["$pid-qt"];

        $query = "UPDATE CART SET cqty = $newqty where aid = $aid and pid = $pid";

        mysqli_query($con, $query);
    }
    
    $_SESSION['selected_address_id'] = $address_id;
    $_SESSION['selected_payment_method_id'] = $payment_method_id;
    
    header("Location: checkout.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ByteBazaar</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />

    <link rel="stylesheet" href="style.css" />


</head>

<body onload="totala()">
    <section id="header">
        <a href="index.php"><img src="img/logo.png" class="logo" alt="" /></a>

        <div>
            <ul id="navbar">
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>

                <?php

                if ($_SESSION['aid'] < 0) {
                    echo "   <li><a href='login.php'>login</a></li>
            <li><a href='signup.php'>SignUp</a></li>
            ";
                } else {
                    echo "   <li><a href='profile.php'>profile</a></li>
          ";
                }
                ?>
                <li><a href="admin.php">Admin</a></li>
                <li id="lg-bag">
                    <a class="active" href="cart.php"><i class="far fa-shopping-bag"></i></a>
                </li>
                <a href="#" id="close"><i class="far fa-times"></i></a>
            </ul>
        </div>
        <div id="mobile">
            <a href="cart.php"><i class="far fa-shopping-bag"></i></a>
            <i id="bar" class="fas fa-outdent"></i>
        </div>
    </section>

    <section id="page-header" class="about-header">
        <h2>#GameTillTheEnd</h2>

        <p>Providing premium gaming experience</p>
    </section>


    <section id="cart" class="section-p1">
        <table width="100%">
            <thead>
                <tr>
                    <td>Remove</td>
                    <td>Image</td>
                    <td>Product</td>
                    <td>Price</td>
                    <td>Quantity</td>
                    <td>Subtotal</td>
                </tr>
            </thead>
            <tbody>

                <?php

                include("include/connect.php");

                $aid = $_SESSION['aid'];

                $query = "SELECT * FROM cart JOIN products ON cart.pid = products.pid WHERE aid = $aid";

                $result = mysqli_query($con, $query);


                while ($row = mysqli_fetch_assoc($result)) {
                    $pid = $row['pid'];
                    $pname = $row['pname'];
                    $desc = $row['description'];
                    $qty = $row['qtyavail'];
                    $price = $row['price'];
                    $cat = $row['category'];
                    $img = $row['img'];
                    $brand = $row['brand'];
                    $cqty = $row['cqty'];
                    
                    if ($cqty > $qty) {
                        
                        $update_query = "UPDATE CART SET cqty = $qty WHERE aid = $aid AND pid = $pid";
                        mysqli_query($con, $update_query);
                        $cqty = $qty;
                    }
                    
                    $a = $price * $cqty;
                    echo "

            <tr>
              <td>
                <a href='cart.php?re=$pid'><i class='far fa-times-circle'></i></a>
              </td>
              <td><img src='product_images/$img' alt='' /></td>
              <td>$pname</td>
              <td class='pr'>$$price</td>
              <td><input type='number' class = 'aqt' value='$cqty' min = '1' max = '$qty' onchange='subprice()' /></td>
              <td class = 'atd'>$$a</td>
            </tr>
            ";
                }
                ?>

            </tbody>
        </table>
    </section>

    <section id="cart-add" class="section-p1">
        <div id="coupon">

        </div>
        <div id="subtotal">
            <h3>Cart Totals</h3>
            <table>
                <tr>
                    <td>Cart Subtotal</td>
                    <td id='tot1' onload="totala()">$</td>
                </tr>
                <tr>
                    <td>Shipping</td>
                    <td>Free</td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td id='tot' onload="totala()"><strong>$</strong></td>
                </tr>
            </table>
            
            <div id="address-selection" style="margin: 20px 0;">
                <h3>Seleccionar Dirección de Envío</h3>
                <?php
                $check_table = "SHOW TABLES LIKE 'addresses'";
                $table_exists = mysqli_query($con, $check_table);
                
                if (mysqli_num_rows($table_exists) > 0) {
                    $address_query = "SELECT * FROM addresses WHERE aid = $aid ORDER BY is_default DESC";
                    $address_result = mysqli_query($con, $address_query);
                    
                    if (mysqli_num_rows($address_result) > 0) {
                        while ($address = mysqli_fetch_assoc($address_result)) {
                            $address_id = $address['address_id'];
                            $street = $address['street'];
                            $city = $address['city'];
                            $state = $address['state'];
                            $zip_code = $address['zip_code'];
                            $country = $address['country'];
                            $is_default = $address['is_default'];
                            
                            echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd;'>";
                            echo "<input type='radio' name='address_id' value='$address_id' id='address_$address_id' " . ($is_default ? "checked" : "") . " required>";
                            echo "<label for='address_$address_id' style='margin-left: 10px;'>";
                            echo "<strong>$street</strong><br>";
                            echo "$city, $state $zip_code<br>";
                            echo "$country";
                            if ($is_default) {
                                echo " <span style='color: green;'>(Predeterminada)</span>";
                            }
                            echo "</label>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p style='color: red;'>No tiene direcciones registradas. <a href='profile.php?section=addresses'>Agregar dirección</a></p>";
                    }
                } else {
           
                    echo "<p style='color: orange;'>Sistema de direcciones no configurado. Usando dirección por defecto.</p>";
                    echo "<input type='hidden' name='address_id' value='1' checked>";
                }
                ?>
            </div>
        
            <div id="payment-selection" style="margin: 20px 0;">
                <h3>Seleccionar Método de Pago</h3>
                <?php

                $check_table = "SHOW TABLES LIKE 'payment_methods'";
                $table_exists = mysqli_query($con, $check_table);
                
                if (mysqli_num_rows($table_exists) > 0) {
                    $payment_query = "SELECT * FROM payment_methods WHERE aid = $aid ORDER BY is_default DESC";
                    $payment_result = mysqli_query($con, $payment_query);
                    
                    if (mysqli_num_rows($payment_result) > 0) {
                        while ($payment = mysqli_fetch_assoc($payment_result)) {
                            $payment_method_id = $payment['payment_method_id'];
                            $card_type = $payment['card_type'];
                            $last_four = $payment['last_four'];
                            $expiry_month = $payment['expiry_month'];
                            $expiry_year = $payment['expiry_year'];
                            $is_default = $payment['is_default'];
                            
                            echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd;'>";
                            echo "<input type='radio' name='payment_method_id' value='$payment_method_id' id='payment_$payment_method_id' " . ($is_default ? "checked" : "") . " required>";
                            echo "<label for='payment_$payment_method_id' style='margin-left: 10px;'>";
                            echo "<strong>$card_type</strong> terminada en ****$last_four<br>";
                            echo "Expira: $expiry_month/$expiry_year";
                            if ($is_default) {
                                echo " <span style='color: green;'>(Predeterminado)</span>";
                            }
                            echo "</label>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p style='color: red;'>No tiene métodos de pago registrados. <a href='profile.php?section=payment'>Agregar método de pago</a></p>";
                    }
                } else {
                    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd;'>";
                    echo "<input type='radio' name='payment_method_id' value='1' id='payment_1' checked required>";
                    echo "<label for='payment_1' style='margin-left: 10px;'>";
                    echo "<strong>Tarjeta de Crédito/Débito</strong><br>";
                    echo "Pago con tarjeta";
                    echo "</label>";
                    echo "</div>";
                    
                    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd;'>";
                    echo "<input type='radio' name='payment_method_id' value='2' id='payment_2'>";
                    echo "<label for='payment_2' style='margin-left: 10px;'>";
                    echo "<strong>PayPal</strong><br>";
                    echo "Pago a través de PayPal";
                    echo "</label>";
                    echo "</div>";
                    
                    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd;'>";
                    echo "<input type='radio' name='payment_method_id' value='3' id='payment_3'>";
                    echo "<label for='payment_3' style='margin-left: 10px;'>";
                    echo "<strong>Transferencia Bancaria</strong><br>";
                    echo "Pago por transferencia";
                    echo "</label>";
                    echo "</div>";
                }
                ?>
            </div>

            <form method="post" onsubmit="return validateStockBeforeCheckout()">
                <?php

                include("include/connect.php");

                $aid = $_SESSION['aid'];

                $query = "SELECT * FROM cart JOIN products ON cart.pid = products.pid WHERE aid = $aid";

                $result = mysqli_query($con, $query);


                while ($row = mysqli_fetch_assoc($result)) {
                    $pid = $row['pid'];
                    $pname = $row['pname'];
                    $desc = $row['description'];
                    $qty = $row['qtyavail'];
                    $price = $row['price'];
                    $cat = $row['category'];
                    $img = $row['img'];
                    $brand = $row['brand'];
                    $cqty = $row['cqty'];
                    
                    if ($cqty > $qty) {
                        $cqty = $qty;
                    }
                    
                    $a = $price * $cqty;
                    echo "

              <input style='display: none;' name='$pid-p' class='inp' type = 'number' value = '$pid'/>
              <input style='display: none;' name='$pid-qt' class='inq' type = 'number' value = '$cqty'/>
              ";
                }
                ?>
                <button class="normal" name="check">Proceed to checkout</button>
            </form>
            </a>
        </div>
    </section>

    <footer class="section-p1">
        <div class="col">
            <img class="logo" src="img/logo.png" />
            <h4>Contact</h4>
            <p>
                <strong>Address: </strong> Street 2, Johar Town Block A,Lahore

            </p>
            <p>
                <strong>Phone: </strong> +92324953752
            </p>
            <p>
                <strong>Hours: </strong> 9am-5pm
            </p>
        </div>

        <div class="col">
            <h4>My Account</h4>
            <a href="cart.php">View Cart</a>
            <a href="wishlist.php">My Wishlist</a>
        </div>
        <div class="col install">
            <p>Secured Payment Gateways</p>
            <img src="img/pay/pay.png" />
        </div>
        <div class="copyright">
            <p>2021. byteBazaar. HTML CSS </p>
        </div>
    </footer>

    <script src="script.js"></script>
</body>

</html>

<script>
function subprice() {
    var qty = document.getElementsByClassName("aqt");
    var sub = document.getElementsByClassName("atd");
    var pri = document.getElementsByClassName("pr");
    var upd = document.getElementsByClassName("inq");

    for (var i = 0; i < qty.length; i++) {
        var quantity = parseInt(qty[i].value);
        var maxQty = parseInt(qty[i].getAttribute('max'));
        var price = parseFloat(pri[i].innerText.replace('$', ''));
        
        if (quantity > maxQty) {
            quantity = maxQty;
            qty[i].value = maxQty;
            alert('La cantidad no puede exceder el stock disponible (' + maxQty + ' unidades)');
        }
        
        if (quantity < 1) {
            quantity = 1;
            qty[i].value = 1;
        }
        
        sub[i].innerHTML = `$${quantity * price}`;
        upd[i].value = parseInt(qty[i].value);
    }

    totala();
}

function totala() {
    var pri = document.getElementsByClassName("atd");
    let yes = 0;
    for (var i = 0; i < pri.length; i++) {
        yes = yes + parseFloat(pri[i].innerText.replace('$', ''));
    }


    document.getElementById('tot').innerHTML = '$' + yes;
    document.getElementById('tot1').innerHTML = '$' + yes;
}

function validateStockBeforeCheckout() {
    var qtyInputs = document.getElementsByClassName("aqt");
    var hasStockErrors = false;
    var errorMessages = [];
   
    var addressSelected = document.querySelector('input[name="address_id"]:checked');
    if (!addressSelected) {
        alert('Debe seleccionar una dirección de envío.');
        return false;
    }
    
    var paymentSelected = document.querySelector('input[name="payment_method_id"]:checked');
    if (!paymentSelected) {
        alert('Debe seleccionar un método de pago.');
        return false;
    }
    
    for (var i = 0; i < qtyInputs.length; i++) {
        var quantity = parseInt(qtyInputs[i].value);
        var maxQty = parseInt(qtyInputs[i].getAttribute('max'));
        var productName = qtyInputs[i].closest('tr').querySelector('td:nth-child(3)').innerText;
        
        if (quantity > maxQty) {
            hasStockErrors = true;
            errorMessages.push(productName + " - Stock disponible: " + maxQty + ", Cantidad solicitada: " + quantity);
        }
    }
    
    if (hasStockErrors) {
        alert("Hay problemas con el stock:\n\n" + errorMessages.join("\n") + "\n\nPor favor, ajuste las cantidades antes de proceder al checkout.");
        return false; 
    }
    
    return true; 
}
</script>

<script>
window.addEventListener("unload", function() {
  var xhr = new XMLHttpRequest();
  xhr.open("GET", "logout.php", false);
  xhr.send();
});
</script>