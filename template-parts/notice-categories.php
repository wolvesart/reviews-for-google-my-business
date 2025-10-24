<?php wgmbr_get_template_parts('template-parts/components/notice', [
        'title' => __("💡 How to use categories", 'wolves-avis-google'),
        'body' => __("
<h3>Assign to reviews</h3>
<p>Go to Google Reviews and check the appropriate categories for each review.</p>
<h3>Use in shortcode</h3>
<p>Filter reviews by category with: [gmb_reviews category='slug']</p>
<h3>Multiple categories</h3>
<p>A review can have multiple categories. It will appear in the filter for each of them.</p>
", 'wolves-avis-google'),
]); ?>