<?php  
/*
Template Name: FAQ Tag-archive
*/

get_header(); 

do_action('wp_enqueue_scripts', array($this, 'enqueue_faq_styles'));
do_action('wp_enqueue_scripts', array($this, 'enqueue_faq_scripts'));
?>
<div class="faq-archive">

    <h1 class="faq-main-title">よくある質問</h1>
    <div class="faq-container">
    <h2 class="faq-tax-title">タグ:<?php single_term_title(); ?></h2>
    <?php echo do_shortcode('[faq_search_form]'); ?>
    <div class="faq-ctaxonomy-archive">
        <?php 
        if (have_posts()) : while (have_posts()) : the_post(); 
        
        
        $view_count = get_post_meta(get_the_ID(), 'faq_view_count', true);
        $view_count = $view_count ? $view_count : 0; // 値がない場合は 0 とする
        
        ?>
           
           <div class="faq-item" data-post-id="<?php echo esc_attr(get_the_ID()); ?>">
                <h3 class="faq-question">
                    <?php the_title(); ?>
                    <span class="faq-view-count">(<?php echo esc_html($view_count); ?>)</span>
                </h3>
                <div class="faq-answer">
                    <?php 
                    
                    // パンくずリストの表示
                    $terms = get_the_terms(get_the_ID(), 'faq_category');
                    if ($terms && !is_wp_error($terms)) {
                        $term = array_shift($terms);
                        $ancestors = get_ancestors($term->term_id, 'faq_category');
                        $ancestors = array_reverse($ancestors);

                        echo '<div class="faq-breadcrumb">';
                        echo '<a href="' . esc_url(get_post_type_archive_link('faq')) . '">FAQ</a> > ';

                        foreach ($ancestors as $ancestor) {
                            $parent_term = get_term($ancestor, 'faq_category');
                            echo '<a href="' . esc_url(get_term_link($parent_term)) . '">' . esc_html($parent_term->name) . '</a> > ';
                        }

                        echo '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
                        echo '</div>';
                    }

                    the_content(); 
                            
                    // 投稿に紐づく 'faq_tag' のタグをリストアップ
                    $faq_tags = get_the_terms(get_the_ID(), 'faq_tag');
                    if ($faq_tags && !is_wp_error($faq_tags)) {
                        echo '<ul class="faq-tag-list">';
                        echo '<li class="faq-tag-label">タグ:</li>';
                        foreach ($faq_tags as $tag) {
                            echo '<li class="faq-tag"><a href="' . esc_url(get_term_link($tag, 'faq_tag')) . '">' . esc_html($tag->name) . '</a></li>';
                        }
                        echo '</ul>';
                    }
                    ?>
                </div>
            </div>
        <?php endwhile; else : ?>
            <p>このタグのついたFAQはありません。</p>
        <?php endif; ?>
    </div>
</div>
</div>
<?php get_footer(); ?>