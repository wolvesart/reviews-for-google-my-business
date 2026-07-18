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
            <span class="gmb-rating-number"><?php echo esc_html(number_format($data['average_rating'], 1)); ?></span>
            <div class="gmb-overall-rating">
                <div class="gmb-stars">
                    <?php echo wp_kses_post(wgmbr_render_stars($data['average_rating'])); ?>
                </div>
                <span class="gmb-total-reviews">
                    <?php
                    printf(
                        /* translators: %d: total number of reviews */
                        esc_html__('Based on %d reviews', 'reviews-for-google-my-business'),
                        absint($data['total'])
                    );
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <?php
    $wgmbr_layout = isset($data['layout']) ? $data['layout'] : WGMBR_DEFAULT_LAYOUT;
    $wgmbr_query_context = isset($data['query_context']) ? $data['query_context'] : array();
    ?>

    <?php if ($wgmbr_layout === 'masonry'): ?>

        <div class="gmb-reviews-masonry-wrapper">
            <div class="gmb-reviews-masonry"
                 data-order="<?php echo esc_attr(isset($wgmbr_query_context['order']) ? $wgmbr_query_context['order'] : 'recent'); ?>"
                 data-limit="<?php echo esc_attr(isset($wgmbr_query_context['limit']) ? $wgmbr_query_context['limit'] : WGMBR_DEFAULT_REVIEW_LIMIT); ?>"
                 data-category="<?php echo esc_attr(isset($wgmbr_query_context['category']) ? $wgmbr_query_context['category'] : ''); ?>"
                 data-category-filter="<?php echo esc_attr(isset($wgmbr_query_context['category_filter']) ? $wgmbr_query_context['category_filter'] : '0'); ?>">
                <?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML built from templates/review-card.php where every value is escaped
                echo wgmbr_render_masonry_items($data['reviews'], 0);
                ?>
            </div>

            <?php if (!empty($data['has_more'])): ?>
                <div class="gmb-show-more-wrapper">
                    <button type="button" class="gmb-show-more-btn">
                        <?php esc_html_e('Show more', 'reviews-for-google-my-business'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>

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

    <?php endif; ?>

    <div class="gmb-review-modal" id="gmb-review-modal">
        <div class="gmb-modal-overlay"></div>
        <div class="gmb-modal-content">
            <button class="gmb-modal-close" aria-label="<?php esc_attr_e('Close', 'reviews-for-google-my-business'); ?>">
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
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML built from templates/review-card.php where every value is escaped
        echo wgmbr_render_modal_templates($data['reviews'], 0);
        ?>
    </div>

</div>
