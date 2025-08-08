# 📋 Guía de Instalación - ESP32 Relay Control

## 🔧 Instalación Paso a Paso

### 1. Descargar el módulo
```bash
cd /tmp
git clone https://github.com/usuario/esp32-relay-issabel.git
cd esp32-relay-issabel
```

### 2. Ejecutar instalación automática
```bash
chmod +x setup/install.sh
./setup/install.sh
```

### 3. Configurar dialplan
Copiar la configuración al archivo de Asterisk:
```bash
cp setup/extensions_custom.conf /etc/asterisk/extensions_custom.conf
```

### 4. Recargar configuración
```bash
asterisk -rx "dialplan reload"
```

## ⚙️ Configuración ESP32

### Código Arduino
Cargar el archivo `setup/esp32_example.ino` en tu ESP32 con:
- WiFi configurado
- IP estática: 192.168.1.26
- Relé en GPIO 2
- Token: mi_token_secreto

### Configuración de Red
```cpp
const char* ssid = "TU_WIFI";
const char* password = "TU_PASSWORD";
IPAddress local_IP(192, 168, 1, 26);
```

## 📞 Uso del Sistema

### Activar Relé
1. Marcar **8000** desde cualquier extensión
2. Escuchar "Ingrese código de acceso"
3. Ingresar **100**
4. El sistema activará el relé ESP32

### Características
- ✅ Máximo 3 intentos de clave
- ✅ Timeout de 10 segundos por intento
- ✅ Confirmación de código aceptado
- ✅ Control directo via HTTP al ESP32

## 🔍 Verificación

### Probar dialplan
```bash
asterisk -rx "dialplan show from-internal-custom"
```

### Ver logs
```bash
tail -f /var/log/asterisk/full
```

### Probar ESP32
```bash
curl "http://192.168.1.26/on?token=mi_token_secreto"
```

## 🛠️ Personalización

### Cambiar clave
Editar en `/etc/asterisk/extensions_custom.conf`:
```
GotoIf($["${codigo}" = "TU_NUEVA_CLAVE"]?correcto:incorrecto)
```

### Cambiar IP ESP32
Modificar la línea:
```
System(curl "http://TU_IP/on?token=mi_token_secreto")
```

### Cambiar extensión
Reemplazar `8000` por tu extensión deseada en todo el dialplan.

## ❌ Troubleshooting

### No pide clave
- Verificar que el archivo esté en `/etc/asterisk/extensions_custom.conf`
- Ejecutar `asterisk -rx "dialplan reload"`

### No activa relé
- Verificar conectividad: `ping 192.168.1.26`
- Probar manualmente: `curl "http://192.168.1.26/on?token=mi_token_secreto"`
- Revisar logs: `tail -f /var/log/asterisk/full`

### Clave no acepta
- Verificar que sea exactamente "100"
- Revisar logs para ver qué código se está recibiendo