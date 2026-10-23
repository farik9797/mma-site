<?php
if (!defined('ABSPATH')) exit;
get_header();
?>
  <main id="main" class="bg-mist py-20 lg:py-28">
    <div class="container-x text-center">
      <p class="eyebrow">Ошибка 404</p>
      <h1 class="h2 mt-3">Страница не найдена</h1>
      <p class="lead mx-auto mt-4 max-w-[460px]">Возможно, она была удалена или адрес набран с ошибкой.</p>
      <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary mt-8">На главную <i data-lucide="arrow-right"></i></a>
    </div>
  </main>
<?php
get_footer();
