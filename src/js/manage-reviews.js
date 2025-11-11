// ============================================================================
// GESTION DES CATÉGORIES
// ============================================================================

document.addEventListener('DOMContentLoaded', () => {
  // Vérifier que l'objet wgmbrManage existe
  if (typeof wgmbrManage === 'undefined') {
    return;
  }

  // ============================================================================
  // SYNC REVIEWS FROM API
  // ============================================================================

  // Global function for syncing reviews from API (called from onclick)
  window.wgmbr_syncReviewsFromAPI = function() {
    const resultDiv = document.getElementById('sync-result');
    const button = event.target;
    const originalText = button.textContent;

    button.disabled = true;
    button.textContent = wgmbrManage.i18n.syncing;

    const formData = new FormData();
    formData.append('action', 'wgmbr_sync_reviews');
    formData.append('nonce', wgmbrManage.syncNonce);

    fetch(wgmbrManage.ajaxUrl, {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          button.textContent = wgmbrManage.i18n.syncComplete;
          // Reload the page after 2 seconds
          setTimeout(() => {
            window.location.reload();
          }, 2000);
        } else {
          resultDiv.innerHTML = '<div class="gmb-notice error"><p> ' + (data.data?.message || wgmbrManage.i18n.errorSyncing) + '</p></div>';
          button.disabled = false;
          button.textContent = originalText;
        }
      })
      .catch(error => {
        resultDiv.innerHTML = '<div class="gmb-notice error"><p>' + wgmbrManage.i18n.networkError + ' ' + error.message + '</p></div>';
        button.disabled = false;
        button.textContent = originalText;
      });
  };

  // ============================================================================
  // SAVE REVIEW (JOB + CATEGORIES)
  // ============================================================================

  document.addEventListener('click', (e) => {
    if (!e.target.matches('.gmb-save-review-btn')) return;

    const btn = e.target;
    const postId = btn.dataset.postId;
    const row = btn.closest('tr');
    const job = row.querySelector('.gmb-job-input').value;
    const categories = Array.from(row.querySelectorAll('input[name="category_ids[]"]:checked')).map(cb => cb.value);
    const original = btn.textContent;

    btn.disabled = true;

    const formData = new FormData();
    formData.append('action', 'wgmbr_save_review');
    formData.append('post_id', postId);
    formData.append('job', job);
    formData.append('nonce', wgmbrManage.saveReviewNonce);
    categories.forEach(id => formData.append('category_ids[]', id));

    fetch(wgmbrManage.ajaxUrl, {
      method: 'POST',
      body: formData
    })
      .then(r => r.json())
      .then(d => {
        btn.textContent = d.success ? wgmbrManage.i18n.updated : wgmbrManage.i18n.error;
        btn.className = d.success ? 'button button-small is-success' : 'button button-small is-error';
        setTimeout(() => {
          btn.textContent = original;
          btn.className = 'button button-small button-primary gmb-save-review-btn';
          btn.disabled = false;
        }, 2000);
      })
      .catch(err => {
        console.error('Erreur:', err);
        btn.textContent = wgmbrManage.i18n.error;
        btn.className = 'button button-small is-error';
        setTimeout(() => {
          btn.textContent = original;
          btn.className = 'button button-small button-primary gmb-save-review-btn';
          btn.disabled = false;
        }, 2000);
      });
  });

  // ============================================================================
  // CATEGORY MANAGEMENT
  // ============================================================================

  // Bouton de création de catégorie
  const createCategoryBtn = document.getElementById('gmb-create-category-btn');
  const categoryNameInput = document.getElementById('gmb-new-category-name');

  if (createCategoryBtn && categoryNameInput) {
    createCategoryBtn.addEventListener('click', () => {
      const categoryName = categoryNameInput.value.trim();

      if (!categoryName) {
        alert(wgmbrManage.i18n.enterCategoryName);
        return;
      }

      // Désactiver le bouton pendant la requête
      createCategoryBtn.disabled = true;
      createCategoryBtn.textContent = wgmbrManage.i18n.creating;

      // Créer la catégorie via AJAX
      fetch(wgmbrManage.ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'wgmbr_create_category',
          category_name: categoryName,
          nonce: wgmbrManage.nonce
        })
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Recharger la page pour afficher la nouvelle catégorie
            window.location.reload();
          } else {
            alert(
              wgmbrManage.i18n.error + ' ' +
              (data.data?.message || wgmbrManage.i18n.unknownError)
            );
            createCategoryBtn.disabled = false;
            createCategoryBtn.textContent = wgmbrManage.i18n.createCategory;
          }
        })
        .catch(error => {
          alert(wgmbrManage.i18n.networkError + ' ' + error.message);
          createCategoryBtn.disabled = false;
          createCategoryBtn.textContent = wgmbrManage.i18n.createCategory;
        });
    });

    // Permettre de créer avec la touche Entrée
    categoryNameInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        createCategoryBtn.click();
      }
    });
  }

  // Boutons de suppression de catégorie
  const deleteCategoryBtns = document.querySelectorAll('.gmb-delete-category-btn');

  deleteCategoryBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const categoryId = this.dataset.categoryId;

      if (!confirm(wgmbrManage.i18n.confirmDeleteCategory)) {
        return;
      }

      // Désactiver le bouton pendant la requête
      this.disabled = true;
      this.textContent = wgmbrManage.i18n.deleting;

      // Supprimer la catégorie via AJAX
      fetch(wgmbrManage.ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'wgmbr_delete_category',
          category_id: categoryId,
          nonce: wgmbrManage.nonce
        })
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Recharger la page pour mettre à jour la liste
            window.location.reload();
          } else {
            alert(
              wgmbrManage.i18n.error + ' ' +
              (data.data?.message || wgmbrManage.i18n.unknownError)
            );
            this.disabled = false;
            this.textContent = wgmbrManage.i18n.delete;
          }
        })
        .catch(error => {
          alert(wgmbrManage.i18n.networkError + ' ' + error.message);
          this.disabled = false;
          this.textContent = wgmbrManage.i18n.delete;
        });
    });
  });
});
