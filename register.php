<?php
// Por si algún include imprime accidentalmente (BOM/espacios), evita "headers already sent"
ob_start();

session_start();
require_once("include/conexion.php");

$ok = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Normaliza y valida
  $nombre = trim($_POST['nombre'] ?? '');
  $correo = strtolower(trim($_POST['correo'] ?? ''));
  $pass1  = $_POST['contrasenia'] ?? '';

  if ($nombre === '' || $correo === '' || $pass1 === '') {
    $error = 'Completa todos los campos.';
  } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $error = 'Ingresa un correo válido.';
  } else {
    // ¿ya existe ese correo?
    $chk = $mysqli->prepare("SELECT 1 FROM usuarios WHERE Email=? LIMIT 1");
    if (!$chk) { $error = 'Error interno (prep chq).'; }
    if (empty($error)) {
      $chk->bind_param("s", $correo);
      $chk->execute();
      $existe = (bool)$chk->get_result()->fetch_row();
      $chk->close();

      if ($existe) {
        $error = 'El correo ya se encuentra registrado.';
      } else {
        $hash = password_hash($pass1, PASSWORD_BCRYPT);
        $rol  = 'cliente'; // o toma el valor del formulario si habilitas selección

        $ins = $mysqli->prepare("INSERT INTO usuarios (Nombre, Email, Contrasenia, Rol) VALUES (?,?,?,?)");
        if (!$ins) { $error = 'Error interno (prep ins).'; }
        if (empty($error)) {
          $ins->bind_param("ssss", $nombre, $correo, $hash, $rol);
          $ok = $ins->execute();
          $ins->close();

          if ($ok) {
            header("Location: index.php?registro=ok");
            exit;
          } else {
            if ($mysqli->errno == 1062) {
              $error = 'Ese correo ya está registrado.';
            } else {
              $error = 'No se pudo crear la cuenta. Intenta de nuevo.';
            }
          }
        }
      }
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
  <title>Registro - Mamalila</title>
</head>
<body class="auth-bg">
  <div class="d-flex align-items-center justify-content-center min-vh-100 px-3">
    <div class="card auth-card p-4 p-md-5">
      <div class="text-center mb-3">
        <div class="brand-circle mb-2">📝</div>
        <h1 class="h3 auth-title mb-0">Crear cuenta</h1>
        <div class="text-muted small">Regístrate para empezar a ordenar</div>
      </div>

      <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input
              class="form-control"
              name="nombre"
              required
              value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>"
              autocomplete="name"
            />
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Correo</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input
              type="email"
              class="form-control"
              name="correo"
              required
              value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>"
              autocomplete="email"
            />
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label d-flex justify-content-between">
            Contraseña
            <button type="button" class="btn btn-link p-0 small" id="togglePass2">Mostrar</button>
          </label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input
              type="password"
              class="form-control"
              name="contrasenia"
              id="pass2"
              required
              autocomplete="new-password"
            />
          </div>
        </div>

        <div class="d-grid">
          <button class="btn btn-primary" id="btnReg">Crear cuenta</button>
        </div>
      </form>

      <div class="text-center mt-3">
        <a href="index.php" class="small">¿Ya tienes cuenta? Inicia sesión</a>
      </div>
    </div>
  </div>

  <script>
    // Mostrar/ocultar contraseña
    document.getElementById('togglePass2')?.addEventListener('click', function () {
      const p = document.getElementById('pass2');
      const is = p.type === 'password';
      p.type = is ? 'text' : 'password';
      this.textContent = is ? 'Ocultar' : 'Mostrar';
    });

    // Evitar doble submit
    document.querySelector('form')?.addEventListener('submit', e=>{
      const btn = document.getElementById('btnReg');
      if(btn){ btn.disabled = true; btn.textContent = 'Procesando...'; }
    });
  </script>
</body>
</html>
