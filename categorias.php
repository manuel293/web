<?php
$conn = new mysqli("localhost", "root", "", "escuela_almacen");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar formularios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_categoria'])) {
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $sql = "INSERT INTO categoria (nombre, descripcion) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nombre, $descripcion);
        if ($stmt->execute()) {
            $mensaje = "Categoría agregada correctamente.";
        } else {
            $error = "Error al agregar la categoría.";
        }
        $stmt->close();
    } elseif (isset($_POST['edit_categoria'])) {
        $id = $_POST['id_categoria'];
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $sql = "UPDATE categoria SET nombre = ?, descripcion = ? WHERE id_categoria = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nombre, $descripcion, $id);
        if ($stmt->execute()) {
            $mensaje = "Categoría actualizada correctamente.";
        } else {
            $error = "Error al actualizar la categoría.";
        }
        $stmt->close();
    } elseif (isset($_POST['delete_categoria'])) {
        $id = $_POST['id_categoria'];
        $sql = "DELETE FROM categoria WHERE id_categoria = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $mensaje = "Categoría eliminada correctamente.";
        } else {
            $error = "Error al eliminar la categoría.";
        }
        $stmt->close();
    }
}

// Obtener categorías
$sql = "SELECT * FROM categoria";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías</title>
    <link rel="stylesheet" href="style1.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

    <header>
        <div class="header-left">
            <img src="logo.png" alt="Logo" class="logo">
            <span>Almacén Escolar</span>
        </div>
        <div class="nav-links">
            <a href="index.html">Inicio</a>
            <a href="categorias.php">Categorías</a>
            <a href="articulos.php">Artículos</a>
            <a href="solicitantes.php">Solicitantes</a>
            <a href="solicitudes.php">Solicitudes</a>
            <a href="mermas.php">Mermas</a>
            <a href="#contacto">Contacto</a>
        </div>
    </header>

    <div class="main-content">
        <h2>Gestión de Categorías</h2>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h3>Nueva Categoría</h3>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control"></textarea>
                </div>
                <button type="submit" name="add_categoria" class="btn btn-red">Agregar Categoría</button>
            </form>
        </div>

        <h3>Listado de Categorías</h3>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id_categoria']}</td>
                                    <td>{$row['nombre']}</td>
                                    <td>{$row['descripcion']}</td>
                                    <td>
                                        <button class='btn btn-gray' data-bs-toggle='modal' data-bs-target='#editModal{$row['id_categoria']}'>Editar</button>
                                        <form method='POST' style='display:inline;'>
                                            <input type='hidden' name='id_categoria' value='{$row['id_categoria']}'>
                                            <button type='submit' name='delete_categoria' class='btn btn-red'>Eliminar</button>
                                        </form>
                                    </td>
                                  </tr>";

                            // Modal para editar
                            echo "<div class='modal fade' id='editModal{$row['id_categoria']}' tabindex='-1'>
                                    <div class='modal-dialog'>
                                        <div class='modal-content'>
                                            <div class='modal-header'>
                                                <h5 class='modal-title'>Editar Categoría</h5>
                                                <button type='button' class='close' data-bs-dismiss='modal'>×</button>
                                            </div>
                                            <div class='modal-body'>
                                                <form method='POST'>
                                                    <input type='hidden' name='id_categoria' value='{$row['id_categoria']}'>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Nombre</label>
                                                        <input type='text' name='nombre' class='form-control' value='{$row['nombre']}' required>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Descripción</label>
                                                        <textarea name='descripcion' class='form-control'>{$row['descripcion']}</textarea>
                                                    </div>
                                                    <div class='modal-footer'>
                                                        <button type='button' class='btn btn-gray' data-bs-dismiss='modal'>Cerrar</button>
                                                        <button type='submit' name='edit_categoria' class='btn btn-red'>Guardar Cambios</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                  </div>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No hay categorías registradas</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>© 2025 Sistema de Almacén Escolar. CETIS 64. | Tel: 55 1234 5678 | Email: contacto@cetis64.edu.mx</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
