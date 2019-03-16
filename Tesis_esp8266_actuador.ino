#include <Bounce2.h>
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
const char* myssid = "ESP-jose";
unsigned long last = 0;
unsigned int tam =0, sta1 = 0;
String datojson, chipid;
bool L,p,pa;
const int capacity =  JSON_OBJECT_SIZE(4) + 50;

Bounce debouncer = Bounce();
ESP8266WebServer server(80);    // Create a webserver object that listens for HTTP request on port 80
WebSocketsServer ws = WebSocketsServer(81); // crear instancia websocket
WebSocketsClient wsclient;
IPAddress local_ip (192,168,4,100);
IPAddress gateway (192,168,4,1);
IPAddress netmask (255,255,255,0);

const char webpri[] PROGMEM = R"====(
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta >
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
  height: 100%;
  width: auto;
  margin:10% 5%;
  padding:3%;
  border: 1px;
  background-color: white;
  border-radius: 8px;
  position: relative;
}

.container_hijo{
  height: 35px;
  width: 30%;
  margin: 3%;
  padding: 1%;
  position:relative;
  left: 30%;
}

.text_led{
  position: relative;
  left: 70px;
  bottom: 8px;
}

.switch {
  position: absolute; 
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input {display:none;}

.slider {
  position: absolute;
  float: right;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
<script>
    
    var sockets = new WebSocket('ws://'+location.hostname+':81/');
    sockets.onmessage = function (e){
    if(e.data == "Connected" ){ 
    console.log('Server: ',e.data);
    }else{
    var datojson = JSON.parse(e.data); 
    console.log(datojson);
      if(datojson.id == "estado"){
       document.getElementById("serial").innerHTML = datojson.chip_id; 
      }
    document.getElementById("LEDstatus").innerHTML = datojson.LED ? "ON":"OFF";  
    document.getElementById("LED").checked = datojson.LED ? true:false;  
    } 
    }; 
  

function control(){
  var txt_led = document.getElementById("LEDstatus");
  var estado_led = document.getElementById("LED");
  var led = estado_led.checked ? 1:0;
  txt_led.innerHTML = estado_led.checked ? "ON":"OFF";
  var estadojson = {LED:led,id:"control"};
  var jsondata = "s"+JSON.stringify(estadojson);
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
      <a href="wifi">WiFi</a>|| 
      </div>
      <div style="float: none;"></div>
  </nav>
  </div>

  <div class="container">
      <p> CHIP_ID: <span id="serial">XXXXX</span></p>
      <h3 style="text-align: center;">Panel control</h3>
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
 var sockets = new WebSocket('ws://'+location.hostname+':81/wifi');
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
      <a href="wifi">WiFi</a>||  
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

void webSocketEvent(uint8_t num, WStype_t type, uint8_t * payload, size_t length) {
    switch(type) {
        
        case WStype_DISCONNECTED:
            Serial.printf("[%u] Disconnected!\n", num);
            break;
            
        case WStype_CONNECTED: {
            IPAddress ip = ws.remoteIP(num);
            Serial.printf("[%u] Connected from %d.%d.%d.%d url: %s\n", num, ip[0], ip[1], ip[2], ip[3], payload);
            // send message to client
            ws.sendTXT(num, "Connected");
            StaticJsonBuffer<capacity> jb;
            JsonObject& datajson = jb.createObject();
            datajson.set("id","estado");
            datajson.set("LED",digitalRead(0));
            datajson.set("chip_id",chipid);
            datajson.printTo(datojson);
            //Serial.println(datojson); //visualizar JSON String  
            ws.sendTXT(num,datojson);
            datojson="";
        }
            break;
            
        case WStype_TEXT:
                String dato;
                if( payload[0] == 's')
                {
                       dato="";
                      for(int i=1 ; i < length ; i++ ){
                      dato += ((char)payload[i]);
                      }
                    //Serial.println(dato); //visulizar JSON String del cliente
                    DynamicJsonBuffer jbuffer;
                    JsonObject& jObject = jbuffer.parseObject(dato);
                    int LED = jObject["LED"];
                    digitalWrite(0,LED);
                    if(LED == HIGH){
                      L = true;
                    }else{
                      L = false;
                    }
                      for(uint8_t i = 0; i< WEBSOCKETS_SERVER_CLIENT_MAX;i++){
                          if(i != num){ 
                          ws.sendTXT(i,dato);} 
                      }
                }else if(payload[0] == 'w'){
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
                      //ConfigFile.close();
                      wificon(); 
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
        if( casoP == chipid){
          if(caso == "control"){
              int LED = jObject["LED"];
              digitalWrite(0,LED);
              if(LED == HIGH){
                L = true;
              }else{
                L = false;
              }
              String datojson = "";
              jObject["id"] = "control_r";
              jObject["LED"] = digitalRead(0);
              jObject["Chip_id"] = chipid;
              jObject.printTo(datojson);
              //Serial.println(datojson); //visualizar JSON String  
              wsclient.sendTXT(datojson);
            }else if(caso == "estado"){
              String datojson = "";
              jObject["id"] = "estado_r";
              jObject["LED"] = digitalRead(0);
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
    pinMode(0,OUTPUT);
    digitalWrite(0,LOW);
    debouncer.attach(2,INPUT_PULLUP); // Attach the debouncer to a pin with INPUT_PULLUP mode
    debouncer.interval(25); // Use a debounce interval of 25 milliseconds
    L = false;
    Serial.begin(115200);
    Serial.println("Booting");
    chipid = (String)ESP.getChipId();
    Serial.print("Serial del dispositivo: ");
    Serial.println(chipid);
    Serial.print("SPIFFS abierto: ");
    Serial.println(SPIFFS.begin() ? "True":"False");
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
  digitalWrite(0,LOW);
}
  
void loop() {
  ws.loop();
  wsclient.loop();
  ArduinoOTA.handle();
  server.handleClient();
  debouncer.update(); // Update the Bounce instance
   
   if ( debouncer.fell() ) {  // Call code if button transitions from HIGH to LOW
     L = !L;
    if(L){
      digitalWrite(0,HIGH);
    }else{
      digitalWrite(0,LOW);
    }
    StaticJsonBuffer<capacity> jb;
    JsonObject& datajson = jb.createObject();
    datajson.set("id","control_local");
    datajson.set("LED",digitalRead(0));
    datajson.set("chip_id",chipid);
    datajson.printTo(datojson);
    Serial.println(datojson); //visualizar JSON String
    wsclient.sendTXT(datojson);
    datojson="";
   }
}

