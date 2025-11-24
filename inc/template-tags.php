<?php
function p5m_get_site_logo(): array {
    // Fallbacks por defecto
    $fallback_url = get_template_directory_uri() . '/assets/img/logo-fallback.svg';
    $fallback_alt = get_bloginfo('name');

    // 0) Preferir ajustes del theme (p5m_settings): logo_id > logo (URL)
    if (function_exists('p5m_get_setting')) {
        $logo_id = intval(p5m_get_setting('logo_id', 0));
        if ($logo_id) {
            $src = wp_get_attachment_image_src($logo_id, 'full');
            if ($src && !empty($src[0])) {
                $alt = get_post_meta($logo_id, '_wp_attachment_image_alt', true);
                return [
                    'url'    => esc_url($src[0]),
                    'alt'    => esc_attr($alt ?: $fallback_alt),
                    'id'     => (int) $logo_id,
                    'width'  => isset($src[1]) ? (int)$src[1] : null,
                    'height' => isset($src[2]) ? (int)$src[2] : null,
                ];
            }
        }
        $logo_url = p5m_get_setting('logo', '');
        if (!empty($logo_url)) {
            return [
                'url' => esc_url($logo_url),
                'alt' => $fallback_alt,
            ];
        }
    }

    // 1) Intentar ACF Options (org_logo)
    if (function_exists('get_field')) {
        $logo = get_field('org_logo', 'option'); // puede ser array, url o id según "Return Format"
        if (!empty($logo)) {
            // ACF: Return Format = Array
            if (is_array($logo) && !empty($logo['url'])) {
                return [
                    'url'    => esc_url($logo['url']),
                    'alt'    => esc_attr($logo['alt'] ?: $fallback_alt),
                    'id'     => !empty($logo['id']) ? (int)$logo['id'] : null,
                    'width'  => !empty($logo['width']) ? (int)$logo['width'] : null,
                    'height' => !empty($logo['height']) ? (int)$logo['height'] : null,
                ];
            }
            // ACF: Return Format = URL (string)
            if (is_string($logo)) {
                return [
                    'url' => esc_url($logo),
                    'alt' => $fallback_alt,
                ];
            }
            // ACF: Return Format = ID (int)
            if (is_numeric($logo)) {
                $src = wp_get_attachment_image_src((int)$logo, 'full');
                if ($src && !empty($src[0])) {
                    $alt = get_post_meta((int)$logo, '_wp_attachment_image_alt', true);
                    return [
                        'url'    => esc_url($src[0]),
                        'alt'    => esc_attr($alt ?: $fallback_alt),
                        'id'     => (int)$logo,
                        'width'  => isset($src[1]) ? (int)$src[1] : null,
                        'height' => isset($src[2]) ? (int)$src[2] : null,
                    ];
                }
            }
        }
    }

    // 2) Intentar Customizer (custom_logo)
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $src = wp_get_attachment_image_src($custom_logo_id, 'full');
        if ($src && !empty($src[0])) {
            $alt = get_post_meta($custom_logo_id, '_wp_attachment_image_alt', true);
            return [
                'url' => esc_url($src[0]),
                'alt' => esc_attr($alt ?: $fallback_alt),
            ];
        }
    }

    // 3) Fallback final (archivo del theme)
    return [
        'url' => esc_url($fallback_url),
        'alt' => esc_attr($fallback_alt),
    ];
}

/**
 * Render a <picture> for an attachment with optional WebP source (if present).
 * Fallbacks gracefully to <img> only.
 */
function p5m_render_picture_for_attachment(int $attachment_id, string $size = 'large', array $attrs = []): void {
    if (!$attachment_id) return;

    $img = wp_get_attachment_image_src($attachment_id, $size);
    if (!$img || empty($img[0])) return;

    $url = $img[0];
    $width = isset($img[1]) ? (int)$img[1] : null;
    $height = isset($img[2]) ? (int)$img[2] : null;

    $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
    if ($alt === '') {
        $alt = get_the_title($attachment_id) ?: '';
    }

    $sizes_attr = $attrs['sizes'] ?? '100vw';
    $class_attr = $attrs['class'] ?? '';
    $loading = $attrs['loading'] ?? null;
    $decoding = $attrs['decoding'] ?? 'async';
    $fetchpriority = $attrs['fetchpriority'] ?? null;

    // Try to detect a matching .webp derivative on disk
    $webp_url = null;
    if (preg_match('/\.(jpe?g|png)$/i', $url)) {
        $candidate = preg_replace('/\.(jpe?g|png)$/i', '.webp', $url);
        $uploads = wp_upload_dir();
        if (!empty($uploads['baseurl']) && !empty($uploads['basedir'])) {
            $candidate_path = str_replace($uploads['baseurl'], $uploads['basedir'], $candidate);
            if ($candidate && $candidate_path && file_exists($candidate_path)) {
                $webp_url = $candidate;
            }
        }
    }

    echo '<picture>';
    if ($webp_url) {
        echo '<source type="image/webp" srcset="' . esc_url($webp_url) . '" sizes="' . esc_attr($sizes_attr) . '">';
    }
    echo '<img src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '"'
        . ($width ? ' width="' . (int)$width . '"' : '')
        . ($height ? ' height="' . (int)$height . '"' : '')
        . ' sizes="' . esc_attr($sizes_attr) . '"'
        . ($class_attr ? ' class="' . esc_attr($class_attr) . '"' : '')
        . ($loading ? ' loading="' . esc_attr($loading) . '"' : '')
        . ($decoding ? ' decoding="' . esc_attr($decoding) . '"' : '')
        . ($fetchpriority ? ' fetchpriority="' . esc_attr($fetchpriority) . '"' : '')
        . ' />';
    echo '</picture>';
}

/**
 * Convenience: Render featured image as <picture> if exists.
 */
function p5m_render_featured_picture(string $size = 'large', array $attrs = []): void {
    $thumb_id = get_post_thumbnail_id();
    if ($thumb_id) {
        p5m_render_picture_for_attachment($thumb_id, $size, $attrs);
    }
}

/**
 * Helper: Get page layout setting
 * Returns one of: 'normal', 'fullwidth', 'sidebar-left', 'sidebar-right'
 * Default is 'normal' (centrado con ancho máximo)
 */
function p5m_get_layout(): string {
    if (!is_singular()) return 'normal';
    $layout = get_post_meta(get_the_ID(), 'p5m_layout', true);
    $valid_layouts = ['normal', 'fullwidth', 'sidebar-left', 'sidebar-right'];
    return in_array($layout, $valid_layouts, true) ? $layout : 'normal';
}

/**
 * Deprecated: Use p5m_get_layout() instead
 * Helper: Check if the current page should be full-width (100% ancho)
 * Returns true if the post meta 'p5m_fullwidth' is set to 1
 */
function p5m_is_fullwidth(): bool {
    return p5m_get_layout() === 'fullwidth';
}

/**
 * Helper: Check if page title should be hidden
 * Returns true if the post meta 'p5m_hide_title' is set to 1
 */
function p5m_hide_title(): bool {
    if (!is_singular()) return false;
    $hide = get_post_meta(get_the_ID(), 'p5m_hide_title', true);
    return $hide === '1';
}

/**
 * Helper: Render sidebar for post/page
 * Outputs widget area or custom content
 */
function p5m_the_sidebar() {
    if (is_active_sidebar('p5m_post_sidebar')) {
        echo '<aside class="prose lg:prose-lg">';
        dynamic_sidebar('p5m_post_sidebar');
        echo '</aside>';
    }
}

/**
 * Helper: Render breadcrumbs with Schema.org BreadcrumbList JSON-LD
 * Displays navigation breadcrumbs and outputs structured data
 */
function p5m_the_breadcrumbs() {
    if (is_front_page()) return;

    // Cache entire breadcrumbs markup per URL + locale for 2 hours
    $cache_key = 'breadcrumbs:' . md5((isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '') . '|' . get_locale());
    if (function_exists('p5m_cache_get')) {
        $cached = p5m_cache_get($cache_key);
        if (!empty($cached)) { echo $cached; return; }
        ob_start();
    }

    $breadcrumbs = [];
    $breadcrumb_json = [];

    // Home
    $breadcrumbs[] = '<a href="' . esc_url(home_url('/')) . '" class="hover:text-blue-600 transition-colors">' . esc_html(get_bloginfo('name')) . '</a>';
    $breadcrumb_json[] = [
        '@type'    => 'ListItem',
        'position' => 1,
        'name'     => get_bloginfo('name'),
        'item'     => esc_url(home_url('/')),
    ];

    $position = 2;

    if (is_search()) {
        $breadcrumbs[] = '<span class="text-gray-500">' . esc_html__('Search', 'p5marketing') . ': ' . esc_html(get_search_query()) . '</span>';
        $breadcrumb_json[] = [
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => __('Search', 'p5marketing') . ': ' . get_search_query(),
        ];
        $position++;
    } elseif (is_404()) {
        $breadcrumbs[] = '<span class="text-gray-500">' . esc_html__('Error 404', 'p5marketing') . '</span>';
        $breadcrumb_json[] = [
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => __('Error 404', 'p5marketing'),
        ];
        $position++;
    } elseif (is_archive()) {
        if (is_category()) {
            $cat = get_queried_object();
            if ($cat->parent) {
                $parent = get_category($cat->parent);
                $breadcrumbs[] = '<a href="' . esc_url(get_category_link($parent->term_id)) . '" class="hover:text-blue-600 transition-colors">' . esc_html($parent->name) . '</a>';
                $breadcrumb_json[] = [
                    '@type'    => 'ListItem',
                    'position' => $position,
                    'name'     => $parent->name,
                    'item'     => esc_url(get_category_link($parent->term_id)),
                ];
                $position++;
            }
            $breadcrumbs[] = '<span class="text-gray-500">' . esc_html($cat->name) . '</span>';
            $breadcrumb_json[] = [
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => $cat->name,
            ];
            $position++;
        } elseif (is_tag()) {
            $tag = get_queried_object();
            $breadcrumbs[] = '<span class="text-gray-500">' . esc_html__('Tag', 'p5marketing') . ': ' . esc_html($tag->name) . '</span>';
            $breadcrumb_json[] = [
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => __('Tag', 'p5marketing') . ': ' . $tag->name,
            ];
            $position++;
        } elseif (is_tax()) {
            $term = get_queried_object();
            $breadcrumbs[] = '<span class="text-gray-500">' . esc_html($term->name) . '</span>';
            $breadcrumb_json[] = [
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => $term->name,
            ];
            $position++;
        } else {
            $breadcrumbs[] = '<span class="text-gray-500">' . esc_html(post_type_archive_title('', false)) . '</span>';
            $breadcrumb_json[] = [
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => post_type_archive_title('', false),
            ];
            $position++;
        }
    } elseif (is_singular()) {
        global $post;
        
        // For posts, show category
        if ($post->post_type === 'post') {
            $categories = get_the_category();
            if (!empty($categories)) {
                $cat = $categories[0];
                $breadcrumbs[] = '<a href="' . esc_url(get_category_link($cat->term_id)) . '" class="hover:text-blue-600 transition-colors">' . esc_html($cat->name) . '</a>';
                $breadcrumb_json[] = [
                    '@type'    => 'ListItem',
                    'position' => $position,
                    'name'     => $cat->name,
                    'item'     => esc_url(get_category_link($cat->term_id)),
                ];
                $position++;
            }
        }

        // Current post/page
        $breadcrumbs[] = '<span class="text-gray-500">' . esc_html(get_the_title()) . '</span>';
        $breadcrumb_json[] = [
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => get_the_title(),
        ];
    } elseif (is_paged()) {
        $breadcrumbs[] = '<span class="text-gray-500">' . esc_html__('Page', 'p5marketing') . ' ' . get_query_var('paged') . '</span>';
        $breadcrumb_json[] = [
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => __('Page', 'p5marketing') . ' ' . get_query_var('paged'),
        ];
    }

    // Output HTML breadcrumbs
    echo '<nav aria-label="Breadcrumb" class="mb-8">';
    echo '<ol class="flex items-center gap-2 text-sm text-gray-700">';
    echo '<li>' . implode('</li><li class="text-gray-400"> / </li><li>', $breadcrumbs) . '</li>';
    echo '</ol>';
    echo '</nav>';

    // Output Schema.org BreadcrumbList JSON-LD
    if (!empty($breadcrumb_json)) {
        $json_ld = [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $breadcrumb_json,
        ];
        echo '<script type="application/ld+json">' . wp_json_encode($json_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }

    if (function_exists('p5m_cache_set')) {
        $html = ob_get_clean();
        p5m_cache_set($cache_key, $html, 2 * HOUR_IN_SECONDS);
        echo $html;
    }
}

/**
 * Helper: Output Schema.org Organization JSON-LD
 * Should be called once in header/footer
 */
function p5m_the_organization_schema() {
    $cache_key = 'schema:org';
    if (function_exists('p5m_cache_get')) {
        $cached = p5m_cache_get($cache_key);
        if (!empty($cached)) { echo $cached; return; }
        ob_start();
    }
    $schema = [
        '@context'  => 'https://schema.org',
        '@type'     => 'Organization',
        'name'      => get_bloginfo('name'),
        'url'       => esc_url(home_url('/')),
        'logo'      => p5m_get_site_logo()['url'],
        'sameAs'    => [],
    ];

    // Add social profiles if available
    $facebook = p5m_get_setting('facebook_url', '');
    if (!empty($facebook)) {
        $schema['sameAs'][] = esc_url($facebook);
    }
    $instagram = p5m_get_setting('instagram_url', '');
    if (!empty($instagram)) {
        $schema['sameAs'][] = esc_url($instagram);
    }
    $twitter = p5m_get_setting('twitter_url', '');
    if (!empty($twitter)) {
        $schema['sameAs'][] = esc_url($twitter);
    }
    $linkedin = p5m_get_setting('linkedin_url', '');
    if (!empty($linkedin)) {
        $schema['sameAs'][] = esc_url($linkedin);
    }

    // Add contact if available
    $contact_email = p5m_get_setting('contact_email', '');
    if (!empty($contact_email)) {
        $schema['contactPoint'] = [
            '@type'   => 'ContactPoint',
            'contact' => 'general',
            'email'   => esc_attr($contact_email),
        ];
    }

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    if (function_exists('p5m_cache_set')) {
        $html = ob_get_clean();
        p5m_cache_set($cache_key, $html, 12 * HOUR_IN_SECONDS);
        echo $html;
    }
}

/**
 * Helper: Output Schema.org Article JSON-LD for blog posts
 * Should be called in singular.php or single.php for posts
 */
function p5m_the_article_schema() {
    if (!is_singular('post')) return;

    $cache_key = 'schema:article:' . get_the_ID();
    if (function_exists('p5m_cache_get')) {
        $cached = p5m_cache_get($cache_key);
        if (!empty($cached)) { echo $cached; return; }
        ob_start();
    }

    $schema = [
        '@context'      => 'https://schema.org',
        '@type'         => 'BlogPosting',
        'headline'      => get_the_title(),
        'description'   => get_the_excerpt() ?: wp_trim_words(get_the_content(), 20),
        'image'         => has_post_thumbnail() ? wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] : p5m_get_site_logo()['url'],
        'datePublished' => esc_attr(get_the_date('c')),
        'dateModified'  => esc_attr(get_the_modified_date('c')),
        'author'        => [
            '@type' => 'Person',
            'name'  => get_the_author_meta('display_name'),
            'url'   => esc_url(get_author_posts_url(get_the_author_meta('ID'))),
        ],
        'publisher'     => [
            '@type' => 'Organization',
            'name'  => get_bloginfo('name'),
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => p5m_get_site_logo()['url'],
            ],
        ],
        'url'           => esc_url(get_the_permalink()),
    ];

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    if (function_exists('p5m_cache_set')) {
        $html = ob_get_clean();
        p5m_cache_set($cache_key, $html, 12 * HOUR_IN_SECONDS);
        echo $html;
    }
}

/**
 * Helper: Output Schema.org WebPage JSON-LD for pages
 * Should be called in singular.php or page.php
 */
function p5m_the_webpage_schema() {
    if (!is_singular() || is_singular('post')) return;

    $cache_key = 'schema:webpage:' . get_the_ID();
    if (function_exists('p5m_cache_get')) {
        $cached = p5m_cache_get($cache_key);
        if (!empty($cached)) { echo $cached; return; }
        ob_start();
    }

    $schema = [
        '@context'      => 'https://schema.org',
        '@type'         => 'WebPage',
        'name'          => get_the_title(),
        'description'   => get_the_excerpt() ?: wp_trim_words(get_the_content(), 20),
        'url'           => esc_url(get_the_permalink()),
        'datePublished' => esc_attr(get_the_date('c')),
        'dateModified'  => esc_attr(get_the_modified_date('c')),
        'author'        => [
            '@type' => 'Organization',
            'name'  => get_bloginfo('name'),
        ],
    ];

    if (has_post_thumbnail()) {
        $schema['image'] = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0];
    }

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    if (function_exists('p5m_cache_set')) {
        $html = ob_get_clean();
        p5m_cache_set($cache_key, $html, 12 * HOUR_IN_SECONDS);
        echo $html;
    }
}

/**
 * Helper: Get custom excerpt with optional length
 * @param int $length - Number of words (default 30)
 * @param int $post_id - Post ID (default current post)
 * @return string Trimmed excerpt with ellipsis
 */
function p5m_excerpt( $length = 30, $post_id = null ) {
    if ( null === $post_id ) {
        $post_id = get_the_ID();
    }

    $post = get_post( $post_id );
    if ( ! $post ) {
        return '';
    }

    $excerpt = $post->post_excerpt ?: $post->post_content;
    $excerpt = wp_strip_all_tags( $excerpt );
    $excerpt = wp_trim_words( $excerpt, $length );

    return esc_html( $excerpt );
}

/**
 * Helper: Output pagination with Tailwind styling
 * @param array $args - Arguments for the_posts_pagination
 */
function p5m_paginate( $args = [] ) {
    $defaults = array(
        'mid_size'           => 2,
        'prev_text'          => esc_html__( '← Previous', 'p5marketing' ),
        'next_text'          => esc_html__( 'Next →', 'p5marketing' ),
        'before_page_number' => '<span class="sr-only">' . esc_html__( 'Page', 'p5marketing' ) . ' </span>',
        'type'               => 'list',
    );

    $args = wp_parse_args( $args, $defaults );

    echo '<nav class="flex items-center justify-between gap-4" aria-label="' . esc_attr__( 'Pagination', 'p5marketing' ) . '">';
    the_posts_pagination( $args );
    echo '</nav>';
}

/**
 * Helper: Get posts by custom query
 * @param array $args - WP_Query arguments
 * @return array Array of WP_Post objects
 */
function p5m_get_posts( $args = [] ) {
    $defaults = array(
        'posts_per_page' => 10,
        'paged'          => get_query_var( 'paged' ) ?: 1,
        'post_status'    => 'publish',
    );

    $args   = wp_parse_args( $args, $defaults );
    $query  = new WP_Query( $args );

    return $query->posts;
}

/**
 * Helper: Render button for AJAX load more
 * @param string $text - Button text (default: "Load More")
 * @param array $query_args - Query arguments for load more (will be stored in data attr)
 */
function p5m_load_more_button( $text = '', $query_args = [] ) {
    if ( empty( $text ) ) {
        $text = esc_html__( 'Load More', 'p5marketing' );
    }

    $query_json = wp_json_encode( $query_args );

    echo '<div class="text-center mt-12">';
    echo '<button class="p5m-load-more-btn inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded hover:bg-blue-700 transition-colors" data-query="' . esc_attr( $query_json ) . '" data-paged="1">';
    echo esc_html( $text );
    echo '</button>';
    echo '</div>';
}

/**
 * Helper: Enqueue AJAX load more script
 * Call this in wp_enqueue_scripts or add_action( 'wp_enqueue_scripts', ... )
 */
function p5m_enqueue_load_more() {
    wp_enqueue_script( 'p5m-load-more', get_template_directory_uri() . '/assets/js/load-more.js', array( 'jquery' ), filemtime( get_template_directory() . '/assets/js/load-more.js' ), true );
    wp_localize_script( 'p5m-load-more', 'p5mLoadMore', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'p5m_load_more_nonce' ),
    ) );
}
