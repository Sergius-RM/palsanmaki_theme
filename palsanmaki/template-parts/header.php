<?php
/**
 * The template for displaying header.
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
$site_name = get_bloginfo( 'name' );
$tagline   = get_bloginfo( 'description', 'display' );
?>

<!-- Start main Header -->
<header class="header_area full-width" role="banner">
    <!--Header-Upper-->

    <div class="site-header">
        <div class="site-branding align-items-center d-flex">

            <div class="navbar-brandlogo_area no_mobile">
                <?php the_custom_logo();?>
            </div>

            <!-- Main Menu -->
            <nav class="site-navigation">
                <div class="no_mobile" role="navigation">
                    <?php wp_nav_menu( array( 'theme_location' => 'menu-1' ) ); ?>
                </div>
            </nav>
            <!-- Main Menu End-->

            <!-- Mobile Menu -->
            <div class="navbar navbar-light bg-light <?php print $navbar_style;?> is_onmobile">
                <span class="navbar-brandlogo_area">
                    <span class="header-logo-darkmode">
                        <?php the_custom_logo();?>
                    </span>
                </span>

                <button class="navbar-toggler is_onmobile" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggleExternalContent" aria-controls="navbarToggleExternalContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <!-- Mobole Menu End-->

            <div class="serice_menu no_mobile ">
                <?php get_template_part( 'template-parts/sections/section-service_menu' ); ?>
            </div>
        </div>
    </div>

    <div class="collapse mob_menu" id="navbarToggleExternalContent">
        <div role="navigation">
            <?php wp_nav_menu( array( 'theme_location' => 'menu-1' ) ); ?>
        </div>
        <div class="serice_menu">
            <?php get_template_part( 'template-parts/sections/section-service_menu' ); ?>
        </div>
    </div>
    <!--End Header Upper-->
</header>

<?php if( tribe_is_event_query() && tribe_is_view('month') ) {
    get_template_part('template-parts/sections/section', 'hero');
} else if( tribe_is_event_query() && is_single() && 'tapahtuma' == get_post_type() ) {
    get_template_part('template-parts/sections/section', 'hero');
} ?>