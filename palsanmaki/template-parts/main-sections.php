<?php
/**
 * All sections and template of EasyE theme
 *
 */
?>

<?php if ( have_rows( 'sections' ) ) : ?>
    <?php while ( have_rows('sections' ) ) : the_row();
        if ( get_row_layout() == 'hero_slider' ) :
            get_template_part('template-parts/sections/section', 'hero_slider');

        elseif ( get_row_layout() == 'two_columns' ) :
            get_template_part('template-parts/sections/section', 'two_columns');

        elseif ( get_row_layout() == 'contactus' ) :
            get_template_part('template-parts/sections/section', 'contactus');

        elseif ( get_row_layout() == 'info_card' ) :
            get_template_part('template-parts/sections/section', 'info_card');

        elseif ( get_row_layout() == 'service_grid' ) :
            get_template_part('template-parts/sections/section', 'service_grid');

        elseif ( get_row_layout() == 'featured_products' ) :
            get_template_part('template-parts/sections/section', 'featured_products');

        elseif ( get_row_layout() == 'maps' ) :
            get_template_part('template-parts/sections/section', 'maps');

        elseif ( get_row_layout() == 'separator' ) :
            get_template_part('template-parts/sections/section', 'separator');

        elseif ( get_row_layout() == 'related_articles' ) :
            get_template_part('template-parts/sections/section', 'related_articles');

            ?>
        <?php endif; ?>
    <?php endwhile; ?>
<?php endif; ?>