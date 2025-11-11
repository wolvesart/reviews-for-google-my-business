<?php
/**
 * Reviews for Google My Business - Template reviews display
 *
 * Variables:
 * - $data (array) : reviews data
 * - $atts (array) : shortcode attributs
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="gmb-reviews-container">

    <?php
    // Check if the summary display is requested (controlled by the shortcode)
    $wgmbr_show_summary = isset($data['show_summary']) ? $data['show_summary'] : true;
    if ($wgmbr_show_summary && isset($data['average_rating']) && $data['average_rating'] > 0):
        ?>
        <div class="gmb-reviews-summary">
            <span class="gmb-rating-number"><?php echo number_format($data['average_rating'], 1); ?></span>
            <div class="gmb-overall-rating">
                <div class="gmb-stars">
                    <?php echo wp_kses_post(wgmbr_render_stars($data['average_rating'])); ?>
                </div>
                <span class="gmb-total-reviews">Basé sur <?php echo esc_html($data['total']); ?> avis</span>
            </div>
        </div>
    <?php endif; ?>

    <div class="gmb-reviews-swiper-wrapper">
        <div class="gmb-reviews-swiper swiper">
            <div class="swiper-wrapper">
                <?php
                $wgmbr_count = 0;
                foreach ($data['reviews'] as $wgmbr_parsed):
                    $wgmbr_count++;

                    // $wgmbr_parsed is already a parsed object from the CPT
                    ?>

                    <div class="swiper-slide">
                        <?php
                        $wgmbr_review_index = $wgmbr_count - 1;
                        $wgmbr_is_modal = false;
                        $wgmbr_parsed_item = $wgmbr_parsed;
                        include WGMBR_PLUGIN_DIR . 'templates/review-card.php';
                        ?>
                    </div>

                <?php endforeach; ?>
            </div>
        </div>

        <div class="gmb-swiper-button-prev"></div>
        <div class="gmb-swiper-button-next"></div>
        <div class="gmb-swiper-pagination"></div>
    </div>

    <div class="gmb-review-modal" id="gmb-review-modal">
        <div class="gmb-modal-overlay"></div>
        <div class="gmb-modal-content">
            <button class="gmb-modal-close" aria-label="Fermer">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>

            <div class="gmb-modal-body">
                <!-- Dynamically injected content -->
            </div>
        </div>
    </div>

    <div id="gmb-modal-templates" style="display: none;">
        <?php
        $wgmbr_count = 0;
        foreach ($data['reviews'] as $wgmbr_parsed):
            $wgmbr_review_index = $wgmbr_count;
            $wgmbr_is_modal = true;
            $wgmbr_parsed_item = $wgmbr_parsed;
            ?>
            <div class="gmb-modal-template" data-review-index="<?php echo absint($wgmbr_review_index); ?>">
                <?php include WGMBR_PLUGIN_DIR . 'templates/review-card.php'; ?>
            </div>
            <?php
            $wgmbr_count++;
        endforeach;
        ?>
    </div>

</div>
