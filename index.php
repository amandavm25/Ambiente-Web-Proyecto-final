<?php
session_start();
require_once("include/conexion.php");
// Login
if($_SERVER['REQUEST_METHOD'] === "POST"){
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $mensaje = "Correo inválido";
    } else {
        $sql = "SELECT Id_usuario, Nombre, Email, Contrasenia, Rol, Direccion, Telefono FROM usuarios WHERE Email=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res && $res->num_rows > 0){
            $u = $res->fetch_assoc();
            if(password_verify($password, $u['Contrasenia'])){
                $_SESSION['usuario'] = $u;
                header("Location: home.php"); exit();
            } else { $mensaje = "Credenciales inválidas"; }
        } else { $mensaje = "Usuario no encontrado"; }
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
  <title>Mamalila - Iniciar sesión</title>
</head>
<body class="bg-light">
  <main>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
      <div class="card p-4 shadow-lg w-100" style="max-width: 420px">
        <h3 class="card-title text-center mb-4">Mamalila</h3>
        <div class="card-body">
          <form method="post">
            <div class="mb-3">
              <label class="form-label" for="email">Correo</label>
              <input class="form-control" type="email" id="email" name="email" required>
            </div>
            <div class="mb-3">
              <label class="form-label" for="password">Contraseña</label>
              <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
          </form>
          <p class="text-center mt-3">¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
          <?php if (!empty($mensaje)): ?>
            <div class="alert alert-danger mt-3"><?php echo htmlspecialchars($mensaje); ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
