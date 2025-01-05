<?php
/**
 * Location meta component
 */
if (!defined('ABSPATH')) exit;

$city = get_post_meta($args['vacancy_id'], '_vacancy_city', true);
$country = get_post_meta($args['vacancy_id'], '_vacancy_country', true);

if ($city || $country): ?>
    <div class="meta-item location">
        <i class="fas fa-map-marker-alt"></i>
        <span>
            <?php
            $location_parts = array_filter(array($city, $country));
            echo esc_html(implode(', ', $location_parts));
            ?>
        </span>
    </div>
<?php endif; ?>
