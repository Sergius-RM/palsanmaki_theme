<?php
/**
 * Template name: Change Password
 *
 * This template is for a page where users can change their password.
 */

get_header();

// Check if the form has been submitted and the user is logged in
if (is_user_logged_in() && isset($_POST['new_password'], $_POST['confirm_password'])) {
    $user = wp_get_current_user();
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if the new passwords match
    if ($new_password === $confirm_password) {
        // Change the password
        wp_set_password($new_password, $user->ID);

        // Send email to the user
        $to = $user->user_email;
        $subject = 'Uusi salasanasi';
        $message = "Salasanasi on vaihdettu muotoon: " . $new_password;
        wp_mail($to, $subject, $message);

        // echo '<script>setTimeout(function(){ window.location.href = "' . esc_url(home_url('/authorization/')) . '"; }, 10000);</script>';
        echo '<div class="notification">Salasanasi on vaihdettu onnistuneesti ja lähetetty sinulle sähköpostitse.<button class="close-registration-success" style="margin-left: 10px;"><i class="bi bi-x-circle"></i></button></div>';
    } else {
        echo '<div class="notification">Salasanat eivät täsmää. Yritä uudelleen.<button class="close-registration-success" style="margin-left: 10px;"><i class="bi bi-x-circle"></i></button></div>';
    }
}
?>
<?php get_template_part('template-parts/sections/section', 'hero'); ?>

<section class="authorization_section user_account user_change_password">
    <div class="container-fluid">
        <div class="container">
            <div class="row">
                <form method="post">
                    <div class="pass">
                        <input type="password" id="new_password" name="new_password" placeholder="Uusi salasana" required>
                        <button class="show_pass" type="button" onclick="togglePassword()">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                    <div class="pass">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Toista salasana" required>
                        <button class="show_pass" type="button" onclick="togglePassword2()">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                    <button type="submit">Vaihda salasana</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    function togglePassword() {
        var passwdInput = document.getElementById('new_password');
        passwdInput.type = passwdInput.type === 'password' ? 'text' : 'password';
    }
    function togglePassword2() {
        var passwdInput = document.getElementById('confirm_password');
        passwdInput.type = passwdInput.type === 'password' ? 'text' : 'password';
    }
</script>
<script>
    jQuery(document).ready(function($) {
        $('.close-registration-success').click(function() {
            $(this).parent('.notification').hide();
        });
    });
</script>
<?php get_footer(); ?>
