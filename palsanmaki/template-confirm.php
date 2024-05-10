<?php
/**
 * Template name: Confirm Page
 *
 */

get_header();
?>

<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

global $wpdb;

$tableName = $wpdb->prefix . 'auction_postage_info';

$user_id = intval($_GET['user_id']);
$auction_id = intval($_GET['auction_id']);
$action = sanitize_text_field($_GET['action']);

if (isset($_GET['comment_saved']) && $_GET['comment_saved'] == '1') {
    $comment_saved_message = 'Kommenttisi on tallennettu onnistuneesti.';
}

$saved_comment = '';
if ($user_id && $auction_id) {
    $saved_comment_query = $wpdb->prepare("SELECT user_comment FROM $tableName WHERE user_id = %d AND auction_id = %d LIMIT 1", $user_id, $auction_id);
    $saved_comment = $wpdb->get_var($saved_comment_query);
};

?>

    <?php get_template_part('template-parts/sections/section', 'hero'); ?>

    <section class="authorization_section user_account confirm_section">

        <div class="container">
            <div class="row">
                <div class="col-12 col-sm-12 col-lg-6 mx-auto">

                <?php if (!isset($_GET['user_id'], $_GET['auction_id'], $_GET['action'])):?>
                    <h2>
                        <?php _e('Tiedot eivät riitä vahvistamiseen', 'default'); ?>!
                    </h2>
                    <script>
                        setTimeout(function() {
                            window.location.replace("<?php echo home_url(); ?>");
                        }, 2000);
                    </script>
                <?php else:?>

                    <?php if (!empty($comment_saved_message)): ?>
                        <div class="notification">
                            <p><?php echo $comment_saved_message; ?></p>
                            <button class="close-registration-success" style="margin-left: 10px;"><i class="bi bi-x-circle"></i></button>
                        </div>
                    <?php endif; ?>

                    <?php
                    if ($action && $user_id && $auction_id) {

                        $tableName = $wpdb->prefix . 'auction_postage_info';

                        if ($action == 'accept_terms') {
                            $wpdb->update($tableName, ['terms_accepted' => 1], ['user_id' => $user_id, 'auction_id' => $auction_id]); ?>

                            <h2>
                                <?php _e('Kiitos vahvistuksesta! Tilaus lähetetään särkyvänä pakettina.', 'default'); ?>
                            </h2>

                            <div class="user_comment_area">
                                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                                <h3>
                                    <?php _e('Kerro toiveesi toimituspaikasta tai anna palautetta.', 'default'); ?>
                                </h3>
                                    <textarea name="user_comment" id="user_comment" cols="30" rows="10"><?php echo esc_textarea($saved_comment); ?></textarea>
                                    <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>">
                                    <input type="hidden" name="auction_id" value="<?php echo esc_attr($auction_id); ?>">
                                    <input type="hidden" name="action" value="save_user_comment">
                                    <?php wp_nonce_field('user_comment_nonce', 'user_comment_nonce_field'); ?>
                                    <button type="submit">
                                        <?php _e('Lähetä kommentti', 'default'); ?>
                                    </button>
                                </form>
                            </div>

                        <?php } elseif ($action == 'normal_postage') {
                            $wpdb->update($tableName, ['normal_postage' => 1], ['user_id' => $user_id, 'auction_id' => $auction_id]); ?>

                            <h2>
                                <?php _e('Kiitos vahvistuksesta! Tilaus lähetetään normaalina pakettina.', 'default'); ?>
                            </h2>

                            <div class="user_comment_area">
                                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                                <h3>
                                    <?php _e('Kerro toiveesi toimituspaikasta tai anna palautetta.', 'default'); ?>
                                </h3>
                                    <textarea name="user_comment" id="user_comment" cols="30" rows="10"><?php echo esc_textarea($saved_comment); ?></textarea>
                                    <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>">
                                    <input type="hidden" name="auction_id" value="<?php echo esc_attr($auction_id); ?>">
                                    <input type="hidden" name="action" value="save_user_comment">
                                    <?php wp_nonce_field('user_comment_nonce', 'user_comment_nonce_field'); ?>
                                    <button type="submit">
                                        <?php _e('Lähetä kommentti', 'default'); ?>
                                    </button>
                                </form>
                            </div>

                        <?php } elseif ($action == 'will_pickup') {
                            $wpdb->update($tableName, ['will_pickup' => 1], ['user_id' => $user_id, 'auction_id' => $auction_id]); ?>

                            <h2>
                                <?php _e('Kiitos vahvistuksesta! Alla voit kommentoida milloin tulet hakemaan tuotteesi, tervetuloa!', 'default'); ?>
                            </h2>

                            <div class="user_comment_area">
                                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" style="background: url(/wp-content/themes/palsanmaki/assets/images/form_plate.png) 50% 50% no-repeat; background-size: cover; padding: 40px;">
                                    <textarea name="user_comment" id="user_comment" cols="30" rows="10"><?php echo esc_textarea($saved_comment); ?></textarea>
                                    <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>">
                                    <input type="hidden" name="auction_id" value="<?php echo esc_attr($auction_id); ?>">
                                    <input type="hidden" name="action" value="save_user_comment">
                                    <?php wp_nonce_field('user_comment_nonce', 'user_comment_nonce_field'); ?>
                                    <button type="submit">
                                        <?php _e('Lähetä kommentti', 'default'); ?>
                                    </button>
                                </form>
                            </div>

                        <?php } else { ?>

                            <h2>
                                <?php _e('Väärä toiminta', 'default'); ?>
                            </h2>

                        <?php }
                    } ?>

                    <div class="link_in">
                        <?php if (is_user_logged_in()) : ?>
                            <?php _e('Voit mennä tilillesi: ', 'default'); ?>
                            <a href="<?php the_field('user_account_link', 'option');?>">
                                <i class="bi bi-person-circle"></i>
                                <?php echo wp_get_current_user()->display_name; ?>
                            </a>
                        <?php else : ?>
                            <?php _e('Voit kirjautua sisään: ', 'default'); ?>
                            <a href="<?php the_field('user_authorization_link', 'option');?>">
                                <?php the_field('user_authorization_name', 'option');?>
                            </a>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

<script>
    jQuery(document).ready(function($) {
        $('.close-registration-success').click(function() {
            $(this).parent('.notification').hide();
        });
    });
</script>
<?php
get_footer();