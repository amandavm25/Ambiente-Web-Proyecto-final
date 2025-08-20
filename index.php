<?php
session_start();
require_once("include/conexion.php");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $correo = strtolower(trim($_POST['correo'] ?? ''));
  $pass   = $_POST['contrasenia'] ?? '';

  if ($correo === '' || $pass === '') {
    $error = 'Completa correo y contraseña.';
  } else {
    $st = $mysqli->prepare("SELECT Id_usuario, Nombre, Email, Contrasenia, Rol FROM usuarios WHERE Email=? LIMIT 1");
    if ($st) {
      $st->bind_param("s", $correo);
      $st->execute();
      $u = $st->get_result()->fetch_assoc();
      $st->close();
    } else {
      $error = 'Error interno.';
    }

    if (empty($error) && $u && password_verify($pass, $u['Contrasenia'])) {
      // Seguridad: nuevo ID de sesión al autenticarse
      session_regenerate_id(true);

      // Guarda datos mínimos en sesión
      $_SESSION['usuario'] = [
        'Id_usuario' => (int)$u['Id_usuario'],
        'Nombre'     => $u['Nombre'],
        'Email'      => $u['Email'],
        'Rol'        => $u['Rol'],
      ];

      // Redirige por rol
      if ($u['Rol'] === 'negocio') {
        header("Location: negocio/dashboard.php");
        exit;
      } else {
        header("Location: home.php");
        exit;
      }
    } else if (empty($error)) {
      $error = 'Correo o contraseña incorrectos.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="assets/css/app.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
  <title>Mamalila - Iniciar sesión</title>
</head>
<body class="auth-bg">
  <div class="d-flex align-items-center justify-content-center min-vh-100 px-3">
    <div class="card auth-card p-4 p-md-5">
      <div class="text-center mb-3">
        <div class="brand-circle mb-2">🍽️</div>
        <h1 class="h3 auth-title mb-0">Mamalila</h1>
        <div class="text-muted small">Inicia sesión para continuar</div>
      </div>

      <?php if(!empty($_GET['registro']) && $_GET['registro']==='ok'): ?>
        <div class="alert alert-success">Cuenta creada. Inicia sesión para continuar.</div>
      <?php endif; ?>

      <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="mb-3">
          <label class="form-label">Correo</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input
              type="email"
              class="form-control"
              name="correo"
              required
              autocomplete="email"
              value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>"
            />
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label d-flex justify-content-between">
            Contraseña
            <button type="button" class="btn btn-link p-0 small" id="togglePass">Mostrar</button>
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input
              type="password"
              class="form-control"
              name="contrasenia"
              id="pass"
              required
              autocomplete="current-password"
            />
          </div>
        </div>

        <div class="d-grid">
          <button class="btn btn-primary" id="btnLogin">Entrar</button>
        </div>
      </form>

      <div class="text-center mt-3">
        <a href="register.php" class="small">¿No tienes cuenta? Regístrate</a>
      </div>
    </div>
  </div>

  <script>
    // Mostrar/ocultar contraseña
    document.getElementById('togglePass')?.addEventListener('click', function () {
      const p = document.getElementById('pass');
      const is = p.type === 'password';
      p.type = is ? 'text' : 'password';
      this.textContent = is ? 'Ocultar' : 'Mostrar';
    });

    // Evitar doble submit
    document.querySelector('form')?.addEventListener('submit', e => {
      const btn = document.getElementById('btnLogin');
      if (btn) { btn.disabled = true; btn.textContent = 'Procesando...'; }
    });
  </script>
</body>
</html>
