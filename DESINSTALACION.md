# 🗑️ DESINSTALACIÓN - Módulo ESP32 Relay Control

## 🔧 **DESINSTALACIÓN COMPLETA DESDE CONSOLA**

### **1. Eliminar archivos del módulo:**
```bash
rm -rf /var/www/html/modules/esp32-relay-issabel/
```

### **2. Eliminar script AGI:**
```bash
rm -f /var/lib/asterisk/agi-bin/esp32_relay_control.php
```

### **3. Eliminar tablas de base de datos:**
```bash
mysql -u root -p asterisk << 'EOF'
DROP TABLE IF EXISTS esp32_config;
DROP TABLE IF EXISTS esp32_access_log;
DROP TABLE IF EXISTS esp32_authorized_extensions;
EOF
```

### **4. Eliminar registro del menú:**
```bash
sqlite3 /var/www/db/menu.db \
"DELETE FROM menu WHERE id='esp32-relay-issabel';"
```

### **5. Eliminar registro ACL:**
```bash
sqlite3 /var/www/db/acl.db \
"DELETE FROM acl_resource WHERE name='esp32-relay-issabel';"
```

### **6. Limpiar dialplan (opcional):**
```bash
# Editar manualmente y eliminar las líneas del ESP32
nano /etc/asterisk/extensions_custom.conf

# Eliminar estas líneas:
# [from-internal-custom]
# exten => 8000,1,NoOp(ESP32 Relay Control - Caller: ${CALLERID(num)})
# exten => 8000,n,AGI(/var/lib/asterisk/agi-bin/esp32_relay_control.php)
# exten => 8000,n,Playback(beep)
# exten => 8000,n,Hangup()
```

### **7. Recargar configuración:**
```bash
amportal reload
service httpd restart
```

## ✅ **VERIFICACIÓN DE DESINSTALACIÓN**

### **Verificar que se eliminó:**
```bash
# Verificar archivos
ls -la /var/www/html/modules/ | grep esp32
ls -la /var/lib/asterisk/agi-bin/ | grep esp32

# Verificar base de datos
mysql -u root -p asterisk -e "SHOW TABLES LIKE 'esp32%';"

# Verificar menú
sqlite3 /var/www/db/menu.db "SELECT * FROM menu WHERE id='esp32-relay-issabel';"

# Verificar ACL
sqlite3 /var/www/db/acl.db "SELECT * FROM acl_resource WHERE name='esp32-relay-issabel';"
```

### **Resultado esperado:**
- No debe aparecer ningún archivo o registro relacionado con ESP32
- El menú "Control Relé ESP32" debe desaparecer de la interfaz web

## 🚨 **SCRIPT DE DESINSTALACIÓN AUTOMÁTICA**

```bash
#!/bin/bash
echo "🗑️ Desinstalando módulo ESP32 Relay Control..."

# Eliminar archivos
rm -rf /var/www/html/modules/esp32-relay-issabel/
rm -f /var/lib/asterisk/agi-bin/esp32_relay_control.php

# Eliminar base de datos
mysql -u root -p asterisk -e "
DROP TABLE IF EXISTS esp32_config;
DROP TABLE IF EXISTS esp32_access_log;
DROP TABLE IF EXISTS esp32_authorized_extensions;
"

# Eliminar registros de menú y ACL
sqlite3 /var/www/db/menu.db "DELETE FROM menu WHERE id='esp32-relay-issabel';"
sqlite3 /var/www/db/acl.db "DELETE FROM acl_resource WHERE name='esp32-relay-issabel';"

# Recargar
amportal reload
service httpd restart

echo "✅ Desinstalación completada"
echo "⚠️  Recuerda eliminar manualmente las líneas del dialplan en extensions_custom.conf"
```

## ⚠️ **NOTAS IMPORTANTES**

1. **Backup**: Hacer backup antes de desinstalar si quieres conservar los datos
2. **Dialplan**: Las líneas del dialplan deben eliminarse manualmente
3. **Permisos**: Algunos comandos requieren permisos de root
4. **Verificación**: Siempre verificar que la desinstalación fue completa

La desinstalación eliminará completamente el módulo del sistema.