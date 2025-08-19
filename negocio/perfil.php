<?php
session_start();
require_once("../include/auth.php"); require_role('negocio');
require_once("../include/conexion.php");

$uid = $_SESSION['usuario']['Id_usuario'] ?? 0;
$st  = $mysqli->prepare("SELECT * FROM negocios WHERE Usuario_id=? LIMIT 1");
$st->bind_param("i",$uid); $st->execute();
$neg = $st->get_result()->fetch_assoc(); $st->close();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $nombre   = trim($_POST['nombre'] ?? '');
  $tel      = trim($_POST['telefono'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $dir      = trim($_POST['direccion'] ?? '');
  $horario  = trim($_POST['horario'] ?? '');
  $envio    = (float)($_POST['costo_envio'] ?? 0);
  $tiempo   = (int)($_POST['tiempo_minutos'] ?? 20);

  $up=$mysqli->prepare("UPDATE negocios
    SET Nombre=?, Telefono=?, Email=?, Direccion=?, Horario=?, Costo_envio=?, Tiempo_minutos=?
    WHERE Id_negocio=? AND Usuario_id=?");
  $up->bind_param("sssssdiii",
    $nombre, $tel, $email, $dir, $horario, $envio, $tiempo,
    $neg['Id_negocio'], $uid
  );
  $up->execute(); $up->close();

  header("Location: perfil.php"); exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/app.css" rel="stylesheet">
  <title>Mi perfil</title>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <?php include("../include/menu.php"); ?>
    <main class="col-md-9 p-4">
      <h3>Mi perfil</h3>

      <form method="post" class="mt-3">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nombre del negocio</label>
            <input class="form-control" name="nombre" value="<?php echo htmlspecialchars($neg['Nombre'] ?? ''); ?>" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Teléfono</label>
            <input class="form-control" name="telefono" value="<?php echo htmlspecialchars($neg['Telefono'] ?? ''); ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($neg['Email'] ?? ''); ?>">
          </div>

          <div class="col-md-12">
            <label class="form-label">Dirección</label>
            <input class="form-control" name="direccion" value="<?php echo htmlspecialchars($neg['Direccion'] ?? ''); ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Horario</label>
            <input class="form-control" name="horario" value="<?php echo htmlspecialchars($neg['Horario'] ?? ''); ?>">
          </div>

          <div class="col-md-3">
            <label class="form-label">Costo de envío (₡)</label>
            <input type="number" min="0" step="1" class="form-control" name="costo_envio"
                   value="<?php echo (int)($neg['Costo_envio'] ?? 0); ?>">
          </div>

          <div class="col-md-3">
            <label class="form-label">Tiempo estimado (min)</label>
            <input type="number" min="5" step="5" class="form-control" name="tiempo_minutos"
                   value="<?php echo (int)($neg['Tiempo_minutos'] ?? 20); ?>">
          </div>
        </div>

        <div class="mt-3">
          <button class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
    </main>
  </div>
</div>
</body>
</html>
