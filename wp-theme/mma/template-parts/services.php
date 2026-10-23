<?php
/* Услуги: карточки; «Подробнее» открывает попап услуги (template-parts/modal-service.php) */
if (!defined('ABSPATH')) exit;
$services = mma_services();
if (!$services) return;
$check = '<i data-lucide="circle-check" class="h-[18px] w-[18px] shrink-0 text-accent"></i>';
?>
    <section id="services" class="bg-white py-14 lg:py-20">
      <div class="container-x">
        <div data-reveal>
          <h2 class="h2"><?php echo esc_html(mma_get('services_title')); ?></h2>
          <?php if ($lead = mma_get('services_lead')) : ?>
            <p class="lead mt-3 hidden max-w-[440px] sm:block"><?php echo esc_html($lead); ?></p>
          <?php endif; ?>
        </div>

        <div class="mt-7 grid gap-6 lg:mt-10 lg:grid-cols-3 lg:gap-8">
          <?php foreach ($services as $s) :
              $bullets = mma_list($s['bullets'] ?? '');
              $pos = trim((string) ($s['image_pos'] ?? '')) ?: 'center';
              ?>
            <article class="card" data-reveal>
              <?php if (!empty($s['image'])) : ?>
                <div class="card-media aspect-[2.2/1]">
                  <?php echo mma_img($s['image'], ['style' => 'object-position:' . $pos, 'sizes' => '(min-width: 1024px) 400px, 100vw'], 'large'); ?>
                </div>
              <?php endif; ?>
              <div class="flex flex-1 flex-col p-5 sm:p-8 lg:p-6 xl:p-8">
                <h3 class="text-[16px] font-bold leading-snug text-navy sm:text-[21px] lg:text-[19px] xl:text-[21px]"><?php echo esc_html(mma_flat($s['title'])); ?></h3>
                <?php if (!empty($s['short_text'])) : ?>
                  <p class="mt-3 hidden max-w-[460px] text-[15px] leading-relaxed text-muted sm:block"><?php echo esc_html($s['short_text']); ?></p>
                <?php endif; ?>
                <?php if ($bullets) : ?>
                  <ul class="mt-5 hidden space-y-2.5 text-[14.5px] text-ink/85 sm:block">
                    <?php foreach ($bullets as $b) : ?>
                      <li class="flex items-center gap-2.5"><?php echo $check . esc_html($b); ?></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
                <a href="#services" data-service-open="<?php echo esc_attr($s['slug']); ?>" class="link-arrow mt-4 self-start sm:mt-auto sm:pt-7" aria-haspopup="dialog">Подробнее <i data-lucide="arrow-right"></i></a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
