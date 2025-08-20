<?php
// Base del proyecto (ajústala si cambia la carpeta)
define('BASE_URL', '/mamalila_prof');
define('BASE_DIR', dirname(__DIR__));

// Rutas de uploads
define('UPLOAD_DIR', BASE_DIR . '/uploads');
define('NEGOCIOS_DIR', UPLOAD_DIR . '/negocios');    // /uploads/negocios/<id>/
define('PLATILLOS_DIR', UPLOAD_DIR . '/platillos');  // /uploads/platillos/<negocio_id>/

// Asegura carpetas raíz
if (!is_dir(NEGOCIOS_DIR)) @mkdir(NEGOCIOS_DIR, 0777, true);
if (!is_dir(PLATILLOS_DIR)) @mkdir(PLATILLOS_DIR, 0777, true);

// Helpers DE NEGOCIO (portada/logo del negocio)
function neg_priv_dir(int $id){
  $d = NEGOCIOS_DIR . '/' . $id;
  if (!is_dir($d)) @mkdir($d, 0777, true);
  return $d;
}
function neg_public_url(int $id){
  return BASE_URL . '/uploads/negocios/' . $id;
}

// Helpers DE PLATILLOS (imágenes de menú)
function plat_priv_dir(int $negocio_id){
  $d = PLATILLOS_DIR . '/' . $negocio_id;
  if (!is_dir($d)) @mkdir($d, 0777, true);
  return $d;
}
function plat_public_url(int $negocio_id){
  return BASE_URL . '/uploads/platillos/' . $negocio_id;
}
