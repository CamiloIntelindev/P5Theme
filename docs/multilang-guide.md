# P5M Multilanguage System - Guía de Uso

## 🚀 Sistema de traducción ligero ES/EN sin WPML

### ✅ Instalación

El sistema ya está activo. Solo necesitas:

1. **Flush rewrite rules** (una sola vez):
   - Ve a: `Ajustes > Enlaces permanentes`
   - Haz click en "Guardar cambios" (no cambies nada)
   - Esto activa las URLs `/en/*`

### 📝 Cómo traducir páginas/posts

1. **Crea el post en español** (ID ejemplo: 123)
2. **Crea el post en inglés** (ID ejemplo: 456)
3. **Vincula ambos posts:**
   - Edita el post español (ID 123)
   - En el sidebar derecho verás el metabox "🌐 Traducciones"
   - Selecciona idioma: "Español"
   - En "Versión en English (ID)": escribe `456`
   - Guarda
   - Edita el post inglés (ID 456)
   - Selecciona idioma: "English"
   - En "Versión en Español (ID)": escribe `123`
   - Guarda

### 🔗 URLs automáticas

- **Español:** `https://sanasana.com/planes/` (normal)
- **English:** `https://sanasana.com/en/plans/` (con prefijo /en/)

El sistema detecta automáticamente el idioma por la URL.

### 🌐 Language Switcher

**Shortcode en cualquier lugar:**
```
[lang_switcher]
```

**Con banderas:**
```
[lang_switcher show_flags="1"]
```

**Solo banderas:**
```
[lang_switcher show_flags="1" show_names="0"]
```

**En código PHP:**
```php
<?php echo p5m_language_switcher(); ?>
```

### 📖 Traducir strings del tema

**En templates PHP:**
```php
<?php echo __t('ver_planes'); ?>
<!-- Muestra "Ver planes" en ES, "View plans" en EN -->
```

**Agregar nuevos strings:**

Edita: `/wp-content/themes/p5marketing/languages/translations.json`

```json
{
  "tu_clave": {
    "es": "Texto en español",
    "en": "Text in English"
  }
}
```

### 🎯 Funciones disponibles

#### `p5m_get_current_language()`
Retorna el idioma actual: `'es'` o `'en'`

```php
$lang = p5m_get_current_language();
if ($lang === 'en') {
  echo 'Hello!';
} else {
  echo '¡Hola!';
}
```

#### `p5m_get_translated_post_id($post_id, $target_lang)`
Obtiene el ID del post traducido

```php
$post_id = 123; // Post en español
$en_post_id = p5m_get_translated_post_id($post_id, 'en');
// Retorna 456 (ID en inglés)
```

#### `p5m_get_translated_url($post_id, $target_lang)`
Obtiene la URL del post traducido

```php
$en_url = p5m_get_translated_url(get_the_ID(), 'en');
// Retorna: https://sanasana.com/en/plans/
```

#### `__t($key, $lang = null)`
Traduce un string del tema

```php
echo __t('contacto'); // Auto-detecta idioma
echo __t('contacto', 'en'); // Forzar inglés
```

### 📋 Menús por idioma

**Crear dos menús separados:**

1. Ve a `Apariencia > Menús`
2. Crea: "Main Menu ES" (asignar a Primary Menu)
3. Crea: "Main Menu EN" (asignar a Primary Menu)

**Mostrar menú según idioma en header:**

```php
<?php
$lang = p5m_get_current_language();
$menu_location = $lang === 'en' ? 'primary-en' : 'primary';
wp_nav_menu([
  'theme_location' => $menu_location,
  'menu_class' => 'main-menu',
]);
?>
```

### 🔍 Detección de idioma

**Prioridad:**
1. URL (`/en/*` = inglés)
2. Cookie (`p5m_lang`)
3. Default (español)

La cookie se guarda automáticamente al hacer click en el language switcher.

### ⚡ Performance

- **0 tablas adicionales** (solo post meta)
- **Cache automático** (WP Object Cache)
- **1-2 queries máximo** por request
- **~5ms overhead** vs 50-100ms de WPML
- **Compatible con Redis/Memcached**

### 🎨 Estilos del Language Switcher

Agrega en tu CSS:

```css
.p5m-lang-switcher {
  display: flex;
  gap: 10px;
}

.p5m-lang-switcher .lang-item {
  padding: 5px 10px;
  text-decoration: none;
  border-radius: 4px;
  transition: all 0.3s;
}

.p5m-lang-switcher .lang-item:hover {
  background: #f0f0f0;
}

.p5m-lang-switcher .lang-item.active {
  background: #0073aa;
  color: white;
  pointer-events: none;
}
```

### 🐛 Troubleshooting

**Las URLs /en/ dan 404:**
- Ve a `Ajustes > Enlaces permanentes`
- Click en "Guardar cambios"

**El switcher no cambia el idioma:**
- Verifica que los posts estén vinculados correctamente (IDs en el metabox)
- Revisa que el idioma del post esté seleccionado

**Los strings no se traducen:**
- Verifica que `translations.json` exista y tenga formato JSON válido
- Usa la clave exacta: `__t('ver_planes')` no `__t('Ver planes')`

### 📊 Migración desde WPML

1. Exporta lista de posts con sus traducciones desde WPML
2. Usa el metabox "🌐 Traducciones" para vincular cada par
3. Marca el idioma de cada post
4. Desactiva WPML cuando todo esté vinculado

### 🚀 Next Steps

- [ ] Vincular posts/páginas existentes
- [ ] Crear menús en ambos idiomas
- [ ] Traducir strings del tema en translations.json
- [ ] Agregar language switcher al header
- [ ] Testear navegación entre idiomas
- [ ] Desactivar WPML cuando todo funcione

---

**Creado con ❤️ para performance**
