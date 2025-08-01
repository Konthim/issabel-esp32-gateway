# 📡 API Documentation

## ESP32 Endpoints

### Activar Relé
```http
GET http://IP_ESP32/on?token=TOKEN
```

**Respuesta exitosa:**
```
HTTP/1.1 200 OK
Content-Type: text/plain

Relay ON
```

### Desactivar Relé
```http
GET http://IP_ESP32/off?token=TOKEN
```

### Consultar Estado
```http
GET http://IP_ESP32/status
```

**Respuesta:**
```
HTTP/1.1 200 OK
Content-Type: text/plain

Relay: ON
```

## Base de Datos

### Tabla: esp32_access_log
```sql
CREATE TABLE esp32_access_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL,
    extension_llamante VARCHAR(20) NOT NULL,
    ip_esp32 VARCHAR(45) NOT NULL,
    accion VARCHAR(10) NOT NULL,
    resultado VARCHAR(20) NOT NULL
);
```

### Tabla: esp32_authorized_extensions
```sql
CREATE TABLE esp32_authorized_extensions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    extension VARCHAR(20) UNIQUE NOT NULL,
    descripcion VARCHAR(100),
    activo TINYINT(1) DEFAULT 1,
    allow_pstn TINYINT(1) DEFAULT 0,
    hora_inicio TIME DEFAULT '00:00:00',
    hora_fin TIME DEFAULT '23:59:59',
    dias_semana VARCHAR(7) DEFAULT '1111111'
);
```

## Estados de Resultado

- `OK`: Activación exitosa
- `ERROR`: Error de conectividad
- `UNAUTHORIZED`: Extensión no autorizada
- `OUT_OF_SCHEDULE`: Fuera de horario
- `SIMULATED_OK`: Modo simulación