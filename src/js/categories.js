// ============================================================================
// GESTION DES CATÉGORIES
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // Vérifier que l'objet wgmbrManage existe
    if (typeof wgmbrManage === 'undefined') {
        return;
    }

    // Bouton de création de catégorie
    const createCategoryBtn = document.getElementById('gmb-create-category-btn');
    const categoryNameInput = document.getElementById('gmb-new-category-name');

    if (createCategoryBtn && categoryNameInput) {
        createCategoryBtn.addEventListener('click', function() {
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
                    'Content-Type': 'application/x-www-form-urlencoded',
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
                    alert(wgmbrManage.i18n.error + ' ' + (data.data?.message || wgmbrManage.i18n.unknownError));
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
        categoryNameInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                createCategoryBtn.click();
            }
        });
    }

    // Boutons de suppression de catégorie
    const deleteCategoryBtns = document.querySelectorAll('.gmb-delete-category-btn');

    deleteCategoryBtns.forEach(function(btn) {
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
                    'Content-Type': 'application/x-www-form-urlencoded',
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
                    alert(wgmbrManage.i18n.error + ' ' + (data.data?.message || wgmbrManage.i18n.unknownError));
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
