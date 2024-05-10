<?php
/**
 */

get_header();

$header_image = '/wp-content/themes/palsanmaki/assets/images/hero_head_img.jpg';

$text = get_the_excerpt();
$words = 20;
$excerpt_lenght = 20;
$more = '…';
$excerpt = wp_trim_words( $text, $words, $more );
?>

<section class="page_header_section" >
    <div class="page_header_container container-fluid" style="background-image: url('<?php echo $header_image;?>'); background-size: cover;">
        <div class="container">
            <div class="row align-items-center">
            <div class=" col-sm-10 col-md-8 col-lg-8 center-area">
                <h1 class="hero_title mx-auto">
                    <?php
                    /* translators: %s: search query. */
                    printf( esc_html__( 'Search Results for: %s', 'miami' ), '<span>' . get_search_query() . '</span>' );
                    ?>
                </h1>

                <div class="search_form_panel d-flex">
                    <div class="search_form">
                        <?php get_search_form();?>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</section>

<?php if ( have_posts() ) : ?>

<!-- Posts Grid Area Start -->
<section class="blogrid_articles">
    <div class="container">
        <div class="row">

        <?php
        // Start the Loop.
        while ( have_posts() ) : the_post(); ?>

            <div class="col-md-6 col-xl-4" id="post-<?php the_ID(); ?>" id="post-<?php the_ID(); ?>">
                <div class="articles-item">
                    <div class="image">
                        <?php if (has_post_thumbnail( $post->ID ) ): ?>
                            <?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'image_full' ); ?>
                            <img src="<?php echo $image[0]; ?>" alt="<?php the_title(); ?>">
                        <?php else: ?>
                            <img src="<?php bloginfo('template_directory'); ?>/assets/images/post_thumbnail.png" alt="<?php the_title(); ?>" />
                        <?php endif; ?>
                    </div>

                    <div class="articles-content">
                        <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>

                        <p><?php echo custom_excerpt($excerpt_lenght); ?></p>

                        <a href="<?php the_permalink(); ?>" class="learn-more"><?php echo 'Lue lisää'; ?> <i class="fas fa-long-arrow-alt-right"></i></a>
                    </div>
                </div>
            </div>

            <?php endwhile; ?>
            </div>

            <?php // Previous/next page navigation.
                the_posts_pagination( array(
                    'prev_text'          => __( 'Previous page', 'twentyfifteen' ),
                    'next_text'          => __( 'Next page', 'twentyfifteen' ),
                    'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'twentyfifteen' ) . ' </span>',
                ) );

                // If no content, include the "No posts found" template.
                else :
                    get_template_part( 'template-parts/content-none' );

            endif; ?>

        </div>
    </div>
</section>
<!-- Posts Grid Area End -->

<?php
get_footer();