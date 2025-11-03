<?php
session_start();
require_once 'proteger_admin.php';
require_once '../conection/db.php';

$categorias = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
$materiales = $conn->query("SELECT id, nombre FROM materiales ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- CAMBIO AQUÍ: Validación de campos obligatorios en PHP ---
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    
    if ($nombre === "" || $precio <= 0) {
        $error = "El Nombre y el Precio (mayor a 0) son obligatorios.";
    }

    // --- CAMBIO AQUÍ: Campos opcionales se preparan para NULL ---
    $descripcion = trim($_POST['descripcion']);
    $stock = intval($_POST['stock']); // intval de "" es 0, lo cual es correcto para stock

    // Si el select viene vacío, guardar NULL, no 0
    $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : NULL;
    $material_id = !empty($_POST['material_id']) ? intval($_POST['material_id']) : NULL;

    $imagen_nombre = "";
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // (Recomendación de seguridad de antes)
        $dir_subida = '../img/';
        $nombre_original = basename($_FILES['imagen']['name']);
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($extension, $extensiones_permitidas)) {
            $error = "Error: Solo se permiten archivos de imagen (jpg, jpeg, png, webp, gif).";
        } else {
            if (!is_dir($dir_subida)) {
                mkdir($dir_subida, 0777, true);
            }
            $archivo_tmp = $_FILES['imagen']['tmp_name'];
            $imagen_nombre = uniqid() . '_' . $nombre_original;
            $ruta_destino = $dir_subida . $imagen_nombre;

            if (!move_uploaded_file($archivo_tmp, $ruta_destino)) {
                $error = "Error al subir la imagen.";
                $imagen_nombre = ""; // Limpiar si falla la subida
            }
        }
    }

    if ($error === "") {
        // La consulta SQL ya soporta NULLs
        $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, categoria_id, material_id, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        // 'ssdiiss' funciona bien con NULLs para los campos 'i'
        $stmt->bind_param("ssdiiss", $nombre, $descripcion, $precio, $stock, $categoria_id, $material_id, $imagen_nombre); 
        
        if($stmt->execute()) {
            header('Location: productos_list.php');
            exit;
        } else {
            $error = "Error al guardar en la base de datos: " . $stmt->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Agregar nuevo producto</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif ?>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label for="precio" class="form-label">Precio <span class="text-danger">*</span></label>
            <input type="number" name="precio" class="form-control" step="0.01" min="0.01" required>
        </div>
        <div class="mb-3">
            <label for="stock" class="form-label">Stock</label>
            <input type="number" name="stock" class="form-control" min="0">
        </div>
        <div class="mb-3">
            <label for="categoria_id" class="form-label">Categoría</label>
            <select name="categoria_id" class="form-control" id="categoria_id">
                <option value="">(Sin categoría)</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="material_id" class="form-label">Material</label>
            <select name="material_id" class="form-control" id="material_id">
                <option value="">(Sin material)</option>
                <?php foreach ($materiales as $mat): ?>
                    <option value="<?= $mat['id'] ?>"><?= htmlspecialchars($mat['nombre']) ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="imagen" class="form-label">Imagen (Opcional)</label>
            <input type="file" name="imagen" class="form-control" id="imagen" accept="image/*">
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="productos_list.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>