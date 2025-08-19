<?php
session_start();
require_once("../include/auth.php"); require_role('cliente');
require_once("../include/conexion.php");

$uid = $_SESSION['usuario']['Id_usuario'];

/* Traemos también Tipo_entrega y Costo_envio */
$pedidos = [];
$sql = "SELECT p.Id_pedido, p.Fecha, p.Estado, p.Total,
               p.Costo_envio, p.Tipo_entrega, p.Direccion_Entrega,
               n.Nombre AS Negocio
        FROM pedidos p
        JOIN negocios n ON n.Id_negocio = p.Negocio_id
        WHERE p.Cliente_id=?
        ORDER BY p.Id_pedido DESC";
$st = $mysqli->prepare($sql);
$st->bind_param("i",$uid); $st->execute();
$res = $st->get_result();
while($row=$res->fetch_assoc()){ $pedidos[] = $row; }
$st->close();

function badgeClass($estado){
  switch($estado){
    case 'Pendiente':       return 'badge-status-pendiente';
    case 'En preparación':  return 'badge-status-preparacion';
    case 'Listo':           return 'badge-status-listo';
    default:                return 'bg-secondary';
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/app.css" rel="stylesheet">
  <title>Mis pedidos</title>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <?php include("../include/menu.php"); ?>
      <main class="col-md-9 p-4">
        <h3>Mis pedidos</h3>

        <?php if(!count($pedidos)): ?>
          <p class="text-muted">Sin pedidos.</p>
        <?php else: ?>
          <?php foreach($pedidos as $p): ?>
            <?php $subtotal = max(0, (float)$p['Total'] - (float)$p['Costo_envio']); ?>
            <div class="card mb-2">
              <div class="card-body d-flex justify-content-between">
                <div>
                  <div class="fw-semibold">#<?php echo $p['Id_pedido']; ?> — <?php echo htmlspecialchars($p['Negocio']); ?></div>
                  <small class="text-muted"><?php echo $p['Fecha']; ?></small>
                  <?php if(!empty($p['Direccion_Entrega'])): ?>
                    <div class="small text-muted">Entrega en: <?php echo htmlspecialchars($p['Direccion_Entrega']); ?></div>
                  <?php endif; ?>
                </div>

                <div class="text-end">
                  <span class="badge <?php echo badgeClass($p['Estado']); ?>"><?php echo $p['Estado']; ?></span>
                  <div class="small mt-1">
                    Entrega: <strong><?php echo htmlspecialchars($p['Tipo_entrega']); ?></strong>
                    • Envío: <strong>₡<?php echo number_format($p['Costo_envio'],0); ?></strong>
                  </div>
                  <div class="small text-muted">Subtotal: ₡<?php echo number_format($subtotal,0); ?></div>
                  <div class="fw-bold">Total: ₡<?php echo number_format($p['Total'],0); ?></div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </main>
    </div>
  </div>
</body>
</html>
