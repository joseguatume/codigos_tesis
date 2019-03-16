<!DOCTYPE html>
<?php  
session_start();
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

  <div class="container_chart">
      <h3 style="text-align: center;">Dispositivos</h3>
      <div>
      <h4> Agregar </h4>
      <form action="/proyecto/sql/conexion.php" method="POST">
      <input class="recuadro2" type="text" id="ubicacion" name="ubicacion" placeholder="UBICACION">
      <input class="recuadro2" type="text" id="chip_id" name="chip_id" placeholder="CHIP ID">
      <br>
      <select id="funcion" name="funcion" style="width: auto;">
        <option value= 1> Actuador</option>
        <option value= 2> Sensor</option>
      </select>
      <button type="submit" class="boton2"> AGREGAR DISPOSITIVO</button>   
      </form>      
      </div>
      <div>
        <h4> Eliminar </h4>
        <form action="/proyecto/sql/conexion.php" method="POST">
         <select id="eliminar" name="eliminar" style="width: auto;">
        <?php
          $conexion = mysqli_connect("localhost","grafica","2uzhtu9OKfkkWz53");
          mysqli_select_db($conexion,"controlesp8266");
          mysqli_query($conexion,"SET NAME 'utf8' ");
          $query = "SELECT `Chip_id` FROM `dispositivos` WHERE 1";
          $result = mysqli_query($conexion,$query);
          while ($row = mysqli_fetch_array($result,MYSQLI_NUM)) {
            echo "<option value ='";
            echo $row[0];
            echo "'>";
            echo $row[0]; //chip id
            echo '</option>';
          }
          mysqli_close($conexion);
          ?> 
        </select>
        <button type="submit" class="boton2"> ELIMINAR DISPOSITIVO</button> 
        </form> 
      </div>
      <div>
      <h4 style="text-align: center;">Actuadores</h4>
      <table>
          <tr>
           <th>Ubicación</th>
           <th>Chip Id</th> 
          </tr>
          <?php
          $conexion = mysqli_connect("localhost","grafica","2uzhtu9OKfkkWz53");
          mysqli_select_db($conexion,"controlesp8266");
          mysqli_query($conexion,"SET NAME 'utf8' ");
          $query = "SELECT `ubicacion`,`Chip_id` FROM `dispositivos` WHERE Funcion = 1";
          $result = mysqli_query($conexion,$query);
          while ($row = mysqli_fetch_array($result,MYSQLI_NUM)) {
            echo "<tr>";
            echo "<td>";
            echo $row[0];//ubicacion
            echo "</td>";
            echo "<td>";
            echo $row[1];//chip_id
            echo "</td>";
            echo "</tr>";
          }
          mysqli_close($conexion);
          ?> 
        </table>
      </div>
      <h4 style="text-align: center;">Sensores</h4>
      <table>
          <tr>
           <th>Ubicación</th>
           <th>Chip Id</th> 
          </tr>
          <?php
          $conexion = mysqli_connect("localhost","grafica","2uzhtu9OKfkkWz53");
          mysqli_select_db($conexion,"controlesp8266");
          mysqli_query($conexion,"SET NAME 'utf8' ");
          $query = "SELECT `ubicacion`,`Chip_id` FROM `dispositivos` WHERE Funcion = 2";
          $result = mysqli_query($conexion,$query);
          while ($row = mysqli_fetch_array($result,MYSQLI_NUM)) {
            echo "<tr>";
            echo "<td>";
            echo $row[0];//ubicacion
            echo "</td>";
            echo "<td>";
            echo $row[1];//chip_id
            echo "</td>";
            echo "</tr>";
          }
          mysqli_close($conexion);
          ?> 
        </table>
      </div> 
  </div>

</body>
</html>

<script>
    var sockets = new WebSocket('ws://192.168.1.101:8888/');
    sockets.onmessage = function (e){
      if(e.data == "Connected" ){ 
      console.log('Server: ',e.data);
      } 
    }; 


document.getElementById('usuario').innerHTML ='<?php echo $_SESSION['usuario_s'] ?>';
document.getElementById('nombre').innerHTML ='<?php echo $_SESSION['nombre_s'] ?>';
document.getElementById('apellido').innerHTML ='<?php echo $_SESSION['apellido_s'] ?>';

</script>
