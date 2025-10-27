<?php
$body_content = '<h3>' . __("Assign to reviews", 'reviews-for-google-my-business') . '</h3>';
$body_content .= '<p>' . __("Go to Google Reviews and check the appropriate categories for each review.", 'reviews-for-google-my-business') . '</p>';
$body_content .= '<h3>' . __("Use in shortcode", 'reviews-for-google-my-business') . '</h3>';
$body_content .= '<p>' . __("Filter reviews by category with:", 'reviews-for-google-my-business') . '<br>[gmb_reviews category="slug"]</p>';
$body_content .= '<h3>' . __("Multiple categories", 'reviews-for-google-my-business') . '</h3>';
$body_content .= '<p>' . __("A review can have multiple categories. For use this please separate slug with comma expl:", 'reviews-for-google-my-business') . '<br>[gmb_reviews category="slug1,slug2,slug3"]</p>';

wgmbr_get_template_parts('template-parts/components/notice', [
        'title' => __("How to use categories", 'reviews-for-google-my-business'),
        'body' => $body_content,
]); ?>