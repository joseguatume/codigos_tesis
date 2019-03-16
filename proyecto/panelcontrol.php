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

  <div class="container_datos">
    <h3 style="text-align: center;" >Sensor</h3>
    <p style="text-align: right;">Estado: <span id="estado_sensor" style="font-weight: bold;">Desconectado</span></p> 
    <select id="sensor" style="width: auto; left: 40%;" onchange="sensor()">
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
    <p  style="text-align: center; font-weight: bold;"> Temperatura: <span id = "Temperatura">25</span> °C | Humedad: <span id="Humedad">50</span> %</p>
  </div>

  <div class="container_panel">
      <h3 style="text-align: center;">Actuador</h3>
      <p style="text-align: right;">Estado: <span id="estado_actuador" style="font-weight: bold;">Desconectado</span></p> 
      <select id="actuador" style="width: auto; left: 40%;" onchange="actuador()">
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
      <div class="container_hijo">
      <label class="switch">
      <input type="checkbox" id="LED" onclick="control()">
      <span class="slider round"></span>
      <p class="text_led">LED:<span id="LEDstatus" style="color: blue;">OFF</span></p>  
      </label>
      </div>  
  </div>

</body>
</html>

<script>
    var chip_id_actuador;
    var chip_id_sensor;
    chip_id_actuador = document.getElementById("actuador").value;
    chip_id_sensor = document.getElementById("sensor").value;
    var sockets = new WebSocket('ws://192.168.1.101:8888/');
    sockets.onmessage = function (e){
      if(e.data == "Connected" ){ 
      console.log('Server: ',e.data);
      var estadojson = {'id':'estado',
                        'LED':0,
                        'Chip_id':chip_id_actuador
                    };
      var jsondata = JSON.stringify(estadojson);
      sockets.send(jsondata);
      var sensor = {
        'id': 'sensor',
        'Temperatura':'0',
        'Humedad':'0',
        'Chip_id':chip_id_sensor
      };
      var sensor = JSON.stringify(sensor);
      sockets.send(sensor);
      }else{
      var datojson = JSON.parse(e.data); 
      console.log(datojson);
        if ( datojson.id == 'control_r' && datojson.Chip_id == chip_id_actuador) {
         var xhr = new XMLHttpRequest();
         document.getElementById("LEDstatus").innerHTML = datojson.LED ? "ON":"OFF";
         document.getElementById("LED").checked = datojson.LED ? true:false;
         var usuario = document.getElementById('usuario').innerHTML;
         var txt_led = document.getElementById("LEDstatus");
         var estado_led = document.getElementById("LED");
         var led = estado_led.checked ? 1:0;
         txt_led.innerHTML = estado_led.checked ? "ON":"OFF";
        
            if (datojson.nombre_u == usuario) {
             xhr.onreadystatechange = function(){
             if( this.onreadyState == 4 && this.status == 200 ){
             console.log(xhr.responseText);
             }
             };
             xhr.open("POST","/proyecto/sql/conexion.php",true);
             xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
             xhr.send('LED='+led+'&usuario='+usuario+'&chip_id='+chip_id_actuador); 
            }
        
        }else if(datojson.id == 'estado_r' && datojson.Chip_id == chip_id_actuador){
        document.getElementById("estado_actuador").innerHTML = "Conectado";
        document.getElementById("LEDstatus").innerHTML = datojson.LED ? "ON":"OFF";
         document.getElementById("LED").checked = datojson.LED ? true:false;
        }else if(datojson.id == 'sensor_estado' && datojson.Chip_id == chip_id_sensor){
         document.getElementById("estado_sensor").innerHTML = "Conectado";
         document.getElementById('Temperatura').innerHTML = datojson.Temperatura;
         document.getElementById('Humedad').innerHTML = datojson.Humedad; 
        }else if( (datojson.id == 'control_local') && datojson.chip_id == chip_id_actuador){
         document.getElementById("LEDstatus").innerHTML = datojson.LED ? "ON":"OFF";
         document.getElementById("LED").checked = datojson.LED ? true:false; 
        }
      } 
    }; 

function sensor(){
  document.getElementById("estado_sensor").innerHTML = "Desconectado";
  chip_id_sensor = document.getElementById("sensor").value;
  var sensor = {
        'id': 'sensor',
        'Temperatura':'0',
        'Humedad':'0',
        'Chip_id':chip_id_sensor
      };
  var sensor = JSON.stringify(sensor);
  sockets.send(sensor);
}

function actuador(){
  document.getElementById("estado_actuador").innerHTML = "Desconectado";
  chip_id_actuador = document.getElementById("actuador").value;
  var estadojson = {'id':'estado',
                        'LED':0,
                        'Chip_id':chip_id_actuador
                    };
  var jsondata = JSON.stringify(estadojson);
  sockets.send(jsondata);
}
  

function control() {
    var usuario = document.getElementById('usuario').innerHTML;
    var txt_led = document.getElementById("LEDstatus");
    var estado_led = document.getElementById("LED");
    var led = estado_led.checked ? 1:0;
    txt_led.innerHTML = estado_led.checked ? "ON":"OFF";
    var estadojson = {'id':'control',
                      'LED':led,
                      'Chip_id':chip_id_actuador,
                      'nombre_u':usuario
                    };
    var jsondata = JSON.stringify(estadojson);
    //console.log(jsondata);
    sockets.send(jsondata);
}

document.getElementById('usuario').innerHTML ='<?php echo $_SESSION['usuario_s'] ?>';
document.getElementById('nombre').innerHTML ='<?php echo $_SESSION['nombre_s'] ?>';
document.getElementById('apellido').innerHTML ='<?php echo $_SESSION['apellido_s'] ?>';

</script>
