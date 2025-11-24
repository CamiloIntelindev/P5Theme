/* p5m-admin.js
   Small helper to open WP Media frame from the theme settings page
*/
(function (){
  if (typeof window === 'undefined') return;
  document.addEventListener('DOMContentLoaded', function () {
    if (!window.jQuery) return;
    var $ = window.jQuery;

    $(document).on('click', '.p5m-media-upload', function (e) {
      e.preventDefault();
      var $btn = $(this);
      var selector = $btn.data('target');
      var $target = $(selector);
      if (!$target.length) return;

      // Ensure wp media is available
      if (typeof wp === 'undefined' || !wp.media) {
        alert('El selector de medios no está disponible.');
        return;
      }

      var frame = wp.media({
        title: 'Selecciona una imagen',
        library: { type: 'image' },
        button: { text: 'Usar esta imagen' },
        multiple: false
      });

      frame.on('select', function () {
        var att = frame.state().get('selection').first().toJSON();
        if (!att) return;
        $target.val(att.url).trigger('change');
        // set hidden id field if present
        var $id = $('#p5m_logo_id');
        if ($id.length && att.id) $id.val(att.id);
        var $preview = $('#p5m_logo_preview');
        if ($preview.length) {
          $preview.attr('src', att.url).show();
        }
      });

      frame.open();
    });

    // When manually pasting a URL update preview
    $(document).on('change', '#p5m_logo', function(){
      var val = $(this).val();
      var $preview = $('#p5m_logo_preview');
      if ($preview.length) {
        if (val) { $preview.attr('src', val).show(); }
        else { $preview.hide(); }
      }
    });
    // Remove button
    $(document).on('click', '.p5m-media-remove', function(e){
      e.preventDefault();
      var $btn = $(this);
      var $target = $($btn.siblings('input[name="p5m_settings[logo]"]').get(0));
      if (!$target || !$target.length) $target = $('#p5m_logo');
      $target.val('').trigger('change');
      $('#p5m_logo_id').val('0');
      $('#p5m_logo_preview').attr('src','').hide();
    });
  });
})();
