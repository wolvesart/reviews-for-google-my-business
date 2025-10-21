<?php
/**
 * Google My Business Reviews - Scripts de gestion des avis
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}
?>

<script>
// ============================================================================
// GESTION DES CATÉGORIES
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // Bouton de création de catégorie
    const createCategoryBtn = document.getElementById('gmb-create-category-btn');
    const categoryNameInput = document.getElementById('gmb-new-category-name');

    if (createCategoryBtn && categoryNameInput) {
        createCategoryBtn.addEventListener('click', function() {
            const categoryName = categoryNameInput.value.trim();

            if (!categoryName) {
                alert('Veuillez entrer un nom de catégorie');
                return;
            }

            // Désactiver le bouton pendant la requête
            createCategoryBtn.disabled = true;
            createCategoryBtn.textContent = 'Création...';

            // Créer la catégorie via AJAX
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'gmb_create_category',
                    category_name: categoryName,
                    nonce: '<?php echo wp_create_nonce('gmb_categories'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recharger la page pour afficher la nouvelle catégorie
                    window.location.reload();
                } else {
                    alert('Erreur : ' + (data.data?.message || 'Erreur inconnue'));
                    createCategoryBtn.disabled = false;
                    createCategoryBtn.textContent = 'Créer une catégorie';
                }
            })
            .catch(error => {
                alert('Erreur réseau : ' + error.message);
                createCategoryBtn.disabled = false;
                createCategoryBtn.textContent = 'Créer une catégorie';
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

            if (!confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ? Elle sera retirée de tous les avis qui l\'utilisent.')) {
                return;
            }

            // Désactiver le bouton pendant la requête
            this.disabled = true;
            this.textContent = 'Suppression...';

            // Supprimer la catégorie via AJAX
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'gmb_delete_category',
                    category_id: categoryId,
                    nonce: '<?php echo wp_create_nonce('gmb_categories'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recharger la page pour mettre à jour la liste
                    window.location.reload();
                } else {
                    alert('Erreur : ' + (data.data?.message || 'Erreur inconnue'));
                    this.disabled = false;
                    this.textContent = 'Supprimer';
                }
            })
            .catch(error => {
                alert('Erreur réseau : ' + error.message);
                this.disabled = false;
                this.textContent = 'Supprimer';
            });
        });
    });
});
</script>
