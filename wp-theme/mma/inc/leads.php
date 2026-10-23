<?php
/**
 * Приём заявок со всех форм сайта (admin-ajax, action=mma_lead).
 * Каждая заявка сохраняется в админке («Заявки») и отправляется на e-mail из «Настроек сайта».
 * Защита от спама: скрытое поле-ловушка, минимальное время заполнения, не больше 5 заявок с IP за 10 минут.
 */
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    register_post_type('mma_lead', [
        'labels' => [
            'name'          => 'Заявки',
            'singular_name' => 'Заявка',
            'menu_name'     => 'Заявки',
            'all_items'     => 'Все заявки',
            'edit_item'     => 'Заявка',
            'search_items'  => 'Найти заявку',
            'not_found'     => 'Заявок пока нет',
        ],
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'menu_position'   => 3,
        'menu_icon'       => 'dashicons-email-alt',
        'supports'        => ['title', 'editor'],
        'capability_type' => 'post',
        'capabilities'    => ['create_posts' => 'do_not_allow'],
        'map_meta_cap'    => true,
        'show_in_rest'    => false,
    ]);
});

/* Колонки списка заявок */
add_filter('manage_mma_lead_posts_columns', function ($cols) {
    return ['cb' => $cols['cb'], 'title' => 'Заявка', 'mma_service' => 'Услуга', 'mma_form' => 'Форма', 'date' => 'Дата'];
});
add_action('manage_mma_lead_posts_custom_column', function ($col, $id) {
    if ($col === 'mma_service') echo esc_html(get_post_meta($id, '_mma_service', true) ?: '—');
    if ($col === 'mma_form') echo esc_html(mma_lead_form_label(get_post_meta($id, '_mma_form', true)));
}, 10, 2);

function mma_lead_form_label($form) {
    $labels = ['modal' => 'Попап «Оставить заявку»', 'cta' => 'Блок «Нужна помощь…»', 'feedback' => 'Обратная связь'];
    return $labels[$form] ?? '—';
}

add_action('wp_ajax_mma_lead', 'mma_handle_lead');
add_action('wp_ajax_nopriv_mma_lead', 'mma_handle_lead');

function mma_handle_lead() {
    $p = wp_unslash($_POST);

    // Ловушка: поле скрыто от людей, боты его заполняют — делаем вид, что всё хорошо
    if (!empty($p['website'])) wp_send_json_success();
    // Форму заполнили быстрее чем за 2 секунды после загрузки — бот
    $started = isset($p['started']) ? (int) $p['started'] : 0;
    if ($started && (time() * 1000 - $started) < 2000) wp_send_json_success();

    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
    $rate_key = 'mma_lead_rate_' . md5($ip);
    $count = (int) get_transient($rate_key);
    if ($count >= 5) {
        wp_send_json_error(['message' => 'Слишком много заявок подряд. Позвоните нам или попробуйте позже.'], 429);
    }

    $form    = in_array($p['form'] ?? '', ['modal', 'cta', 'feedback'], true) ? $p['form'] : 'modal';
    $name    = mb_substr(sanitize_text_field($p['name'] ?? ''), 0, 100);
    $phone_d = preg_replace('/\D+/', '', (string) ($p['phone'] ?? ''));
    if (strpos($phone_d, '998') === 0) $phone_d = substr($phone_d, 3);
    $service = mb_substr(sanitize_text_field($p['service'] ?? ''), 0, 150);
    $message = mb_substr(sanitize_textarea_field($p['message'] ?? ''), 0, 2000);
    $consent = !empty($p['consent']);
    $page    = esc_url_raw($p['page'] ?? '');

    if ($name === '' || strlen($phone_d) !== 9 || !$consent) {
        wp_send_json_error(['message' => 'Заполните имя, телефон и подтвердите согласие на обработку данных.'], 400);
    }
    if ($service !== '' && !in_array($service, mma_service_options(), true)) $service = '';

    $phone = sprintf('+998 (%s) %s-%s-%s', substr($phone_d, 0, 2), substr($phone_d, 2, 3), substr($phone_d, 5, 2), substr($phone_d, 7, 2));

    $rows = [
        'Имя'     => $name,
        'Телефон' => $phone,
        'Услуга'  => $service ?: '—',
        'Сообщение' => $message ?: '—',
        'Форма'   => mma_lead_form_label($form),
        'Страница' => $page ?: home_url('/'),
        'Время'   => wp_date('d.m.Y H:i'),
    ];
    $text = '';
    foreach ($rows as $k => $v) $text .= $k . ': ' . $v . "\n";

    $post_id = wp_insert_post([
        'post_type'    => 'mma_lead',
        'post_status'  => 'private',
        'post_title'   => $name . ', ' . $phone,
        'post_content' => $text,
    ]);
    if ($post_id && !is_wp_error($post_id)) {
        update_post_meta($post_id, '_mma_service', $service);
        update_post_meta($post_id, '_mma_form', $form);
        update_post_meta($post_id, '_mma_phone', '+998' . $phone_d);
    }

    $to = array_filter(array_map('trim', explode(',', (string) mma_opt('lead_emails'))), 'is_email');
    if (!$to) $to = [get_option('admin_email')];
    $subject = 'Заявка с сайта: ' . $name . ($service ? ' — ' . $service : '');
    wp_mail($to, $subject, $text . "\nВсе заявки: " . admin_url('edit.php?post_type=mma_lead') . "\n");

    set_transient($rate_key, $count + 1, 10 * MINUTE_IN_SECONDS);
    wp_send_json_success(['message' => 'Спасибо! Заявка отправлена — мы свяжемся с вами.']);
}
