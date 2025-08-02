# 🔌 ESP32 Relay Control Module para Issabel 5

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/usuario/esp32-relay-issabel)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Issabel](https://img.shields.io/badge/Issabel-5.x-orange.svg)](https://www.issabel.org/)

Módulo profesional para controlar relés ESP32 mediante llamadas telefónicas en Issabel PBX.

## ✨ Características Principales

- 🔌 **Control remoto**: Activa relés ESP32 via HTTP desde cualquier extensión
- 🔒 **Seguridad avanzada**: Lista blanca de extensiones con control de horarios
- 📞 **Soporte PSTN**: Permite activación desde llamadas externas
- 🕐 **Control temporal**: Horarios y días de la semana configurables
- 📊 **Auditoría completa**: Logs detallados con exportación CSV
- 🎨 **Interfaz moderna**: Dashboard responsive con Bootstrap 5
- 🧪 **Modo prueba**: Simulación sin afectar hardware
- 🔐 **Autenticación**: Soporte para tokens de seguridad

## 🚀 Instalación Rápida

### 1. Preparar archivos
```bash
cd /tmp
# Copiar los archivos del módulo a este directorio
```

### 2. Ejecutar instalación
```bash
chmod +x setup/install.sh
./setup/install.sh
```

### 3. Configurar dialplan
Agregar al archivo `/etc/asterisk/extensions_custom.conf`:
```
[from-internal-custom]
exten => 8000,1,NoOp(ESP32 Relay Control - Caller: ${CALLERID(num)})
exten => 8000,n,AGI(/var/lib/asterisk/agi-bin/esp32_relay_control.php)
exten => 8000,n,Playback(beep)
exten => 8000,n,Hangup()
```

### 4. Recargar configuración
```bash
amportal reload
```

## Configuración ESP32

1. Cargar el código `setup/esp32_example.ino` en tu ESP32
2. Configurar WiFi y IP estática
3. Conectar relé al pin GPIO 2
4. Opcional: configurar token de seguridad

## Uso del Módulo

### Acceso Web
- Ir a: **PBX → Control Relé ESP32**
- Configurar IP del ESP32 y extensión objetivo
- Agregar extensiones autorizadas
- Revisar logs de auditoría

### Activación del Relé
1. Desde una extensión autorizada, marcar **8000**
2. El sistema valida la autorización
3. Envía comando HTTP al ESP32
4. Registra la acción en el log

## Estructura de Archivos

```
modulo-Issabel/
├── agi/
│   └── esp32_relay_control.php    # Script AGI principal
├── sql/
│   └── install.sql                # Tablas de base de datos
├── setup/
│   ├── install.sh                 # Script de instalación
│   ├── uninstall.sh              # Script de desinstalación
│   ├── extensions_custom.conf    # Configuración dialplan
│   └── esp32_example.ino         # Código ESP32
└── web/
    ├── index.php                 # Interfaz web principal
    ├── menu.xml                  # Definición de menú
    └── themes/default/
        └── index.tpl             # Template HTML
```

## Tablas de Base de Datos

### esp32_access_log
- `id`: ID autoincremental
- `fecha_hora`: Timestamp del evento
- `extension_llamante`: Extensión que realizó la llamada
- `ip_esp32`: IP del ESP32 contactado
- `accion`: Tipo de acción (ON/OFF)
- `resultado`: Resultado (OK/ERROR/UNAUTHORIZED/SIMULATED_OK)

### esp32_config
- Configuración del módulo (IP, puerto, token, etc.)

### esp32_authorized_extensions
- Lista de extensiones autorizadas

## Configuración Avanzada

### Modo Simulación
- Activar desde la interfaz web
- No envía comandos reales al ESP32
- Útil para pruebas de autorización

### Token de Seguridad
- Configurar en la interfaz web
- Se envía como parámetro GET: `?token=mi_token`
- El ESP32 debe validar el token

### Múltiples ESP32
- Modificar el código para soportar múltiples dispositivos
- Usar tabla adicional para mapear extensiones → ESP32

## Troubleshooting

### El relé no se activa
1. Verificar conectividad de red al ESP32
2. Revisar logs en `/var/log/asterisk/full`
3. Comprobar que la extensión esté autorizada
4. Verificar configuración del token

### Error en la interfaz web
1. Verificar permisos de archivos
2. Comprobar conexión a base de datos
3. Revisar logs de Apache/Nginx

## 📷 Screenshots

### Dashboard Principal
![Dashboard](docs/images/dashboard.png)

### Configuración de Extensiones
![Extensions](docs/images/extensions.png)

### Logs de Auditoría
![Logs](docs/images/logs.png)

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 📦 Releases

- **v1.1.0** - Interfaz completa con pestañas, documentación de instalación
- **v1.0.0** - Release inicial con todas las funcionalidades

## 📞 Soporte

Para soporte técnico:
- 📝 [Issues en GitHub](https://github.com/usuario/esp32-relay-issabel/issues)
- 📊 Logs de Asterisk: `/var/log/asterisk/full`
- 📊 Logs del módulo en tabla `esp32_access_log`
- 🌐 Estado ESP32: `http://IP_ESP32/status`

## ⭐ ¿Te gustó el proyecto?

¡Dale una estrella en GitHub! Ayuda a otros a encontrar este módulo.