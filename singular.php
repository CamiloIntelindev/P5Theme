<?php
/**
 * The template for displaying all single posts, CPTs, and sometimes pages 
 * when page.php or single.php are not present.
 *
 * @package P5Marketing
 */
get_header(); ?>

<!-- Contenedor <main> principal, con padding y centrado de contenido -->
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
        
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            
            <!-- Contenedor de contenido principal con flexbox para sidebars -->
            <div class="<?php echo esc_attr($inner_class); ?> <?php echo $layout === 'fullwidth' ? 'px-4 sm:px-6 lg:px-8' : ''; ?>">
                
                <!-- Contenido Principal -->
                <article <?php post_class($layout === 'fullwidth' ? 'w-full' : ($has_sidebar ? 'flex-1 prose lg:prose-lg' : 'prose lg:prose-lg mx-auto')); ?>>
                    
                    <!-- 1. Meta y Título -->
                    <?php if (!p5m_hide_title()): ?>
                    <header class="mb-8">
                        <h1 class="text-4xl lg:text-5xl font-extrabold mb-4 leading-tight">
                            <?php the_title(); ?>
                        </h1>
                        
                        <!-- Metadatos de Publicación (Solo para posts y CPTs) -->
                        <?php if (get_post_type() === 'post'): ?>
                            <div class="text-sm text-gray-600 space-x-2">
                                <span>By <?php the_author(); ?></span>
                                <span>&middot;</span>
                                <time datetime="<?php echo get_the_date('c'); ?>">
                                    <?php echo get_the_date('F j, Y'); ?>
                                </time>
                                <?php if (has_category()): ?>
                                    <span>&middot;</span>
                                    <?php the_category(', '); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </header>
                    <?php endif; ?>

                    <!-- 2. Imagen Destacada (Si existe) -->
                    <?php
                        if ( has_post_thumbnail() && p5m_show_featured() ) {
                            echo '<div class="mb-8 rounded-xl overflow-hidden shadow-xl border border-gray-100">';
                            // Usar <picture> con WebP si existe, lazy por defecto
                            p5m_render_featured_picture('large', [
                                'class' => 'w-full h-auto object-cover',
                                'sizes' => '100vw'
                            ]);
                            echo '</div>';
                        }
                    ?>
                    
                    <!-- 3. Contenido Principal de Gutenberg -->
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>

                    <!-- 4. Paginación interna y comentarios -->
                    <?php
                    // Paginación si el post usa la etiqueta <!--nextpage-->
                    wp_link_pages([
                        'before' => '<nav class="mt-10 pagination text-center border-t pt-6">Pages: ',
                        'after'  => '</nav>',
                        'next_or_number' => 'number',
                        'pagelink' => '<span>%</span>',
                    ]);
                    
                    // Mostrar comentarios si están habilitados
                    if (comments_open() || get_comments_number()) {
                        comments_template();
                    }
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