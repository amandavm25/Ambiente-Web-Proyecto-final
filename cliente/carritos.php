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
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h3 class="mb-0">Carritos</h3>
            <p class="text-muted mb-0">Tienes un carrito por cada tienda.</p>
          </div>
          <button class="btn btn-outline-secondary" id="btnVaciarTodo">
            Vaciar todos
          </button>
        </div>

        <hr class="mt-3">

        <div id="wrap"></div>
      </main>
    </div>
  </div>

  <script>
    // --- Helpers de storage ---
    function _getAll() {
      try { return JSON.parse(localStorage.getItem('mama_cart')) || {}; }
      catch { return {}; }
    }
    function _saveAll(obj) {
      localStorage.setItem('mama_cart', JSON.stringify(obj || {}));
    }
    function clearOneCart(id) {
      const all = _getAll();
      delete all[String(id)];
      _saveAll(all);
    }
    function clearAllCarts() {
      localStorage.removeItem('mama_cart');
    }

    // --- API negocio (para nombres bonitos en la tarjeta) ---
    async function getNegocioName(id) {
      try {
        const r = await fetch('../api/negocio_info.php?id=' + encodeURIComponent(id));
        if (!r.ok) return 'Tienda #' + id;
        const j = await r.json();
        return j?.Nombre || ('Tienda #' + id);
      } catch {
        return 'Tienda #' + id;
      }
    }

    // --- Render principal ---
    async function render() {
      const cont = document.getElementById('wrap');
      cont.innerHTML = '';

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
            <div class="d-flex gap-2">
              <a class="btn btn-primary" href="../checkout.php?negocio=${id}">Ver el carrito</a>
              <a class="btn btn-outline-secondary" href="../menu.php?id=${id}">Ver la tienda</a>
              <button class="btn btn-outline-danger" data-clear="${id}">Vaciar este</button>
            </div>
          </div>
        `;
        cont.appendChild(card);
      }

      // Wire de botones "Vaciar este"
      cont.querySelectorAll('button[data-clear]').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = e.currentTarget.getAttribute('data-clear');
          if (confirm('¿Vaciar el carrito de esta tienda?')) {
            clearOneCart(id);
            render();
          }
        });
      });
    }

    // --- Botón "Vaciar todos" ---
    document.getElementById('btnVaciarTodo')?.addEventListener('click', () => {
      if (confirm('¿Vaciar TODOS los carritos?')) {
        clearAllCarts();
        render();
      }
    });

    // Primer render
    render();
  </script>
</body>
</html>