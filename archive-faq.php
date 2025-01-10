<?php
/*
Template Name: FAQ Archive
*/

get_header();

do_action('wp_enqueue_scripts', array($this, 'enqueue_faq_styles'));
do_action('wp_enqueue_scripts', array($this, 'enqueue_faq_scripts'));
?>

<div class="faq-archive">
    <h1>FAQ一覧</h1>
    <?php
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
                while ($faq_query->have_posts()) : $faq_query->the_post();
                    ?>
                    <div class="faq-item" data-post-id="' . get_the_ID() . '">
                        <h3 class="faq-question"><?php the_title(); ?></h3>
                        <div class="faq-answer">
                            <?php the_content(); ?>
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