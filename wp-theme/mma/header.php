<?php
if (!defined('ABSPATH')) exit;
$phone = mma_opt('phone');
$tel   = mma_tel($phone);
/* На главной пункты меню — якоря блоков, на других страницах — ссылки на блоки главной */
$a = function ($hash) { return is_front_page() ? $hash : home_url('/' . $hash); };
$nav = [
    'top'      => 'Главная',
    'about'    => 'О компании',
    'services' => 'Услуги',
    'partners' => 'Партнёры',
    'news'     => 'Новости',
    'contacts' => 'Контакты',
];
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class('bg-white font-sans text-ink antialiased'); ?>>
  <?php wp_body_open(); ?>
  <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-navy focus:shadow-lg">Перейти к содержанию</a>

  <!-- ============ ШАПКА ============ -->
  <header id="site-header" class="sticky top-0 z-50 border-b border-transparent bg-white transition-[box-shadow,border-color] duration-300">
    <div class="container-x flex h-[68px] items-center justify-between gap-6 lg:h-[80px]">
      <!-- Логотип: временная версия, заменить на векторный логотип заказчика -->
      <a href="<?php echo esc_attr($a('#top')); ?>" class="flex shrink-0 items-center gap-2.5" aria-label="MODERN MEDICINE ALLIANCE — на главную">
        <span class="relative block h-10 w-10 text-navy lg:h-11 lg:w-11">
          <i data-lucide="shield" class="h-full w-full" stroke-width="2.25"></i>
          <i data-lucide="check" class="absolute left-1/2 top-[47%] h-1/2 w-1/2 -translate-x-1/2 -translate-y-1/2 text-accent" stroke-width="3"></i>
        </span>
        <span class="text-[11.5px] font-extrabold uppercase leading-[1.08] tracking-[0.03em] text-navy lg:text-[12.5px]">Modern<br>Medicine<br>Alliance</span>
      </a>

      <nav class="hidden lg:block" aria-label="Основное меню">
        <ul class="flex items-center gap-7 text-[14px] font-medium">
          <?php foreach ($nav as $id => $label) : ?>
            <li><a href="<?php echo esc_attr($a('#' . $id)); ?>" class="nav-link<?php echo $id === 'top' && is_front_page() ? ' is-active' : ''; ?>"<?php echo is_front_page() ? ' data-nav="' . esc_attr($id) . '"' : ''; ?>><?php echo esc_html($label); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <div class="flex items-center gap-6">
        <?php if ($tel) : ?>
          <a href="<?php echo esc_attr($tel); ?>" class="hidden items-center gap-2 text-[14px] font-medium text-navy transition-colors hover:text-accent xl:flex">
            <i data-lucide="phone" class="h-4 w-4 text-accent"></i><?php echo esc_html($phone); ?>
          </a>
        <?php endif; ?>
        <a href="<?php echo esc_attr($a('#cta')); ?>" data-modal-open class="btn btn-primary hidden h-11 px-6 text-[14px] lg:inline-flex">Оставить заявку</a>
        <button id="menu-toggle" type="button" class="-mr-2 grid h-11 w-11 cursor-pointer place-items-center rounded-md text-navy lg:hidden" aria-expanded="false" aria-controls="mobile-menu" aria-label="Открыть меню">
          <i data-lucide="menu" class="icon-open h-7 w-7"></i>
          <i data-lucide="x" class="icon-close hidden h-7 w-7"></i>
        </button>
      </div>
    </div>

    <div id="mobile-menu" class="hidden h-[calc(100dvh-68px)] overflow-y-auto border-t border-line bg-white lg:hidden">
      <nav class="container-x pb-6 pt-2" aria-label="Мобильное меню">
        <ul class="divide-y divide-line">
          <?php foreach ($nav as $id => $label) : ?>
            <li><a href="<?php echo esc_attr($a('#' . $id)); ?>" class="flex items-center justify-between py-4 text-[16px] font-semibold text-navy"><?php echo esc_html($label); ?> <i data-lucide="arrow-right" class="h-4 w-4 text-muted"></i></a></li>
          <?php endforeach; ?>
        </ul>
        <div class="mt-4 grid gap-3">
          <?php if ($tel) : ?>
            <a href="<?php echo esc_attr($tel); ?>" class="btn w-full border border-line text-navy"><i data-lucide="phone" class="!h-4 !w-4 text-accent"></i><?php echo esc_html($phone); ?></a>
          <?php endif; ?>
          <a href="<?php echo esc_attr($a('#cta')); ?>" data-modal-open class="btn btn-primary w-full">Оставить заявку</a>
        </div>
      </nav>
    </div>
  </header>
