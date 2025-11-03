<?php
session_start();
require_once 'proteger_admin.php';
require_once '../conection/db.php';

$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM productos WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$producto) {
    // (Texto traducido)
    die("Product not found.");
}

$categorias = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
$materiales = $conn->query("SELECT id, nombre FROM materiales ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    
    if ($nombre === "" || $precio <= 0) {
        // (Texto traducido)
        $error = "Name and Price (greater than 0) are required.";
    }

    $descripcion = trim($_POST['descripcion']);
    $stock = intval($_POST['stock']);

    $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : NULL;
    $material_id = !empty($_POST['material_id']) ? intval($_POST['material_id']) : NULL;

    $imagen_nombre = $producto['imagen'];
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        
        $dir_subida = '../img/';
        $nombre_original = basename($_FILES['imagen']['name']);
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($extension, $extensiones_permitidas)) {
            // (Texto traducido)
            $error = "Error: Only image files (jpg, jpeg, png, webp, gif) are allowed.";
        } else {
            $archivo_tmp = $_FILES['imagen']['tmp_name'];
            $imagen_nombre_nueva = uniqid() . '_' . $nombre_original;
            $ruta_destino = $dir_subida . $imagen_nombre_nueva;

            if (move_uploaded_file($archivo_tmp, $ruta_destino)) {
                if (!empty($producto['imagen']) && file_exists($dir_subida . $producto['imagen'])) {
                    unlink($dir_subida . $producto['imagen']);
                }
                $imagen_nombre = $imagen_nombre_nueva;
            } else {
                // (Texto traducido)
                $error = "Error uploading new image.";
            }
        }
    }

    if ($error === "") {
        $stmt = $conn->prepare("UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, categoria_id=?, material_id=?, imagen=? WHERE id=?");
        $stmt->bind_param("ssdiissi", $nombre, $descripcion, $precio, $stock, $categoria_id, $material_id, $imagen_nombre, $id);
        
        if($stmt->execute()) {
            header('Location: productos_list.php');
            exit;
        } else {
            // (Texto traducido)
            $error = "Error updating: " . $stmt->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Edit Product</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif ?>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nombre" class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($producto['nombre']) ?>" required autofocus>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Description</label>
            <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="precio" class="form-label">Price <span class="text-danger">*</span></label>
            <input type="number" name="precio" class="form-control" step="0.01" min="0.01" value="<?= htmlspecialchars($producto['precio']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="stock" class="form-label">Stock</label>
            <input type="number" name="stock" class="form-control" min="0" value="<?= htmlspecialchars($producto['stock']) ?>">
        </div>
        <div class="mb-3">
            <label for="categoria_id" class="form-label">Category</label>
            
            <select name="categoria_id" class="form-control">
                <option value="">(No category)</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $producto['categoria_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="material_id" class="form-label">Material</label>

            <select name="material_id" class="form-control">
                <option value="">(No material)</option>
                <?php foreach ($materiales as $mat): ?>
                    <option value="<?= $mat['id'] ?>" <?= $mat['id'] == $producto['material_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($mat['nombre']) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="imagen" class="form-label">Image</label>
            <?php if ($producto['imagen']): ?>
                <div>
                    <img src="../img/<?= htmlspecialchars($producto['imagen']) ?>" width="100" style="object-fit:cover;max-height:100px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                    <br><small>Upload a new image to replace it (optional)</small>
                </div>
            <?php else: ?>
                 <small>No image. Upload one (optional).</small>
            <?php endif ?>
            <input type="file" name="imagen" class="form-control mt-2" id="imagen" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="productos_list.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>