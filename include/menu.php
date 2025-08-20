<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$u   = $_SESSION['usuario'] ?? null;
$rol = $u['Rol'] ?? null;
$BASE = "/mamalila_prof";

function active($needle)
{
  return (strpos($_SERVER['PHP_SELF'], $needle) !== false) ? 'active' : '';
}
?>
<aside class="col-md-3 app-sidebar p-4">
  <h4 class="mb-4">Menú</h4>

  <ul class="nav flex-column">
    <?php if ($rol === 'negocio'): ?>
      <li class="nav-item">
        <a class="nav-link <?php echo active('/negocio/dashboard.php'); ?>" href="<?php echo $BASE; ?>/negocio/dashboard.php">Panel</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo active('/negocio/platillos.php'); ?>" href="<?php echo $BASE; ?>/negocio/platillos.php">Platillos</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo active('/negocio/pedidos.php'); ?>" href="<?php echo $BASE; ?>/negocio/pedidos.php">Pedidos</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo active('/negocio/perfil.php'); ?>" href="<?php echo $BASE; ?>/negocio/perfil.php">Mi perfil</a>
      </li>

    <?php elseif ($rol === 'cliente'): ?>
      <li class="nav-item">
        <a class="nav-link <?php echo active('/search.php'); ?>" href="<?php echo $BASE; ?>/search.php">Buscar</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo active('/home.php'); ?>" href="<?php echo $BASE; ?>/home.php">Inicio</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo active('/cliente/favoritos.php'); ?>" href="<?php echo $BASE; ?>/cliente/favoritos.php">Favoritos</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo active('/cliente/carritos.php'); ?>" href="<?php echo $BASE; ?>/cliente/carritos.php">Carrito</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo active('/cliente/pedidos.php'); ?>" href="<?php echo $BASE; ?>/cliente/pedidos.php">Mis pedidos</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?php echo active('/cliente/perfil.php'); ?>" href="<?php echo $BASE; ?>/cliente/perfil.php">Mi perfil</a>
      </li>

    <?php else: ?>
      <!-- Fallback si no hay sesión válida -->
      <li class="nav-item">
        <a class="nav-link" href="<?php echo $BASE; ?>/home.php">Inicio</a>
      </li>
    <?php endif; ?>
  </ul>

  <form action="<?php echo $BASE; ?>/include/logout.php" method="post" class="mt-4">
    <button type="submit" class="btn btn-danger w-100">Cerrar sesión</button>
  </form>
</aside>