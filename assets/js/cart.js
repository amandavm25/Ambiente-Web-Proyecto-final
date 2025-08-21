// assets/js/cart.js

// --- Storage helpers ---
function _getAll() {
  try { return JSON.parse(localStorage.getItem('mama_cart')) || {}; }
  catch { return {}; }
}
function _saveAll(c) { localStorage.setItem('mama_cart', JSON.stringify(c || {})); }

// Normaliza un número (>=0)
function _n(x, def = 0) {
  const v = Number(x);
  return Number.isFinite(v) ? v : def;
}

// --- API pública ---

/**
 * addToCart - Compatible con:
 *   A) addToCart(this)  // botón con data-id, data-name, data-price, [data-negocio]
 *   B) addToCart({ id, name, price, negocioId, qty, hideAlert })
 *   C) addToCart(id, name, price, negocioId)   // firma antigua
 */
function addToCart(a, name, price, negocioId) {
  let id, qty = 1, nId, nm, pr;

  // A) desde botón (this)
  if (a && typeof a === 'object' && a.dataset) {
    const btn = a;
    id  = _n(btn.dataset.id);
    nm  = String(btn.dataset.name ?? '');
    pr  = _n(btn.dataset.price);
    nId = String(btn.dataset.negocio ?? (typeof window.NEGOCIO !== 'undefined' ? window.NEGOCIO : ''));

    // fallback: deduce negocio de la URL (?id=123) si no vino en data-negocio ni window.NEGOCIO
    if (!nId) {
      const qs = new URLSearchParams(location.search);
      nId = String(qs.get('id') ?? '');
    }
  }
  // B) objeto de opciones
  else if (typeof a === 'object') {
    id  = _n(a.id);
    nm  = String(a.name ?? '');
    pr  = _n(a.price);
    nId = String(a.negocioId ?? a.negocio ?? '');
    qty = Math.max(1, _n(a.qty, 1));
  }
  // C) firma antigua (id, name, price, negocioId)
  else {
    id  = _n(a);
    nm  = String(name ?? '');
    pr  = _n(price);
    nId = String(negocioId ?? '');
  }

  if (!nId || !id || pr < 0) {
    console.warn('addToCart: datos inválidos', { id, name: nm, price: pr, negocioId: nId, qty });
    return;
  }

  const all = _getAll();
  if (!all[nId]) all[nId] = [];
  const list = all[nId];

  const i = list.findIndex(x => String(x.id) === String(id));
  if (i >= 0) {
    list[i].qty = _n(list[i].qty, 0) + qty;
  } else {
    list.push({ id, name: nm, price: pr, qty });
  }

  _saveAll(all);

  // Evento opcional para UI (badges, etc.)
  try { document.dispatchEvent(new CustomEvent('mama:cart:updated', { detail: { negocioId: nId } })); } catch {}

  // Si vino en forma objeto y a.hideAlert === true, no mostrar alerta
  const hideAlert = (typeof a === 'object' && a && 'hideAlert' in a && a.hideAlert);
  if (!hideAlert) {
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
  try { document.dispatchEvent(new CustomEvent('mama:cart:updated', { detail: { negocioId: String(negocioId) } })); } catch {}
}

function clearCartFor(negocioId) {
  const all = _getAll();
  delete all[String(negocioId)];
  _saveAll(all);
  try { document.dispatchEvent(new CustomEvent('mama:cart:updated', { detail: { negocioId: String(negocioId) } })); } catch {}
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
    items   += q;
    subtotal += q * _n(it.price);
  }
  return { items, subtotal };
}

// Para depurar si hace falta:
function debugCart(){ console.log('mama_cart =', _getAll()); }

