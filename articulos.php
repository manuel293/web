<?php
$conn = new mysqli("localhost", "root", "", "escuela_almacen");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar formularios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_articulo'])) {
        $nombre = $_POST['nombre'];
        $id_categoria = $_POST['id_categoria'];
        $descripcion = $_POST['descripcion'];
        $cantidad = $_POST['cantidad'];
        $sql = "INSERT INTO articulo (nombre, id_categoria, descripcion, cantidad) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisi", $nombre, $id_categoria, $descripcion, $cantidad);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['edit_articulo'])) {
        $id = $_POST['id_articulo'];
        $nombre = $_POST['nombre'];
        $id_categoria = $_POST['id_categoria'];
        $descripcion = $_POST['descripcion'];
        $cantidad = $_POST['cantidad'];
        $sql = "UPDATE articulo SET nombre = ?, id_categoria = ?, descripcion = ?, cantidad = ? WHERE id_articulo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisii", $nombre, $id_categoria, $descripcion, $cantidad, $id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_articulo'])) {
        $id = $_POST['id_articulo'];
        $sql = "DELETE FROM articulo WHERE id_articulo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Obtener artículos
$sql = "SELECT a.*, c.nombre as categoria_nombre FROM articulo a JOIN categoria c ON a.id_categoria = c.id_categoria";
$result = $conn->query($sql);

// Obtener categorías para el formulario
$categorias = $conn->query("SELECT * FROM categoria");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Artículos</title>
    <link rel="stylesheet" href="style.css">
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
        <h2>Gestión de Artículos</h2>

        <div class="form-container">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Categoría</label>
                    <select name="id_categoria" class="form-select" required>
                        <option value="">Selecciona una categoría</option>
                        <?php while ($cat = $categorias->fetch_assoc()) {
                            echo "<option value='{$cat['id_categoria']}'>{$cat['nombre']}</option>";
                        }
                        $categorias->data_seek(0);
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cantidad</label>
                    <input type="number" name="cantidad" class="form-control" min="0" required>
                </div>
                <button type="submit" name="add_articulo" class="btn btn-red">Agregar</button>
            </form>
        </div>

        <h3>Listado de Artículos</h3>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id_articulo']}</td>
                                    <td>{$row['nombre']}</td>
                                    <td>{$row['categoria_nombre']}</td>
                                    <td>{$row['descripcion']}</td>
                                    <td>{$row['cantidad']}</td>
                                    <td>
                                        <button class='btn btn-gray' data-bs-toggle='modal' data-bs-target='#editModal{$row['id_articulo']}'>Editar</button>
                                        <form method='POST' style='display:inline;'>
                                            <input type='hidden' name='id_articulo' value='{$row['id_articulo']}'>
                                            <button type='submit' name='delete_articulo' class='btn btn-red'>Eliminar</button>
                                        </form>
                                    </td>
                                  </tr>";

                            // Modal para editar
                            echo "<div class='modal fade' id='editModal{$row['id_articulo']}' tabindex='-1'>
                                    <div class='modal-dialog'>
                                        <div class='modal-content'>
                                            <div class='modal-header'>
                                                <h5 class='modal-title'>Editar Artículo</h5>
                                                <button type='button' class='close' data-bs-dismiss='modal'>&times;</button>
                                            </div>
                                            <div class='modal-body'>
                                                <form method='POST'>
                                                    <input type='hidden' name='id_articulo' value='{$row['id_articulo']}'>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Nombre</label>
                                                        <input type='text' name='nombre' class='form-control' value='{$row['nombre']}' required>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Categoría</label>
                                                        <select name='id_categoria' class='form-select' required>";
                                                            while ($cat = $categorias->fetch_assoc()) {
                                                                $selected = ($cat['id_categoria'] == $row['id_categoria']) ? 'selected' : '';
                                                                echo "<option value='{$cat['id_categoria']}' $selected>{$cat['nombre']}</option>";
                                                            }
                                                            $categorias->data_seek(0);
                                                        echo "</select>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Descripción</label>
                                                        <textarea name='descripcion' class='form-control'>{$row['descripcion']}</textarea>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Cantidad</label>
                                                        <input type='number' name='cantidad' class='form-control' value='{$row['cantidad']}' min='0' required>
                                                    </div>
                                                    <div class='modal-footer'>
                                                        <button type='button' class='btn btn-gray' data-bs-dismiss='modal'>Cerrar</button>
                                                        <button type='submit' name='edit_articulo' class='btn btn-red'>Guardar Cambios</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                  </div>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No hay artículos registrados</td></tr>";
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