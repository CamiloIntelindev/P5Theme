<?php
/**
 * The header for the theme
 *
 * @package P5Marketing
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class('min-h-screen bg-white antialiased'); ?>>
<?php wp_body_open(); ?>

<?php
// Logo del header (prioridad: ajuste de header > ajuste general > fallback)
$header_logo = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('logo') : '';
if (empty($header_logo)) {
	// Fallback al logo general del sitio
	$header_logo = function_exists('p5m_get_site_logo') ? p5m_get_site_logo() : [
		'url' => get_template_directory_uri() . '/assets/img/logo-fallback.svg',
		'alt' => get_bloginfo('name')
	];
	// Si p5m_get_site_logo devuelve array
	if (is_array($header_logo)) {
		$logo_url = $header_logo['url'];
		$logo_alt = $header_logo['alt'];
		$logo_w   = $header_logo['width'] ?? null;
		$logo_h   = $header_logo['height'] ?? null;
	} else {
		$logo_url = $header_logo;
		$logo_alt = get_bloginfo('name');
		$logo_w = $logo_h = null;
	}
} else {
	$logo_url = $header_logo;
	$logo_alt = get_bloginfo('name');
	$logo_w = $logo_h = null;
}

// Menú a usar (por defecto 'primary')
$header_menu = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('menu', 'primary') : 'primary';

// Modo de visualización del menú: full | hamburger
$menu_display = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('menu_display', 'full') : 'full';

// Orden del layout: logo-nav-cta | logo-cta-nav | nav-logo-cta | cta-logo-nav | nav-cta-logo | cta-nav-logo
$layout_order = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('layout_order', 'logo-nav-cta') : 'logo-nav-cta';

// Mapeo del orden a valores de flexbox order
$order_map = [
	'logo-nav-cta' => ['logo' => 1, 'nav' => 2, 'cta' => 3],
	'logo-cta-nav' => ['logo' => 1, 'nav' => 3, 'cta' => 2],
	'nav-logo-cta' => ['logo' => 2, 'nav' => 1, 'cta' => 3],
	'cta-logo-nav' => ['logo' => 2, 'nav' => 3, 'cta' => 1],
	'nav-cta-logo' => ['logo' => 3, 'nav' => 1, 'cta' => 2],
	'cta-nav-logo' => ['logo' => 3, 'nav' => 2, 'cta' => 1],
];

$current_order = $order_map[$layout_order] ?? $order_map['logo-nav-cta'];

// Posición del header: sticky | static | fixed
$header_position = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('position', 'sticky') : 'sticky';
switch ($header_position) {
	case 'fixed':
		$header_position_class = 'fixed top-0 left-0 right-0 z-50';
		break;
	case 'static':
		$header_position_class = 'relative';
		break;
	case 'sticky':
	default:
		$header_position_class = 'sticky top-0 z-50';
}

// Borde inferior
$header_border = function_exists('p5m_get_header_setting') ? intval(p5m_get_header_setting('border_bottom', 1)) : 1;
$header_border_class = $header_border ? 'border-b' : '';

// Fondo: sólido o gradiente
$bg_mode   = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('background_mode', 'solid') : 'solid';
$bg_color  = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('bg_color', '') : '';
$bg_opacity = function_exists('p5m_get_header_setting') ? floatval(p5m_get_header_setting('bg_opacity', 1)) : 1;
$grad_a    = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('gradient_start', '') : '';
$grad_a_op = function_exists('p5m_get_header_setting') ? floatval(p5m_get_header_setting('gradient_start_opacity', 1)) : 1;
$grad_b    = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('gradient_end', '') : '';
$grad_b_op = function_exists('p5m_get_header_setting') ? floatval(p5m_get_header_setting('gradient_end_opacity', 1)) : 1;
$grad_dir  = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('gradient_direction', 'horizontal') : 'horizontal';

$header_style_attr = '';

// Helpers para color
$p5m_hex_to_rgb = function($hex) {
	$hex = trim($hex);
	if ($hex === '') return null;
	$hex = ltrim($hex, '#');
	if (strlen($hex) === 3) {
		$r = hexdec(str_repeat(substr($hex,0,1),2));
		$g = hexdec(str_repeat(substr($hex,1,1),2));
		$b = hexdec(str_repeat(substr($hex,2,1),2));
	} elseif (strlen($hex) === 6) {
		$r = hexdec(substr($hex,0,2));
		$g = hexdec(substr($hex,2,2));
		$b = hexdec(substr($hex,4,2));
	} else {
		return null;
	}
	return [$r,$g,$b];
};

$p5m_color_with_opacity = function($hex, $opacity) use ($p5m_hex_to_rgb) {
	if (!$hex) return '';
	if ($opacity >= 0 && $opacity < 1) {
		$rgb = $p5m_hex_to_rgb($hex);
		if ($rgb) {
			return 'rgba(' . $rgb[0] . ',' . $rgb[1] . ',' . $rgb[2] . ',' . max(0,min(1,$opacity)) . ')';
		}
	}
	return $hex; // por defecto hex
};

if ($bg_mode === 'solid' && $bg_color) {
	$css_color = $p5m_color_with_opacity($bg_color, $bg_opacity);
	$header_style_attr = ' style="background-color:' . esc_attr($css_color) . '"';
} elseif ($bg_mode === 'gradient' && $grad_a && $grad_b) {
	$dir_css = ($grad_dir === 'vertical') ? 'to bottom' : 'to right';
	$ca = $p5m_color_with_opacity($grad_a, $grad_a_op);
	$cb = $p5m_color_with_opacity($grad_b, $grad_b_op);
	$header_style_attr = ' style="background-image:linear-gradient(' . esc_attr($dir_css) . ',' . esc_attr($ca) . ',' . esc_attr($cb) . ')"';
}
$header_bg_class = empty($header_style_attr) ? ' bg-white/95' : '';

// Scroll threshold y colores al hacer scroll
$scroll_threshold   = function_exists('p5m_get_header_setting') ? intval(p5m_get_header_setting('scroll_threshold', 0)) : 0;
$sc_bg   = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('scrolled_bg_color', '') : '';
$sc_bg_op = function_exists('p5m_get_header_setting') ? floatval(p5m_get_header_setting('scrolled_bg_opacity', 1)) : 1;
$sc_link = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('scrolled_link_color', '') : '';
$sc_cta_bg   = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('scrolled_cta_bg', '') : '';
$sc_cta_text = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('scrolled_cta_text', '') : '';

// Derivados para submenu (hover y borde) basados en el color de link scrolleado
$sc_link_hover_bg_css = '';
$sc_link_border_css = '';
if ($sc_link) {
	$sc_link_hover_bg_css = $p5m_color_with_opacity($sc_link, 0.08); // leve overlay para hover
	$sc_link_border_css   = $p5m_color_with_opacity($sc_link, 0.18); // borde sutil
}

// Ancho del logo
$logo_width = function_exists("p5m_get_header_setting") ? p5m_get_header_setting("logo_width", "") : "";
$logo_width_style = $logo_width ? " style=\"width:" . esc_attr($logo_width) . ";height:auto;\"" : "";

// Altura mínima del header
$header_min_height = function_exists("p5m_get_header_setting") ? intval(p5m_get_header_setting("min_height", 0)) : 0;
$header_min_height_style = $header_min_height > 0 ? " style=\"min-height:" . esc_attr($header_min_height) . "px;\"" : "";

// Color de enlaces de navegación (base state)
$nav_link_color = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('link_color', '') : '';

// CTA del header
$cta_enable = function_exists('p5m_get_header_setting') ? intval(p5m_get_header_setting('cta_enable', 1)) : 1;
$cta_text = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('cta_text', 'Book Your Private 30-Minute Webinar') : 'Book Your Private 30-Minute Webinar';
$cta_url = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('cta_url', '/contact') : '/contact';

// CTA Customization
$cta_bg_color = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('cta_bg_color', '') : '';
$cta_text_color = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('cta_text_color', '') : '';
$cta_padding = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('cta_padding', '') : '';
$cta_border = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('cta_border', '') : '';
$cta_border_radius = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('cta_border_radius', '') : '';
$cta_hover_bg_color = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('cta_hover_bg_color', '') : '';
$cta_hover_text_color = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('cta_hover_text_color', '') : '';

// Build CTA inline styles
$cta_inline_styles = [];
if ($cta_bg_color) $cta_inline_styles[] = 'background-color:' . esc_attr($cta_bg_color);
if ($cta_text_color) $cta_inline_styles[] = 'color:' . esc_attr($cta_text_color);
if ($cta_padding) $cta_inline_styles[] = 'padding:' . esc_attr($cta_padding);
if ($cta_border) $cta_inline_styles[] = 'border:' . esc_attr($cta_border);
if ($cta_border_radius) $cta_inline_styles[] = 'border-radius:' . esc_attr($cta_border_radius);
$cta_style_attr = !empty($cta_inline_styles) ? ' style="' . implode(';', $cta_inline_styles) . '"' : '';

// Grillas personalizables
$top_col1 = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('top_col1', '') : '';
$top_col2 = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('top_col2', '') : '';
$top_col3 = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('top_col3', '') : '';
$bottom_col1 = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('bottom_col1', '') : '';
$bottom_col2 = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('bottom_col2', '') : '';
$bottom_col3 = function_exists('p5m_get_header_setting') ? p5m_get_header_setting('bottom_col3', '') : '';

// Verificar si hay contenido en las grillas
$has_top_grid = !empty($top_col1) || !empty($top_col2) || !empty($top_col3);
$has_bottom_grid = !empty($bottom_col1) || !empty($bottom_col2) || !empty($bottom_col3);
?>

<!-- Grilla Superior (Antes del Header) -->
<?php if ($has_top_grid): ?>
<div class="p5-header-top-grid bg-gray-50 border-b">
	<div class="p5-container">
		<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
			<?php if ($top_col1): ?>
				<div class="p5-header-top-col1">
					<?php echo do_shortcode(wp_kses_post($top_col1)); ?>
				</div>
			<?php endif; ?>
			<?php if ($top_col2): ?>
				<div class="p5-header-top-col2">
					<?php echo do_shortcode(wp_kses_post($top_col2)); ?>
				</div>
			<?php endif; ?>
			<?php if ($top_col3): ?>
				<div class="p5-header-top-col3">
					<?php echo do_shortcode(wp_kses_post($top_col3)); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- Contenedor principal de Alpine para el menú móvil -->
<header 
	x-data="{ isOpen: false }"
	class="p5-header <?php echo esc_attr($header_position_class . ' ' . $header_border_class); ?>  bg-transparent transition-all duration-300"
	data-scroll-threshold="<?php echo esc_attr($scroll_threshold); ?>"
	data-scroll-min-width="1024"
	<?php if ($sc_bg) echo ' data-scrolled-bg="' . esc_attr($sc_bg) . '"'; ?>
	<?php if ($sc_link) echo ' data-scrolled-link="' . esc_attr($sc_link) . '"'; ?>
	<?php if ($sc_cta_bg) echo ' data-scrolled-cta-bg="' . esc_attr($sc_cta_bg) . '"'; ?>
	<?php if ($sc_cta_text) echo ' data-scrolled-cta-text="' . esc_attr($sc_cta_text) . '"'; ?>
>
	<?php if ($scroll_threshold > 0 && ($sc_bg || $sc_link || $sc_cta_bg || $sc_cta_text)) : ?>
		<style id="p5-header-scrolled-styles">
			.p5-header.is-scrolled { <?php if ($sc_bg) { $sc_bg_css = $p5m_color_with_opacity($sc_bg, $sc_bg_op); echo 'background:' . esc_html($sc_bg_css) . ' !important;'; } ?> }
			.p5-header.is-scrolled nav a { <?php if ($sc_link) echo 'color:' . esc_html($sc_link) . ' !important;'; ?> }
			.p5-header.is-scrolled .book-contact { <?php if ($sc_cta_bg) echo 'background:' . esc_html($sc_cta_bg) . ' !important;'; ?> <?php if ($sc_cta_text) echo 'color:' . esc_html($sc_cta_text) . ' !important;'; ?> }
			/* Submenu background and links when header is scrolled */
			.p5-header.is-scrolled nav .sub-menu,
			.p5-header.is-scrolled nav .wp-block-navigation__submenu-container { <?php if ($sc_bg) { $__bg = $p5m_color_with_opacity($sc_bg, $sc_bg_op); echo 'background:' . esc_html($__bg) . ' !important;'; } ?> <?php if ($sc_link_border_css) echo 'border:1px solid ' . esc_html($sc_link_border_css) . ' !important;'; ?> }
			.p5-header.is-scrolled nav .sub-menu a,
			.p5-header.is-scrolled nav .wp-block-navigation__submenu-container a { <?php if ($sc_link) echo 'color:' . esc_html($sc_link) . ' !important;'; ?> }
			.p5-header.is-scrolled nav .sub-menu a:hover,
			.p5-header.is-scrolled nav .sub-menu a:focus,
			.p5-header.is-scrolled nav .wp-block-navigation__submenu-container a:hover,
			.p5-header.is-scrolled nav .wp-block-navigation__submenu-container a:focus { <?php if ($sc_link_hover_bg_css) echo 'background:' . esc_html($sc_link_hover_bg_css) . ' !important;'; ?> <?php if ($sc_link) echo 'color:' . esc_html($sc_link) . ' !important;'; ?> }
		</style>
	<?php endif; ?>

	<?php
	// Mobile nav styling using scrolled settings (keeps consistency on mobile)
	$mobile_nav_bg_css = '';
	$mobile_nav_link = '';
	$mobile_nav_hover_bg = '';
	$mobile_nav_border = '';
	if ($sc_bg) {
		$mobile_nav_bg_css = $p5m_color_with_opacity($sc_bg, $sc_bg_op);
	}
	if ($sc_link) {
		$mobile_nav_link = $sc_link;
		$mobile_nav_hover_bg = $p5m_color_with_opacity($sc_link, 0.08);
		$mobile_nav_border = $p5m_color_with_opacity($sc_link, 0.18);
	} elseif ($nav_link_color) {
		$mobile_nav_link = $nav_link_color;
		$mobile_nav_hover_bg = $p5m_color_with_opacity($nav_link_color, 0.08);
		$mobile_nav_border = $p5m_color_with_opacity($nav_link_color, 0.18);
	}
	if ($mobile_nav_bg_css || $mobile_nav_link) : ?>
		<style id="p5-header-mobile-nav-colors">
			#mobile-nav { <?php if ($mobile_nav_bg_css) echo 'background:' . esc_html($mobile_nav_bg_css) . ' !important;'; ?> }
			#mobile-nav a { <?php if ($mobile_nav_link) echo 'color:' . esc_html($mobile_nav_link) . ' !important;'; ?> }
			#mobile-nav a:hover, #mobile-nav a:focus { <?php if ($mobile_nav_hover_bg) echo 'background:' . esc_html($mobile_nav_hover_bg) . ' !important;'; ?> <?php if ($mobile_nav_link) echo 'color:' . esc_html($mobile_nav_link) . ' !important;'; ?> }
			#mobile-nav .wp-block-navigation__submenu-container { <?php if ($mobile_nav_bg_css) echo 'background:' . esc_html($mobile_nav_bg_css) . ' !important;'; ?> <?php if ($mobile_nav_border) echo 'border:1px solid ' . esc_html($mobile_nav_border) . ' !important;'; ?> }
			#mobile-nav .wp-block-navigation__submenu-container a { <?php if ($mobile_nav_link) echo 'color:' . esc_html($mobile_nav_link) . ' !important;'; ?> }
			#mobile-nav .wp-block-navigation__submenu-container a:hover,
			#mobile-nav .wp-block-navigation__submenu-container a:focus { <?php if ($mobile_nav_hover_bg) echo 'background:' . esc_html($mobile_nav_hover_bg) . ' !important;'; ?> <?php if ($mobile_nav_link) echo 'color:' . esc_html($mobile_nav_link) . ' !important;'; ?> }
		</style>
	<?php endif; ?>
	<?php if ($nav_link_color) : ?>
		<style id="p5-header-nav-color">
			.p5-header nav a { color: <?php echo esc_html($nav_link_color); ?>; }
		</style>
	<?php endif; ?>
	
	<!-- Estilos de orden de layout -->
	<style id="p5-header-layout-order">
		.p5-header-logo { order: <?php echo (int)$current_order['logo']; ?>; }
		.p5-header-nav { order: <?php echo (int)$current_order['nav']; ?>; }
		.p5-header-cta { order: <?php echo (int)$current_order['cta']; ?><?php if (!$cta_enable) echo '; display: none;'; ?> }
	</style>
	
	<?php if ($cta_enable && ($cta_hover_bg_color || $cta_hover_text_color)) : ?>
	<!-- Estilos de hover del CTA -->
	<style id="p5-header-cta-hover">
		.p5-header-cta .book-contact:hover {
			<?php if ($cta_hover_bg_color) echo 'background-color:' . esc_html($cta_hover_bg_color) . ' !important;'; ?>
			<?php if ($cta_hover_text_color) echo 'color:' . esc_html($cta_hover_text_color) . ' !important;'; ?>
		}
	</style>
	<?php endif; ?>
	
	<div class="p5-container"<?php echo $header_min_height_style; ?>>
		<div class="flex items-center justify-around gap-6 py-4 nav-menu-container">

			<!-- Logo Container -->
			<div class="p5-header-logo">
				<a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-3 shrink-0">
					<img src="<?php echo esc_url($logo_url); ?>"
						 alt="<?php echo esc_attr($logo_alt); ?>"
						 <?php if (!empty($logo_w) && !empty($logo_h)) : ?>
							width="<?php echo (int)$logo_w; ?>" height="<?php echo (int)$logo_h; ?>"
						 <?php endif; ?>
						 decoding="async"
						 class="h-10 w-auto logo-responsive"<?php echo $logo_width_style; ?> />
				</a>
			</div>

			<!-- Nav Container -->
			<div class="p5-header-nav <?php echo $menu_display === 'full' ? 'hidden lg:flex' : 'hidden'; ?> flex-1 justify-center">
				<nav class="nav-menu-desktop">
					<?php
					wp_nav_menu([
						 'theme_location' => $header_menu,
						  'container'      => false,
						  'menu_class'     => 'flex items-center gap-8',
						  'fallback_cb'    => false,
					]);
					?>
				</nav>
			</div>

			<!-- CTA Container -->
			<div class="p5-header-cta flex items-center gap-4">
				<!-- CTA Button -->
				<a href="<?php echo esc_url($cta_url); ?>" 
				   class="hidden sm:inline-flex items-center px-4 py-2 text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring transition book-contact"<?php echo $cta_style_attr; ?>>
					<?php echo esc_html($cta_text); ?>
				</a>
				
				<!-- Hamburger Button (visible en mobile siempre, o en desktop si menu_display = hamburger) -->
				<button 
					class="<?php echo $menu_display === 'hamburger' ? 'inline-flex' : 'lg:hidden inline-flex'; ?> items-center justify-center focus:outline-none"
					aria-controls="mobile-nav" 
					aria-expanded="false"
					@click="isOpen = !isOpen"
					:aria-expanded="isOpen.toString()"
					style="min-width: 30px"
				>
					<svg xmlns="http://www.w3.org/2000/svg" width="36" height="25" viewBox="0 0 36 25" fill="none">
						<path d="M2 12.5H33.5M2 2H33.5M2 23H33.5" stroke="#FEFDFD" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			</div>
            
		</div>

		<!-- Navegación Móvil (Controlada por Alpine.js) -->
		<div 
			id="mobile-nav" 
			class="<?php echo $menu_display === 'hamburger' ? '' : 'lg:hidden'; ?> border-t py-3"
			x-cloak
			x-show="isOpen" 
			x-transition:enter="transition ease-out duration-300"
			x-transition:enter-start="opacity-0 -translate-y-2"
			x-transition:enter-end="opacity-100 translate-y-0"
			x-transition:leave="transition ease-in duration-200"
			x-transition:leave-start="opacity-100 translate-y-0"
			x-transition:leave-end="opacity-0 -translate-y-2"
		>
			<?php
			// Menú Principal Móvil
			wp_nav_menu([
				'theme_location' => $header_menu,
				'container'      => false,
				'menu_class'     => 'flex flex-col gap-3 pt-3 pb-4',
				'fallback_cb'    => false,
			]);
			?>
		</div>
	</div>
</header>

<!-- Grilla Inferior (Después del Header) -->
<?php if ($has_bottom_grid): ?>
<div class="p5-header-bottom-grid bg-gray-50 border-b">
	<div class="p5-container">
		<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
			<?php if ($bottom_col1): ?>
				<div class="p5-header-bottom-col1">
					<?php echo do_shortcode(wp_kses_post($bottom_col1)); ?>
				</div>
			<?php endif; ?>
			<?php if ($bottom_col2): ?>
				<div class="p5-header-bottom-col2">
					<?php echo do_shortcode(wp_kses_post($bottom_col2)); ?>
				</div>
			<?php endif; ?>
			<?php if ($bottom_col3): ?>
				<div class="p5-header-bottom-col3">
					<?php echo do_shortcode(wp_kses_post($bottom_col3)); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php endif; ?>
