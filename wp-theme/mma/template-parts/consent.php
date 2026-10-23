<?php
/* Подпись чекбокса согласия: ссылка на политику, если страница опубликована */
if (!defined('ABSPATH')) exit;
$privacy = mma_privacy_url();
?>
<span>Я согласен на <?php if ($privacy) : ?><a href="<?php echo esc_url($privacy); ?>" target="_blank" class="text-accent underline-offset-2 hover:underline">обработку персональных данных</a><?php else : ?>обработку персональных данных<?php endif; ?></span>
