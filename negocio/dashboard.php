<?php
session_start();
require_once("../include/auth.php"); require_role('negocio');
require_once("../include/conexion.php");

$uid = $_SESSION['usuario']['Id_usuario'];

// Id y nombre del negocio
$st = $mysqli->prepare("SELECT Id_negocio, Nombre FROM negocios WHERE Usuario_id=?");
$st->bind_param("i",$uid); 
$st->execute();
$neg = $st->get_result()->fetch_assoc(); 
$st->close();

$negocio_id = (int)($neg['Id_negocio'] ?? 0);

// Métricas helper
function one($mysqli,$sql,$negocio_id){
  $s=$mysqli->prepare($sql);
  $s->bind_param("i",$negocio_id);
  $s->execute();
  $v=$s->get_result()->fetch_row()[0]??0;
  $s->close();
  return $v;
}

$pend   = one($mysqli,"SELECT COUNT(*) FROM pedidos WHERE Negocio_id=? AND Estado='Pendiente'", $negocio_id);
$prep   = one($mysqli,"SELECT COUNT(*) FROM pedidos WHERE Negocio_id=? AND Estado='En preparación'", $negocio_id);
$listo  = one($mysqli,"SELECT COUNT(*) FROM pedidos WHERE Negocio_id=? AND Estado='Listo'", $negocio_id);
$hoy    = one($mysqli,"SELECT COUNT(*) FROM pedidos WHERE Negocio_id=? AND DATE(Fecha)=CURDATE()", $negocio_id);
$ventaH = one($mysqli,"SELECT COALESCE(SUM(Total),0) FROM pedidos WHERE Negocio_id=? AND DATE(Fecha)=CURDATE()", $negocio_id);

// Últimos 5 pedidos
$ult = [];
$sql = "SELECT p.Id_pedido, p.Fecha, p.Estado, p.Total, u.Nombre AS Cliente
        FROM pedidos p 
        JOIN usuarios u ON u.Id_usuario=p.Cliente_id
        WHERE p.Negocio_id=? 
        ORDER BY p.Id_pedido DESC LIMIT 5";
$s=$mysqli->prepare($sql); 
$s->bind_param("i",$negocio_id); 
$s->execute();
$r=$s->get_result(); 
while($row=$r->fetch_assoc()){ $ult[]=$row; } 
$s->close();

// Badge por estado
function badgeClass($estado){
  switch($estado){
    case 'Pendiente':       return 'badge-status-pendiente';
    case 'En preparación':  return 'badge-status-preparacion';
    case 'Listo':           return 'badge-status-listo';
    default:                return 'bg-secondary';
  }
}

$BASE="/mamalila_prof";
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/app.css" rel="stylesheet">
  <title>Panel del negocio</title>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <?php include("../include/menu.php"); ?>
    <main class="col-md-9 p-4">
      <h3>Panel del negocio</h3>
      <p class="text-muted mb-4">Bienvenido, <?php echo htmlspecialchars($neg['Nombre'] ?? ''); ?></p>

      <!-- Métricas -->
      <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="stat"><div class="label">Pendientes</div><div class="value"><?php echo $pend; ?></div></div></div>
        <div class="col-md-3"><div class="stat"><div class="label">En preparación</div><div class="value"><?php echo $prep; ?></div></div></div>
        <div class="col-md-3"><div class="stat"><div class="label">Listos</div><div class="value"><?php echo $listo; ?></div></div></div>
        <div class="col-md-3"><div class="stat"><div class="label">Pedidos hoy</div><div class="value"><?php echo $hoy; ?></div></div></div>
      </div>

      <div class="mb-4">
        <span class="badge bg-success">Ventas hoy: ₡<?php echo number_format($ventaH,0); ?></span>
      </div>

      <div class="mb-4">
        <a class="btn btn-primary me-2" href="<?php echo $BASE; ?>/negocio/platillos.php">Administrar platillos</a>
        <a class="btn btn-outline-primary me-2" href="<?php echo $BASE; ?>/negocio/perfil.php">Mi perfil</a>
        <a class="btn btn-outline-secondary" href="<?php echo $BASE; ?>/negocio/pedidos.php">Ver pedidos</a>
      </div>

      <h5>Últimos pedidos</h5>
      <?php if(!count($ult)): ?>
        <p class="text-muted">Aún no hay pedidos.</p>
      <?php else: ?>
        <?php foreach($ult as $p): ?>
          <div class="card mb-2">
            <div class="card-body d-flex justify-content-between">
              <div>#<?php echo $p['Id_pedido']; ?> - <?php echo htmlspecialchars($p['Cliente']); ?>
                <br><small><?php echo $p['Fecha']; ?></small></div>
              <div>
                <span class="badge <?php echo badgeClass($p['Estado']); ?>"><?php echo $p['Estado']; ?></span>
                <br><strong>₡<?php echo number_format($p['Total'],0); ?></strong>
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
