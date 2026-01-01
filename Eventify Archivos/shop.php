<?php
session_start();
include("include/connect.php");

if (empty($_SESSION['aid']))
    $_SESSION['aid'] = -1;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Eventify</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link rel="stylesheet" href="style.css" />

    <style>
    .search-container {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        background: #e3e6f3;
        padding: 15px 20px;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
        border-radius: 10px;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: nowrap;
    }

    #artist-filter {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        min-width: 200px;
        background-color: white;
        font-size: 14px;
    }

    #event-type-filter {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: white;
        font-size: 14px;
    }
    
    #date-filter {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: white;
        font-size: 14px;
        min-width: 150px;
    }

    #search {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        width: 300px;
        font-size: 14px;
    }

    #search-btn {
        outline: none;
        border: none;
        padding: 12px 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    #search-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .event-date {
        color: #667eea;
        font-size: 13px;
        margin: 5px 0;
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 500;
    }
    
    .event-location {
        color: #666;
        font-size: 12px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .event-type {
        display: inline-block;
        color: white;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        margin-bottom: 5px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .event-type-concierto {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .event-type-festival {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .alert-message {
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 25px;
        border-radius: 10px;
        z-index: 1000;
        display: none;
        animation: slideIn 0.3s ease-out;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        max-width: 350px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alert-message i {
        font-size: 20px;
        color: #fff;
    }
    
    .alert-message.hide {
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
    
    .tickets-available {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .tickets-low {
        color: #f5576c;
        font-weight: bold;
    }
    
    .event-card {
        transition: all 0.3s ease;
        border-radius: 15px;
        overflow: hidden;
        border: 1px solid #eaeaea;
        background: white;
        position: relative;
    }
    
    .event-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    
    .pro {
        position: relative;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .event-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        color: white;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
        z-index: 2;
    }
    
    .festival-badge {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .concierto-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .search-container label {
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }
    
    .no-events {
        text-align: center;
        padding: 60px 20px;
        grid-column: 1 / -1;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 15px;
        margin: 20px 0;
    }
    
    .no-events i {
        font-size: 64px;
        color: #667eea;
        margin-bottom: 20px;
        opacity: 0.7;
    }
    
    .no-events h3 {
        color: #333;
        margin-bottom: 10px;
        font-size: 24px;
    }
    
    .no-events p {
        color: #666;
        font-size: 16px;
        margin-bottom: 30px;
    }
    
    .pro img {
        height: 250px;
        width: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .pro:hover img {
        transform: scale(1.05);
    }
    
    .des {
        padding: 20px;
    }
    
    .des span {
        color: #667eea;
        font-size: 14px;
        font-weight: 600;
        display: block;
        margin-bottom: 5px;
    }
    
    .des h5 {
        color: #333;
        font-size: 18px;
        margin-bottom: 10px;
        line-height: 1.4;
        min-height: 50px;
    }
    
    .star {
        margin: 10px 0;
    }
    
    .star i {
        font-size: 14px;
        color: #ffd700;
    }
    
    .des h4 {
        color: #f5576c;
        font-size: 20px;
        font-weight: 700;
        margin-top: 10px;
    }
    
    .cart {
        position: absolute;
        bottom: 20px;
        right: 20px;
        background: white;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
        font-size: 18px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        z-index: 2;
    }
    
    .cart:hover {
        background: #667eea;
        color: white;
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }
    
    .search-container label {
        white-space: nowrap;
    }
    
    .filter-group select {
        cursor: pointer;
    }
    
    .filter-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    #search:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    /* Notificación de carrito */
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
    
    /* Estilos para Admin en navbar */
    .admin-link {
        display: none;
    }
    </style>
</head>

<body>
<section id="header">
    <a href="index.php"><img src="img/logo.png" class="logo" alt="Eventify" /></a>

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

            <li id="lg-cart">
                <a href="cart.php"><i class="far fa-shopping-cart"></i></a>
            </li>
            <a href="#" id="close"><i class="far fa-times"></i></a>
        </ul>
    </div>
</section>


    <div class="search-container">
        <form id="search-form" method="post" class="filters-form">
            <div class="filter-group">
                <label for="search">Buscar:</label>
                <input type="text" id="search" name="search" 
                       placeholder="Nombre del evento, artista, lugar..." 
                       value="<?php echo isset($_POST['search']) ? htmlspecialchars($_POST['search']) : ''; ?>">
            </div>
            
            <div class="filter-group">
                <label for="artist-filter">Artista:</label>
                <select id="artist-filter" name="artist">
                    <option value="all">Todos los artistas</option>
                    <?php
                    include("include/connect.php");
                    // Obtener artistas únicos activos de la base de datos
                    $artistas_query = "SELECT DISTINCT a.aid, CONCAT(a.afname, ' ', a.alname) AS artist_name
                        FROM products p
                        INNER JOIN accounts a ON p.artist_id = a.aid
                        WHERE a.role = 'artista'
                        AND p.qtyavail > 0
                        AND p.event_type IN ('concierto','festival')
                        ORDER BY artist_name
                    ";
                    $artistas_result = mysqli_query($con, $artistas_query);
                    
                    if ($artistas_result && mysqli_num_rows($artistas_result) > 0) {
                        while ($artista = mysqli_fetch_assoc($artistas_result)) {
                        $id = $artista['aid'];
                        $nombre = htmlspecialchars($artista['artist_name']);
                        $selected = (isset($_POST['artist']) && $_POST['artist'] == $id) ? 'selected' : '';
                        echo "<option value='$id' $selected>$nombre</option>";
                        }

                    } else {
                        echo "<option value=''>No hay artistas disponibles</option>";
                    }
                    mysqli_close($con);
                    ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="event-type-filter">Tipo:</label>
                <select id="event-type-filter" name="event_type">
                    <option value="all">Todos los tipos</option>
                    <?php
                    $tipos = array('concierto', 'festival');
                    foreach ($tipos as $tipo) {
                        $selected = (isset($_POST['event_type']) && $_POST['event_type'] == $tipo) ? 'selected' : '';
                        echo "<option value='$tipo' $selected>$tipo</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="date-filter"> Fecha:</label>
                <select id="date-filter" name="date_filter">
                    <option value="all">Todas las fechas</option>
                    <option value="today" <?php echo (isset($_POST['date_filter']) && $_POST['date_filter'] == 'today') ? 'selected' : ''; ?>>Hoy</option>
                    <option value="week" <?php echo (isset($_POST['date_filter']) && $_POST['date_filter'] == 'week') ? 'selected' : ''; ?>>Esta semana</option>
                    <option value="month" <?php echo (isset($_POST['date_filter']) && $_POST['date_filter'] == 'month') ? 'selected' : ''; ?>>Este mes</option>
                    <option value="future" <?php echo (isset($_POST['date_filter']) && $_POST['date_filter'] == 'future') ? 'selected' : ''; ?>>Próximos</option>
                </select>
            </div>
            
            <button type="submit" id="search-btn" name="search1">
                <i class="fas fa-search"></i> Buscar Eventos
            </button>
        </form>
    </div>

    <?php
    include("include/connect.php");
    
    // Construir la consulta basada en los filtros
    $where_conditions = array();
    $hayFiltros =
    !empty($_POST['search']) ||
    ($_POST['artist'] ?? 'all') !== 'all' ||
    ($_POST['event_type'] ?? 'all') !== 'all' ||
    ($_POST['date_filter'] ?? 'all') !== 'all';


    if ($hayFiltros) {
        $search = mysqli_real_escape_string($con, $_POST['search']);
        $artist = mysqli_real_escape_string($con, $_POST['artist']);
        $event_type = mysqli_real_escape_string($con, $_POST['event_type']);
        $date_filter = mysqli_real_escape_string($con, $_POST['date_filter']);
        
        // Solo eventos con boletos disponibles
        $where_conditions[] = "qtyavail > 0";
        
        // Solo conciertos y festivales
        $where_conditions[] = "event_type IN ('concierto', 'festival')";
        
        // Filtro de búsqueda general
        if (!empty($search)) {
            $where_conditions[] = "(p.pname LIKE '%$search%' OR 
                                   p.venue LIKE '%$search%' OR 
                                   p.description LIKE '%$search%' OR 
                                   description LIKE '%$search%' OR
                                   CONCAT(a.afname,' ',a.alname) LIKE '%$search%'
                                   )";
        }
        
        // Filtro por artista
        if ($artist != "all" && is_numeric($artist)) {
            $where_conditions[] = "artist_id = $artist";
        }

        
        // Filtro por tipo de evento
        if ($event_type != "all") {
            $where_conditions[] = "event_type = '$event_type'";
        }
        
        // Filtro por fecha
        if ($date_filter != "all") {
            $today = date('Y-m-d');
            switch ($date_filter) {
                case 'today':
                    $where_conditions[] = "DATE(event_date) = '$today'";
                    break;
                case 'week':
                    $week_start = date('Y-m-d', strtotime('monday this week'));
                    $week_end = date('Y-m-d', strtotime('sunday this week'));
                    $where_conditions[] = "DATE(event_date) BETWEEN '$week_start' AND '$week_end'";
                    break;
                case 'month':
                    $month_start = date('Y-m-01');
                    $month_end = date('Y-m-t');
                    $where_conditions[] = "DATE(event_date) BETWEEN '$month_start' AND '$month_end'";
                    break;
                case 'future':
                    $where_conditions[] = "DATE(event_date) >= '$today'";
                    break;
            }
        }
        
        $where_clause = "";
        if (!empty($where_conditions)) {
            $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        }
        
        $query = "SELECT p.*, CONCAT(a.afname,' ',a.alname) AS artist_name FROM products p LEFT JOIN accounts a ON p.artist_id = a.aid $where_clause ORDER BY p.event_date ASC";
        $result = mysqli_query($con, $query);
        
     } else {
            // Consulta por defecto: SOLO eventos vigentes
            $today = date('Y-m-d');

            $query = "SELECT p.*, CONCAT(a.afname,' ',a.alname) AS artist_name
                    FROM products p
                    LEFT JOIN accounts a ON p.artist_id = a.aid
                    WHERE p.event_type IN ('concierto','festival')
                    AND (DATE(p.event_date) >= '$today' OR p.event_date IS NULL)
                    ORDER BY p.event_date ASC";

            $result = mysqli_query($con, $query);
        }


    if ($result && mysqli_num_rows($result) > 0) {
        echo "<section id='product1' class='section-p1'>
                <div class='pro-container'>";

        while ($row = mysqli_fetch_assoc($result)) {
            $pid = $row['pid'];
            $pname = $row['pname'];
            if (strlen($pname) > 35) {
                $pname = substr($pname, 0, 35) . '...';
            }
            $desc = $row['description'];
            $qty = $row['qtyavail'];
            $price = $row['price'];
            $cat = $row['category'];
            $img = $row['img'];
            $brand = $row['brand'];
            $artist = !empty($row['artist_name']) ? $row['artist_name'] : 'Artista por confirmar';
            $event_date = isset($row['event_date']) ? date('d/m/Y H:i', strtotime($row['event_date'])) : 'Por definir';
            $venue = isset($row['venue']) ? $row['venue'] : 'Lugar por definir';
            $event_type = isset($row['event_type']) ? $row['event_type'] : 'concierto';
            
            // Formatear precio
            $formatted_price = number_format($price, 0, '.', ',');
            
            // Determinar clase para el tipo de evento
            $event_class = strtolower($event_type);
            $badge_class = ($event_type == 'Festival') ? 'event-badge festival-badge' : 'event-badge concierto-badge';
            
            // Clase para boletos bajos
            $ticket_class = ($qty < 10) ? 'tickets-low' : '';
            
            // Obtener calificación promedio
            $query2 = "SELECT pid, AVG(rating) AS average_rating FROM reviews WHERE pid = $pid GROUP BY pid";
            $result2 = mysqli_query($con, $query2);
            $row2 = mysqli_fetch_assoc($result2);

            if ($row2) {
                $stars = $row2['average_rating'];
            } else {
                $stars = 0;
            }
            $stars = round($stars, 0);
            $empty = 5 - $stars;

            // Escapar el nombre del evento para JavaScript
            $js_event_name = htmlspecialchars($pname, ENT_QUOTES, 'UTF-8');
            
            echo "
                <div class='pro event-card' data-pid='$pid'>
                    <div class='$badge_class'>$event_type</div>
                    <img src='product_images/$img' alt='$pname' />
                    <div class='des'>
                        <span>$artist</span>
                        <h5>$pname</h5>
                        <div class='event-date'>
                            <i class='far fa-calendar'></i> $event_date
                        </div>
                        <div class='event-location'>
                            <i class='far fa-map-marker-alt'></i> $venue
                        </div>
                        <div class='star'>";
            
            for ($i = 1; $i <= $stars; $i++) {
                echo "<i class='fas fa-star'></i>";
            }
            for ($i = 1; $i <= $empty; $i++) {
                echo "<i class='far fa-star'></i>";
            }
            
            echo "  </div>
                        <div class='tickets-available $ticket_class'>
                            <i class='fas fa-ticket-alt'></i> " . ($qty > 0 ? "$qty boletos disponibles" : "Agotado") . "
                        </div>
                        <h4>$$formatted_price <span style='font-size: 12px; color: #888;'>por boleto</span></h4>
                    </div>

                    </a>
                </div>";
        }

        echo "</div></section>";
        
    } else {
        echo "<section id='product1' class='section-p1'>
                <div class='pro-container'>
                    <div class='no-events'>
                        <i class='far fa-calendar-times'></i>
                        <h3>No se encontraron eventos</h3>
                        <p>Intenta con otros términos de búsqueda o ajusta los filtros</p>
                        <a href='shop.php' class='btn-view-all' style='display: inline-block; padding: 10px 25px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; text-decoration: none; margin-top: 15px;'>
                            Ver todos los eventos
                        </a>
                    </div>
                </div>
              </section>";
    }
    
    mysqli_close($con);
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

    <script src="script.js"></script>
    <script>
    // Función para navegar a la página del evento
    function topage(pid) {
        window.location.href = 'sproduct.php?pid=' + pid;
    }
    
    // Función para mostrar notificación de carrito
    function showCartNotification(eventName) {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = 'cart-notification';
        notification.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>¡Evento agregado!</strong>
                <p>${eventName} se agregó a tu carrito.</p>
                <small><a href="cart.php" style="color: #fff; text-decoration: underline;">Ver carrito</a></small>
            </div>
        `;
        
        // Agregar al cuerpo
        document.body.appendChild(notification);
        
        // Remover después de 5 segundos
        setTimeout(() => {
            notification.classList.add('hide');
            setTimeout(() => {
                if (notification.parentNode) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
    
    // Función para actualizar contador del carrito
    function updateCartCount() {
        fetch('get_cart_count.php')
            .then(response => response.text())
            .then(count => {
                let cartCount = document.querySelector('.cart-count');
                if (!cartCount) {
                    cartCount = createCartCountElement();
                }
                cartCount.textContent = count;
                
                // Mostrar/ocultar basado en el conteo
                if (parseInt(count) > 0) {
                    cartCount.style.display = 'flex';
                } else {
                    cartCount.style.display = 'none';
                }
            })
            .catch(error => console.error('Error al obtener contador del carrito:', error));
    }
    
    // Función para crear elemento de contador si no existe
    function createCartCountElement() {
        const cartIcon = document.querySelector('#lg-bag a');
        if (!cartIcon) return null;
        
        const cartCount = document.createElement('span');
        cartCount.className = 'cart-count';
        cartCount.style.cssText = `
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
        `;
        cartIcon.style.position = 'relative';
        cartIcon.appendChild(cartCount);
        
        return cartCount;
    }
    
    // Función para agregar al carrito
    function addToCart(pid, eventName) {
        // Mostrar notificación inmediatamente
        showCartNotification(eventName);
        
        // Agregar al carrito mediante AJAX
        fetch('addtocart.php?pid=' + pid + '&qty=1')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar contador del carrito
                    updateCartCount();
                } else {
                    // Mostrar error
                    alert(data.message);
                    // Si es error de autenticación, redirigir a login
                    if (data.message.includes('sesión')) {
                        window.location.href = 'login.php?redirect=sproduct.php?pid=' + pid;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Redirigir a la página de login si no está autenticado
                window.location.href = 'login.php?redirect=sproduct.php?pid=' + pid;
            });
    }
    
    // Manejar clic en botón de carrito
    document.addEventListener('click', function(e) {
        if (e.target.closest('.cart-btn')) {
            e.preventDefault();
            e.stopPropagation();
            
            const cartBtn = e.target.closest('.cart-btn');
            const pid = cartBtn.getAttribute('data-pid');
            const eventName = cartBtn.getAttribute('data-event-name');
            
            addToCart(pid, eventName);
        }
    });
    
    // Manejar clic en la tarjeta del evento (navegar a detalles)
    document.addEventListener('click', function(e) {
        const eventCard = e.target.closest('.pro.event-card');
        if (eventCard && !e.target.closest('.cart-btn')) {
            const pid = eventCard.getAttribute('data-pid');
            if (pid) {
                window.location.href = 'sproduct.php?pid=' + pid;
            }
        }
    });
    
    // Manejar formulario de búsqueda
    document.getElementById('search-form').addEventListener('submit', function(e) {
        const searchInput = document.getElementById('search');
        const artistSelect = document.getElementById('artist-filter');
        const typeSelect = document.getElementById('event-type-filter');
        const dateSelect = document.getElementById('date-filter');
        
        // Si todos los filtros están vacíos o en "all", mostrar todos los eventos
        if (searchInput.value.trim() === '' && 
            artistSelect.value === 'all' && 
            typeSelect.value === 'all' &&
            dateSelect.value === 'all') {
            // No hacer nada, el formulario se enviará normalmente
        }
    });
    
    // Actualizar contador del carrito al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        updateCartCount();
        
        // Añadir efecto hover a las tarjetas de evento
        document.querySelectorAll('.event-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
        
        // Mostrar alerta si viene de agregar al carrito
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('added')) {
            const eventName = urlParams.get('event_name') || 'Evento';
            showCartNotification(eventName);
        }
    });
    </script>
</body>

</html>