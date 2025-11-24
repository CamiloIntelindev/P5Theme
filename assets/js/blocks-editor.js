/**
 * Custom Gutenberg Blocks Editor
 * JavaScript para los controles del editor de bloques
 *
 * @package P5Marketing
 */

(function(wp) {
  const { registerBlockType } = wp.blocks;
  const { InspectorControls } = wp.blockEditor || wp.editor;
  const { PanelBody, RangeControl, ToggleControl, SelectControl, CheckboxControl } = wp.components;
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

})(window.wp);
