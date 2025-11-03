<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Bracelet Store</title>
    
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<header>
    <div class="contenedor-header">
        <a href="index.php">
            <img src="img/logo.jpg" alt="Store Logo" class="logo">
        </a>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="catalogo.php">Catalog</a></li>
                <li><a href="contacto.php">Contact</a></li>
                <?php
                if (isset($_SESSION['usuario_email'])):
                    $admin_email = 'amayabryan579@gmail.com'; 
                    if ($_SESSION['usuario_email'] === $admin_email):
                ?>
                        <li><a href="admin/">Admin Panel</a></li>
                <?php
                    endif;
                ?>
                    <li><a href="logout.php">Log Out</a></li>
                <?php else: ?>
                    <li><a href="login.php">Log In</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<main>