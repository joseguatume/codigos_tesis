<!DOCTYPE html>
<?php  
session_start();
$temp = "";
$hum = "";
if (isset($_POST['sensor'])) {
  $sensor = $_POST['sensor'];
  $conexion = mysqli_connect("localhost","grafica","2uzhtu9OKfkkWz53");
  mysqli_select_db($conexion,"controlesp8266");
  mysqli_query($conexion,"SET NAME 'utf8' ");
  $query = "SELECT `Temperatura`,`Humedad`,UNIX_TIMESTAMP(Fecha) FROM `datos_temp_hum` WHERE `Chip_id_fk`='$sensor' ORDER BY Id DESC LIMIT 30";
  $result = mysqli_query($conexion,$query);
  while ($row = mysqli_fetch_array($result,MYSQLI_NUM)) {
    $temp = $temp."{x:new Date(". $row[2]*1000 ."), y:".$row[0]."},";
    $hum = $hum."{x:new Date(". $row[2]*1000 ."), y:".$row[1]."},";
  }
$temp = trim($temp,",");
$hum = trim($hum,",");
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
      <h3 style="text-align: center;"> Grafica</h3>
      <form action="/proyecto/grafico.php" method="POST">
        <select id="sensor" name="sensor" style="width: auto;">
        <?php
          $conexion = mysqli_connect("localhost","grafica","2uzhtu9OKfkkWz53");
          mysqli_select_db($conexion,"controlesp8266");
          mysqli_query($conexion,"SET NAME 'utf8' ");
          $query = "SELECT `Chip_id`,`ubicacion` FROM `dispositivos` WHERE `Funcion` = 2";
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
        <button type="submit" class="boton2">GRAFICAR</button>
      </form>  
      <canvas id="mychart" width="400" height="200">
      </canvas>
  </div>
</body>
<script>
    var sockets = new WebSocket('ws://192.168.1.101:8888/');
    sockets.onmessage = function (e){
      if(e.data == "Connected" ){ 
      console.log('Server: ',e.data);
      } 
    }; 

var config = {
      type: 'scatter',
      data: {
        datasets: [{
            label: "Temperatura",
            fill: false,
            borderColor: 'blue',
            data: [<?php echo $temp ?>],
        },{
          label: "Humedad",
            fill: false,
            borderColor: 'red',
            data: [<?php echo $hum ?>],
        }]
    },
      options: {
        scales: {
            xAxes: [{
                type: 'time',
                time: {
                    displayFormats: {
                        quarter: 'MMM YYYY'
                    }
                }
            }]
        },
        showLines:1,
        animation:{
          duration:0
        },
        responsive: true,
        title:{
                display : true,
                text: "Temperatura y Humedad la ultima hora",
                fontSize: 14,
                fontColor: 'Black'
        }
      }
    };

    window.onload = function() {
      document.getElementById('usuario').innerHTML ='<?php echo $_SESSION['usuario_s'] ?>';
      document.getElementById('nombre').innerHTML ='<?php echo $_SESSION['nombre_s'] ?>';
      document.getElementById('apellido').innerHTML ='<?php echo $_SESSION['apellido_s'] ?>';
      var ctx = document.getElementById('mychart').getContext('2d');
      window.myPie = new Chart(ctx, config);
    };

</script>
</html>

