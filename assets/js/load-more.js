/**
 * AJAX Load More Handler
 * Loads more posts via AJAX when button is clicked
 * 
 * Usage:
 * 1. Call p5m_enqueue_load_more() in your template or functions.php
 * 2. Use p5m_load_more_button( text, query_args ) to render the button
 * 3. This script will handle clicks and append new posts
 */

(function($) {
    'use strict';

    $(document).on('click', '.p5m-load-more-btn', function(e) {
        e.preventDefault();

        const btn = $(this);
        const originalText = btn.text();
        const paged = parseInt(btn.data('paged'), 10) || 1;
        const queryArgs = btn.data('query') || {};

        // Set loading state
        btn.prop('disabled', true).text('Loading...');

        // Make AJAX request
        $.ajax({
            type: 'POST',
            url: p5mLoadMore.ajaxUrl,
            data: {
                action: 'p5m_load_more_posts',
                nonce: p5mLoadMore.nonce,
                paged: paged + 1,
                query_args: queryArgs,
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.html) {
                    // Append new posts before the button
                    btn.before(response.data.html);

                    // Update paged counter
                    btn.data('paged', paged + 1);

                    // Hide button if no more posts
                    if (!response.data.has_more) {
                        btn.fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        btn.prop('disabled', false).text(originalText);
                    }
                } else {
                    btn.prop('disabled', false).text(originalText);
                    console.error('Load more failed:', response);
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false).text(originalText);
                console.error('AJAX error:', error);
            }
        });
    });
})(jQuery);
