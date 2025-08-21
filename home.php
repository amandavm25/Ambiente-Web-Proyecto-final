<?php
session_start();
require_once("include/auth.php");
require_login();
$u = $_SESSION['usuario'];
if ($u['Rol'] === 'negocio') {
  header("Location: negocio/dashboard.php");
  exit();
}
require_once("include/conexion.php");

$BASE = "/mamalila_prof";
$PUBLIC_NEG = $BASE . "/uploads/negocios";

$uid = $_SESSION['usuario']['Id_usuario'];

// Negocios
$negocios = [];
$res = $mysqli->query("SELECT Id_negocio, Nombre, Horario, Costo_envio, Tiempo_minutos, Imagen FROM negocios ORDER BY Nombre");
while ($row = $res->fetch_assoc()) {
  $negocios[] = $row;
}
$res->close();

// Favoritos del usuario (para pintar el corazón)
$favs = [];
$st = $mysqli->prepare("SELECT Negocio_id FROM favoritos WHERE Usuario_id=?");
$st->bind_param("i", $uid);
$st->execute();
$r = $st->get_result();
while ($x = $r->fetch_row()) {
  $favs[(int)$x[0]] = true;
}
$st->close();
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
          <?php foreach ($negocios as $n): ?>
            <div class="col-md-4">
              <div class="card h-100">
                <?php
                  $img   = trim($n['Imagen'] ?? '');
                  $idNeg = (int)$n['Id_negocio'];
                  $isLogo = preg_match('/\.svg$/i', $img); 
                ?>
                <?php if ($img !== ''): ?>
                  <img
                    class="neg-cover<?php echo $isLogo ? ' contain' : ''; ?> mb-2"
                    src="<?php echo $PUBLIC_NEG . '/' . $idNeg . '/' . htmlspecialchars($img); ?>?v=<?php echo time(); ?>"
                    alt="Portada de <?php echo htmlspecialchars($n['Nombre']); ?>">
                <?php else: ?>
                  <div class="neg-cover placeholder mb-2"></div>
                <?php endif; ?>

                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($n['Nombre']); ?></h5>
                    <?php $isFav = !empty($favs[$n['Id_negocio']]); ?>
                    <button type="button"
                      class="btn btn-sm <?php echo $isFav ? 'btn-primary' : 'btn-outline-primary'; ?>"
                      data-neg="<?php echo $n['Id_negocio']; ?>"
                      onclick="toggleFav(this)">
                      <span class="fav-text"><?php echo $isFav ? '❤' : '♡'; ?></span>
                    </button>
                  </div>

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
  <script>
  async function toggleFav(btn) {
    const id  = btn.getAttribute('data-neg');
    const url = (location.pathname.includes('/cliente/'))
                ? 'favorito_toggle.php'
                : 'cliente/favorito_toggle.php';

    btn.disabled = true;

    try {
      const r = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ negocio: id })
      });

      let j;
      try { j = await r.json(); }
      catch {
        const txt = await r.text();
        console.error('Respuesta no-JSON:', txt);
        alert('Error al marcar favorito (respuesta inválida).');
        btn.disabled = false;
        return;
      }

      if (!j.ok) {
        console.warn('toggleFav fallo:', j);
        alert(j.msg || 'No se pudo actualizar favorito.');
        btn.disabled = false;
        return;
      }

      btn.classList.toggle('btn-primary', j.fav);
      btn.classList.toggle('btn-outline-primary', !j.fav);
      const span = btn.querySelector('.fav-text');
      if (span) span.textContent = j.fav ? '❤' : '♡';

      if (!j.fav && location.pathname.includes('/cliente/favoritos')) {
        const col = btn.closest('.col-md-4');
        col?.remove();

        if (!document.querySelector('.row.g-3 .col-md-4')) {
          const main = document.querySelector('main');
          main.insertAdjacentHTML('beforeend', '<p class="text-muted">Aún no tienes negocios favoritos.</p>');
        }
      }
    } catch (e) {
      console.error(e);
      alert('No se pudo conectar con el servidor.');
    } finally {
      btn.disabled = false;
    }
  }
</script>
</body>
</html>
