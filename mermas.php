<?php
// Conexión a la base de datos
$conexion = mysqli_connect("localhost", "root", "", "escuela_almacen");
if (!$conexion) {
    die("Error al conectar a la base de datos: " . mysqli_connect_error());
}

// Registrar nueva merma
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["registrar_merma"])) {
    $id_articulo = intval($_POST["id_articulo"]);
    $cantidad = intval($_POST["cantidad"]);
    $motivo = mysqli_real_escape_string($conexion, $_POST["motivo"]);

    // Validar existencia del artículo y cantidad
    $query_articulo = mysqli_query($conexion, "SELECT cantidad FROM articulo WHERE id_articulo = $id_articulo");
    $row = mysqli_fetch_assoc($query_articulo);

    if ($row && $row["cantidad"] >= $cantidad) {
        // Registrar merma
        mysqli_query($conexion, "INSERT INTO merma (id_articulo, cantidad, motivo) VALUES ($id_articulo, $cantidad, '$motivo')");

        // Actualizar inventario
        mysqli_query($conexion, "UPDATE articulo SET cantidad = cantidad - $cantidad WHERE id_articulo = $id_articulo");

        $mensaje = "Merma registrada correctamente.";
    } else {
        $error = "Cantidad inválida. No hay suficiente stock.";
    }
}

// Obtener listado de mermas
$mermas = mysqli_query($conexion, "SELECT m.*, a.nombre AS articulo_nombre 
                                   FROM merma m
                                   INNER JOIN articulo a ON m.id_articulo = a.id_articulo
                                   ORDER BY m.fecha DESC");

// Obtener lista de artículos para el formulario
$articulos = mysqli_query($conexion, "SELECT id_articulo, nombre, cantidad FROM articulo");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Mermas</title>
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
        <h2>Registro de Mermas</h2>

        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="registrar_merma" value="1">
                <div class="mb-3">
                    <label class="form-label">Artículo</label>
                    <select name="id_articulo" class="form-select" required>
                        <option value="">Seleccione</option>
                        <?php while ($a = mysqli_fetch_assoc($articulos)): ?>
                            <option value="<?php echo $a["id_articulo"]; ?>">
                                <?php echo htmlspecialchars($a["nombre"]) . " (" . $a["cantidad"] . " disponibles)"; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cantidad</label>
                    <input type="number" name="cantidad" class="form-control" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Motivo</label>
                    <textarea name="motivo" class="form-control" required></textarea>
                </div>
                <button type="submit" class="btn btn-red">Registrar Merma</button>
            </form>
        </div>

        <h3>Historial de Mermas</h3>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Artículo</th>
                        <th>Cantidad</th>
                        <th>Motivo</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($m = mysqli_fetch_assoc($mermas)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($m["articulo_nombre"]); ?></td>
                            <td><?php echo $m["cantidad"]; ?></td>
                            <td><?php echo htmlspecialchars($m["motivo"]); ?></td>
                            <td><?php echo $m["fecha"]; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>© 2025 Sistema de Almacén Escolar. CETIS 64. | Tel: 55 1234 5678 | Email: contacto@cetis64.edu.mx</p>
    </footer>
</body>
</html>