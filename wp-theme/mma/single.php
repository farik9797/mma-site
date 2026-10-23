<?php
/* Обычная страница (политика конфиденциальности и т. п.) */
if (!defined('ABSPATH')) exit;
get_header();
?>
  <main id="main" class="bg-white py-12 lg:py-20">
    <div class="container-x max-w-[860px]">
      <?php while (have_posts()) : the_post(); ?>
        <h1 class="h2"><?php the_title(); ?></h1>
        <div class="page-content mt-8"><?php the_content(); ?></div>
      <?php endwhile; ?>
    </div>
  </main>
<?php
get_footer();
