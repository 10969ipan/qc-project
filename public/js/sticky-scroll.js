/**
 * Sticky Horizontal Scroll Bar
 * Keeps horizontal scroll bar visible at bottom of viewport for wide tables
 */

document.addEventListener('DOMContentLoaded', function() {
    // Find all table-responsive elements
    const tableContainers = document.querySelectorAll('.table-responsive');
    
    tableContainers.forEach(function(container) {
        // Skip if table doesn't need horizontal scroll
        if (container.scrollWidth <= container.clientWidth) {
            return;
        }
        
        // Create sticky scroll bar
        const stickyScrollBar = document.createElement('div');
        stickyScrollBar.className = 'sticky-scroll-bar';
        
        const stickyScrollContent = document.createElement('div');
        stickyScrollContent.className = 'sticky-scroll-content';
        stickyScrollContent.style.width = container.scrollWidth + 'px';
        
        stickyScrollBar.appendChild(stickyScrollContent);
        container.parentNode.insertBefore(stickyScrollBar, container.nextSibling);
        
        // Sync scroll positions
        container.addEventListener('scroll', function() {
            stickyScrollBar.scrollLeft = container.scrollLeft;
        });
        
        stickyScrollBar.addEventListener('scroll', function() {
            container.scrollLeft = stickyScrollBar.scrollLeft;
        });
        
        // Update width on window resize
        window.addEventListener('resize', function() {
            stickyScrollContent.style.width = container.scrollWidth + 'px';
            
            // Hide sticky scroll if not needed
            if (container.scrollWidth <= container.clientWidth) {
                stickyScrollBar.style.display = 'none';
            } else {
                stickyScrollBar.style.display = 'block';
            }
        });
    });
});
