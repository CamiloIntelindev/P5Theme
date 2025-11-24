<?php
/**
 * The template for displaying all pages.
 *
 * @package P5Marketing
 */
get_header(); ?>

<!-- 
    Usamos la etiqueta <main> para accesibilidad.
    Le damos padding vertical (pt-12, pb-24) para espaciar el contenido del header y footer. 
    Soporta múltiples layouts: normal, full-width, sidebar-left, sidebar-right.
-->
<main class="pt-0">
    <?php
    // Mostrar breadcrumbs
    $enable_breadcrumbs = p5m_get_setting('enable_breadcrumbs', true);
    if ($enable_breadcrumbs && function_exists('p5m_the_breadcrumbs')) {
        echo '<div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8 mb-8">';
        p5m_the_breadcrumbs();
        echo '</div>';
    }
    
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
          <div class="<?php echo esc_attr($inner_class); ?> <?php echo $layout === 'fullwidth' ?  : ''; ?>">
            
            <!-- Contenido Principal -->
            <article <?php post_class($layout === 'fullwidth' ? 'w-full' : ($has_sidebar ? 'flex-1 container' : 'container')); ?>>

              <?php // Título de página ?>
              <?php if ( !is_home() && !is_front_page() && !p5m_hide_title()) { ?>
                  <h1 class="mb-6"><?php the_title(); ?></h1>
              <?php } ?>
              
              <?php
              // Imagen destacada
              if ( has_post_thumbnail() ) {
                echo '<div class="mb-8 rounded-xl overflow-hidden shadow-lg border border-gray-100">';
                p5m_render_featured_picture('large', [
                  'class' => 'w-full h-auto object-cover',
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

<?php 
/**
 * Incluimos el footer, el cual cerrará el </body> y </html>
 */
get_footer(); ?>