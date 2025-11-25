<?php
/**
 * Posts Carousel Block
 * Carrusel de posts con imagen destacada como background y título overlay
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Posts Carousel Block
 */
function p5m_register_posts_carousel_block() {
  if (!function_exists('register_block_type')) {
    return;
  }

  register_block_type('p5m/posts-carousel', [
    'attributes' => [
      'postsPerPage' => [ 'type' => 'number', 'default' => 6 ],
      'postTypes' => [ 'type' => 'array', 'default' => ['post'] ],
      'slidesDesktop' => [ 'type' => 'number', 'default' => 3 ],
      'slidesTablet' => [ 'type' => 'number', 'default' => 2 ],
      'slidesMobile' => [ 'type' => 'number', 'default' => 1 ],
      'gapSize' => [ 'type' => 'string', 'default' => 'medium' ],
      'minSize' => [ 'type' => 'string', 'default' => '300px' ],
      'autoplay' => [ 'type' => 'boolean', 'default' => true ],
      'autoplayDelay' => [ 'type' => 'number', 'default' => 3000 ],
      'transitionSpeed' => [ 'type' => 'number', 'default' => 400 ],
      'infinite' => [ 'type' => 'boolean', 'default' => true ],
      'marquee' => [ 'type' => 'boolean', 'default' => false ],
      'marqueeSpeed' => [ 'type' => 'number', 'default' => 60 ],
      'marqueePauseOnHover' => [ 'type' => 'boolean', 'default' => true ],
      'showArrows' => [ 'type' => 'boolean', 'default' => true ],
      'showDots' => [ 'type' => 'boolean', 'default' => false ],
      'titleColor' => [ 'type' => 'string', 'default' => '#ffffff' ],
      'titleBg' => [ 'type' => 'string', 'default' => 'rgba(0,0,0,0.45)' ],
      'overlayPadding' => [ 'type' => 'string', 'default' => '0.75rem 1rem' ],
      'imageSize' => [ 'type' => 'string', 'default' => 'large' ],
      'orderBy' => [ 'type' => 'string', 'default' => 'date' ],
      'order' => [ 'type' => 'string', 'default' => 'DESC' ],
    ],
    'render_callback' => 'p5m_render_posts_carousel_block',
  ]);
}

/**
 * Render Posts Carousel Block
 */
function p5m_render_posts_carousel_block($attributes) {
  $posts_per_page = isset($attributes['postsPerPage']) ? intval($attributes['postsPerPage']) : 6;
  $post_types = isset($attributes['postTypes']) ? (array)$attributes['postTypes'] : ['post'];
  $slides_desktop = isset($attributes['slidesDesktop']) ? max(1, intval($attributes['slidesDesktop'])) : 3;
  $slides_tablet = isset($attributes['slidesTablet']) ? max(1, intval($attributes['slidesTablet'])) : 2;
  $slides_mobile = isset($attributes['slidesMobile']) ? max(1, intval($attributes['slidesMobile'])) : 1;
  $gap_size = isset($attributes['gapSize']) ? $attributes['gapSize'] : 'medium';
  $min_size = isset($attributes['minSize']) ? $attributes['minSize'] : '300px';
  $autoplay = !empty($attributes['autoplay']);
  $autoplay_delay = isset($attributes['autoplayDelay']) ? intval($attributes['autoplayDelay']) : 3000;
  $transition_speed = isset($attributes['transitionSpeed']) ? intval($attributes['transitionSpeed']) : 400;
  $infinite = !empty($attributes['infinite']);
  $marquee = !empty($attributes['marquee']);
  $marquee_speed = isset($attributes['marqueeSpeed']) ? intval($attributes['marqueeSpeed']) : 60; // px/s
  $marquee_pause = !empty($attributes['marqueePauseOnHover']);
  $show_arrows = !empty($attributes['showArrows']);
  $show_dots = !empty($attributes['showDots']);
  $title_color = isset($attributes['titleColor']) ? $attributes['titleColor'] : '#fff';
  $title_bg = isset($attributes['titleBg']) ? $attributes['titleBg'] : 'rgba(0,0,0,0.45)';
  $overlay_padding = isset($attributes['overlayPadding']) ? $attributes['overlayPadding'] : '0.75rem 1rem';
  $image_size = isset($attributes['imageSize']) ? $attributes['imageSize'] : 'large';
  $order_by = isset($attributes['orderBy']) ? $attributes['orderBy'] : 'date';
  $order = isset($attributes['order']) ? $attributes['order'] : 'DESC';

  $gap_classes = [
    'none' => 'gap-0',
    'small' => 'gap-3',
    'medium' => 'gap-4',
    'large' => 'gap-6',
  ];
  $gap_class = isset($gap_classes[$gap_size]) ? $gap_classes[$gap_size] : 'gap-4';

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

  // Unique id
  $block_id = 'p5m-pc-' . wp_generate_uuid4();

  // Data settings
  $settings = [
    'slidesDesktop' => $slides_desktop,
    'slidesTablet' => $slides_tablet,
    'slidesMobile' => $slides_mobile,
    'autoplay' => $autoplay,
    'autoplayDelay' => $autoplay_delay,
    'transitionSpeed' => $transition_speed,
    'infinite' => $infinite,
    'marquee' => $marquee,
    'marqueeSpeed' => $marquee_speed,
    'marqueePauseOnHover' => $marquee_pause,
    'showArrows' => $show_arrows,
    'showDots' => $show_dots,
    'minSize' => $min_size,
  ];

  // Enqueue frontend script
  if (function_exists('wp_enqueue_script')) {
    wp_enqueue_script('p5m-posts-carousel');
  }

  ob_start();
  ?>
  <div id="<?php echo esc_attr($block_id); ?>" class="p5m-posts-carousel relative overflow-hidden" data-settings='<?php echo json_encode($settings, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>'>
    <div class="p5m-carousel-viewport">
      <div class="p5m-carousel-track flex <?php echo esc_attr($gap_class); ?>" style="will-change: transform;">
        <?php while ($query->have_posts()) : $query->the_post(); 
          $img = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), $image_size) : '';
          $bg_style = $img ? 'background-image:url(' . esc_url($img) . ');' : '';
          $card_styles = 'min-width:' . esc_attr($min_size) . ';min-height:' . esc_attr($min_size) . ';background-size:cover;background-position:center;';
          ?>
          <a class="p5m-carousel-card block relative rounded-lg shadow-md overflow-hidden" href="<?php the_permalink(); ?>" style="<?php echo esc_attr($card_styles . $bg_style); ?>">
            <span class="p5m-carousel-title absolute left-0 right-0 bottom-0" style="color:<?php echo esc_attr($title_color); ?>;background:<?php echo esc_attr($title_bg); ?>;padding:<?php echo esc_attr($overlay_padding); ?>;">
              <?php the_title(); ?>
            </span>
          </a>
        <?php endwhile; ?>
      </div>
    </div>

    <?php if ($show_arrows && !$marquee) : ?>
      <button class="p5m-carousel-prev absolute inset-y-0 left-2 my-auto w-9 h-9 rounded-full bg-white/80 hover:bg-white shadow flex items-center justify-center" aria-label="Prev">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      </button>
      <button class="p5m-carousel-next absolute inset-y-0 right-2 my-auto w-9 h-9 rounded-full bg-white/80 hover:bg-white shadow flex items-center justify-center" aria-label="Next">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      </button>
    <?php endif; ?>

    <?php if ($show_dots && !$marquee) : ?>
      <div class="p5m-carousel-dots absolute bottom-2 left-0 right-0 flex justify-center gap-2"></div>
    <?php endif; ?>
  </div>
  <?php
  wp_reset_postdata();
  return ob_get_clean();
}

?>
