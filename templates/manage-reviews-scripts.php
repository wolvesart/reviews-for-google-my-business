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
                alert('<?php _e('Please enter a category name', 'wolves-avis-google'); ?>');
                return;
            }

            // Désactiver le bouton pendant la requête
            createCategoryBtn.disabled = true;
            createCategoryBtn.textContent = '<?php _e('Creating...', 'wolves-avis-google'); ?>';

            // Créer la catégorie via AJAX
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'wgmbr_create_category',
                    category_name: categoryName,
                    nonce: '<?php echo wp_create_nonce('wgmbr_categories'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recharger la page pour afficher la nouvelle catégorie
                    window.location.reload();
                } else {
                    alert('<?php _e('Error:', 'wolves-avis-google'); ?> ' + (data.data?.message || '<?php _e('Unknown error', 'wolves-avis-google'); ?>'));
                    createCategoryBtn.disabled = false;
                    createCategoryBtn.textContent = '<?php _e('Create category', 'wolves-avis-google'); ?>';
                }
            })
            .catch(error => {
                alert('<?php _e('Network error:', 'wolves-avis-google'); ?> ' + error.message);
                createCategoryBtn.disabled = false;
                createCategoryBtn.textContent = '<?php _e('Create category', 'wolves-avis-google'); ?>';
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

            if (!confirm('<?php _e('Are you sure you want to delete this category? It will be removed from all reviews that use it.', 'wolves-avis-google'); ?>')) {
                return;
            }

            // Désactiver le bouton pendant la requête
            this.disabled = true;
            this.textContent = '<?php _e('Deleting...', 'wolves-avis-google'); ?>';

            // Supprimer la catégorie via AJAX
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'wgmbr_delete_category',
                    category_id: categoryId,
                    nonce: '<?php echo wp_create_nonce('wgmbr_categories'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recharger la page pour mettre à jour la liste
                    window.location.reload();
                } else {
                    alert('<?php _e('Error:', 'wolves-avis-google'); ?> ' + (data.data?.message || '<?php _e('Unknown error', 'wolves-avis-google'); ?>'));
                    this.disabled = false;
                    this.textContent = '<?php _e('Delete', 'wolves-avis-google'); ?>';
                }
            })
            .catch(error => {
                alert('<?php _e('Network error:', 'wolves-avis-google'); ?> ' + error.message);
                this.disabled = false;
                this.textContent = '<?php _e('Delete', 'wolves-avis-google'); ?>';
            });
        });
    });
});
</script>
