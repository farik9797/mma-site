<?php
if (!defined('ABSPATH')) exit;
$a = function ($hash) { return is_front_page() ? $hash : home_url('/' . $hash); };
$phone   = mma_opt('phone');
$tel     = mma_tel($phone);
$email   = mma_opt('email');
$tgs     = mma_telegrams();
$privacy = mma_privacy_url();
?>
  <!-- ============ ПОДВАЛ ============ -->
  <footer class="bg-navy-950 text-white">
    <div class="container-x">
      <div class="grid gap-10 pt-10 lg:grid-cols-12 lg:pt-14">
        <div class="lg:col-span-4">
          <a href="<?php echo esc_attr($a('#top')); ?>" class="inline-flex items-center gap-2.5" aria-label="MODERN MEDICINE ALLIANCE — наверх">
            <span class="relative block h-11 w-11 text-white">
              <i data-lucide="shield" class="h-full w-full" stroke-width="2"></i>
              <i data-lucide="check" class="absolute left-1/2 top-[47%] h-1/2 w-1/2 -translate-x-1/2 -translate-y-1/2" stroke-width="2.75"></i>
            </span>
            <span class="text-[12.5px] font-extrabold uppercase leading-[1.08] tracking-[0.03em]">Modern<br>Medicine<br>Alliance</span>
          </a>
          <?php if ($ft = mma_opt('footer_text')) : ?>
            <p class="mt-5 hidden max-w-[300px] text-[14px] leading-relaxed text-white/70 lg:block"><?php echo mma_lines($ft); ?></p>
          <?php endif; ?>
        </div>
        <nav class="hidden lg:col-span-3 lg:col-start-6 lg:block" aria-label="Разделы сайта">
          <p class="footer-title">Разделы</p>
          <ul class="mt-4 space-y-2.5">
            <?php foreach (['top' => 'Главная', 'about' => 'О компании', 'services' => 'Услуги', 'partners' => 'Партнёры', 'news' => 'Новости', 'contacts' => 'Контакты'] as $id => $label) : ?>
              <li><a href="<?php echo esc_attr($a('#' . $id)); ?>" class="footer-link"><?php echo esc_html($label); ?></a></li>
            <?php endforeach; ?>
          </ul>
        </nav>
        <div class="hidden lg:col-span-4 lg:col-start-9 lg:block">
          <p class="footer-title">Контакты</p>
          <ul class="mt-4 space-y-3.5 text-[14px] text-white/70">
            <?php if ($addr = mma_opt('address')) : ?>
              <li class="flex gap-3"><i data-lucide="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-white/60"></i><span><?php echo mma_lines($addr); ?></span></li>
            <?php endif; ?>
            <?php if ($tel) : ?>
              <li class="flex gap-3"><i data-lucide="phone" class="mt-0.5 h-4 w-4 shrink-0 text-white/60"></i><a href="<?php echo esc_attr($tel); ?>" class="transition-colors hover:text-white"><?php echo esc_html($phone); ?></a></li>
            <?php endif; ?>
            <?php if ($tgs) : ?>
              <li class="flex gap-3"><?php echo mma_tg_svg('mt-0.5 h-4 w-4 shrink-0 text-white/60'); ?><span>Telegram: <?php foreach ($tgs as $tg) : ?><br><a href="<?php echo esc_url($tg['link']); ?>" target="_blank" rel="noopener" class="transition-colors hover:text-white" aria-label="Написать в Telegram: <?php echo esc_attr($tg['label']); ?>"><?php echo esc_html($tg['label']); ?></a><?php endforeach; ?></span></li>
            <?php endif; ?>
            <?php if ($email) : ?>
              <li class="flex gap-3"><i data-lucide="mail" class="mt-0.5 h-4 w-4 shrink-0 text-white/60"></i><a href="mailto:<?php echo esc_attr($email); ?>" class="transition-colors hover:text-white"><?php echo esc_html($email); ?></a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
      <div class="mt-6 flex flex-col gap-3 pb-8 text-[13px] text-white/60 lg:mt-12 lg:flex-row lg:items-center lg:justify-between lg:border-t lg:border-white/10 lg:py-6">
        <p><?php echo esc_html(str_replace('{year}', wp_date('Y'), mma_opt('copyright'))); ?></p>
        <?php if ($privacy) : ?>
          <a href="<?php echo esc_url($privacy); ?>" class="hidden transition-colors hover:text-white lg:inline">Политика конфиденциальности</a>
        <?php endif; ?>
      </div>
    </div>
  </footer>

  <?php get_template_part('template-parts/fab'); ?>

  <a href="<?php echo esc_attr($a('#top')); ?>" id="to-top" class="to-top" aria-label="Наверх"><i data-lucide="arrow-up" class="h-5 w-5"></i></a>

  <?php
  get_template_part('template-parts/modal-lead');
  if (is_front_page()) {
      get_template_part('template-parts/modal-service');
      get_template_part('template-parts/modal-news');
  }
  wp_footer();
  ?>
</body>
</html>
