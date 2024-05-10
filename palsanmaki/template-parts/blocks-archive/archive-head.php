<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// получаем ID термина на странице термина
$term_id = get_queried_object_id();
// получим ID картинки из метаполя термина
$image_id = get_term_meta( $term_id, '_thumbnail_id', 1 );
// ссылка на полный размер картинки по ID вложения
$tax_image = wp_get_attachment_image_url( $image_id, 'full' );

if ( wp_get_attachment_image_url( $image_id, 'full' )) {
    $header_image = $tax_image;
} else {
    $header_image = '/wp-content/themes/palsanmaki/assets/images/hero_head_img.jpg';
}

?>

<!-- Archive Hero Section Start -->
<section class="page_header_section" >
    <div class="section_overlay"></div>
    <div class="page_header_container container-fluid" style="background-image: url('<?php echo $header_image;?>'); background-size: cover;">
        <div class="container">
            <div class="row align-items-center">
            <div class=" col-sm-10 col-md-8 col-lg-8 center-area">
                <h1 class="hero_title mx-auto">
                    <?php single_cat_title();?>
                </h1>
                <span class="page_header_content d-block">
                    <?php echo category_description();?>
                </span>
            </div>
        </div>
        </div>
    </div>
</section>
<!-- Archive Hero Section End -->
