<?php
/**
 * Template name: Change User Account
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 */

 get_header();
 ?>

    <?php get_template_part('template-parts/sections/section', 'hero'); ?>

    <section class="authorization_section user_account">
        <div class="container-fluid">
            <div class="container">
                <div class="row">

                <form method="post" id="form">
                    <?php wp_nonce_field('update_user_info_nonce', 'security'); ?>
                    <input type="hidden" name="action" value="update_user_info">

                    <div class="d-flex justify-content-center">
                        <input type="text" id="first_name" name="first_name" placeholder="Etunimi"
                            value="<?php echo $current_user->first_name; ?>">
                        <input type="text" id="last_name" name="last_name" placeholder="Sukunimi"
                            value="<?php echo $current_user->last_name; ?>">
                    </div>
                    <div class="d-flex justify-content-center">
                        <input type="text" id="phone" name="phone" placeholder="Puhelinnumero"
                            value="<?php echo get_user_meta($current_user->ID, 'phone', true); ?>">
                        <input type="email" id="email" name="email" placeholder="Sähköposti"
                            value="<?php echo $current_user->user_email; ?>">
                    </div>

                    <input type="text" id="address" name="address" placeholder="Osoite"
                        value="<?php echo get_user_meta($current_user->ID, 'address', true); ?>">

                    <div class="d-flex justify-content-center">
                        <input type="text" id="zip_code" name="zip_code" placeholder="Postinumero"
                            value="<?php echo get_user_meta($current_user->ID, 'zip_code', true); ?>">
                        <input type="text" id="city" name="city" placeholder="Paikkakunta"
                            value="<?php echo get_user_meta($current_user->ID, 'city', true); ?>">
                    </div>
                    <button type="submit">
                        <?php _e('Päivitä', 'default'); ?>
                    </button>

                </form>

                </div>
            </div>
        </div>
    </section>

    <script>
        // Получаем форму по id
        var form = document.getElementById("form");

        // Вешаем обработчик отправки формы
        form.addEventListener("submit", function(e) {

        // Отменяем стандартное поведение
        e.preventDefault();

        // Данные формы
        var data = new FormData(form);

        // Отправляем AJAX запрос
        fetch(ajaxurl, {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(result => {

            // Обновляем значения полей формы
            document.getElementById('first_name').value = result.first_name;
            document.getElementById('last_name').value = result.last_name;
            document.getElementById('phone').value = result.phone;
            document.getElementById('email').value = result.email;
            document.getElementById('address').value = result.address;
            document.getElementById('zip_code').value = result.zip_code;
            document.getElementById('city').value = result.city;
        });

        });
    </script>

 <?php
 get_footer();