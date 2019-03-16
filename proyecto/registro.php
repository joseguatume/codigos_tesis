<!DOCTYPE html>
<html>
<head>
	<title>Configuracion del WiFi</title>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="/proyecto/css/estilo.css">
<style type="text/css">
	body{
  background:#9fdf9f;
}
</style>
</head>
<body>
 <div class="container_top">
  <nav>
      <div  style="float:left; ">
      <a href="/proyecto/">Principal</a>
      </div>
      <div  style="float:right; ">
      <a href="/proyecto/Logueo.php"> Iniciar Sesión |</a>
      <a href="/proyecto/registro.php" > Registro </a>  
      </div>
      <div style="float: none;"></div>
  </nav>
  </div>

  <div class="container_portada"> 
    <h2 style="text-align: center;">Registro de usuario</h2>
    <form action="/proyecto/sql/conexion.php" method="POST">
      <input class="recuadro" type="text" id="nombre" name="nombre"  placeholder="NOMBRE">
    <br>
    <input class="recuadro" type="text" id="apellido" name="apellido"  placeholder="APELLIDO">
    <br>
      <input class="recuadro" type="text" id="usuario" name="usuario"  placeholder="NOMBRE DE USUARIO">
    <br>
      <input class="recuadro" type="password" id="pwd" name="pwd" placeholder="CONTRASEÑA" >
    <br>
      <input class="recuadro" type="password" id="r_pwd" name="r_pwd" placeholder=" REPETIR CONTRASEÑA">
      <button class="boton" type="submit" > Registrar </button>    
    </form> 
  </div>
</body>
</html>