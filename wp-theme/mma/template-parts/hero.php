<?php
/* Первый экран: слайдер направлений — фото и тексты меняются вместе, полосы снизу — прогресс и переключение */
if (!defined('ABSPATH')) exit;
$slides = array_values(array_filter((array) mma_get('hero_slides'), function ($s) { return is_array($s) && !empty($s['title']); }));
if (!$slides) return;
$total = count($slides);
?>
    <section id="top" class="hero relative isolate overflow-hidden bg-navy text-white" data-hero aria-roledescription="слайдер" aria-label="Направления работы">
      <div class="hero-slides absolute inset-0 -z-20" aria-hidden="true">
        <?php foreach ($slides as $i => $s) :
            $pos = trim((string) ($s['pos_mobile'] ?? '')) ?: 'center';
            echo mma_img($s['image'] ?? '', [
                'class'         => 'hero-slide' . ($i === 0 ? ' is-active' : ''),
                'style'         => '--pos-m:' . $pos,
                'alt'           => '',
                'aria-hidden'   => 'true',
                'sizes'         => '100vw',
                'loading'       => $i === 0 ? 'eager' : 'lazy',
                'fetchpriority' => $i === 0 ? 'high' : 'low',
            ], 'full');
        endforeach; ?>
      </div>
      <div class="hero-shade absolute inset-0 -z-10" aria-hidden="true"></div>

      <div class="hero-inner container-x flex flex-col pb-8 pt-10 lg:pb-10 lg:pt-12">
        <div class="hero-panes hero-anim grow">
          <?php foreach ($slides as $i => $s) :
              $tag   = $i === 0 ? 'h1' : 'h2';
              $items = array_values(array_filter((array) ($s['card_items'] ?? []), function ($r) { return is_array($r) && (trim((string) ($r['text'] ?? '')) !== '' || trim((string) ($r['value'] ?? '')) !== ''); }));
              $style = ($s['card_style'] ?? 'list') === 'facts' ? 'facts' : 'list';
              ?>
          <div class="hero-pane<?php echo $i === 0 ? ' is-active' : ''; ?>" id="hero-pane-<?php echo (int) $i; ?>" role="group" aria-roledescription="слайд" aria-label="<?php echo esc_attr(($i + 1) . ' из ' . $total . ': ' . mma_flat($s['title'])); ?>" aria-hidden="<?php echo $i === 0 ? 'false' : 'true'; ?>">
            <div class="grid gap-8 lg:h-full lg:grid-rows-[1fr_auto]">
              <div class="lg:self-center">
                <<?php echo $tag; ?> class="hero-title"><?php echo mma_lines($s['title']); ?></<?php echo $tag; ?>>
                <?php if (!empty($s['text'])) : ?>
                  <p class="hero-text"><?php echo esc_html($s['text']); ?></p>
                <?php endif; ?>
                <div class="hero-actions">
                  <?php if (!empty($s['btn1_text'])) : ?>
                    <a href="#cta" data-modal-open class="btn btn-primary"><?php echo esc_html($s['btn1_text']); ?> <i data-lucide="arrow-right"></i></a>
                  <?php endif; ?>
                  <?php if (!empty($s['btn2_text'])) : ?>
                    <a <?php echo mma_target_attrs($s['btn2_target'] ?? ''); ?> class="btn btn-outline-light"><?php echo esc_html($s['btn2_text']); ?> <i data-lucide="arrow-right"></i></a>
                  <?php endif; ?>
                </div>
              </div>
              <?php if ($items || !empty($s['card_title'])) : ?>
                <aside class="hero-card" aria-label="<?php echo esc_attr($s['card_title'] ?: 'Подробнее'); ?>">
                  <div class="hero-card-head">
                    <?php if (!empty($s['card_title'])) : ?><p class="hero-card-eyebrow"><?php echo esc_html($s['card_title']); ?></p><?php endif; ?>
                    <?php if (!empty($s['card_link_text'])) : ?>
                      <a <?php echo mma_target_attrs($s['card_link_target'] ?? '', '#about'); ?> class="hero-card-link"><?php echo esc_html($s['card_link_text']); ?> <i data-lucide="arrow-right"></i></a>
                    <?php endif; ?>
                  </div>
                  <?php if ($items && $style === 'facts') : $last = count($items) - 1; ?>
                    <div class="mt-4 grid grid-cols-3 divide-x divide-white/15">
                      <?php foreach ($items as $k => $it) : $pad = $k === 0 ? 'pr-5' : ($k === $last ? 'pl-5' : 'px-5'); ?>
                        <div class="<?php echo $pad; ?>">
                          <p class="text-[26px] font-bold leading-none tracking-[-0.01em]"><?php echo esc_html($it['value'] ?? ''); ?><?php if (!empty($it['suffix'])) : ?> <span class="text-[0.62em] font-semibold"><?php echo esc_html($it['suffix']); ?></span><?php endif; ?></p>
                          <?php if (!empty($it['text'])) : ?><p class="mt-2 text-[13px] leading-snug text-white/75"><?php echo esc_html($it['text']); ?></p><?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php elseif ($items) : ?>
                    <ul class="hero-card-list">
                      <?php foreach ($items as $it) : ?>
                        <li class="flex gap-3"><?php echo mma_icon($it['icon'] ?: 'circle-check', 'mt-0.5 h-5 w-5 shrink-0 text-accent-300'); ?><?php echo esc_html($it['text'] ?? ''); ?></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </aside>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if ($total > 1) : ?>
          <!-- Полосы: текущий слайд, отсчёт до автопрокрутки и ручное переключение -->
          <div class="hero-status hero-anim" role="group" aria-label="Переключение слайдов">
            <?php foreach ($slides as $i => $s) : ?>
              <button type="button" class="hero-status__bar<?php echo $i === 0 ? ' is-active' : ''; ?>" aria-label="<?php echo esc_attr('Слайд ' . ($i + 1) . ' из ' . $total . ': ' . mma_flat($s['title'])); ?>" aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>"><i></i></button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <?php
    /* Цифры */
    $stats = array_values(array_filter((array) mma_get('stats'), function ($r) { return is_array($r) && !empty($r['value']); }));
    if ($stats) :
        $last = count($stats) - 1;
        ?>
    <section aria-label="Компания в цифрах" class="bg-mist">
      <div class="container-x">
        <div class="grid gap-8 py-10 lg:grid-cols-4 lg:gap-0 lg:divide-x lg:divide-line lg:py-12">
          <?php foreach ($stats as $k => $st) : $pad = $k === 0 ? 'lg:pr-8' : ($k === $last ? 'lg:pl-10' : 'lg:px-10'); ?>
            <div class="flex items-center gap-5 lg:block <?php echo $pad; ?>" data-reveal>
              <?php echo mma_icon($st['icon'] ?: 'circle-check', 'h-10 w-10 shrink-0 text-accent', 'stroke-width="1.5"'); ?>
              <div class="lg:mt-5">
                <p class="text-[24px] font-bold leading-none tracking-[-0.01em] text-navy lg:text-[32px]"><?php echo esc_html($st['value']); ?></p>
                <?php if (!empty($st['text'])) : ?><p class="mt-2 text-[14.5px] leading-snug text-muted lg:mt-3"><?php echo mma_lines($st['text']); ?></p><?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>
