#!/bin/bash

echo "Instalando módulo ESP32 Relay Control para Issabel..."

# Crear directorio del módulo
mkdir -p /var/www/html/admin/modules/esp32_relay

# Copiar archivos web
cp -r web/* /var/www/html/admin/modules/esp32_relay/

# Copiar script AGI
cp agi/esp32_relay_control.php /var/lib/asterisk/agi-bin/
chmod +x /var/lib/asterisk/agi-bin/esp32_relay_control.php
chown asterisk:asterisk /var/lib/asterisk/agi-bin/esp32_relay_control.php

# Instalar base de datos
mysql -u root -peLaStIx.2oo7 asterisk < sql/install.sql

# Registrar módulo en Issabel
mysql -u root -peLaStIx.2oo7 asterisk -e "
INSERT IGNORE INTO acl_resource (id, description) VALUES (NULL, 'esp32_relay');
INSERT IGNORE INTO acl_resource_group (id, id_resource, id_group) 
SELECT NULL, r.id, 1 FROM acl_resource r WHERE r.description = 'esp32_relay';
"

# Recargar menús
/usr/bin/amportal admin reload

echo "Instalación completada."
echo ""
echo "PASOS SIGUIENTES:"
echo "1. Agregar la configuración del dialplan a /etc/asterisk/extensions_custom.conf"
echo "2. Ejecutar 'amportal reload' para aplicar cambios"
echo "3. Acceder al módulo desde: PBX -> Control Relé ESP32"
echo ""
echo "Configuración del dialplan a agregar:"
cat setup/extensions_custom.conf