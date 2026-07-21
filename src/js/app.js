// Common frontend script: review modal + "Read more" detection.
// Layout-specific behavior lives in slider.js (Swiper) and masonry.js (AJAX
// "Show more"), loaded conditionally depending on the active layout.

document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality for GMB Reviews
    initGMBReviewModal();

    // Check which reviews need "Read more" button
    checkTruncatedReviews();

    // The first pass runs before the cards have their final width (Swiper sizes
    // its slides after init) and before webfonts swap in: both change how many
    // lines the text wraps onto, so re-measure once the layout has settled.
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(checkTruncatedReviews);
    }
    window.addEventListener('load', checkTruncatedReviews);

    // Card width changes with the viewport (Swiper breakpoints, masonry columns)
    let resizeTimer = null;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(checkTruncatedReviews, 150);
    });
});

// Function to initialize review modal
function initGMBReviewModal() {
    const modal = document.getElementById('gmb-review-modal');
    const modalBody = modal ? modal.querySelector('.gmb-modal-body') : null;
    const closeBtn = modal ? modal.querySelector('.gmb-modal-close') : null;
    const overlay = modal ? modal.querySelector('.gmb-modal-overlay') : null;
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

    // Event delegation for "Read more" buttons, so cards appended later
    // (masonry "Show more") work without rebinding
    document.addEventListener('click', function(e) {
        const button = e.target.closest('.gmb-read-more-btn');
        if (!button) return;

        e.preventDefault();
        const reviewIndex = parseInt(button.getAttribute('data-review-index'));
        openModal(reviewIndex);
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
        // By comparing scroll height with visible height (1px tolerance:
        // sub-pixel line heights can make both differ without any clamping)
        const isTruncated = paragraph.scrollHeight - paragraph.clientHeight > 1;

        // Toggle, not just add: a card truncated at one breakpoint may fit at
        // another, and this function runs again on every resize
        button.classList.toggle('is-visible', isTruncated);
    });
}

// Re-run truncation detection after new cards are appended (used by masonry.js)
window.wgmbrCheckTruncatedReviews = checkTruncatedReviews;
