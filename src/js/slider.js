// Slider layout script — bundles Swiper.
// Loaded only when the "Slider" layout is active (see wgmbr_enqueue_frontend_scripts).

// Import Swiper and modules (using require for compatibility)
const Swiper = require('swiper').default;
const { Navigation, Pagination } = require('swiper/modules');

// Initialize Swiper for GMB Reviews
document.addEventListener('DOMContentLoaded', function() {
    const reviewsSwiper = document.querySelector('.gmb-reviews-swiper');

    if (!reviewsSwiper) return;

    // Count number of slides
    const slides = reviewsSwiper.querySelectorAll('.swiper-slide');
    const slideCount = slides.length;

    // 1 slide: disable slider completely
    if (slideCount === 1) {
        const wrapper = reviewsSwiper.closest('.gmb-reviews-swiper-wrapper');
        if (wrapper) {
            wrapper.classList.add('gmb-static-layout');
            wrapper.setAttribute('data-slide-count', slideCount);
        }

        // Hide navigation elements
        const navButtons = document.querySelectorAll('.gmb-swiper-button-prev, .gmb-swiper-button-next');
        const pagination = document.querySelector('.gmb-swiper-pagination');
        navButtons.forEach(btn => btn.style.display = 'none');
        if (pagination) pagination.style.display = 'none';

        return;
    }

    // Add slide count attribute for CSS styling
    const wrapper = reviewsSwiper.closest('.gmb-reviews-swiper-wrapper');
    if (wrapper) {
        wrapper.setAttribute('data-slide-count', slideCount);
    }

    // 2 slides: always show 1 at a time (for loop to work)
    // 3+ slides: responsive (1 -> 2 -> 3)
    const breakpointsConfig = slideCount === 2
        ? {
            640: { slidesPerView: 1, spaceBetween: 24, slidesPerGroup: 1 },
            768: { slidesPerView: 1, spaceBetween: 24, slidesPerGroup: 1 },
            1024: { slidesPerView: 1, spaceBetween: 32, slidesPerGroup: 1 }
        }
        : {
            640: { slidesPerView: 1, spaceBetween: 24, slidesPerGroup: 1 },
            768: { slidesPerView: 2, spaceBetween: 24, slidesPerGroup: 1 },
            1024: { slidesPerView: 3, spaceBetween: 32, slidesPerGroup: 1 }
        };

    new Swiper('.gmb-reviews-swiper', {
        modules: [Navigation, Pagination],

        // Slides per view
        slidesPerView: 1,
        spaceBetween: 24,
        slidesPerGroup: 1,

        // Responsive breakpoints
        breakpoints: breakpointsConfig,

        // Navigation arrows
        navigation: {
            nextEl: '.gmb-swiper-button-next',
            prevEl: '.gmb-swiper-button-prev',
        },

        // Pagination
        pagination: {
            el: '.gmb-swiper-pagination',
            clickable: true,
            dynamicBullets: true,
            type: 'bullets',
        },

        on: {
            // Swiper sizes the dynamic-bullets container for
            // dynamicMainBullets + 4 (= 5) bullets and slides the strip to
            // center the active one: with fewer bullets this leaves the
            // strip off-center and clips edge bullets in half. In that case
            // show all bullets, centered, keeping the dynamic size classes.
            paginationUpdate(swiper) {
                const bullets = swiper.pagination ? swiper.pagination.bullets : null;
                if (!bullets || bullets.length === 0 || bullets.length >= 5) {
                    return; // native dynamic layout is correct from 5 bullets up
                }

                const style = window.getComputedStyle(bullets[0]);
                const bulletSize = bullets[0].offsetWidth
                    + parseFloat(style.marginLeft)
                    + parseFloat(style.marginRight);

                const paginationEls = Array.isArray(swiper.pagination.el)
                    ? swiper.pagination.el
                    : [swiper.pagination.el];
                paginationEls.forEach(el => {
                    el.style.width = (bulletSize * bullets.length) + 'px';
                });
                bullets.forEach(bullet => {
                    bullet.style.left = '0px';
                });
            },
        },

        // Loop for seamless cycling
        loop: false,

        // Lock navigation/pagination (swiper-button-lock / swiper-pagination-lock)
        // when all slides fit in the view, e.g. 3 reviews at 3 slidesPerView
        watchOverflow: true,

        // Autoplay disabled
        autoplay: false,

        // Improve touch performance on mobile
        touchRatio: 1,
        touchAngle: 45,
        simulateTouch: true,
        shortSwipes: true,
        longSwipesRatio: 0.5,
        longSwipesMs: 300,

        // Hardware acceleration
        speed: 300,
        observer: true,
        observeParents: true,
    });
});
