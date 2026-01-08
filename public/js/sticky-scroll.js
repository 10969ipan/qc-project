/**
 * Sticky Horizontal Scroll Bar
 * Creates a fixed scroll bar at bottom of viewport for wide tables
 * Always visible when table needs horizontal scrolling
 */

document.addEventListener('DOMContentLoaded', function () {
    // Create single sticky scroll bar for all tables
    let stickyScrollBar = null;
    let currentTable = null;

    function createStickyScrollBar() {
        if (!stickyScrollBar) {
            stickyScrollBar = document.createElement('div');
            stickyScrollBar.className = 'sticky-scroll-bar';

            const stickyScrollContent = document.createElement('div');
            stickyScrollContent.className = 'sticky-scroll-content';

            stickyScrollBar.appendChild(stickyScrollContent);
            document.body.appendChild(stickyScrollBar);
        }
        return stickyScrollBar;
    }

    function updateStickyScroll() {
        const tableContainers = document.querySelectorAll('.table-responsive');
        let activeContainer = null;

        // Find the first visible table that needs horizontal scroll
        for (const container of tableContainers) {
            const rect = container.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight && rect.bottom > 0;

            if (isVisible && container.scrollWidth > container.clientWidth) {
                activeContainer = container;
                break;
            }
        }

        if (activeContainer) {
            const scrollBar = createStickyScrollBar();
            const scrollContent = scrollBar.querySelector('.sticky-scroll-content');

            // Update width to match table
            scrollContent.style.width = activeContainer.scrollWidth + 'px';

            // Sync scroll position
            if (currentTable !== activeContainer) {
                scrollBar.scrollLeft = activeContainer.scrollLeft;
                currentTable = activeContainer;
            }

            // Show sticky scroll bar
            scrollBar.style.display = 'block';

            // Sync scroll events
            scrollBar.onscroll = function () {
                activeContainer.scrollLeft = scrollBar.scrollLeft;
            };

            activeContainer.onscroll = function () {
                scrollBar.scrollLeft = activeContainer.scrollLeft;
            };
        } else if (stickyScrollBar) {
            // Hide if no table needs scrolling
            stickyScrollBar.style.display = 'none';
            currentTable = null;
        }
    }

    // Update on scroll, resize, and initially
    window.addEventListener('scroll', updateStickyScroll);
    window.addEventListener('resize', updateStickyScroll);
    updateStickyScroll();

    // Update after a short delay to ensure tables are rendered
    setTimeout(updateStickyScroll, 500);
});
