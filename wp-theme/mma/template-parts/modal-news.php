<?php
/* Попап «Новости и полезные материалы»: по панели на каждый материал */
if (!defined('ABSPATH')) exit;
$news = array_values(array_filter((array) mma_get('news'), function ($n) { return is_array($n) && !empty($n['title']); }));
if (!$news) return;
$eyebrow = mma_get('news_eyebrow');
$check = '<i data-lucide="circle-check" class="mt-0.5 h-[18px] w-[18px] shrink-0 text-accent"></i>';
?>
  <dialog id="news-modal" class="modal modal-wide" aria-labelledby="news-title-n1">
    <div class="sticky top-0 z-20 flex h-0 justify-end">
      <button type="button" data-modal-close class="mr-3 mt-3 grid h-10 w-10 shrink-0 cursor-pointer place-items-center rounded-full bg-white/90 text-navy shadow-[0_6px_18px_-8px_rgba(16,43,70,0.5)] transition-colors hover:bg-white" aria-label="Закрыть">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>

    <?php foreach ($news as $i => $n) :
        $key = 'n' . ($i + 1);
        $items = mma_list($n['list_items'] ?? '');
        ?>
    <article data-news-panel="<?php echo esc_attr($key); ?>"<?php echo $i ? ' hidden' : ''; ?>>
      <?php if (!empty($n['image'])) : ?>
        <div class="aspect-[2.4/1] overflow-hidden bg-mist sm:aspect-[3.2/1]">
          <?php echo mma_img($n['image'], ['class' => 'h-full w-full object-cover', 'alt' => '', 'sizes' => '(min-width: 640px) 820px, 100vw'], 'large'); ?>
        </div>
      <?php endif; ?>
      <div class="p-6 sm:p-9">
        <p class="flex items-center gap-2.5">
          <?php if ($eyebrow) : ?><span class="eyebrow"><?php echo esc_html($eyebrow); ?></span><?php endif; ?>
          <?php if ($eyebrow && !empty($n['date'])) : ?><span class="text-line" aria-hidden="true">|</span><?php endif; ?>
          <?php if (!empty($n['date'])) : ?><time class="text-[13px] text-muted"><?php echo esc_html($n['date']); ?></time><?php endif; ?>
        </p>
        <h2 id="news-title-<?php echo esc_attr($key); ?>" class="mt-2 text-[22px] font-bold leading-tight text-navy sm:text-[28px]"><?php echo esc_html(mma_flat($n['title'])); ?></h2>
        <?php if (!empty($n['lead'])) : ?>
          <p class="mt-3 text-[15.5px] leading-relaxed text-muted"><?php echo esc_html($n['lead']); ?></p>
        <?php endif; ?>

        <?php if ($items) : ?>
          <div class="mt-7">
            <?php if (!empty($n['list_title'])) : ?><h3 class="svc-h"><?php echo esc_html($n['list_title']); ?></h3><?php endif; ?>
            <?php if (($n['list_style'] ?? 'check') === 'numbered') : ?>
              <ol class="mt-4 space-y-3 text-[15px] text-ink">
                <?php foreach ($items as $k => $item) : ?>
                  <li class="flex gap-3"><span class="svc-num"><?php echo (int) $k + 1; ?></span><?php echo esc_html($item); ?></li>
                <?php endforeach; ?>
              </ol>
            <?php else : ?>
              <ul class="mt-4 space-y-3 text-[15px] text-ink">
                <?php foreach ($items as $item) : ?>
                  <li class="flex gap-3"><?php echo $check . esc_html($item); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($n['body'])) : ?>
          <div class="mma-prose mt-8"><?php echo wp_kses_post($n['body']); ?></div>
        <?php endif; ?>
      </div>
      <div class="svc-footer">
        <p class="text-[14px] text-muted">Остались вопросы? <span class="font-semibold text-navy">Проконсультируем</span></p>
        <button type="button" data-news-cta="<?php echo esc_attr($n['cta_service'] ?? ''); ?>" class="btn btn-primary w-full sm:w-auto">Получить консультацию <i data-lucide="arrow-right"></i></button>
      </div>
    </article>
    <?php endforeach; ?>
  </dialog>
