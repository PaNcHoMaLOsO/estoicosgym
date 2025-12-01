<?php
/**
 * Single Page Template
 * 
 * @package EstoicosGym
 */

get_header();
?>

<section class="page-section">
    <div class="page-container">
        <?php while (have_posts()): the_post(); ?>
            <article class="page-content">
                <header class="page-header">
                    <h1 class="page-title"><?php the_title(); ?></h1>
                </header>
                
                <div class="page-body">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</section>

<?php get_footer(); ?>
