<?php
/**
 * Вспомогательные функции: чтение полей ACF с запасными значениями из макета, картинки, телефоны, иконки.
 */
if (!defined('ABSPATH')) exit;

function mma_front_id() {
    return (int) get_option('page_on_front');
}

function mma_has_value($v) {
    return !($v === null || $v === '' || $v === false || $v === []);
}

/* Поле главной страницы; пустое — значение из макета */
function mma_get($name) {
    if (function_exists('get_field') && ($id = mma_front_id())) {
        $v = get_field($name, $id);
        if (mma_has_value($v)) return $v;
    }
    $d = mma_defaults();
    return $d['home'][$name] ?? '';
}

/* Поле «Настроек сайта»; пустое — значение из макета */
function mma_opt($name) {
    if (function_exists('get_field')) {
        $v = get_field($name, 'option');
        if (mma_has_value($v)) return $v;
    }
    $d = mma_defaults();
    return $d['options'][$name] ?? '';
}

/* Текст с переносами строк из textarea: экранируем и меняем \n на $br */
function mma_lines($text, $br = '<br>') {
    $text = trim(str_replace("\r", '', (string) $text));
    return str_replace("\n", $br, esc_html($text));
}

/* Текст без переносов (для заголовков в попапах, aria-label) */
function mma_flat($text) {
    return trim(preg_replace('/\s*\R\s*/u', ' ', (string) $text));
}

/* Список из textarea: одна строка — один пункт */
function mma_list($text) {
    return array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $text)), 'strlen'));
}

/* Иконка Lucide: <i data-lucide> превращается в SVG скриптом lucide (assets/js/vendor) */
function mma_icon($name, $class = '', $extra = '') {
    if (!$name) return '';
    return '<i data-lucide="' . esc_attr($name) . '"' . ($class ? ' class="' . esc_attr($class) . '"' : '') . ($extra ? ' ' . $extra : '') . '></i>';
}

/* Знак Telegram — Simple Icons (simple-icons@13.21.0), путь без изменений. Не перерисовывать. */
function mma_tg_svg($class = '') {
    return '<svg' . ($class ? ' class="' . esc_attr($class) . '"' : '') . ' viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>';
}

/* tel:-ссылка из номера в любом формате */
function mma_tel($phone) {
    $d = preg_replace('/\D+/', '', (string) $phone);
    return $d ? 'tel:+' . $d : '';
}

/* Ссылка на Telegram: номер → t.me/+998…, @username → t.me/username, готовая ссылка — как есть */
function mma_tg_link($value) {
    $value = trim((string) $value);
    if ($value === '') return '';
    if (preg_match('~^https?://~i', $value)) return $value;
    if ($value[0] === '@') return 'https://t.me/' . substr($value, 1);
    $d = preg_replace('/\D+/', '', $value);
    return $d ? 'https://t.me/+' . $d : '';
}

function mma_telegrams() {
    $rows = mma_opt('telegram');
    $out = [];
    foreach ((array) $rows as $row) {
        $num = is_array($row) ? ($row['number'] ?? '') : $row;
        $link = mma_tg_link($num);
        if ($link) $out[] = ['label' => trim($num), 'link' => $link];
    }
    return $out;
}

/* Картинка: ID вложения (из ACF) или путь к файлу темы (значение по умолчанию) */
function mma_img($img, $attrs = [], $size = 'large') {
    $attrs += ['alt' => '', 'loading' => 'lazy', 'decoding' => 'async'];
    if (is_array($img) && !empty($img['ID'])) $img = (int) $img['ID'];
    if (is_numeric($img) && (int) $img > 0) {
        $id = (int) $img;
        $att_alt = get_post_meta($id, '_wp_attachment_image_alt', true);
        if (get_post_mime_type($id) === 'image/svg+xml') {
            // SVG у WordPress нет размеров — выводим как есть
            $url = wp_get_attachment_url($id);
            if (!$url) return '';
            if ($att_alt) $attrs['alt'] = $att_alt;
            unset($attrs['sizes']);
            $html = '<img src="' . esc_url($url) . '"';
            foreach ($attrs as $k => $v) if ($v !== null && $v !== false) $html .= ' ' . $k . '="' . esc_attr($v) . '"';
            return $html . '>';
        }
        if ($att_alt) unset($attrs['alt']);
        foreach ($attrs as $k => $v) if ($v === null || $v === false) unset($attrs[$k]);
        return wp_get_attachment_image($id, $size, false, $attrs);
    }
    if (!is_string($img) || $img === '') return '';
    $rel = ltrim($img, '/');
    $file = MMA_DIR . '/assets/' . $rel;
    if (!isset($attrs['width']) && is_readable($file) && ($dim = @getimagesize($file))) {
        $attrs['width'] = $dim[0];
        $attrs['height'] = $dim[1];
    }
    $alts = mma_default_alts();
    if ($attrs['alt'] === '' && isset($alts[$rel]) && empty($attrs['aria-hidden'])) $attrs['alt'] = $alts[$rel];
    unset($attrs['sizes']);
    $html = '<img src="' . esc_url(MMA_URI . '/assets/' . $rel) . '"';
    foreach ($attrs as $k => $v) {
        if ($v === null || $v === false) continue;
        $html .= ' ' . $k . '="' . esc_attr($v) . '"';
    }
    return $html . '>';
}

/* URL картинки (для фона и т. п.) */
function mma_img_url($img, $size = 'large') {
    if (is_array($img) && !empty($img['ID'])) $img = (int) $img['ID'];
    if (is_numeric($img) && (int) $img > 0) return (string) wp_get_attachment_image_url((int) $img, $size);
    return is_string($img) && $img !== '' ? MMA_URI . '/assets/' . ltrim($img, '/') : '';
}

/* ---------- Услуги ---------- */

function mma_services() {
    static $list = null;
    if ($list !== null) return $list;
    $list = [];
    foreach ((array) mma_get('services') as $i => $s) {
        if (!is_array($s) || empty($s['title'])) continue;
        $slug = sanitize_title($s['slug'] ?? '');
        if ($slug === '') $slug = 'service-' . ($i + 1);
        $s['slug'] = $slug;
        $s['form_name'] = trim((string) ($s['form_name'] ?? '')) ?: mma_flat($s['title']);
        $list[] = $s;
    }
    return $list;
}

/* Варианты «Вида услуги» в формах заявки */
function mma_service_options() {
    $names = array_map(function ($s) { return $s['form_name']; }, mma_services());
    $names[] = 'Другой вопрос';
    return array_values(array_unique($names));
}

/* Куда ведёт кнопка: '#services' / '#about' — прокрутка к блоку, 'svc:slug' — попап услуги, 'lead' — форма заявки */
function mma_target_attrs($target, $fallback = '#services') {
    $target = trim((string) $target);
    if ($target === '') $target = $fallback;
    if (strpos($target, 'svc:') === 0) {
        return 'href="#services" data-service-open="' . esc_attr(substr($target, 4)) . '" aria-haspopup="dialog"';
    }
    if ($target === 'lead') return 'href="#cta" data-modal-open';
    return 'href="' . esc_attr($target) . '"';
}

/* Выбор цели кнопки в админке */
function mma_target_choices($with_lead = false) {
    $choices = [
        '#services' => 'Прокрутить к блоку «Услуги»',
        '#about'    => 'Прокрутить к блоку «О компании»',
        '#contacts' => 'Прокрутить к контактам',
    ];
    if ($with_lead) $choices['lead'] = 'Открыть форму заявки';
    foreach (mma_services_raw_for_admin() as $slug => $title) {
        $choices['svc:' . $slug] = 'Попап услуги: ' . $title;
    }
    return $choices;
}

/* Услуги для выпадающих списков в админке — читаем мету напрямую, без get_field (нет рекурсии в load_field) */
function mma_services_raw_for_admin() {
    $out = [];
    $id = mma_front_id();
    $count = $id ? (int) get_post_meta($id, 'services', true) : 0;
    if ($count > 0) {
        for ($i = 0; $i < $count; $i++) {
            $title = mma_flat(get_post_meta($id, "services_{$i}_title", true));
            $slug = sanitize_title(get_post_meta($id, "services_{$i}_slug", true)) ?: 'service-' . ($i + 1);
            if ($title !== '') $out[$slug] = $title;
        }
    }
    if (!$out) {
        foreach (mma_defaults()['home']['services'] as $s) $out[$s['slug']] = mma_flat($s['title']);
    }
    return $out;
}

function mma_service_name_choices() {
    $id = mma_front_id();
    $out = ['' => '— без выбора —'];
    $count = $id ? (int) get_post_meta($id, 'services', true) : 0;
    for ($i = 0; $i < $count; $i++) {
        $name = trim((string) get_post_meta($id, "services_{$i}_form_name", true)) ?: mma_flat(get_post_meta($id, "services_{$i}_title", true));
        if ($name !== '') $out[$name] = $name;
    }
    if ($count === 0) {
        foreach (mma_defaults()['home']['services'] as $s) $out[$s['form_name']] = $s['form_name'];
    }
    return $out;
}

/* Иконки Lucide, доступные в админке (имена проверены по lucide@0.460.0) */
function mma_icon_choices() {
    return [
        'shield-check'    => 'Щит с галочкой',
        'shield'          => 'Щит',
        'users'           => 'Люди',
        'user'            => 'Человек',
        'user-check'      => 'Человек с галочкой',
        'calendar-days'   => 'Календарь',
        'clock'           => 'Часы',
        'zap'             => 'Молния (оперативность)',
        'clipboard-check' => 'Планшет с галочкой',
        'clipboard-list'  => 'Планшет со списком',
        'file-check'      => 'Документ с галочкой',
        'file-text'       => 'Документ',
        'file-badge'      => 'Документ с печатью',
        'handshake'       => 'Рукопожатие',
        'map-pin'         => 'Метка на карте',
        'ruler'           => 'Линейка (замеры)',
        'gauge'           => 'Измерительный прибор',
        'circle-check'    => 'Галочка в круге',
        'badge-check'     => 'Значок с галочкой',
        'award'           => 'Награда',
        'wallet'          => 'Кошелёк (экономия)',
        'graduation-cap'  => 'Шапка выпускника (обучение)',
        'book-open'       => 'Открытая книга',
        'presentation'    => 'Презентация',
        'hard-hat'        => 'Каска',
        'building-2'      => 'Здание',
        'factory'         => 'Завод',
        'briefcase'       => 'Портфель',
        'stethoscope'     => 'Стетоскоп (медосмотры)',
        'heart-pulse'     => 'Пульс',
        'scale'           => 'Весы (право)',
        'search-check'    => 'Проверка (аудит)',
        'target'          => 'Цель',
        'thumbs-up'       => 'Палец вверх',
        'headset'         => 'Гарнитура (поддержка)',
        'message-circle'  => 'Сообщение',
        'phone'           => 'Телефон',
        'mail'            => 'Почта',
    ];
}
