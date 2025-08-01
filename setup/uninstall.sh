#!/bin/bash

echo "Desinstalando módulo ESP32 Relay Control..."

# Eliminar archivos web
rm -rf /var/www/html/admin/modules/esp32_relay

# Eliminar script AGI
rm -f /var/lib/asterisk/agi-bin/esp32_relay_control.php

# Eliminar de ACL (opcional - mantener datos)
read -p "¿Eliminar tablas de base de datos? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    mysql -u root -peLaStIx.2oo7 asterisk -e "
    DROP TABLE IF EXISTS esp32_access_log;
    DROP TABLE IF EXISTS esp32_config;
    DROP TABLE IF EXISTS esp32_authorized_extensions;
    DELETE FROM acl_resource WHERE description = 'esp32_relay';
    "
    echo "Tablas eliminadas."
fi

echo "Desinstalación completada."
echo "Recuerda eliminar manualmente la configuración del dialplan de extensions_custom.conf"