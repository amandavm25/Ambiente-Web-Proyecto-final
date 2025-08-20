// --- Storage helpers ---
function _getAll() {
  try { return JSON.parse(localStorage.getItem('mama_cart')) || {}; }
  catch { return {}; }
}
function _saveAll(c) { localStorage.setItem('mama_cart', JSON.stringify(c)); }

// Normaliza un número (>=0)
function _n(x, def=0){ const v = Number(x); return Number.isFinite(v) ? v : def; }

// --- API pública ---

/**
 * addToCart - Compatible con:
 *   addToCart(id, name, price, negocioId)      // qty = 1
 *   addToCart({id, name, price, negocioId, qty})
 */
function addToCart(a, name, price, negocioId) {
  let id, qty = 1, nId;

  if (typeof a === 'object') {
    id = _n(a.id);
    name = String(a.name ?? '');
    price = _n(a.price);
    nId = String(a.negocioId ?? a.negocio ?? '');
    qty = Math.max(1, _n(a.qty, 1));
  } else {
    // firma antigua
    id = _n(a);
    name = String(name ?? '');
    price = _n(price);
    nId = String(negocioId ?? '');
  }

  if (!nId || !id || price < 0) {
    console.warn('addToCart: datos inválidos', {id, name, price, negocioId:nId, qty});
    return;
  }

  const all = _getAll();
  if (!all[nId]) all[nId] = [];
  const list = all[nId];

  const i = list.findIndex(x => String(x.id) === String(id));
  if (i >= 0) {
    list[i].qty = _n(list[i].qty, 0) + qty;
  } else {
    list.push({ id, name, price, qty });
  }

  _saveAll(all);

  // Evento opcional para UI (badges, etc.)
  document.dispatchEvent(new CustomEvent('mama:cart:updated', { detail: { negocioId: nId } }));

  // Feedback simple
  if (!('hideAlert' in a) || !a.hideAlert) {
    try { alert('Agregado al carrito'); } catch {}
  }
}

function getCartFor(negocioId) {
  const all = _getAll();
  return all[String(negocioId)] || [];
}

function setCartFor(negocioId, items) {
  const all = _getAll();
  all[String(negocioId)] = (items || []).filter(it => _n(it.qty) > 0);
  _saveAll(all);
  document.dispatchEvent(new CustomEvent('mama:cart:updated', { detail: { negocioId: String(negocioId) } }));
}

function clearCartFor(negocioId) {
  const all = _getAll();
  delete all[String(negocioId)];
  _saveAll(all);
  document.dispatchEvent(new CustomEvent('mama:cart:updated', { detail: { negocioId: String(negocioId) } }));
}

function removeFromCart(negocioId, id) {
  const list = getCartFor(negocioId);
  const idx = list.findIndex(x => String(x.id) === String(id));
  if (idx >= 0) {
    list.splice(idx, 1);
    setCartFor(negocioId, list);
  }
}

function setQty(negocioId, id, qty) {
  qty = _n(qty);
  const list = getCartFor(negocioId);
  const i = list.findIndex(x => String(x.id) === String(id));
  if (i >= 0) {
    if (qty <= 0) list.splice(i, 1);
    else list[i].qty = qty;
    setCartFor(negocioId, list);
  }
}

// Totales rápidos (para badges o mini-cart)
function getTotals(negocioId) {
  const list = getCartFor(negocioId);
  let items = 0, subtotal = 0;
  for (const it of list) {
    const q = _n(it.qty);
    items += q;
    subtotal += q * _n(it.price);
  }
  return { items, subtotal };
}

// Para depurar si hace falta:
function debugCart(){ console.log('mama_cart =', _getAll()); }