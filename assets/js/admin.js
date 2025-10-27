/******/ (() => { // webpackBootstrap
/*!*************************!*\
  !*** ./src/js/admin.js ***!
  \*************************/
// ============================================================================
// GESTION DES TABS
// ============================================================================

document.addEventListener('DOMContentLoaded', function () {
  var tabButtons = document.querySelectorAll('.gmb-tab-button');
  var tabContents = document.querySelectorAll('.gmb-tab-content');

  // Function to activate a specific tab
  function activateTab(tabName) {
    // Remove active class from all buttons and contents
    tabButtons.forEach(function (btn) {
      return btn.classList.remove('active');
    });
    tabContents.forEach(function (content) {
      return content.classList.remove('active');
    });

    // Add active class to the target button
    var targetButton = document.querySelector("[data-tab=\"".concat(tabName, "\"]"));
    if (targetButton) {
      targetButton.classList.add('active');
    }

    // Show the corresponding content
    var targetContent = document.querySelector("[data-tab-content=\"".concat(tabName, "\"]"));
    if (targetContent) {
      targetContent.classList.add('active');
    }

    // Update URL hash
    window.location.hash = tabName;
  }

  // Check if there's a hash in the URL
  var activeTab = window.location.hash.substring(1); // Remove the #

  // Fallback to query parameter for backward compatibility
  if (!activeTab) {
    var urlParams = new URLSearchParams(window.location.search);
    activeTab = urlParams.get('tab');
  }

  // Activate the tab if found
  if (activeTab) {
    activateTab(activeTab);
  }

  // Handle tab button clicks
  tabButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      var targetTab = this.dataset.tab;
      activateTab(targetTab);
    });
  });

  // Handle hash changes (browser back/forward)
  window.addEventListener('hashchange', function () {
    var hash = window.location.hash.substring(1);
    if (hash) {
      activateTab(hash);
    }
  });
});

// ============================================================================
// FONCTIONS AJAX
// ============================================================================

window.refreshLocations = function () {
  var button = event.target;
  var originalText = button.textContent;
  button.disabled = true;
  button.textContent = wgmbrAdmin.i18n.loading;
  fetch(wgmbrAdmin.ajaxUrl + '?action=wgmbr_refresh_locations').then(function (response) {
    return response.json();
  }).then(function (data) {
    if (data.success) {
      window.location.href = wgmbrAdmin.settingsUrl + '&status=success&auto_fetch=1';
    } else {
      var _data$data;
      alert(wgmbrAdmin.i18n.errorFetchingLocations + ' ' + (((_data$data = data.data) === null || _data$data === void 0 ? void 0 : _data$data.message) || wgmbrAdmin.i18n.unknownError));
      button.disabled = false;
      button.textContent = originalText;
    }
  })["catch"](function (error) {
    alert(wgmbrAdmin.i18n.networkError + ' ' + error.message);
    button.disabled = false;
    button.textContent = originalText;
  });
};
window.clearGMBCache = function () {
  document.getElementById('gmb-test-result').innerHTML = '<p>' + wgmbrAdmin.i18n.clearingCache + '</p>';
  fetch(wgmbrAdmin.ajaxUrl + '?action=wgmbr_clear_cache').then(function (response) {
    return response.json();
  }).then(function (data) {
    if (data.success) {
      document.getElementById('gmb-test-result').innerHTML = '<div class="gmb-notice success"><p>' + wgmbrAdmin.i18n.cacheCleared + '</p></div>';
    } else {
      document.getElementById('gmb-test-result').innerHTML = '<div class="gmb-notice error"><p>' + wgmbrAdmin.i18n.errorClearingCache + '</p></div>';
    }
  });
};
window.testGMBConnection = function () {
  document.getElementById('gmb-test-result').innerHTML = '<p>' + wgmbrAdmin.i18n.loading + '</p>';
  fetch(wgmbrAdmin.ajaxUrl + '?action=wgmbr_test_connection').then(function (response) {
    return response.json();
  }).then(function (data) {
    if (data.success) {
      document.getElementById('gmb-test-result').innerHTML = '<div class="gmb-notice success"><p>' + wgmbrAdmin.i18n.connectionSuccessful + ' ' + data.data.count + ' ' + wgmbrAdmin.i18n.reviewsFetched + '</p></div>';
    } else {
      var errorMsg = data.data && data.data.message ? data.data.message : wgmbrAdmin.i18n.unknownError;
      var debugInfo = '';
      if (data.data && data.data.response) {
        debugInfo = '<pre style="background: #f0f0f1; padding: 10px; overflow: auto; margin-top: 10px;">' + JSON.stringify(data.data.response, null, 2) + '</pre>';
      }
      document.getElementById('gmb-test-result').innerHTML = '<div class="gmb-notice error"><p>' + wgmbrAdmin.i18n.error + ' ' + errorMsg + '</p>' + debugInfo + '</div>';
    }
  })["catch"](function (error) {
    document.getElementById('gmb-test-result').innerHTML = '<div class="gmb-notice error"><p>' + wgmbrAdmin.i18n.networkError + ' ' + error.message + '</p></div>';
  });
};
window.resetGMBCustomization = function (btn) {
  if (!confirm(wgmbrAdmin.i18n.confirmReset)) {
    return;
  }
  var originalText = btn.textContent;
  btn.disabled = true;
  btn.textContent = wgmbrAdmin.i18n.loading;
  fetch(wgmbrAdmin.ajaxUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: 'action=wgmbr_reset_customization'
  }).then(function (response) {
    if (!response.ok) {
      throw new Error('HTTP error ' + response.status);
    }
    return response.json();
  }).then(function (data) {
    if (data.success) {
      // Force page reload to update the form with default values
      window.location.reload();
    } else {
      var _data$data2;
      alert(wgmbrAdmin.i18n.errorResetting + ' ' + (((_data$data2 = data.data) === null || _data$data2 === void 0 ? void 0 : _data$data2.message) || wgmbrAdmin.i18n.unknownError));
      btn.disabled = false;
      btn.textContent = originalText;
    }
  })["catch"](function (error) {
    alert(wgmbrAdmin.i18n.networkError + ' ' + error.message);
    btn.disabled = false;
    btn.textContent = originalText;
  });
};

// ============================================================================
// AJAX CUSTOMIZATION FORM
// ============================================================================

document.addEventListener('DOMContentLoaded', function () {
  var customizationForm = document.getElementById('gmb-customization-form');
  if (customizationForm) {
    customizationForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var submitBtn = customizationForm.querySelector('button[type="submit"]');
      var originalText = submitBtn.textContent;

      // Disable submit button
      submitBtn.disabled = true;
      submitBtn.textContent = wgmbrAdmin.i18n.loading || 'Saving...';

      // Prepare form data
      var formData = new FormData(customizationForm);

      // Send AJAX request
      fetch(wgmbrAdmin.ajaxUrl, {
        method: 'POST',
        body: formData
      }).then(function (response) {
        return response.json();
      }).then(function (data) {
        if (data.success) {
          var _wgmbrAdmin$i18n;
          submitBtn.textContent = ((_wgmbrAdmin$i18n = wgmbrAdmin.i18n) === null || _wgmbrAdmin$i18n === void 0 ? void 0 : _wgmbrAdmin$i18n.saved) || '✓ Saved';
          submitBtn.classList.add('is-success');

          // Reset button state after 2 seconds
          setTimeout(function () {
            submitBtn.textContent = originalText;
            submitBtn.classList.remove('is-success');
            submitBtn.disabled = false;
          }, 2000);
        } else {
          var _wgmbrAdmin$i18n2;
          submitBtn.textContent = ((_wgmbrAdmin$i18n2 = wgmbrAdmin.i18n) === null || _wgmbrAdmin$i18n2 === void 0 ? void 0 : _wgmbrAdmin$i18n2.errorSaving) || 'Error';
          submitBtn.classList.add('is-error');
          setTimeout(function () {
            submitBtn.textContent = originalText;
            submitBtn.classList.remove('is-error');
            submitBtn.disabled = false;
          }, 3000);
        }
      })["catch"](function (error) {
        var _wgmbrAdmin$i18n3;
        submitBtn.textContent = ((_wgmbrAdmin$i18n3 = wgmbrAdmin.i18n) === null || _wgmbrAdmin$i18n3 === void 0 ? void 0 : _wgmbrAdmin$i18n3.errorSaving) || 'Error';
        submitBtn.classList.add('is-error');
        setTimeout(function () {
          submitBtn.textContent = originalText;
          submitBtn.classList.remove('is-error');
          submitBtn.disabled = false;
        }, 3000);
      });
    });
  }
});

// ============================================================================
// COLOR PICKER WITH HEX INPUT SYNC (All fields)
// ============================================================================

document.addEventListener('DOMContentLoaded', function () {
  // List of all color fields
  var colorFields = ['wgmbr_color_card_bg', 'wgmbr_color_star', 'wgmbr_color_text_primary', 'wgmbr_color_accent', 'wgmbr_color_text_resume'];

  // Initialize sync for each color field
  colorFields.forEach(function (fieldName) {
    var colorPicker = document.getElementById(fieldName + '_picker');
    var hexInput = document.getElementById(fieldName + '_hex');
    var hiddenInput = document.getElementById(fieldName);
    if (!colorPicker || !hexInput || !hiddenInput) return;

    // Sync color picker → hex input
    colorPicker.addEventListener('input', function () {
      var color = this.value.toUpperCase();
      hexInput.value = color;
      hiddenInput.value = color;
    });

    // Sync hex input → color picker
    hexInput.addEventListener('input', function () {
      var value = this.value.trim().toUpperCase();

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
    hexInput.addEventListener('blur', function () {
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

window.wgmbrCopyShortcode = function (btn) {
  // Récupérer le texte du shortcode
  var shortcode = document.getElementById("gmb-shortcode");
  if (!shortcode) {
    console.error('Shortcode element not found');
    return;
  }

  // Copier dans le presse-papier
  navigator.clipboard.writeText(shortcode.textContent).then(function () {
    var _wgmbrAdmin$i18n4;
    // Feedback visuel dans le bouton
    var originalHTML = btn.innerHTML;
    btn.innerHTML = '<span class="dashicons dashicons-yes"></span> ' + (((_wgmbrAdmin$i18n4 = wgmbrAdmin.i18n) === null || _wgmbrAdmin$i18n4 === void 0 ? void 0 : _wgmbrAdmin$i18n4.copied) || 'Copied!');
    btn.style.color = '#46b450';

    // Restaurer le bouton après 2 secondes
    setTimeout(function () {
      btn.innerHTML = originalHTML;
      btn.style.color = '';
    }, 2000);
  })["catch"](function (err) {
    console.error('Failed to copy shortcode:', err);
    alert('Failed to copy shortcode');
  });
};

// ============================================================================
// SHORTCODE GENERATOR
// ============================================================================

window.wgmbrGenerateShortcode = function () {
  var limit = document.getElementById('gmb-gen-limit').value;
  var categoriesContainer = document.getElementById('gmb-gen-categories');
  var showSummary = document.getElementById('gmb-gen-summary').checked;
  var outputElement = document.getElementById('gmb-generated-shortcode');
  if (!outputElement) return;

  // Récupérer les catégories sélectionnées depuis les checkboxes
  var selectedCategories = Array.from(categoriesContainer.querySelectorAll('input[name="gen_category_slugs[]"]:checked')).map(function (checkbox) {
    return checkbox.value;
  });

  // Construire le shortcode
  var shortcode = '[gmb_reviews';

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
};
window.wgmbrCopyGeneratedShortcode = function (btn) {
  var shortcode = document.getElementById("gmb-generated-shortcode");
  if (!shortcode) {
    console.error('Generated shortcode element not found');
    return;
  }

  // Copier dans le presse-papier
  navigator.clipboard.writeText(shortcode.textContent).then(function () {
    var _wgmbrAdmin$i18n5;
    // Feedback visuel dans le bouton
    var originalHTML = btn.innerHTML;
    btn.innerHTML = '<span class="dashicons dashicons-yes"></span> ' + (((_wgmbrAdmin$i18n5 = wgmbrAdmin.i18n) === null || _wgmbrAdmin$i18n5 === void 0 ? void 0 : _wgmbrAdmin$i18n5.copied) || 'Copied!');
    btn.classList.add('is-success');

    // Restaurer le bouton après 2 secondes
    setTimeout(function () {
      btn.innerHTML = originalHTML;
      btn.classList.remove('is-success');
    }, 2000);
  })["catch"](function (err) {
    console.error('Failed to copy shortcode:', err);
    alert('Failed to copy shortcode');
  });
};
/******/ })()
;