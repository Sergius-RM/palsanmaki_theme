<?php
/**
 * Media
 *
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

add_theme_support('post-thumbnails');
add_theme_support('automatic-feed-links');
add_theme_support('title-tag');
add_theme_support(
    'html5',
    array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    )
);
add_theme_support(
    'custom-logo',
    array(
        'height'      => 100,
        'width'       => 350,
        'flex-height' => true,
        'flex-width'  => true,
    )
);

/**
 * Get image and fallback to random placeholder image if needed.
 */
function get_image_url($attacment_id = null, $size = '', $fallback = true)
{

    if ($attacment_id != null) {
        $image_url = wp_get_attachment_image_src($attacment_id, $size);

        if (isset($image_url[0])) {
            return $image_url[0];
        } else {
            $attacment_id = 0;
        }
    }

    if ((int) $attacment_id == 0 && $fallback === true) {

        if (function_exists('get_field')) {
            $image = get_field('placeholder_images_placeholder_image_' . rand(1, 5), 'option');
        }

        $image_url = wp_get_attachment_image_src($image, 'large');
        if (isset($image_url[0])) {
            return $image_url[0];
        }
    }

    return false;

}


/**
 * Returns URl for local resource, relative to theme root.
 *
 * @param string $resource
 * @param string $mode Determines wether to return server side or client side path.
 */
function themeresource(string $resource = '', $mode = 'uri')
{
    $path = '';
    $localpath = '';

    if (is_child_theme()) {
        // If it's a child theme, we're most likely going to want to get the
        // resources from the child theme dir.
        $path = get_stylesheet_directory_uri();
        $localpath = get_stylesheet_directory();
    } else {
        $path = get_template_directory_uri();
        $localpath = get_template_directory();
    }

    $path = $path . DIRECTORY_SEPARATOR;
    $localpath = $localpath . DIRECTORY_SEPARATOR;

    if (!file_exists($localpath . $resource)) {
        return false;
    }

    if ($mode === 'local') {
        return $localpath . $resource;
    }

    return $path . $resource;
}


/**
 * Return an inline svg element, contained in a wrapper div.
 *
 * @param mixed $name
 */
function inline_svg($name, $title = '')
{
    $checklist = [$name, "$name.svg", "icon_$name.svg"]; // check in this order
    $build_svg = function ($path, $title) {
        $svg = file_get_contents($path);
        $title_attr = '';
        if (!empty($title)) {
            $title_attr = ' title="' . esc_attr($title) . '"';
        }
        return '<div class="inline-svg"' . $title_attr . '>' . $svg . '</div><span class="sr-only">' . esc_attr($title) . '</span>';
    };
    foreach ($checklist as $file) {
        $filepath = themeresource($file, 'local');
        if ($filepath) {
            return $build_svg($filepath, $title);
        }

        return false;
    }

    return false;
}
