<?php
/**
 * Content Filters
 * Navegación, menús, AJAX load more posts
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

// Menús: clases y compatibilidad con Navigation block + Tailwind
add_filter('nav_menu_link_attributes', function ($atts, $item, $args) {
  if (!isset($args->theme_location)) return $atts;

  if ($args->theme_location === 'primary') {
    $base = 'wp-block-navigation-item__content inline-flex items-center gap-1 py-2 text-gray-700 hover:text-gray-900 transition';
    if (isset($item->menu_item_parent) && $item->menu_item_parent > 0) {
      $base .= ' pl-4';
    }
    $atts['class'] = isset($atts['class']) ? "{$atts['class']} {$base}" : $base;
  } elseif ($args->theme_location === 'social') {
    $base = 'inline-flex items-center text-gray-500 hover:text-gray-800 transition';
    $atts['class'] = isset($atts['class']) ? "{$atts['class']} {$base}" : $base;
  }

  if (in_array('menu-item-has-children', $item->classes ?? [], true)) {
    $atts['aria-haspopup'] = 'true';
    $atts['aria-expanded'] = 'false';
  }
  return $atts;
}, 10, 3);

add_filter('nav_menu_css_class', function ($classes, $item, $args) {
  if (!isset($args->theme_location)) return $classes;

  if ($args->theme_location === 'primary') {
    $classes[] = 'list-none wp-block-navigation-item';
    if (in_array('menu-item-has-children', $classes, true)) {
      $classes[] = 'has-child relative group';
    }
  } elseif ($args->theme_location === 'social') {
    $classes[] = 'list-none';
  }
  return $classes;
}, 10, 3);

add_filter('nav_menu_submenu_css_class', function ($classes, $args) {
  if (!isset($args->theme_location) || $args->theme_location !== 'primary') return $classes;
  $classes[] = 'wp-block-navigation__submenu-container';
  $classes[] = 'hidden group-hover:block absolute left-0 top-full mt-0 min-w-[12rem] rounded-lg border bg-white shadow-lg z-50 transition-opacity duration-150 opacity-100';
  return $classes;
}, 10, 2);

// ============================================================================
// Language Switcher in Primary Menu
// ============================================================================

/**
 * Agregar language switcher automáticamente al final del menú primario
 */
add_filter('wp_nav_menu_items', function($items, $args) {
  // Solo agregar al menú primario y en frontend
  if (isset($args->theme_location) && $args->theme_location === 'primary' && !is_admin()) {
    
    $current_lang = p5m_get_current_language();
    $post_id = get_queried_object_id();
    
    $languages = [
      'es' => ['name' => 'Español', 'short' => 'ES', 'flag' => ''],
      'en' => ['name' => 'Inglés',  'short' => 'EN', 'flag' => '']
    ];
    
    // Determinar texto del parent (idioma actual)
  $current_lang_data = $languages[$current_lang];
  // Mostrar el nombre completo del idioma (sin bandera)
  $parent_text = $current_lang_data['name'];
    
    // Clases del LI principal (mismo estilo que "Programas")
    $li_classes = 'menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children p5m-lang-switcher list-none wp-block-navigation-item has-child relative group';
    
    // Iniciar el menu item principal
    $switcher_html = sprintf(
      '<li id="menu-item-lang-switcher" class="%s">',
      esc_attr($li_classes)
    );
    
    // Link principal (no clickeable, solo muestra idioma actual)
    $switcher_html .= sprintf(
      '<a href="#" class="wp-block-navigation-item__content inline-flex items-center gap-1 py-2 text-gray-700 hover:text-gray-900 transition" aria-haspopup="true" aria-expanded="false">%s</a>',
      $parent_text
    );
    
    // Submenu con ambos idiomas
    $switcher_html .= '<ul class="sub-menu wp-block-navigation__submenu-container hidden group-hover:block absolute left-0 top-full mt-0 min-w-[12rem] rounded-lg border bg-white shadow-lg z-50 transition-opacity duration-150 opacity-100">';
    
    foreach ($languages as $code => $data) {
      $is_active = ($code === $current_lang);
      
      // Determinar URL
      if ($post_id) {
        $translated_url = p5m_get_translated_url($post_id, $code);
        $url = $translated_url ?: home_url($code === 'en' ? '/en/' : '/');
      } else {
        $url = home_url($code === 'en' ? '/en/' : '/');
      }
      
      // Clases del submenu item
      $sub_li_classes = 'menu-item menu-item-type-custom menu-item-object-custom list-none wp-block-navigation-item';
      if ($is_active) {
        $sub_li_classes .= ' current-menu-item';
      }
      
      $sub_a_classes = 'wp-block-navigation-item__content inline-flex items-center gap-1 py-2 text-gray-700 hover:text-gray-900 transition pl-4';
      
      // HTML del submenu item (solo texto del idioma)
      $switcher_html .= sprintf(
        '<li class="%s"><a href="%s" class="%s" %s>%s</a></li>',
        esc_attr($sub_li_classes),
        esc_url($url),
        esc_attr($sub_a_classes),
        !$is_active ? 'onclick="document.cookie=\'p5m_lang=' . $code . ';path=/;max-age=31536000\'"' : '',
        esc_html($data['name'])
      );
    }
    
    $switcher_html .= '</ul>';
    $switcher_html .= '</li>';
    
    // Agregar al final del menú
    $items .= $switcher_html;
  }
  
  return $items;
}, 10, 2);

// AJAX: Load More Posts
add_action('wp_ajax_p5m_load_more_posts', 'p5m_load_more_posts_callback');
add_action('wp_ajax_nopriv_p5m_load_more_posts', 'p5m_load_more_posts_callback');

function p5m_load_more_posts_callback() {
  check_ajax_referer('p5m_load_more_nonce', 'nonce');

  $paged = intval($_POST['paged'] ?? 1);
  $query_args = isset($_POST['query_args']) ? (array)$_POST['query_args'] : [];

  $defaults = array(
    'posts_per_page' => 6,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post_status'    => 'publish',
  );

  $args = wp_parse_args($query_args, $defaults);
  $args['paged'] = $paged;

  $query = new WP_Query($args);

  if ($query->have_posts()) {
    ob_start();
    while ($query->have_posts()) {
      $query->the_post();
      ?>
      <article <?php post_class('bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden flex flex-col'); ?>>
        <?php
        if (has_post_thumbnail()) {
          ?>
          <div class="h-48 overflow-hidden bg-gray-200">
            <?php
            the_post_thumbnail('medium', array(
              'class' => 'w-full h-full object-cover hover:scale-105 transition-transform duration-300',
              'alt'   => get_the_title(),
            ));
            ?>
          </div>
          <?php
        }
        ?>
        <div class="p-6 flex flex-col flex-1">
          <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
            <a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition-colors">
              <?php the_title(); ?>
            </a>
          </h3>
          <time class="text-sm text-gray-500 mb-3">
            <?php echo esc_html(get_the_date('d F Y')); ?>
          </time>
          <p class="text-gray-600 text-sm line-clamp-3 mb-4">
            <?php echo esc_html(wp_trim_words(get_the_excerpt(), 20)); ?>
          </p>
          <a href="<?php the_permalink(); ?>" class="inline-block text-blue-600 hover:text-blue-700 font-medium text-sm mt-auto">
            <?php esc_html_e('Read more →', 'p5marketing'); ?>
          </a>
        </div>
      </article>
      <?php
    }
    $html = ob_get_clean();
    wp_reset_postdata();

    $has_more = ($paged < $query->max_num_pages);

    wp_send_json_success(array(
      'html'     => $html,
      'has_more' => $has_more,
    ));
  } else {
    wp_send_json_error(array(
      'message' => __('No more posts found.', 'p5marketing'),
    ));
  }
}
