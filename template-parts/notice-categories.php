<?php wgmbr_get_template_parts('template-parts/components/notice', [
        'title' => __("How to use categories", 'google-my-business-reviews'),
        'body' => __("
<h3>Assign to reviews</h3>
<p>Go to Google Reviews and check the appropriate categories for each review.</p>
<h3>Use in shortcode</h3>
<p>Filter reviews by category with:<br>[gmb_reviews category=\"slug\"]</p>
<h3>Multiple categories</h3>
<p>A review can have multiple categories. For use this please separate slug with comma expl:<br>[gmb_reviews category=\"slug1,slug2,slug3\"]</p>
", 'google-my-business-reviews'),
]); ?>