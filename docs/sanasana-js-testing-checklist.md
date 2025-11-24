# ✅ Checklist de Testing - Sanasana JS Optimizations

## Información General
- **Fecha**: 2025-11-17
- **Versión Plugin**: 1.0.4
- **Archivos**: script-price-optimized.min.js, script-tabs-optimized.min.js, script-general-optimized.min.js

## Scripts Cargados Correctamente ✅
```bash
✅ script-price-optimized.min.js (4.5 KB) - ACTIVO
✅ script-tabs-optimized.min.js (3.9 KB) - ACTIVO  
✅ script-general-optimized.min.js (516 B) - ACTIVO
```

## Tests Funcionales

### 1. Price Toggle (script-price-optimized.min.js)
- [ ] Toggle mensual/anual cambia valores correctamente
- [ ] Animación de transición fluida
- [ ] Valores data-monthly y data-annual se actualizan
- [ ] Texto del período cambia (/mes vs /año)
- [ ] No hay errores en consola

**Casos de prueba:**
1. Click en toggle → precios cambian de 29/59/99 a 290/590/990
2. Click de nuevo → regresan a valores mensuales
3. Scroll en página con precios → no hay lag (60fps)

### 2. Tabs Horizontales (script-tabs-optimized.min.js)
- [ ] Click en tab cambia contenido
- [ ] Progress bar se anima correctamente
- [ ] Solo un tab activo a la vez
- [ ] Clase 'active' se añade/remueve
- [ ] No hay errores en consola

**Casos de prueba:**
1. Click tab "Precios" → muestra contenido correcto
2. Click tab "Soporte" → oculta anterior, muestra nuevo
3. Resize ventana → tabs responden correctamente

### 3. Tabs Verticales (script-tabs-optimized.min.js)
- [ ] Click en tab vertical funciona
- [ ] Contenido se intercambia correctamente
- [ ] Clases active se manejan bien
- [ ] No hay errores en consola

### 4. FAQ Accordion (script-tabs-optimized.min.js)
- [ ] Click en pregunta expande respuesta
- [ ] Click de nuevo colapsa
- [ ] Múltiples FAQs pueden estar abiertas
- [ ] Animación smooth de expand/collapse
- [ ] No hay errores en consola

**Casos de prueba:**
1. Click FAQ 1 → expande
2. Click FAQ 2 → expande (FAQ 1 permanece abierto)
3. Click FAQ 1 de nuevo → colapsa

### 5. NotifyController (script-general-optimized.min.js)
- [ ] window.notifyController existe
- [ ] notifyController.success() muestra notificación verde
- [ ] notifyController.error() muestra notificación roja
- [ ] notifyController.showMessage() funciona con tipo personalizado
- [ ] Si Notyf no carga, fallback a console.log funciona
- [ ] No hay errores en consola

**Casos de prueba:**
1. Ejecutar en consola: `notifyController.success('Test')`
2. Ejecutar: `notifyController.error('Test error')`
3. Verificar estilos personalizados (colores, iconos)

## Performance Validation

### Chrome DevTools
- [ ] Network tab muestra 3 archivos JS (no 5)
- [ ] Tamaño total JS: ~9 KB (antes ~14 KB)
- [ ] No hay requests a script-tabs.js, script-tabs-horizontal.js, script-tabs-vertical.js
- [ ] Cache headers correctos (version 1.0.4)

### Performance Tab
- [ ] Scroll a 60fps constante
- [ ] No hay Long Tasks >50ms en scroll
- [ ] Time to Interactive mejorado vs versión anterior

### Console
- [ ] Sin errores JavaScript
- [ ] Sin warnings de funciones deprecated
- [ ] Logs de inicialización correctos:
  ```
  ✅ NotifyController: Object
  ✅ TabsManager: Object
  ```

## Métricas Esperadas

### Antes (versión 1.0.3)
- **JS Total**: ~14 KB
- **HTTP Requests**: 5 scripts
- **Scroll FPS**: 30-50fps variable
- **TTI**: baseline

### Después (versión 1.0.4) ✅
- **JS Total**: ~8.9 KB (-36%)
- **HTTP Requests**: 3 scripts (-40%)
- **Scroll FPS**: 60fps constante
- **TTI**: ~100-200ms mejor

## Navegadores Testeados
- [ ] Chrome (última versión)
- [ ] Safari (última versión)
- [ ] Firefox (última versión)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

## Rollback Plan
Si algún test falla crítico:

```php
// En /wp-content/plugins/sanasana/inc/General/EnqueueController.php
// Reemplazar líneas ~43-50 con:

wp_enqueue_script('scripts-price', $this->plugin_url . 'assets/js/script-price.js', ['jquery'], $this->version, true);
wp_enqueue_script('scripts-tabs', $this->plugin_url . 'assets/js/script-tabs.js', ['jquery'], $this->version, true);
wp_enqueue_script('scripts-tabs-horizontal', $this->plugin_url . 'assets/js/script-tabs-horizontal.js', ['jquery'], $this->version, true);
wp_enqueue_script('scripts-tabs-vertical', $this->plugin_url . 'assets/js/script-tabs-vertical.js', ['jquery'], $this->version, true);
wp_enqueue_script('script-general', $this->plugin_url . 'assets/js/script-general.js', ['jquery'], $this->version, true);

// Cambiar version a 1.0.3 para cache bust
```

## Notas de Testing
<!-- Agregar observaciones durante testing -->

---
**Status**: ⏳ EN PROGRESO
**Último update**: 2025-11-17
