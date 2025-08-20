<?php
session_start();
require_once("../include/auth.php");
require_role('cliente');
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/app.css" rel="stylesheet">
  <title>Mi carrito</title>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <?php include("../include/menu.php"); ?>
      <main class="col-md-9 p-4">
        <h3>Carritos</h3>
        <p class="text-muted">Tienes un carrito por cada tienda.</p>
        <div id="wrap"></div>
      </main>
    </div>
  </div>

  <script>
    function _getAll() {
      try {
        return JSON.parse(localStorage.getItem('mama_cart')) || {};
      } catch (e) {
        return {}
      }
    }
    async function getNegocioName(id) {
        const r = await fetch('../api/negocio_info.php?id=' + id);
        if (!r.ok) return 'Tienda #' + id;
        const j = await r.json();
        return j?.Nombre || ('Tienda #' + id);
      }
      (async function() {
        const cont = document.getElementById('wrap');
        const all = _getAll();
        const ids = Object.keys(all);
        if (!ids.length) {
          cont.innerHTML = '<p class="text-muted">No tienes carritos activos.</p>';
          return;
        }
        for (const id of ids) {
          const nombre = await getNegocioName(id);
          const items = all[id] || [];
          const total = items.reduce((a, it) => a + (Number(it.price) || 0) * (Number(it.qty) || 0), 0);
          const card = document.createElement('div');
          card.className = 'card mb-2';
          card.innerHTML = `
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-semibold">${nombre}</div>
          <div class="small text-muted">${items.length} artículo(s) • ₡${total.toLocaleString()}</div>
        </div>
        <div>
          <a class="btn btn-primary me-2" href="../checkout.php?negocio=${id}">Ver el carrito</a>
          <a class="btn btn-outline-secondary" href="../menu.php?id=${id}">Ver la tienda</a>
        </div>
      </div>`;
          cont.appendChild(card);
        }
      })();
  </script>
</body>

</html>