<?php
include 'templates/header.php';
include_once 'conection/db.php';

// Obtener categorías y materiales para los filtros
$categorias = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
$materiales = $conn->query("SELECT id, nombre FROM materiales ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

// Construir la consulta SQL con filtros
$sql = "SELECT id, nombre, descripcion, precio, imagen FROM productos";
$params = [];
$types = "";
$where = [];

$categoria_id = intval($_GET['categoria'] ?? 0);
$material_id = intval($_GET['material'] ?? 0);

if ($categoria_id > 0) {
    $where[] = "categoria_id = ?";
    $params[] = $categoria_id;
    $types .= "i";
}

if ($material_id > 0) {
    $where[] = "material_id = ?";
    $params[] = $material_id;
    $types .= "i";
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$productos = [];
if ($result) {
    $productos = $result->fetch_all(MYSQLI_ASSOC);
}

$stmt->close();
$conn->close();
?>

<div class="container my-5">
    
    <h1 class="text-center mb-4" style="color: #8d2146;">Explore our Catalog</h1>

    <div class="row g-5">
        
        <div class="col-lg-3">
            <h3 style="border-bottom: 2px solid #e14b7b; padding-bottom: 5px;">Filters</h3>
            
            <form action="catalogo.php" method="get">
                <div class="mb-3">
                    <label for="categoria" class="form-label">Category</label>
                    <select class="form-select" id="categoria" name="categoria">
                        <option value="">All</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoria_id == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="material" class="form-label">Material</label>
                    <select class="form-select" id="material" name="material">
                        <option value="">All</option>
                        <?php foreach ($materiales as $mat): ?>
                            <option value="<?= $mat['id'] ?>" <?= $mat['id'] == $mat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn w-100" style="background-color: #e14b7b; color: white;">Apply Filters</button>
            </form>
        </div>

        <div class="col-lg-9">
            <div class="lista-productos">
                <?php if (!empty($productos)): ?>
                    <?php foreach ($productos as $prod): ?>
                        <?php
                            $descripcion_corta = mb_strimwidth(htmlspecialchars($prod["descripcion"]), 0, 50, "...");
                        ?>
                        <a href="productos.php?id=<?= $prod['id'] ?>" style="text-decoration: none;">
                            <div class="producto">
                                <?php if (!empty($prod["imagen"]) && file_exists("img/" . htmlspecialchars($prod["imagen"]))): ?>
                                    <img src="img/<?= htmlspecialchars($prod['imagen']) ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>">
                                <?php else: ?>
                                    <img src="img/placeholder.jpg" alt="Image not available">
                                <?php endif; ?>
                                
                                <h3><?= htmlspecialchars($prod['nombre']) ?></h3>
                                <p><?= $descripcion_corta ?></p>
                                <p class="precio">$<?= htmlspecialchars(number_format($prod['precio'], 2)) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center w-100">No products found matching your selection.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>