<?php
session_start();
include("include/connect.php");

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
    .events-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
        padding: 80px 20px;
        margin-bottom: 40px;
    }
    
    .events-header h2 {
        font-size: 48px;
        margin-bottom: 20px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .events-header p {
        font-size: 18px;
        max-width: 800px;
        margin: 0 auto;
        line-height: 1.6;
    }
    
    .events-container {
        max-width: 1200px;
        margin: 0 auto 60px;
        padding: 0 20px;
    }
    
    .events-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 30px;
        margin-bottom: 50px;
    }
    
    .event-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border: 1px solid #eaeaea;
        position: relative;
    }
    
    .event-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .event-image {
        width: 100%;
        height: 250px;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .event-card:hover .event-image {
        transform: scale(1.05);
    }
    
    .event-content {
        padding: 25px;
    }
    
    .event-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        display: inline-block;
        z-index: 2;
        letter-spacing: 0.5px;
    }
    
    .festival-badge {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .concert-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .event-artist {
        color: #667eea;
        font-size: 14px;
        font-weight: 600;
        display: block;
        margin-bottom: 5px;
    }
    
    .event-title {
        color: #333;
        font-size: 20px;
        margin-bottom: 15px;
        line-height: 1.4;
        min-height: 60px;
    }
    
    .event-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin: 15px 0;
    }
    
    .info-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #666;
        font-size: 14px;
    }
    
    .info-item i {
        width: 20px;
        text-align: center;
    }
    
    .info-item .concert-icon {
        color: #667eea;
    }
    
    .info-item .festival-icon {
        color: #f5576c;
    }
    
    .event-price {
        font-size: 24px;
        font-weight: 700;
        color: #333;
        margin: 15px 0;
    }
    
    .concert-price {
        color: #667eea;
    }
    
    .festival-price {
        color: #f5576c;
    }
    
    .btn-event {
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s;
        text-align: center;
        width: 100%;
        box-sizing: border-box;
    }
    
    .btn-concert {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .btn-festival {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .btn-event:hover {
        transform: scale(1.05);
        color: white;
    }
    
    .btn-concert:hover {
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-festival:hover {
        box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
    }
    
    .no-events {
        text-align: center;
        padding: 60px 20px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 15px;
        margin: 20px 0;
        grid-column: 1 / -1;
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
    
    .event-description {
        color: #555;
        line-height: 1.5;
        margin: 15px 0;
        font-size: 14px;
        max-height: 100px;
        overflow: hidden;
        position: relative;
    }
    
    .event-description::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: 0;
        width: 100%;
        height: 30px;
        background: linear-gradient(to bottom, rgba(255,255,255,0), white);
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
    
    .about-content {
        background: white;
        border-radius: 15px;
        padding: 40px;
        margin-bottom: 50px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .about-content h2 {
        color: #333;
        margin-bottom: 20px;
        font-size: 32px;
        border-bottom: 3px solid #667eea;
        padding-bottom: 10px;
        display: inline-block;
    }
    
    .about-text {
        color: #555;
        line-height: 1.8;
        font-size: 16px;
        margin-bottom: 25px;
    }
    
    .events-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 40px;
    }
    
    .event-feature {
        background: linear-gradient(135deg, #fdfcfb 0%, #e2d1c3 100%);
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        transition: transform 0.3s;
    }
    
    .event-feature:hover {
        transform: translateY(-5px);
    }
    
    .event-feature i {
        font-size: 36px;
        color: #667eea;
        margin-bottom: 15px;
    }
    
    .event-feature h4 {
        color: #333;
        margin-bottom: 10px;
    }
    
    .event-feature p {
        color: #666;
        font-size: 14px;
    }
    
    .event-filters {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }
    
    .filter-btn {
        padding: 10px 25px;
        border: 2px solid #667eea;
        border-radius: 25px;
        background: white;
        color: #667eea;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .filter-btn:hover {
        background: #667eea;
        color: white;
    }
    
    .filter-btn.active {
        background: #667eea;
        color: white;
    }
    
    .filter-btn.festival {
        border-color: #f5576c;
        color: #f5576c;
    }
    
    .filter-btn.festival:hover,
    .filter-btn.festival.active {
        background: #f5576c;
        color: white;
    }
    
    .category-title {
        color: #333;
        margin: 40px 0 20px;
        font-size: 28px;
        text-align: center;
        padding-bottom: 10px;
        border-bottom: 2px solid #eaeaea;
    }
    
    .festival-title {
        color: #f5576c;
        border-bottom-color: #f5576c;
    }
    
    .concert-title {
        color: #667eea;
        border-bottom-color: #667eea;
    }
    </style>
</head>

<body>
    <section id="header">
        <a href="index.php"><img src="img/logo.png" class="logo" alt="Eventify" /></a>

        <div>
            <ul id="navbar">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="shop.php">Eventos</a></li>
                <li><a class="active" href="about.php">Acerca de nosotros</a></li>

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
        <div id="mobile">
            <a href="cart.php"><i class="far fa-shopping-bag"></i></a>
            <i id="bar" class="fas fa-outdent"></i>
        </div>
    </section>


    <div class="about-content">
        <h2>Sobre Nuestros Eventos</h2>
        <p class="about-text">
            En Eventify nos especializamos en ofrecerte los mejores eventos musicales, desde íntimos conciertos 
            hasta grandiosos festivales multitudinarios. Nuestra misión es conectar a los amantes de la música 
            con experiencias únicas que quedarán grabadas en su memoria.
        </p>
        <p class="about-text">
            Trabajamos directamente con promotores, artistas y salas de concierto para traerte eventos de calidad 
            garantizada. Ya sea que prefieras la energía de un festival al aire libre o la acústica perfecta de 
            un concierto en sala, tenemos opciones para todos los gustos y presupuestos.
        </p>
        
        <div class="events-features">
            <div class="event-feature">
                <i class="fas fa-music"></i>
                <h4>Artistas de Calidad</h4>
                <p>Los mejores artistas nacionales e internacionales</p>
            </div>
            <div class="event-feature">
                <i class="fas fa-ticket-alt"></i>
                <h4>Entradas Garantizadas</h4>
                <p>Boletos 100% auténticos con validez asegurada</p>
            </div>
            <div class="event-feature">
                <i class="fas fa-shield-alt"></i>
                <h4>Compra Segura</h4>
                <p>Proceso de compra protegido y confiable</p>
            </div>
            <div class="event-feature">
                <i class="fas fa-headphones-alt"></i>
                <h4>Experiencias Únicas</h4>
                <p>Eventos diseñados para momentos memorables</p>
            </div>
        </div>
    </div>

    <div class="events-container">
        <h2 style="color: #333; margin-bottom: 20px; font-size: 28px; text-align: center;">
            <i class="fas fa-calendar-alt" style="color: #667eea;"></i> Eventos Disponibles
        </h2>
        
        <div class="event-filters">
            <button class="filter-btn active" data-filter="all">
                <i class="fas fa-list"></i> Todos los Eventos
            </button>
            <button class="filter-btn concert" data-filter="concierto">
                <i class="fas fa-guitar"></i> Conciertos
            </button>
            <button class="filter-btn festival" data-filter="festival">
                <i class="fas fa-music"></i> Festivales
            </button>
        </div>
        
        <?php
        include("include/connect.php");
        
        // Obtener conciertos y festivales con boletos disponibles
        $query = "SELECT * FROM products 
                  WHERE event_type IN ('concierto', 'festival') 
                  AND qtyavail > 0
                  AND (DATE(event_date) >= CURDATE() OR event_date IS NULL)
                  ORDER BY event_type, event_date ASC";
        $result = mysqli_query($con, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            // Separar conciertos y festivales
            $concerts = [];
            $festivals = [];
            
            while ($row = mysqli_fetch_assoc($result)) {
                if ($row['event_type'] == 'concierto') {
                    $concerts[] = $row;
                } else {
                    $festivals[] = $row;
                }
            }
            
            // Mostrar festivales
            if (!empty($festivals)) {
                echo "<h3 class='category-title festival-title'><i class='fas fa-music'></i> Festivales</h3>";
                echo "<div class='events-grid' data-type='festival'>";
                
                foreach ($festivals as $row) {
                    displayEventCard($row);
                }
                
                echo "</div>";
            }
            
            // Mostrar conciertos
            if (!empty($concerts)) {
                echo "<h3 class='category-title concert-title'><i class='fas fa-guitar'></i> Conciertos</h3>";
                echo "<div class='events-grid' data-type='concierto'>";
                
                foreach ($concerts as $row) {
                    displayEventCard($row);
                }
                
                echo "</div>";
            }
            
        } else {
            echo "
            <div class='no-events'>
                <i class='fas fa-calendar-times'></i>
                <h3>No hay eventos disponibles en este momento</h3>
                <p>Estamos trabajando en traerte los mejores eventos musicales</p>
                <a href='shop.php' class='filter-btn' style='width: auto; display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none;'>
                    <i class='fas fa-search'></i> Buscar Eventos
                </a>
            </div>";
        }
        
        mysqli_close($con);
        
        // Función para mostrar tarjetas de eventos
        function displayEventCard($row) {
            $pid = $row['pid'];
            $pname = htmlspecialchars($row['pname']);
            if (strlen($pname) > 40) {
                $pname = substr($pname, 0, 40) . '...';
            }
            $desc = htmlspecialchars($row['description']);
            if (strlen($desc) > 150) {
                $desc = substr($desc, 0, 150) . '...';
            }
            $qty = $row['qtyavail'];
            $price = number_format($row['price'], 0, '.', ',');
            $img = $row['img'];
            $artist = htmlspecialchars($row['artist'] ?? 'Artista por confirmar');
            $event_date = isset($row['event_date']) ? date('d/m/Y H:i', strtotime($row['event_date'])) : 'Por definir';
            $venue = htmlspecialchars($row['venue'] ?? 'Lugar por definir');
            $event_type = $row['event_type'] ?? 'concierto';
            
            // Determinar colores según tipo
            $is_festival = ($event_type == 'festival');
            $badge_class = $is_festival ? 'event-badge festival-badge' : 'event-badge concert-badge';
            $price_class = $is_festival ? 'festival-price' : 'concert-price';
            $btn_class = $is_festival ? 'btn-event btn-festival' : 'btn-event btn-concert';
            $icon_class = $is_festival ? 'festival-icon' : 'concert-icon';
            
            // Clase para boletos bajos
            $ticket_class = ($qty < 10) ? 'tickets-low' : '';
            
            echo "
            <div class='event-card' data-pid='$pid' data-type='" . strtolower($event_type) . "'>
                <div class='$badge_class'>$event_type</div>
                <img src='product_images/$img' alt='$pname' class='event-image' />
                <div class='event-content'>
                    <span class='event-artist'>$artist</span>
                    <h5 class='event-title'>$pname</h5>
                    
                    <div class='event-info'>
                        <div class='info-item'>
                            <i class='far fa-calendar $icon_class'></i>
                            <span>$event_date</span>
                        </div>
                        <div class='info-item'>
                            <i class='far fa-map-marker-alt $icon_class'></i>
                            <span>$venue</span>
                        </div>
                    </div>
                    
                    <div class='event-description'>$desc</div>
                    
                    <div class='tickets-available $ticket_class'>
                        <i class='fas fa-ticket-alt $icon_class'></i> " . ($qty > 0 ? "$qty boletos disponibles" : "Agotado") . "
                    </div>
                    
                    <div class='event-price $price_class'>$$price <span style='font-size: 12px; color: #888;'>por boleto</span></div>
                    
                    <a href='sproduct.php?pid=$pid' class='$btn_class'>
                        <i class='fas fa-info-circle'></i> Ver Detalles y Comprar
                    </a>
                </div>
            </div>";
        }
        ?>
    </div>

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
            <a href="about.php">Eventos y Conciertos</a>
        </div>
        
        <div class="col">
            <h4>Eventify</h4>
            <a href="index.php">Inicio</a>
            <a href="shop.php">Eventos</a>
            <a href="about.php">Eventos y Conciertos</a>
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
</body>

</html>

<script>

// Filtros de eventos
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const eventCards = document.querySelectorAll('.event-card');
    const eventGrids = document.querySelectorAll('.events-grid');
    
    // Manejar clic en filtros
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover clase active de todos los botones
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Agregar clase active al botón clickeado
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            
            // Mostrar/ocultar eventos según el filtro
            eventCards.forEach(card => {
                const type = card.getAttribute('data-type');
                
                if (filter === 'all' || filter === type) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Mostrar/ocultar secciones según el filtro
            eventGrids.forEach(grid => {
                const gridType = grid.getAttribute('data-type');
                const sectionTitle = grid.previousElementSibling;
                
                if (filter === 'all') {
                    grid.style.display = 'grid';
                    if (sectionTitle && sectionTitle.classList.contains('category-title')) {
                        sectionTitle.style.display = 'block';
                    }
                } else if (filter === gridType) {
                    grid.style.display = 'grid';
                    if (sectionTitle && sectionTitle.classList.contains('category-title')) {
                        sectionTitle.style.display = 'block';
                    }
                } else {
                    grid.style.display = 'none';
                    if (sectionTitle && sectionTitle.classList.contains('category-title')) {
                        sectionTitle.style.display = 'none';
                    }
                }
            });
        });
    });
    
    // Efecto hover para tarjetas de eventos
    eventCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
            this.style.boxShadow = '0 20px 40px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)';
        });
        
        // Navegar a detalles al hacer clic (excepto en botones)
        card.addEventListener('click', function(e) {
            if (!e.target.closest('a') && !e.target.closest('button')) {
                const pid = this.getAttribute('data-pid');
                if (pid) {
                    window.location.href = 'sproduct.php?pid=' + pid;
                }
            }
        });
    });
});
</script>