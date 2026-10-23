<?php
/* Кнопки быстрой связи: Telegram (если номеров несколько — меню выбора) и звонок */
if (!defined('ABSPATH')) exit;
$phone = mma_opt('phone');
$tel   = mma_tel($phone);
$tgs   = mma_telegrams();
if (!$tel && !$tgs) return;
?>
  <div class="fab-stack" id="fab-stack">
    <?php if (count($tgs) > 1) : ?>
      <div class="fab-tg-wrap relative">
        <button type="button" class="fab fab-tg" aria-label="Написать в Telegram: выбрать номер" aria-expanded="false" aria-controls="tg-menu" data-tg-toggle>
          <?php echo mma_tg_svg(); ?>
          <span class="fab-label" aria-hidden="true">Написать в Telegram</span>
        </button>
        <div id="tg-menu" class="tg-menu" hidden>
          <p class="tg-menu-title">Написать в Telegram</p>
          <?php foreach ($tgs as $tg) : ?>
            <a href="<?php echo esc_url($tg['link']); ?>" target="_blank" rel="noopener" class="tg-menu-link"><?php echo mma_tg_svg('tg-ico'); ?><span class="whitespace-nowrap"><?php echo esc_html($tg['label']); ?></span><i data-lucide="arrow-up-right"></i></a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php elseif ($tgs) : ?>
      <a href="<?php echo esc_url($tgs[0]['link']); ?>" target="_blank" rel="noopener" class="fab fab-tg" aria-label="Написать в Telegram: <?php echo esc_attr($tgs[0]['label']); ?>">
        <?php echo mma_tg_svg(); ?>
        <span class="fab-label" aria-hidden="true">Написать в Telegram</span>
      </a>
    <?php endif; ?>
    <?php if ($tel) : ?>
      <a href="<?php echo esc_attr($tel); ?>" class="fab fab-call" aria-label="Позвонить: <?php echo esc_attr($phone); ?>">
        <i data-lucide="phone"></i>
        <span class="fab-label" aria-hidden="true"><?php echo esc_html($phone); ?></span>
      </a>
    <?php endif; ?>
  </div>
