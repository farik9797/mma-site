<?php
/* Запасной шаблон: сайт — лендинг, отдельных архивов нет */
if (!defined('ABSPATH')) exit;
get_header();
?>
  <main id="main" class="bg-white py-12 lg:py-20">
    <div class="container-x max-w-[860px]">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article class="border-b border-line py-6">
          <h2 class="text-[20px] font-bold text-navy"><a href="<?php the_permalink(); ?>" class="hover:text-accent"><?php the_title(); ?></a></h2>
          <div class="page-content mt-3"><?php the_excerpt(); ?></div>
        </article>
      <?php endwhile; else : ?>
        <h1 class="h2">Ничего не найдено</h1>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary mt-8">На главную <i data-lucide="arrow-right"></i></a>
      <?php endif; ?>
    </div>
  </main>
<?php
get_footer();
