# Guía de Optimización de Imágenes

## Descripción General

El tema P5 Marketing incluye un sistema automático de optimización de imágenes que mejora significativamente el rendimiento y las métricas de PageSpeed Insights, especialmente el LCP (Largest Contentful Paint).

## Características

### 1. Compresión Automática
- **Calidad ajustable**: Define la calidad de compresión JPEG/WebP (60-100%)
- **Por defecto**: 82% (balance óptimo entre calidad y peso)
- **Aplicación**: Automática al subir imágenes

### 2. Conversión a WebP
- **Automática**: Genera versiones WebP de todas las imágenes JPEG/PNG
- **Reducción de peso**: 25-35% menos tamaño de archivo
- **Compatibilidad**: Fallback automático a formato original
- **Implementación**: Element `<picture>` con `<source type="image/webp">`

### 3. Redimensionamiento Inteligente
- **Ancho máximo configurable**: Por defecto 2560px
- **Rango**: 1200px - 4000px
- **Mantiene proporciones**: Redimensiona solo si excede el máximo
- **Tipos soportados**: JPEG, PNG (excluye SVG y GIF)

### 4. Imágenes Críticas (LCP)
- **Loading eager**: Carga inmediata para imágenes críticas
- **Fetchpriority high**: Prioridad alta en la descarga
- **Casos de uso**: Hero images, logos principales, banners above-the-fold

## Configuración en WordPress Admin

### Ubicación
**Apariencia → P5 Settings → Optimización de Imágenes**

### Campos Disponibles

#### 1. Calidad de compresión JPEG/WebP (%)
```
Valor: 60-100
Por defecto: 82
Recomendado: 75-85
```
- **Menor valor**: Archivos más pequeños, menor calidad
- **Mayor valor**: Mejor calidad, archivos más grandes

#### 2. Convertir a WebP automáticamente
```
Checkbox: Activado/Desactivado
Por defecto: Activado
```
Genera versiones WebP de todas las imágenes subidas.

#### 3. URLs de imágenes críticas (eager loading)
```
Tipo: Textarea (una URL por línea)
Ejemplo:
https://ejemplo.com/wp-content/uploads/2024/hero-banner.jpg
/wp-content/uploads/logo-principal.png
```

**Cuándo usar:**
- Hero images que aparecen inmediatamente
- Logos en el header
- Banners principales above-the-fold
- Cualquier imagen que afecte el LCP

**Efecto:**
- `loading="eager"` (carga inmediata)
- `fetchpriority="high"` (prioridad máxima)

#### 4. URLs específicas para optimizar (de PageSpeed)
```
Tipo: Textarea (una URL por línea)
Ejemplo:
https://ejemplo.com/wp-content/uploads/2024/imagen-pesada.jpg
/wp-content/uploads/banner-grande.png
```

**Cómo usar con PageSpeed Insights:**

1. Ejecuta análisis en PageSpeed Insights
2. Busca la sección "Reduce el tiempo de descarga de imágenes"
3. Copia las URLs que recomienda optimizar
4. Pégalas en este campo (una por línea)
5. Guarda cambios
6. El tema intentará:
   - Re-comprimir la imagen
   - Generar versión WebP
   - Aplicar optimizaciones automáticas

#### 5. Ancho máximo de imágenes (px)
```
Valor: 1200-4000
Por defecto: 2560
Recomendado: 2048-2560
```

**Pantallas 4K**: 2560px es suficiente
**Pantallas Retina**: 2048px cubre la mayoría de casos

## Flujo de Trabajo Recomendado

### Para Nuevas Imágenes
1. Sube la imagen normalmente en WordPress
2. El tema automáticamente:
   - Redimensiona si excede el ancho máximo
   - Comprime según la calidad configurada
   - Genera versión WebP
   - Crea WebP para todos los tamaños (thumbnail, medium, large, etc.)

### Para PageSpeed Insights
1. **Ejecuta análisis inicial**
2. **Identifica imágenes problemáticas:**
   - Largest Contentful Paint (LCP)
   - Reduce el tiempo de descarga de imágenes
3. **Configura imágenes críticas:**
   - Copia URL de la imagen LCP
   - Agrégala en "URLs de imágenes críticas"
4. **Optimiza imágenes específicas:**
   - Copia URLs de imágenes pesadas
   - Agrégalas en "URLs específicas para optimizar"
5. **Guarda y vuelve a testear**

### Ejemplo Práctico

**Antes:**
```
PageSpeed Score: 65/100
LCP: 3.8s
Imagen hero: 850KB (JPEG)
```

**Configuración aplicada:**
```
Calidad: 78%
WebP: Activado
Imagen crítica: /uploads/2024/hero.jpg
Ancho máximo: 2048px
```

**Después:**
```
PageSpeed Score: 92/100
LCP: 1.2s
Imagen hero: 145KB (WebP) / 320KB (JPEG fallback)
```

## Código Técnico

### Filtros Aplicados

```php
// Calidad de compresión
add_filter('jpeg_quality', function($quality) {
  return intval(p5m_get_setting('image_quality', 82));
});

// Atributos de imágenes críticas
add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) {
  $critical_images = p5m_get_setting('critical_images', '');
  // ... lógica de detección ...
  if ($is_critical) {
    $attr['fetchpriority'] = 'high';
    $attr['loading'] = 'eager';
  }
  return $attr;
}, 10, 3);

// Redimensionamiento automático
add_filter('wp_handle_upload_prefilter', function($file) {
  $max_width = intval(p5m_get_setting('max_image_width', 2560));
  // ... lógica de resize ...
});

// Generación de WebP
add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
  // ... genera WebP para todas las versiones ...
}, 10, 2);
```

### Elemento Picture Generado

```html
<picture>
  <source type="image/webp" srcset="imagen.webp">
  <img src="imagen.jpg" alt="..." loading="lazy" decoding="async">
</picture>
```

## Beneficios Medibles

### Performance
- **LCP mejorado**: 30-60% más rápido
- **Peso reducido**: 25-35% menos bytes
- **Tiempo de descarga**: 40-50% más rápido

### PageSpeed Insights
- **Performance Score**: +15 a +30 puntos típicamente
- **Métricas Core Web Vitals**: Todas mejoradas
- **Reduce el tiempo de descarga**: ✓ Resuelto
- **Usa formatos modernos**: ✓ WebP implementado

### SEO
- **Ranking mejorado**: Google prioriza sitios rápidos
- **Mobile-first indexing**: Mejor experiencia móvil
- **User engagement**: Menos rebote por carga lenta

## Troubleshooting

### WebP no se genera
- Verificar que PHP tenga soporte GD con WebP: `php -i | grep -i webp`
- Verificar permisos de escritura en uploads
- Revisar logs de errores PHP

### Imágenes no se redimensionan
- Verificar que `max_image_width` esté configurado
- Verificar que las imágenes sean JPEG/PNG (no SVG/GIF)
- Comprobar límites de memoria PHP

### Loading eager no aplica
- Verificar que la URL en "imágenes críticas" coincida
- Puede ser URL completa o parcial
- Revisar con DevTools que el atributo esté presente

## Recomendaciones Adicionales

1. **CDN**: Combinar con Cloudflare u otro CDN para mejor distribución
2. **Lazy loading nativo**: Ya implementado automáticamente
3. **Dimensiones explícitas**: Siempre define width/height para evitar CLS
4. **Formato correcto**: JPEG para fotos, PNG para logos/transparencias
5. **Revisión periódica**: Re-testear con PageSpeed cada mes

## Recursos

- [Google PageSpeed Insights](https://pagespeed.web.dev/)
- [Web.dev - Optimize LCP](https://web.dev/optimize-lcp/)
- [MDN - Picture Element](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/picture)
- [Can I Use - WebP](https://caniuse.com/webp)
