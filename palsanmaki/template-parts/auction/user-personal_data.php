<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>

    <?php
        $current_user = wp_get_current_user();
        $first_name = $current_user->first_name;
        $last_name = $current_user->last_name;
        $phone_number = get_user_meta($current_user->ID, 'phone', true);
        $email = $current_user->user_email;
        $address = get_user_meta($current_user->ID, 'address', true);
        $postal_code = get_user_meta($current_user->ID, 'zip_code', true);
        $city = get_user_meta($current_user->ID, 'city', true);
    ?>

    <div class="user_data_tab">
        <div class="user_data_item">
            <strong><?php _e( 'Etunimi', 'default' ) ?></strong> <?php echo esc_html($first_name); ?>
        </div>
        <div class="user_data_item">
            <strong><?php _e( 'Sukunimi', 'default' ) ?></strong> <?php echo esc_html($last_name); ?>
        </div>
        <div class="user_data_item">
            <strong><?php _e( 'Puhelinnumero', 'default' ) ?></strong> <?php echo esc_html($phone_number); ?>
        </div>
        <div class="user_data_item">
            <strong><?php _e( 'Sähköposti', 'default' ) ?></strong> <?php echo esc_html($email); ?>
        </div>
        <div class="user_data_item">
            <strong><?php _e( 'Osoite', 'default' ) ?></strong> <?php echo esc_html($address); ?>
        </div>
        <div class="user_data_item">
            <strong><?php _e( 'Postinumero', 'default' ) ?></strong> <?php echo esc_html($postal_code); ?>
        </div>
        <div class="user_data_item">
            <strong><?php _e( 'Paikkakunta', 'default' ) ?></strong> <?php echo esc_html($city); ?>
        </div>
    </div>

    <div class="user_control_btn">
        <a href="<?php echo esc_url(home_url('/muuta-tietoja')); ?>" class="change_data_btn">
            <?php _e( 'Muuta tietoja', 'default' ) ?>
        </a>
        <a href="<?php echo esc_url(home_url('/vaihda-salasana')); ?>" class="change_password_btn">
            <?php _e( 'Vaihda salasana', 'default' ) ?>
        </a>
    </div>