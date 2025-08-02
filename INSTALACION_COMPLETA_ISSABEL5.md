# 🚀 INSTALACIÓN COMPLETA - Módulo ESP32 Relay Control para Issabel 5

## 📋 **Requisitos Previos**
- Issabel 5.x instalado y funcionando
- Acceso root al servidor
- ESP32 configurado en la red local
- Git instalado

## 🔧 **INSTALACIÓN PASO A PASO**

### **1. Descargar el módulo desde GitHub:**
```bash
cd /var/www/html/modules/
git clone https://github.com/BlackBoysNetworks/esp32-relay-issabel.git
```

### **2. Ajustar permisos:**
```bash
chown -R asterisk:asterisk /var/www/html/modules/esp32-relay-issabel
find /var/www/html/modules/esp32-relay-issabel -type d -exec chmod 755 {} \;
find /var/www/html/modules/esp32-relay-issabel -type f -exec chmod 644 {} \;
```

### **3. Crear las tablas de base de datos:**
```bash
mysql -u root -p asterisk < /var/www/html/modules/esp32-relay-issabel/sql/install.sql
```

### **4. Registrar el módulo en ACL (permisos):**
```bash
sqlite3 /var/www/db/acl.db \
"INSERT INTO acl_resource (name, description) VALUES ('esp32-relay-issabel', 'Control Relé ESP32');"
```

### **5. Registrar el módulo en el menú:**
```bash
sqlite3 /var/www/db/menu.db \
"INSERT INTO menu (id, IdParent, Link, Name, Type, order_no)
 VALUES ('esp32-relay-issabel', 'pbxconfig', '', 'Control Relé ESP32', 'module', 8);"
```

### **6. Asignar permisos al grupo administrador:**
- Ir a **Sistema → Permisos de grupos**
- Seleccionar grupo **"administrador"**
- En la sección **"pbxconfig"** marcar **"Control Relé ESP32"**
- Guardar cambios

### **7. Instalar script AGI:**
```bash
cp /var/www/html/modules/esp32-relay-issabel/agi/esp32_relay_control.php /var/lib/asterisk/agi-bin/
chmod +x /var/lib/asterisk/agi-bin/esp32_relay_control.php
chown asterisk:asterisk /var/lib/asterisk/agi-bin/esp32_relay_control.php
```

### **8. Configurar dialplan:**
```bash
cat >> /etc/asterisk/extensions_custom.conf << 'EOF'

[from-internal-custom]
exten => 8000,1,NoOp(ESP32 Relay Control - Caller: ${CALLERID(num)})
exten => 8000,n,AGI(/var/lib/asterisk/agi-bin/esp32_relay_control.php)
exten => 8000,n,Playback(beep)
exten => 8000,n,Hangup()
EOF
```

### **9. Recargar configuración:**
```bash
amportal reload
service httpd restart
```

## ✅ **VERIFICACIÓN DE INSTALACIÓN**

### **1. Verificar menú:**
- Cerrar sesión en Issabel
- Volver a entrar
- Ir a **PBX → Control Relé ESP32**

### **2. Verificar base de datos:**
```bash
mysql -u root -p asterisk -e "SHOW TABLES LIKE 'esp32%';"
```

### **3. Verificar AGI:**
```bash
ls -la /var/lib/asterisk/agi-bin/esp32_relay_control.php
```

## 🎯 **CONFIGURACIÓN INICIAL**

### **1. En la interfaz web:**
- Ir a **PBX → Control Relé ESP32**
- Pestaña **"Configuración"**:
  - IP del ESP32: `192.168.1.100`
  - Puerto: `80`
  - Extensión objetivo: `8000`
  - Desactivar "Modo simulación" para producción

### **2. Agregar extensiones autorizadas:**
- Pestaña **"Extensiones Autorizadas"**
- Agregar extensiones que pueden activar el relé
- Configurar horarios de acceso
- Marcar PSTN si es necesario

## 🧪 **PRUEBAS**

### **1. Prueba básica:**
- Desde una extensión autorizada, marcar **8000**
- Verificar activación del relé ESP32
- Revisar logs en pestaña **"Log de Auditoría"**

### **2. Verificar logs:**
```bash
tail -f /var/log/asterisk/full | grep ESP32
```

## 🔧 **SOLUCIÓN DE PROBLEMAS**

### **Módulo no aparece en menú:**
```bash
# Verificar registro ACL
sqlite3 /var/www/db/acl.db "SELECT * FROM acl_resource WHERE name='esp32-relay-issabel';"

# Verificar registro menú
sqlite3 /var/www/db/menu.db "SELECT * FROM menu WHERE id='esp32-relay-issabel';"

# Limpiar caché
rm -rf /var/www/html/admin/modules/_cache/*
service httpd restart
```

### **Error de base de datos:**
```bash
# Verificar tablas
mysql -u root -p asterisk -e "DESCRIBE esp32_config;"

# Recrear tablas si es necesario
mysql -u root -p asterisk < /var/www/html/modules/esp32-relay-issabel/sql/install.sql
```

### **AGI no funciona:**
```bash
# Verificar permisos
ls -la /var/lib/asterisk/agi-bin/esp32_relay_control.php
chown asterisk:asterisk /var/lib/asterisk/agi-bin/esp32_relay_control.php
chmod +x /var/lib/asterisk/agi-bin/esp32_relay_control.php
```

## 📞 **SOPORTE**

- **GitHub Issues**: https://github.com/BlackBoysNetworks/esp32-relay-issabel/issues
- **Logs importantes**:
  - `/var/log/asterisk/full`
  - `/var/log/httpd/error_log`
  - Pestaña "Log de Auditoría" en el módulo

---

## ⚠️ **NOTAS IMPORTANTES**

1. **Backup**: Siempre hacer backup antes de instalar
2. **Permisos**: Los permisos son críticos para el funcionamiento
3. **Firewall**: Asegurar conectividad con el ESP32
4. **Horarios**: Configurar correctamente los horarios de acceso
5. **Producción**: Desactivar modo simulación en producción

¡El módulo estará completamente funcional siguiendo estos pasos!