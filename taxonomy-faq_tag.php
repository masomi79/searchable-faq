<?php  get_header(); ?>
<div class="site-main">
    <h1>タグ：<?php single_term_title(); ?></h1>
    <div class="faq-list">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <div class="faq-item">
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <div><?php the_excerpt(); ?></div>
            </div>
        <?php endwhile; else : ?>
            <p>このタグのついたFAQはありません。</p>
        <?php endif; ?>
    </div>
</div>
<?php get_footer(); ?>