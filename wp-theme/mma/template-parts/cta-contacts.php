<?php
/* Финальный призыв с формой + Контакты (карта, форма обратной связи) */
if (!defined('ABSPATH')) exit;
$phone = mma_opt('phone');
$tel   = mma_tel($phone);
$email = mma_opt('email');
$addr  = mma_opt('address');
$tgs   = mma_telegrams();
$map_q = trim((string) mma_opt('map_query'));
?>
    <section id="cta" class="relative isolate overflow-hidden bg-navy-950 text-white">
      <?php echo mma_img(mma_get('cta_image'), [
          'class'       => 'absolute inset-y-0 left-[22%] -z-20 hidden h-full w-[42%] object-cover opacity-50 mix-blend-luminosity lg:block',
          'alt'         => '',
          'aria-hidden' => 'true',
          'sizes'       => '42vw',
      ], 'large'); ?>
      <div class="cta-shade absolute inset-0 -z-10 hidden lg:block" aria-hidden="true"></div>

      <div class="container-x grid items-center gap-8 py-12 lg:grid-cols-12 lg:gap-10 lg:py-16">
        <div class="lg:col-span-5" data-reveal>
          <h2 class="text-[25px] font-bold leading-[1.2] sm:text-[32px] lg:text-[40px] lg:leading-[1.15]"><?php echo mma_lines(mma_get('cta_title')); ?></h2>
          <?php if ($ct = mma_get('cta_text')) : ?>
            <p class="mt-4 max-w-[380px] text-[14.5px] leading-relaxed text-white/75 lg:text-[16px]"><?php echo esc_html($ct); ?></p>
          <?php endif; ?>
          <a href="#cta-form" id="cta-open" class="btn btn-primary mt-7 w-full sm:w-auto sm:px-12" aria-controls="cta-form" aria-expanded="false"><?php echo esc_html(mma_get('cta_btn')); ?></a>
        </div>

        <div id="cta-form" class="hidden lg:col-span-6 lg:col-start-7 lg:block">
          <form class="js-form relative rounded-xl bg-white p-6 text-ink shadow-[0_40px_80px_-40px_rgba(0,0,0,0.6)] sm:p-9" data-form="cta" novalidate>
            <?php get_template_part('template-parts/honeypot'); ?>
            <div class="grid gap-4">
              <input id="cta-name" name="name" class="field field-fill" type="text" required autocomplete="name" placeholder="Ваше имя *" aria-label="Ваше имя">
              <input id="cta-phone" name="phone" class="field field-fill" type="tel" required autocomplete="tel" inputmode="tel" placeholder="Телефон *" aria-label="Телефон">
              <div>
                <label class="label" for="cta-service">Вид услуги*</label>
                <div class="relative">
                  <select id="cta-service" name="service" required class="field field-fill cursor-pointer appearance-none pr-10">
                    <option value="" selected>Выберите услугу</option>
                    <?php foreach (mma_service_options() as $opt) : ?>
                      <option><?php echo esc_html($opt); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <i data-lucide="chevron-down" class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted"></i>
                </div>
              </div>
              <label class="flex cursor-pointer items-start gap-3 text-[13.5px] leading-snug text-muted">
                <input type="checkbox" name="consent" required class="check">
                <?php get_template_part('template-parts/consent'); ?>
              </label>
              <button type="submit" class="btn btn-primary w-full disabled:cursor-wait disabled:opacity-70 sm:w-auto sm:justify-self-start sm:px-24">Отправить</button>
              <p class="form-status hidden text-[13.5px]" role="status" aria-live="polite"></p>
            </div>
          </form>
        </div>
      </div>
    </section>

    <section id="contacts" class="bg-mist py-14 lg:py-20">
      <div class="container-x">
        <div data-reveal>
          <h2 class="h2"><?php echo esc_html(mma_get('contacts_title')); ?></h2>
          <?php if ($cl = mma_get('contacts_lead')) : ?><p class="lead mt-3 hidden sm:block"><?php echo esc_html($cl); ?></p><?php endif; ?>
        </div>

        <div class="mt-7 grid gap-8 lg:mt-10 lg:grid-cols-[1fr_1.2fr_1fr] lg:gap-10">
          <ul class="space-y-5 lg:space-y-7" data-reveal>
            <?php if ($addr) : ?>
              <li class="flex gap-4">
                <span class="contact-icon"><i data-lucide="map-pin" class="h-5 w-5"></i></span>
                <div>
                  <p class="text-[15px] font-semibold text-navy">Адрес</p>
                  <p class="mt-1 text-[14.5px] leading-relaxed text-muted"><?php echo mma_lines($addr); ?></p>
                </div>
              </li>
            <?php endif; ?>
            <?php if ($tel) : ?>
              <li class="flex gap-4">
                <span class="contact-icon"><i data-lucide="phone" class="h-5 w-5"></i></span>
                <div>
                  <p class="text-[15px] font-semibold text-navy">Телефон</p>
                  <p class="mt-1 text-[14.5px] text-muted"><a href="<?php echo esc_attr($tel); ?>" class="transition-colors hover:text-accent"><?php echo esc_html($phone); ?></a></p>
                </div>
              </li>
            <?php endif; ?>
            <?php if ($tgs) : ?>
              <li class="flex gap-4">
                <span class="contact-icon"><?php echo mma_tg_svg('h-5 w-5'); ?></span>
                <div>
                  <p class="text-[15px] font-semibold text-navy">Telegram</p>
                  <p class="mt-1 text-[14.5px] leading-relaxed text-muted">
                    <?php foreach ($tgs as $k => $tg) : ?><?php echo $k ? '<br>' : ''; ?><a href="<?php echo esc_url($tg['link']); ?>" target="_blank" rel="noopener" class="transition-colors hover:text-accent" aria-label="Написать в Telegram: <?php echo esc_attr($tg['label']); ?>"><?php echo esc_html($tg['label']); ?></a><?php endforeach; ?>
                  </p>
                </div>
              </li>
            <?php endif; ?>
            <?php if ($email) : ?>
              <li class="flex gap-4">
                <span class="contact-icon"><i data-lucide="mail" class="h-5 w-5"></i></span>
                <div>
                  <p class="text-[15px] font-semibold text-navy">Email</p>
                  <p class="mt-1 text-[14.5px] text-muted"><a href="mailto:<?php echo esc_attr($email); ?>" class="transition-colors hover:text-accent"><?php echo esc_html($email); ?></a></p>
                </div>
              </li>
            <?php endif; ?>
          </ul>

          <?php if ($map_q) : $q = rawurlencode($map_q); ?>
            <div data-reveal>
              <div class="relative h-[220px] overflow-hidden rounded-lg bg-white shadow-[0_14px_40px_-22px_rgba(16,43,70,0.35)] sm:h-[280px] lg:h-[250px]">
                <iframe
                  title="<?php echo esc_attr('Офис MODERN MEDICINE ALLIANCE на карте: ' . $map_q); ?>"
                  src="https://maps.google.com/maps?q=<?php echo $q; ?>&amp;z=15&amp;hl=ru&amp;output=embed"
                  class="absolute inset-0 h-full w-full grayscale-[0.35]" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
              </div>
              <a href="https://www.google.com/maps/search/?api=1&amp;query=<?php echo $q; ?>"
                 target="_blank" rel="noopener" class="link-arrow mt-4 hidden text-[14px] sm:inline-flex">Открыть в Google Maps <i data-lucide="arrow-right"></i></a>
            </div>
          <?php endif; ?>

          <form class="js-form relative hidden lg:block" data-form="feedback" novalidate data-reveal>
            <?php get_template_part('template-parts/honeypot'); ?>
            <p class="text-[19px] font-semibold text-accent"><?php echo esc_html(mma_get('feedback_title')); ?></p>
            <div class="mt-5 grid gap-3.5">
              <input id="fb-name" name="name" class="field" type="text" required autocomplete="name" placeholder="Ваше имя *" aria-label="Ваше имя">
              <input id="fb-phone" name="phone" class="field" type="tel" required autocomplete="tel" inputmode="tel" placeholder="Телефон *" aria-label="Телефон">
              <textarea id="fb-message" name="message" class="field" placeholder="Сообщение" aria-label="Сообщение"></textarea>
              <label class="flex cursor-pointer items-start gap-3 text-[13px] leading-snug text-muted">
                <input type="checkbox" name="consent" required class="check">
                <?php get_template_part('template-parts/consent'); ?>
              </label>
              <button type="submit" class="btn btn-primary mt-1 justify-self-start px-14 disabled:cursor-wait disabled:opacity-70">Отправить <i data-lucide="send"></i></button>
              <p class="form-status hidden text-[13.5px]" role="status" aria-live="polite"></p>
            </div>
          </form>
        </div>
      </div>
    </section>
