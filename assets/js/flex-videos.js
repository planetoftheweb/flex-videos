/**
 * Flex Videos Plugin JavaScript
 * 
 * @package FlexVideos
 * @version 1.0.1
 */

(function($) {
    'use strict';

    /**
     * Initialize the plugin when DOM is ready
     */
    $(document).ready(function() {
        initFlexVideos();
    });

    /**
     * Initialize flex videos functionality
     */
    function initFlexVideos() {
        // Make videos responsive
        makeVideosResponsive();
        
        // Handle lazy loading if needed
        handleLazyLoading();
        
        // Hide play button overlays
        hidePlayButtonOverlays();
        
        // Add click tracking
        addClickTracking();
    }

    /**
     * Hide YouTube play button overlays to show thumbnails clearly
     */
    function hidePlayButtonOverlays() {
        // Hide play buttons on YouTube iframes
        $('.flex-video-item iframe[src*="youtube"]').each(function() {
            var $iframe = $(this);
            
            // Add parameters to hide controls and overlays
            var src = $iframe.attr('src');
            if (src && src.indexOf('controls=0') === -1) {
                // Add parameters to minimize overlays
                var separator = src.indexOf('?') !== -1 ? '&' : '?';
                src += separator + 'controls=0&showinfo=0&rel=0&modestbranding=1&playsinline=1';
                $iframe.attr('src', src);
            }
        });

        // Hide any play button overlays that might appear
        setInterval(function() {
            $('.flex-video-item .ytp-large-play-button, .flex-video-item .ytp-cued-thumbnail-overlay').hide();
        }, 1000);

        // Use CSS to ensure play buttons stay hidden
        if (!$('#hide-play-buttons-style').length) {
            $('<style id="hide-play-buttons-style">')
                .text(`
                    .flex-video-item .ytp-large-play-button,
                    .flex-video-item .ytp-play-button,
                    .flex-video-item .ytp-cued-thumbnail-overlay {
                        display: none !important;
                        opacity: 0 !important;
                        visibility: hidden !important;
                    }
                `)
                .appendTo('head');
        }
    }

    /**
     * Make embedded videos responsive
     */
    function makeVideosResponsive() {
        $('.flex-video-item iframe').each(function() {
            var $iframe = $(this);
            var width = $iframe.attr('width');
            var height = $iframe.attr('height');
            
            if (width && height) {
                var aspectRatio = (height / width) * 100;
                $iframe.wrap('<div class="flex-video-responsive" style="position: relative; padding-bottom: ' + aspectRatio + '%; height: 0; overflow: hidden;"></div>');
                $iframe.css({
                    'position': 'absolute',
                    'top': '0',
                    'left': '0',
                    'width': '100%',
                    'height': '100%'
                });
            }
        });
    }

    /**
     * Handle lazy loading for better performance
     */
    function handleLazyLoading() {
        if ('IntersectionObserver' in window) {
            var lazyVideoObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var lazyVideo = entry.target;
                        var src = lazyVideo.getAttribute('data-src');
                        if (src) {
                            lazyVideo.setAttribute('src', src);
                            lazyVideo.removeAttribute('data-src');
                            lazyVideoObserver.unobserve(lazyVideo);
                        }
                    }
                });
            });

            $('.flex-video-item iframe[data-src]').each(function() {
                lazyVideoObserver.observe(this);
            });
        }
    }

    /**
     * Add click tracking for analytics
     */
    function addClickTracking() {
        $('.flex-video-item').on('click', function() {
            var videoTitle = $(this).find('.flex-video-title').text() || 'Unknown Video';
            
            // Track with Google Analytics if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'video_click', {
                    'video_title': videoTitle,
                    'event_category': 'flex_videos',
                    'event_label': videoTitle
                });
            }
            
            // Track with Google Analytics Universal if available
            if (typeof ga !== 'undefined') {
                ga('send', 'event', 'Flex Videos', 'Video Click', videoTitle);
            }
        });
    }

    /**
     * Admin functionality
     */
    if ($('.flex-videos-admin').length > 0) {
        // Cache reset functionality
        $('#reset-cache-btn').on('click', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var originalText = $btn.text();
            
            $btn.text('Resetting...').prop('disabled', true);
            
            $.ajax({
                url: flexVideosAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'flex_videos_reset_cache',
                    nonce: flexVideosAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $btn.text('Cache Reset!').addClass('button-primary');
                        setTimeout(function() {
                            $btn.text(originalText).removeClass('button-primary').prop('disabled', false);
                        }, 2000);
                    } else {
                        alert('Error resetting cache: ' + response.data);
                        $btn.text(originalText).prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Error resetting cache. Please try again.');
                    $btn.text(originalText).prop('disabled', false);
                }
            });
        });
    }

})(jQuery);
