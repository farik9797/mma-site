<?php
/* Попап «Подробнее об услуге»: по панели на каждую услугу из блока «Услуги» */
if (!defined('ABSPATH')) exit;
$services = mma_services();
if (!$services) return;
$check = '<i data-lucide="circle-check" class="mt-0.5 h-[18px] w-[18px] shrink-0 text-accent"></i>';
?>
  <dialog id="service-modal" class="modal modal-wide" aria-labelledby="svc-title-<?php echo esc_attr($services[0]['slug']); ?>">
    <div class="sticky top-0 z-20 flex h-0 justify-end">
      <button type="button" data-modal-close class="mr-3 mt-3 grid h-10 w-10 shrink-0 cursor-pointer place-items-center rounded-full bg-white/90 text-navy shadow-[0_6px_18px_-8px_rgba(16,43,70,0.5)] transition-colors hover:bg-white" aria-label="Закрыть">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>

    <?php foreach ($services as $i => $s) :
        $col1 = mma_list($s['col1_items'] ?? '');
        $col2 = array_values(array_filter((array) ($s['col2_items'] ?? []), function ($r) { return is_array($r) && trim((string) ($r['text'] ?? '')) !== ''; }));
        $facts = array_values(array_filter((array) ($s['facts'] ?? []), function ($r) { return is_array($r) && trim((string) ($r['value'] ?? '')) !== ''; }));
        $pos = trim((string) ($s['image_pos'] ?? '')) ?: 'center';
        ?>
    <article data-service-panel="<?php echo esc_attr($s['slug']); ?>"<?php echo $i ? ' hidden' : ''; ?>>
      <?php if (!empty($s['image'])) : ?>
        <div class="aspect-[2.4/1] overflow-hidden bg-mist sm:aspect-[3.2/1]">
          <?php echo mma_img($s['image'], ['class' => 'h-full w-full object-cover', 'style' => 'object-position:' . $pos, 'alt' => '', 'sizes' => '(min-width: 640px) 820px, 100vw'], 'large'); ?>
        </div>
      <?php endif; ?>
      <div class="p-6 sm:p-9">
        <p class="eyebrow">Услуга</p>
        <h2 id="svc-title-<?php echo esc_attr($s['slug']); ?>" class="mt-2 text-[22px] font-bold leading-tight text-navy sm:text-[28px]"><?php echo esc_html(mma_flat($s['title'])); ?></h2>
        <?php if (!empty($s['popup_lead'])) : ?>
          <p class="mt-3 text-[15.5px] leading-relaxed text-muted"><?php echo esc_html($s['popup_lead']); ?></p>
        <?php endif; ?>

        <?php if ($col1 || $col2) : ?>
          <div class="mt-7 grid gap-8 sm:grid-cols-2 sm:gap-10">
            <?php if ($col1) : ?>
              <div>
                <?php if (!empty($s['col1_title'])) : ?><h3 class="svc-h"><?php echo esc_html($s['col1_title']); ?></h3><?php endif; ?>
                <ul class="mt-4 space-y-3 text-[15px] text-ink">
                  <?php foreach ($col1 as $item) : ?>
                    <li class="flex gap-3"><?php echo $check . esc_html($item); ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>
            <?php if ($col2) : ?>
              <div>
                <?php if (!empty($s['col2_title'])) : ?><h3 class="svc-h"><?php echo esc_html($s['col2_title']); ?></h3><?php endif; ?>
                <?php if (($s['col2_style'] ?? 'numbered') === 'icons') : ?>
                  <ul class="mt-4 space-y-3 text-[15px] text-ink">
                    <?php foreach ($col2 as $row) : ?>
                      <li class="flex gap-3"><?php echo mma_icon($row['icon'] ?: 'circle-check', 'mt-0.5 h-[18px] w-[18px] shrink-0 text-accent') . esc_html($row['text']); ?></li>
                    <?php endforeach; ?>
                  </ul>
                <?php else : ?>
                  <ol class="mt-4 space-y-3 text-[15px] text-ink">
                    <?php foreach ($col2 as $n => $row) : ?>
                      <li class="flex gap-3"><span class="svc-num"><?php echo (int) $n + 1; ?></span><?php echo esc_html($row['text']); ?></li>
                    <?php endforeach; ?>
                  </ol>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if ($facts) : ?>
          <dl class="svc-facts mt-8" style="grid-template-columns: repeat(<?php echo count($facts); ?>, minmax(0, 1fr));">
            <?php foreach ($facts as $f) : ?>
              <div><dt><?php echo esc_html($f['value']); ?></dt><dd><?php echo esc_html($f['text'] ?? ''); ?></dd></div>
            <?php endforeach; ?>
          </dl>
        <?php endif; ?>
      </div>
      <div class="svc-footer">
        <?php if (!empty($s['price_label']) || !empty($s['price_value'])) : ?>
          <p class="text-[14px] text-muted"><?php echo esc_html($s['price_label'] ?? ''); ?><?php if (!empty($s['price_label']) && !empty($s['price_value'])) echo "\u{00A0}— "; ?><span class="font-semibold text-navy"><?php echo esc_html($s['price_value'] ?? ''); ?></span></p>
        <?php else : ?>
          <span></span>
        <?php endif; ?>
        <button type="button" data-service-cta="<?php echo esc_attr($s['form_name']); ?>" class="btn btn-primary w-full sm:w-auto">Оставить заявку <i data-lucide="arrow-right"></i></button>
      </div>
    </article>
    <?php endforeach; ?>
  </dialog>
