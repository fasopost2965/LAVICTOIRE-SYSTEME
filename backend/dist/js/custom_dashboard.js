/**
 * E-Victoire Dashboard Interactions v2.0
 * Phases 2 & 3 : Graphiques dynamiques + Micro-interactions
 * Compatible API mobile Android - aucun endpoint modifie.
 */

(function () {
    'use strict';

    // =========================================================
    // PHASE 3 — PAGE FADE-IN
    // =========================================================
    const contentWrapper = document.querySelector('.content-wrapper');
    if (contentWrapper) {
        contentWrapper.style.opacity = '0';
        requestAnimationFrame(() => {
            contentWrapper.style.transition = 'opacity 0.4s ease-in-out';
            contentWrapper.style.opacity = '1';
        });
    }

    // =========================================================
    // PHASE 2 — CHART.JS THEME E-VICTOIRE
    // =========================================================
    const EV_COLORS = {
        primary: '#1a237e',
        primaryLight: '#3f51b5',
        accent: '#0097a7',
        accentLight: '#00bcd4',
        success: '#2e7d32',
        warning: '#f57f17',
        danger: '#c62828',
        palette: ['#1a237e', '#0097a7', '#3f51b5', '#00bcd4', '#2e7d32', '#f57f17', '#c62828', '#7b1fa2']
    };

    function applyChartTheme() {
        if (!window.Chart) return;

        // Global Chart.js defaults
        Chart.defaults.global = Chart.defaults.global || {};
        Chart.defaults.global.defaultFontFamily = "'Inter', 'Segoe UI', sans-serif";
        Chart.defaults.global.defaultFontSize = 12;
        Chart.defaults.global.defaultFontColor = '#64748b';

        if (Chart.defaults.global.animation) {
            Chart.defaults.global.animation.duration = 900;
            Chart.defaults.global.animation.easing = 'easeInOutQuart';
        } else {
            Chart.defaults.global.animation = { duration: 900, easing: 'easeInOutQuart' };
        }

        // Custom tooltip style
        Chart.defaults.global.tooltips = {
            backgroundColor: 'rgba(15, 23, 42, 0.92)',
            titleFontSize: 13,
            titleFontColor: '#ffffff',
            bodyFontColor: '#e2e8f0',
            bodyFontSize: 12,
            cornerRadius: 8,
            xPadding: 12,
            yPadding: 10,
            displayColors: true
        };

        // Intercept Chart constructor to apply our color palette
        const OriginalChart = window.Chart;
        window.Chart = function (ctx, config) {
            if (config && config.data && config.data.datasets) {
                config.data.datasets.forEach((dataset, i) => {
                    const color = EV_COLORS.palette[i % EV_COLORS.palette.length];
                    if (!dataset.backgroundColor) {
                        // For line charts, use transparent fill
                        if (config.type === 'line') {
                            dataset.backgroundColor = color + '20';
                            dataset.borderColor = dataset.borderColor || color;
                            dataset.borderWidth = dataset.borderWidth || 2;
                            dataset.pointBackgroundColor = color;
                            dataset.pointRadius = 4;
                            dataset.pointHoverRadius = 6;
                        } else {
                            dataset.backgroundColor = EV_COLORS.palette.slice(0, (config.data.labels || []).length);
                        }
                    }
                    if (!dataset.borderColor && config.type !== 'pie' && config.type !== 'doughnut') {
                        dataset.borderColor = color;
                    }
                });
            }
            return new OriginalChart(ctx, config);
        };
        // Preserve static methods
        Object.assign(window.Chart, OriginalChart);
        window.Chart.prototype = OriginalChart.prototype;
    }

    // =========================================================
    // PHASE 2 — CHART ANIMATION ON SCROLL (IntersectionObserver)
    // =========================================================
    function initChartScrollAnimation() {
        const charts = document.querySelectorAll('canvas, .chart-wrapper, .box.chart-box');
        if (!charts.length || !window.IntersectionObserver) return;

        charts.forEach(el => el.classList.add('ev-chart-animate'));

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('ev-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        charts.forEach(el => observer.observe(el));
    }

    // =========================================================
    // PHASE 3 — COUNT-UP ANIMATION FOR STAT NUMBERS
    // =========================================================
    function animateCounters() {
        const numbers = document.querySelectorAll('.info-box-number, .small-box .inner h3');
        numbers.forEach(el => {
            const raw = el.textContent.trim().replace(/[^0-9]/g, '');
            if (!raw || isNaN(raw) || raw.length > 6) return;
            const target = parseInt(raw, 10);
            const duration = 900;
            const step = Math.ceil(target / (duration / 16));
            let current = 0;
            const timer = setInterval(() => {
                current = Math.min(current + step, target);
                el.textContent = current.toLocaleString('fr-FR');
                if (current >= target) clearInterval(timer);
            }, 16);
        });
    }

    // =========================================================
    // PHASE 3 — SIDEBAR ACTIVE INDICATOR
    // =========================================================
    function initSidebarIndicator() {
        const activeItem = document.querySelector('.sidebar-menu > li.active');
        if (!activeItem) return;

        // Animated left border indicator
        const indicator = document.createElement('span');
        indicator.style.cssText = [
            'position:absolute',
            'left:0',
            'top:50%',
            'transform:translateY(-50%)',
            'width:4px',
            'height:60%',
            'background:linear-gradient(180deg,#1a237e,#3f51b5)',
            'border-radius:0 4px 4px 0',
            'box-shadow:2px 0 8px rgba(26,35,126,0.4)',
            'transition:height 0.3s ease'
        ].join(';');
        activeItem.style.position = 'relative';
        activeItem.appendChild(indicator);
    }

    // =========================================================
    // PHASE 3 — CARD HOVER 3D TILT EFFECT (subtle)
    // =========================================================
    function initCardTilt() {
        const cards = document.querySelectorAll('.info-box, .small-box');
        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width - 0.5) * 6;
                const y = ((e.clientY - rect.top) / rect.height - 0.5) * -6;
                card.style.transform = `translateY(-4px) rotateX(${y}deg) rotateY(${x}deg)`;
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
                card.style.transition = 'transform 0.4s ease';
            });
        });
    }

    // =========================================================
    // PHASE 3 — BUTTON RIPPLE
    // =========================================================
    function initRipple() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn');
            if (!btn) return;
            const ripple = document.createElement('span');
            const rect = btn.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.cssText = [
                `width:${size}px`,
                `height:${size}px`,
                `left:${e.clientX - rect.left - size / 2}px`,
                `top:${e.clientY - rect.top - size / 2}px`,
                'position:absolute',
                'background:rgba(255,255,255,0.35)',
                'border-radius:50%',
                'pointer-events:none',
                'transform:scale(0)',
                'animation:ev-ripple 0.5s ease-out forwards'
            ].join(';');
            if (!btn.style.position || btn.style.position === 'static') {
                btn.style.position = 'relative';
            }
            btn.style.overflow = 'hidden';
            btn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });

        // Inject ripple keyframe
        if (!document.getElementById('ev-ripple-style')) {
            const style = document.createElement('style');
            style.id = 'ev-ripple-style';
            style.textContent = '@keyframes ev-ripple{to{transform:scale(3);opacity:0}}';
            document.head.appendChild(style);
        }
    }

    // =========================================================
    // PHASE 3 — SMOOTH NOTIFICATION BADGE
    // =========================================================
    function initNotificationBadge() {
        const badges = document.querySelectorAll('.navbar .label, .navbar .badge');
        badges.forEach(badge => {
            badge.style.transition = 'transform 0.3s ease';
            badge.addEventListener('mouseenter', () => {
                badge.style.transform = 'scale(1.3)';
            });
            badge.addEventListener('mouseleave', () => {
                badge.style.transform = 'scale(1)';
            });
        });
    }

    // =========================================================
    // INIT — Run everything on DOMContentLoaded
    // =========================================================
    function init() {
        applyChartTheme();
        initChartScrollAnimation();
        animateCounters();
        initSidebarIndicator();
        initCardTilt();
        initRipple();
        initNotificationBadge();
        console.log('[E-Victoire] Dashboard v2.0 loaded - All phases active');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();

// LA VICTOIRE — Custom JS v1.0
document.addEventListener('DOMContentLoaded', function () {

  // Animation douce des cards au chargement
  var cards = document.querySelectorAll('.small-box, .info-box, .box');
  cards.forEach(function (card, i) {
    card.style.opacity = '0';
    card.style.transform = 'translateY(8px)';
    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    setTimeout(function () {
      card.style.opacity = '1';
      card.style.transform = 'translateY(0)';
    }, i * 40);
  });

  // Highlight lien actif sidebar selon URL
  var links = document.querySelectorAll('.main-sidebar a');
  links.forEach(function (link) {
    if (link.href === window.location.href) {
      link.parentElement.classList.add('active');
    }
  });

});

// LA VICTOIRE — Custom JS v1.0
document.addEventListener('DOMContentLoaded', function () {

  // Animation douce des cards au chargement
  var cards = document.querySelectorAll('.small-box, .info-box, .box');
  cards.forEach(function (card, i) {
    card.style.opacity = '0';
    card.style.transform = 'translateY(8px)';
    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    setTimeout(function () {
      card.style.opacity = '1';
      card.style.transform = 'translateY(0)';
    }, i * 40);
  });

  // Highlight lien actif sidebar selon URL
  var links = document.querySelectorAll('.main-sidebar a');
  links.forEach(function (link) {
    if (link.href === window.location.href) {
      link.parentElement.classList.add('active');
    }
  });

});

// LA VICTOIRE — Info-box couleurs par label v4.0
(function() {
  var colorMap = {
    'enseignants':        '#7C3AED',
    'teacher':            '#7C3AED',
    'admin':              '#475569',
    'super admin':        '#0F172A',
    'comptable':          '#0891B2',
    'librarian':          '#0891B2',
    'receptioniste':      '#16A34A',
    'réceptioniste':      '#16A34A',
    'directrice':         '#DC2626',
    'directeur':          '#DC2626',
    'élève':              '#2B52D4',
    'nombre d\'élèves':   '#2B52D4',
    'collecte':           '#16A34A',
    'dépenses':           '#D97706',
    'depenses':           '#D97706'
  };

  document.querySelectorAll('.info-box').forEach(function(box) {
    var labelEl = box.querySelector('.info-box-text');
    var iconEl  = box.querySelector('.info-box-icon');
    if (!labelEl || !iconEl) return;

    var label = labelEl.textContent.trim().toLowerCase();
    var color = null;

    Object.keys(colorMap).forEach(function(key) {
      if (label.indexOf(key) !== -1) color = colorMap[key];
    });

    if (color) {
      iconEl.style.background = color;
      iconEl.style.color = '#fff';
    }
  });
})();

// LA VICTOIRE — Info-box couleurs par label v5.0
(function() {
  function applyColors() {
    var colorMap = {
      'enseignants':        '#7C3AED',
      'teacher':            '#7C3AED',
      'admin':              '#475569',
      'super admin':        '#0F172A',
      'comptable':          '#0891B2',
      'librarian':          '#0891B2',
      'receptioniste':      '#16A34A',
      'réceptioniste':      '#16A34A',
      'directrice':         '#DC2626',
      'directeur':          '#DC2626',
      'élève':              '#2B52D4',
      'nombre d\'élèves':   '#2B52D4',
      'collecte':           '#16A34A',
      'dépenses':           '#D97706',
      'depenses':           '#D97706'
    };

    document.querySelectorAll('.info-box').forEach(function(box) {
      var labelEl = box.querySelector('.info-box-text');
      var iconEl  = box.querySelector('.info-box-icon');
      if (!labelEl || !iconEl) return;

      var label = labelEl.textContent.trim().toLowerCase();
      var color = null;

      Object.keys(colorMap).forEach(function(key) {
        if (label.indexOf(key) !== -1) color = colorMap[key];
      });

      if (color) {
        iconEl.style.setProperty('background', color, 'important');
        iconEl.style.setProperty('color', '#fff', 'important');
      }
    });
  }

  if (document.readyState === 'complete') {
    applyColors();
  } else {
    window.addEventListener('load', applyColors);
  }
  setTimeout(applyColors, 1000);
  setTimeout(applyColors, 3000);
})();
