<?php
session_start();
require_once("include/auth.php");
require_login();
require_once("include/conexion.php");
$BASE = "/mamalila_prof";
$PUBLIC_NEG = $BASE . "/uploads/negocios";

$q = trim($_GET['q'] ?? '');
$grupos = [];

if ($q !== '' && mb_strlen($q) >= 2) {
  $like = "%$q%";
  $sql = "SELECT n.Id_negocio, n.Nombre AS Negocio, n.Horario, n.Costo_envio, n.Tiempo_minutos, n.Imagen AS ImgNeg,
                 p.Id_platillo, p.Nombre AS Platillo, p.Descripcion, p.Precio, p.Imagen AS ImgPlat
          FROM platillos p
          JOIN negocios n ON n.Id_negocio = p.Negocio_id
          WHERE p.Disponible=1 AND (p.Nombre LIKE ? OR p.Descripcion LIKE ?)
          ORDER BY n.Nombre, p.Nombre";
  $st = $mysqli->prepare($sql);
  $st->bind_param("ss", $like, $like);
  $st->execute();
  $res = $st->get_result();
  while ($r = $res->fetch_assoc()) {
    $nid = (int)$r['Id_negocio'];
    if (!isset($grupos[$nid])) {
      $grupos[$nid] = [
        'Id_negocio'     => $nid,
        'Negocio'        => $r['Negocio'],
        'Horario'        => $r['Horario'],
        'Costo_envio'    => $r['Costo_envio'],
        'Tiempo_minutos' => $r['Tiempo_minutos'],
        'ImgNeg'         => $r['ImgNeg'],
        'items'          => []
      ];
    }
    $grupos[$nid]['items'][] = [
      'Id_platillo' => (int)$r['Id_platillo'],
      'Platillo'    => $r['Platillo'],
      'Descripcion' => $r['Descripcion'],
      'Precio'      => (float)$r['Precio'],
      'ImgPlat'     => $r['ImgPlat']
    ];
  }
  $st->close();
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
  <title>Buscar platillos</title>
  <style>
    .cover {
      height: 140px;
      object-fit: cover;
      border-top-left-radius: 16px;
      border-top-right-radius: 16px;
    }

    .placeholder-cover {
      height: 140px;
      border-top-left-radius: 16px;
      border-top-right-radius: 16px;
      background: #eef3ee;
    }

    .thumb {
      width: 48px;
      height: 48px;
      border-radius: 8px;
      object-fit: cover;
      background: #f3f3f3;
    }

    .thumb--placeholder {
      width: 48px;
      height: 48px;
      border-radius: 8px;
      background: #e9f2ea;
    }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php include("include/menu.php"); ?>
      <main class="col-md-9 p-4">
        <h3>Buscar platillos</h3>
        <form method="get" class="row g-2 mb-3">
          <div class="col-md-9">
            <input name="q" class="form-control" placeholder="Ej: tacos, ramen, hamburguesa..." value="<?php echo htmlspecialchars($q); ?>">
          </div>
          <div class="col-md-3">
            <button class="btn btn-primary w-100">Buscar</button>
          </div>
        </form>

        <?php if ($q === ''): ?>
          <p class="text-muted">Escribe al menos 2 caracteres para buscar.</p>
        <?php elseif (mb_strlen($q) < 2): ?>
          <p class="text-muted">Escribe al menos 2 caracteres.</p>
        <?php elseif (!count($grupos)): ?>
          <p class="text-muted">No hay coincidencias.</p>
        <?php else: ?>
          <?php foreach ($grupos as $g): ?>
            <div class="card mb-3">
              <?php if (!empty($g['ImgNeg'])): ?>
                <img
                  class="neg-cover<?php echo preg_match('/\.svg$/i', $g['ImgNeg']) ? ' contain' : ''; ?>"
                  src="<?php echo $PUBLIC_NEG . '/' . htmlspecialchars($g['ImgNeg']); ?>"
                  alt="">
              <?php else: ?>
                <div class="neg-cover placeholder"></div>
              <?php endif; ?>
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($g['Negocio']); ?></h5>
                    <div class="small text-muted">
                      Envío: ₡<?php echo number_format($g['Costo_envio'] ?? 0, 0); ?> • <?php echo (int)($g['Tiempo_minutos'] ?? 20); ?> min
                    </div>
                  </div>
                  <a class="btn btn-outline-primary" href="menu.php?id=<?php echo $g['Id_negocio']; ?>">Ver menú</a>
                </div>

                <div class="table-responsive mt-2">
                  <table class="table table-sm mb-0 align-middle">
                    <tbody>
                      <?php foreach ($g['items'] as $it): ?>
                        <tr>
                          <td style="width:56px">
                            <?php if (!empty($it['ImgPlat'])): ?>
                              <img class="thumb" src="uploads/platillos/<?php echo htmlspecialchars($it['ImgPlat']); ?>" alt="">
                            <?php else: ?>
                              <div class="thumb thumb--placeholder"></div>
                            <?php endif; ?>
                          </td>
                          <td>
                            <div class="fw-semibold"><?php echo htmlspecialchars($it['Platillo']); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars($it['Descripcion'] ?? ''); ?></div>
                          </td>
                          <td class="text-end">₡<?php echo number_format($it['Precio'], 0); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
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