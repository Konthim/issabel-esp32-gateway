# 📁 Archivos Importantes del Proyecto

## 🔧 Archivos de Configuración

### Dialplan de Asterisk
- **Archivo activo**: `/etc/asterisk/extensions_custom.conf`
- **Archivo del módulo**: `setup/extensions_custom.conf` (plantilla)

**⚠️ IMPORTANTE**: 
- Asterisk lee SOLO el archivo en `/etc/asterisk/`
- El archivo en `setup/` es solo una plantilla de referencia
- Para cambios, editar `/etc/asterisk/extensions_custom.conf`

## 📝 Flujo de Configuración

### 1. Instalación inicial
```bash
# Copiar plantilla al directorio de Asterisk
cp setup/extensions_custom.conf /etc/asterisk/extensions_custom.conf
```

### 2. Modificaciones posteriores
```bash
# Editar directamente el archivo de Asterisk
nano /etc/asterisk/extensions_custom.conf

# Recargar configuración
asterisk -rx "dialplan reload"
```

### 3. Verificar configuración activa
```bash
asterisk -rx "dialplan show from-internal-custom"
```

## 🎯 Archivos por Función

### Dialplan (Control de llamadas)
- **Activo**: `/etc/asterisk/extensions_custom.conf`
- **Plantilla**: `setup/extensions_custom.conf`

### Scripts AGI (Lógica de negocio)
- **Activo**: `/var/lib/asterisk/agi-bin/esp32_relay_control.php`
- **Fuente**: `agi/esp32_relay_control.php`

### Interfaz Web (Administración)
- **Activo**: `/var/www/html/admin/modules/esp32_relay/`
- **Fuente**: `web/`

### Base de Datos (Configuración)
- **Script**: `sql/install.sql`
- **Tablas**: `esp32_config`, `esp32_access_log`, etc.

## 🔄 Sincronización de Archivos

Si modificas la plantilla en `setup/`, recuerda:
1. Copiar cambios a `/etc/asterisk/extensions_custom.conf`
2. Recargar dialplan: `asterisk -rx "dialplan reload"`
3. Probar la funcionalidad