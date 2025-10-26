// ============================================================================
// GESTION DES TABS
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.gmb-tab-button');
    const tabContents = document.querySelectorAll('.gmb-tab-content');

    // Function to activate a specific tab
    function activateTab(tabName) {
        // Remove active class from all buttons and contents
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));

        // Add active class to the target button
        const targetButton = document.querySelector(`[data-tab="${tabName}"]`);
        if (targetButton) {
            targetButton.classList.add('active');
        }

        // Show the corresponding content
        const targetContent = document.querySelector(`[data-tab-content="${tabName}"]`);
        if (targetContent) {
            targetContent.classList.add('active');
        }

        // Update URL hash
        window.location.hash = tabName;
    }

    // Check if there's a hash in the URL
    let activeTab = window.location.hash.substring(1); // Remove the #

    // Fallback to query parameter for backward compatibility
    if (!activeTab) {
        const urlParams = new URLSearchParams(window.location.search);
        activeTab = urlParams.get('tab');
    }

    // Activate the tab if found
    if (activeTab) {
        activateTab(activeTab);
    }

    // Handle tab button clicks
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            activateTab(targetTab);
        });
    });

    // Handle hash changes (browser back/forward)
    window.addEventListener('hashchange', function() {
        const hash = window.location.hash.substring(1);
        if (hash) {
            activateTab(hash);
        }
    });
});

// ============================================================================
// FONCTIONS AJAX
// ============================================================================

window.refreshLocations = function() {
    const button = event.target;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = wgmbrAdmin.i18n.loading;

    fetch(wgmbrAdmin.ajaxUrl + '?action=wgmbr_refresh_locations')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = wgmbrAdmin.settingsUrl + '&status=success&auto_fetch=1';
            } else {
                alert(wgmbrAdmin.i18n.errorFetchingLocations + ' ' + (data.data?.message || wgmbrAdmin.i18n.unknownError));
                button.disabled = false;
                button.textContent = originalText;
            }
        })
        .catch(error => {
            alert(wgmbrAdmin.i18n.networkError + ' ' + error.message);
            button.disabled = false;
            button.textContent = originalText;
        });
}

window.clearGMBCache = function() {
    document.getElementById('gmb-test-result').innerHTML = '<p>' + wgmbrAdmin.i18n.clearingCache + '</p>';

    fetch(wgmbrAdmin.ajaxUrl + '?action=wgmbr_clear_cache')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('gmb-test-result').innerHTML =
                    '<div class="gmb-notice success"><p>' + wgmbrAdmin.i18n.cacheCleared + '</p></div>';
            } else {
                document.getElementById('gmb-test-result').innerHTML =
                    '<div class="gmb-notice error"><p>' + wgmbrAdmin.i18n.errorClearingCache + '</p></div>';
            }
        });
}

window.testGMBConnection = function() {
    document.getElementById('gmb-test-result').innerHTML = '<p>' + wgmbrAdmin.i18n.loading + '</p>';

    fetch(wgmbrAdmin.ajaxUrl + '?action=wgmbr_test_connection')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('gmb-test-result').innerHTML =
                    '<div class="gmb-notice success"><p>' + wgmbrAdmin.i18n.connectionSuccessful + ' ' +
                    data.data.count + ' ' + wgmbrAdmin.i18n.reviewsFetched + '</p></div>';
            } else {
                let errorMsg = data.data && data.data.message ? data.data.message : wgmbrAdmin.i18n.unknownError;
                let debugInfo = '';

                if (data.data && data.data.response) {
                    debugInfo = '<pre style="background: #f0f0f1; padding: 10px; overflow: auto; margin-top: 10px;">' +
                               JSON.stringify(data.data.response, null, 2) + '</pre>';
                }

                document.getElementById('gmb-test-result').innerHTML =
                    '<div class="gmb-notice error"><p>' + wgmbrAdmin.i18n.error + ' ' + errorMsg + '</p>' + debugInfo + '</div>';
            }
        })
        .catch(error => {
            document.getElementById('gmb-test-result').innerHTML =
                '<div class="gmb-notice error"><p>' + wgmbrAdmin.i18n.networkError + ' ' + error.message + '</p></div>';
        });
}

window.resetGMBCustomization = function() {
    if (!confirm(wgmbrAdmin.i18n.confirmReset)) {
        return;
    }

    fetch(wgmbrAdmin.ajaxUrl + '?action=wgmbr_reset_customization')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = wgmbrAdmin.settingsUrl + '#customization';
            } else {
                alert(wgmbrAdmin.i18n.errorResetting + ' ' + (data.data?.message || wgmbrAdmin.i18n.unknownError));
            }
        })
        .catch(error => {
            alert(wgmbrAdmin.i18n.networkError + ' ' + error.message);
        });
}

// ============================================================================
// COLOR PICKER WITH HEX INPUT SYNC (All fields)
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // List of all color fields
    const colorFields = [
        'wgmbr_card_bg_color',
        'wgmbr_star_color',
        'wgmbr_text_color',
        'wgmbr_text_color_name',
        'gmb-accent-color'
    ];

    // Initialize sync for each color field
    colorFields.forEach(function(fieldName) {
        const colorPicker = document.getElementById(fieldName + '_picker');
        const hexInput = document.getElementById(fieldName + '_hex');
        const hiddenInput = document.getElementById(fieldName);

        if (!colorPicker || !hexInput || !hiddenInput) return;

        // Sync color picker → hex input
        colorPicker.addEventListener('input', function() {
            const color = this.value.toUpperCase();
            hexInput.value = color;
            hiddenInput.value = color;
        });

        // Sync hex input → color picker
        hexInput.addEventListener('input', function() {
            let value = this.value.trim().toUpperCase();

            // Auto-add # if missing
            if (value && !value.startsWith('#')) {
                value = '#' + value;
                this.value = value;
            }

            // Validate hex format
            if (/^#[0-9A-F]{6}$/i.test(value)) {
                colorPicker.value = value;
                hiddenInput.value = value;
                this.style.borderColor = '';
            } else if (value.length >= 7) {
                this.style.borderColor = '#dc3232';
            }
        });

        // Clean up on blur
        hexInput.addEventListener('blur', function() {
            if (!/^#[0-9A-F]{6}$/i.test(this.value)) {
                this.value = colorPicker.value;
                this.style.borderColor = '';
            }
        });
    });
});

// ============================================================================
// OTHERS
// ============================================================================

window.wgmbrCopyShortcode = function(btn) {
    // Récupérer le texte du shortcode
    const shortcode = document.getElementById("gmb-shortcode");

    if (!shortcode) {
        console.error('Shortcode element not found');
        return;
    }

    // Copier dans le presse-papier
    navigator.clipboard.writeText(shortcode.textContent)
        .then(() => {
            // Feedback visuel dans le bouton
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<span class="dashicons dashicons-yes"></span> ' + (wgmbrAdmin.i18n?.copied || 'Copied!');
            btn.style.color = '#46b450';

            // Restaurer le bouton après 2 secondes
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.style.color = '';
            }, 2000);
        })
        .catch(err => {
            console.error('Failed to copy shortcode:', err);
            alert('Failed to copy shortcode');
        });
}

// ============================================================================
// SHORTCODE GENERATOR
// ============================================================================

window.wgmbrGenerateShortcode = function() {
    const limit = document.getElementById('gmb-gen-limit').value;
    const categoriesSelect = document.getElementById('gmb-gen-categories');
    const showSummary = document.getElementById('gmb-gen-summary').checked;
    const outputElement = document.getElementById('gmb-generated-shortcode');

    if (!outputElement) return;

    // Récupérer les catégories sélectionnées
    const selectedCategories = Array.from(categoriesSelect.selectedOptions)
        .map(option => option.value)
        .filter(value => value !== ''); // Exclure l'option "All categories"

    // Construire le shortcode
    let shortcode = '[gmb_reviews';

    // Ajouter le paramètre limit seulement s'il est différent de la valeur par défaut
    if (limit && limit !== '50') {
        shortcode += ' limit="' + limit + '"';
    }

    // Ajouter les catégories
    if (selectedCategories.length > 0) {
        shortcode += ' category="' + selectedCategories.join(',') + '"';
    }

    // Ajouter show_summary seulement si false
    if (!showSummary) {
        shortcode += ' show_summary="false"';
    }

    shortcode += ']';

    // Mettre à jour l'affichage
    outputElement.textContent = shortcode;
}

window.wgmbrCopyGeneratedShortcode = function(btn) {
    const shortcode = document.getElementById("gmb-generated-shortcode");

    if (!shortcode) {
        console.error('Generated shortcode element not found');
        return;
    }

    // Copier dans le presse-papier
    navigator.clipboard.writeText(shortcode.textContent)
        .then(() => {
            // Feedback visuel dans le bouton
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<span class="dashicons dashicons-yes"></span> ' + (wgmbrAdmin.i18n?.copied || 'Copied!');
            btn.classList.add('is-success');

            // Restaurer le bouton après 2 secondes
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('is-success');
            }, 2000);
        })
        .catch(err => {
            console.error('Failed to copy shortcode:', err);
            alert('Failed to copy shortcode');
        });
}