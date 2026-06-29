/* 
    E-Victoire Dashboard Interactions
    Adds modern animations and chart theme updates.
*/

document.addEventListener('DOMContentLoaded', () => {
    console.log('E-Victoire Dashboard Enhancements Loaded');

    // 1. Page Fade-in Effect
    const contentWrapper = document.querySelector('.content-wrapper');
    if (contentWrapper) {
        contentWrapper.style.opacity = '0';
        contentWrapper.style.transition = 'opacity 0.5s ease-in-out';
        setTimeout(() => {
            contentWrapper.style.opacity = '1';
        }, 100);
    }

    // 2. Add dynamic hover effects to cards if not handled by CSS
    const cards = document.querySelectorAll('.box, .info-box');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            // Additional JS effects if needed
        });
    });

    // 3. Chart.js Theme Injection
    // Many school systems use Chart.js for statistics.
    if (window.Chart) {
        const originalInit = Chart.prototype.init;
        Chart.prototype.init = function() {
            if (this.options && this.options.elements) {
                // Apply our colors to datasets
                const colors = ['#1a237e', '#0097a7', '#3f51b5', '#ffd600', '#ff5252'];
                if (this.data.datasets) {
                    this.data.datasets.forEach((dataset, i) => {
                        dataset.backgroundColor = dataset.backgroundColor || colors[i % colors.length];
                        dataset.borderColor = dataset.borderColor || colors[i % colors.length];
                    });
                }
            }
            return originalInit.apply(this, arguments);
        };
    }

    // 4. Sidebar active state pulse
    const activeItem = document.querySelector('.sidebar-menu li.active');
    if (activeItem) {
        activeItem.style.position = 'relative';
        const pulse = document.createElement('div');
        pulse.style.cssText = 'position:absolute; left:0; top:10%; height:80%; width:4px; background:#1a237e; border-radius:0 4px 4px 0; shadow: 0 0 10px rgba(26,35,126,0.5);';
        activeItem.appendChild(pulse);
    }
});
