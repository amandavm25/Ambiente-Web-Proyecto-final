<?php
session_start();
require_once("../include/auth.php");
require_role('negocio');
require_once("../include/conexion.php");

/* Rutas públicas/privadas para la imagen del negocio */
$BASE = "/mamalila_prof";
$BASE_DIR = dirname(__DIR__);
$NEG_DIR = $BASE_DIR . "/uploads/negocios";
$PUBLIC_NEG = $BASE . "/uploads/negocios";
if (!is_dir($NEG_DIR)) {
  @mkdir($NEG_DIR, 0777, true);
}

/* Helper para subir portada */
function subir_portada($file, $dir)
{
  if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) return [null, null];
  $size = (int)$file['size'];
  if ($size > 2 * 1024 * 1024) return [null, "La imagen supera 2MB."];

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime  = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);
  $exts = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
  if (!isset($exts[$mime])) return [null, "Formato no permitido (JPG/PNG/WEBP)."];

  $name = 'neg_' . uniqid('', true) . '.' . $exts[$mime];
  if (!@move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
    return [null, "No se pudo guardar la imagen."];
  }
  return [$name, null];
}

/* Datos del negocio del usuario */
$uid = $_SESSION['usuario']['Id_usuario'] ?? 0;
$st  = $mysqli->prepare("SELECT * FROM negocios WHERE Usuario_id=? LIMIT 1");
$st->bind_param("i", $uid);
$st->execute();
$neg = $st->get_result()->fetch_assoc();
$st->close();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre   = trim($_POST['nombre'] ?? '');
  $tel      = trim($_POST['telefono'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $dir      = trim($_POST['direccion'] ?? '');
  $horario  = trim($_POST['horario'] ?? '');
  $envio    = (float)($_POST['costo_envio'] ?? 0);
  $tiempo   = (int)($_POST['tiempo_minutos'] ?? 20);

  // Subir imagen de negocio 
  $imgUpdated = false;
  if (!empty($_FILES['imagen_negocio']['name'])) {
    [$fileName, $err] = subir_portada($_FILES['imagen_negocio'], $NEG_DIR);
    if (!$err && $fileName) {
      // borrar portada anterior si existía
      if (!empty($neg['Imagen']) && file_exists($NEG_DIR . '/' . $neg['Imagen'])) {
        @unlink($NEG_DIR . '/' . $neg['Imagen']);
      }
      $upI = $mysqli->prepare("UPDATE negocios SET Imagen=? WHERE Id_negocio=? AND Usuario_id=?");
      $upI->bind_param("sii", $fileName, $neg['Id_negocio'], $uid);
      $upI->execute();
      $upI->close();
      $imgUpdated = true;
      // refresca en memoria para mostrar de inmediato
      $neg['Imagen'] = $fileName;
    }
  }

  // Guardar demás datos
  $up = $mysqli->prepare("UPDATE negocios
    SET Nombre=?, Telefono=?, Email=?, Direccion=?, Horario=?, Costo_envio=?, Tiempo_minutos=?
    WHERE Id_negocio=? AND Usuario_id=?");
  $up->bind_param(
    "sssssdiii",
    $nombre,
    $tel,
    $email,
    $dir,
    $horario,
    $envio,
    $tiempo,
    $neg['Id_negocio'],
    $uid
  );
  $up->execute();
  $up->close();

  $_SESSION['flash'] = $imgUpdated ? "Imagen actualizada." : "Perfil actualizado.";
  header("Location: perfil.php");
  exit;
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

        <?php if ($flash): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($flash); ?></div>
        <?php endif; ?>
        <?php if (!empty($neg['Imagen'])): ?>
          <img class="neg-cover mb-3" src="<?php echo $PUBLIC_NEG . '/' . htmlspecialchars($neg['Imagen']); ?>" alt="">
        <?php else: ?>
          <div class="neg-cover placeholder mb-3"></div>
        <?php endif; ?>

        <form method="post" class="row g-3" enctype="multipart/form-data">
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

          <div class="col-md-6">
            <label class="form-label">Imagen de portada (opcional)</label>
            <input type="file" name="imagen_negocio" class="form-control" accept="image/jpeg,image/png,image/webp">
            <small class="text-muted">JPG/PNG/WEBP, máximo 2MB</small>
          </div>

          <div class="col-12">
            <button class="btn btn-primary">Guardar cambios</button>
          </div>
        </form>
      </main>
    </div>
  </div>
</body>

</html>