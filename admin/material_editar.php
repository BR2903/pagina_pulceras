<?php
session_start();
require_once 'proteger_admin.php';
require_once '../conection/db.php';

$id = intval($_GET['id'] ?? 0);
$error = "";

$stmt = $conn->prepare("SELECT * FROM materiales WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$material = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$material) {
    die("Material no encontrado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    if ($nombre === "") {
        $error = "El nombre es obligatorio.";
    } else {
        // --- CORRECCIÓN AQUÍ --- 
        // Se eliminó el '}' al final de la consulta SQL
        $stmt = $conn->prepare("UPDATE materiales SET nombre=? WHERE id=?");
        // -------------------------

        $stmt->bind_param("si", $nombre, $id);
        if($stmt->execute()) {
            header('Location: materiales_list.php');
            exit;
        } else {
            $error = "Error al actualizar.";
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
    <title>Editar Material</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Editar material</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif ?>
    <form method="post">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del material</label>
            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($material['nombre']) ?>" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="materiales_list.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>