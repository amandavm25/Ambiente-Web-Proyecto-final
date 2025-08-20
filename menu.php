<?php
session_start();
require_once("include/auth.php");
require_login();
require_once("include/conexion.php");

// Id del negocio
$negocio_id = (int)($_GET['id'] ?? 0);
if ($negocio_id <= 0) {
  die("Negocio inválido");
}

// Info del negocio 
$st = $mysqli->prepare("SELECT Id_negocio, Nombre, Horario, Costo_envio, Tiempo_minutos FROM negocios WHERE Id_negocio=?");
$st->bind_param("i", $negocio_id);
$st->execute();
$neg = $st->get_result()->fetch_assoc();
$st->close();
if (!$neg) {
  die("Negocio no encontrado");
}

// Platillos disponibles
$plats = [];
$st = $mysqli->prepare("SELECT Id_platillo, Nombre, Descripcion, Precio, Imagen FROM platillos WHERE Negocio_id=? AND Disponible=1 ORDER BY Nombre");
$st->bind_param("i", $negocio_id);
$st->execute();
$r = $st->get_result();
while ($row = $r->fetch_assoc()) {
  $plats[] = $row;
}
$st->close();

// Rutas para imágenes de platillos subidas por el negocio
$BASE = "/mamalila_prof";
$PUBLIC_UPLOAD = $BASE . "/uploads/platillos";
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
  <title><?php echo htmlspecialchars($neg['Nombre']); ?> – Menú</title>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <?php include("include/menu.php"); ?>

      <main class="col-md-9 p-4">
        <a href="home.php">&larr; Volver al inicio</a>
        <h3 class="mt-2"><?php echo htmlspecialchars($neg['Nombre']); ?></h3>
        <div class="text-muted mb-3">
          Costo de envío: ₡<?php echo number_format((float)($neg['Costo_envio'] ?? 0), 0); ?>
          • Tiempo estimado: <?php echo (int)($neg['Tiempo_minutos'] ?? 20); ?> min
          <?php if (!empty($neg['Horario'])): ?> • Horario: <?php echo htmlspecialchars($neg['Horario']); ?><?php endif; ?>
        </div>

        <div class="row g-3">
          <div class="col-lg-8">
            <div class="row g-3">
              <?php if (!count($plats)): ?>
                <p class="text-muted">Este negocio aún no tiene platillos disponibles.</p>
              <?php else: ?>
                <?php foreach ($plats as $p): ?>
                  <div class="col-md-6">
                    <div class="card h-100">
                      <?php if (!empty($p['Imagen'])): ?>
                        <img class="card-img-top" style="object-fit:cover; height:180px"
                          src="<?php echo $PUBLIC_UPLOAD . '/' . htmlspecialchars($p['Imagen']); ?>"
                          alt="img">
                      <?php endif; ?>
                      <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($p['Nombre']); ?></h5>
                        <div class="small text-muted mb-2"><?php echo htmlspecialchars($p['Descripcion'] ?? ''); ?></div>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                          <div class="fw-semibold">₡<?php echo number_format($p['Precio'], 0); ?></div>

                          <!-- Botón Agregar: usa SIEMPRE addToCart de cart.js -->
                          <button
                            class="btn btn-sm btn-outline-primary"
                            data-id="<?php echo (int)$p['Id_platillo']; ?>"
                            data-name="<?php echo htmlspecialchars($p['Nombre'], ENT_QUOTES); ?>"
                            data-price="<?php echo (float)$p['Precio']; ?>"
                            onclick="addToCart({
                              id: Number(this.dataset.id),
                              name: this.dataset.name,
                              price: Number(this.dataset.price),
                              negocioId: NEGOCIO,
                              qty: 1
                            }); renderCart();">
                            Agregar
                          </button>

                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <!-- Carrito lateral -->
          <div class="col-lg-4">
            <div class="card position-sticky" style="top:1rem">
              <div class="card-body">
                <h5 class="card-title">Tu carrito</h5>
                <div id="cartEmpty" class="text-muted">No hay productos agregados.</div>
                <div id="cartBox" style="display:none">
                  <div class="table-responsive">
                    <table class="table table-sm align-middle">
                      <thead>
                        <tr>
                          <th>Producto</th>
                          <th class="text-center">Cant.</th>
                          <th class="text-end">Subtotal</th>
                        </tr>
                      </thead>
                      <tbody id="cartRows"></tbody>
                      <tfoot>
                        <tr>
                          <th colspan="2" class="text-end">Subtotal</th>
                          <th class="text-end" id="cartSubtotal">₡0</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  <div class="d-flex gap-2">
                    <a class="btn btn-primary flex-fill" href="checkout.php?negocio=<?php echo $negocio_id; ?>">
                      Ir al carrito
                    </a>
                    <button class="btn btn-outline-secondary" type="button" onclick="vaciar()">Vaciar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div><!-- /row -->
      </main>
    </div>
  </div>

  <script src="assets/js/cart.js"></script>
  <script>
    const NEGOCIO = <?php echo (int)$negocio_id; ?>;

    function vaciar() {
      if (!confirm('¿Vaciar carrito?')) return;
      clearCartFor(NEGOCIO);
      renderCart();
    }

    function renderCart() {
      const items  = getCartFor(NEGOCIO) || [];
      const rows   = document.getElementById('cartRows');
      const box    = document.getElementById('cartBox');
      const empty  = document.getElementById('cartEmpty');
      const subEl  = document.getElementById('cartSubtotal');

      rows.innerHTML = '';
      if (!items.length) {
        box.style.display   = 'none';
        empty.style.display = 'block';
        subEl.textContent   = '₡0';
        return;
      }
      box.style.display   = 'block';
      empty.style.display = 'none';

      let subtotal = 0;
      for (const it of items) {
        const price = Number(it.price) || 0;
        const qty   = Number(it.qty)   || 0;
        const sub   = price * qty;
        subtotal   += sub;

        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${it.name ?? ('Producto #' + it.id)}</td>
          <td class="text-center">
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-secondary" data-act="dec">-</button>
              <button type="button" class="btn btn-light" disabled>${qty}</button>
              <button type="button" class="btn btn-outline-secondary" data-act="inc">+</button>
            </div>
          </td>
          <td class="text-end">₡${sub.toLocaleString()}</td>
        `;
        tr.querySelector('[data-act="dec"]').addEventListener('click', () => setQtyDelta(it.id, -1));
        tr.querySelector('[data-act="inc"]').addEventListener('click', () => setQtyDelta(it.id, +1));
        rows.appendChild(tr);
      }
      subEl.textContent = '₡' + subtotal.toLocaleString();
    }

    function setQtyDelta(id, delta) {
      const items = getCartFor(NEGOCIO) || [];
      const i = items.findIndex(x => String(x.id) === String(id));
      if (i >= 0) {
        const next = (Number(items[i].qty) || 0) + delta;
        if (next <= 0) { items.splice(i, 1); }
        else { items[i].qty = next; }
        setCartFor(NEGOCIO, items);
        renderCart();
      }
    }

    // Debug útil en pruebas
    console.log('RAW mama_cart:', localStorage.getItem('mama_cart'));
    console.log('getCartFor NEGOCIO=', NEGOCIO, getCartFor(NEGOCIO));

    renderCart();
  </script>
</body>
</html>
