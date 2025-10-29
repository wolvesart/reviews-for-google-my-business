<div class="card info">
    <img class="pattern-info" src="<?php echo esc_url(WOLVES_GMB_PLUGIN_URL . 'assets/images/gmb-pattern-info.png'); ?>"
         alt=""
         width="550"
         height="256"
         loading="lazy"
         fetchpriority="low"
    >
    <?php if (isset($params['title'])): ?>
        <h2><?php echo esc_html($params['title']); ?></h2>
    <?php endif; ?>
    <?php if (isset($params['body'])): ?>
        <?php echo wp_kses($params['body'], ['p' => [], 'br' => [], 'ul' => [], 'ol' => [], 'li' => [], 'a' => ['href' => [], 'title' => [], 'target' => []], 'h3' => [], 'h4' => []]); ?>
    <?php endif; ?>
</div>