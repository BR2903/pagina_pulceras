<?php
include 'templates/header.php';
?>

<section class="bienvenida">
    <h1>Welcome to the Bracelet Store</h1>
    <p>Discover our exclusive collection of handmade bracelets.</p>
</section>

<div class="container">

    <section class="productos-destacados" style="background: none; padding-top: 20px;">
        
        <h2 style="text-align: center; color: #8d2146; margin-bottom: 30px;">
            Our Newest Products
        </h2>
        
        <div class="lista-productos">
            <?php
            // (CAMBIO) Movemos la conexión aquí para que sirva para AMBAS consultas
            include_once 'conection/db.php';

            // Consulta para los 8 productos más recientes
            $sql_newest = "SELECT id, nombre, descripcion, precio, imagen FROM productos ORDER BY id DESC LIMIT 8";
            $result_newest = $conn->query($sql_newest);

            if ($result_newest && $result_newest->num_rows > 0) {
                while($row = $result_newest->fetch_assoc()) {
                    
                    $descripcion_corta = mb_strimwidth(htmlspecialchars($row["descripcion"]), 0, 50, "...");

                    echo '<a href="productos.php?id=' . htmlspecialchars($row["id"]) . '" style="text-decoration: none;">';
                    echo '<div class="producto">';
                    
                    if (!empty($row["imagen"]) && file_exists("img/" . htmlspecialchars($row["imagen"]))) {
                        echo '<img src="img/' . htmlspecialchars($row["imagen"]) . '" alt="' . htmlspecialchars($row["nombre"]) . '">';
                    } else {
                        echo '<img src="img/placeholder.jpg" alt="Image not available">';
                    }
                    
                    echo '<h3>' . htmlspecialchars($row["nombre"]) . '</h3>';
                    echo '<p>' . $descripcion_corta . '</p>';
                    echo '<p class="precio">$' . htmlspecialchars(number_format($row["precio"], 2)) . '</p>';
                    echo '</div>';
                    echo '</a>';
                }
            } else {
                echo "<p style='text-align: center;'>No featured products at this time.</p>";
            }

            // (CAMBIO) NO cerramos la conexión aquí ($conn->close();)
            ?>
        </div>
    </section>

    <section class="productos-destacados" style="background: none; padding-top: 40px; border-top: 1px solid #eee; margin-top: 40px;">
        
        <h2 style="text-align: center; color: #8d2146; margin-bottom: 30px;">
            Our Best Sellers
        </h2>
        
        <div class="lista-productos">
            <?php
            // La conexión $conn sigue abierta desde la consulta anterior

            // Esta consulta suma las cantidades de la tabla 'detalle_pedidos'
            // y las une con 'productos' para obtener los más vendidos.
            $sql_best_sellers = "SELECT
                                    productos.id,
                                    productos.nombre,
                                    productos.descripcion,
                                    productos.precio,
                                    productos.imagen,
                                    SUM(detalle_pedidos.cantidad) AS total_vendido
                                FROM
                                    detalle_pedidos
                                JOIN
                                    productos ON detalle_pedidos.producto_id = productos.id
                                GROUP BY
                                    productos.id
                                ORDER BY
                                    total_vendido DESC
                                LIMIT 4"; // Mostramos el Top 4

            $result_best_sellers = $conn->query($sql_best_sellers);

            if ($result_best_sellers && $result_best_sellers->num_rows > 0) {
                while($row = $result_best_sellers->fetch_assoc()) {
                    
                    $descripcion_corta = mb_strimwidth(htmlspecialchars($row["descripcion"]), 0, 50, "...");

                    echo '<a href="productos.php?id=' . htmlspecialchars($row["id"]) . '" style="text-decoration: none;">';
                    echo '<div class="producto">';
                    
                    if (!empty($row["imagen"]) && file_exists("img/" . htmlspecialchars($row["imagen"]))) {
                        echo '<img src="img/' . htmlspecialchars($row["imagen"]) . '" alt="' . htmlspecialchars($row["nombre"]) . '">';
                    } else {
                        echo '<img src="img/placeholder.jpg" alt="Image not available">';
                    }
                    
                    echo '<h3>' . htmlspecialchars($row["nombre"]) . '</h3>';
                    echo '<p>' . $descripcion_corta . '</p>';
                    echo '<p class="precio">$' . htmlspecialchars(number_format($row["precio"], 2)) . '</p>';
                    echo '</div>';
                    echo '</a>';
                }
            } else {
                echo "<p style='text-align: center;'>No best sellers to show yet.</p>";
            }

            // (CAMBIO) Cerramos la conexión aquí, después de la ÚLTIMA consulta
            $conn->close();
            ?>
        </div>
    </section>
    
</div> <?php
include 'templates/footer.php';
?>