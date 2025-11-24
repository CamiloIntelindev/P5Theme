<?php
/**
 * Posts Grid Block
 * Grilla personalizable de posts con múltiples opciones de visualización
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Posts Grid Block
 */
function p5m_register_posts_grid_block() {
  if (!function_exists('register_block_type')) {
    return;
  }
  
  register_block_type('p5m/posts-grid', [
    'attributes' => [
      'columns' => [
        'type' => 'number',
        'default' => 3,
      ],
      'rows' => [
        'type' => 'number',
        'default' => 2,
      ],
      'postsPerPage' => [
        'type' => 'number',
        'default' => 6,
      ],
      'postTypes' => [
        'type' => 'array',
        'default' => ['post'],
      ],
      'showFeaturedImage' => [
        'type' => 'boolean',
        'default' => true,
      ],
      'showTitle' => [
        'type' => 'boolean',
        'default' => true,
      ],
      'showExcerpt' => [
        'type' => 'boolean',
        'default' => true,
      ],
      'showAuthor' => [
        'type' => 'boolean',
        'default' => true,
      ],
      'showDate' => [
        'type' => 'boolean',
        'default' => true,
      ],
      'showCategories' => [
        'type' => 'boolean',
        'default' => false,
      ],
      'showReadMore' => [
        'type' => 'boolean',
        'default' => true,
      ],
      'excerptLength' => [
        'type' => 'number',
        'default' => 20,
      ],
      'orderBy' => [
        'type' => 'string',
        'default' => 'date',
      ],
      'order' => [
        'type' => 'string',
        'default' => 'DESC',
      ],
      'imageSize' => [
        'type' => 'string',
        'default' => 'medium',
      ],
      'gapSize' => [
        'type' => 'string',
        'default' => 'medium',
      ],
    ],
    'render_callback' => 'p5m_render_posts_grid_block',
  ]);
}

/**
 * Render Posts Grid Block
 */
function p5m_render_posts_grid_block($attributes) {
  $columns = isset($attributes['columns']) ? intval($attributes['columns']) : 3;
  $rows = isset($attributes['rows']) ? intval($attributes['rows']) : 2;
  $posts_per_page = isset($attributes['postsPerPage']) ? intval($attributes['postsPerPage']) : 6;
  $post_types = isset($attributes['postTypes']) ? $attributes['postTypes'] : ['post'];
  $show_featured_image = isset($attributes['showFeaturedImage']) ? $attributes['showFeaturedImage'] : true;
  $show_title = isset($attributes['showTitle']) ? $attributes['showTitle'] : true;
  $show_excerpt = isset($attributes['showExcerpt']) ? $attributes['showExcerpt'] : true;
  $show_author = isset($attributes['showAuthor']) ? $attributes['showAuthor'] : true;
  $show_date = isset($attributes['showDate']) ? $attributes['showDate'] : true;
  $show_categories = isset($attributes['showCategories']) ? $attributes['showCategories'] : false;
  $show_read_more = isset($attributes['showReadMore']) ? $attributes['showReadMore'] : true;
  $excerpt_length = isset($attributes['excerptLength']) ? intval($attributes['excerptLength']) : 20;
  $order_by = isset($attributes['orderBy']) ? $attributes['orderBy'] : 'date';
  $order = isset($attributes['order']) ? $attributes['order'] : 'DESC';
  $image_size = isset($attributes['imageSize']) ? $attributes['imageSize'] : 'medium';
  $gap_size = isset($attributes['gapSize']) ? $attributes['gapSize'] : 'medium';
  
  // Gap sizes mapping
  $gap_classes = [
    'none' => 'gap-0',
    'small' => 'gap-4',
    'medium' => 'gap-6',
    'large' => 'gap-8',
  ];
  $gap_class = isset($gap_classes[$gap_size]) ? $gap_classes[$gap_size] : 'gap-6';
  
  // Columns classes (responsive)
  $col_classes = [
    1 => 'grid-cols-1',
    2 => 'grid-cols-1 md:grid-cols-2',
    3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
    5 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-5',
    6 => 'grid-cols-1 md:grid-cols-3 lg:grid-cols-6',
  ];
  $col_class = isset($col_classes[$columns]) ? $col_classes[$columns] : 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3';
  
  // Query args
  $args = [
    'post_type' => $post_types,
    'posts_per_page' => $posts_per_page,
    'orderby' => $order_by,
    'order' => $order,
    'post_status' => 'publish',
    'ignore_sticky_posts' => true,
  ];
  
  $query = new WP_Query($args);
  
  if (!$query->have_posts()) {
    return '<p class="text-gray-500">' . esc_html__('No se encontraron posts.', 'p5marketing') . '</p>';
  }
  
  ob_start();
  ?>
  
  <div class="p5m-posts-grid grid <?php echo esc_attr($col_class . ' ' . $gap_class); ?>">
    <?php while ($query->have_posts()) : $query->the_post(); ?>
      <article class="p5m-post-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
        
        <?php if ($show_featured_image && has_post_thumbnail()) : ?>
          <div class="p5m-post-image">
            <a href="<?php the_permalink(); ?>" class="block">
              <?php the_post_thumbnail($image_size, ['class' => 'w-full h-48 object-cover']); ?>
            </a>
          </div>
        <?php endif; ?>
        
        <div class="p5m-post-content p-6">
          
          <?php if ($show_categories && has_category()) : ?>
            <div class="p5m-post-categories mb-2">
              <?php 
              $categories = get_the_category();
              if ($categories) {
                echo '<div class="flex flex-wrap gap-2">';
                foreach ($categories as $category) {
                  echo '<span class="inline-block bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded">' . esc_html($category->name) . '</span>';
                }
                echo '</div>';
              }
              ?>
            </div>
          <?php endif; ?>
          
          <?php if ($show_title) : ?>
            <h3 class="p5m-post-title text-xl font-bold mb-2">
              <a href="<?php the_permalink(); ?>" class="text-gray-900 hover:text-indigo-600 transition-colors">
                <?php the_title(); ?>
              </a>
            </h3>
          <?php endif; ?>
          
          <?php if ($show_date || $show_author) : ?>
            <div class="p5m-post-meta text-sm text-gray-600 mb-3 flex items-center gap-3">
              <?php if ($show_author) : ?>
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                  </svg>
                  <?php the_author(); ?>
                </span>
              <?php endif; ?>
              
              <?php if ($show_date) : ?>
                <span class="flex items-center gap-1">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                  </svg>
                  <?php echo get_the_date(); ?>
                </span>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          
          <?php if ($show_excerpt) : ?>
            <div class="p5m-post-excerpt text-gray-700 mb-4">
              <?php 
              $excerpt = get_the_excerpt();
              if ($excerpt_length > 0) {
                $excerpt = wp_trim_words($excerpt, $excerpt_length, '...');
              }
              echo '<p>' . esc_html($excerpt) . '</p>';
              ?>
            </div>
          <?php endif; ?>
          
          <?php if ($show_read_more) : ?>
            <div class="p5m-post-read-more">
              <a href="<?php the_permalink(); ?>" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                <?php esc_html_e('Leer más', 'p5marketing'); ?>
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </a>
            </div>
          <?php endif; ?>
          
        </div>
      </article>
    <?php endwhile; ?>
  </div>
  
  <?php
  wp_reset_postdata();
  
  return ob_get_clean();
}
