<?php
/* Попап «Оставить заявку» (нативный <dialog>). Без JS кнопки ведут к форме в блоке #cta */
if (!defined('ABSPATH')) exit;
?>
  <dialog id="lead-modal" class="modal" aria-labelledby="lead-modal-title">
    <div class="relative p-6 sm:p-9">
      <button type="button" data-modal-close class="absolute right-3 top-3 grid h-10 w-10 cursor-pointer place-items-center rounded-full text-muted transition-colors hover:bg-mist hover:text-navy" aria-label="Закрыть">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>

      <div data-modal-view="form">
        <span class="grid h-12 w-12 place-items-center rounded-full bg-accent/10 text-accent">
          <i data-lucide="clipboard-pen" class="h-[22px] w-[22px]"></i>
        </span>
        <h2 id="lead-modal-title" class="mt-5 text-[22px] font-bold leading-snug text-navy sm:text-[24px]">Оставить заявку</h2>
        <p class="mt-2 text-[15px] leading-relaxed text-muted">Оставьте контакты, и менеджер свяжется с вами, чтобы уточнить задачу.</p>

        <form class="js-modal-form relative mt-6 grid gap-4" data-form="modal" novalidate>
          <?php get_template_part('template-parts/honeypot'); ?>
          <div>
            <label class="mb-1.5 block text-[13.5px] font-medium text-navy" for="m-name">Ваше имя <span class="text-accent">*</span></label>
            <input id="m-name" name="name" class="field" type="text" required autocomplete="name" aria-describedby="m-name-err">
            <p id="m-name-err" class="field-error hidden">Укажите, как к вам обращаться</p>
          </div>
          <div>
            <label class="mb-1.5 block text-[13.5px] font-medium text-navy" for="m-phone">Телефон <span class="text-accent">*</span></label>
            <input id="m-phone" name="phone" class="field" type="tel" required autocomplete="tel" inputmode="tel" placeholder="+998 (__) ___-__-__" aria-describedby="m-phone-err">
            <p id="m-phone-err" class="field-error hidden">Введите номер телефона полностью</p>
          </div>
          <div>
            <label class="mb-1.5 block text-[13.5px] font-medium text-navy" for="m-service">Вид услуги</label>
            <div class="relative">
              <select id="m-service" name="service" class="field cursor-pointer appearance-none pr-10">
                <option value="" selected>Выберите услугу</option>
                <?php foreach (mma_service_options() as $opt) : ?>
                  <option><?php echo esc_html($opt); ?></option>
                <?php endforeach; ?>
              </select>
              <i data-lucide="chevron-down" class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted"></i>
            </div>
          </div>
          <div>
            <label class="flex cursor-pointer items-start gap-3 text-[13.5px] leading-snug text-muted">
              <input type="checkbox" name="consent" required class="check" aria-describedby="m-consent-err">
              <?php get_template_part('template-parts/consent'); ?>
            </label>
            <p id="m-consent-err" class="field-error hidden">Нужно согласие на обработку данных</p>
          </div>
          <button type="submit" class="btn btn-primary mt-1 w-full disabled:cursor-wait disabled:opacity-70">Отправить заявку</button>
          <p class="form-status hidden text-[13.5px] text-[#C2410C]" role="alert"></p>
        </form>
      </div>

      <div data-modal-view="success" class="hidden py-4 text-center" role="status" aria-live="polite">
        <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-accent/10 text-accent">
          <i data-lucide="circle-check-big" class="h-8 w-8"></i>
        </span>
        <p class="mt-5 text-[22px] font-bold text-navy">Спасибо!</p>
        <p class="mx-auto mt-2 max-w-[320px] text-[15px] leading-relaxed text-muted">Менеджер свяжется с вами по указанному телефону.</p>
        <button type="button" data-modal-close class="btn mt-6 border border-line px-10 text-navy hover:border-accent hover:text-accent">Закрыть</button>
      </div>
    </div>
  </dialog>
