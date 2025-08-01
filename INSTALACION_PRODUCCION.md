# 🚀 INSTALACIÓN EN ISSABEL PRODUCCIÓN

## 📋 PRE-REQUISITOS

1. **Acceso root** al servidor Issabel
2. **Backup completo** del sistema antes de instalar
3. **ESP32 configurado** y conectado a la red

## 🔧 PASOS DE INSTALACIÓN

### 1. Subir archivos al servidor
```bash
# Crear directorio temporal
mkdir /tmp/esp32-module
cd /tmp/esp32-module

# Subir todos los archivos del módulo aquí
# (usar scp, rsync, o método preferido)
```

### 2. Ejecutar instalación
```bash
# Hacer ejecutable el instalador
chmod +x setup/install.sh

# Ejecutar instalación
./setup/install.sh
```

### 3. Configurar dialplan
```bash
# Editar extensions_custom.conf
nano /etc/asterisk/extensions_custom.conf

# Agregar al final:
[from-internal-custom]
exten => 8000,1,NoOp(ESP32 Relay Control - Caller: ${CALLERID(num)})
exten => 8000,n,AGI(/var/lib/asterisk/agi-bin/esp32_relay_control.php)
exten => 8000,n,Playback(beep)
exten => 8000,n,Hangup()

# Para soporte PSTN agregar también:
[from-pstn-custom]
exten => 8000,1,NoOp(ESP32 Relay Control PSTN - Caller: ${CALLERID(num)})
exten => 8000,n,AGI(/var/lib/asterisk/agi-bin/esp32_relay_control.php)
exten => 8000,n,Playback(beep)
exten => 8000,n,Hangup()
```

### 4. Recargar configuración
```bash
# Recargar Asterisk
amportal reload

# Verificar que el módulo aparezca en el menú web
```

## ⚙️ CONFIGURACIÓN INICIAL

### 1. Acceder al módulo
- Ir a: **PBX → Control Relé ESP32**

### 2. Configurar parámetros
- **IP ESP32**: Dirección IP del dispositivo
- **Puerto**: 80 (por defecto)
- **Extensión objetivo**: 8000
- **Token**: (opcional, para seguridad)
- **Modo simulación**: Desactivar para producción

### 3. Configurar extensiones autorizadas
- Agregar extensiones que pueden activar el relé
- Configurar horarios de uso
- Definir permisos PSTN si es necesario

## 🧪 PRUEBAS

### 1. Prueba básica
```bash
# Desde una extensión autorizada, marcar 8000
# Verificar en logs: /var/log/asterisk/full
```

### 2. Verificar logs
- Ir a **Log de Auditoría** en el módulo
- Confirmar que se registran las activaciones

### 3. Probar ESP32
```bash
# Comando manual para probar conectividad
curl http://IP_ESP32/on
curl http://IP_ESP32/status
```

## 🔒 SEGURIDAD

### 1. Configurar token en ESP32
```cpp
const char* token = "tu_token_seguro_aqui";
```

### 2. Actualizar configuración del módulo
- Agregar el mismo token en la configuración web

### 3. Firewall (opcional)
```bash
# Permitir solo tráfico interno al ESP32
iptables -A OUTPUT -d IP_ESP32 -p tcp --dport 80 -j ACCEPT
```

## 📊 MONITOREO

### 1. Logs del sistema
```bash
# Ver logs de Asterisk
tail -f /var/log/asterisk/full | grep ESP32

# Ver logs del módulo en la base de datos
mysql -u root -p asterisk -e "SELECT * FROM esp32_access_log ORDER BY fecha_hora DESC LIMIT 10;"
```

### 2. Estado del ESP32
- Verificar conectividad periódicamente
- Monitorear respuestas HTTP

## 🚨 TROUBLESHOOTING

### Problema: Módulo no aparece en menú
```bash
# Verificar instalación
ls -la /var/www/html/admin/modules/esp32_relay/

# Verificar permisos en base de datos
mysql -u root -p asterisk -e "SELECT * FROM acl_resource WHERE description='esp32_relay';"
```

### Problema: AGI no funciona
```bash
# Verificar archivo AGI
ls -la /var/lib/asterisk/agi-bin/esp32_relay_control.php
chmod +x /var/lib/asterisk/agi-bin/esp32_relay_control.php
chown asterisk:asterisk /var/lib/asterisk/agi-bin/esp32_relay_control.php
```

### Problema: No se conecta al ESP32
```bash
# Probar conectividad
ping IP_ESP32
curl -v http://IP_ESP32/status
```

## 📝 MANTENIMIENTO

### Backup regular
```bash
# Backup de configuración
mysqldump -u root -p asterisk esp32_access_log esp32_config esp32_authorized_extensions > esp32_backup.sql

# Backup de archivos
tar -czf esp32_module_backup.tar.gz /var/www/html/admin/modules/esp32_relay/ /var/lib/asterisk/agi-bin/esp32_relay_control.php
```

### Actualización del módulo
```bash
# Hacer backup antes de actualizar
# Reemplazar archivos
# Ejecutar: amportal reload
```

## 📞 SOPORTE

Si encuentras problemas:
1. Revisar logs de Asterisk
2. Verificar conectividad de red
3. Comprobar configuración de base de datos
4. Validar permisos de archivos

---
**⚠️ IMPORTANTE**: Siempre hacer backup completo antes de instalar en producción.