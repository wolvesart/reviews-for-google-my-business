// Import Swiper and modules (using require for compatibility)
const Swiper = require('swiper').default;
const { Navigation, Pagination, Autoplay } = require('swiper/modules');

// Initialize Swiper for GMB Reviews
document.addEventListener('DOMContentLoaded', function() {
    const reviewsSwiper = document.querySelector('.gmb-reviews-swiper');

    if (reviewsSwiper) {
        // Count number of slides
        const slides = reviewsSwiper.querySelectorAll('.swiper-slide');
        const slideCount = slides.length;

        // Only enable loop if there are enough slides
        const shouldLoop = slideCount > 3;

        new Swiper('.gmb-reviews-swiper', {
            modules: [Navigation, Pagination, Autoplay],

            // Slides per view
            slidesPerView: 1,
            spaceBetween: 24,
            centeredSlides: false,
            slidesPerGroup: 1,

            // Responsive breakpoints
            breakpoints: {
                640: {
                    slidesPerView: 1,
                    spaceBetween: 24,
                    slidesPerGroup: 1
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 24,
                    slidesPerGroup: 1
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 32,
                    slidesPerGroup: 1
                }
            },

            // Navigation arrows
            navigation: {
                nextEl: '.gmb-swiper-button-next',
                prevEl: '.gmb-swiper-button-prev',
            },

            // Pagination
            pagination: {
                el: '.gmb-swiper-pagination',
                clickable: true,
                dynamicBullets: false,
                type: 'bullets',
            },

            // Autoplay (optional)
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },

            // Loop (only if enough slides)
            loop: shouldLoop,

            // Prevent issues with pagination in loop mode
            loopAdditionalSlides: shouldLoop ? 1 : 0,
        });
    }

    // Modal functionality for GMB Reviews
    initGMBReviewModal();

    // Check which reviews need "Read more" button
    checkTruncatedReviews();
});

// Function to initialize review modal
function initGMBReviewModal() {
    const modal = document.getElementById('gmb-review-modal');
    const modalBody = modal ? modal.querySelector('.gmb-modal-body') : null;
    const closeBtn = modal ? modal.querySelector('.gmb-modal-close') : null;
    const overlay = modal ? modal.querySelector('.gmb-modal-overlay') : null;
    const readMoreButtons = document.querySelectorAll('.gmb-read-more-btn');
    const modalTemplates = document.getElementById('gmb-modal-templates');

    if (!modal || !modalBody || !modalTemplates) return;

    // Function to open modal
    function openModal(reviewIndex) {
        // Get the corresponding pre-rendered template
        const template = modalTemplates.querySelector(`[data-review-index="${reviewIndex}"]`);
        if (!template) return;

        // Clone template content into modal
        modalBody.innerHTML = template.innerHTML;

        // Show modal
        modal.classList.add('is-active');
        document.body.style.overflow = 'hidden';
    }

    // Function to close modal
    function closeModal() {
        modal.classList.remove('is-active');
        document.body.style.overflow = '';
    }

    // Event listeners for "Read more" buttons
    readMoreButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const reviewIndex = parseInt(this.getAttribute('data-review-index'));
            openModal(reviewIndex);
        });
    });

    // Event listener for close button
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    // Close modal by clicking overlay
    if (overlay) {
        overlay.addEventListener('click', closeModal);
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('is-active')) {
            closeModal();
        }
    });
}

// Function to check if text is truncated and show button if needed
function checkTruncatedReviews() {
    const reviewContents = document.querySelectorAll('.gmb-review-content');

    reviewContents.forEach(content => {
        const paragraph = content.querySelector('p');
        const button = content.querySelector('.gmb-read-more-btn');

        if (!paragraph || !button) return;

        // Check if text is truncated
        // By comparing scroll height with visible height
        const isTruncated = paragraph.scrollHeight > paragraph.clientHeight;

        if (isTruncated) {
            button.classList.add('is-visible');
        }
    });
}