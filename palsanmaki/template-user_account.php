<?php
/**
 * Template name: User Account
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 */

get_header();
?>

    <?php if (is_user_logged_in()) : ?>

    <section class="user_account_section">
        <div class="container">
            <div class="row mx-auto">
                <div class="col-12">

                    <div class="container mt-5">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" id="huudot-tab" data-bs-toggle="tab" href="#huudot"><?php _e( 'Omat huudot', 'default' ) ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tili-tab" data-bs-toggle="tab" href="#tili"><?php _e( 'Oma tili', 'default' ) ?></a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="huudot">

                                <?php if (current_user_can('manage_options')): ?>

                                    <?php get_template_part('template-parts/auction/admin', 'personal_account'); ?>

                                <?php else:?>

                                    <?php get_template_part('template-parts/auction/user', 'personal_analytics'); ?>

                                <?php endif;?>
                            </div>

                            <div class="tab-pane fade" id="tili">
                                <h3><?php _e( 'Oma tili', 'default' ) ?></h3>

                                <?php get_template_part('template-parts/auction/user', 'personal_data'); ?>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php else : ?>

        <script>
            // JavaScript для редиректа на главную страницу
            window.location.replace("<?php echo home_url(); ?>");
        </script>

    <?php endif; ?>

<?php
get_footer();