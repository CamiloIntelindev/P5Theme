<?php
/**
 * The template for displaying Archive pages (Category, Tag, Author, Date, Custom Post Types).
 *
 * @package P5Marketing
 */
get_header(); ?>

<main class="pt-0">
    <!-- Contenedor de ancho máximo y padding responsivo -->
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">

        <!-- 1. Título de la Página de Archivo -->
        <header class="mb-10 text-center">
            <h1 class="text-3xl lg:text-4xl font-extrabold text-gray-900 mb-2">
                <?php the_archive_title(); // Muestra el título de la categoría/etiqueta/archivo ?>
            </h1>
            <?php 
            // Muestra una descripción del archivo, si existe
            $description = get_the_archive_description();
            if ($description) : ?>
                <div class="text-gray-600 max-w-3xl mx-auto">
                    <?php echo wp_kses_post($description); ?>
                </div>
            <?php endif; ?>
        </header>

        <!-- 2. Bucle de Posts y Layout de Grid -->
        <?php if (have_posts()) : ?>
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                <?php while (have_posts()) : the_post(); ?>
                    
                    <!-- Tarjeta/Resumen de Post Individual -->
                    <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white border rounded-xl shadow-md hover:shadow-lg transition overflow-hidden flex flex-col'); ?>>
                        
                        <!-- Imagen Destacada -->
                        <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php the_permalink(); ?>" class="block h-48 overflow-hidden bg-gray-100">
                                <?php the_post_thumbnail('medium', ['class' => 'w-full h-full object-cover transition-transform duration-300 hover:scale-105']); ?>
                            </a>
                        <?php endif; ?>

                        <div class="p-6 flex flex-col flex-grow">
                            
                            <!-- Metadatos (Fecha) -->
                            <div class="text-xs text-indigo-600 font-semibold uppercase mb-1">
                                <time datetime="<?php echo get_the_date('c'); ?>">
                                    <?php echo get_the_date('M j, Y'); ?>
                                </time>
                            </div>
                            
                            <!-- Título del Post -->
                            <h2 class="text-xl font-bold leading-tight mb-2 flex-grow">
                                <a href="<?php the_permalink(); ?>" class="hover:text-indigo-600 transition">
                                    <?php the_title(); ?>
                                </a>
                            </h2>
                            
                            <!-- Extracto -->
                            <div class="text-gray-600 text-sm mb-4">
                                <?php the_excerpt(); ?>
                            </div>
                            
                            <!-- Leer Más -->
                            <a href="<?php the_permalink(); ?>" class="text-indigo-600 font-medium text-sm hover:underline mt-auto">
                                Leer más &rarr;
                            </a>
                        </div>
                    </article>

                <?php endwhile; ?>

            </div>

            <!-- 3. Paginación de Archivo -->
            <div class="mt-12">
                <?php 
                the_posts_pagination([
                    'prev_text' => '<span class="px-2 py-1 border rounded bg-white hover:bg-gray-100">&larr; Previous</span>',
                    'next_text' => '<span class="px-2 py-1 border rounded bg-white hover:bg-gray-100">Next &rarr;</span>',
                    'screen_reader_text' => 'Post navigation',
                    'before_page_number' => '<span class="px-2 py-1">',
                    'after_page_number' => '</span>',
                ]); 
                ?>
            </div>

        <?php else : ?>
            <!-- 4. Contenido si no hay posts -->
            <div class="text-center py-20">
                <p class="text-lg text-gray-700">No se encontraron posts en este archivo.</p>
                <?php get_search_form(); ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>