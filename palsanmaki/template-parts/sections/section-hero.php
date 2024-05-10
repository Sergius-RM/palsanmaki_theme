<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>
<!-- Hero Section Start -->
<section class="page-hero-section">

    <div class="hero-container container">
        <div class="row mx-auto">

            <h1>
            <?php if( tribe_is_event_query() && tribe_is_view('month') ) :?>
                <?php _e( 'Tapahtumat', 'default' ) ?>
            <?php else:?>
                <?php the_title(); ?>
            <?php endif;?>
            </h1>

        </div>
    </div>
</section>
<!-- Hero Section End -->