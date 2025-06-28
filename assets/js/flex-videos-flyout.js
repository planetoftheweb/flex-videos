// Flex Videos Simple Overlay
document.addEventListener('DOMContentLoaded', function() {
  var overlay = document.getElementById('flex-videos-flyout-overlay');
  var items = document.querySelectorAll('.flex-videos-item-has-overlay');
  
  if (!overlay || !items.length) return;
  
  function showOverlay(item) {
    var rect = item.getBoundingClientRect();
    var title = item.getAttribute('data-title') || '';
    var desc = item.getAttribute('data-desc') || '';
    var thumbUrl = item.getAttribute('data-thumb') || '';
    
    var html = '';
    if (thumbUrl) html += '<img class="flex-videos-overlay-thumb" src="'+thumbUrl+'" alt="Large Thumbnail">';
    if (title) html += '<h2 class="flex-videos-overlay-title">'+title+'</h2>';
    if (desc) html += '<p class="flex-videos-overlay-desc">'+desc+'</p>';
    
    overlay.innerHTML = html;
    overlay.style.display = 'flex';
    overlay.style.opacity = '0.95';
    
    // Position to the right of the thumbnail
    var left = rect.right + 15;
    var top = rect.top;
    
    // If not enough space on right, try left
    if (left + 350 > window.innerWidth) {
      left = rect.left - 365;
    }
    
    // If still not enough space, center above
    if (left < 0) {
      left = rect.left + (rect.width / 2) - 175;
      top = rect.top - 300;
      if (top < 0) top = rect.bottom + 15;
    }
    
    overlay.style.left = left + 'px';
    overlay.style.top = top + 'px';
  }
  
  function hideOverlay() {
    overlay.style.opacity = '0';
    setTimeout(function() {
      overlay.style.display = 'none';
    }, 250);
  }
  
  items.forEach(function(item) {
    item.addEventListener('mouseenter', function() { 
      clearTimeout(window.flexVideosHideTimer);
      showOverlay(item); 
    });
    item.addEventListener('mouseleave', function() {
      window.flexVideosHideTimer = setTimeout(hideOverlay, 100);
    });
  });
  
  // Keep overlay visible when hovering over it
  overlay.addEventListener('mouseenter', function() {
    clearTimeout(window.flexVideosHideTimer);
  });
  
  overlay.addEventListener('mouseleave', function() {
    window.flexVideosHideTimer = setTimeout(hideOverlay, 100);
  });
  
  // Hide on scroll
  window.addEventListener('scroll', hideOverlay);
});
