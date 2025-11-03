<?php
// 1. Incluir el header
include 'templates/header.php';
require_once 'conection/db.php';

// 2. Obtener el ID del producto
$id = intval($_GET['id'] ?? 0);
$producto = null; 

if ($id > 0) {
    // 3. Buscar el producto de forma segura
    $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// 4. Manejar producto no encontrado
if (!$producto) {
    echo '<div class="container my-5">
            <div class="alert alert-danger text-center">
                <strong>Error:</strong> Product not found.
            </div>
            <div class="text-center">
                <a href="catalogo.php" class="btn btn-primary">Back to catalog</a>
            </div>
          </div>';
    // (CAMBIO) Cerramos la conexión aquí también
    $conn->close(); 
    include 'templates/footer.php';
    exit;
}

// =================================================
// (NUEVO) LÓGICA DE PRODUCTOS RECOMENDADOS
// =================================================
$recommended_products = [];
$categoria_id = $producto['categoria_id'];
$current_product_id = $producto['id'];

// Primero, intentar obtener productos de la misma categoría
if ($categoria_id) {
    $sql_related = "SELECT id, nombre, precio, imagen, descripcion FROM productos 
                    WHERE categoria_id = ? AND id != ? 
                    ORDER BY id DESC LIMIT 4";
    $stmt_related = $conn->prepare($sql_related);
    $stmt_related->bind_param("ii", $categoria_id, $current_product_id);
    $stmt_related->execute();
    $result_related = $stmt_related->get_result();
    while ($row = $result_related->fetch_assoc()) {
        $recommended_products[] = $row;
    }
    $stmt_related->close();
}

// Si no hay relacionados (o no había categoría), mostrar los más nuevos (fallback)
if (empty($recommended_products)) {
    $sql_fallback = "SELECT id, nombre, precio, imagen, descripcion FROM productos 
                     WHERE id != ? 
                     ORDER BY id DESC LIMIT 4";
    $stmt_fallback = $conn->prepare($sql_fallback);
    $stmt_fallback->bind_param("i", $current_product_id);
    $stmt_fallback->execute();
    $result_fallback = $stmt_fallback->get_result();
    while ($row = $result_fallback->fetch_assoc()) {
        $recommended_products[] = $row;
    }
    $stmt_fallback->close();
}

// (CAMBIO) Movemos el cierre de la conexión al final de TODAS las consultas
$conn->close();

// 5. Si el producto se encuentra, mostrar el HTML
?>

<div class="container my-5">
    <div class="row g-5">

        <div class="col-md-6 text-center">
            <img src="img/<?= htmlspecialchars($producto['imagen']) ?>" 
                 alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                 class="img-fluid rounded shadow-sm" 
                 style="max-height: 450px; width: auto; border: 1px solid #eee; padding: 10px;">
        </div>

        <div class="col-md-6">
            
            <h1 style="color: #8d2146; font-weight: 600;"><?= htmlspecialchars($producto['nombre']) ?></h1>
            
            <h3 class="my-3" style="color: #e14b7b; font-weight: bold;">
                $<?= htmlspecialchars(number_format($producto['precio'], 2)) ?>
            </h3>

            <p class="lead" style="font-size: 1.1em;">
                <?= nl2br(htmlspecialchars($producto['descripcion'])) ?>
            </p>

            <hr class="my-4">

            <?php if ($producto['stock'] > 0): ?>
                
                <p class="text-muted">Available: <?= htmlspecialchars($producto['stock']) ?> units</p>

                <form action="carrito.php" method="post" class="mt-3">
                    <input type="hidden" name="producto_id" value="<?= $id ?>">
                    
                    <div class="row g-2">
                        <div class="col-auto">
                            <label for="cantidad" class="form-label">Quantity:</label>
                            <input type="number" name="cantidad" id="cantidad" 
                                   class="form-control" 
                                   value="1" min="1" 
                                   max="<?= $producto['stock'] ?>" 
                                   style="width: 100px;">
                        </div>
                        <div class="col-auto d-flex align-items-end">
                            <button type="submit" class="btn btn-primary" style="background-color: #e14b7b; border: none; height: 38px;">
                                Add to cart
                            </button>
                        </div>
                    </div>
                </form>

            <?php else: ?>
                
                <div class="alert alert-warning" role="alert">
                    <strong>Out of stock!</strong> This product is not available at the moment.
                </div>

            <?php endif; ?>
            
            <a href="catalogo.php" class="btn btn-link mt-4 ps-0">← Back to catalog</a>
        </div>
    </div>
    
    <?php if (!empty($recommended_products)): ?>
        <hr class="my-5">
        
        <h2 style="text-align: center; color: #8d2146; margin-bottom: 30px;">
            You might also like
        </h2>
        
        <div class="lista-productos">
            <?php foreach ($recommended_products as $rec_prod): ?>
                <?php
                    $descripcion_corta = mb_strimwidth(htmlspecialchars($rec_prod["descripcion"]), 0, 50, "...");
                ?>
                <a href="productos.php?id=<?= htmlspecialchars($rec_prod["id"]) ?>" style="text-decoration: none;">
                    <div class="producto">
                        <?php if (!empty($rec_prod["imagen"]) && file_exists("img/" . htmlspecialchars($rec_prod["imagen"]))): ?>
                            <img src="img/<?= htmlspecialchars($rec_prod["imagen"]) ?>" alt="<?= htmlspecialchars($rec_prod["nombre"]) ?>">
                        <?php else: ?>
                            <img src="img/placeholder.jpg" alt="Image not available">
                        <?php endif; ?>
                        
                        <h3><?= htmlspecialchars($rec_prod["nombre"]) ?></h3>
                        <p><?= $descripcion_corta ?></p>
                        <p class="precio">$<?= htmlspecialchars(number_format($rec_prod["precio"], 2)) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
</div> <?php
include 'templates/footer.php';
?>