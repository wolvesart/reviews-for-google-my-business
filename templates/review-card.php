<?php
/**
 * Partial template for review card
 *
 * Variables:
 * - $wgmbr_parsed_item (object)
 * - $wgmbr_is_modal (bool)
 * - $wgmbr_review_index (int)
 */

if (!defined('ABSPATH')) {
    exit;
}

// SANITIZE: Ensure variables are properly typed and safe
$wgmbr_is_modal = isset($wgmbr_is_modal) ? (bool) $wgmbr_is_modal : false;
$wgmbr_review_index = isset($wgmbr_review_index) ? absint($wgmbr_review_index) : 0;
$wgmbr_parsed_item = isset($wgmbr_parsed_item) ? $wgmbr_parsed_item : null;
?>

<div class="gmb-review-card <?php echo esc_attr($wgmbr_is_modal ? 'is-modal' : ''); ?>">
    <div class="gmb-review-header">
        <div class="gmb-review-meta">
            <div class="gmb-stars">
                <?php echo wp_kses_post(wgmbr_render_stars($wgmbr_parsed_item->rating)); ?>
            </div>
        </div>
        <?php if (!$wgmbr_is_modal): ?>
            <div class="gmb-review-source"></div>
        <?php endif; ?>
    </div>

    <?php if ($wgmbr_parsed_item->comment): ?>
        <div class="gmb-review-content"
             <?php if (!$wgmbr_is_modal): ?>data-review-index="<?php echo absint($wgmbr_review_index); ?>"<?php endif; ?>>
            <p><?php echo esc_html($wgmbr_parsed_item->comment); ?></p>
            <?php if (!$wgmbr_is_modal): ?>
                <button class="gmb-read-more-btn" data-review-index="<?php echo absint($wgmbr_review_index); ?>">
                    <?php esc_html_e('Read more', 'reviews-for-google-my-business'); ?>
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="gmb-review-footer">
        <?php if ($wgmbr_parsed_item->photo): ?>
            <img src="<?php echo esc_url($wgmbr_parsed_item->photo); ?>"
                 alt="<?php echo esc_attr($wgmbr_parsed_item->name); ?>"
                 class="gmb-review-avatar"
                 loading="lazy">
        <?php else: ?>
            <div class="gmb-review-avatar-placeholder">
                <?php echo esc_html(substr($wgmbr_parsed_item->name, 0, 1)); ?>
            </div>
        <?php endif; ?>

        <div class="gmb-review-author-info">
            <span class="gmb-author-name">
                <?php echo esc_html($wgmbr_parsed_item->name); ?>
            </span>
            <?php if ($wgmbr_parsed_item->job): ?>
                <p class="gmb-author-job">
                    <?php echo esc_html($wgmbr_parsed_item->job); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
