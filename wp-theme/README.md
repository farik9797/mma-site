# Тема WordPress «MODERN MEDICINE ALLIANCE» (mma17.uz)

Лендинг перенесён 1:1 со статического макета (farik9797.github.io/mma-site) в тему `mma/` + ACF Pro.

## Структура
- `mma/` — тема (загружается в `wp-content/themes/mma`).
  - `inc/defaults.php` — все тексты и фото макета; пустое поле ACF на сайте заменяется ими.
  - `inc/acf-fields.php` — поля: «Настройки сайта» (Options Page) и «Главная страница» (вкладки по блокам, Repeater).
  - `inc/leads.php` — приём заявок со всех форм (admin-ajax `mma_lead`): сохраняются в «Заявки» + письмо на e-mail из настроек.
  - `inc/setup-content.php` — первичная настройка при первом входе в админку: включает ACF Pro, создаёт «Главную», ЧПУ, загружает фото в медиатеку, заполняет поля.
  - `template-parts/` — блоки главной и попапы.
  - `assets/css/main.css` — собранный Tailwind; `assets/js/vendor/` — Lucide 0.460.0, GSAP 3.12.5 (без CDN).
- `src/input.css` + `tailwind.config.js` — исходник стилей.

## Сборка CSS (после правки классов в PHP/JS)
```bash
cd wp-theme && npx tailwindcss@3.4.16 -c tailwind.config.js -i src/input.css -o mma/assets/css/main.css --minify
```
Версия файлов в подключении — по времени изменения, кэш браузера сбрасывается сам.

## Установка на хостинг
1. Залить WordPress (ru_RU) + `wp-content/themes/mma` + `wp-content/plugins/advanced-custom-fields-pro` + `wp-config.php` (в нём `WP_DEFAULT_THEME = mma`, префикс таблиц `mma_`).
2. Открыть `https://домен/wp-admin/install.php`, ввести название сайта, логин/пароль администратора, e-mail (делает владелец).
3. Войти в админку — тема сама включит ACF Pro и заполнит главную.
4. «Настройки сайта» — проверить телефон, Telegram, e-mail для заявок.
5. «Настройки → Конфиденциальность» — опубликовать политику (ссылка появится в формах и подвале).
6. Почта: если письма о заявках не приходят — поставить WP Mail SMTP (заявки в любом случае сохраняются в «Заявки»).

## Локальная проверка
`wp-local/` (в .gitignore) — WordPress на SQLite, запуск: `.claude/launch.json` → `mma-wp` (http://localhost:8890).
