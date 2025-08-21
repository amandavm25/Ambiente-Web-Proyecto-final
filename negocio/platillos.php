<?php
session_start();
require_once("../include/auth.php");
require_role('negocio');
require_once("../include/conexion.php");

/* === Id del negocio del usuario logueado === */
$uid = (int)($_SESSION['usuario']['Id_usuario'] ?? 0);
if ($uid <= 0) { die("Sesión inválida."); }

$st = $mysqli->prepare("SELECT Id_negocio FROM negocios WHERE Usuario_id=? LIMIT 1");
$st->bind_param("i", $uid);
$st->execute();
$row = $st->get_result()->fetch_assoc();
$st->close();

$negocio_id = $row ? (int)$row['Id_negocio'] : 0;
if ($negocio_id <= 0) {
  die("No se encontró un negocio asociado a tu cuenta.");
}

/* === Rutas (ya con $negocio_id definido) === */
$BASE          = "/mamalila_prof";                   
$BASE_DIR      = dirname(__DIR__);
$UPLOAD_DIR    = $BASE_DIR . "/uploads/platillos/" . $negocio_id;
$PUBLIC_UPLOAD = $BASE     . "/uploads/platillos/" . $negocio_id;

if (!is_dir($UPLOAD_DIR)) { @mkdir($UPLOAD_DIR, 0777, true); }

/* === Helpers de imagen === */
function subir_imagen($file, $UPLOAD_DIR)
{
  if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) return [null, null];

  $tmp  = $file['tmp_name'];
  $size = (int)$file['size'];
  if ($size > 2 * 1024 * 1024) return [null, "La imagen supera 2MB."];

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime  = finfo_file($finfo, $tmp);
  finfo_close($finfo);

  $map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
  if (!isset($map[$mime])) return [null, "Formato no permitido (solo JPG/PNG/WEBP)."];

  $ext  = $map[$mime];
  $name = uniqid('pl_', true) . "." . $ext;

  if (!@move_uploaded_file($tmp, $UPLOAD_DIR . "/" . $name)) {
    return [null, "No se pudo guardar la imagen."];
  }
  return [$name, null];
}

/* === Acciones POST === */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = $_POST['accion'] ?? '';

  if ($accion === 'crear') {
    $nombre = trim($_POST['nombre'] ?? '');
    $desc   = trim($_POST['descripcion'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0);
    $disp   = (int)($_POST['disponible'] ?? 1);
    $imgName = null;

    if (!empty($_FILES['imagen']['name'])) {
      [$imgName, $err] = subir_imagen($_FILES['imagen'], $UPLOAD_DIR);
      // podrías manejar $err para mostrar feedback
    }

    if ($nombre !== '' && $precio > 0) {
      $sql = "INSERT INTO platillos (Negocio_id, Nombre, Descripcion, Precio, Disponible, Imagen)
              VALUES (?,?,?,?,?,?)";
      $ins = $mysqli->prepare($sql);
      $ins->bind_param("issdis", $negocio_id, $nombre, $desc, $precio, $disp, $imgName);
      $ins->execute();
      $ins->close();
    }

  } elseif ($accion === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
      // Borrar archivo si existe
      $q = $mysqli->prepare("SELECT Imagen FROM platillos WHERE Id_platillo=? AND Negocio_id=?");
      $q->bind_param("ii", $id, $negocio_id);
      $q->execute();
      $img = $q->get_result()->fetch_assoc()['Imagen'] ?? null;
      $q->close();

      if ($img && file_exists($UPLOAD_DIR . "/" . $img)) {
        @unlink($UPLOAD_DIR . "/" . $img);
      }

      // Borrar registro
      $del = $mysqli->prepare("DELETE FROM platillos WHERE Id_platillo=? AND Negocio_id=?");
      $del->bind_param("ii", $id, $negocio_id);
      $del->execute();
      $del->close();
    }

  } elseif ($accion === 'editar') {
    $id     = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $desc   = trim($_POST['descripcion'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0);
    $disp   = (int)($_POST['disponible'] ?? 1);

    if ($id > 0 && $nombre !== '' && $precio > 0) {
      // Traer imagen actual
      $q = $mysqli->prepare("SELECT Imagen FROM platillos WHERE Id_platillo=? AND Negocio_id=?");
      $q->bind_param("ii", $id, $negocio_id);
      $q->execute();
      $oldImg = $q->get_result()->fetch_assoc()['Imagen'] ?? null;
      $q->close();

      $newImg = $oldImg;
      if (!empty($_FILES['imagen_edit']['name'])) {
        [$tmpName, $err] = subir_imagen($_FILES['imagen_edit'], $UPLOAD_DIR);
        if (!$err && $tmpName) { $newImg = $tmpName; }
      }

      // Actualizar (Disponible es entero -> 'i')
      $sql = "UPDATE platillos
              SET Nombre=?, Descripcion=?, Precio=?, Disponible=?, Imagen=?
              WHERE Id_platillo=? AND Negocio_id=?";
      $up = $mysqli->prepare($sql);
      $up->bind_param("ssdisii", $nombre, $desc, $precio, $disp, $newImg, $id, $negocio_id);
      $up->execute();
      $up->close();

      // Si se reemplazó imagen, borra la anterior
      if ($newImg !== $oldImg && $oldImg && file_exists($UPLOAD_DIR . "/" . $oldImg)) {
        @unlink($UPLOAD_DIR . "/" . $oldImg);
      }
    }
  }

  header("Location: platillos.php");
  exit();
}

/* === Listado de platillos del negocio === */
$platos = [];
$st = $mysqli->prepare("SELECT Id_platillo, Nombre, Descripcion, Precio, Disponible, Imagen
                        FROM platillos WHERE Negocio_id=? ORDER BY Id_platillo DESC");
$st->bind_param("i", $negocio_id);
$st->execute();
$res = $st->get_result();
while ($r = $res->fetch_assoc()) { $platos[] = $r; }
$st->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/app.css" rel="stylesheet">
  <title>Platillos</title>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <?php include("../include/menu.php"); ?>
      <main class="col-md-9 p-4">
        <h3>Platillos</h3>

        <!-- Crear -->
        <form method="post" class="row g-2 mt-2 mb-3" enctype="multipart/form-data">
          <input type="hidden" name="accion" value="crear">
          <div class="col-md-3">
            <input class="form-control" name="nombre" placeholder="Nombre" required>
          </div>
          <div class="col-md-3">
            <input class="form-control" name="descripcion" placeholder="Descripción">
          </div>
          <div class="col-md-2">
            <input class="form-control" name="precio" type="number" step="0.01" min="0.01" placeholder="Precio" required>
          </div>
          <div class="col-md-2">
            <select class="form-select" name="disponible">
              <option value="1">Disponible</option>
              <option value="0">No</option>
            </select>
          </div>
          <div class="col-md-2">
            <input type="file" class="form-control" name="imagen" accept="image/jpeg,image/png,image/webp">
          </div>
          <div class="col-12 text-end">
            <button class="btn btn-primary">Agregar</button>
          </div>
        </form>

        <!-- Tabla -->
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th style="width:70px"></th>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Precio</th>
              <th>Disponible</th>
              <th style="width:160px"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($platos as $p): ?>
              <tr>
                <td style="width:70px">
                  <?php
                  $img = trim($p['Imagen'] ?? '');
                  $src = '';
                  if ($img !== '') {
                    // 1) ruta por-negocio
                    $pathNegocio = $UPLOAD_DIR . '/' . $img;
                    if (file_exists($pathNegocio)) {
                      $src = $PUBLIC_UPLOAD . '/' . rawurlencode($img);
                    } else {
                      // 2) fallback a carpeta global (para registros antiguos)
                      $pathGlobal = $BASE_DIR . '/uploads/platillos/' . $img;
                      if (file_exists($pathGlobal)) {
                        $src = $BASE . '/uploads/platillos/' . rawurlencode($img);
                      }
                    }
                  }
                  if ($src) {
                    echo '<img class="thumb" src="'.$src.'" alt="">';
                  } else {
                    echo '<div class="thumb thumb--placeholder"></div>';
                  }
                  ?>
                </td>
                <td><?php echo htmlspecialchars($p['Nombre']); ?></td>
                <td><?php echo htmlspecialchars($p['Descripcion'] ?? ''); ?></td>
                <td>₡<?php echo number_format((float)$p['Precio'], 2); ?></td>
                <td><?php echo ((int)$p['Disponible'] ? 'Sí' : 'No'); ?></td>
                <td class="text-end">
                  <button
                    class="btn btn-sm btn-outline-primary me-1"
                    data-bs-toggle="modal" data-bs-target="#modalEditar"
                    data-id="<?php echo (int)$p['Id_platillo']; ?>"
                    data-nombre="<?php echo htmlspecialchars($p['Nombre'], ENT_QUOTES); ?>"
                    data-desc="<?php echo htmlspecialchars($p['Descripcion'] ?? '', ENT_QUOTES); ?>"
                    data-precio="<?php echo (float)$p['Precio']; ?>"
                    data-disp="<?php echo (int)$p['Disponible']; ?>"
                    data-img="<?php echo htmlspecialchars($p['Imagen'] ?? '', ENT_QUOTES); ?>">
                    Editar
                  </button>

                  <form method="post" style="display:inline" onsubmit="return confirm('¿Eliminar platillo?')">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?php echo (int)$p['Id_platillo']; ?>">
                    <button class="btn btn-sm btn-danger">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </main>
    </div>
  </div>

  <!-- Modal Editar -->
  <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content" method="post" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="id" id="edit_id">

        <div class="modal-header">
          <h5 class="modal-title">Editar platillo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Nombre</label>
            <input class="form-control" name="nombre" id="edit_nombre" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Descripción</label>
            <input class="form-control" name="descripcion" id="edit_desc">
          </div>
          <div class="row g-2">
            <div class="col-md-6 mb-2">
              <label class="form-label">Precio</label>
              <input class="form-control" name="precio" id="edit_precio" type="number" step="0.01" min="0.01" required>
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label">Disponible</label>
              <select class="form-select" name="disponible" id="edit_disp">
                <option value="1">Sí</option>
                <option value="0">No</option>
              </select>
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label">Imagen (opcional)</label>
            <input type="file" class="form-control" name="imagen_edit" accept="image/jpeg,image/png,image/webp">
            <div class="mt-2">
              <img id="edit_preview" class="thumb" alt="" style="display:none">
            </div>
            <small class="text-muted">Si subes una imagen nueva, reemplaza la anterior.</small>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const PUBLIC_UPLOAD = "<?php echo $PUBLIC_UPLOAD; ?>";
    const modal = document.getElementById('modalEditar');
    modal.addEventListener('show.bs.modal', function(event) {
      const btn    = event.relatedTarget;
      const id     = btn.getAttribute('data-id');
      const nombre = btn.getAttribute('data-nombre');
      const desc   = btn.getAttribute('data-desc');
      const precio = btn.getAttribute('data-precio');
      const disp   = btn.getAttribute('data-disp');
      const img    = btn.getAttribute('data-img');

      document.getElementById('edit_id').value     = id;
      document.getElementById('edit_nombre').value = nombre || '';
      document.getElementById('edit_desc').value   = desc || '';
      document.getElementById('edit_precio').value = precio || 0;
      document.getElementById('edit_disp').value   = (String(disp) === '0') ? '0' : '1';

      const prev = document.getElementById('edit_preview');
      if (img) {
        prev.src = PUBLIC_UPLOAD + '/' + img;
        prev.style.display = 'block';
      } else {
        prev.removeAttribute('src');
        prev.style.display = 'none';
      }
    });
  </script>
</body>
</html>
