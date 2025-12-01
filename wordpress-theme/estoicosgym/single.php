<?php
/**
 * Single Post Template
 * 
 * @package EstoicosGym
 */

get_header();
?>

<section class="single-section">
    <div class="single-container">
        <?php while (have_posts()): the_post(); ?>
            <article class="single-post">
                <header class="single-header">
                    <h1 class="single-title"><?php the_title(); ?></h1>
                    
                    <div class="single-meta">
                        <span class="meta-date">
                            <i class="fas fa-calendar"></i>
                            <?php echo get_the_date(); ?>
                        </span>
                        <span class="meta-author">
                            <i class="fas fa-user"></i>
                            <?php the_author(); ?>
                        </span>
                        <?php if (has_category()): ?>
                            <span class="meta-category">
                                <i class="fas fa-folder"></i>
                                <?php the_category(', '); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </header>
                
                <?php if (has_post_thumbnail()): ?>
                    <div class="single-image">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
                
                <div class="single-content">
                    <?php the_content(); ?>
                </div>
                
                <footer class="single-footer">
                    <?php if (has_tag()): ?>
                        <div class="single-tags">
                            <i class="fas fa-tags"></i>
                            <?php the_tags('', ', '); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="single-share">
                        <span>Compartir:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank" rel="noopener">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" rel="noopener">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>" target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </footer>
                
                <nav class="post-navigation">
                    <div class="nav-previous">
                        <?php previous_post_link('%link', '<i class="fas fa-chevron-left"></i> %title'); ?>
                    </div>
                    <div class="nav-next">
                        <?php next_post_link('%link', '%title <i class="fas fa-chevron-right"></i>'); ?>
                    </div>
                </nav>
            </article>
        <?php endwhile; ?>
    </div>
</section>

<?php get_footer(); ?>
