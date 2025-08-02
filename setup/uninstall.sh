#!/bin/bash

echo "Desinstalando módulo ESP32 Relay Control..."

# Eliminar directorio del módulo
rm -rf /var/www/html/modules/esp32_relay

# Eliminar script AGI
rm -f /var/lib/asterisk/agi-bin/esp32_relay_control.php

# Eliminar tablas de base de datos
mysql -u root -peLaStIx.2oo7 asterisk -e "
DROP TABLE IF EXISTS esp32_access_log;
DROP TABLE IF EXISTS esp32_config;
DROP TABLE IF EXISTS esp32_authorized_extensions;
"

# Eliminar registros del menú y permisos
sqlite3 /var/www/db/acl.db "DELETE FROM acl_resource WHERE name = 'esp32_relay';"
sqlite3 /var/www/db/menu.db "DELETE FROM menu WHERE id = 'esp32_relay';"

echo "Desinstalación completada."