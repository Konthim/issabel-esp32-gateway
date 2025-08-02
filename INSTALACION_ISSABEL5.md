# Instalación del Módulo ESP32 Relay Control para Issabel 5

## Estructura Reparada

El módulo ha sido reparado para ser compatible con Issabel 5. Los cambios principales incluyen:

### Estructura de Archivos Corregida
```
esp32_relay/
├── index.php              # Controlador principal del módulo
├── configs/
│   └── default.conf.php   # Configuración del módulo
├── lang/
│   ├── es.lang           # Idioma español
│   └── en.lang           # Idioma inglés
├── themes/default/
│   └── index.tpl         # Template principal
└── images/
    └── esp32.png         # Icono del módulo
```

### Cambios Realizados

1. **Estructura de módulo**: Movido de `web/` a `esp32_relay/` siguiendo la estructura estándar de Issabel 5
2. **Configuración**: Agregado `configs/default.conf.php` requerido por Issabel 5
3. **Idiomas**: Creados archivos de idioma `es.lang` y `en.lang`
4. **Templates**: Adaptado el template para usar el sistema de Smarty de Issabel
5. **Base de datos**: Actualizado para usar SQLite para menús y permisos (Issabel 5)
6. **Instalación**: Script actualizado para la estructura de directorios de Issabel 5

## Instrucciones de Instalación

### Paso 1: Preparar archivos
```bash
cd /Users/mjara/Proyectos/html/modulo-Issabel
```

### Paso 2: Ejecutar instalación
```bash
sudo ./setup/install.sh
```

### Paso 3: Configurar permisos en Issabel
1. Ir a **Sistema** → **Permisos de Grupo**
2. Seleccionar grupo **administrator**
3. Expandir **PBX**
4. Marcar **ESP32 Relay Control**
5. Guardar cambios

### Paso 4: Acceder al módulo
- Ir a **PBX** → **ESP32 Relay Control**

## Verificación de la Instalación

1. **Verificar archivos del módulo**:
   ```bash
   ls -la /var/www/html/modules/esp32_relay/
   ```

2. **Verificar base de datos**:
   ```bash
   mysql -u root -peLaStIx.2oo7 asterisk -e "SHOW TABLES LIKE 'esp32%';"
   ```

3. **Verificar menú**:
   ```bash
   sqlite3 /var/www/db/menu.db "SELECT * FROM menu WHERE id = 'esp32_relay';"
   ```

4. **Verificar permisos**:
   ```bash
   sqlite3 /var/www/db/acl.db "SELECT * FROM acl_resource WHERE name = 'esp32_relay';"
   ```

## Solución de Problemas

### Error: Módulo no aparece en el menú
- Verificar que los archivos estén en `/var/www/html/modules/esp32_relay/`
- Verificar permisos: `chown -R asterisk:asterisk /var/www/html/modules/esp32_relay`
- Verificar entrada en base de datos de menú

### Error: Sin permisos para acceder
- Ir a Sistema → Permisos de Grupo
- Asignar permisos al grupo correspondiente

### Error: Base de datos
- Verificar que las tablas se crearon correctamente
- Ejecutar manualmente el SQL desde `sql/install.sql`

## Desinstalación

Para desinstalar el módulo:
```bash
sudo ./setup/uninstall.sh
```