<?php
session_start();
require_once("include/auth.php"); require_login();
$u = $_SESSION['usuario'];
if ($u['Rol'] === 'negocio') { header("Location: negocio/dashboard.php"); exit(); }
require_once("include/conexion.php");

// Obtener negocios 
$negocios = [];
$res = $mysqli->query("SELECT Id_negocio, Nombre, Horario, Costo_envio, Tiempo_minutos FROM negocios ORDER BY Nombre");
if($res){ while($row = $res->fetch_assoc()){ $negocios[] = $row; } $res->close(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
  <title>Inicio - Mamalila</title>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <?php include("include/menu.php"); ?>
      <main class="col-md-9 p-4">
        <h3>Hola, <?php echo htmlspecialchars($u['Nombre']); ?> 👋</h3>
        <p class="text-muted">Elige un negocio para ver su menú</p>
        <div class="row g-3">
          <?php foreach($negocios as $n): ?>
            <div class="col-md-4">
              <div class="card h-100">
                <div class="card-body">
                  <h5 class="card-title mb-1"><?php echo htmlspecialchars($n['Nombre']); ?></h5>

                  <!-- costo/tiempo -->
                  <div class="small text-muted mb-1">
                    Costo de envío: ₡<?php echo number_format($n['Costo_envio'] ?? 0, 0); ?>
                    • <?php echo (int)($n['Tiempo_minutos'] ?? 20); ?> min
                  </div>

                  <p class="card-text"><?php echo htmlspecialchars($n['Horario'] ?? ''); ?></p>
                  <a class="btn btn-outline-primary" href="menu.php?id=<?php echo $n['Id_negocio']; ?>">Ver menú</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </main>
    </div>
  </div>
</body>
</html>