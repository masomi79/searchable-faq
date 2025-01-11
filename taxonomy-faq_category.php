<?php
/*
Template Name: FAQ Category Archive
*/

get_header();


// プラグインのCSSとJSを読み込む
do_action('wp_enqueue_scripts');

?>

<div class="faq-category-archive">
    <div class="faq-container">
    <h1><?php single_term_title(); ?> のFAQ一覧</h1>
    <?php echo do_shortcode('[faq_search_form]'); ?>

    <?php
    $term = get_queried_object();

    if ($term && !is_wp_error($term)) {
        $args = array(
            'post_type' => 'faq',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'faq_category',
                    'field' => 'slug',
                    'terms' => $term->slug
                )
            )
        );

        $faq_query = new WP_Query($args);

        if ($faq_query->have_posts()) :
            echo '<div class = "faq-category-archive">';
            while ($faq_query->have_posts()) : $faq_query->the_post();
                $view_count = get_post_meta(get_the_ID(), 'faq_view_count', true);
                $view_count = $view_count ? $view_count : 0; // 値がない場合は 0 とする
                ?>
                <div class="faq-item" data-post-id="<?php echo get_the_ID(); ?>">
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
                            echo '<a href="' . get_post_type_archive_link('faq') . '">FAQ</a> > ';

                            foreach ($ancestors as $ancestor) {
                                $parent_term = get_term($ancestor, 'faq_category');
                                echo '<a href="' . get_term_link($parent_term) . '">' . esc_html($parent_term->name) . '</a> > ';
                            }

                            echo '<a href="' . get_term_link($term) . '">' . esc_html($term->name) . '</a>';
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
                <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<p>FAQが見つかりませんでした。</p>';
        endif;
    }
    ?>
</div>
</div>

<?php
get_footer();
?>