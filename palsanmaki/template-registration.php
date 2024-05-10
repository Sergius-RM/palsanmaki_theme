<?php
/**
 * Template name: Registration
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 */

// Обработка регистрации пользователя
if (isset($_POST['email']) && isset($_POST['password']) && !is_user_logged_in()) {
    // Формирование логина пользователя из имени и фамилии
    $user_login = sanitize_user($_POST['first_name'] . '_' . $_POST['last_name']);
    $user_mail = sanitize_email($_POST['email']);

    // Подготовка данных пользователя
    $user_data = array(
        'user_login' => $user_login,
        'user_email' => sanitize_email($_POST['email']),
        'user_pass' => sanitize_text_field($_POST['password']),
        'first_name' => sanitize_text_field($_POST['first_name']),
        'last_name' => sanitize_text_field($_POST['last_name']),
        // 'role' => 'subscriber' // Устанавливаем роль пользователя, если это необходимо
    );

    // Вставка пользователя в базу данных WordPress
    $user_id = wp_insert_user($user_data);

    // Проверяем, что пользователь был успешно создан и не возникло ошибок
    if (!is_wp_error($user_id)) {
        // Обновление пользовательских мета-данных
        update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
        update_user_meta($user_id, 'address', sanitize_text_field($_POST['address']));
        update_user_meta($user_id, 'zip_code', sanitize_text_field($_POST['zip_code']));
        update_user_meta($user_id, 'city', sanitize_text_field($_POST['city']));

        // Авторизация пользователя
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        // Отправка данных аккаунта на электронную почту пользователя
        $to = $_POST['email'];
        $subject = 'Tilisi tiedot';
        $message = "Tervetuloa Huutokauppa Palsanmäkeen! Kirjautumistietosi ovat alla:\nKäyttäjätunnus: " . $user_login . "\nSähköposti: " . $user_mail . "\nLinkki sivustolle: www.palsanmaki.fi";
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        wp_mail($to, $subject, $message, $headers);

        // Перенаправление в личный кабинет
        wp_redirect(home_url('/user-account/'));
        exit;
    } else {
        // Обработка ошибки создания пользователя
        echo '<div class="error">' . $user_id->get_error_message() . '</div>';
    }
}

get_header();
?>

<?php if (!is_user_logged_in()) : ?>

    <?php get_template_part('template-parts/sections/section', 'hero'); ?>

    <section class="authorization_section user_account">

        <div class="container-fluid">
            <div class="container">
                <div class="row">

                    <form method="post">
                        <h3><?php _e('TÄYTÄ LOMAKE JA REKISTERÖIDY HUUTAJAKSI', 'default') ?></h3>
                        <div class="d-flex d-flex justify-content-center">
                            <input type="text" name="first_name" placeholder="Etunimi" required>
                            <input type="text" name="last_name" placeholder="Sukunimi" required>
                        </div>
                        <div class="d-flex d-flex justify-content-center">
                            
                            <input type="email" name="email" placeholder="Sähköposti" required>
                            <input type="password" name="password" placeholder="Salasana" required>
                        </div>
                        <div class="d-flex d-flex justify-content-center">
                            <input type="text" name="phone" placeholder="Puhelinnumero" required>
                            <input type="text" id="address__" name="address" placeholder="Postiosoite" required>
                        </div>
                        <div class="d-flex d-flex justify-content-center">
                            <input type="text" name="zip_code" placeholder="Postinumero" required>
                            <input type="text" name="city" placeholder="Paikkakunta" required>
                        </div>
                        <button type="submit">Rekisteröidy</button>
                    </form>

                </div>
            </div>
        </div>
    </section>

<?php else : ?>

    <script>
        // JavaScript для редиректа на главную страницу
        window.location.replace("<?php echo '/user-account'; ?>");
    </script>

<?php endif; ?>

<?php
get_footer();