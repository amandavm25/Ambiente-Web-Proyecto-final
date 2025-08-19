<?php
session_start();
require_once("../include/auth.php"); require_role('negocio');
require_once("../include/conexion.php");

/* Negocio del usuario */
$uid = $_SESSION['usuario']['Id_usuario'];
$st = $mysqli->prepare("SELECT Id_negocio FROM negocios WHERE Usuario_id=?");
$st->bind_param("i",$uid); $st->execute();
$negocio_id = $st->get_result()->fetch_assoc()['Id_negocio'] ?? 0;
$st->close();

/* Filtro por estado */
$estado = $_GET['estado'] ?? '';
$ESTADOS = ['Pendiente','En preparación','Listo'];
$estadoVal = in_array($estado,$ESTADOS,true);

/* Consulta pedidos (incluye Tipo_entrega y Costo_envio) */
$sql = "SELECT p.Id_pedido, p.Fecha, p.Estado, p.Total, p.Direccion_Entrega,
               p.Costo_envio, p.Tipo_entrega, u.Nombre AS Cliente
        FROM pedidos p
        JOIN usuarios u ON u.Id_usuario = p.Cliente_id
        WHERE p.Negocio_id=?";
if ($estadoVal) $sql .= " AND p.Estado=?";
$sql .= " ORDER BY p.Id_pedido DESC";

if ($estadoVal) { $st = $mysqli->prepare($sql); $st->bind_param("is",$negocio_id,$estado); }
else            { $st = $mysqli->prepare($sql); $st->bind_param("i",$negocio_id); }
$st->execute(); $res = $st->get_result();
$pedidos=[]; while($r=$res->fetch_assoc()){ $pedidos[]=$r; } $st->close();

/* Export CSV */
if (isset($_GET['export']) && $_GET['export']==='csv') {
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="pedidos.csv"');
  $out=fopen('php://output','w');
  fputcsv($out,['Id','Fecha','Estado','Tipo_entrega','Costo_envio','Total','Cliente','Direccion']);
  foreach($pedidos as $p){
    fputcsv($out,[
      $p['Id_pedido'],$p['Fecha'],$p['Estado'],$p['Tipo_entrega'],$p['Costo_envio'],
      $p['Total'],$p['Cliente'],$p['Direccion_Entrega']
    ]);
  }
  fclose($out); exit;
}

/* Cambiar estado */
if($_SERVER['REQUEST_METHOD']==='POST'){
  $id = (int)($_POST['id'] ?? 0);
  $nuevo = $_POST['estado'] ?? 'Pendiente';
  if (in_array($nuevo,$ESTADOS,true)) {
    $up = $mysqli->prepare("UPDATE pedidos SET Estado=? WHERE Id_pedido=? AND Negocio_id=?");
    $up->bind_param("sii",$nuevo,$id,$negocio_id); $up->execute(); $up->close();
  }
  header("Location: pedidos.php".($estadoVal ? "?estado=".urlencode($estado):""));
  exit;
}

/* Helper badge */
function badgeClass($estado){
  switch($estado){
    case 'Pendiente':       return 'badge-status-pendiente';
    case 'En preparación':  return 'badge-status-preparacion';
    case 'Listo':           return 'badge-status-listo';
    default:                return 'bg-secondary';
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/app.css" rel="stylesheet">
  <title>Pedidos recibidos</title>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <?php include("../include/menu.php"); ?>
    <main class="col-md-9 p-4">
      <h3>Pedidos recibidos</h3>

      <!-- Filtro / Export -->
      <form method="get" class="row g-2 mb-3">
        <div class="col-md-3">
          <select name="estado" class="form-select">
            <option value="">Todos los estados</option>
            <?php foreach($ESTADOS as $e): ?>
              <option value="<?php echo $e; ?>" <?php echo ($estadoVal && $estado===$e)?'selected':''; ?>><?php echo $e; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <button class="btn btn-outline-primary">Filtrar</button>
          <a class="btn btn-outline-secondary ms-2"
             href="pedidos.php?<?php echo $estadoVal?'estado='.urlencode($estado).'&':''; ?>export=csv">Exportar CSV</a>
        </div>
      </form>

      <?php if(!count($pedidos)): ?>
        <p class="text-muted">Sin pedidos.</p>
      <?php else: ?>
        <?php foreach($pedidos as $p): ?>
          <?php
            // detalle de líneas
            $det = [];
            $sd = $mysqli->prepare("SELECT d.Cantidad, pl.Nombre, d.Subtotal
                                    FROM pedido_detalles d
                                    JOIN platillos pl ON pl.Id_platillo = d.Platillo_id
                                    WHERE d.Pedido_id=?");
            $sd->bind_param("i",$p['Id_pedido']); $sd->execute();
            $rr=$sd->get_result(); while($row=$rr->fetch_assoc()){ $det[]=$row; } $sd->close();

            $collapseId = "det-".$p['Id_pedido'];
            $subtotal = max(0,(float)$p['Total'] - (float)$p['Costo_envio']);
          ?>
          <div class="card mb-2">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <div class="fw-semibold">#<?php echo $p['Id_pedido']; ?> — <?php echo htmlspecialchars($p['Cliente']); ?></div>
                  <small class="text-muted"><?php echo $p['Fecha']; ?> | <?php echo htmlspecialchars($p['Direccion_Entrega']); ?></small>
                  <div class="small">
                    Entrega: <strong><?php echo htmlspecialchars($p['Tipo_entrega']); ?></strong>
                    • Envío: <strong>₡<?php echo number_format($p['Costo_envio'],0); ?></strong>
                  </div>
                </div>
                <div class="text-end">
                  <div class="mb-1">
                    <span class="badge <?php echo badgeClass($p['Estado']); ?>"><?php echo $p['Estado']; ?></span>
                  </div>
                  <div class="small text-muted">Subtotal: ₡<?php echo number_format($subtotal,0); ?></div>
                  <div class="fw-bold">Total: ₡<?php echo number_format($p['Total'],0); ?></div>
                  <form method="post" class="mt-2">
                    <input type="hidden" name="id" value="<?php echo $p['Id_pedido']; ?>">
                    <select name="estado" class="form-select form-select-sm" style="min-width:180px" onchange="this.form.submit()">
                      <?php foreach($ESTADOS as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo $s===$p['Estado']?'selected':''; ?>><?php echo $s; ?></option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                  <button class="btn btn-sm btn-outline-secondary mt-2" type="button"
                          data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>">
                    Ver detalle
                  </button>
                </div>
              </div>

              <div class="collapse mt-3" id="<?php echo $collapseId; ?>">
                <?php if(count($det)): ?>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0">
                      <thead><tr><th>Cant.</th><th>Platillo</th><th>Subtotal</th></tr></thead>
                      <tbody>
                        <?php foreach($det as $d): ?>
                          <tr>
                            <td><?php echo (int)$d['Cantidad']; ?></td>
                            <td><?php echo htmlspecialchars($d['Nombre']); ?></td>
                            <td>₡<?php echo number_format($d['Subtotal'],0); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php else: ?>
                  <em class="text-muted">Sin detalle.</em>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
