#!/bin/bash

echo "🚀 Creando paquete para instalación en producción..."

# Crear directorio de distribución
mkdir -p dist/esp32-relay-module

# Copiar archivos necesarios
cp -r agi/ dist/esp32-relay-module/
cp -r sql/ dist/esp32-relay-module/
cp -r setup/ dist/esp32-relay-module/
cp -r web/ dist/esp32-relay-module/
cp INSTALACION_PRODUCCION.md dist/esp32-relay-module/
cp README.md dist/esp32-relay-module/

# Crear archivo de versión
echo "ESP32 Relay Control Module v1.0" > dist/esp32-relay-module/VERSION
echo "Fecha: $(date)" >> dist/esp32-relay-module/VERSION

# Crear tarball
cd dist/
tar -czf esp32-relay-module-v1.0.tar.gz esp32-relay-module/

echo "✅ Paquete creado: dist/esp32-relay-module-v1.0.tar.gz"
echo ""
echo "📋 Para instalar en producción:"
echo "1. Subir el archivo .tar.gz al servidor Issabel"
echo "2. Extraer: tar -xzf esp32-relay-module-v1.0.tar.gz"
echo "3. Seguir las instrucciones en INSTALACION_PRODUCCION.md"