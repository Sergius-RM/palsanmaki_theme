<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>

<aside class="widget widget_nav_menu">
    <div class="menu-serice-menu-container">
        <ul id="menu-serice-menu" class="menu">

            <?php if (is_user_logged_in()) : ?>
                <li class="menu-item">
                    <a href="<?php echo wp_logout_url( home_url() ); ?>">
                        <?php the_field('user_account_exit', 'option');?>
                    </a>
                </li>
                <li class="menu-item">
                    <div class="logged-user">
                        <a href="<?php the_field('user_account_link', 'option');?>">
                            <i class="bi bi-person-circle"></i>
                            <?php echo wp_get_current_user()->display_name; ?>
                        </a>
                    </div>
                </li>
            <?php else : ?>
                <li class="menu-item">
                    <a href="<?php the_field('user_authorization_link', 'option');?>">
                        <?php the_field('user_authorization_name', 'option');?>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?php the_field('user_registration_link', 'option');?>">
                        <?php the_field('user_registration_name', 'option');?>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </div>
</aside>