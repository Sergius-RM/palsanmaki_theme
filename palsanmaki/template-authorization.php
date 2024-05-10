<?php
/**
 * Template name: Authorization
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 */

get_header();
?>

<?php if (!is_user_logged_in()):?>

<section class="authorization_section">

    <section class="page-hero-section">

    </section>

    <div class="container-fluid">
        <div class="container">
            <div class="row">

                <form action="<?php echo esc_url(site_url('wp-login.php')); ?>" method="post">
                    <h3><?php _e( 'KIRJAUDU SISÄÄN', 'default' ) ?></h3>

                    <input type="text" placeholder="Sähköposti" name="log" id="user_login">

                    <div class="pass">
                        <input type="password" placeholder="Salasana" name="pwd" id="user_pass">
                        <button class="show_pass" type="button" onclick="togglePassword()">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>

                    <script>
                        function togglePassword() {
                            var passwdInput = document.getElementById('user_pass');
                            if (passwdInput.type === 'password') {
                                passwdInput.type = 'text';
                            } else {
                                passwdInput.type = 'password';
                            }
                        }
                    </script>

                    <p><?php _e( 'Unohtuiko käyttäjätunnus tai salasana?', 'default' ) ?> <a class="remind_password" href="/salasanan_palautus/"><?php _e( 'Unohtuiko salasana', 'default' ) ?></a></p>
                    
                    <p><?php _e( 'Jos sinulla ei ole vielä tunnusta rekisteröidy tästä.', 'default' ) ?> <a class="remind_password" href="<?php the_field('user_registration_link', 'option');?>"><?php _e( 'Rekisteröidy', 'default' ) ?></a></p>

                    <input type="submit" name="wp-submit" value="Kirjaudu">

                    <input type="hidden" name="redirect_to" value="<?php echo site_url('/user-account'); ?>">
                </form>

            </div>
        </div>
    </div>
</section>
<?php else:?>
    <h3 style="text-align: center; padding: 10vw 0;">
        <?php _e( 'Olet jo kirjautunut sisään', 'default' ) ?>
    </h3>
<?php endif;?>

<?php
get_footer();