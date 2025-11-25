/**
 * Custom Gutenberg Blocks Editor
 * JavaScript para los controles del editor de bloques
 *
 * @package P5Marketing
 */

(function(wp) {
  const { registerBlockType } = wp.blocks;
  const { InspectorControls } = wp.blockEditor || wp.editor;
  const { PanelBody, RangeControl, ToggleControl, SelectControl, CheckboxControl, TextControl, ColorPicker } = wp.components;
  const { __ } = wp.i18n;
  const { createElement: el, Fragment } = wp.element;
  const { ServerSideRender } = wp;

  /**
   * Posts Grid Block
   */
  registerBlockType('p5m/posts-grid', {
    title: __('Grilla de Posts', 'p5marketing'),
    description: __('Muestra posts en una grilla personalizable con múltiples opciones de visualización', 'p5marketing'),
    icon: 'grid-view',
    category: 'p5m-blocks',
    keywords: [__('posts', 'p5marketing'), __('grid', 'p5marketing'), __('blog', 'p5marketing')],
    
    edit: function(props) {
      const { attributes, setAttributes } = props;
      
      // Obtener post types disponibles
      const postTypeOptions = window.p5mBlocksData && window.p5mBlocksData.postTypes 
        ? window.p5mBlocksData.postTypes 
        : [{label: 'Posts', value: 'post'}];
      
      // Opciones de tamaños de imagen
      const imageSizeOptions = window.p5mBlocksData && window.p5mBlocksData.imageSizes
        ? window.p5mBlocksData.imageSizes
        : [
            {label: 'Thumbnail', value: 'thumbnail'},
            {label: 'Medium', value: 'medium'},
            {label: 'Large', value: 'large'},
            {label: 'Full', value: 'full'},
          ];
      
      return el(
        Fragment,
        {},
        el(
          InspectorControls,
          {},
          // Panel: Configuración de Layout
          el(
            PanelBody,
            {
              title: __('Configuración de Layout', 'p5marketing'),
              initialOpen: true,
            },
            el(RangeControl, {
              label: __('Columnas', 'p5marketing'),
              value: attributes.columns,
              onChange: (value) => setAttributes({ columns: value }),
              min: 1,
              max: 6,
              help: __('Número de columnas en la grilla (responsive)', 'p5marketing'),
            }),
            el(RangeControl, {
              label: __('Filas (aproximadas)', 'p5marketing'),
              value: attributes.rows,
              onChange: (value) => setAttributes({ rows: value }),
              min: 1,
              max: 10,
              help: __('Número de filas a mostrar', 'p5marketing'),
            }),
            el(RangeControl, {
              label: __('Posts por página', 'p5marketing'),
              value: attributes.postsPerPage,
              onChange: (value) => setAttributes({ postsPerPage: value }),
              min: 1,
              max: 50,
              help: __('Total de posts a mostrar', 'p5marketing'),
            }),
            el(SelectControl, {
              label: __('Espaciado entre items', 'p5marketing'),
              value: attributes.gapSize,
              options: [
                { label: __('Sin espacio', 'p5marketing'), value: 'none' },
                { label: __('Pequeño', 'p5marketing'), value: 'small' },
                { label: __('Mediano', 'p5marketing'), value: 'medium' },
                { label: __('Grande', 'p5marketing'), value: 'large' },
              ],
              onChange: (value) => setAttributes({ gapSize: value }),
            })
          ),
          
          // Panel: Tipografía
          el(
            PanelBody,
            {
              title: __('Tipografía', 'p5marketing'),
              initialOpen: false,
            },
            el('div', { style: { marginBottom: '12px' } },
              el('strong', {}, __('Título', 'p5marketing'))
            ),
            el(TextControl, {
              label: __('Familia de fuente (CSS)', 'p5marketing'),
              value: attributes.titleFontFamily || '',
              onChange: (value) => setAttributes({ titleFontFamily: value }),
              help: __('Ej: Inter, \'Helvetica Neue\', Arial, sans-serif', 'p5marketing'),
            }),
            el(TextControl, {
              label: __('Tamaño de fuente (CSS)', 'p5marketing'),
              value: attributes.titleFontSize || '',
              onChange: (value) => setAttributes({ titleFontSize: value }),
              help: __('Ej: 20px, 1.25rem', 'p5marketing'),
            }),
            el(TextControl, {
              label: __('Peso de fuente (opcional)', 'p5marketing'),
              value: attributes.titleWeight || '',
              onChange: (value) => setAttributes({ titleWeight: value }),
              help: __('Ej: 400, 600, 700 o bold', 'p5marketing'),
            }),
            el('div', { style: { marginTop: '8px' } },
              el('label', { className: 'components-base-control__label' }, __('Color del título', 'p5marketing')),
              el(ColorPicker, {
                color: attributes.titleColor || '',
                onChangeComplete: (value) => setAttributes({ titleColor: value.hex || value }),
                disableAlpha: true,
              })
            ),
            el('hr', {}),
            el('div', { style: { marginBottom: '12px' } },
              el('strong', {}, __('Texto/Extracto', 'p5marketing'))
            ),
            el(TextControl, {
              label: __('Familia de fuente (CSS)', 'p5marketing'),
              value: attributes.textFontFamily || '',
              onChange: (value) => setAttributes({ textFontFamily: value }),
            }),
            el(TextControl, {
              label: __('Tamaño de fuente (CSS)', 'p5marketing'),
              value: attributes.textFontSize || '',
              onChange: (value) => setAttributes({ textFontSize: value }),
            }),
            el('div', { style: { marginTop: '8px' } },
              el('label', { className: 'components-base-control__label' }, __('Color del texto', 'p5marketing')),
              el(ColorPicker, {
                color: attributes.textColor || '',
                onChangeComplete: (value) => setAttributes({ textColor: value.hex || value }),
                disableAlpha: true,
              })
            )
          ),

          // Panel: Botón "Leer más"
          el(
            PanelBody,
            {
              title: __('Botón "Leer más"', 'p5marketing'),
              initialOpen: false,
            },
            el(TextControl, {
              label: __('Texto del botón', 'p5marketing'),
              value: attributes.readMoreText || 'Leer más',
              onChange: (value) => setAttributes({ readMoreText: value }),
            }),
            el('div', { style: { marginTop: '8px' } },
              el('label', { className: 'components-base-control__label' }, __('Color de fondo', 'p5marketing')),
              el(ColorPicker, {
                color: attributes.buttonBgColor || '',
                onChangeComplete: (value) => setAttributes({ buttonBgColor: value.hex || value }),
                disableAlpha: false,
              })
            ),
            el('div', { style: { marginTop: '8px' } },
              el('label', { className: 'components-base-control__label' }, __('Color de texto', 'p5marketing')),
              el(ColorPicker, {
                color: attributes.buttonTextColor || '',
                onChangeComplete: (value) => setAttributes({ buttonTextColor: value.hex || value }),
                disableAlpha: false,
              })
            ),
            el(TextControl, {
              label: __('Familia de fuente (CSS)', 'p5marketing'),
              value: attributes.buttonFontFamily || '',
              onChange: (value) => setAttributes({ buttonFontFamily: value }),
            }),
            el(TextControl, {
              label: __('Tamaño de fuente (CSS)', 'p5marketing'),
              value: attributes.buttonFontSize || '',
              onChange: (value) => setAttributes({ buttonFontSize: value }),
            }),
            el(TextControl, {
              label: __('Padding (CSS)', 'p5marketing'),
              value: attributes.buttonPadding || '',
              onChange: (value) => setAttributes({ buttonPadding: value }),
              help: __('Ej: 10px 16px', 'p5marketing'),
            }),
            el(TextControl, {
              label: __('Ancho (CSS o "full"/"auto")', 'p5marketing'),
              value: attributes.buttonWidth || 'auto',
              onChange: (value) => setAttributes({ buttonWidth: value }),
              help: __('Ej: 200px, 100%, full, auto', 'p5marketing'),
            }),
            el(SelectControl, {
              label: __('Alineación', 'p5marketing'),
              value: attributes.buttonAlign || 'left',
              options: [
                { label: __('Izquierda', 'p5marketing'), value: 'left' },
                { label: __('Centro', 'p5marketing'), value: 'center' },
                { label: __('Derecha', 'p5marketing'), value: 'right' },
              ],
              onChange: (value) => setAttributes({ buttonAlign: value }),
            }),
            el(TextControl, {
              label: __('Borde (CSS)', 'p5marketing'),
              value: attributes.buttonBorder || '',
              onChange: (value) => setAttributes({ buttonBorder: value }),
              help: __('Ej: 1px solid #000', 'p5marketing'),
            }),
            el(TextControl, {
              label: __('Radio de borde (CSS)', 'p5marketing'),
              value: attributes.buttonRadius || '',
              onChange: (value) => setAttributes({ buttonRadius: value }),
              help: __('Ej: 6px', 'p5marketing'),
            }),
            el('div', { style: { marginTop: '8px' } },
              el('label', { className: 'components-base-control__label' }, __('Color fondo hover', 'p5marketing')),
              el(ColorPicker, {
                color: attributes.buttonHoverBgColor || '',
                onChangeComplete: (value) => setAttributes({ buttonHoverBgColor: value.hex || value }),
                disableAlpha: false,
              })
            ),
            el('div', { style: { marginTop: '8px' } },
              el('label', { className: 'components-base-control__label' }, __('Color texto hover', 'p5marketing')),
              el(ColorPicker, {
                color: attributes.buttonHoverTextColor || '',
                onChangeComplete: (value) => setAttributes({ buttonHoverTextColor: value.hex || value }),
                disableAlpha: false,
              })
            )
          ),

          // Panel: Post Types
          el(
            PanelBody,
            {
              title: __('Tipos de Post', 'p5marketing'),
              initialOpen: false,
            },
            el('p', { className: 'components-base-control__help' }, 
              __('Selecciona uno o más tipos de post a mostrar:', 'p5marketing')
            ),
            postTypeOptions.map(function(postType) {
              const isChecked = attributes.postTypes && attributes.postTypes.indexOf(postType.value) !== -1;
              
              return el(CheckboxControl, {
                label: postType.label,
                checked: isChecked,
                onChange: function(checked) {
                  let newPostTypes = attributes.postTypes ? [...attributes.postTypes] : [];
                  
                  if (checked) {
                    if (newPostTypes.indexOf(postType.value) === -1) {
                      newPostTypes.push(postType.value);
                    }
                  } else {
                    newPostTypes = newPostTypes.filter(function(type) {
                      return type !== postType.value;
                    });
                  }
                  
                  setAttributes({ postTypes: newPostTypes });
                },
              });
            })
          ),
          
          // Panel: Elementos Visibles
          el(
            PanelBody,
            {
              title: __('Elementos a Mostrar', 'p5marketing'),
              initialOpen: false,
            },
            el(ToggleControl, {
              label: __('Imagen destacada', 'p5marketing'),
              checked: attributes.showFeaturedImage,
              onChange: (value) => setAttributes({ showFeaturedImage: value }),
            }),
            attributes.showFeaturedImage && el(SelectControl, {
              label: __('Tamaño de imagen', 'p5marketing'),
              value: attributes.imageSize,
              options: imageSizeOptions,
              onChange: (value) => setAttributes({ imageSize: value }),
            }),
            el(ToggleControl, {
              label: __('Título', 'p5marketing'),
              checked: attributes.showTitle,
              onChange: (value) => setAttributes({ showTitle: value }),
            }),
            el(ToggleControl, {
              label: __('Extracto', 'p5marketing'),
              checked: attributes.showExcerpt,
              onChange: (value) => setAttributes({ showExcerpt: value }),
            }),
            attributes.showExcerpt && el(RangeControl, {
              label: __('Longitud del extracto (palabras)', 'p5marketing'),
              value: attributes.excerptLength,
              onChange: (value) => setAttributes({ excerptLength: value }),
              min: 5,
              max: 100,
            }),
            el(ToggleControl, {
              label: __('Autor', 'p5marketing'),
              checked: attributes.showAuthor,
              onChange: (value) => setAttributes({ showAuthor: value }),
            }),
            el(ToggleControl, {
              label: __('Fecha', 'p5marketing'),
              checked: attributes.showDate,
              onChange: (value) => setAttributes({ showDate: value }),
            }),
            el(ToggleControl, {
              label: __('Categorías', 'p5marketing'),
              checked: attributes.showCategories,
              onChange: (value) => setAttributes({ showCategories: value }),
            }),
            el(ToggleControl, {
              label: __('Botón "Leer más"', 'p5marketing'),
              checked: attributes.showReadMore,
              onChange: (value) => setAttributes({ showReadMore: value }),
            })
          ),
          
          // Panel: Ordenamiento
          el(
            PanelBody,
            {
              title: __('Ordenamiento', 'p5marketing'),
              initialOpen: false,
            },
            el(SelectControl, {
              label: __('Ordenar por', 'p5marketing'),
              value: attributes.orderBy,
              options: [
                { label: __('Fecha', 'p5marketing'), value: 'date' },
                { label: __('Título', 'p5marketing'), value: 'title' },
                { label: __('Autor', 'p5marketing'), value: 'author' },
                { label: __('Modificado', 'p5marketing'), value: 'modified' },
                { label: __('Aleatorio', 'p5marketing'), value: 'rand' },
                { label: __('Comentarios', 'p5marketing'), value: 'comment_count' },
              ],
              onChange: (value) => setAttributes({ orderBy: value }),
            }),
            el(SelectControl, {
              label: __('Orden', 'p5marketing'),
              value: attributes.order,
              options: [
                { label: __('Descendente (nuevo → viejo)', 'p5marketing'), value: 'DESC' },
                { label: __('Ascendente (viejo → nuevo)', 'p5marketing'), value: 'ASC' },
              ],
              onChange: (value) => setAttributes({ order: value }),
            })
          )
        ),
        
        // Vista previa en el editor
        el('div', { className: 'p5m-block-preview' },
          el(wp.serverSideRender || wp.components.ServerSideRender, {
            block: 'p5m/posts-grid',
            attributes: attributes,
          })
        )
      );
    },
    
    save: function() {
      // Bloque dinámico, renderizado en el servidor
      return null;
    },
  });

  /**
   * Posts Carousel Block
   */
  registerBlockType('p5m/posts-carousel', {
    title: __('Carrusel de Posts', 'p5marketing'),
    description: __('Carrusel con imagen destacada como fondo y título superpuesto', 'p5marketing'),
    icon: 'images-alt2',
    category: 'p5m-blocks',
    keywords: [__('posts', 'p5marketing'), __('carousel', 'p5marketing'), __('marquesina', 'p5marketing')],

    edit: function(props) {
      const { attributes, setAttributes } = props;

      const postTypeOptions = window.p5mBlocksData && window.p5mBlocksData.postTypes 
        ? window.p5mBlocksData.postTypes 
        : [{label: 'Posts', value: 'post'}];

      const imageSizeOptions = window.p5mBlocksData && window.p5mBlocksData.imageSizes
        ? window.p5mBlocksData.imageSizes
        : [
            {label: 'Thumbnail', value: 'thumbnail'},
            {label: 'Medium', value: 'medium'},
            {label: 'Large', value: 'large'},
            {label: 'Full', value: 'full'},
          ];

      return el(
        Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(
            PanelBody,
            { title: __('Contenido', 'p5marketing'), initialOpen: true },
            el('p', { className: 'components-base-control__help' }, __('Selecciona tipos de post y cantidad:', 'p5marketing')),
            postTypeOptions.map(function(postType) {
              const isChecked = attributes.postTypes && attributes.postTypes.indexOf(postType.value) !== -1;
              return el(CheckboxControl, {
                label: postType.label,
                checked: isChecked,
                onChange: function(checked) {
                  let newPostTypes = attributes.postTypes ? [...attributes.postTypes] : [];
                  if (checked) {
                    if (newPostTypes.indexOf(postType.value) === -1) newPostTypes.push(postType.value);
                  } else {
                    newPostTypes = newPostTypes.filter(function(type) { return type !== postType.value; });
                  }
                  setAttributes({ postTypes: newPostTypes });
                },
              });
            }),
            el(RangeControl, {
              label: __('Posts a mostrar', 'p5marketing'),
              value: attributes.postsPerPage || 6,
              onChange: (value) => setAttributes({ postsPerPage: value }),
              min: 1, max: 50,
            }),
            el(SelectControl, {
              label: __('Tamaño de imagen', 'p5marketing'),
              value: attributes.imageSize || 'large',
              options: imageSizeOptions,
              onChange: (value) => setAttributes({ imageSize: value }),
            })
          ),

          el(
            PanelBody,
            { title: __('Layout', 'p5marketing'), initialOpen: false },
            el(RangeControl, {
              label: __('Slides Desktop', 'p5marketing'),
              value: attributes.slidesDesktop || 3,
              onChange: (value) => setAttributes({ slidesDesktop: value }),
              min: 1, max: 6,
            }),
            el(RangeControl, {
              label: __('Slides Tablet', 'p5marketing'),
              value: attributes.slidesTablet || 2,
              onChange: (value) => setAttributes({ slidesTablet: value }),
              min: 1, max: 6,
            }),
            el(RangeControl, {
              label: __('Slides Mobile', 'p5marketing'),
              value: attributes.slidesMobile || 1,
              onChange: (value) => setAttributes({ slidesMobile: value }),
              min: 1, max: 3,
            }),
            el(SelectControl, {
              label: __('Espaciado', 'p5marketing'),
              value: attributes.gapSize || 'medium',
              options: [
                { label: __('Sin espacio', 'p5marketing'), value: 'none' },
                { label: __('Pequeño', 'p5marketing'), value: 'small' },
                { label: __('Mediano', 'p5marketing'), value: 'medium' },
                { label: __('Grande', 'p5marketing'), value: 'large' },
              ],
              onChange: (value) => setAttributes({ gapSize: value }),
            }),
            el(TextControl, {
              label: __('Tamaño mínimo de tarjeta', 'p5marketing'),
              value: attributes.minSize || '300px',
              onChange: (value) => setAttributes({ minSize: value }),
              help: __('Ej: 300px', 'p5marketing'),
            })
          ),

          el(
            PanelBody,
            { title: __('Comportamiento', 'p5marketing'), initialOpen: false },
            el(ToggleControl, {
              label: __('Marquesina infinita (continuous)', 'p5marketing'),
              checked: !!attributes.marquee,
              onChange: (value) => setAttributes({ marquee: value }),
            }),
            !attributes.marquee && el(ToggleControl, {
              label: __('Autoplay', 'p5marketing'),
              checked: attributes.autoplay !== false,
              onChange: (value) => setAttributes({ autoplay: value }),
            }),
            !attributes.marquee && el(RangeControl, {
              label: __('Tiempo entre transiciones (ms)', 'p5marketing'),
              value: attributes.autoplayDelay || 3000,
              onChange: (value) => setAttributes({ autoplayDelay: value }),
              min: 1000, max: 10000, step: 100,
            }),
            !attributes.marquee && el(RangeControl, {
              label: __('Velocidad de transición (ms)', 'p5marketing'),
              value: attributes.transitionSpeed || 400,
              onChange: (value) => setAttributes({ transitionSpeed: value }),
              min: 100, max: 2000, step: 50,
            }),
            !attributes.marquee && el(ToggleControl, {
              label: __('Loop infinito', 'p5marketing'),
              checked: attributes.infinite !== false,
              onChange: (value) => setAttributes({ infinite: value }),
            }),
            attributes.marquee && el(RangeControl, {
              label: __('Velocidad marquesina (px/s)', 'p5marketing'),
              value: attributes.marqueeSpeed || 60,
              onChange: (value) => setAttributes({ marqueeSpeed: value }),
              min: 20, max: 300, step: 5,
            }),
            attributes.marquee && el(ToggleControl, {
              label: __('Pausar al pasar el mouse', 'p5marketing'),
              checked: attributes.marqueePauseOnHover !== false,
              onChange: (value) => setAttributes({ marqueePauseOnHover: value }),
            }),
            !attributes.marquee && el(ToggleControl, {
              label: __('Mostrar flechas', 'p5marketing'),
              checked: attributes.showArrows !== false,
              onChange: (value) => setAttributes({ showArrows: value }),
            }),
            !attributes.marquee && el(ToggleControl, {
              label: __('Mostrar puntos', 'p5marketing'),
              checked: !!attributes.showDots,
              onChange: (value) => setAttributes({ showDots: value }),
            })
          ),

          el(
            PanelBody,
            { title: __('Estilos del título', 'p5marketing'), initialOpen: false },
            el('div', { style: { marginTop: '8px' } },
              el('label', { className: 'components-base-control__label' }, __('Color del texto', 'p5marketing')),
              el(ColorPicker, {
                color: attributes.titleColor || '#ffffff',
                onChangeComplete: (value) => setAttributes({ titleColor: value.hex || value }),
                disableAlpha: false,
              })
            ),
            el('div', { style: { marginTop: '8px' } },
              el('label', { className: 'components-base-control__label' }, __('Background del overlay', 'p5marketing')),
              el(ColorPicker, {
                color: attributes.titleBg || 'rgba(0,0,0,0.45)',
                onChangeComplete: (value) => setAttributes({ titleBg: value.hex || value }),
                disableAlpha: false,
              })
            ),
            el(TextControl, {
              label: __('Padding overlay', 'p5marketing'),
              value: attributes.overlayPadding || '0.75rem 1rem',
              onChange: (value) => setAttributes({ overlayPadding: value }),
            })
          )
        ),

        el('div', { className: 'p5m-block-preview' },
          el(wp.serverSideRender || wp.components.ServerSideRender, {
            block: 'p5m/posts-carousel',
            attributes: attributes,
          })
        )
      );
    },
    save: function(){ return null; }
  });

})(window.wp);
