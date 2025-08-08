# 🔑 Instrucciones para subir a GitHub

## Problema de autenticación resuelto

El repositorio está configurado correctamente. Para subir el código:

## 1. Crear token de acceso personal
1. Ve a GitHub → Settings → Developer settings → Personal access tokens
2. Generate new token (classic)
3. Selecciona scopes: `repo` (acceso completo a repositorios)
4. Copia el token generado

## 2. Subir código usando token
```bash
cd /var/www/html/modules/esp32-relay-issabel

# Cuando te pida password, usa el TOKEN (no tu password de GitHub)
git push -u origin main
git push origin --tags
```

## 3. Alternativa: Configurar credenciales
```bash
git config --global user.name "Konthim"
git config --global user.email "tu-email@ejemplo.com"

# Usar token como password
git push -u origin main
```

## ✅ Repositorio configurado:
- **Usuario**: Konthim  
- **Repo**: issabel-esp32-gateway
- **URL**: https://github.com/Konthim/issabel-esp32-gateway.git

## 📦 Contenido listo para subir:
- ✅ Dialplan con clave 100
- ✅ Guías de instalación
- ✅ README actualizado  
- ✅ Código ESP32
- ✅ Tag v1.1.0