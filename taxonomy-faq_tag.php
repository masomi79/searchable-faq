<?php  
/*
Template Name: FAQ Tag-archive
*/

get_header(); 

do_action('wp_enqueue_scripts', array($this, 'enqueue_faq_styles'));
do_action('wp_enqueue_scripts', array($this, 'enqueue_faq_scripts'));
?>
<div class="faq-container">
    <h2 class="faq_tag-title">FAQタグ:<?php single_term_title(); ?></h2>
    <div class="faq-list">
        <?php 
        if (have_posts()) : while (have_posts()) : the_post(); 
        
        
        $view_count = get_post_meta(get_the_ID(), 'faq_view_count', true);
        $view_count = $view_count ? $view_count : 0; // 値がない場合は 0 とする
        
        ?>
           
           <div class="faq-item" data-post-id="<?php echo get_the_ID(); ?>">
                <h3 class="faq-question">
                    <?php the_title(); ?>
                    <span class="faq-view-count">(<?php echo esc_html($view_count); ?>)</span>
                </h3>
                <div class="faq-answer">
                    <?php the_content(); ?>
                </div>
            </div>
        <?php endwhile; else : ?>
            <p>このタグのついたFAQはありません。</p>
        <?php endif; ?>
    </div>
</div>
<?php get_footer(); ?>