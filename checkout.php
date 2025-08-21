<?php
session_start();
require_once("include/auth.php");
require_login();
require_once("include/conexion.php");

// === Negocio ===
$negocio_id = (int)($_GET['negocio'] ?? $_POST['negocio'] ?? 0);
if ($negocio_id <= 0) { die("Negocio inválido"); }

$st = $mysqli->prepare("SELECT Id_negocio, Nombre, Costo_envio, Tiempo_minutos FROM negocios WHERE Id_negocio=?");
$st->bind_param("i", $negocio_id);
$st->execute();
$neg = $st->get_result()->fetch_assoc();
$st->close();
if (!$neg) { die("Negocio no encontrado"); }
$costo_envio_neg = (float)($neg['Costo_envio'] ?? 0);

// === Mensajes ===
$msg_ok = null;
$msg_err = null;

// === POST (confirmar pedido) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $direccion = trim($_POST['direccion'] ?? '');
  $entrega   = $_POST['entrega'] ?? 'Domicilio';
  $cupon_in  = trim($_POST['cupon'] ?? '');
  $items     = json_decode($_POST['items_json'] ?? '[]', true);

  if ($direccion === '' || !is_array($items) || !count($items)) {
    $msg_err = "Datos inválidos";
  } else {
    $cliente_id = (int)($_SESSION['usuario']['Id_usuario'] ?? 0);

    // Recalcular precios desde BD
    $subtotal = 0;
    $lineas   = [];
    foreach ($items as $it) {
      $pid = (int)($it['id'] ?? 0);
      $qty = (int)($it['qty'] ?? 0);
      if ($pid <= 0 || $qty <= 0) { $msg_err = "Datos inválidos"; break; }

      $q = $mysqli->prepare("SELECT Precio FROM platillos WHERE Id_platillo=? AND Negocio_id=? AND Disponible=1");
      $q->bind_param("ii", $pid, $negocio_id);
      $q->execute();
      $row = $q->get_result()->fetch_assoc();
      $q->close();
      if (!$row) { $msg_err = "Producto no disponible"; break; }

      $precio   = (float)$row['Precio'];
      $sub      = $precio * $qty;
      $subtotal += $sub;
      $lineas[] = [$pid, $qty, $sub];
    }

    // Envío
    $esPickup = ($entrega === 'Recoger en tienda');
    $envio    = $esPickup ? 0 : $costo_envio_neg;

    // Cupón
    $cupon_cod = null;
    $descuento = 0;
    if (!$msg_err && $cupon_in !== '') {
      $c = $mysqli->prepare("
        SELECT * FROM cupones
        WHERE Codigo=? AND Activo=1
          AND (Negocio_id IS NULL OR Negocio_id=?)
          AND (Vigente_desde IS NULL OR Vigente_desde<=CURDATE())
          AND (Vigente_hasta IS NULL OR Vigente_hasta>=CURDATE())
          AND (Usos_max IS NULL OR Usos_actuales < Usos_max)
        LIMIT 1
      ");
      $c->bind_param("si", $cupon_in, $negocio_id);
      $c->execute();
      $cup = $c->get_result()->fetch_assoc();
      $c->close();

      if ($cup) {
        if ($cup['Tipo'] === 'porc') {
          $descuento = round($subtotal * ((float)$cup['Valor'] / 100), 2);
        } else {
          $descuento = (float)$cup['Valor'];
        }
        $descuento = max(0, min($descuento, $subtotal));
        $cupon_cod = $cup['Codigo'];
      }
    }

    $total = max(0, $subtotal - $descuento + $envio);
    if (!$msg_err && $total <= 0 && $subtotal <= 0) { $msg_err = "Carrito vacío"; }

    // Guardar pedido
    if (!$msg_err) {
      try {
        $mysqli->begin_transaction();

        $estado = 'Pendiente';
        $fecha  = date('Y-m-d H:i:s');

        $ins = $mysqli->prepare("
          INSERT INTO pedidos
            (Cliente_id, Negocio_id, Fecha, Estado, Total, Direccion_Entrega, Costo_envio, Tipo_entrega, Cupon_codigo, Descuento)
          VALUES (?,?,?,?,?,?,?,?,?,?)
        ");
        $ins->bind_param(
          "iissdsdssd",
          $cliente_id, $negocio_id, $fecha, $estado, $total,
          $direccion, $envio, $entrega, $cupon_cod, $descuento
        );
        $ins->execute();
        $pedido_id = $ins->insert_id;
        $ins->close();

        $det = $mysqli->prepare("INSERT INTO pedido_detalles (Pedido_id, Platillo_id, Cantidad, Subtotal) VALUES (?,?,?,?)");
        foreach ($lineas as [$pid, $qty, $sub]) {
          $det->bind_param("iiid", $pedido_id, $pid, $qty, $sub);
          $det->execute();
        }
        $det->close();

        if ($cupon_cod) {
          $u = $mysqli->prepare("UPDATE cupones SET Usos_actuales=Usos_actuales+1 WHERE Codigo=? LIMIT 1");
          $u->bind_param("s", $cupon_cod);
          $u->execute();
          $u->close();
        }

        $mysqli->commit();
        $msg_ok = "Pedido #$pedido_id registrado. " . ($cupon_cod ? "Cupón '$cupon_cod' aplicado." : "Sin cupón.");
      } catch (Throwable $e) {
        $mysqli->rollback();
        $msg_err = "No se pudo registrar el pedido: " . $e->getMessage();
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
  <title>Checkout</title>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <?php include("include/menu.php"); ?>
      <main class="col-md-9 p-4">
        <a href="menu.php?id=<?php echo $negocio_id; ?>">&larr; Volver al menú</a>
        <h3 class="mt-2">Checkout – <?php echo htmlspecialchars($neg['Nombre']); ?></h3>
        <p class="text-muted">Tiempo estimado: <?php echo (int)($neg['Tiempo_minutos'] ?? 20); ?> min</p>

        <?php if ($msg_ok): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($msg_ok); ?></div>
          <script>
            // Limpia el carrito de este negocio y redirige a pedidos
            (function(){
              try {
                const key = 'mama_cart';
                const all = JSON.parse(localStorage.getItem(key) || '{}');
                delete all["<?php echo (int)$negocio_id; ?>"];
                localStorage.setItem(key, JSON.stringify(all));
              } catch {}
              setTimeout(()=>location.href='cliente/pedidos.php', 800);
            })();
          </script>
        <?php elseif ($msg_err): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($msg_err); ?></div>
        <?php endif; ?>

        <div class="row g-3">
          <!-- Resumen -->
          <div class="col-lg-6">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Tu orden</h5>
                <div id="cartEmpty" class="text-muted">No hay productos en el carrito.</div>
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
                        <tr>
                          <th colspan="2" class="text-end">Descuento</th>
                          <th class="text-end" id="cartDesc">₡0</th>
                        </tr>
                        <tr>
                          <th colspan="2" class="text-end">Envío</th>
                          <th class="text-end" id="cartEnvio">₡0</th>
                        </tr>
                        <tr>
                          <th colspan="2" class="text-end">Total</th>
                          <th class="text-end" id="cartTotal">₡0</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  <button class="btn btn-outline-secondary btn-sm" id="btnVaciar">Vaciar carrito</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Formulario -->
          <div class="col-lg-6">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Datos de entrega</h5>
                <form method="post" id="checkoutForm">
                  <input type="hidden" name="negocio" value="<?php echo (int)$negocio_id; ?>">
                  <input type="hidden" name="items_json" id="items_json">

                  <div class="mb-3">
                    <label class="form-label">Dirección de entrega</label>
                    <textarea name="direccion" class="form-control" rows="3" required></textarea>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Tipo de entrega</label>
                    <select name="entrega" class="form-select" id="selEntrega">
                      <option value="Domicilio">Domicilio (₡<?php echo number_format($costo_envio_neg, 0); ?>)</option>
                      <option value="Recoger en tienda">Recoger en tienda (₡0)</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Cupón (opcional)</label>
                    <input class="form-control" name="cupon" id="inpCupon" placeholder="Ej: GLOBAL10">
                    <div class="form-text">Se aplicará al confirmar el pedido.</div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label">Método de pago</label>
                    <select id="metodoPago" class="form-select">
                      <option value="">Seleccionar</option>
                      <option value="tarjeta">Tarjeta</option>
                      <option value="sinpe">SINPE Móvil</option>
                      <option value="transferencia">Transferencia bancaria</option>
                      <option value="contra_entrega">Contra entrega</option>
                    </select>
                  </div>

                  <!-- Secciones de pago -->
                  <div id="pagoTarjeta" class="pago-seccion d-none">
                    <h6>Pago con tarjeta</h6>
                    <input type="text" class="form-control mb-2" placeholder="Número de tarjeta">
                    <input type="text" class="form-control mb-2" placeholder="MM/AA">
                    <input type="text" class="form-control mb-2" placeholder="CVV">
                  </div>

                  <div id="pagoSinpe" class="pago-seccion d-none">
                    <h6>Pago con SINPE Móvil</h6>
                    <p>Realiza la transferencia al número: <strong>8888-8888</strong></p>
                  </div>

                  <div id="pagoTransferencia" class="pago-seccion d-none">
                    <h6>Transferencia bancaria</h6>
                    <p>Cuenta IBAN: <strong>CR00-1234-5678-9012-3456</strong></p>
                  </div>

                  <div id="pagoContra" class="pago-seccion d-none">
                    <h6>Contra entrega</h6>
                    <p>Paga en efectivo al recibir tu pedido.</p>
                  </div>

                  <button class="btn btn-primary">Confirmar pedido</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script src="assets/js/cart.js"></script>
  <script>
    // === Config desde PHP ===
    const NEGOCIO   = <?php echo (int)$negocio_id; ?>;
    const ENVIO_NEG = <?php echo (float)$costo_envio_neg; ?>;

    // === UI ===
    const rows = document.getElementById('cartRows');
    const subEl = document.getElementById('cartSubtotal');
    const descEl = document.getElementById('cartDesc');
    const envEl  = document.getElementById('cartEnvio');
    const totEl  = document.getElementById('cartTotal');
    const cartBox = document.getElementById('cartBox');
    const cartEmpty = document.getElementById('cartEmpty');
    const selEntrega = document.getElementById('selEntrega');

    // Actualiza textos del selector de entrega con montos reales
    function formatCRC(n){ return '₡' + (Number(n)||0).toLocaleString('es-CR'); }
    (function updateEntregaLabels(){
      const optDom  = selEntrega?.querySelector('option[value="Domicilio"]');
      const optPick = selEntrega?.querySelector('option[value="Recoger en tienda"]');
      if (optDom)  optDom.textContent  = `Domicilio (${formatCRC(ENVIO_NEG)})`;
      if (optPick) optPick.textContent = `Recoger en tienda (${formatCRC(0)})`;
    })();

    // === Fallbacks por si el cart.js no está por cache ===
    function _getAll(){ try { return JSON.parse(localStorage.getItem('mama_cart')) || {}; } catch { return {}; } }
    function _saveAll(o){ localStorage.setItem('mama_cart', JSON.stringify(o||{})); }
    function getCartForSafe(id){
      if (typeof window.getCartFor === 'function') return window.getCartFor(id);
      const all=_getAll(); return all[String(id)] || [];
    }
    function setCartForSafe(id, items){
      if (typeof window.setCartFor === 'function') return window.setCartFor(id, items);
      const all=_getAll(); all[String(id)] = items; _saveAll(all);
    }
    function clearCartForSafe(id){
      if (typeof window.clearCartFor === 'function') return window.clearCartFor(id);
      const all=_getAll(); delete all[String(id)]; _saveAll(all);
    }

    // === Render ===
    function renderCart() {
      const items = getCartForSafe(NEGOCIO) || [];
      rows.innerHTML = '';

      if (!items.length) {
        cartBox.style.display = 'none';
        cartEmpty.style.display = 'block';
        calcTotals();
        return;
      }
      cartBox.style.display = 'block';
      cartEmpty.style.display = 'none';

      for (const it of items) {
        const price = Number(it.price) || 0;
        const qty   = Number(it.qty)   || 0;
        const sub   = price * qty;

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
        tr.querySelector('[data-act="dec"]').addEventListener('click', () => changeQty(it.id, -1));
        tr.querySelector('[data-act="inc"]').addEventListener('click', () => changeQty(it.id, +1));
        rows.appendChild(tr);
      }
      calcTotals();
    }

    function changeQty(id, delta) {
      const items = getCartForSafe(NEGOCIO);
      const i = items.findIndex(x => String(x.id) === String(id));
      if (i >= 0) {
        items[i].qty = (Number(items[i].qty) || 0) + delta;
        if (items[i].qty <= 0) items.splice(i, 1);
        setCartForSafe(NEGOCIO, items);
        renderCart();
      }
    }

    function calcTotals() {
      const items = getCartForSafe(NEGOCIO) || [];
      let subtotal = 0;
      for (const it of items) subtotal += (Number(it.price) || 0) * (Number(it.qty) || 0);
      const envio = (selEntrega?.value === 'Recoger en tienda') ? 0 : ENVIO_NEG;
      const desc  = 0; 
      subEl.textContent = '₡' + subtotal.toLocaleString('es-CR');
      descEl.textContent = '₡' + desc.toLocaleString('es-CR');
      envEl.textContent  = '₡' + envio.toLocaleString('es-CR');
      totEl.textContent  = '₡' + (subtotal - desc + envio).toLocaleString('es-CR');
    }

    selEntrega?.addEventListener('change', calcTotals);
    document.getElementById('btnVaciar')?.addEventListener('click', () => {
      if (confirm('¿Vaciar carrito?')) { clearCartForSafe(NEGOCIO); renderCart(); }
    });

    // Envío del formulario: empaqueta solo id/qty
    document.getElementById('checkoutForm').addEventListener('submit', (e) => {
      const items = getCartForSafe(NEGOCIO) || [];
      if (!items.length) { e.preventDefault(); alert('Tu carrito está vacío.'); return; }
      document.getElementById('items_json').value = JSON.stringify(
        items.map(x => ({ id: x.id, qty: Number(x.qty) || 0 }))
      );
    });

    // Toggle de métodos de pago
    const selectPago = document.getElementById("metodoPago");
    const seccionesPago = {
      tarjeta: document.getElementById("pagoTarjeta"),
      sinpe: document.getElementById("pagoSinpe"),
      transferencia: document.getElementById("pagoTransferencia"),
      contra_entrega: document.getElementById("pagoContra"),
    };
    selectPago?.addEventListener("change", () => {
      Object.values(seccionesPago).forEach(div => div?.classList.add("d-none"));
      if (selectPago.value && seccionesPago[selectPago.value]) {
        seccionesPago[selectPago.value].classList.remove("d-none");
      }
    });

    // Primer render
    renderCart();
  </script>
</body>
</html>