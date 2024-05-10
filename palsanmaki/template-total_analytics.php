<?php
/**
 * Template name: Simple Analytics Page
 *
 */

 get_header();

 global $wpdb;

$yearly_bids_stats = $wpdb->get_results("
    SELECT YEAR(bid_time) AS year,
           COUNT(*) AS total_bids,
           SUM(bid_value) AS total_bids_value,
           SUM(CASE WHEN winner = 1 THEN 1 ELSE 0 END) AS total_winning_bids,
           SUM(CASE WHEN winner = 1 THEN bid_value ELSE 0 END) AS total_winning_bids_value
    FROM {$wpdb->prefix}auction_bids
    GROUP BY YEAR(bid_time)
");

$current_year = date('Y');
$auctions = $wpdb->get_results("
    SELECT
        p.ID,
        p.post_title,
        MAX(pm.meta_value) as auction_date
    FROM
        {$wpdb->posts} p
    LEFT JOIN
        {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'auction_date'
    WHERE
        p.post_type = 'auction'
        AND YEAR(pm.meta_value) = '{$current_year}'
    GROUP BY
        p.ID
    ORDER BY
        auction_date ASC
");
?>

<?php get_template_part('template-parts/sections/section', 'hero'); ?>

<section class="total_analytics_section">
    <div class="container">
        <div class="row">

        <div class="annual_analytics">
            <h3><?php _e('TILASTOT VUOSITTAIN: ', 'default'); ?></h3>
            <table>
                <tr class="analytics_head">
                    <td><?php _e('Vuosi', 'default'); ?></td>
                    <td><?php _e('Ennakkohuutojen määrä', 'default'); ?></td>
                    <td><?php _e('Voitetut ennakkohuudot', 'default'); ?></td>
                    <td><?php _e('Ennakkohuutojen summa', 'default'); ?></td>
                    <td><?php _e('Voitettujen huutojen summa', 'default'); ?></td>
                </tr>
                <?php foreach ($yearly_bids_stats as $stat): ?>
                <tr>
                    <td><?php echo esc_html($stat->year); ?></td>
                    <td><?php echo esc_html($stat->total_bids); ?></td>
                    <td><?php echo esc_html($stat->total_winning_bids); ?></td>
                    <td><?php echo esc_html(number_format($stat->total_bids_value, 2, '.', '')); ?> €</td>
                    <td><?php echo esc_html(number_format($stat->total_winning_bids_value, 2, '.', '')); ?> €</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="auction_analytics_list">
            <h3><?php _e('TILASTOT PER HUUTOKAUPPA: ', 'default'); ?></h3>
            <table>
                <tr class="analytics_head">
                    <td><?php _e('Päivämäärä', 'default'); ?></td>
                    <td><?php _e('Huutokaupan nimi', 'default'); ?></td>
                    <td><?php _e('Ennakkohuutojen määrä', 'default'); ?></td>
                    <td><?php _e('Voitetut ennakkohuudot', 'default'); ?></td>
                    <td><?php _e('Ennakkohuutojen summa', 'default'); ?></td>
                    <td><?php _e('Voitettujen huutojen summa', 'default'); ?></td>
                </tr>
                <?php foreach ($auctions as $auction):
                    // Запрос для получения статистики по каждому аукциону
                    $stats = $wpdb->get_row("
                        SELECT
                            COUNT(*) as bids_count,
                            SUM(bid_value) as bids_sum,
                            SUM(CASE WHEN winner = 1 THEN bid_value ELSE 0 END) as winner_bids_sum,
                            SUM(CASE WHEN winner = 1 THEN 1 ELSE 0 END) as winner_bids_count
                        FROM
                            {$wpdb->prefix}auction_bids
                        WHERE
                            auction_id = {$auction->ID}
                    ");
                ?>
                <tr>
                    <td><?php echo date('d.m.Y', strtotime($auction->auction_date)); ?></td>
                    <td><?php echo esc_html($auction->post_title); ?></td>
                    <td>
                        <?php if (!empty($stats->bids_count)):?>
                            <?php echo esc_html($stats->bids_count); ?>
                        <?php else:?> 0
                        <?php endif;?>
                    </td>
                    <td>
                        <?php if (!empty($stats->winner_bids_count)):?>
                            <?php echo esc_html($stats->winner_bids_count); ?>
                        <?php else:?> 0
                        <?php endif;?>
                    </td>
                    <td>
                        <?php if (!empty($stats->bids_sum)):?>
                            <?php echo esc_html($stats->bids_sum); ?> €
                        <?php else:?> 0.00 €
                        <?php endif;?>
                    </td>
                    <td>
                        <?php if (!empty($stats->winner_bids_sum)):?>
                            <?php echo esc_html($stats->winner_bids_sum); ?> €
                        <?php else:?> 0.00 €
                        <?php endif;?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        </div>
    </div>
</section>

<?php
get_footer();