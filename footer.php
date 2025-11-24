<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the <main> element, the closing of the <body> and <html> tags.
 *
 * @package P5Marketing
 */
?>

<!-- 
    El margen superior (mt-16) proporciona un espacio visual clave entre el contenido principal 
    (que termina en page.php) y el pie de página.
-->
<?php
  // Leer colores desde opciones
  $p5m_footer_bg = function_exists('p5m_get_footer_setting') ? p5m_get_footer_setting('background_color', '') : '';
  $p5m_footer_text = function_exists('p5m_get_footer_setting') ? p5m_get_footer_setting('text_color', '') : '';
  $p5m_footer_link = function_exists('p5m_get_footer_setting') ? p5m_get_footer_setting('link_color', '') : '';
  $p5m_footer_link_hover = function_exists('p5m_get_footer_setting') ? p5m_get_footer_setting('link_hover_color', '') : '';
  $footer_style_attr = '';
  if ($p5m_footer_bg || $p5m_footer_text) {
      $styles = [];
      if ($p5m_footer_bg) $styles[] = 'background-color:' . esc_attr($p5m_footer_bg);
      if ($p5m_footer_text) $styles[] = 'color:' . esc_attr($p5m_footer_text);
      if (!empty($styles)) $footer_style_attr = ' style="' . esc_attr(implode(';', $styles)) . '"';
  }
?>
<footer class="p5-footer"<?php echo $footer_style_attr; ?>>
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 py-10">
        <?php if ($p5m_footer_link || $p5m_footer_link_hover): ?>
        <style id="p5-footer-inline-colors">
            <?php if ($p5m_footer_link): ?>
            .p5-footer a { color: <?php echo esc_html($p5m_footer_link); ?>; }
            <?php endif; ?>
            <?php if ($p5m_footer_link_hover): ?>
            .p5-footer a:hover { color: <?php echo esc_html($p5m_footer_link_hover); ?>; }
            <?php endif; ?>
        </style>
        <?php endif; ?>
        
        <?php
        // Grilla de 3×3 configurable desde P5 Footer Settings
        // Fila 1
        $has_row1 = false;
        for ($col = 1; $col <= 3; $col++) {
            $content = function_exists('p5m_get_footer_setting') ? p5m_get_footer_setting("col{$col}_row1") : '';
            if (!empty($content)) {
                $has_row1 = true;
                break;
            }
        }
        
        // Fila 2
        $has_row2 = false;
        for ($col = 1; $col <= 3; $col++) {
            $content = function_exists('p5m_get_footer_setting') ? p5m_get_footer_setting("col{$col}_row2") : '';
            if (!empty($content)) {
                $has_row2 = true;
                break;
            }
        }
        
        // Fila 3
        $has_row3 = false;
        for ($col = 1; $col <= 3; $col++) {
            $content = function_exists('p5m_get_footer_setting') ? p5m_get_footer_setting("col{$col}_row3") : '';
            if (!empty($content)) {
                $has_row3 = true;
                break;
            }
        }
        ?>
        
        <?php if ($has_row1): ?>
        <!-- Fila 1 -->
        <div class="grid gap-8 md:grid-cols-3 lg:grid-cols-3 items-start mb-2">
            <?php for ($col = 1; $col <= 3; $col++): 
                $content = function_exists('p5m_get_footer_setting') ? p5m_get_footer_setting("col{$col}_row1") : '';
            ?>
                <div class="footer-block">
                    <?php echo do_shortcode(wp_kses_post($content)); ?>
                </div>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($has_row2): ?>
        <!-- Fila 2 -->
        <div class="grid gap-8 md:grid-cols-3 lg:grid-cols-3 items-start mb-2">
            <?php for ($col = 1; $col <= 3; $col++): 
                $content = function_exists('p5m_get_footer_setting') ? p5m_get_footer_setting("col{$col}_row2") : '';
            ?>
                <div class="footer-block">
                    <?php echo do_shortcode(wp_kses_post($content)); ?>
                </div>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($has_row3): ?>
        <!-- Fila 3 -->
        <div class="grid md:grid-cols-1 lg:grid-cols-1 items-start mt-2">
            <?php for ($col = 1; $col <= 3; $col++): 
                $content = function_exists('p5m_get_footer_setting') ? p5m_get_footer_setting("col{$col}_row3") : '';
            ?>
                <div class="footer-block">
                    <?php echo do_shortcode(wp_kses_post($content)); ?>
                </div>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <!-- Sección de Copyright y Políticas -->
         <!--
        <div class="border-t mt-10 pt-6 flex flex-col sm:flex-row justify-between items-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
            <p class="mt-2 sm:mt-0">
                <!-- Ejemplo de un enlace estático -->
                 <!--
                <a href="/privacy-policy" class="hover:text-gray-800 transition">Privacy Policy</a>
            </p>
        </div>
            -->
    </div>
</footer>

<!-- 
    wp_footer() es fundamental para que WordPress y los plugins inyecten scripts antes del cierre de </body>.
-->
<?php wp_footer(); ?>
</body>
</html>