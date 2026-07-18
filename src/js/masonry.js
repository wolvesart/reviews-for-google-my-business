// Masonry layout script — "Show more" AJAX loading.
// The masonry grid itself is pure CSS (multi-column); this script only appends
// new cards and their modal templates on demand.
//
// The next batch is prefetched in the background: an admin-ajax request pays
// the full WordPress bootstrap cost, so we pay it while the visitor reads the
// first cards instead of when they click — the click then feels instant.

document.addEventListener('DOMContentLoaded', function() {
    const grid = document.querySelector('.gmb-reviews-masonry');
    const showMoreBtn = document.querySelector('.gmb-show-more-btn');

    if (!grid || !showMoreBtn) return;

    const showMoreWrapper = showMoreBtn.closest('.gmb-show-more-wrapper');
    const modalTemplates = document.getElementById('gmb-modal-templates');
    const originalLabel = showMoreBtn.textContent;

    let nextBatch = null; // prefetched data, ready to append
    let pending = null;   // in-flight prefetch promise

    function requestBatch() {
        // IDs of the reviews already displayed, so the server excludes them
        // (works for both "recent" and "random" orders, no duplicates)
        const shownIds = Array.from(grid.querySelectorAll('.gmb-masonry-item[data-post-id]'))
            .map(item => item.getAttribute('data-post-id'));

        const formData = new FormData();
        formData.append('action', 'wgmbr_load_more_reviews');
        formData.append('order', grid.dataset.order || 'recent');
        formData.append('limit', grid.dataset.limit || '');
        formData.append('category', grid.dataset.category || '');
        formData.append('category_filter', grid.dataset.categoryFilter || '0');
        shownIds.forEach(id => formData.append('exclude[]', id));

        return fetch(wgmbrFront.ajaxUrl, {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error((data.data && data.data.message) || 'Unknown error');
                }
                return data.data;
            });
    }

    function prefetch() {
        if (nextBatch || pending) return;

        pending = requestBatch()
            .then(data => { nextBatch = data; })
            .catch(() => { /* silent: the click falls back to a live request */ })
            .finally(() => { pending = null; });
    }

    function append(data) {
        if (data.html) {
            const previousCount = grid.children.length;
            grid.insertAdjacentHTML('beforeend', data.html);

            // Staggered entrance animation on the appended cards only
            Array.from(grid.children).slice(previousCount).forEach((item, i) => {
                item.style.animationDelay = (i * 60) + 'ms';
                item.classList.add('is-appearing');
            });
        }

        if (modalTemplates && data.modals) {
            modalTemplates.insertAdjacentHTML('beforeend', data.modals);
        }

        // Show "Read more" buttons on newly added truncated cards
        if (typeof window.wgmbrCheckTruncatedReviews === 'function') {
            window.wgmbrCheckTruncatedReviews();
        }

        if (data.has_more) {
            showMoreBtn.disabled = false;
            showMoreBtn.textContent = originalLabel;
            // Warm up the following batch right away
            prefetch();
        } else if (showMoreWrapper) {
            showMoreWrapper.remove();
        }
    }

    function fail() {
        showMoreBtn.disabled = false;
        showMoreBtn.textContent = (window.wgmbrFront && wgmbrFront.i18n.error) || originalLabel;

        setTimeout(() => {
            showMoreBtn.textContent = originalLabel;
        }, 3000);
    }

    showMoreBtn.addEventListener('click', function() {
        // Instant path: the batch is already prefetched
        if (nextBatch) {
            const data = nextBatch;
            nextBatch = null;
            append(data);
            return;
        }

        showMoreBtn.disabled = true;
        showMoreBtn.textContent = (window.wgmbrFront && wgmbrFront.i18n.loading) || 'Loading...';

        // If a prefetch is in flight, wait for it instead of doubling the request
        const source = pending
            ? pending.then(() => {
                if (nextBatch) {
                    const data = nextBatch;
                    nextBatch = null;
                    return data;
                }
                return requestBatch();
            })
            : requestBatch();

        source.then(append).catch(fail);
    });

    // Prefetch the next batch early — whichever comes first:
    // the reviews section approaching the viewport, or a short idle delay
    // (safety net: IntersectionObserver needs rendering to fire). prefetch()
    // is idempotent so both triggers can't double the request.
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function(entries) {
            if (entries.some(entry => entry.isIntersecting)) {
                observer.disconnect();
                prefetch();
            }
        }, { rootMargin: '1200px 0px' });

        observer.observe(showMoreWrapper || grid);
    }

    setTimeout(prefetch, 3500);
});
