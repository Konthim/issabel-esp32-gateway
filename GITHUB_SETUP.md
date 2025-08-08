# 🚀 Instrucciones para subir a GitHub

## 1. Crear repositorio en GitHub
1. Ve a https://github.com/new
2. Nombre: `issabel-esp32-gateway`
3. Descripción: `🔌 Gateway ESP32 para control de relés via llamadas telefónicas en Issabel PBX`
4. Público/Privado según prefieras
5. NO inicializar con README (ya tenemos uno)

## 2. Conectar repositorio local
```bash
cd /var/www/html/modules/esp32-relay-issabel

# Agregar remote origin (reemplaza TU_USUARIO)
git remote add origin https://github.com/TU_USUARIO/issabel-esp32-gateway.git

# Subir código y tags
git push -u origin main
git push origin --tags
```

## 3. Verificar subida
- Ve a tu repositorio en GitHub
- Verifica que todos los archivos estén presentes
- Comprueba que el tag v1.1.0 aparezca en Releases

## 📁 Archivos incluidos en esta versión:
- ✅ Dialplan actualizado con clave 100
- ✅ Guía de instalación (INSTALL.md)
- ✅ README actualizado
- ✅ Código ESP32 de ejemplo
- ✅ Scripts de instalación
- ✅ Estructura completa del módulo

## 🔧 Configuración actual:
- **Extensión**: 8000
- **Clave**: 100
- **IP ESP32**: 192.168.1.26
- **Token**: mi_token_secreto
- **Intentos máximos**: 3