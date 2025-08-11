# Changelog - ESP32 Relay Control Module

## [v2.1.0] - 2025-08-11

### 🔧 ETIQUETAS DE AUDITORÍA EN ESPAÑOL
- **Cambio de etiquetas del sistema:**
  - `OK` → `APROBADO` ✅
  - `UNAUTHORIZED` → `NO AUTORIZADO` 🚫
  - `FAILED_ATTEMPTS` → `DENEGADO` ❌
- **Actualización automática** de logs históricos en base de datos
- **Iconos visuales distintivos** para cada tipo de resultado
- **Colores mejorados** en badges de estado para mejor identificación

### 🔍 FILTRO AVANZADO EN LOG DE AUDITORÍA
- **Filtro por rango de fechas:** Desde/Hasta para búsquedas temporales
- **Filtro por extensión:** Buscar registros de números específicos
- **Filtro por resultado:** APROBADO/DENEGADO/NO AUTORIZADO
- **Botón 'Limpiar'** para resetear todos los filtros
- **Filtros combinables** para búsquedas precisas y detalladas
- **Implementación dual** en ambos archivos index.php

### 🔒 SISTEMA DE BLOQUEO/DESBLOQUEO
- **Botones de acción directos** en log de auditoría
- **Bloquear extensiones activas** con un solo clic
- **Desbloquear extensiones bloqueadas** fácilmente
- **Confirmaciones de seguridad** para prevenir acciones accidentales
- **Estados visuales claros:**
  - 🟢 Activo → Botón "Bloquear"
  - 🔴 Bloqueado → Botón "Desbloquear"
  - ⚪ No registrado → Indicador visual
- **Gestión rápida** sin necesidad de navegar entre pestañas

### ⚡ MEJORAS TÉCNICAS
- **Consultas SQL optimizadas** con LEFT JOIN para mejor rendimiento
- **Manejo robusto** de estados de extensiones
- **JavaScript mejorado** para interacciones fluidas
- **Interfaz responsive** y moderna con Bootstrap 5
- **Compatibilidad completa** con etiquetas antiguas y nuevas

### 📊 FUNCIONALIDADES AGREGADAS
- **Nueva columna 'Acciones'** en tabla de logs
- **Detección automática** del estado de extensiones
- **Mensajes de confirmación** y notificaciones de éxito
- **Límite optimizado** de registros (50-100) para mejor rendimiento
- **Exportación CSV** que respeta filtros aplicados

### 🎨 MEJORAS DE UX/UI
- **Iconos Font Awesome** para mejor visualización
- **Colores distintivos** por tipo de resultado
- **Layout reorganizado** para mejor aprovechamiento del espacio
- **Botones intuitivos** con tooltips informativos
- **Experiencia de usuario** fluida y sin interrupciones

### 🔄 VALIDACIÓN DE HORARIOS (Versión anterior)
- Sistema de validación precisa por minutos desde medianoche
- Consultas MySQL separadas para mayor confiabilidad
- Comparación exacta de horarios HH:MM:SS
- Logs detallados para debugging y monitoreo

---

## Archivos Modificados
- `index.php` - Interfaz principal con nuevas funcionalidades
- `web/index.php` - Interfaz web con filtros y bloqueo
- `setup/extensions_custom.conf` - Etiquetas actualizadas
- `push_to_github.sh` - Script de deployment

## Instalación
1. Clonar el repositorio
2. Ejecutar `setup/install.sh`
3. Configurar base de datos
4. Recargar dialplan de Asterisk

## Uso
1. Acceder al módulo desde Issabel Web
2. Configurar extensiones autorizadas
3. Usar filtros en Log de Auditoría
4. Bloquear/Desbloquear extensiones según necesidad