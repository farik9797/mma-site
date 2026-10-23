<?php
/**
 * Поля ACF Pro: «Настройки сайта» (Options Page) и «Главная страница» (вкладки по блокам).
 * Повторяющиеся наборы — Repeater. Пустое поле на сайте заменяется текстом из макета (inc/defaults.php).
 */
if (!defined('ABSPATH')) exit;

add_action('acf/init', 'mma_register_acf');

/* Короткие конструкторы полей: ключ уникален, имя — как в inc/defaults.php */
function mma_f($name, $label, $type = 'text', $extra = []) {
    return array_merge(['key' => 'field_mma_' . $name, 'name' => $name, 'label' => $label, 'type' => $type], $extra);
}
function mma_sf($parent, $name, $label, $type = 'text', $extra = []) {
    return array_merge(['key' => 'field_mma_' . $parent . '__' . $name, 'name' => $name, 'label' => $label, 'type' => $type], $extra);
}
function mma_tab($label) {
    static $n = 0;
    $n++;
    return ['key' => 'field_mma_tab_' . $n, 'label' => $label, 'name' => '', 'type' => 'tab', 'placement' => 'top'];
}
function mma_image_field_args($extra = []) {
    return array_merge(['return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all'], $extra);
}
function mma_icon_field($parent, $label = 'Иконка', $extra = []) {
    return mma_sf($parent, 'icon', $label, 'select', array_merge([
        'choices' => mma_icon_choices(), 'allow_null' => 1, 'ui' => 1, 'return_format' => 'value',
    ], $extra));
}

function mma_register_acf() {
    if (!function_exists('acf_add_local_field_group')) return;
    mma_acf_options();
    mma_acf_home();
}

/* ---------- Настройки сайта ---------- */
function mma_acf_options() {
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => 'Настройки сайта',
            'menu_title' => 'Настройки сайта',
            'menu_slug'  => 'mma-settings',
            'capability' => 'edit_pages',
            'icon_url'   => 'dashicons-admin-generic',
            'position'   => 59,
            'redirect'   => false,
        ]);
    }

    acf_add_local_field_group([
        'key'    => 'group_mma_options',
        'title'  => 'Контакты и общие настройки',
        'fields' => [
            mma_tab('Контакты'),
            mma_f('phone', 'Телефон для звонков', 'text', ['instructions' => 'Как показывать на сайте, например +998 93 397-80-68. Ссылка «позвонить» собирается из цифр.']),
            mma_f('telegram', 'Telegram', 'repeater', [
                'instructions' => 'Номер (+998 …) или @имя_пользователя. Если номеров несколько, кнопка Telegram предложит выбор.',
                'layout' => 'table', 'button_label' => 'Добавить номер',
                'sub_fields' => [mma_sf('telegram', 'number', 'Номер или @имя')],
            ]),
            mma_f('email', 'E-mail', 'email'),
            mma_f('address', 'Адрес', 'textarea', ['rows' => 2, 'new_lines' => '', 'instructions' => 'Перенос строки сохраняется.']),
            mma_f('map_query', 'Адрес для карты Google', 'text', ['instructions' => 'Что искать на карте, например «Ташкент, улица Лутфий, 6».']),

            mma_tab('Заявки'),
            mma_f('lead_emails', 'Куда отправлять заявки', 'text', ['instructions' => 'Один или несколько e-mail через запятую. Все заявки также сохраняются в разделе «Заявки».']),

            mma_tab('Подвал'),
            mma_f('footer_text', 'Текст под логотипом', 'textarea', ['rows' => 2, 'new_lines' => '']),
            mma_f('copyright', 'Копирайт', 'text', ['instructions' => '{year} заменяется на текущий год.']),

            mma_tab('SEO главной'),
            mma_f('seo_title', 'Title главной', 'text', ['instructions' => 'Не используется, если установлен SEO-плагин (Yoast, Rank Math) — тогда заполняйте в нём.']),
            mma_f('seo_description', 'Meta description главной', 'textarea', ['rows' => 3, 'new_lines' => '']),
        ],
        'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'mma-settings']]],
    ]);
}

/* ---------- Главная страница ---------- */
function mma_acf_home() {
    $ta = function ($rows = 2) { return ['rows' => $rows, 'new_lines' => '']; };
    $lines = 'Одна строка — один пункт.';
    $br = 'Перенос строки (Enter) сохраняется на сайте.';

    acf_add_local_field_group([
        'key'    => 'group_mma_home',
        'title'  => 'Главная страница — блоки',
        'style'  => 'seamless',
        'hide_on_screen' => ['the_content', 'excerpt', 'discussion', 'comments', 'revisions', 'slug', 'author', 'format', 'featured_image', 'categories', 'tags', 'send-trackbacks'],
        'fields' => [
            /* Первый экран */
            mma_tab('Первый экран'),
            mma_f('hero_slides', 'Слайды', 'repeater', [
                'instructions' => 'Слайды листаются автоматически. Заголовок первого слайда — главный заголовок страницы (H1).',
                'layout' => 'block', 'button_label' => 'Добавить слайд', 'min' => 1, 'collapsed' => 'field_mma_hero_slides__title',
                'sub_fields' => [
                    mma_sf('hero_slides', 'image', 'Фото', 'image', mma_image_field_args(['instructions' => 'Широкое фото, от 2000 px по ширине. На десктопе текст лежит на левой затемнённой части.'])),
                    mma_sf('hero_slides', 'pos_mobile', 'Кадр на телефоне', 'text', ['instructions' => 'Какую часть фото показывать на узком экране: «80% center», «center», «right center».', 'wrapper' => ['width' => 40]]),
                    mma_sf('hero_slides', 'title', 'Заголовок', 'textarea', $ta(3) + ['instructions' => $br, 'wrapper' => ['width' => 60]]),
                    mma_sf('hero_slides', 'text', 'Текст', 'textarea', $ta(3)),
                    mma_sf('hero_slides', 'btn1_text', 'Кнопка 1 (открывает форму заявки)', 'text', ['wrapper' => ['width' => 34]]),
                    mma_sf('hero_slides', 'btn2_text', 'Кнопка 2', 'text', ['wrapper' => ['width' => 33]]),
                    mma_sf('hero_slides', 'btn2_target', 'Кнопка 2 ведёт', 'select', ['choices' => [], 'wrapper' => ['width' => 33]]),
                    mma_sf('hero_slides', 'card_style', 'Плашка: вид', 'select', ['choices' => ['facts' => 'Цифры', 'list' => 'Список с иконками'], 'wrapper' => ['width' => 25]]),
                    mma_sf('hero_slides', 'card_title', 'Плашка: подпись', 'text', ['wrapper' => ['width' => 25]]),
                    mma_sf('hero_slides', 'card_link_text', 'Плашка: текст ссылки', 'text', ['wrapper' => ['width' => 25]]),
                    mma_sf('hero_slides', 'card_link_target', 'Плашка: ссылка ведёт', 'select', ['choices' => [], 'wrapper' => ['width' => 25]]),
                    mma_sf('hero_slides', 'card_items', 'Плашка: пункты (до 3)', 'repeater', [
                        'instructions' => 'Плашка видна на компьютере. Для вида «Цифры» заполните число и подпись, для «Списка» — иконку и текст.',
                        'layout' => 'table', 'max' => 3, 'button_label' => 'Добавить пункт',
                        'sub_fields' => [
                            mma_icon_field('hero_card_items', 'Иконка (для списка)'),
                            mma_sf('hero_card_items', 'value', 'Число'),
                            mma_sf('hero_card_items', 'suffix', 'Мелко после числа'),
                            mma_sf('hero_card_items', 'text', 'Текст'),
                        ],
                    ]),
                ],
            ]),

            /* Цифры */
            mma_tab('Цифры'),
            mma_f('stats', 'Показатели под первым экраном', 'repeater', [
                'layout' => 'table', 'button_label' => 'Добавить показатель', 'max' => 4,
                'sub_fields' => [
                    mma_icon_field('stats'),
                    mma_sf('stats', 'value', 'Значение'),
                    mma_sf('stats', 'text', 'Подпись', 'textarea', $ta(2) + ['instructions' => $br]),
                ],
            ]),

            /* Услуги */
            mma_tab('Услуги'),
            mma_f('services_title', 'Заголовок блока'),
            mma_f('services_lead', 'Подзаголовок', 'textarea', $ta(2)),
            mma_f('services', 'Услуги', 'repeater', [
                'instructions' => 'Карточка на странице + попап «Подробнее». Названия услуг попадают в список «Вид услуги» в формах заявки.',
                'layout' => 'block', 'button_label' => 'Добавить услугу', 'collapsed' => 'field_mma_services__title',
                'sub_fields' => [
                    mma_sf('services', 'title', 'Название', 'text', ['wrapper' => ['width' => 50]]),
                    mma_sf('services', 'slug', 'Код для ссылок (латиницей)', 'text', ['instructions' => 'Например training. Не меняйте без необходимости — на него ссылаются кнопки.', 'wrapper' => ['width' => 25]]),
                    mma_sf('services', 'form_name', 'Название в форме заявки', 'text', ['instructions' => 'Короткое, если название длинное.', 'wrapper' => ['width' => 25]]),
                    mma_sf('services', 'image', 'Фото', 'image', mma_image_field_args(['wrapper' => ['width' => 50]])),
                    mma_sf('services', 'image_pos', 'Кадр фото', 'text', ['instructions' => 'Какая часть фото видна: «center», «center 40%», «center top».', 'wrapper' => ['width' => 50]]),
                    mma_sf('services', 'short_text', 'Краткое описание (карточка)', 'textarea', $ta(2)),
                    mma_sf('services', 'bullets', 'Пункты в карточке', 'textarea', $ta(3) + ['instructions' => $lines]),
                    mma_sf('services', 'popup_lead', 'Попап: описание', 'textarea', $ta(3)),
                    mma_sf('services', 'col1_title', 'Попап: заголовок левой колонки', 'text', ['wrapper' => ['width' => 50]]),
                    mma_sf('services', 'col2_title', 'Попап: заголовок правой колонки', 'text', ['wrapper' => ['width' => 50]]),
                    mma_sf('services', 'col1_items', 'Попап: пункты левой колонки', 'textarea', $ta(5) + ['instructions' => $lines, 'wrapper' => ['width' => 50]]),
                    mma_sf('services', 'col2_style', 'Попап: вид правой колонки', 'select', ['choices' => ['numbered' => 'Нумерованные шаги', 'icons' => 'Пункты с иконками'], 'wrapper' => ['width' => 50]]),
                    mma_sf('services', 'col2_items', 'Попап: пункты правой колонки', 'repeater', [
                        'layout' => 'table', 'button_label' => 'Добавить пункт',
                        'sub_fields' => [
                            mma_icon_field('services_col2', 'Иконка (для вида «с иконками»)'),
                            mma_sf('services_col2', 'text', 'Текст'),
                        ],
                    ]),
                    mma_sf('services', 'facts', 'Попап: цифры (до 3)', 'repeater', [
                        'layout' => 'table', 'max' => 3, 'button_label' => 'Добавить',
                        'sub_fields' => [mma_sf('services_facts', 'value', 'Число'), mma_sf('services_facts', 'text', 'Подпись')],
                    ]),
                    mma_sf('services', 'price_label', 'Попап: подпись внизу', 'text', ['wrapper' => ['width' => 50]]),
                    mma_sf('services', 'price_value', 'Попап: значение внизу', 'text', ['wrapper' => ['width' => 50]]),
                ],
            ]),

            /* О компании */
            mma_tab('О компании'),
            mma_f('about_image', 'Фото', 'image', mma_image_field_args(['wrapper' => ['width' => 50]])),
            mma_f('about_image_pos', 'Кадр фото', 'text', ['instructions' => 'Например «18% 70%» или «center».', 'wrapper' => ['width' => 50]]),
            mma_f('about_eyebrow', 'Надзаголовок', 'text', ['wrapper' => ['width' => 50]]),
            mma_f('about_title', 'Заголовок', 'textarea', $ta(2) + ['instructions' => $br, 'wrapper' => ['width' => 50]]),
            mma_f('about_text', 'Текст', 'textarea', $ta(4)),
            mma_f('about_facts', 'Факты', 'repeater', [
                'layout' => 'table', 'button_label' => 'Добавить факт',
                'sub_fields' => [mma_icon_field('about_facts'), mma_sf('about_facts', 'text', 'Текст')],
            ]),
            mma_f('about_btn_text', 'Текст кнопки', 'text', ['instructions' => 'Пусто — кнопки нет.', 'wrapper' => ['width' => 50]]),
            mma_f('about_btn_link', 'Ссылка кнопки', 'text', ['instructions' => 'Адрес страницы или якорь блока: #services, #contacts.', 'wrapper' => ['width' => 50]]),

            /* Как мы работаем */
            mma_tab('Как мы работаем'),
            mma_f('process_title', 'Заголовок', 'text', ['wrapper' => ['width' => 50]]),
            mma_f('process_lead', 'Подзаголовок', 'text', ['wrapper' => ['width' => 50]]),
            mma_f('process_steps', 'Этапы', 'repeater', [
                'layout' => 'table', 'button_label' => 'Добавить этап', 'max' => 4,
                'sub_fields' => [
                    mma_sf('process_steps', 'title', 'Заголовок', 'textarea', $ta(2) + ['instructions' => 'Перенос строки — только на компьютере.']),
                    mma_sf('process_steps', 'text', 'Текст', 'textarea', $ta(2)),
                ],
            ]),

            /* Почему выбирают нас */
            mma_tab('Почему мы'),
            mma_f('adv_title', 'Заголовок', 'text', ['wrapper' => ['width' => 50]]),
            mma_f('adv_image', 'Фото справа (компьютер)', 'image', mma_image_field_args(['wrapper' => ['width' => 50]])),
            mma_f('adv_items', 'Преимущества', 'repeater', [
                'layout' => 'table', 'button_label' => 'Добавить', 'max' => 4,
                'sub_fields' => [
                    mma_icon_field('adv_items'),
                    mma_sf('adv_items', 'title', 'Текст', 'textarea', $ta(2) + ['instructions' => 'Перенос строки — только на компьютере.']),
                ],
            ]),

            /* Партнёры */
            mma_tab('Партнёры'),
            mma_f('partners_title', 'Заголовок', 'text', ['wrapper' => ['width' => 50]]),
            mma_f('partners_lead', 'Подзаголовок', 'text', ['wrapper' => ['width' => 50]]),
            mma_f('partners', 'Партнёры', 'repeater', [
                'instructions' => 'Без логотипа показывается название текстом. Логотипы — только официальные.',
                'layout' => 'table', 'button_label' => 'Добавить партнёра',
                'sub_fields' => [
                    mma_sf('partners', 'name', 'Название', 'textarea', $ta(2) + ['instructions' => 'Перенос строки сохраняется.']),
                    mma_sf('partners', 'logo', 'Логотип', 'image', mma_image_field_args()),
                    mma_sf('partners', 'logo_h', 'Высота логотипа, px', 'number', ['min' => 16, 'max' => 90, 'instructions' => 'На компьютере; на телефоне на 20 % меньше.']),
                    mma_sf('partners', 'keep_case', 'Не заглавными', 'true_false', ['ui' => 1]),
                ],
            ]),

            /* Новости и материалы */
            mma_tab('Новости'),
            mma_f('news_title', 'Заголовок (компьютер)', 'text', ['wrapper' => ['width' => 34]]),
            mma_f('news_title_mobile', 'Заголовок (телефон)', 'text', ['wrapper' => ['width' => 33]]),
            mma_f('news_eyebrow', 'Надпись в попапе', 'text', ['wrapper' => ['width' => 33]]),
            mma_f('news', 'Материалы', 'repeater', [
                'instructions' => 'Карточка + попап с подробностями. Дата необязательна — пустая не выводится.',
                'layout' => 'block', 'button_label' => 'Добавить материал', 'collapsed' => 'field_mma_news__title',
                'sub_fields' => [
                    mma_sf('news', 'title', 'Заголовок', 'textarea', $ta(2) + ['instructions' => 'Перенос строки — только в карточке.', 'wrapper' => ['width' => 50]]),
                    mma_sf('news', 'date', 'Дата', 'text', ['instructions' => 'Например 23.10.2026.', 'wrapper' => ['width' => 20]]),
                    mma_sf('news', 'image', 'Фото', 'image', mma_image_field_args(['wrapper' => ['width' => 30]])),
                    mma_sf('news', 'lead', 'Вводный абзац', 'textarea', $ta(3)),
                    mma_sf('news', 'list_title', 'Заголовок списка', 'text', ['wrapper' => ['width' => 50]]),
                    mma_sf('news', 'list_style', 'Вид списка', 'select', ['choices' => ['check' => 'Галочки', 'numbered' => 'Нумерованные шаги'], 'wrapper' => ['width' => 50]]),
                    mma_sf('news', 'list_items', 'Пункты списка', 'textarea', $ta(4) + ['instructions' => $lines]),
                    mma_sf('news', 'body', 'Полный текст', 'wysiwyg', ['tabs' => 'all', 'toolbar' => 'basic', 'media_upload' => 1]),
                    mma_sf('news', 'cta_service', 'Услуга в форме заявки из попапа', 'select', ['choices' => [], 'allow_null' => 1]),
                ],
            ]),

            /* Финальный призыв */
            mma_tab('Призыв и контакты'),
            mma_f('cta_title', 'Призыв: заголовок', 'textarea', $ta(3) + ['instructions' => $br, 'wrapper' => ['width' => 50]]),
            mma_f('cta_text', 'Призыв: текст', 'textarea', $ta(3) + ['wrapper' => ['width' => 50]]),
            mma_f('cta_btn', 'Призыв: текст кнопки', 'text', ['instructions' => 'На телефоне раскрывает форму, на компьютере ставит курсор в первое поле.', 'wrapper' => ['width' => 50]]),
            mma_f('cta_image', 'Призыв: фото на фоне', 'image', mma_image_field_args(['wrapper' => ['width' => 50]])),
            mma_f('contacts_title', 'Контакты: заголовок', 'text', ['wrapper' => ['width' => 34]]),
            mma_f('contacts_lead', 'Контакты: подзаголовок', 'text', ['wrapper' => ['width' => 33]]),
            mma_f('feedback_title', 'Форма обратной связи: заголовок', 'text', ['wrapper' => ['width' => 33]]),
        ],
        'location' => [[['param' => 'page_type', 'operator' => '==', 'value' => 'front_page']]],
        'menu_order' => 0,
        'position' => 'normal',
    ]);
}

/* Выпадающие списки, зависящие от услуг: заполняются при открытии формы в админке */
add_filter('acf/load_field/key=field_mma_hero_slides__btn2_target', function ($field) {
    $field['choices'] = mma_target_choices(true);
    return $field;
});
add_filter('acf/load_field/key=field_mma_hero_slides__card_link_target', function ($field) {
    $field['choices'] = mma_target_choices(true);
    return $field;
});
add_filter('acf/load_field/key=field_mma_news__cta_service', function ($field) {
    $field['choices'] = mma_service_name_choices();
    return $field;
});
