#include <DHT.h>
#include <DHT_U.h>
#include <WebSocketsServer.h>
#include <WebSocketsClient.h>
#include <ESP8266WebServer.h>
#include <ESP8266WiFi.h>
#include <ArduinoOTA.h>
#include <ArduinoJson.h>
#include "FS.h"



//TP-LINK_9F02F8
//TP-LINK_D18ECC
const char* ssid = "TP-LINK_D18ECC";
const char* password = "guatume8891";
const char* myssid = "ESP-jose2";
unsigned long last = 0;
unsigned int tam =0, sta1 = 0;
String datojson, chipid,temp,hum;
const int capacity = JSON_OBJECT_SIZE(4)+ 50;

// Definimos el pin digital donde se conecta el sensor
#define DHTPIN 2
// Dependiendo del tipo de sensor
#define DHTTYPE DHT11
// Inicializamos el sensor DHT11
DHT dht(DHTPIN, DHTTYPE);
ESP8266WebServer server(80);    // Create a webserver object that listens for HTTP request on port 80
WebSocketsServer ws = WebSocketsServer(888); // crear instancia websocket
WebSocketsClient wsclient;
IPAddress local_ip (192,168,4,100);
IPAddress gateway (192,168,4,1);
IPAddress netmask (255,255,255,0);

const char webpri[] PROGMEM = R"====(
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="utf-8">
  <title> Control de relay </title>
<style type="text/css">

body{
  background:#9fdf9f;
}

a{
  color: #000;
  font-size: 20px;
  text-decoration: none;
}

.container_top{
  height: 30px;
  margin:10px 0px ;
  padding:5px ;
  border: 2px;
  background-color: #006600;
  border-radius: 8px;
  border-color: white;
}

.container{
  height: auto;
  width: auto;
  margin:10% 5%;
  padding:3%;
  border: 1px;
  background-color: white;
  border-radius: 8px;
  position: relative;
}



</style>
<script>
    
    var sockets = new WebSocket('ws://'+location.hostname+':888/');
    sockets.onmessage = function (e){
    if(e.data == "Connected" ){ 
    console.log('Server: ',e.data);
    }else{
    var datojson = JSON.parse(e.data); 
    console.log(datojson);
    document.getElementById('serial').innerHTML = datojson.chip_id;
    document.getElementById('Temperatura').innerHTML = datojson.Temperatura;
    document.getElementById('Humedad').innerHTML = datojson.Humedad; 
    } 
    };

    setInterval(function (){
     var json= {'Temperatura': 0,'Humedad': 0};
     json= 's'+JSON.stringify(json);
     //console.log(json);
     sockets.send(json);
      },20000);
  
</script>
</head>
<body>
  <div class="container_top">
  <nav>
      <div  style="float:left; ">
      <a href="/">Principal</a>
      </div>
      <div  style="float:right; ">
      <a href="wifi">WiFi</a>
      <div style="float: none;"></div>
  </nav>
  </div>

  <div class="container">
      <p> CHIP_ID: <span id="serial">XXXXX</span></p>
      <h3 style="text-align: center;">Sensor</h3>
      <p  style="text-align: center; font-weight: bold;"> Temperatura: <span id = "Temperatura">25</span> °C | Humedad: <span id="Humedad">50</span> %</p>  
  </div>
  
</body>
</html>
  )====";

const char webwifi[] PROGMEM = R"====(
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
  <title>WiFi</title>

<style type="text/css">

body{
  background:#9fdf9f;
}

a{
  color: #000;
  font-size: 20px;
  text-decoration: none;
}

.container_top{
  height: 30px;
  margin:10px 0px ;
  padding:5px ;
  border: 2px;
  background-color: #006600;
  border-radius: 8px;
  border-color: white;
}

.container{
  height: 100%;
  width: auto;
  margin:10% 5%;
  padding:3%;
  border: 1px;
  background-color: white;
  border-radius: 8px;
  position: relative;
}

</style>
<script>
 var sockets = new WebSocket('ws://'+location.hostname+':888/wifi');
    sockets.onmessage = function (e){
    if(e.data == "Connected" ){ 
    console.log('Server: ',e.data);
    }
    };

function wifisetting(){
  var ssid = document.getElementById("ssid").value;
  var password = document.getElementById("password").value;
  var jsondata = {ssid:ssid,password:password};
  var jsondata = "w"+JSON.stringify(jsondata);
  console.log(jsondata);
  sockets.send(jsondata);
}
</script>
</head>
<body>
  <div class="container_top">
  <nav>
      <div  style="float:left; ">
      <a href="/">Principal</a>
      </div>
      <div  style="float:right; ">
      <a href="wifi">WiFi</a>  
      </div>
      <div style="float: none;"></div>
  </nav>
  </div>

  <div class="container">
      <h1 style="text-align: center;">WIFI CONFIGURACION</h1>   
  <div align="center">
        <input type="text" value="" placeholder="SSID" id="ssid" />
  </div>
  <br>
  <div align="center">
        <input type="password" placeholder="PASSWORD" id="password" value=""/>
  </div>
  <br>
  <div align="center">
        <button onclick="wifisetting()"> Conectar </button>    
  </div>
  </div>
</body>
</html>
)====";

void wificon(){
        WiFi.disconnect();
        WiFi.softAPdisconnect(true);
        delay(1000);
        File ConfigFile = SPIFFS.open("/config.json","r");
          if(!ConfigFile){
              Serial.println("Falla en la lectura del archivo");
          }else{
                size_t size = ConfigFile.size();
                if (size > 1024) {
                Serial.println("Config file size is too large");
                }else{
                std::unique_ptr<char[]> buf(new char[size]);
                ConfigFile.readBytes(buf.get(), size);
                //ConfigFile.close();
                DynamicJsonBuffer jbuffer;
                JsonObject& jObject = jbuffer.parseObject(buf.get());
                if(!jObject.success()){
                 Serial.println("falla en JSON"); 
                }else{
                const char* _ssid = jObject["ssid"];
                const char* _password = jObject["password"];
                Serial.print("SSID: ");
                Serial.println(_ssid);
                Serial.print("PASSWORD: ");
                Serial.println(_password);
                WiFi.mode(WIFI_STA);
                WiFi.begin(_ssid,_password); 
                }
                
               }
          }             
    delay(10000);

    if(WiFi.status() == WL_CONNECTED){
      Serial.println("Ready");
      Serial.print("IP address: ");
      Serial.println(WiFi.localIP());
    }else{
      WiFi.mode(WIFI_AP);
      WiFi.softAPConfig(local_ip,gateway,netmask);
      WiFi.softAP(myssid,password);
      Serial.println("Ready");
      Serial.print("IP address: ");
      Serial.println(WiFi.softAPIP());
    }  
}

void sensor (){
  int t = dht.readTemperature();
  temp = String(t);
  int h = dht.readHumidity();
  hum = String (h);
  Serial.print("Temperatura: ");
  Serial.println(temp);
  Serial.print("Humedad: ");
  Serial.println(hum);
  Serial.println();
}

void webSocketEvent(uint8_t num, WStype_t type, uint8_t * payload, size_t length) {
    switch(type) {
        
        case WStype_DISCONNECTED:
            Serial.printf("[%u] Disconnected!\n", num);
            break;
            
        case WStype_CONNECTED:{ 
            IPAddress ip = ws.remoteIP(num);
            Serial.printf("[%u] Connected from %d.%d.%d.%d url: %s\n", num, ip[0], ip[1], ip[2], ip[3], payload);
            // send message to client
            ws.sendTXT(num, "Connected");
            sensor();
            StaticJsonBuffer<capacity> jb;
            JsonObject& datajson = jb.createObject();
            datajson["chip_id"] = chipid;
            datajson["id"] = "sensor";
            datajson["Temperatura"] = temp;
            datajson["Humedad"] = hum;
            datajson.printTo(datojson);
            //Serial.println(datojson); //visualizar JSON String  
            ws.sendTXT(num,datojson);
            datojson="";
            }
            break;
            
        case WStype_TEXT:
                String dato;
                //Serial.println(dato);
                if(payload[0] == 'w'){
                      dato="";
                      for(int i=1 ; i < length ; i++ ){
                      dato += ((char)payload[i]);
                      }
                      DynamicJsonBuffer jbuffer;
                      JsonObject& jObject = jbuffer.parseObject(dato);
                      File ConfigFile = SPIFFS.open("/config.json","w");
                        if(!ConfigFile){ // la creacion del archivo fallo 
                          Serial.println("Falla en la escritura del archivo");
                        }
                      jObject.printTo(ConfigFile);    
                      ConfigFile.close();
                      wificon(); 
                }else if(payload[0] == 's'){
                  String datojson = "";
                   dato="";
                      for(int i=1 ; i < length ; i++ ){
                      dato += ((char)payload[i]);
                      }
                   sensor();
                   StaticJsonBuffer<capacity> jb;
                   JsonObject& json = jb.createObject();
                   json["chip_id"] = chipid;
                   json["id"] = "sensor";
                   json["Temperatura"] = temp;
                   json["Humedad"] = hum; 
                   json.printTo(datojson);
                   //Serial.println(datojson); //visualizar JSON String  
                   ws.sendTXT(num,datojson);
                   datojson=""; 
                }
            break;
        
     }
}

void webSocketClientEvent(WStype_t type, uint8_t * payload, size_t length) {

  switch(type) {
    case WStype_DISCONNECTED:
      Serial.printf("[WSc] Disconnected!\n");
      break;
    case WStype_CONNECTED: {
      Serial.printf("[WSc] Connected to url: %s\n", payload);
    }
      break;
    case WStype_TEXT:
    {
      String buf="";
      for(int i = 0 ; i < length ; i++ )
      {
       buf += ((char)payload[i]); 
      }
      if(buf == "Connected")
      {
       Serial.printf("[WSc] WebSockets Client: %s\n", payload); 
      }else
      {
        DynamicJsonBuffer jbuffer;
        JsonObject& jObject = jbuffer.parseObject(buf);
        //jObject.printTo(Serial); //visualizar String JSON
        Serial.println(" ");
        String caso = jObject["id"];
        String casoP = jObject["Chip_id"];
        //Serial.println(caso); //visualizar "id"
        if( casoP == chipid || casoP == "0"){
             if(caso == "sensor"){
              String datojson = "";
              sensor();
              jObject["id"] = "sensor_estado";
              jObject["Humedad"] = hum;
              jObject["Temperatura"] = temp;
              jObject["Chip_id"] = chipid;
              jObject.printTo(datojson);
              //Serial.println(datojson); //visualizar JSON String  
              wsclient.sendTXT(datojson);
            }
        }
      }
    }
      break;
      
    case WStype_BIN:
      Serial.printf("[WSc] get binary length: %u\n", length);
      hexdump(payload, length);
      break;
  }

}
  
void setup() {
    Serial.begin(115200);
    Serial.println("Booting");
    chipid = (String)ESP.getChipId();
    Serial.print("Serial del dispositivo: ");
    Serial.println(chipid);
    Serial.print("SPIFFS abierto: ");
    Serial.println(SPIFFS.begin() ? "True":"False");
    // Comenzamos el sensor DHT
    dht.begin();
    wificon();
    
    delay(2000);
  // Port defaults to 8266
  // ArduinoOTA.setPort(8266);

  // Hostname defaults to esp8266-[ChipID]
  // ArduinoOTA.setHostname("myesp8266");

  // No authentication by default
  //ArduinoOTA.setPassword((const char *)"25017320");

  ArduinoOTA.onStart([]() {
    Serial.println("Start");
  });
  ArduinoOTA.onEnd([]() {
    Serial.println("\nEnd");
  });
  ArduinoOTA.onProgress([](unsigned int progress, unsigned int total) {
    Serial.printf("Progress: %u%%\r", (progress / (total / 100)));
  });
  ArduinoOTA.onError([](ota_error_t error) {
    Serial.printf("Error[%u]: ", error);
    if (error == OTA_AUTH_ERROR) Serial.println("Auth Failed");
    else if (error == OTA_BEGIN_ERROR) Serial.println("Begin Failed");
    else if (error == OTA_CONNECT_ERROR) Serial.println("Connect Failed");
    else if (error == OTA_RECEIVE_ERROR) Serial.println("Receive Failed");
    else if (error == OTA_END_ERROR) Serial.println("End Failed");
  });
  ArduinoOTA.begin();
 
  server.on("/",[](){server.send_P(200,"text/html",webpri);});               // Call the 'handleRoot' function when a client requests URI "/"
  server.on("/wifi",[](){server.send_P(200,"text/html",webwifi);});              
  server.onNotFound([](){server.send(404,"text/plain","404: Not found");});        // When a client requests an unknown URI (i.e. something other than "/"), call function "handleNotFound"
  server.begin();                           // Actually start the server
  Serial.println("HTTP server started");
  ws.begin();
  ws.onEvent(webSocketEvent);
  wsclient.begin("192.168.1.101",8888, "/");
  wsclient.onEvent(webSocketClientEvent);
  wsclient.setReconnectInterval(5000);
}
  
void loop() {
  ws.loop();
  wsclient.loop();
  ArduinoOTA.handle();
  server.handleClient();
}

