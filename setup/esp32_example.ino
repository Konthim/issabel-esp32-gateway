#include <WiFi.h>
#include <WebServer.h>

const char* ssid = "IoT";
const char* password = "anitalahuerfanita";
const char* token = "mi_token_secreto"; // Opcional

const int relayPin = 2; // Pin GPIO para el relé
WebServer server(80);

void setup() {
  Serial.begin(115200);
  pinMode(relayPin, OUTPUT);
  digitalWrite(relayPin, LOW);
  
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Conectando a WiFi...");
  }
  
  Serial.println("WiFi conectado!");
  Serial.print("IP: ");
  Serial.println(WiFi.localIP());
  
  server.on("/on", handleRelayOn);
  server.on("/off", handleRelayOff);
  server.on("/status", handleStatus);
  
  server.begin();
  Serial.println("Servidor HTTP iniciado");
}

void loop() {
  server.handleClient();
}

void handleRelayOn() {
  if (validateToken()) {
    digitalWrite(relayPin, HIGH);
    Serial.println("Relé activado");
    server.send(200, "text/plain", "Relay ON");
  } else {
    server.send(401, "text/plain", "Unauthorized");
  }
}

void handleRelayOff() {
  if (validateToken()) {
    digitalWrite(relayPin, LOW);
    Serial.println("Relé desactivado");
    server.send(200, "text/plain", "Relay OFF");
  } else {
    server.send(401, "text/plain", "Unauthorized");
  }
}

void handleStatus() {
  String status = digitalRead(relayPin) ? "ON" : "OFF";
  server.send(200, "text/plain", "Relay: " + status);
}

bool validateToken() {
  if (strlen(token) == 0) return true; // Sin token requerido
  
  String receivedToken = server.arg("token");
  return receivedToken == token;
}