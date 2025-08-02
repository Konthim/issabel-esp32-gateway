#!/bin/bash

echo "Instalando módulo ESP32 Relay Control para Issabel 5..."

# Crear directorio del módulo
mkdir -p /var/www/html/modules/esp32_relay

# Copiar archivos del módulo
cp -r esp32_relay/* /var/www/html/modules/esp32_relay/

# Copiar script AGI
cp agi/esp32_relay_control.php /var/lib/asterisk/agi-bin/
chmod +x /var/lib/asterisk/agi-bin/esp32_relay_control.php
chown asterisk:asterisk /var/lib/asterisk/agi-bin/esp32_relay_control.php

# Instalar base de datos
mysql -u root -peLaStIx.2oo7 asterisk < sql/install.sql

# Registrar módulo en Issabel 5
sqlite3 /var/www/db/acl.db "INSERT OR IGNORE INTO acl_resource (name, description) VALUES ('esp32_relay', 'ESP32 Relay Control');"
sqlite3 /var/www/db/menu.db "INSERT OR IGNORE INTO menu (id, IdParent, Link, Name, Type, order_no) VALUES ('esp32_relay', 'pbx', '', 'ESP32 Relay Control', 'module', 999);"

# Asignar permisos
chown -R asterisk:asterisk /var/www/html/modules/esp32_relay
chmod -R 755 /var/www/html/modules/esp32_relay

echo "Instalación completada."
echo ""
echo "PASOS SIGUIENTES:"
echo "1. Agregar la configuración del dialplan a /etc/asterisk/extensions_custom.conf"
echo "2. Ejecutar 'amportal reload' para aplicar cambios"
echo "3. Acceder al módulo desde: PBX -> Control Relé ESP32"
echo ""
echo "Configuración del dialplan a agregar:"
cat setup/extensions_custom.conf