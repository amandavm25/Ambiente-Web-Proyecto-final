// assets/js/cart.js
function _getAll() {
  try { return JSON.parse(localStorage.getItem('mama_cart')) || {}; }
  catch { return {}; }
}
function _saveAll(c) { localStorage.setItem('mama_cart', JSON.stringify(c)); }

function addToCart(id, name, price, negocioId) {
  const all = _getAll();
  const key = String(negocioId);
  if (!all[key]) all[key] = [];
  const found = all[key].find(x => x.id == id);
  if (found) found.qty += 1;
  else all[key].push({ id: Number(id), name, price: Number(price), qty: 1 });
  _saveAll(all);
  alert('Agregado al carrito');
}

function getCartFor(negocioId) {
  const all = _getAll(); return all[String(negocioId)] || [];
}
function setCartFor(negocioId, items) {
  const all = _getAll(); all[String(negocioId)] = items; _saveAll(all);
}
function clearCartFor(negocioId) {
  const all = _getAll(); delete all[String(negocioId)]; _saveAll(all);
}
