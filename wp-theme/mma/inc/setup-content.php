<?php
/**
 * Первичная настройка сайта при первом входе администратора в админку (SSH/WP-CLI на хостинге нет):
 *  1) включает ACF Pro, если он загружен в plugins, но не активен;
 *  2) создаёт страницу «Главная» и делает её главной, включает ЧПУ, часовой пояс Ташкента;
 *  3) загружает фото из темы в медиатеку и заполняет поля ACF текстами из макета (inc/defaults.php).
 * Выполняется один раз (опция mma_setup_done). Повторить: удалить опцию mma_setup_done.
 */
if (!defined('ABSPATH')) exit;

add_action('admin_init', 'mma_maybe_setup', 20);

function mma_maybe_setup() {
    if (wp_doing_ajax() || !current_user_can('manage_options')) return;
    if (get_option('mma_setup_done')) return;

    // 1. ACF Pro
    if (!function_exists('acf_add_local_field_group')) {
        $plugin = 'advanced-custom-fields-pro/acf.php';
        if (!get_option('mma_acf_tried') && file_exists(WP_PLUGIN_DIR . '/' . $plugin)) {
            update_option('mma_acf_tried', 1, false);
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            $res = activate_plugin($plugin);
            if (!is_wp_error($res)) {
                wp_safe_redirect(admin_url('index.php')); // поля ACF доступны со следующего запроса
                exit;
            }
        }
        return; // без ACF поля не заполнить — сайт покажет тексты макета
    }

    // 2. Главная страница и базовые настройки
    $front = (int) get_option('page_on_front');
    if (!$front || get_post_status($front) !== 'publish') {
        $front = wp_insert_post([
            'post_type'   => 'page',
            'post_status' => 'publish',
            'post_title'  => 'Главная',
            'post_name'   => 'glavnaya',
        ]);
        if (is_wp_error($front) || !$front) return;
        update_option('show_on_front', 'page');
        update_option('page_on_front', $front);
    }
    if (!get_option('permalink_structure')) {
        mma_setup_permalinks();
    }
    if (!get_option('timezone_string')) update_option('timezone_string', 'Asia/Tashkent');
    update_option('date_format', 'd.m.Y');
    $tagline = get_option('blogdescription');
    if ($tagline === '' || $tagline === 'Ещё один сайт на WordPress' || $tagline === 'Just another WordPress site') {
        update_option('blogdescription', 'Охрана труда и аттестация рабочих мест в Ташкенте');
    }
    // Стандартные «Привет, мир!» и «Пример страницы» — в корзину (только если не менялись)
    foreach ([1 => 'post', 2 => 'page'] as $id => $type) {
        $p = get_post($id);
        if ($p && $p->post_type === $type && $p->post_status === 'publish' && $p->post_modified === $p->post_date) {
            wp_trash_post($id);
        }
    }

    // 3. Поля ACF: тексты макета + фото в медиатеку
    $d = mma_defaults();
    foreach ($d['home'] as $name => $value) {
        update_field('field_mma_' . $name, mma_setup_media($value, $name), $front);
    }
    foreach ($d['options'] as $name => $value) {
        update_field('field_mma_' . $name, $value, 'option');
    }

    update_option('mma_setup_done', MMA_VER, false);
    add_action('admin_notices', function () {
        echo '<div class="notice notice-success"><p><strong>MODERN MEDICINE ALLIANCE:</strong> главная страница создана и заполнена текстами макета. Редактирование — «Страницы → Главная» и «Настройки сайта».</p></div>';
    });
}

/* ЧПУ /%postname%/. На Apache WordPress записывает .htaccess — в нём же правило, передающее
   заголовок Authorization в PHP (нужно для паролей приложений и REST API).
   Если после записи /wp-json/ не открывается (ЧПУ сервер не поддерживает) — оставляем простые ссылки, чтобы не было 404. */
function mma_setup_permalinks() {
    global $wp_rewrite;
    update_option('permalink_structure', '/%postname%/');
    $wp_rewrite->init();
    flush_rewrite_rules(true);

    $res = wp_remote_get(home_url('/wp-json/'), ['timeout' => 8, 'sslverify' => false, 'redirection' => 2]);
    $ok = !is_wp_error($res) && (int) wp_remote_retrieve_response_code($res) === 200
        && strpos((string) wp_remote_retrieve_header($res, 'content-type'), 'json') !== false;
    if (!$ok) {
        update_option('permalink_structure', '');
        $wp_rewrite->init();
        flush_rewrite_rules(false);
    }
    update_option('mma_permalinks_check', $ok ? 'pretty' : 'plain', false);
}

/* Рекурсивно меняет пути к картинкам темы (img/…) на ID вложений в медиатеке */
function mma_setup_media($value, $key = '') {
    if (is_array($value)) {
        foreach ($value as $k => $v) $value[$k] = mma_setup_media($v, is_string($k) ? $k : $key);
        return $value;
    }
    if (is_string($value) && preg_match('~^img/.+\.(jpe?g|png|svg|webp)$~i', $value)) {
        return mma_import_theme_image($value) ?: '';
    }
    return $value;
}

function mma_import_theme_image($rel) {
    $map = get_option('mma_imported_images', []);
    if (!empty($map[$rel]) && get_post($map[$rel])) return (int) $map[$rel];

    $src = MMA_DIR . '/assets/' . $rel;
    if (!is_readable($src)) return 0;

    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';

    $upload = wp_upload_bits(basename($rel), null, file_get_contents($src));
    if (!empty($upload['error'])) return 0;

    $type = wp_check_filetype($upload['file']);
    $id = wp_insert_attachment([
        'post_mime_type' => $type['type'] ?: 'image/jpeg',
        'post_title'     => pathinfo($rel, PATHINFO_FILENAME),
        'post_status'    => 'inherit',
    ], $upload['file']);
    if (is_wp_error($id) || !$id) return 0;

    if (($type['ext'] ?? '') !== 'svg') {
        wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
    }
    $alts = mma_default_alts();
    if (!empty($alts[$rel])) update_post_meta($id, '_wp_attachment_image_alt', $alts[$rel]);

    $map[$rel] = $id;
    update_option('mma_imported_images', $map, false);
    return (int) $id;
}
