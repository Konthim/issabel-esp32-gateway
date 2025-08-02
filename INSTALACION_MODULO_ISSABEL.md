# Instalación del Módulo ESP32 Relay Control via Instalador de Issabel

## Archivo Generado
- **esp32_relay-1.0.0.tar.gz** - Paquete listo para instalar via interfaz web de Issabel

## Estructura del Paquete
```
esp32_relay/
├── index.php
├── configs/default.conf.php
├── lang/es.lang
├── lang/en.lang
├── themes/default/index.tpl
├── images/esp32.png
├── agi/esp32_relay_control.php
└── setup/build/1/
    ├── install.sql
    ├── menu.xml
    └── postinstall
```

## Instalación via Interfaz Web

1. **Acceder al instalador de módulos**:
   - Ir a **Sistema** → **Administrador de Módulos**

2. **Subir el módulo**:
   - Hacer clic en **"Subir Módulo"**
   - Seleccionar el archivo **esp32_relay-1.0.0.tar.gz**
   - Hacer clic en **"Instalar"**

3. **Configurar permisos**:
   - Ir a **Sistema** → **Permisos de Grupo**
   - Seleccionar grupo **administrator**
   - Expandir **PBX**
   - Marcar **ESP32 Relay Control**
   - Guardar cambios

4. **Acceder al módulo**:
   - Ir a **PBX** → **ESP32 Relay Control**

## Verificación Post-Instalación

- Verificar que aparece en el menú PBX
- Verificar acceso a las 3 pestañas: Log, Extensiones, Configuración
- Verificar que el script AGI se copió a `/var/lib/asterisk/agi-bin/`

## Desinstalación

Para desinstalar:
1. Ir a **Sistema** → **Administrador de Módulos**
2. Buscar **ESP32 Relay Control**
3. Hacer clic en **"Desinstalar"**