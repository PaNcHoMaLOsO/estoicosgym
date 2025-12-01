<?php
/**
 * The main template file
 * 
 * @package EstoicosGym
 */

get_header();
?>

<!-- Blog / Default Content -->
<section class="blog-section">
    <div class="blog-container">
        <div class="section-header">
            <h1 class="section-title">Blog</h1>
        </div>
        
        <?php if (have_posts()): ?>
            <div class="blog-grid">
                <?php while (have_posts()): the_post(); ?>
                    <article class="blog-card">
                        <?php if (has_post_thumbnail()): ?>
                            <div class="blog-image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium_large'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="blog-content">
                            <h2 class="blog-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <div class="blog-meta">
                                <span class="blog-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo get_the_date(); ?>
                                </span>
                            </div>
                            
                            <div class="blog-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            
                            <a href="<?php the_permalink(); ?>" class="btn btn-outline btn-sm">
                                Leer más <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <div class="blog-pagination">
                <?php the_posts_pagination(array(
                    'prev_text' => '<i class="fas fa-chevron-left"></i>',
                    'next_text' => '<i class="fas fa-chevron-right"></i>',
                )); ?>
            </div>
        <?php else: ?>
            <div class="no-content">
                <p>No hay contenido disponible.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
