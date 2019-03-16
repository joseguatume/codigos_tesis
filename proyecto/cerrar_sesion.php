<?php  
session_start();
unset($_SESSION['usuario_s']);
unset($_SESSION['nombre_s']);
unset($_SESSION['apellido_s']);
session_destroy();

echo '<meta http-equiv="refresh" content="0; url=http://192.168.1.101/proyecto/">';
?>