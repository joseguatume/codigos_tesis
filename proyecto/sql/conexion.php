<?php  
session_start();
$conexion = mysqli_connect("localhost","cliente","lX5LMCStBm1YnSdO");
mysqli_select_db($conexion,"controlesp8266");
mysqli_query($conexion,"SET NAME 'utf8' ");
$d_ip = '192.168.1.101';

if (mysqli_connect_error()) {
    printf("Conexión fallida: %s\n", mysqli_connect_error());
    exit();
}


if (isset($_POST['LED']) && isset($_POST['usuario']) && isset($_POST['chip_id'])) {
	
$LED = $_POST['LED']+1;
$usuario = $_POST['usuario'];
$chip_id = $_POST['chip_id'];
	$q = "SELECT `ubicacion` FROM `dispositivos` WHERE `Chip_id`='$chip_id'";
	$r = mysqli_query($conexion,$q);
	$row = mysqli_fetch_array($r,MYSQLI_NUM);

	$query = "INSERT INTO `datos_leds` (`id`, `LED`, `fecha`,`usuario_fk`,`Chip_id_fk`,`ubicacion`,`control`) VALUES (NULL, '$LED', CURRENT_TIMESTAMP,'$usuario','$chip_id','$row[0]',1)";
	
	$result = mysqli_query($conexion,$query);

	echo "DATOS INGRESADOS CORRECTAMENTE";
	mysqli_close($conexion);
}

if (isset($_POST['usuario']) && isset($_POST['pwd']) && isset($_POST['r_pwd']) && isset($_POST['nombre']) && isset($_POST['apellido'])) {
	
$usuario = strip_tags($_POST['usuario']);
$pwd = strip_tags($_POST['pwd']) ;
$r_pwd = strip_tags($_POST['r_pwd']);
$length = strlen($pwd);
$nombre = strip_tags($_POST['nombre']);
$apellido = strip_tags($_POST['apellido']);


if ($usuario == NULL || $pwd == NULL || $r_pwd == NULL || $nombre == NULL || $apellido == NULL) {
	echo '<meta http-equiv="refresh" content="0; url=http://192.168.1.101/proyecto/registro.php">';
	echo '<script>';
	echo 'alert("No puede haber campos vacios")';
	echo '</script>';
}else if ( $length < 8) {
	echo '<meta http-equiv="refresh" content="0; url=http://192.168.1.101/proyecto/registro.php">';
	echo '<script>';
	echo 'alert("Contraseña debe ser mayor o igual a 8 caracteres")';
	echo '</script>';
}else if ($pwd != $r_pwd) {
	echo '<meta http-equiv="refresh" content="0; url=http://192.168.1.101/proyecto/registro.php">';
	echo '<script>';
	echo 'alert("Las contraseñas no coinciden")';
	echo '</script>';
}else{
	$pwd = sha1($pwd);
	$query = "SELECT `usuario` FROM `usuarios` WHERE `usuario`='$usuario'";
	$resultado = mysqli_query($conexion, $query);
	$row = mysqli_fetch_array($resultado,MYSQLI_NUM);

	if($row[0] == $usuario){
	echo '<meta http-equiv="refresh" content="0; url=http://192.168.1.101/proyecto/registro.php">';
	echo '<script>';
	echo 'alert(" Nombre de usuario ya existe, intente con otro")';
	echo '</script>';
	}else{
	$query = "INSERT INTO `usuarios` (`usuario`,`clave`,`fecha`,`nombre`,`apellido`) VALUES ('$usuario','$pwd',NULL,'$nombre','$apellido')";
	$result=mysqli_query($conexion,$query);
	mysqli_close($conexion);
	echo '<meta http-equiv="refresh" content="0; url=http://192.168.1.101/proyecto/logueo.php">';
	echo '<script>';
	echo 'alert("DATOS INGRESADOS CORRECTAMENTE")';
	echo '</script>';	
	}

}
		
}

if (isset($_POST['user']) && isset($_POST['clave'])) {
	$usuario = strip_tags($_POST['user']);
	$clave = sha1(strip_tags($_POST['clave']));

	$query = "SELECT * FROM `usuarios` WHERE `usuario` = '$usuario' AND `clave` = '$clave'";
	$result = mysqli_query($conexion,$query);
	$row = mysqli_fetch_array($result,MYSQLI_NUM);

	if ($row) {
		$usuario = $row[0];
		$nombre = $row[3];
		$apellido = $row[4];
		$_SESSION['usuario_s'] = $usuario;
		$_SESSION['nombre_s'] = $nombre;
		$_SESSION['apellido_s'] = $apellido;

		echo '<meta http-equiv="refresh" content="0; url=http://192.168.1.101/proyecto/panelcontrol.php">';
		mysqli_close($conexion);
		}else{
		echo '<meta http-equiv="refresh" content="0; url=http://192.168.1.101/proyecto/logueo.php">';
		echo '<script>';
		echo 'alert("Usuario o contraseña incorrecta")';
		echo '</script>';
		mysqli_close($conexion);
	}
}

if (isset($_POST['ubicacion']) && isset($_POST['chip_id']) && isset($_POST['funcion'])) {
	$ubicacion = strip_tags($_POST['ubicacion']);
	$chip_id = strip_tags($_POST['chip_id']);
	$funcion = $_POST['funcion'];

	$query = "SELECT * FROM `dispositivos` WHERE `Chip_id` = '$chip_id'";
	$result = mysqli_query($conexion,$query);
	$row = mysqli_fetch_array($result,MYSQLI_NUM);

	if ($row) {
	echo '<meta http-equiv="refresh" content="0; url=http://192.168.1.101/proyecto/dispositivos.php">';
		echo '<script>';
		echo 'alert("Chip Id ya regitrados")';
		echo '</script>';
		mysqli_close($conexion);
	}else{
		$q = "INSERT INTO `dispositivos` (`Chip_id`, `id`, `Funcion`, `ubicacion`) VALUES ('$chip_id', NULL, '$funcion','$ubicacion')";
		$r = mysqli_query($conexion,$q);
		mysqli_close($conexion);
		echo '<meta http-equiv="refresh" content="0; url=http://192.168.1.101/proyecto/dispositivos.php">';
	}
}

if (isset($_POST['eliminar'])) {
	$eliminar = $_POST['eliminar'];
	$q = "DELETE FROM `dispositivos` WHERE `Chip_id` = '$eliminar'";
	$r = mysqli_query($conexion,$q);
	mysqli_close($conexion);
	echo '<meta http-equiv="refresh" content="0; url=http://192.168.1.101/proyecto/dispositivos.php">';
	
}


?>

