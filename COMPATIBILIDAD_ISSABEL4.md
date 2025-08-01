# ⚠️ COMPATIBILIDAD CON ISSABEL 4

## 🔍 ANÁLISIS DE COMPATIBILIDAD

### ❌ **PROBLEMAS IDENTIFICADOS:**

1. **Estructura de directorios diferente**
   - Issabel 4: `/var/www/html/modules/`
   - Issabel 5: `/var/www/html/admin/modules/`

2. **Sistema de menús**
   - Issabel 4: Usa sistema de menús XML diferente
   - Issabel 5: Estructura de menús actualizada

3. **Clases PHP**
   - `paloDB` puede tener diferencias entre versiones
   - `paloSantoGrid` y `paloSantoForm` pueden no existir en Issabel 4

4. **Base de datos**
   - Tablas ACL pueden tener estructura diferente
   - Credenciales por defecto pueden cambiar

5. **Bootstrap/CSS**
   - Issabel 4 usa versiones más antiguas de Bootstrap
   - Estilos CSS pueden no ser compatibles

### ✅ **COMPONENTES QUE SÍ FUNCIONARÍAN:**

1. **Script AGI** (`esp32_relay_control.php`)
   - Funcionalidad básica compatible
   - Requiere ajustes menores en rutas

2. **Tablas SQL**
   - Estructura de base de datos compatible
   - MySQL funciona igual

3. **Lógica de negocio**
   - Validación de horarios
   - Control de relé ESP32
   - Sistema de auditoría

## 📊 **ESTIMACIÓN DE COMPATIBILIDAD: 40%**

### 🔧 **MODIFICACIONES NECESARIAS:**

1. **Cambiar rutas de instalación**
2. **Adaptar sistema de menús**
3. **Reescribir interfaz web** (sin Bootstrap 5)
4. **Ajustar clases PHP** a versión Issabel 4
5. **Modificar permisos ACL**

## 🎯 **CONCLUSIÓN:**

**NO es directamente compatible con Issabel 4.**

El módulo requeriría una **reescritura significativa** (60% del código) para funcionar en Issabel 4, especialmente:
- Interfaz web completa
- Sistema de menús
- Integración con framework de Issabel 4

## 💡 **RECOMENDACIÓN:**

Si necesitas usar Issabel 4, sería más eficiente:
1. **Actualizar a Issabel 5** (recomendado)
2. O crear una **versión simplificada** solo con AGI y configuración manual

El módulo actual está **optimizado para Issabel 5** y aprovecha sus características modernas.