<?php
/**
 * Template name: Remind password
 *
 * This template is for creating a password reset request page.
 */

get_header();

// Check if the user is already logged in
if (!is_user_logged_in()):

    // Check if the form has been submitted
    if (isset($_POST['user_email'])) {
        $user_email = sanitize_email($_POST['user_email']);
        $user = get_user_by('email', $user_email);

        if ($user) {
            // Generate a unique reset key
            $reset_key = get_password_reset_key($user);

            // Create a password reset URL
            $reset_url = add_query_arg(array(
                'action' => 'rp',
                'key' => $reset_key,
                'login' => rawurlencode($user->user_login),
            ), wp_login_url());

            // Prepare and send the email
            $subject = __('Salasanan palautuspyyntö');
            $message = __('Tässä on salasanasi palautuslinkki:') . "\r\n\r\n";
            $message .= $reset_url . "\r\n\r\n";
            $message .= __('Jos tämä oli virhe, jätä tämä sähköposti huomioimatta, niin mitään ei tapahdu.');

            wp_mail($user_email, $subject, $message);

            // Notify the user to check their email
            echo '<div class="notification">Salasanan palautuslinkki on lähetetty sähköpostiosoitteeseesi.<button class="close-registration-success" style="margin-left: 10px;"><i class="bi bi-x-circle"></i></button></div>';
        } else {
            // Notify the user if the email doesn't exist
            echo '<div class="notification">Tällä sähköpostiosoitteella ei löytynyt käyttäjää.<button class="close-registration-success" style="margin-left: 10px;"><i class="bi bi-x-circle"></i></button></div>';
        }
    }
?>
<section class="authorization_section">

    <section class="page-hero-section">

    </section>

    <div class="container-fluid">
        <div class="container">
            <div class="row">

                <section class="password-remind-section">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <form method="post">
                                    <h3><?php _e('Nollaa salasana', 'default'); ?></h3>
                                    <input type="email" name="user_email" placeholder="<?php esc_attr_e('Syötä sähköpostiosoitteesi', 'default'); ?>" required><br>
                                    <input class="password-remind-btn" type="submit" value="<?php esc_attr_e('Pyydä salasanan vaihto', 'default'); ?>">
                                </form>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>
</section>
<?php else: ?>
    <script>
        window.location.replace("<?php echo home_url('/user-account'); ?>");
    </script>
<?php endif; ?>

<script>
    jQuery(document).ready(function($) {
        // Обработка клика по кнопке закрытия
        $('.close-registration-success').click(function() {
            // Скрываем родительский блок сообщения
            $(this).parent('.notification').hide();
        });
    });
</script>
<?php get_footer(); ?>
