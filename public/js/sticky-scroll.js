/**
 * Sticky Horizontal Scroll Bar
 * Creates a fixed scroll bar at bottom of viewport for wide tables
 * Always visible when table needs horizontal scrolling
 */

(function () {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        console.log('[Sticky Scroll] Initializing...');

        // Create single sticky scroll bar for all tables
        let stickyScrollBar = null;
        let currentTable = null;

        function createStickyScrollBar() {
            if (!stickyScrollBar) {
                console.log('[Sticky Scroll] Creating sticky scroll bar element');
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
            try {
                const tableContainers = document.querySelectorAll('.table-responsive');
                console.log('[Sticky Scroll] Found', tableContainers.length, 'table containers');

                let activeContainer = null;

                // Find the first table that needs horizontal scroll
                for (const container of tableContainers) {
                    if (container.scrollWidth > container.clientWidth) {
                        activeContainer = container;
                        console.log('[Sticky Scroll] Active container found - scrollWidth:', container.scrollWidth, 'clientWidth:', container.clientWidth);
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

                    // Check footer visibility and hide scroll bar if footer is in viewport
                    const footer = document.querySelector('footer.sticky-footer');
                    let shouldHide = false;

                    if (footer) {
                        const footerRect = footer.getBoundingClientRect();
                        const viewportHeight = window.innerHeight;

                        // If footer is visible in viewport, hide the sticky scroll bar
                        if (footerRect.top < viewportHeight) {
                            shouldHide = true;
                            console.log('[Sticky Scroll] Footer visible, hiding scroll bar');
                        }
                    }

                    // Show or hide sticky scroll bar based on footer visibility
                    if (shouldHide) {
                        scrollBar.style.display = 'none';
                    } else {
                        scrollBar.style.display = 'block';
                        console.log('[Sticky Scroll] Showing scroll bar');

                        // Sync scroll events
                        scrollBar.onscroll = function () {
                            activeContainer.scrollLeft = scrollBar.scrollLeft;
                        };

                        activeContainer.onscroll = function () {
                            scrollBar.scrollLeft = activeContainer.scrollLeft;
                        };
                    }
                } else if (stickyScrollBar) {
                    // Hide if no table needs scrolling
                    console.log('[Sticky Scroll] No active container, hiding scroll bar');
                    stickyScrollBar.style.display = 'none';
                    currentTable = null;
                }
            } catch (error) {
                console.error('[Sticky Scroll] Error:', error);
            }
        }

        // Update on scroll, resize, and initially
        window.addEventListener('scroll', updateStickyScroll);
        window.addEventListener('resize', updateStickyScroll);
        updateStickyScroll();

        // Update after delays to ensure tables are rendered
        setTimeout(updateStickyScroll, 500);
        setTimeout(updateStickyScroll, 1000);
        setTimeout(updateStickyScroll, 2000);

        console.log('[Sticky Scroll] Initialization complete');
    }
})();
