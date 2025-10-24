<?php
/**
 * Template partiel pour une carte d'avis
 *
 * Variables attendues :
 * - $parsed (object) : Données parsées de l'avis
 * - $is_modal (bool) : Si true, affichage pour la modale (sans truncation)
 * - $review_index (int) : Index de l'avis (pour le bouton "Lire la suite")
 */

// Interdire l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

$is_modal = isset($is_modal) ? $is_modal : false;
$review_index = isset($review_index) ? $review_index : 0;
?>

<div class="gmb-review-card <?php echo $is_modal ? 'is-modal' : ''; ?>">
    <div class="gmb-review-header">
        <div class="gmb-review-meta">
            <div class="gmb-stars">
                <?php echo wgmbr_render_stars($parsed->rating); ?>
            </div>
        </div>
        <?php if (!$is_modal): ?>
            <div class="gmb-review-source"></div>
        <?php endif; ?>
    </div>

    <?php if ($parsed->comment): ?>
        <div class="gmb-review-content"
             <?php if (!$is_modal): ?>data-review-index="<?php echo $review_index; ?>"<?php endif; ?>>
            <p><?php echo esc_html($parsed->comment); ?></p>
            <?php if (!$is_modal): ?>
                <button class="gmb-read-more-btn" data-review-index="<?php echo $review_index; ?>">
                    Lire la suite
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="gmb-review-footer">
        <?php if ($parsed->photo): ?>
            <img src="<?php echo esc_url($parsed->photo); ?>"
                 alt="<?php echo esc_attr($parsed->name); ?>"
                 class="gmb-review-avatar">
        <?php else: ?>
            <div class="gmb-review-avatar-placeholder">
                <?php echo esc_html(substr($parsed->name, 0, 1)); ?>
            </div>
        <?php endif; ?>

        <div class="gmb-review-author-info">
            <span class="gmb-author-name">
                <?php echo esc_html($parsed->name); ?>
            </span>
            <?php if ($parsed->job): ?>
                <p class="gmb-author-job">
                    <?php echo esc_html($parsed->job); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
