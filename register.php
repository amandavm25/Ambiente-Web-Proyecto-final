<?php
session_start();
require_once("include/conexion.php");

if ($_SERVER['REQUEST_METHOD'] === "POST") {
  $nombre = $_POST['nombre'] ?? '';
  $email  = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  $confirm  = $_POST['confirm'] ?? '';
  $telefono = $_POST['telefono'] ?? '';
  $direccion = $_POST['direccion'] ?? '';
  $rol = $_POST['rol'] ?? 'cliente';

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $mensaje = "Correo inválido";
    $tipo = "danger";
  } elseif ($password !== $confirm) {
    $mensaje = "Las contraseñas no coinciden";
    $tipo = "danger";
  } else {
    // Email único
    $stmt = $mysqli->prepare("SELECT Id_usuario FROM usuarios WHERE Email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
      $mensaje = "Email ya registrado";
      $tipo = "warning";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $sql = "INSERT INTO usuarios (Nombre, Email, Contrasenia, Direccion, Telefono, Rol) VALUES (?,?,?,?,?,?)";
      $ins = $mysqli->prepare($sql);
      $ins->bind_param("ssssss", $nombre, $email, $hash, $direccion, $telefono, $rol);
      $ok = $ins->execute();
      if ($ok) {
        $uid = $ins->insert_id;
        // Si es negocio, crear perfil base
        if ($rol === 'negocio') {
          $sqlN = "INSERT INTO negocios (Usuario_id, Nombre, Email, Telefono) VALUES (?,?,?,?)";
          $insN = $mysqli->prepare($sqlN);
          $insN->bind_param("isss", $uid, $nombre, $email, $telefono);
          $insN->execute();
          $insN->close();
        }
        $ins->close();
        // Autologin
        $stmt2 = $mysqli->prepare("SELECT Id_usuario, Nombre, Email, Contrasenia, Rol, Direccion, Telefono FROM usuarios WHERE Id_usuario=?");
        $stmt2->bind_param("i", $uid);
        $stmt2->execute();
        $u = $stmt2->get_result()->fetch_assoc();
        $_SESSION['usuario'] = $u;
        header("Location: home.php");
        exit();
      } else {
        $mensaje = "Error al crear usuario";
        $tipo = "danger";
      }
    }
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
  <title>Registro - Mamalila</title>
</head>

<body class="bg-light">
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card p-4 shadow-lg w-100" style="max-width: 600px">
      <div class="card-header">
        <h3 class="card-title">Registro</h3>
      </div>
      <div class="card-body">
        <form method="post">
          <div class="mb-3">
            <label class="form-label" for="nombre">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="email">Correo</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          <div class="row g-2">
            <div class="col-md-6 mb-3">
              <label class="form-label" for="password">Contraseña</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label" for="confirm">Confirmar</label>
              <input type="password" class="form-control" id="confirm" name="confirm" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="telefono">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono">
          </div>
          <div class="mb-3">
            <label class="form-label" for="direccion">Dirección (si eres cliente)</label>
            <input type="text" class="form-control" id="direccion" name="direccion">
          </div>
          <div class="mb-3">
            <label class="form-label" for="rol">Rol</label>
            <select class="form-select" id="rol" name="rol" required>
              <option value="cliente">Cliente</option>
              <option value="negocio">Negocio</option>
            </select>
          </div>
          <button type="submit" class="btn btn-success w-100">Registrarme</button>
          <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo ?? 'danger'; ?> mt-3"><?php echo htmlspecialchars($mensaje); ?></div>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</body>

</html>