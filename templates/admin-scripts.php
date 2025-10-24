<?php
/**
 * Google My Business Reviews - Scripts admin
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}
?>

<script>
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

function refreshLocations() {
    // Afficher un loader
    const button = event.target;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Chargement...';

    fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=wgmbr_refresh_locations')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recharger la page pour afficher les nouvelles locations
                window.location.href = '<?php echo admin_url('admin.php?page=gmb-settings&status=success&auto_fetch=1'); ?>';
            } else {
                alert('Erreur lors de la récupération des locations: ' + (data.data?.message || 'Erreur inconnue'));
                button.disabled = false;
                button.textContent = originalText;
            }
        })
        .catch(error => {
            alert('Erreur réseau: ' + error.message);
            button.disabled = false;
            button.textContent = originalText;
        });
}

function clearGMBCache() {
    document.getElementById('gmb-test-result').innerHTML = '<p>Suppression du cache...</p>';

    fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=wgmbr_clear_cache')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('gmb-test-result').innerHTML =
                    '<div class="notice notice-success"><p>✓ Cache vidé avec succès !</p></div>';
            } else {
                document.getElementById('gmb-test-result').innerHTML =
                    '<div class="notice notice-error"><p>✗ Erreur lors de la suppression du cache</p></div>';
            }
        });
}

function testGMBConnection() {
    document.getElementById('gmb-test-result').innerHTML = '<p>Chargement...</p>';

    fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=wgmbr_test_connection')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('gmb-test-result').innerHTML =
                    '<div class="notice notice-success"><p>✓ Connexion réussie ! ' +
                    data.data.count + ' avis récupérés.</p></div>';
            } else {
                let errorMsg = data.data && data.data.message ? data.data.message : 'Erreur inconnue';
                let debugInfo = '';

                if (data.data && data.data.response) {
                    debugInfo = '<pre style="background: #f0f0f1; padding: 10px; overflow: auto; margin-top: 10px;">' +
                               JSON.stringify(data.data.response, null, 2) + '</pre>';
                }

                document.getElementById('gmb-test-result').innerHTML =
                    '<div class="notice notice-error"><p>✗ Erreur: ' + errorMsg + '</p>' + debugInfo + '</div>';
            }
        })
        .catch(error => {
            document.getElementById('gmb-test-result').innerHTML =
                '<div class="notice notice-error"><p>✗ Erreur réseau: ' + error.message + '</p></div>';
        });
}

function resetGMBCustomization() {
    if (!confirm('Êtes-vous sûr de vouloir réinitialiser la personnalisation aux valeurs par défaut ?')) {
        return;
    }

    fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=wgmbr_reset_customization')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recharger la page sur l'onglet personnalisation
                window.location.href = '<?php echo admin_url('admin.php?page=gmb-settings'); ?>#customization';
            } else {
                alert('Erreur lors de la réinitialisation: ' + (data.data?.message || 'Erreur inconnue'));
            }
        })
        .catch(error => {
            alert('Erreur réseau: ' + error.message);
        });
}

// ============================================================================
// TOGGLE SUMMARY VISIBILITY - SHOW/HIDE COLOR FIELD
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    const showSummaryToggle = document.getElementById('wgmbr_show_summary');
    const resumeTextColorRow = document.getElementById('wgmbr_resume_text_color_row');

    if (showSummaryToggle && resumeTextColorRow) {
        // Function to toggle visibility
        function toggleResumeColorField() {
            if (showSummaryToggle.checked) {
                resumeTextColorRow.style.display = '';
            } else {
                resumeTextColorRow.style.display = 'none';
            }
        }

        // Set initial state
        toggleResumeColorField();

        // Listen for changes
        showSummaryToggle.addEventListener('change', toggleResumeColorField);
    }
});

// ============================================================================
// COLOR PICKER WITH HEX INPUT SYNC (All fields)
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // List of all color fields
    const colorFields = [
        'wgmbr_resume_text_color',
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
</script>
