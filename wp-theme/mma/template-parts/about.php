<?php
/* О компании + Как мы работаем + Почему выбирают нас */
if (!defined('ABSPATH')) exit;
$facts = array_values(array_filter((array) mma_get('about_facts'), function ($r) { return is_array($r) && !empty($r['text']); }));
$about_pos = trim((string) mma_get('about_image_pos')) ?: 'center';
?>
    <section id="about" class="bg-white pb-14 lg:pb-24 lg:pt-4">
      <div class="container-x grid items-center gap-6 lg:grid-cols-2 lg:gap-14">
        <?php echo mma_img(mma_get('about_image'), [
            'class'       => 'aspect-[2.4/1] w-full rounded-lg object-cover sm:aspect-[16/10] lg:aspect-[1.1/1]',
            'style'       => 'object-position:' . $about_pos,
            'data-reveal' => '',
            'sizes'       => '(min-width: 1024px) 590px, 100vw',
        ], 'large'); ?>

        <div data-reveal>
          <?php if ($eb = mma_get('about_eyebrow')) : ?><p class="eyebrow"><?php echo esc_html($eb); ?></p><?php endif; ?>
          <h2 class="h2 mt-3"><?php echo mma_lines(mma_get('about_title')); ?></h2>
          <?php if ($txt = mma_get('about_text')) : ?>
            <p class="mt-4 max-w-[540px] text-[15px] leading-relaxed text-muted lg:mt-5 lg:text-[16px]"><?php echo esc_html($txt); ?></p>
          <?php endif; ?>
          <?php if ($facts) : ?>
            <ul class="mt-7 hidden space-y-4 text-[15px] text-ink sm:block">
              <?php foreach ($facts as $f) : ?>
                <li class="flex items-center gap-4"><?php echo mma_icon($f['icon'] ?: 'circle-check', 'h-[22px] w-[22px] shrink-0 text-accent', 'stroke-width="1.6"') . esc_html($f['text']); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <?php if ($btn = mma_get('about_btn_text')) : ?>
            <a href="<?php echo esc_attr(mma_get('about_btn_link') ?: '#services'); ?>" class="btn btn-primary mt-7 lg:mt-9"><?php echo esc_html($btn); ?> <i data-lucide="arrow-right"></i></a>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <?php
    /* Как мы работаем */
    $steps = array_values(array_filter((array) mma_get('process_steps'), function ($r) { return is_array($r) && !empty($r['title']); }));
    if ($steps) :
        $last = count($steps) - 1;
        ?>
    <section id="process" class="bg-mist py-14 lg:py-20">
      <div class="container-x">
        <div data-reveal>
          <h2 class="h2"><?php echo esc_html(mma_get('process_title')); ?></h2>
          <?php if ($pl = mma_get('process_lead')) : ?><p class="lead mt-3 hidden sm:block"><?php echo esc_html($pl); ?></p><?php endif; ?>
        </div>

        <div class="relative mt-8 lg:mt-12">
          <span class="absolute left-5 right-5 top-5 hidden h-px bg-[#C9D5E2] lg:block" aria-hidden="true"></span>
          <span class="absolute right-0 top-[11px] hidden text-[#AAB8C8] lg:block" aria-hidden="true"><i data-lucide="chevron-right" class="h-[18px] w-[18px]"></i></span>
          <ol class="grid gap-7 lg:grid-cols-4 lg:gap-8">
            <?php foreach ($steps as $k => $st) : ?>
              <li class="flex gap-5 lg:block" data-reveal>
                <span class="step-num"><?php echo (int) $k + 1; ?></span>
                <div class="pt-2 lg:mt-6 lg:pt-0">
                  <h3 class="text-[15px] font-bold leading-snug text-navy lg:text-[16px]"><?php echo mma_lines($st['title'], ' <br class="hidden lg:block">'); ?></h3>
                  <?php if (!empty($st['text'])) : ?>
                    <p class="mt-2 <?php echo $k === $last ? 'max-w-[240px]' : 'max-w-[230px]'; ?> text-[14px] leading-relaxed text-muted"><?php echo esc_html($st['text']); ?></p>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php
    /* Почему выбирают нас */
    $adv = array_values(array_filter((array) mma_get('adv_items'), function ($r) { return is_array($r) && !empty($r['title']); }));
    if ($adv) : ?>
    <section id="advantages" class="bg-mist px-4 pb-14 sm:px-6 lg:bg-navy lg:p-0">
      <div class="relative isolate overflow-hidden rounded-2xl bg-navy text-white lg:rounded-none">
        <?php echo mma_img(mma_get('adv_image'), [
            'class'       => 'absolute inset-y-0 right-0 -z-20 hidden h-full w-[46%] object-cover object-[95%_30%] lg:block',
            'alt'         => '',
            'aria-hidden' => 'true',
            'sizes'       => '46vw',
        ], 'large'); ?>
        <div class="why-shade absolute inset-0 -z-10 hidden lg:block" aria-hidden="true"></div>

        <div class="container-x px-5 py-8 sm:px-8 lg:px-8 lg:py-20">
          <h2 class="text-[20px] font-bold leading-snug lg:text-[16px] lg:uppercase lg:tracking-[0.04em]" data-reveal><?php echo esc_html(mma_get('adv_title')); ?></h2>
          <ul class="mt-6 grid gap-5 lg:mt-12 lg:grid-cols-4 lg:gap-10">
            <?php foreach ($adv as $it) : ?>
              <li class="flex items-center gap-4 lg:block" data-reveal>
                <span class="adv-icon"><?php echo mma_icon($it['icon'] ?: 'circle-check', 'h-6 w-6 lg:h-7 lg:w-7', 'stroke-width="1.5"'); ?></span>
                <h3 class="text-[14.5px] font-semibold leading-snug lg:mt-7 lg:text-[18px]"><?php echo mma_lines($it['title'], ' <br class="hidden lg:block">'); ?></h3>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </section>
    <?php endif; ?>
