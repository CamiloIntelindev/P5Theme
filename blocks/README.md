# Custom Gutenberg Blocks

Esta carpeta contiene todos los bloques personalizados de Gutenberg para el tema P5 Marketing.

## Estructura

```
blocks/
├── posts-grid.php          # Bloque de grilla de posts
├── testimonials.php        # (futuro) Bloque de testimonios
├── cta-banner.php          # (futuro) Bloque de banner CTA
└── README.md               # Este archivo
```

## Cómo Funciona

1. **Cada bloque = 1 archivo PHP** en esta carpeta
2. **El loader** (`inc/blocks-loader.php`) carga todos los bloques automáticamente
3. **El JavaScript** (`assets/js/blocks-editor.js`) proporciona la interfaz del editor
4. **Se integra** vía `functions.php`

## Crear un Nuevo Bloque

### 1. Crear archivo en `/blocks/nombre-bloque.php`

```php
<?php
/**
 * Nombre del Bloque
 * Descripción breve
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

function p5m_register_nombre_bloque_block() {
  if (!function_exists('register_block_type')) {
    return;
  }
  
  register_block_type('p5m/nombre-bloque', [
    'attributes' => [
      'titulo' => [
        'type' => 'string',
        'default' => 'Título por defecto',
      ],
      // ... más atributos
    ],
    'render_callback' => 'p5m_render_nombre_bloque_block',
  ]);
}

function p5m_render_nombre_bloque_block($attributes) {
  // HTML del bloque
  ob_start();
  ?>
  <div class="mi-bloque">
    <!-- Tu código HTML -->
  </div>
  <?php
  return ob_get_clean();
}
```

### 2. Agregar al loader (`inc/blocks-loader.php`)

```php
$blocks = [
  'posts-grid.php',
  'nombre-bloque.php',  // ← Agregar aquí
];
```

```php
function p5m_register_all_blocks() {
  p5m_register_posts_grid_block();
  p5m_register_nombre_bloque_block();  // ← Y aquí
}
```

### 3. Agregar JavaScript del editor (`assets/js/blocks-editor.js`)

```javascript
registerBlockType('p5m/nombre-bloque', {
  title: __('Nombre del Bloque', 'p5marketing'),
  icon: 'admin-post',
  category: 'p5m-blocks',
  
  edit: function(props) {
    // Controles del editor
  },
  
  save: function() {
    return null; // Bloque dinámico
  },
});
```

## Bloques Disponibles

### Posts Grid (`p5m/posts-grid`)

Grilla personalizable de posts con opciones completas.

**Atributos:**
- `columns` (1-6): Número de columnas
- `rows` (1-10): Número de filas aproximadas
- `postsPerPage` (1-50): Total de posts
- `postTypes` (array): Tipos de post a mostrar
- `showFeaturedImage` (bool): Mostrar imagen destacada
- `showTitle` (bool): Mostrar título
- `showExcerpt` (bool): Mostrar extracto
- `showAuthor` (bool): Mostrar autor
- `showDate` (bool): Mostrar fecha
- `showCategories` (bool): Mostrar categorías
- `showReadMore` (bool): Mostrar botón leer más
- `excerptLength` (5-100): Palabras del extracto
- `orderBy` (string): Campo de ordenamiento
- `order` (ASC/DESC): Dirección del ordenamiento
- `imageSize` (string): Tamaño de imagen
- `gapSize` (none/small/medium/large): Espaciado

**Uso:**
Agregar desde el editor Gutenberg → Buscar "Grilla de Posts" en la categoría "P5 Marketing Blocks"

## Ventajas de Esta Estructura

✅ **Modular**: Cada bloque en su propio archivo
✅ **Escalable**: Fácil agregar más bloques
✅ **Mantenible**: Código organizado y limpio
✅ **Reutilizable**: Bloques independientes
✅ **Performance**: Solo se carga lo necesario

## Próximos Bloques Sugeridos

- [ ] Testimonials Grid (grilla de testimonios con estrellas)
- [ ] CTA Banner (banner de llamado a la acción personalizable)
- [ ] Services Grid (grilla de servicios con iconos)
- [ ] Team Members (equipo con fotos y bio)
- [ ] Pricing Tables (tablas de precios comparativas)
- [ ] FAQ Accordion (preguntas frecuentes con acordeón)
- [ ] Stats Counter (contadores animados)
- [ ] Timeline (línea de tiempo de eventos)

## Recursos

- [WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [Register Block Type](https://developer.wordpress.org/reference/functions/register_block_type/)
- [Block API Reference](https://developer.wordpress.org/block-editor/reference-guides/block-api/)
