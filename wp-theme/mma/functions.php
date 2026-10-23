<?php
/**
 * Тема MODERN MEDICINE ALLIANCE (mma17.uz)
 */
if (!defined('ABSPATH')) exit;

define('MMA_VER', '1.0.0');
define('MMA_DIR', get_template_directory());
define('MMA_URI', get_template_directory_uri());

require MMA_DIR . '/inc/defaults.php';
require MMA_DIR . '/inc/helpers.php';
require MMA_DIR . '/inc/acf-fields.php';
require MMA_DIR . '/inc/leads.php';
require MMA_DIR . '/inc/setup-content.php';

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('responsive-embeds');
});

/* Стили и скрипты. Версия файла = время изменения: после правок браузер сразу берёт новый файл */
function mma_asset_ver($rel) {
    $file = MMA_DIR . '/' . $rel;
    return MMA_VER . '.' . (is_readable($file) ? filemtime($file) : '0');
}

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('mma-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap', [], null);
    wp_enqueue_style('mma-main', MMA_URI . '/assets/css/main.css', [], mma_asset_ver('assets/css/main.css'));

    wp_enqueue_script('lucide', MMA_URI . '/assets/js/vendor/lucide.min.js', [], '0.460.0', true);
    wp_enqueue_script('gsap', MMA_URI . '/assets/js/vendor/gsap.min.js', [], '3.12.5', true);
    wp_enqueue_script('gsap-st', MMA_URI . '/assets/js/vendor/ScrollTrigger.min.js', ['gsap'], '3.12.5', true);
    wp_enqueue_script('mma-main', MMA_URI . '/assets/js/main.js', ['lucide', 'gsap-st'], mma_asset_ver('assets/js/main.js'), true);
    wp_localize_script('mma-main', 'MMA', [
        'ajax'  => admin_url('admin-ajax.php'),
        'phone' => mma_opt('phone'),
    ]);

    // На лендинге не нужны: стили блоков Gutenberg и глобальные стили
    if (is_front_page()) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('global-styles');
        wp_dequeue_style('classic-theme-styles');
    }
});

add_filter('wp_resource_hints', function ($urls, $type) {
    if ($type === 'preconnect') {
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin'];
    }
    return $urls;
}, 10, 2);

/* Лишнее в <head> */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');

/* SEO главной из «Настроек сайта», если нет SEO-плагина */
function mma_has_seo_plugin() {
    return defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('THE_SEO_FRAMEWORK_VERSION') || defined('AIOSEO_VERSION');
}
add_filter('pre_get_document_title', function ($title) {
    if (is_front_page() && !mma_has_seo_plugin() && ($t = trim((string) mma_opt('seo_title')))) return $t;
    return $title;
});
add_action('wp_head', function () {
    echo '<meta name="theme-color" content="#102B46">' . "\n";
    if (is_front_page() && !mma_has_seo_plugin() && ($desc = trim((string) mma_opt('seo_description')))) {
        echo '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    }
}, 1);

/* Главная редактируется полями ACF — классический экран без блочного редактора */
add_filter('use_block_editor_for_post', function ($use, $post) {
    if ($post && (int) $post->ID === mma_front_id()) return false;
    return $use;
}, 10, 2);

/* SVG-логотипы партнёров: загрузка только для тех, кому и так разрешён любой HTML (администратор) */
add_filter('upload_mimes', function ($mimes) {
    if (current_user_can('unfiltered_html')) $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
    if (current_user_can('unfiltered_html') && strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'svg') {
        $data['ext'] = 'svg';
        $data['type'] = 'image/svg+xml';
        $data['proper_filename'] = $filename;
    }
    return $data;
}, 10, 4);

/* Ссылка на политику конфиденциальности (страница WordPress «Конфиденциальность», если опубликована) */
function mma_privacy_url() {
    $url = get_privacy_policy_url();
    return $url ?: '';
}
