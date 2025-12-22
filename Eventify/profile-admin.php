<?php
session_start();
include("include/connect.php");

// Producto más vendido del mes
$bestProductQuery = "SELECT p.pid, p.pname, SUM(od.qty) AS total_vendidos
                    FROM `order-details` od
                    JOIN orders o ON o.oid = od.oid
                    JOIN products p ON p.pid = od.pid
                    WHERE o.status IN ('pagado','enviado','entregado') 
                    AND MONTH(o.dateod) = MONTH(CURRENT_DATE()) 
                    AND YEAR(o.dateod) = YEAR(CURRENT_DATE())
                    GROUP BY p.pid
                    ORDER BY total_vendidos DESC
                    LIMIT 1";


$bestProductResult = mysqli_query($con, $bestProductQuery);
$bestProduct = mysqli_fetch_assoc($bestProductResult);

if (isset($_GET['lo'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

$aid = isset($_SESSION['aid']) ? $_SESSION['aid'] : -1;
if ($aid == -1) { header("Location: index.php"); exit(); }

$roleQuery = mysqli_query($con, "SELECT role FROM accounts WHERE aid = $aid");
$roleRow = mysqli_fetch_assoc($roleQuery);
if ($roleRow['role'] !== 'admin') { header("Location: index.php"); exit(); }


// actualizar perfil
if (isset($_POST['submit_profile'])) {
    $firstname = $_POST['a1']; $lastname = $_POST['a2'];
    $email = $_POST['a3']; $phone = $_POST['a4']; $dob = $_POST['a5'];
    mysqli_query($con, "UPDATE accounts SET afname = '$firstname', alname='$lastname', email='$email', phone='$phone', dob='$dob' WHERE aid = $aid");
    echo "<script>alert('Datos actualizados'); window.location.href='profile-admin.php?view=users';</script>";
}

// agregar producto
if (isset($_POST['add_product'])) {
    $pname = $_POST['pname'];
    $category = $_POST['category'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $qty = $_POST['qtyavail'];
    $venue = $_POST['venue'];   
    $artist_id = $_POST['artist_id'];
    $edate = str_replace("T", " ", $_POST['event_date']); 
    $del_est = isset($_POST['delivery_est']) ? $_POST['delivery_est'] : '3-5 días'; 

    if ($price < 0 || $qty < 0) {
        echo "<script>alert('El precio y el stock no pueden ser negativos');</script>";
        exit();
    }

    $allowedCategories = ['Concierto', 'Festival'];
    if (!in_array($category, $allowedCategories)) {
        echo "<script>alert('La categoría solo puede ser Concierto o Festival');</script>";
        exit();
    }

    $eventTimestamp = strtotime($edate);
    $now = time();
    if ($eventTimestamp < $now) {
        echo "<script>alert('No puedes crear un evento con fecha pasada');</script>";
        exit();
    }

    $query = "INSERT INTO products (pname, category, description, price, qtyavail, artist_id, event_date, venue, delivery_est) 
          VALUES ('$pname', '$category', '$desc', '$price', '$qty', '$artist_id', '$edate', '$venue', '$del_est')";

    if (mysqli_query($con, $query)) {
        $productId = mysqli_insert_id($con);
        if (!empty($_FILES['images']['name'][0])) {
            $isFirst = true;
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
                if ($tmp != '') {
                    $ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                    $fileName = uniqid('img_', true) . '.' . $ext;
                    $filePath = "product_images/" . $fileName;
                    if (move_uploaded_file($tmp, $filePath)) {
                        if ($isFirst) {
                    // imagen principal
                    mysqli_query($con,"UPDATE products  SET img = '$fileName'  WHERE pid = $productId");
                    $isFirst = false;
                } else {
                    // las demás imagenes
                    mysqli_query($con,"INSERT INTO product_images (product_id, image_path) VALUES ($productId, '$fileName')" );
                }
                    }
                }
            }
        }
        echo "<script>alert('Evento creado correctamente'); window.location.href='profile-admin.php?view=inventory';</script>";
    } else {
        echo "<script>alert('Error al agregar: " . mysqli_error($con) . "');</script>";
    }
}

// eliminar producto
if (isset($_POST['delete_product'])) {
    $pid = $_POST['pid'];
    $imgs = mysqli_query($con, "SELECT image_path FROM product_images WHERE product_id = $pid");
    while ($img = mysqli_fetch_assoc($imgs)) {
        $path = "product_images/" . $img['image_path'];
        if (file_exists($path)) { unlink($path); }
    }
    mysqli_query($con, "DELETE FROM product_images WHERE product_id = $pid");
    mysqli_query($con, "DELETE FROM products WHERE pid = $pid");

    echo "<script>alert('Evento eliminado correctamente'); window.location.href='profile-admin.php?view=inventory';</script>";
    exit();
}

// actualizar producto
if (isset($_POST['update_product'])) {
    $pid = $_POST['pid'];
    $pname = $_POST['pname'];
    $category = $_POST['category'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $qty = $_POST['qtyavail'];
    $del_est = isset($_POST['delivery_est']) ? $_POST['delivery_est'] : '3-5 días';
    
    // Nuevos campos
    $venue = $_POST['venue'];
    $edate = str_replace("T", " ", $_POST['event_date']);

    $query = "UPDATE products SET 
              pname='$pname', category='$category', description='$desc', 
              price='$price', qtyavail='$qty', delivery_est='$del_est',
              venue='$venue', event_date='$edate' 
              WHERE pid=$pid";
              
    mysqli_query($con, $query);

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
            if ($tmp != '') {
                $ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                $fileName = uniqid('img_', true) . '.' . $ext;
                $filePath = "product_images/" . $fileName;
                if (move_uploaded_file($tmp, $filePath)) {
                    mysqli_query($con, "INSERT INTO product_images (product_id, image_path) VALUES ($pid, '$fileName')");
                }
            }
        }
    }
    echo "<script>window.location.href='profile-admin.php?view=inventory';</script>";
}

// borrar una sola imagen
if (isset($_GET['del_img'])) {
    $img_id = $_GET['del_img'];
    mysqli_query($con, "DELETE FROM product_images WHERE image_id = $img_id");
    header("Location: profile-admin.php?view=inventory");
    exit();
}

// actualizar usuario
if (isset($_POST['update_user'])) {
    $aid_u = $_POST['aid'];
    $afname = $_POST['afname'];
    $alname = $_POST['alname'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    mysqli_query($con, "UPDATE accounts SET afname='$afname', alname='$alname', email='$email', role='$role' WHERE aid=$aid_u");
    echo "<script>window.location.href='profile-admin.php?view=accounts';</script>";
}

// eliminar usuario
if (isset($_POST['delete_user'])) {
    $deleteAid = $_POST['aid'];
    if ($deleteAid == $aid) {
        echo "<script>alert('No puedes eliminar tu propio usuario');</script>";
        exit();
    }
    mysqli_query($con, "DELETE FROM accounts WHERE aid = $deleteAid");
    echo "<script>alert('Usuario eliminado correctamente'); window.location.href='profile-admin.php?view=accounts';</Script>";
}

$view = isset($_GET['view']) ? $_GET['view'] : 'reports'; 
   $artistsQ = mysqli_query($con, "SELECT aid, afname, alname, username 
                               FROM accounts 
                               WHERE role = 'artista'");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Eventify</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    
    <style>
        body { background-color: #f0f2f5; }
        .tb { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; background: #fff; }
        .tb th, .tb td { border: 1px solid #ddd; padding: 10px; vertical-align: middle; text-align: center; }
        .tb th { background-color: #2c3e50; color: #333; }
        
        .report-card.full-width {grid-column: 1 / -1;}
        .btn-action { padding: 8px 12px; background-color: #088178; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; width: 100%; margin-top: 2px;}
        .form-control { width: 100%; padding: 6px; margin-bottom: 5px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px;}
        .create-box { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-top: 5px solid #088178; }
        
        .sidenav { width: 250px !important; padding-top: 20px; background: #fff; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .main { margin-left: 270px !important; padding: 20px; width: calc(100% - 270px) !important; }
        .sidenav .sidenav-url .url { text-align: center; width: 100%; margin-bottom: 10px; }
        .sidenav .sidenav-url .url a.btn {
            display: block !important; width: 85% !important; margin: 0 auto !important;
            padding: 12px 10px !important; font-size: 15px !important;
            border-radius: 8px !important; color: #fff !important; 
            background-color: #2c3e50 !important; text-decoration: none; text-align: center;
            white-space: normal !important; height: auto !important;
        }
        .sidenav .sidenav-url .url hr { display: none; }
        
        .report-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .report-card { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .big-number { font-size: 2em; font-weight: bold; color: #088178; }
        .stars { color: #f1c40f; letter-spacing: 2px; }
    </style>
    
    <script>
        function toggleDiv(id) {
            var x = document.getElementById(id);
            x.style.display = (x.style.display === "none") ? "block" : "none";
        }
    </script>
</head>

<body>

    <section id="header">
        <a href="index.php"><img src="img/logo.png" class="logo" alt="" /></a>
        <div>
            <ul id="navbar">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="profile-admin.php" class="active">Panel administrador</a></li>
                <li><a href='profile.php?lo=1'>Cerrar sesión</a></li>

            </ul>
        </div>
    </section>

    <div class="sidenav">
        <div class="profile" style="text-align: center;">
            <?php
            $queryUser = "SELECT * FROM accounts WHERE aid = $aid";
            $resUser = mysqli_query($con, $queryUser);
            $userRow = mysqli_fetch_assoc($resUser);
            ?>
            <div class="name" style="font-weight: bold; margin-top:10px;"><?php echo $userRow['afname']; ?></div>
            <div class="job" style="color: #088178;">ADMINISTRADOR</div>
        </div>
        <br>
        <div class="sidenav-url">
            <div class="url"><a href="profile-admin.php?view=reports" class="btn">Reportes y Estadísticas</a></div>
            <div class="url"><a href="profile-admin.php?view=inventory" class="btn">Gestión de Productos</a></div>
            <div class="url"><a href="profile-admin.php?view=users" class="btn">Información Personal</a></div>
            <div class="url"><a href="profile-admin.php?view=accounts" class="btn">Gestión de usuarios</a></div>
        </div>
    </div>

    <div class="main">
        
        <?php if ($view == 'reports') { ?>
            <h2>Reportes de Ventas</h2>

            <div class="report-grid" style="margin-bottom:20px;" >
                <div class="report-card full-width">
                    <h2>Evento con más boletos vendidos del mes</h2>

                    <?php if ($bestProduct) { ?>
                        <p><strong><?php echo $bestProduct['pname'];?></strong></p>
                        <p style = "font-size: 20px; font-weight: 500;">
                            <?php echo $bestProduct['total_vendidos']; ?> boletos vendidos
                        </p>
                    <?php } else { ?>
                        <p>No hay ventas registradas este mes.</p>
                    <?php } ?>
                </div>
            </div>


            <div class="create-box" style="border-top: 5px solid #2c3e50;">
                <form method="get" action="profile-admin.php">
                    <input type="hidden" name="view" value="reports">
                    <h4 style="margin-top:0;">Filtrar Estadísticas</h4>
                    
                    <div style="display: flex; gap: 15px; align-items: flex-end;">
                        <div style="flex:1;">
                            <label>Fecha específica:</label>
                            <input type="date" name="filter_date" value="<?php echo isset($_GET['filter_date']) ? $_GET['filter_date'] : ''; ?>" class="form-control">
                        </div>
                        <div style="flex:1;">
                            <label>Artista:</label>
                            <select name="filter_artist" class="form-control">
                                <option value="">-- Todos los Artistas --</option>
                                <?php
                                $artQ = mysqli_query($con, "SELECT aid, afname, alname, username FROM accounts WHERE role='artista'");
                                while($art = mysqli_fetch_assoc($artQ)) {
                                    $sel = (isset($_GET['filter_artist']) && $_GET['filter_artist'] == $art['aid']) ? 'selected' : '';
                                    echo "<option value='{$art['aid']}' $sel>{$art['afname']} {$art['alname']} ({$art['username']})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn-action" style="padding: 9px 20px; width: auto; margin-bottom: 5px;">Filtrar</button>
                            <a href="profile-admin.php?view=reports" class="btn-action" style="padding: 9px 20px; background-color: #7f8c8d; text-decoration:none; margin-left:5px;">Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php
            $whereSQL = "WHERE o.status IN ('pagado','enviado','entregado')"; // Solo ventas completadas
            
            // Filtro Fecha
            $filterDateText = "Histórico General";
            if (isset($_GET['filter_date']) && !empty($_GET['filter_date'])) {
                $fDate = $_GET['filter_date'];
                $whereSQL .= " AND DATE(o.dateod) = '$fDate'";
                $filterDateText = "Resultados del: " . date('d/m/Y', strtotime($fDate));
            } else {
                // Si no hay filtro y tampoco filtro de artista, mostrar mes actual por defecto
                if (!isset($_GET['filter_artist'])) {
                    $month = date('m'); $year = date('Y');
                    $whereSQL .= " AND MONTH(o.dateod) = '$month' AND YEAR(o.dateod) = '$year'";
                    $filterDateText = "Este Mes (" . date('M Y') . ")";
                }
            }

            // Filtro Artista
            if (isset($_GET['filter_artist']) && !empty($_GET['filter_artist'])) {
                $fArt = $_GET['filter_artist'];
                $whereSQL .= " AND p.artist_id = $fArt";
                $filterDateText .= " (Filtrado por Artista)";
            }

            // Query unificado: Orders -> Order-Details -> Products
            $statsQuery = " SELECT 
                            DATE(o.dateod) AS dia,
                            COUNT(DISTINCT o.oid) AS pedidos,
                            SUM(od.qty) AS boletos_vendidos,
                            SUM(od.subtotal) AS dinero
                        FROM `order-details` od
                        JOIN orders o ON od.oid = o.oid
                        JOIN products p ON od.pid = p.pid
                        $whereSQL
                        GROUP BY dia
                        ORDER BY dia DESC
                        ";


            $statsRes = mysqli_query($con, $statsQuery);
            ?>

            <div class="report-grid">
                <div class="report-card" style="grid-column: span 2;">
                    <h3>Reporte: <?php echo $filterDateText; ?></h3>
                    <table class="tb">
                        <thead>
                            <tr><th>Fecha</th>
                                <th>Pedidos</th>
                                <th>Boletos Vendidos</th>
                                <th>Ingresos ($)</th></tr>
                        </thead>
                        <tbody>
                        <?php
                        $totalDinero = 0; $totalOps = 0; $totalBoletos=0;
                        if(mysqli_num_rows($statsRes) > 0) {
                            while($d = mysqli_fetch_assoc($statsRes)){
                                $totalDinero += $d['dinero'];
                                $totalOps += $d['pedidos'];
                                $totalBoletos += $d['boletos_vendidos'];                               
                               echo "<tr>
                                        <td>".date('d/m/Y', strtotime($d['dia']))."</td>
                                        <td>{$d['pedidos']}</td>
                                        <td>{$d['boletos_vendidos']}</td>
                                        <td>$".number_format($d['dinero'], 2)."</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No se encontraron ventas con estos filtros.</td></tr>";
                        }
                        ?>
                        <tr style="background-color: #ecf0f1;">
                            <td><strong>TOTALES</strong></td>
                            <td><strong><?php echo $totalOps; ?></strong></td>
                            <td><strong><?php echo $totalBoletos; ?></strong></td>
                            <td><strong class="big-number" style="font-size: 1.5em;">
                                $<?php echo number_format($totalDinero, 2); ?>
                            </strong></td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>

        <?php } elseif ($view == 'inventory') { ?>
            <h2>Gestión de Catálogo</h2>
            
            <div class="create-box">
                <h3 style="cursor: pointer;" onclick="toggleDiv('addForm')"><i class="fas fa-plus-circle"></i> Agregar nuevo evento (Click aquí)</h3>
                <div id="addForm" style="display:none; margin-top:15px;">
                    <form method="post" enctype="multipart/form-data">
                        <table style="width:100%">
                            <tr>
                                <td><label>Nombre:</label><input type="text" name="pname" class="form-control" required></td>
                                <td><label>Categoría:</label>
                                    <select name="category" class="form-control">
                                        <option value="Concierto">Concierto</option>
                                        <option value="Festival">Festival</option>
                                    </select>
                                </td>
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
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label>Precio ($):</label><input type="number" name="price" class="form-control" required></td>
                                <td><label>Stock (Unidades):</label><input type="number" name="qtyavail" class="form-control" required></td>
                            </tr>
                            <tr>
                                <td><label>Tiempo Entrega:</label><input type="text" name="delivery_est" class="form-control" placeholder="Ej: 3-5 días hábiles" required></td>
                                <td><label>Artista:</label>
                                    <select name="artist_id" class="form-control" required>
                                        <option value="">-- Selecciona el artista --</option>

                                        <?php
                                        while ($art = mysqli_fetch_assoc($artistsQ)) {
                                            $name = trim($art['afname']." ".$art['alname']);
                                            echo "<option value='{$art['aid']}'>
                                                    {$name} ({$art['username']})
                                                </option>";
                                        }
                                        ?>
                                    </select>
                                </td>

                            </tr>
                            <tr>
                                <td colspan="2"><label>Imágenes Extra (Selecciona varias):</label><input type="file" name="images[]" class="form-control" multiple></td>
                            </tr>
                            <tr>
                                <td colspan="2"><label>Descripción:</label><textarea name="description" class="form-control" style="height:50px"></textarea></td>
                            </tr>
                            <tr><td colspan="2"><button type="submit" name="add_product" class="btn-action" style="padding:10px; font-size:14px;">GUARDAR PRODUCTO</button></td></tr>
                        </table>
                    </form>
                </div>
            </div>

            <div class="create-box" style="padding: 10px;">
                <table class="tb">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="10%">Img</th>
                            <th width="20%">Evento</th>
                            <th width="15%">Artista (Dueño)</th>
                            <th width="15%">Detalles (Lugar/Fecha)</th>
                            <th width="10%">Venta</th>
                            <th width="10%">Reseñas</th>
                            <th width="10%">Galería</th>
                            <th width="5%">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // --- CONSULTA SIN FILTROS PARA EL INVENTARIO ---
                        // Usamos LEFT JOIN por si acaso un producto tuviera un artista borrado.
                        $prodQ = "SELECT p.*, a.aid, a.afname, a.alname, a.username 
                                  FROM products p 
                                  LEFT JOIN accounts a ON p.artist_id = a.aid 
                                  ORDER BY p.pid DESC";
                        
                        $prodRes = mysqli_query($con, $prodQ);
                        
                        if (mysqli_num_rows($prodRes) > 0) {
                            while ($p = mysqli_fetch_assoc($prodRes)) {
                                $pid = $p['pid'];
                                
                                // Lógica de Estrellas/Reseñas
                                $stars = 0; $reviewsCount = 0;
                                $checkRev = mysqli_query($con, "SHOW TABLES LIKE 'reviews'");
                                if(mysqli_num_rows($checkRev) > 0) {
                                    $avgQ = mysqli_query($con, "SELECT AVG(rating) as prom, COUNT(*) as total FROM reviews WHERE pid = $pid");
                                    $avgRow = mysqli_fetch_assoc($avgQ);
                                    $stars = round($avgRow['prom'], 1);
                                    $reviewsCount = $avgRow['total'];
                                }

                                // Imágenes
                                $imgsQ = mysqli_query($con, "SELECT * FROM product_images WHERE product_id = $pid");
                                $firstImgQ = mysqli_query($con, "SELECT image_path FROM product_images WHERE product_id = $pid LIMIT 1");
                                $firstImg = mysqli_fetch_assoc($firstImgQ);
                                $imgPath = $firstImg ? $firstImg['image_path'] : 'default.png';

                                echo "<form method='post' enctype='multipart/form-data'>";
                                echo "<tr>";
                                echo "<td>#$pid</td>";
                                
                                echo "<td><img src='product_images/$imgPath' width='50' style='border-radius:5px;'></td>";
                                
                                // Columna Evento
                                echo "<td>
                                        <input type='text' name='pname' value='{$p['pname']}' class='form-control' style='font-weight:bold; font-size:12px;'>
                                        <select name='category' class='form-control' style='font-size:11px;'>
                                            <option value='{$p['category']}'>{$p['category']}</option>
                                            <option value='Concierto'>Concierto</option>
                                            <option value='Festival'>Festival</option>
                                        </select>
                                        <textarea name='description' class='form-control' style='height:60px; font-size:11px;'>{$p['description']}</textarea>
                                      </td>";
                                
                                // Columna Artista
                                // Verificamos si existe el artista (por el LEFT JOIN)
                                $artName = $p['username'] ? "{$p['afname']} {$p['alname']}" : "<span style='color:red'>Sin Artista</span>";
                                $artUser = $p['username'] ? $p['username'] : "--";
                                
                                echo "<td style='text-align:left; font-size:11px;'>
                                        <b>ID:</b> #{$p['artist_id']}<br>
                                        <b>User:</b> $artUser<br>
                                        <b>Nom:</b> $artName
                                      </td>";

                                // Columna Detalles
                                $phpDate = date('Y-m-d\TH:i', strtotime($p['event_date']));
                                echo "<td>
                                        <small>Lugar:</small>
                                        <input type='text' name='venue' value='{$p['venue']}' class='form-control' style='font-size:11px;'>
                                        <small>Fecha:</small>
                                        <input type='datetime-local' name='event_date' value='$phpDate' class='form-control' style='font-size:11px;'>
                                      </td>";
                                
                                // Columna Venta
                                echo "<td>
                                        <small>$</small><input type='number' name='price' value='{$p['price']}' class='form-control' style='width:60px; display:inline;'>
                                        <small>Cant:</small><input type='number' name='qtyavail' value='{$p['qtyavail']}' class='form-control' style='width:50px; display:inline;'>
                                        <input type='text' name='delivery_est' value='{$p['delivery_est']}' class='form-control' style='font-size:10px; margin-top:2px;'>
                                      </td>";
                                
                                // Columna Reseñas
                                echo "<td>
                                        <div class='stars' style='font-size:10px;'>".str_repeat('★', floor($stars))."</div>
                                        <small>$stars ($reviewsCount)</small>
                                      </td>";
                            
                                // Columna Galería
                                echo "<td>";
                                echo "<input type='file' name='images[]' multiple class='form-control' style='font-size:9px;'>";
                                echo "<div style='max-height:60px; overflow-y:auto; margin-top:2px;'>";
                                while($img = mysqli_fetch_assoc($imgsQ)){
                                    echo "<a href='profile-admin.php?del_img={$img['image_id']}' style='color:red; font-size:10px;'>[x]</a> ";
                                }
                                echo "</div></td>";
                                
                                // Botones Acción
                                echo "<td>
                                        <input type='hidden' name='pid' value='$pid'>
                                        <button type='submit' name='update_product' title='Guardar Cambios' class='btn-action' style='padding:5px;'><i class='fas fa-save'></i></button>
                                        <button type='submit' name='delete_product' title='Eliminar' class='btn-action' style='background:#c0392b; margin-top:5px; padding:5px;'
                                        onclick='return confirm(\"¿Eliminar evento?\");'>
                                        <i class='fas fa-trash'></i> </button>
                                      </td>";
                                echo "</tr></form>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No hay eventos registrados en la base de datos.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        <?php } elseif ($view == 'users') { ?>
             <h2>Mis Datos (Admin)</h2>
             <div class="create-box">
                <form method="post">
                    <table class="tb">
                        <tr><td>Nombre:</td><td><input type="text" name="a1" value="<?php echo $userRow['afname']; ?>" class="form-control"></td></tr>
                        <tr><td>Apellido:</td><td><input type="text" name="a2" value="<?php echo $userRow['alname']; ?>" class="form-control"></td></tr>
                        <tr><td>Email:</td><td><input type="text" name="a3" value="<?php echo $userRow['email']; ?>" class="form-control"></td></tr>
                        <tr><td>Teléfono:</td><td><input type="text" name="a4" value="<?php echo $userRow['phone']; ?>" class="form-control"></td></tr>
                        <tr><td>Fecha Nac.:</td><td><input type="date" name="a5" value="<?php echo $userRow['dob']; ?>" class="form-control"></td></tr>
                        <tr><td colspan="2"><button name="submit_profile" class="btn-action">Guardar Cambios</button></td></tr>
                    </table>
                </form>
             </div>
        <?php } elseif ($view == 'accounts') { ?>

        <h2>Gestión de Usuarios</h2>

        <div class="create-box">
            <table class="tb">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="20%">Nombre</th>
                        <th width="10%">Usuario</th>
                        <th width="15%">Email</th>
                        <th width="15%">Rol</th>
                        <th width="15%">Fecha Registro</th>
                        <th width="10%">Acción</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                $usersQ = mysqli_query($con, "SELECT * FROM accounts ORDER BY created_at DESC");

                while ($u = mysqli_fetch_assoc($usersQ)) {
                ?>
                    <form method="post">
                    <tr>
                        <td>#<?= $u['aid'] ?></td>

                        <td>
                            <input type="text" name="afname" value="<?= $u['afname'] ?>" class="form-control">
                            <input type="text" name="alname" value="<?= $u['alname'] ?>" class="form-control">
                        </td>

                        <td><?= $u['username'] ?></td>

                        <td>
                            <input type="email" name="email" value="<?= $u['email'] ?>" class="form-control">
                        </td>

                        <td>
                            <select name="role" class="form-control">
                                <option value="<?= $u['role'] ?>">Actual: <?= $u['role'] ?></option>
                                <option value="admin">admin</option>
                                <option value="cliente">cliente</option>
                                <option value="artista">artista</option>
                            </select>
                        </td>

                        <td><?= $u['created_at'] ?></td>

                        <td>
                            <input type="hidden" name="aid" value="<?= $u['aid'] ?>">
                            <button name="update_user" class="btn-action">Actualizar</button>

                            <?php if ($u['aid'] != $aid) { ?>
                                <button 
                                    name="delete_user" 
                                    class="btn-action" 
                                    style="background:#e74c3c; margin-top:5px;"
                                    onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">
                                    Eliminar
                                </button>
                            <?php } else { ?>
                                <small style="color:gray;">No puedes eliminarte</small>
                            <?php } ?>
                        </td>
                    </tr>
                    </form>
                <?php } ?>

                </tbody>
            </table>
        </div>

        <?php } ?>

    </div>
</body>
</html>
