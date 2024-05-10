<?php
/**
 * Pagination Functions
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Get pagination.
 *
 * @param  string $aria_label
 * @return void
 */
function pagination($aria_label = 'Archive pagination') {

    global $wp_query, $wp_rewrite;

    $total_pages = $wp_query->max_num_pages;

    if ($total_pages > 1) {
        $current_page = max(1, get_query_var('paged'));

        $pagenum_link = html_entity_decode(get_pagenum_link());
        $query_args = array();
        $url_parts = explode('?', $pagenum_link);
        if (isset($url_parts[1])) {
            wp_parse_str($url_parts[1], $query_args);
        }

        $pagenum_link = remove_query_arg(array_keys($query_args), $pagenum_link);
        $pagenum_link = trailingslashit($pagenum_link) . '%_%';
        $format = $wp_rewrite->using_index_permalinks() && !strpos($pagenum_link, 'index.php') ? 'index.php/' : '';
        $format .= $wp_rewrite->using_permalinks() ? user_trailingslashit($wp_rewrite->pagination_base . '/%#%', 'paged') : '?paged=%#%';

        /**
         * Bootstrap 4 style.
         */
        ob_start();
        $args = [
            'base' => $pagenum_link,
            'format' => $format,
            'current' => $current_page,
            'total' => $total_pages,
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'type' => 'array',
            'end_size' => 3,
            'mid_size' => 1,
            'add_args' => array_map('urlencode', $query_args)
        ];
        $pagination = paginate_links($args);

        if (! empty($pagination)) : ?>
            <nav aria-label="<?php echo $aria_label; ?>">
            <ul class="pagination">
                <?php foreach ($pagination as $key => $page_link) : ?>
                    <li class="page-item<?php if (strpos($page_link, 'current') !== false) { echo ' active'; } ?>">
                        <?php if (false !== strpos($page_link, 'prev')) : ?>
                            <?php echo str_replace(['page-numbers', 'class="prev', $args['prev_text'] ], ['page-link', 'aria-label="' . __('Edellinen', 'greatcompany') . '" class="prev', '<span aria-hidden="true">' . $args['prev_text']. '</span>'], $page_link); ?>
                        <?php elseif (false !== strpos($page_link, 'next')) : ?>
                            <?php echo str_replace(['page-numbers', 'class="next', $args['next_text'] ], ['page-link', 'aria-label="' . __('Seuraava', 'greatcompany') . '" class="next', '<span aria-hidden="true">' . $args['next_text']. '</span>'], $page_link); ?>
                        <?php else : ?>
                            <?php echo str_replace('page-numbers', 'page-link', $page_link); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach ?>
            </ul>
            </nav>
        <?php endif;
        $links = ob_get_clean();
        return $links;
    }
}
