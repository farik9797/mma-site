<?php
/* Партнёры + Новости и полезные материалы */
if (!defined('ABSPATH')) exit;

$partners = array_values(array_filter((array) mma_get('partners'), function ($r) { return is_array($r) && (!empty($r['name']) || !empty($r['logo'])); }));
if ($partners) : ?>
    <!-- Логотипы — только официальные (akfamedline.uz, grandpharmlogistics.uz, profiuniversity.uz, veolia.com). Без логотипа — название текстом. -->
    <section id="partners" class="bg-white py-14 lg:py-20">
      <div class="container-x">
        <div data-reveal>
          <h2 class="h2"><?php echo esc_html(mma_get('partners_title')); ?></h2>
          <?php if ($pl = mma_get('partners_lead')) : ?><p class="lead mt-3 hidden sm:block"><?php echo esc_html($pl); ?></p><?php endif; ?>
        </div>
        <ul class="mt-8 grid grid-cols-3 items-center gap-x-4 gap-y-7 sm:grid-cols-4 lg:mt-12 lg:flex lg:justify-between lg:gap-6" data-reveal>
          <?php foreach ($partners as $p) :
              $h = max(16, min(90, (int) ($p['logo_h'] ?? 40) ?: 40));
              ?>
            <li class="partner<?php echo !empty($p['keep_case']) ? ' normal-case' : ''; ?>">
              <?php if (!empty($p['logo'])) :
                  echo mma_img($p['logo'], ['class' => 'partner-logo', 'style' => '--h:' . $h . 'px', 'alt' => mma_flat($p['name'] ?? ''), 'sizes' => '160px'], 'medium');
              else :
                  echo mma_lines($p['name']);
              endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
<?php endif;

$news = array_values(array_filter((array) mma_get('news'), function ($n) { return is_array($n) && !empty($n['title']); }));
if ($news) : ?>
    <section id="news" class="bg-mist py-14 lg:py-20">
      <div class="container-x">
        <h2 class="h2" data-reveal><span class="lg:hidden"><?php echo esc_html(mma_get('news_title_mobile') ?: mma_get('news_title')); ?></span><span class="hidden lg:inline"><?php echo esc_html(mma_get('news_title')); ?></span></h2>

        <div class="news-track -mx-4 mt-7 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 sm:-mx-6 sm:px-6 lg:mx-0 lg:mt-10 lg:grid lg:grid-cols-3 lg:gap-8 lg:overflow-visible lg:px-0 lg:pb-0">
          <?php foreach ($news as $i => $n) :
              $key = 'n' . ($i + 1);
              $flat = mma_flat($n['title']);
              ?>
            <article class="card w-[84%] shrink-0 snap-start sm:w-[46%] lg:w-auto" data-reveal>
              <a href="#news" data-news-open="<?php echo esc_attr($key); ?>" class="card-media block aspect-[2.4/1]" tabindex="-1" aria-hidden="true">
                <?php echo mma_img($n['image'] ?? '', ['alt' => '', 'sizes' => '(min-width: 1024px) 400px, 84vw'], 'large'); ?>
              </a>
              <div class="flex flex-1 flex-col p-5 lg:p-6">
                <?php if (!empty($n['date'])) : ?><time class="mb-2 block text-[12.5px] text-muted"><?php echo esc_html($n['date']); ?></time><?php endif; ?>
                <h3 class="text-[15.5px] font-semibold leading-snug text-navy lg:text-[16px]"><a href="#news" data-news-open="<?php echo esc_attr($key); ?>" aria-haspopup="dialog" class="transition-colors hover:text-accent"><?php echo mma_lines($n['title']); ?></a></h3>
                <a href="#news" data-news-open="<?php echo esc_attr($key); ?>" aria-haspopup="dialog" class="link-arrow mt-auto pt-4 text-[14px]" aria-label="<?php echo esc_attr('Подробнее: ' . $flat); ?>">Подробнее <i data-lucide="arrow-right"></i></a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
<?php endif;
