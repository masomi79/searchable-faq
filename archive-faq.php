<?php
/*
Template Name: FAQ Archive
*/

get_header();

do_action('wp_enqueue_scripts', array($this, 'enqueue_faq_styles'));
do_action('wp_enqueue_scripts', array($this, 'enqueue_faq_scripts'));
?>

<div class="faq-archive">
    <h1>よくある質問</h1>
    <?php
    //検索フォームの表示
    echo do_shortcode('[faq_search_form]');

    $atts = array(
        'categories' => '',
        'limit' => -1,
        'order' => 'DESC',
        'orderby' => 'meta_value_num',
        'meta_key' => 'faq_view_count'
    );

    $category_slugs = !empty($atts['categories']) ? explode(',', $atts['categories']) : array();

    echo '<div class="faq-container">';

    if (empty($category_slugs)) {
        // カテゴリが指定されていない場合、すべてのカテゴリを取得
        $faq_categories = get_terms(array(
            'taxonomy' => 'faq_category',
            'hide_empty' => true,
            'parent' => 0,
        ));

        foreach ($faq_categories as $faq_category) {
            $args = array(
                'post_type' => 'faq',
                'posts_per_page' => $atts['limit'],
                'order' => $atts['order'],
                'orderby' => $atts['orderby'],
                'meta_key' => $atts['meta_key'],
                'tax_query' => array(
                    array(
                        'taxonomy' => 'faq_category',
                        'field' => 'slug',
                        'terms' => $faq_category->slug
                    )
                )
            );

            $faq_query = new WP_Query($args);

            if ($faq_query->have_posts()) :
                echo '<h2>' . esc_html($faq_category->name) . '</h2>';
                while ($faq_query->have_posts()) :
                    $faq_query->the_post();
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
                            ?>

                            <?php
                            // 投稿に紐づく 'faq_tag' のタグをリストアップ
                            $faq_tags = get_the_terms(get_the_ID(), 'faq_tag');
                            if ($faq_tags && !is_wp_error($faq_tags)) {
                                echo '<ul class="faq-tag-list">';
                                foreach ($faq_tags as $tag) {
                                    echo '<li><a href="' . esc_url(get_term_link($tag, 'faq_tag')) . '">' . esc_html($tag->name) . '</a></li>';
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
                ?>
                <p><?php echo esc_html($faq_category->name); ?>にFAQが見つかりませんでした。</p>
                <?php
            endif;
        }
    } else {
        // カテゴリが指定されている場合
        foreach ($category_slugs as $category_slug) {
            $args = array(
                'post_type' => 'faq',
                'posts_per_page' => $atts['limit'],
                'order' => $atts['order'],
                'orderby' => $atts['orderby'],
                'meta_key' => $atts['meta_key'],
                'tax_query' => array(
                    array(
                        'taxonomy' => 'faq_category',
                        'field' => 'slug',
                        'terms' => $category_slug
                    )
                )
            );

            $faq_query = new WP_Query($args);

            if ($faq_query->have_posts()) :
                while ($faq_query->have_posts()) : $faq_query->the_post();
                    ?>
                    <div class="faq-item">
                        <h2><?php the_title(); ?></h2>
                        <div class="faq-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                    <?php
                endwhile;
                wp_reset_postdata();
            else :
                ?>
                <p>FAQが見つかりませんでした。</p>
                <?php
            endif;
        }
    }

    echo '</div>';
    ?>
</div>

<?php
get_footer();
?>