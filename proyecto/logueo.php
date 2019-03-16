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
    <h1 style="text-align: center;">Inicio de sesión de usuario</h1>
    <form action="/proyecto/sql/conexion.php" method="POST">
      <input class="recuadro" type="text" id="user" name="user" placeholder="USUARIO" >
    <br>
      <input class="recuadro" type="password" id="clave" name="clave" placeholder="CONTRASEÑA">
    <br>
      <button class="boton" type="submit" > Iniciar Sesión</button>    
    </form>
  </div>
</body>
</html>