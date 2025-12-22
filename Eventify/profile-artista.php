<?php
session_start();
include("include/connect.php");

if (isset($_GET['lo'])) {
    session_destroy(); 
    header("Location: index.php");
    exit();
}

$aid = isset($_SESSION['aid']) ? $_SESSION['aid'] : -1;

if ($aid == -1) {
    header("Location: index.php");
    exit();
}

$roleQuery = mysqli_query($con, "SELECT role FROM accounts WHERE aid = $aid");
$roleRow = mysqli_fetch_assoc($roleQuery);

if ($roleRow['role'] !== 'artista') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['submit_profile'])) {
    $firstname = $_POST['a1'];
    $lastname = $_POST['a2'];
    $email = $_POST['a3'];
    $phone = $_POST['a4'];
    $dob = $_POST['a5'];

    $query = "UPDATE ACCOUNTS SET afname = '$firstname', alname='$lastname', email='$email', phone='$phone', dob='$dob' WHERE aid = $aid";
    mysqli_query($con, $query);
    echo "<script>alert('Perfil actualizado'); window.location.href='profile-artista.php?view=info';</script>";
}

    //AÑADIR UN PRODUCTO
if (isset($_POST['add_product'])) {
    $pname = $_POST['pname'];
    $category = $_POST['category'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $qty = $_POST['qtyavail'];
    $venue = $_POST['venue'];
    $edate = str_replace("T", " ", $_POST['event_date']); 

     if ($price < 0 || $qty < 0) {
        echo "<script>alert('El precio y el stock no pueden ser negativos');</script>";
        exit();
    }

    // Categoría válida
    $allowedCategories = ['Concierto', 'Festival'];
    if (!in_array($category, $allowedCategories)) {
        echo "<script>alert('La categoría solo puede ser Concierto o Festival');</script>";
        exit();
    }

    //  Fecha NO pasada
    $eventTimestamp = strtotime($edate);
    $now = time();

    if ($eventTimestamp < $now) {
        echo "<script>alert('No puedes crear un evento con fecha pasada');</script>";
        exit();
    }

$query = "INSERT INTO products (pname, category, description, price, qtyavail, artist_id, event_date, venue) 
          VALUES ('$pname', '$category', '$desc', '$price', '$qty', '$aid', '$edate', '$venue')";

if (mysqli_query($con, $query)) {

    $productId = mysqli_insert_id($con); 

    if (!empty($_FILES['images']['name'][0])) {

        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {

            $ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
            $fileName = uniqid('img_', true) . '.' . $ext;
            $filePath = "product_images/" . $fileName;

            if (move_uploaded_file($tmpName, $filePath)) {
                mysqli_query($con, "
                    INSERT INTO product_images (product_id, image_path)
                    VALUES ($productId, '$fileName')
                ");
            }
        }
    }

    echo "<script>alert('Evento creado exitosamente'); window.location.href='profile-artista.php?view=products';</script>";

} else {
    echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
}
    
}

    //ACTUALIZAR PRODUCTO
if (isset($_POST['update_product'])) {
    $pid = $_POST['pid'];
    $pname = $_POST['pname'];
    $category = $_POST['category'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $qty = $_POST['qtyavail'];
    $venue = $_POST['venue'];
    $edate = str_replace("T", " ", $_POST['event_date']);

     if ($price < 0 || $qty < 0) {
        echo "<script>alert('El precio y el stock no pueden ser negativos');window.location.href='profile-artista.php?view=products';</script>";
        exit();
    }

    //  Categoría válida
    $allowedCategories = ['Concierto', 'Festival'];
    if (!in_array($category, $allowedCategories)) {
        echo "<script>alert('La categoría solo puede ser Concierto o Festival');window.location.href='profile-artista.php?view=products';</script>";
        exit();
    }

    //  Fecha NO pasada
    $eventTimestamp = strtotime($edate);
    $now = time();

    if ($eventTimestamp < $now) {
        echo "<script>alert('No puedes poner una fecha pasada');window.location.href='profile-artista.php?view=products';</script>";
        exit();
    }

    $query = "UPDATE products SET pname='$pname', category='$category', description='$desc', price='$price', qtyavail='$qty', venue='$venue', event_date='$edate' 
              WHERE pid = $pid AND artist_id = $aid";
    mysqli_query($con, $query);

if (!empty($_FILES['images']['name'][0])) {

    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {

        $fileName = time() . "_" . $_FILES['images']['name'][$key];
        $filePath = "product_images/" . $fileName;

        if (move_uploaded_file($tmpName, $filePath)) {
            mysqli_query($con, "INSERT INTO product_images (product_id, image_path) VALUES ($productId, '$fileName')
            ");
        }
    }
}
    
    echo "<script>alert('Evento modificado'); window.location.href='profile-artista.php?view=products';</script>";
}

if (isset($_POST['update_status'])) {
    $oid = $_POST['oid'];
    $status = $_POST['status'];
    $query = "UPDATE orders SET status = '$status' WHERE oid = $oid";
    mysqli_query($con, $query);
    echo "<script>window.location.href='profile-artista.php?view=sales';</script>";
}

$view = isset($_GET['view']) ? $_GET['view'] : 'info'; 

if (isset($_POST['delete_product'])) {

    $pid = intval($_POST['pid']);

    // Verificar que el evento pertenece al artista
    $check = mysqli_query($con, "SELECT pid FROM products WHERE pid = $pid AND artist_id = $aid");

    if (mysqli_num_rows($check) == 0) {
        echo "<script>alert('Evento no encontrado o no autorizado');</script>";
        exit();
    }

    // Obtener TODAS las imágenes del evento
    $imgs = mysqli_query($con, "SELECT image_path FROM product_images WHERE product_id = $pid");

    // Borrar archivos físicos
    while ($img = mysqli_fetch_assoc($imgs)) {
        $path = "product_images/" . $img['image_path'];
        if (file_exists($path)) {
            unlink($path);
        }
    }

    // Borrar registros de imágenes en BD
    mysqli_query($con, "DELETE FROM product_images WHERE product_id = $pid ");

    // Borrar el evento
    mysqli_query($con, "DELETE FROM products WHERE pid = $pid AND artist_id = $aid");

    echo "<script>alert('Evento eliminado correctamente');
            window.location.href='profile-artista.php?view=products';
        </script>";

    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel Artista - Eventify</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="style.css" />
    
    <style>
        .tb { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px; }
        .tb th, .tb td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
        .tb th { background-color: #088178; color: #333; text-align: center;}
        
        .btn-action { 
            padding: 8px 15px; background-color: #088178; color: white; border: none; border-radius: 4px; cursor: pointer; margin-top: 5px; width: 100%;
        }
        .btn-action:hover { background-color: #065c56; }

        .form-control { width: 100%; padding: 5px; box-sizing: border-box; margin-bottom: 5px; border: 1px solid #ccc; border-radius: 4px;}
        textarea.form-control { resize: vertical; height: 60px; }
        
        .toggle-create {
            background-color: #e3e6f3; padding: 15px; cursor: pointer; border-radius: 5px; font-weight: bold; margin-bottom: 10px;
        }
        .create-section { display: none; padding: 15px; border: 1px solid #e3e6f3; margin-bottom: 20px; }
        
        .summary-box { background-color: #e3e6f3; padding: 20px; border-radius: 8px; margin-bottom: 20px; }

        .sidenav { width: 250px !important; padding-top: 20px; }
        .main { margin-left: 270px !important; padding: 20px 30px; width: calc(100% - 270px) !important;}
        .sidenav .sidenav-url .url { text-align: center; width: 100%; margin-bottom: 10px; }
        .sidenav .sidenav-url .url a.btn {
            display: block !important; width: 85% !important; margin: 0 auto !important;
            padding: 12px 10px !important; font-size: 16px !important;
            white-space: normal !important; height: auto !important;
            border-radius: 8px !important; color: #fff !important; 
            background-color: #088178 !important; text-decoration: none;
        }
        .sidenav .sidenav-url .url hr { display: none; }
    </style>
    
    <script>
        function toggleCreate() {
            var x = document.getElementById("createDiv");
            if (x.style.display === "none") { x.style.display = "block"; } else { x.style.display = "none"; }
        }
    </script>
</head>

<body>
    <section id="header">
        <a href="index.php"><img src="img/logo.png" class="logo" alt="" /></a>
        <div>
            <ul id="navbar">
                <li><a href="shop.php">Eventos</a></li>
                <li><a href="profile-artista.php" class="active">Mi panel artista</a></li>
                <li><a href='profile.php?lo=1'>Cerrar sesión</a></li>
            </ul>
        </div>
    </section>

    <div class="sidenav">
        <div class="profile">
            <?php
            $queryUser = "SELECT * FROM accounts WHERE aid = $aid";
            $resUser = mysqli_query($con, $queryUser);
            $userRow = mysqli_fetch_assoc($resUser);
            ?>
            <img src="img/usuario.png" alt="" width="50">
            <div class="name"><?php echo $userRow['afname'] . " " . $userRow['alname']; ?></div>
            <div class="job">Artista</div>
            
            <div class="job" style="margin-top: 5px; color: #555; font-weight: bold;">
                ID: #<?php echo $aid; ?>
            </div>
        </div>

        <div class="sidenav-url">
            <div class="url"><a href="profile-artista.php?view=info" class="btn logup">Mi Perfil</a><hr></div>
            <div class="url"><a href="profile-artista.php?view=products" class="btn logup">Mis Eventos</a><hr></div>
            <div class="url"><a href="profile-artista.php?view=sales" class="btn logup">Mis Ventas</a><hr></div>
        </div>
    </div>

    <div class="main">
        
        <?php if ($view == 'info') { ?>
            <h2>Editar Información Personal</h2>
            <div class="card">
                <div class="card-body">
                    <form method="post">
                        <table class="tb">
                            <tr><td>Nombre</td><td><input type="text" name="a1" value="<?php echo $userRow['afname']; ?>" class="form-control"></td></tr>
                            <tr><td>Apellidos</td><td><input type="text" name="a2" value="<?php echo $userRow['alname']; ?>" class="form-control"></td></tr>
                            <tr><td>Correo</td><td><input type="text" name="a3" value="<?php echo $userRow['email']; ?>" class="form-control"></td></tr>
                            <tr><td>Teléfono</td><td><input type="text" name="a4" value="<?php echo $userRow['phone']; ?>" class="form-control"></td></tr>
                            <tr><td>Nacimiento</td><td><input type="date" name="a5" value="<?php echo $userRow['dob']; ?>" class="form-control"></td></tr>
                            <tr><td colspan="2"><button name="submit_profile" class="btn-action">Guardar Cambios</button></td></tr>
                        </table>
                    </form>
                </div>
            </div>

        <?php } elseif ($view == 'products') { ?>
            <h2>Mis Eventos</h2>

            <div class="toggle-create" onclick="toggleCreate()">+ Crear Nuevo Evento (Click para desplegar)</div>
            <div id="createDiv" class="create-section">
                <h3>Registrar Nuevo Evento</h3><br>
                <form method="post" enctype="multipart/form-data">
                    <table style="width:100%">
                        <tr>
                            <td><label>Nombre del Evento:</label><input type="text" name="pname" class="form-control" required></td>
                            <td><label>Categoría:</label><select name="category" class="form-control" required>
                                <option value="">-- Selecciona categoría --</option>
                                <option value="Concierto">Concierto</option>
                                <option value="Festival">Festival</option>
                            </select> </td>
                        </tr>
                        <tr>
                            <td><label>Fecha:</label><input type="datetime-local" name="event_date" class="form-control" required></td>
                            <td><label>Lugar del evento:</label>
                            <select name="venue" class="form-control" required>
                                <option value="">-- Selecciona un lugar --</option>
                                <option value="Palacio de los Deportes">Palacio de los Deportes</option>
                                <option value="Estadio Azteca">Estadio Azteca</option>
                                <option value="Estadio GNP Seguros">Estadio GNP Seguros</option>
                                <option value="Autódromo Hermanos Rodríguez">Autódromo Hermanos Rodríguez</option>
                                <option value="Parque Bicentenario">Parque Bicentenario</option>
                                <option value="Auditorio Nacional">Auditorio Nacional</option>
                                <option value="Arena Ciudad de México">Arena Ciudad de México</option>
                                <option value="Teatro Metropólitan">Teatro Metropólitan</option>
                                <option value="Plaza de Toros México">Plaza de Toros México</option>
                                <option value="Parque Nacional de las Estacas">Parque Nacional de las Estacas</option>
                            </td>
                        </tr>
                        <tr>
                            <td><label>Precio:</label><input type="number" name="price" class="form-control" min="0" step="1" oninput="noNegative(this)" required></td>
                            <td><label>Stock:</label><input type="number" name="qtyavail" class="form-control" min="0" step="1" oninput="noNegative(this)" required></td>
                        </tr>
                        <tr>
                            <td colspan="2"><label>Descripción:</label><textarea name="description" class="form-control"></textarea></td>
                        </tr>
                        <tr>
                            <td colspan="2"><label>Imagen:</label><input type="file" name="images[]" class="form-control" multiple required></td>
                        </tr>
                        <tr>
                            <td colspan="2"><button type="submit" name="add_product" class="btn-action">Crear Evento</button></td>
                        </tr>
                    </table>
                </form>
            </div>

            <div class="card">
                <div class="card-body tb-container">
                    <table class="tb">
                        <thead>
                            <tr>
                                <th width="10%">Imagen</th>
                                <th width="20%">Información Principal</th>
                                <th width="25%">Detalles</th>
                                <th width="12%">Venta</th>
                                <th width="13%">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $prodQuery = "SELECT * FROM products WHERE artist_id = $aid ORDER BY event_date ASC"; 
                            $prodRes = mysqli_query($con, $prodQuery);

                            if (mysqli_num_rows($prodRes) > 0) {
                                while ($prod = mysqli_fetch_assoc($prodRes)) {
                                    $phpdate = strtotime( $prod['event_date'] );
                                    $mysqldate = date( 'Y-m-d\TH:i', $phpdate );

                                    echo "<form method='post' enctype='multipart/form-data'>";
                                    echo "<tr>";
                                    $imgs = mysqli_query($con, " SELECT image_path 
                                                                FROM product_images 
                                                                WHERE product_id = {$prod['pid']}");

                                    echo "<td style='text-align:center'>";
                                    while ($img = mysqli_fetch_assoc($imgs)) {
                                        echo "
                                            <img 
                                                src='product_images/{$img['image_path']}'
                                                style='width:60px; height:60px; object-fit:cover; margin:4px; border-radius:6px;'
                                            >
                                        ";
                                    }
                                    echo "
                                    <br>
                                    <input type='file' name='images[]' class='form-control' multiple style='font-size:10px'>
                                    </td>";                                   

                                    echo "<td>
                                             <small>Categoría:</small>
                                            <select name='category' class='form-control'>
                                                <option value=''>-- Selecciona --</option>
                                                <option value='Concierto'" . ($prod['category']=='Concierto' ? " selected" : "") . ">Concierto</option>
                                                <option value='Festival'" . ($prod['category']=='Festival' ? " selected" : "") . ">Festival</option>
                                            </select>
                                            <small>Fecha:</small>
                                            <input type='datetime-local' name='event_date' class='form-control' value='$mysqldate'>
                                          </td>";
                                    echo "<td>
                                            <small>Lugar:</small>
                                            <select name='venue' class='form-control'>
                                                <option value=''>-- Selecciona un lugar --</option>
                                                <option value='Palacio de los Deportes'" . ($prod['venue']=='Palacio de los Deportes' ? " selected" : "") . ">Palacio de los Deportes</option>
                                                <option value='Estadio Azteca'" . ($prod['venue']=='Estadio Azteca' ? " selected" : "") . ">Estadio Azteca</option>
                                                <option value='Estadio GNP Seguros'" . ($prod['venue']=='Estadio GNP Seguros' ? " selected" : "") . ">Estadio GNP Seguros</option>
                                                <option value='Autódromo Hermanos Rodríguez'" . ($prod['venue']=='Autódromo Hermanos Rodríguez' ? " selected" : "") . ">Autódromo Hermanos Rodríguez</option>
                                                <option value='Parque Bicentenario'" . ($prod['venue']=='Parque Bicentenario' ? " selected" : "") . ">Parque Bicentenario</option>
                                                <option value='Auditorio Nacional'" . ($prod['venue']=='Auditorio Nacional' ? " selected" : "") . ">Auditorio Nacional</option>
                                                <option value='Arena Ciudad de México'" . ($prod['venue']=='Arena Ciudad de México' ? " selected" : "") . ">Arena Ciudad de México</option>
                                                <option value='Teatro Metropólitan'" . ($prod['venue']=='Teatro Metropólitan' ? " selected" : "") . ">Teatro Metropólitan</option>
                                                <option value='Plaza de Toros México'" . ($prod['venue']=='Plaza de Toros México' ? " selected" : "") . ">Plaza de Toros México</option>
                                                <option value='Parque Nacional de las Estacas'" . ($prod['venue']=='Parque Nacional de las Estacas' ? " selected" : "") . ">Parque Nacional de las Estacas</option>
                                            </select>
                                            <small>Descripción:</small>
                                            <textarea name='description' class='form-control'>{$prod['description']}</textarea>
                                          </td>";
                                    echo "<td>
                                            <small>Precio ($):</small>
                                            <input type='number' name='price' class='form-control' value='{$prod['price']}'>
                                            <small>Stock:</small>
                                            <input type='number' name='qtyavail' class='form-control' value='{$prod['qtyavail']}'>
                                          </td>";
                                    echo "<td>
                                            <input type='hidden' name='pid' value='{$prod['pid']}'>
                                            <button type='submit' name='update_product' class='btn-action'>Actualizar</button>
                                            <button type='submit' name='delete_product' class='btn-action' onclick='return confirm('¿Seguro que quieres eliminar el evento?')'
                                            style='background:#d9534f;'>Eliminar</button>
                                          </td>";
                                    echo "</tr>";
                                    echo "</form>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center;'>No tienes eventos registrados. ¡Usa el botón de arriba para crear uno!</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php } elseif ($view == 'sales') { ?>
            <h2>Mis Ventas e Ingresos</h2>
             <form method="get" style="margin-bottom:20px;">
                <input type="hidden" name="view" value="sales">

                <label><strong>Selecciona el día:</strong></label>
                <input type="date"
                    name="filter_date"
                    value="<?php echo $_GET['filter_date'] ?? date('Y-m-d'); ?>"
                    required>

                <button type="submit" class="btn-action" style="width:auto;">
                    Consultar
                </button>
            </form>
            
            <?php
            $filterDate = isset($_GET['filter_date']) && $_GET['filter_date'] != ''
            ? $_GET['filter_date']
            : null;
            
            $today = date('Y-m-d');
            
            $salesQuery = " SELECT COALESCE(SUM(od.subtotal),0) AS total_dia
                            FROM orders o
                            JOIN `order-details` od ON o.oid = od.oid
                            JOIN products p ON od.pid = p.pid
                            WHERE p.artist_id = $aid
                            AND o.status IN ('pagado','enviado','entregado')";

            if ($filterDate) {
                $salesQuery .= " AND o.dateod = '$filterDate'";
            } else {
                $salesQuery .= " AND o.dateod = CURDATE()";
            }

            $salesRes = mysqli_query($con, $salesQuery);
            $salesRow = mysqli_fetch_assoc($salesRes);
            $totalDia = $salesRow['total_dia'];
                           
            $salesRes = mysqli_query($con, $salesQuery);
            $salesRow = mysqli_fetch_assoc($salesRes);
            $totalDia = $salesRow['total_dia'] ? $salesRow['total_dia'] : 0;
            ?>

            <div class="summary-box">
            <h3>Ganancias del <?php echo $filterDate ? $filterDate : 'día de hoy'; ?>: $<?php echo $totalDia; ?> MXN
            </h3>
        </div>

            <div class="card">
                <div class="card-body">
                    <table class="tb">
                        <thead>
                            <tr>
                                <th>Orden ID</th>
                                <th>Fecha</th>
                                <th>Mi Ganancia</th>
                                <th>Dirección</th>
                                <th>Estatus Global</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $orderQuery = "SELECT DISTINCT o.*
                                           FROM orders o
                                           JOIN `order-details` od ON o.oid = od.oid
                                           JOIN products p ON od.pid = p.pid
                                           WHERE p.artist_id = $aid
                                           ORDER BY o.dateod DESC";
                                           
                            $orderRes = mysqli_query($con, $orderQuery);
                            
                            if (mysqli_num_rows($orderRes) > 0) {
                                while ($ord = mysqli_fetch_assoc($orderRes)) {
                                    $currStatus = $ord['status'];
                                    
                                    $thisOid = $ord['oid'];
                                    $subQ = "SELECT SUM(od.subtotal) as subtotal
                                             FROM `order-details` od
                                             JOIN products p ON od.pid = p.pid
                                             WHERE od.oid = $thisOid AND p.artist_id = $aid";
                                    $subRes = mysqli_query($con, $subQ);
                                    $subRow = mysqli_fetch_assoc($subRes);
                                    $myEarnings = $subRow['subtotal'] ? $subRow['subtotal'] : 0;
                                    echo "<form method='post'><tr>";
                                    echo "<td>#{$ord['oid']}</td>";
                                    echo "<td>{$ord['dateod']}</td>";
                                    echo "<td>\${$myEarnings} <small style='color:gray;'>(de \${$ord['total']})</small></td>";
                                    echo "<td style='font-size:12px;'>{$ord['address_snapshot']}</td>";
                                    echo "<td>
                                            <select name='status' class='form-control'>
                                                <option value='pendiente' ".($currStatus=='pendiente'?'selected':'').">Pendiente</option>
                                                <option value='pagado' ".($currStatus=='pagado'?'selected':'').">Pagado</option>
                                                <option value='enviado' ".($currStatus=='enviado'?'selected':'').">Enviado</option>
                                                <option value='entregado' ".($currStatus=='entregado'?'selected':'').">Entregado</option>
                                            </select>
                                          </td>";
                                    echo "<td>
                                            <input type='hidden' name='oid' value='{$ord['oid']}'>
                                            <button type='submit' name='update_status' class='btn-action'>Guardar</button>
                                          </td>";
                                    echo "</tr></form>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center;'>Aún no tienes ventas registradas.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>

    </div>

    <footer class="section-p1">
        <div class="copyright"><p>2025. Eventify - Panel Artista</p></div>
    </footer>

</body>
</html>
