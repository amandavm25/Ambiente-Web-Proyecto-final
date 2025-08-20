<?php
session_start();
require_once("../include/auth.php");
require_role('cliente');
require_once("../include/conexion.php");

$uid = $_SESSION['usuario']['Id_usuario'] ?? 0;

/* Cargar perfil */
$st = $mysqli->prepare("SELECT Nombre, Email, Telefono, Direccion FROM usuarios WHERE Id_usuario=?");
$st->bind_param("i", $uid);
$st->execute();
$user = $st->get_result()->fetch_assoc();
$st->close();

$msg1 = $msg2 = null;

/* Guardar perfil */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['__form'] ?? '') === 'perfil') {
  $nombre = trim($_POST['nombre'] ?? '');
  $email  = trim($_POST['email'] ?? '');
  $tel    = trim($_POST['telefono'] ?? '');
  $dir    = trim($_POST['direccion'] ?? '');

  $up = $mysqli->prepare("UPDATE usuarios SET Nombre=?, Email=?, Telefono=?, Direccion=? WHERE Id_usuario=?");
  $up->bind_param("ssssi", $nombre, $email, $tel, $dir, $uid);
  $up->execute();
  $up->close();

  $_SESSION['usuario']['Nombre'] = $nombre;
  $msg1 = "Perfil actualizado.";
}

/* Cambiar contraseña */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['__form'] ?? '') === 'pass') {
  $act = $_POST['actual'] ?? '';
  $n1 = $_POST['nueva1'] ?? '';
  $n2 = $_POST['nueva2'] ?? '';
  if ($n1 !== $n2) {
    $msg2 = "La confirmación no coincide.";
  } else {
    $q = $mysqli->prepare("SELECT Contrasenia FROM usuarios WHERE Id_usuario=?");
    $q->bind_param("i", $uid);
    $q->execute();
    $hash = $q->get_result()->fetch_assoc()['Contrasenia'] ?? '';
    $q->close();
    if (!$hash || !password_verify($act, $hash)) {
      $msg2 = "Contraseña actual incorrecta.";
    } else {
      $new = password_hash($n1, PASSWORD_BCRYPT);
      $w = $mysqli->prepare("UPDATE usuarios SET Contrasenia=? WHERE Id_usuario=?");
      $w->bind_param("si", $new, $uid);
      $w->execute();
      $w->close();
      $msg2 = "Contraseña actualizada.";
    }
  }
  // recargar datos visibles
  $st = $mysqli->prepare("SELECT Nombre, Email, Telefono, Direccion FROM usuarios WHERE Id_usuario=?");
  $st->bind_param("i", $uid);
  $st->execute();
  $user = $st->get_result()->fetch_assoc();
  $st->close();
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
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

        <?php if ($msg1): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg1); ?></div><?php endif; ?>

        <form method="post" class="row g-3">
          <input type="hidden" name="__form" value="perfil">
          <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input class="form-control" name="nombre" value="<?php echo htmlspecialchars($user['Nombre'] ?? ''); ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Teléfono</label>
            <input class="form-control" name="telefono" value="<?php echo htmlspecialchars($user['Telefono'] ?? ''); ?>">
          </div>
          <div class="col-md-12">
            <label class="form-label">Dirección</label>
            <input class="form-control" name="direccion" value="<?php echo htmlspecialchars($user['Direccion'] ?? ''); ?>">
          </div>
          <div class="col-12">
            <button class="btn btn-primary">Guardar cambios</button>
          </div>
        </form>

        <hr class="my-4">

        <h5>Cambiar contraseña</h5>
        <?php if ($msg2): ?><div class="alert alert-<?php echo strpos($msg2, 'incorrecta') !== false || strpos($msg2, 'no coincide') !== false ? 'danger' : 'success'; ?>"><?php echo htmlspecialchars($msg2); ?></div><?php endif; ?>
        <form method="post" class="row g-3">
          <input type="hidden" name="__form" value="pass">
          <div class="col-md-4">
            <label class="form-label">Actual Contraseña</label>
            <input type="password" class="form-control" name="actual" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Nueva Contraseña</label>
            <input type="password" class="form-control" name="nueva1" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Repetir nueva contraseña</label>
            <input type="password" class="form-control" name="nueva2" required>
          </div>
          <div class="col-12">
            <button class="btn btn-outline-primary">Actualizar contraseña</button>
          </div>
        </form>
      </main>
    </div>
  </div>
</body>

</html>