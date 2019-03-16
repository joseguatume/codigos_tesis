const WebSocket = require('ws');
const mysql = require('mysql');

var connection = mysql.createConnection({
  host     : 'localhost',
  user     : 'servidor',
  password : 'sFZ7FO3s9UT6GhRv',
  database : 'controlesp8266',
  port : 3306
});

connection.connect(function(err) {
  if (err) {
    console.error('error connecting: ' + err.stack);
    return;
  }
  console.log('connected as id ' + connection.threadId);
});
 
const wss = new WebSocket.Server({ 
	host: '192.168.1.101',
	port: 8888 });

wss.broadcast = function broadcast(data) {
  wss.clients.forEach(function each(client) {
    //console.log('IT IS GETTING INSIDE CLIENTS');
    //console.log(client);

    // The data is coming in correctly
    //console.log(data);
    client.send(data);
  });
};
 
wss.on('connection', function(ws,req) {
	
	const ip = req.connection.remoteAddress;
	console.log(ip);
	const path = req.url;
	console.log(path);	
	ws.send('Connected');
	
  	ws.on('message', function(message) {
    	console.log('received: %s', message);
      var data = JSON.parse(message);
      //console.log(data.id);
    	   if(data.id == 'sensor_estado'){
          connection.query('INSERT INTO `datos_temp_hum` (`id`,`Temperatura`,`Humedad`,`Fecha`,`Chip_id_fk`) VALUES (NULL, ?,?,NULL,?)',[data.Temperatura,data.Humedad,data.Chip_id],
            function(err,res,field){
              if(err) throw err;
            });
      }else if(data.id == 'control_local'){
          var ubi;
          connection.query('SELECT `ubicacion` FROM `dispositivos` WHERE `Chip_id`= ?',[data.chip_id],
            function(err,res,field){
              if(err) throw err;
              ubi = res[0].ubicacion;
              connection.query('INSERT INTO `datos_leds` (`id`,`LED`,`fecha`,`usuario_fk`,`Chip_id_fk`,`ubicacion`,`control`) VALUES (NULL,?,NULL,?,?,?,?)',[data.LED+1,"LOCAL",data.chip_id,ubi,2],
            function(err2,res2,field2){
              if(err2) throw err2;
            });
            });

          
      }
      wss.clients.forEach(function each(client) {
      		if (client !== ws && client.readyState === WebSocket.OPEN) {
        	client.send(message);
        	}
      	});
  	});
 
});

setInterval(function(){
  var dato = {
    'id':'sensor',
    'Temperatura':'0',
    'Humedad':'0',
    'Chip_id':'0'
  };
  dato = JSON.stringify(dato);
  wss.broadcast(dato);
}, 20000);

wss.on('error',function(e){
console.log('Error en la creacion del websockets');
});
console.log('WebSocket server start');