#!/bin/bash

# Script de desinstalación automática del módulo ESP32 Relay Control
# Uso: ./uninstall.sh

echo "🗑️ Iniciando desinstalación del módulo ESP32 Relay Control..."
echo ""

# Verificar si se ejecuta como root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Este script debe ejecutarse como root"
    echo "   Usa: sudo ./uninstall.sh"
    exit 1
fi

# Solicitar confirmación
read -p "⚠️  ¿Estás seguro de que quieres desinstalar el módulo ESP32 Relay Control? (s/N): " confirm
if [[ ! $confirm =~ ^[Ss]$ ]]; then
    echo "❌ Desinstalación cancelada"
    exit 0
fi

echo ""
echo "🔄 Eliminando archivos del módulo..."

# Eliminar archivos del módulo
if [ -d "/var/www/html/modules/esp32-relay-issabel" ]; then
    rm -rf /var/www/html/modules/esp32-relay-issabel/
    echo "✅ Directorio del módulo eliminado"
else
    echo "⚠️  Directorio del módulo no encontrado"
fi

# Eliminar script AGI
if [ -f "/var/lib/asterisk/agi-bin/esp32_relay_control.php" ]; then
    rm -f /var/lib/asterisk/agi-bin/esp32_relay_control.php
    echo "✅ Script AGI eliminado"
else
    echo "⚠️  Script AGI no encontrado"
fi

echo ""
echo "🗄️ Eliminando tablas de base de datos..."

# Solicitar contraseña de MySQL
read -s -p "🔑 Ingresa la contraseña de MySQL root: " mysql_password
echo ""

# Eliminar tablas de base de datos
mysql -u root -p$mysql_password asterisk << 'EOF' 2>/dev/null
DROP TABLE IF EXISTS esp32_config;
DROP TABLE IF EXISTS esp32_access_log;
DROP TABLE IF EXISTS esp32_authorized_extensions;
EOF

if [ $? -eq 0 ]; then
    echo "✅ Tablas de base de datos eliminadas"
else
    echo "❌ Error al eliminar tablas de base de datos"
fi

echo ""
echo "📋 Eliminando registros de menú y ACL..."

# Eliminar registro del menú
if [ -f "/var/www/db/menu.db" ]; then
    sqlite3 /var/www/db/menu.db "DELETE FROM menu WHERE id='esp32-relay-issabel';" 2>/dev/null
    echo "✅ Registro de menú eliminado"
else
    echo "⚠️  Base de datos de menú no encontrada"
fi

# Eliminar registro ACL
if [ -f "/var/www/db/acl.db" ]; then
    sqlite3 /var/www/db/acl.db "DELETE FROM acl_resource WHERE name='esp32-relay-issabel';" 2>/dev/null
    echo "✅ Registro ACL eliminado"
else
    echo "⚠️  Base de datos ACL no encontrada"
fi

echo ""
echo "🔄 Recargando configuración..."

# Recargar configuración
amportal reload >/dev/null 2>&1
service httpd restart >/dev/null 2>&1

echo "✅ Configuración recargada"

echo ""
echo "🎉 ¡Desinstalación completada exitosamente!"
echo ""
echo "⚠️  RECORDATORIO:"
echo "   - El módulo 'Control Relé ESP32' ya no aparecerá en el menú"
echo "   - Si configuraste el dialplan (extensión 8000), elimínalo manualmente de:"
echo "     /etc/asterisk/extensions_custom.conf"
echo ""
echo "📋 Para verificar que se eliminó completamente, ejecuta:"
echo "   ls -la /var/www/html/modules/ | grep esp32"
echo "   mysql -u root -p asterisk -e \"SHOW TABLES LIKE 'esp32%';\""
echo ""