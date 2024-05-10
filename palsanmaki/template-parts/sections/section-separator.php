<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$separator_style = get_sub_field('separator_style');

if ($separator_style == 'blue') {
    $style = 'separator_blue';
} else if ($separator_style == 'orange') {
    $style = 'separator_orange';
}

?>

<section class="separator_section <?php if ( get_sub_field('separator_style')):?> <?php echo $style;?> <?php endif; ?>">

</section>