<?php  get_header(); ?>
<div class="site-main has-global-padding faq-container entry-content">
    <h2 class="faq_tag-title">FAQタグ:<?php single_term_title(); ?></h2>
    <div class="faq-list">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <div class="faq-item">
                <h3 class="faq-question"><?php the_title(); ?></h3>
                <div class="faq-answer"><?php the_excerpt(); ?><a href="<?php the_permalink(); ?>">...詳細を見る</a></div>
            </div>
        <?php endwhile; else : ?>
            <p>このタグのついたFAQはありません。</p>
        <?php endif; ?>
    </div>
</div>
<?php get_footer(); ?>