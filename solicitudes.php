<?php
$conn = new mysqli("localhost", "root", "", "escuela_almacen");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar formularios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_solicitud'])) {
        $id_solicitante = $_POST['id_solicitante'];
        $id_articulo = $_POST['id_articulo'];
        $cantidad_solicitada = $_POST['cantidad_solicitada'];
        $estado = $_POST['estado'];
        $fecha_solicitud = date('Y-m-d H:i:s');
        $sql = "INSERT INTO solicitud (id_solicitante, id_articulo, fecha_solicitud, cantidad_solicitada, estado) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisis", $id_solicitante, $id_articulo, $fecha_solicitud, $cantidad_solicitada, $estado);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['edit_solicitud'])) {
        $id = $_POST['id_solicitud'];
        $id_solicitante = $_POST['id_solicitante'];
        $id_articulo = $_POST['id_articulo'];
        $cantidad_solicitada = $_POST['cantidad_solicitada'];
        $estado = $_POST['estado'];
        $sql = "UPDATE solicitud SET id_solicitante = ?, id_articulo = ?, cantidad_solicitada = ?, estado = ? WHERE id_solicitud = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisi", $id_solicitante, $id_articulo, $cantidad_solicitada, $estado, $id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_solicitud'])) {
        $id = $_POST['id_solicitud'];
        $sql = "DELETE FROM solicitud WHERE id_solicitud = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Obtener solicitudes
$sql = "SELECT s.*, a.nombre as articulo_nombre, sol.nombre as solicitante_nombre 
        FROM solicitud s 
        JOIN articulo a ON s.id_articulo = a.id_articulo 
        JOIN solicitante sol ON s.id_solicitante = sol.id_solicitante";
$result = $conn->query($sql);

// Obtener solicitantes y artículos para el formulario
$solicitantes = $conn->query("SELECT * FROM solicitante");
$articulos = $conn->query("SELECT * FROM articulo");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Solicitudes</title>
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
        <h2>Gestión de Solicitudes</h2>

        <div class="form-container">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Solicitante</label>
                    <select name="id_solicitante" class="form-select" required>
                        <option value="">Selecciona un solicitante</option>
                        <?php while ($sol = $solicitantes->fetch_assoc()) {
                            echo "<option value='{$sol['id_solicitante']}'>{$sol['nombre']}</option>";
                        }
                        $solicitantes->data_seek(0);
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Artículo</label>
                    <select name="id_articulo" class="form-select" required>
                        <option value="">Selecciona un artículo</option>
                        <?php while ($art = $articulos->fetch_assoc()) {
                            echo "<option value='{$art['id_articulo']}'>{$art['nombre']}</option>";
                        }
                        $articulos->data_seek(0);
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cantidad Solicitada</label>
                    <input type="number" name="cantidad_solicitada" class="form-control" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Aprobada">Aprobada</option>
                        <option value="Rechazada">Rechazada</option>
                    </select>
                </div>
                <button type="submit" name="add_solicitud" class="btn btn-red">Agregar</button>
            </form>
        </div>

        <h3>Listado de Solicitudes</h3>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Solicitante</th>
                        <th>Artículo</th>
                        <th>Fecha</th>
                        <th>Cantidad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id_solicitud']}</td>
                                    <td>{$row['solicitante_nombre']}</td>
                                    <td>{$row['articulo_nombre']}</td>
                                    <td>{$row['fecha_solicitud']}</td>
                                    <td>{$row['cantidad_solicitada']}</td>
                                    <td>{$row['estado']}</td>
                                    <td>
                                        <button class='btn btn-gray' data-bs-toggle='modal' data-bs-target='#editModal{$row['id_solicitud']}'>Editar</button>
                                        <form method='POST' style='display:inline;'>
                                            <input type='hidden' name='id_solicitud' value='{$row['id_solicitud']}'>
                                            <button type='submit' name='delete_solicitud' class='btn btn-red'>Eliminar</button>
                                        </form>
                                    </td>
                                  </tr>";

                            // Modal para editar
                            echo "<div class='modal fade' id='editModal{$row['id_solicitud']}' tabindex='-1'>
                                    <div class='modal-dialog'>
                                        <div class='modal-content'>
                                            <div class='modal-header'>
                                                <h5 class='modal-title'>Editar Solicitud</h5>
                                                <button type='button' class='close' data-bs-dismiss='modal'>&times;</button>
                                            </div>
                                            <div class='modal-body'>
                                                <form method='POST'>
                                                    <input type='hidden' name='id_solicitud' value='{$row['id_solicitud']}'>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Solicitante</label>
                                                        <select name='id_solicitante' class='form-select' required>";
                                                            while ($sol = $solicitantes->fetch_assoc()) {
                                                                $selected = ($sol['id_solicitante'] == $row['id_solicitante']) ? 'selected' : '';
                                                                echo "<option value='{$sol['id_solicitante']}' $selected>{$sol['nombre']}</option>";
                                                            }
                                                            $solicitantes->data_seek(0);
                                                        echo "</select>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Artículo</label>
                                                        <select name='id_articulo' class='form-select' required>";
                                                            while ($art = $articulos->fetch_assoc()) {
                                                                $selected = ($art['id_articulo'] == $row['id_articulo']) ? 'selected' : '';
                                                                echo "<option value='{$art['id_articulo']}' $selected>{$art['nombre']}</option>";
                                                            }
                                                            $articulos->data_seek(0);
                                                        echo "</select>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Cantidad</label>
                                                        <input type='number' name='cantidad_solicitada' class='form-control' value='{$row['cantidad_solicitada']}' min='1' required>
                                                    </div>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>Estado</label>
                                                        <select name='estado' class='form-select' required>
                                                            <option value='Pendiente' " . ($row['estado'] == 'Pendiente' ? 'selected' : '') . ">Pendiente</option>
                                                            <option value='Aprobada' " . ($row['estado'] == 'Aprobada' ? 'selected' : '') . ">Aprobada</option>
                                                            <option value='Rechazada' " . ($row['estado'] == 'Rechazada' ? 'selected' : '') . ">Rechazada</option>
                                                        </select>
                                                    </div>
                                                    <div class='modal-footer'>
                                                        <button type='button' class='btn btn-gray' data-bs-dismiss='modal'>Cerrar</button>
                                                        <button type='submit' name='edit_solicitud' class='btn btn-red'>Guardar Cambios</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                  </div>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No hay solicitudes registradas</td></tr>";
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



