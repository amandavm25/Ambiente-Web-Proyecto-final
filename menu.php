<?php
session_start();
require_once("include/auth.php"); require_login();
require_once("include/conexion.php");

/* === Rutas  === */
$BASE = "/mamalila_prof";
$PUBLIC_UPLOAD = $BASE . "/uploads/platillos";

$negocioId = (int)($_GET['id'] ?? 0);

/* Negocio */
$st = $mysqli->prepare("SELECT Id_negocio, Nombre FROM negocios WHERE Id_negocio=?");
$st->bind_param("i", $negocioId);
$st->execute();
$negocio = $st->get_result()->fetch_assoc();
$st->close();

/* Platillos (con imagen) */
$platillos = [];
$st = $mysqli->prepare("SELECT Id_platillo, Nombre, Descripcion, Precio, Imagen 
                        FROM platillos 
                        WHERE Negocio_id=? AND Disponible=1 
                        ORDER BY Nombre");
$st->bind_param("i", $negocioId);
$st->execute();
$res = $st->get_result();
while($row = $res->fetch_assoc()){ $platillos[] = $row; }
$st->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
  <title>Menú - Mamalila</title>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <?php include("include/menu.php"); ?>
      <main class="col-md-9 p-4">
        <a href="home.php">&larr; Volver</a>
        <h3 class="mt-2"><?php echo htmlspecialchars($negocio['Nombre'] ?? 'Menú'); ?></h3>

        <div class="row g-3 mt-1">
          <?php foreach($platillos as $p): ?>
            <div class="col-md-4">
              <div class="card h-100">
                <div class="card-body">
                  <?php if(!empty($p['Imagen'])): ?>
                    <img class="thumb mb-2" src="<?php echo $PUBLIC_UPLOAD . '/' . htmlspecialchars($p['Imagen']); ?>" alt="img">
                  <?php endif; ?>
                  <h5 class="card-title mb-1">
                    <?php echo htmlspecialchars($p['Nombre']); ?> 
                    <small class="text-muted">₡<?php echo number_format($p['Precio'],0); ?></small>
                  </h5>
                  <p class="card-text"><?php echo htmlspecialchars($p['Descripcion'] ?? ''); ?></p>
                  <button class="btn btn-primary"
                          onclick="addToCart(<?php echo (int)$p['Id_platillo']; ?>, 
                                             '<?php echo htmlspecialchars($p['Nombre'], ENT_QUOTES); ?>', 
                                             <?php echo (float)$p['Precio']; ?>, 
                                             <?php echo (int)$negocioId; ?>)">
                    Agregar
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <a class="btn btn-success mt-3" href="checkout.php?negocio=<?php echo (int)$negocioId; ?>">Ir al carrito</a>
      </main>
    </div>
  </div>
  <script src="assets/js/cart.js"></script>
</body>
</html>
