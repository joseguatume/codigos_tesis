<!DOCTYPE html>
<?php  
session_start();
$tabla="";
if (isset($_POST['actuador'])) {
  $actuador = $_POST['actuador'];
  $conexion = mysqli_connect("localhost","grafica","2uzhtu9OKfkkWz53");     
  mysqli_select_db($conexion,"controlesp8266");
  mysqli_query($conexion,"SET NAME 'utf8' ");
  $query = "SELECT LED,usuario_fk,fecha,ubicacion,control FROM `datos_leds` WHERE `Chip_id_fk` = '$actuador' ORDER BY `id` DESC LIMIT 10";
  $result = mysqli_query($conexion,$query);
      while ($row = mysqli_fetch_array($result,MYSQLI_NUM)) {
      $tabla =$tabla."<tr><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td></tr>";
      }
  mysqli_close($conexion);
}


?>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

  <title> Control de relay </title>

<link rel="stylesheet" type="text/css" href="/proyecto/css/estilo.css">

<style type="text/css">
	body{
  background:#9fdf9f;
}
</style>

<script src="/proyecto/js/Chart.bundle.js"></script>
<script src="/proyecto/js/utils.js"></script>
</head>

<body>
  <div class="container_top">
  <nav>
      <div  style="float:left; ">
      <a href="/proyecto/panelcontrol.php">Principal</a>
      </div>
      <div  style="float:right; ">
      <a href="/proyecto/cerrar_sesion.php">Cerrar Sesión</a>  
      </div>
      <div style="float: none;"></div>
  </nav>
  </div>
  <div class="container_datos">
    <a href="/proyecto/panelcontrol.php">| Inicio |</a>
    <a href="/proyecto/grafico.php">| Grafico |</a>
    <a href="/proyecto/tabla.php">| Tabla |</a>
    <a href="/proyecto/dispositivos.php"> | Dispositivos |</a>
  </div>

  <div class="container_datos" > 
    <h3 style="text-align: center;">Datos del usuario</h3>
    <p style="text-align: center; font-weight: bold;"> Usuario: <span id="usuario">Usuario</span>  |  Nombre: <span id="nombre">Nombre</span>  |  Apellido: <span id="apellido"> Apellido</span> </p>
  </div>

  <div class="container_chart" id="canvas-container">
      <h3 style="text-align: center;">Tabla</h3>
      <form action="/proyecto/tabla.php" method="POST">
        <select id="actuador" name="actuador" style="width: auto;">
        <?php
          $conexion = mysqli_connect("localhost","grafica","2uzhtu9OKfkkWz53");
          mysqli_select_db($conexion,"controlesp8266");
          mysqli_query($conexion,"SET NAME 'utf8' ");
          $query = "SELECT `Chip_id`,`ubicacion` FROM `dispositivos` WHERE `Funcion` = 1";
          $result = mysqli_query($conexion,$query);
          while ($row = mysqli_fetch_array($result,MYSQLI_NUM)) {
            echo "<option value ='";
            echo $row[0];//chip id
            echo "'>";
            echo $row[1]; //ubicacion
            echo '</option>';
          }
          mysqli_close($conexion);
          ?> 
        </select>
        <button type="submit" class="boton2">MOSTRAR TABLA</button>
      </form> 
      <div>
        <table>
          <tr>
           <th>LED </th>
           <th>USUARIO</th>
           <th>FECHA</th>
           <th>UBICACION</th>
           <th>CONTROL</th> 
          </tr>
        <?php echo $tabla; ?>
        </table>
      </div>
  </div>
</body>
<script>
    var sockets = new WebSocket('ws://192.168.1.101:8888/');
    sockets.onmessage = function (e){
      if(e.data == "Connected" ){ 
      console.log('Server: ',e.data);
      } 
    }; 


    window.onload = function() {
      document.getElementById('usuario').innerHTML ='<?php echo $_SESSION['usuario_s'] ?>';
      document.getElementById('nombre').innerHTML ='<?php echo $_SESSION['nombre_s'] ?>';
      document.getElementById('apellido').innerHTML ='<?php echo $_SESSION['apellido_s'] ?>';
    };

</script>
</html>

