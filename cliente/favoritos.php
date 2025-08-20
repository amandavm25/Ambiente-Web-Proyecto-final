<?php
session_start();
require_once("../include/auth.php");
require_role('cliente');
require_once("../include/conexion.php");

$uid = $_SESSION['usuario']['Id_usuario'];
$lista = [];
$q = $mysqli->prepare("
  SELECT n.Id_negocio, n.Nombre, n.Horario, n.Costo_envio, n.Tiempo_minutos
  FROM favoritos f JOIN negocios n ON n.Id_negocio=f.Negocio_id
  WHERE f.Usuario_id=?
  ORDER BY n.Nombre
");
$q->bind_param("i", $uid);
$q->execute();
$r = $q->get_result();
while ($row = $r->fetch_assoc()) {
  $lista[] = $row;
}
$q->close();
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/app.css" rel="stylesheet">
  <title>Favoritos</title>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php include("../include/menu.php"); ?>
      <main class="col-md-9 p-4">
        <h3>Favoritos</h3>
        <?php if (!count($lista)): ?>
          <p class="text-muted">Aún no tienes negocios favoritos.</p>
        <?php else: ?>
          <div class="row g-3">
            <?php foreach ($lista as $n): ?>
              <div class="col-md-4">
                <div class="card h-100">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                      <h5 class="card-title mb-1"><?php echo htmlspecialchars($n['Nombre']); ?></h5>

                      <!-- Aquí siempre arranca como favorito -->
                      <button type="button"
                        class="btn btn-sm btn-primary"
                        data-neg="<?php echo $n['Id_negocio']; ?>"
                        onclick="toggleFav(this)">
                        <span class="fav-text">❤</span>
                      </button>
                    </div>

                    <div class="small text-muted mb-1">
                      Costo de envío: ₡<?php echo number_format($n['Costo_envio'] ?? 0, 0); ?>
                      • <?php echo (int)($n['Tiempo_minutos'] ?? 20); ?> min
                    </div>

                    <p class="card-text"><?php echo htmlspecialchars($n['Horario'] ?? ''); ?></p>
                    <a class="btn btn-outline-primary" href="../menu.php?id=<?php echo $n['Id_negocio']; ?>">Ver menú</a>
                  </div>

                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </main>
    </div>
  </div>
  <script>
    function toggleFav(btn) {
      const id = btn.getAttribute('data-neg');
      fetch('favorito_toggle.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            negocio: id
          })
        })
        .then(r => r.json())
        .then(j => {
          if (!j.ok) return;
          btn.classList.toggle('btn-primary', j.fav);
          btn.classList.toggle('btn-outline-primary', !j.fav);
          btn.querySelector('.fav-text').textContent = j.fav ? '❤' : '♡';
          if (!j.fav) {
            const col = btn.closest('.col-md-4');
            col?.remove();
            if (!document.querySelector('.row.g-3 .col-md-4')) {
              const main = document.querySelector('main');
              main.insertAdjacentHTML('beforeend', '<p class="text-muted">Aún no tienes negocios favoritos.</p>');
            }
          }
        })
        .catch(() => {});
    }
  </script>
</body>

</html>