<?php
/**
 * Template for the front page (homepage)
 * 
 * This template is optimized for maximum performance:
 * - No page builder assets loaded
 * - Clean, minimal markup
 * - Full support for layout options (normal, fullwidth, sidebar)
 * - All theme optimizations applied (cache, deferred scripts, etc.)
 * 
 * @package P5Marketing
 */

// Nota: no bloqueamos Beaver Builder aquí para evitar correr demasiado pronto.
// El bloqueo/ajuste de Beaver se hace en functions.php dentro de 'init'.

get_header(); 
?>

<main class="pt-0">
    <?php
    $layout = p5m_get_layout();
    $has_sidebar = in_array($layout, ['sidebar-left', 'sidebar-right']);
    
    // Contenedor externo: max-width según layout
    $outer_class = $layout === 'fullwidth'
        ? ''
        : 'mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8';
    
    // Contenedor interno: flex si hay sidebar
    $inner_class = $has_sidebar
        ? 'flex gap-8 ' . ($layout === 'sidebar-left' ? 'flex-row-reverse' : 'flex-row')
        : '';
    ?>
    
    <div class="<?php echo esc_attr($outer_class); ?>">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
          
          <!-- Contenedor de contenido con flexbox para sidebars -->
          <div class="<?php echo esc_attr($inner_class); ?> <?php echo $layout === 'fullwidth' ? '' : ''; ?>">
            
            <!-- Contenido Principal -->
            <article <?php post_class($layout === 'fullwidth' ? 'w-full' : ($has_sidebar ? 'flex-1' : '')); ?>>

              <?php
              // Hero section con imagen destacada (opcional)
              if ( has_post_thumbnail() && p5m_show_featured() ) {
                echo '<div class="mb-12 rounded-xl overflow-hidden shadow-2xl">';
                // Picture con WebP si existe, y atributos de prioridad
                p5m_render_featured_picture('full', [
                  'class' => 'w-full h-auto object-cover',
                  'loading' => 'eager', // Primera imagen = alta prioridad
                  'fetchpriority' => 'high',
                  'sizes' => '100vw'
                ]);
                echo '</div>';
              }
              ?>

              <div class="entry-content">
                <?php the_content(); ?>
              </div>

              <?php
                // Soporte a paginación interna de contenido (<!--nextpage-->)
                wp_link_pages([
                  'before' => '<nav class="mt-8 pagination text-center border-t pt-4">',
                  'after'  => '</nav>',
                  'next_or_number' => 'number',
                  'pagelink' => '<span>%</span>',
                ]);
              ?>
            </article>
            
            <!-- Sidebar (si aplica) -->
            <?php if ($has_sidebar): ?>
                <aside class="w-80 prose lg:prose-lg">
                    <?php
                    if (is_active_sidebar('p5m_post_sidebar')) {
                        dynamic_sidebar('p5m_post_sidebar');
                    } else {
                        echo '<p class="text-gray-500 italic">' . __('Sidebar area is empty. Add widgets in WordPress admin.', 'p5marketing') . '</p>';
                    }
                    ?>
                </aside>
            <?php endif; ?>
          </div>

        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>
