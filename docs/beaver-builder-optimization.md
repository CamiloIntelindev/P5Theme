# Beaver Builder Asset Optimization

Este documento describe la optimización condicional de los assets de Beaver Builder para mejorar el rendimiento en páginas que no usan el constructor.

## Objetivo
Evitar cargar CSS y JS de Beaver Builder en páginas donde no se está usando el constructor, manteniendo intactas las páginas construidas con BB.

## Mecanismo
En `functions.php` se define `p5m_conditionally_optimize_bb_assets()` (hook: `wp_enqueue_scripts` prioridad 999) que:
1. Verifica si la clase `FLBuilderModel` existe (plugin activo).
2. Detecta si la página actual usa BB mediante `FLBuilderModel::is_builder_enabled($post_id)` o fallback al post meta `_fl_builder_enabled`.
3. Si no es página BB, des-encola handles globales y específicos del layout generado.

## Assets desencolados
| Tipo | Handles |
|------|---------|
| Layout-specific CSS | `fl-builder-layout-{$post_id}`, `fl-builder-layout-bundle-{$post_id}` |
| Layout-specific JS | `fl-builder-layout-{$post_id}` |
| Global CSS | `font-awesome-5`, `foundation-icons`, `fl-slideshow`, `fl-builder-layout-bundle` |
| Global JS | `jquery-waypoints`, `imagesloaded`, `fl-slideshow`, `yui3`, `youtube-player`, `vimeo-player` |

## Detección de páginas BB
El plugin marca posts habilitados con BB mediante:
- Función estática: `FLBuilderModel::is_builder_enabled($post_id)` (recomendado, verifica contexto y plantillas globales).
- Post meta: `_fl_builder_enabled` = `1` (fallback directo).

## Casos cubiertos
- Posts/páginas singulares con BB: mantienen todos sus assets.
- Posts sin BB: se eliminan todos los handles globales + layout.
- Archives y listados: la detección ocurre por post_id de contexto; si el template de archivo no usa BB (Theme Builder desactivado) se limpia.

## Rollback rápido
Eliminar o comentar:
```php
add_action('wp_enqueue_scripts', 'p5m_conditionally_optimize_bb_assets', 999);
```
Todos los assets de BB volverán a cargarse en todas las páginas.

## Extensiones recomendadas
- **Preload crítico**: Si algunas páginas siempre usan BB (home, landing), puedes forzar preload de `font-awesome-5` con `<link rel="preload">` inline.
- **Lazy load de módulos específicos**: Beaver Builder permite deshabilitar módulos no usados vía `FLBuilder::$enabled_modules`. Combina con esta optimización para no cargar módulos innecesarios (Contact Form, Counter, etc).
- **Critical CSS**: Extraer el CSS above-fold del layout y dejarlo inline; defer el bundle completo.
- **HTTP/2 Push**: Si usas H2, considera push de layout CSS crítico en páginas BB.

## Consideraciones de seguridad
- La detección es post-level; páginas de archivo o listados dependen de `FLBuilderModel::is_builder_enabled()` sin post_id (fallback conservador).
- Templates de Theme Builder (header/footer/singular globales): si están activos, BB detecta correctamente y mantendrá assets.

## Testing rápido
1. Página sin BB (post estándar sin layout): inspeccionar Network → CSS/JS → no deben aparecer `font-awesome-5`, `fl-builder-layout-X`.
2. Página con BB (layout creado): verificar carga de layout CSS + JS; widgets/módulos funcionan.
3. Plantilla Theme Builder (ej. header/footer personalizado): confirmar que BB assets persisten.

## Métricas esperadas
- Páginas sin BB: reducción de ~80-150 KB (CSS) + ~60-100 KB (JS) según módulos/icons usados en otras páginas.
- TTI/FCP mejora estimada: 5-15% en páginas sin constructor.

## Compatibilidad
- Compatible con Beaver Builder 2.x y superior.
- No interfiere con modo edición del builder (admin bar "Launch Beaver Builder").
- Respeta cachés WP Rocket/LiteSpeed (purgar después de activar).

---
Última actualización: 2025-11-17
