<?php
/* Главная — лендинг. Блоки в template-parts/, тексты — поля ACF «Главная страница» (пустые — из макета) */
if (!defined('ABSPATH')) exit;
get_header();
?>
  <main id="main">
    <?php
    get_template_part('template-parts/hero');           // первый экран + цифры
    get_template_part('template-parts/services');
    get_template_part('template-parts/about');          // о компании + как мы работаем + почему мы
    get_template_part('template-parts/partners-news');
    get_template_part('template-parts/cta-contacts');
    ?>
  </main>
<?php
get_footer();
