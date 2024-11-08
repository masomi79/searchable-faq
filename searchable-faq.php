<?php
/*
Plugin Name: Searchable FAQ
Plugin URI: http://github.com/masomi79/sarchable-faq
Description: A simple FAQ plugin for WordPress
Version: 7.5.8.2.1
Author: masomi79
Author URI: https://massumifukuda.work/wp/
License: GPL2
*/
namespace SearchableFAQ;

use WP_Query;

//お約束
if (!defined('ABSPATH')) {
    exit;
}

class SearchableFAQ {
    public function __construct() {
        add_action('init', array($this, 'register_faq_post_type'));
        add_action('init', array($this, 'register_faq_taxonomy'));
        add_action('init', array($this, 'register_faq_tags'));
        add_shortcode('faq', array($this, 'faq_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_faq_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_faq_scripts'));
        add_shortcode('faq_search_form', array($this,'faq_search_form_shortcode'));
        add_action('wp_footer', array($this, 'enqueue_faq_scripts'));
        add_action('template_redirect', array($this, 'display_faq_single'));
    }

    public function register_faq_post_type(){
        
        $labels = array(
            'name'              => 'FAQs',
            'singular_name'     => 'FAQ',
            'menu_name'         => 'FAQs',
            'name_admin_bar'    => 'FAQ',
            'add_new'           => 'Add New',
            'add_new_item'      => 'Add New FAQ',
            'new_item'          => 'new FAQ',
            'edit_item'         => 'Edit FAQ',
            'viwe_item'         => 'View FAQ',
            'all_items'         => 'All FAQs',
            'search_items'      => 'Search FAQs',
            'parent_item_colon' => 'Parent FAQ',
            'not_found'         => 'No FAQs found.',
            'not_found_in_trash'=> 'No FAQs found in Trash.'
        );

        $args = array(
            'labels'            => $labels,
            'public'            => true,
            'publicly_queryable'=> true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'query_bar'         => true,
            'rewrite'           => array(
                'slug'              => 'faq'
            ),
            'capability_type'   => 'post',
            'has_archive'       => true,
            'hierarchical'      => false,
            'menu_position'     => null,
            'supports'          => array(
                'title',
                'editor',
                'thumbnail',
                'excerpt',
                'comments'
            )
        );
        register_post_type('faq', $args);
    }

    //階層カテゴリーを追加する
    public function register_faq_taxonomy(){
        $labels = array(
            'name'              => 'FAQ Ctegories',
            'singular_name'     => 'FAQ Category',
            'search_items'      => 'Search FAQ Categories',
            'all_items'         => 'All FAQ Categories',
            'parent_items'      => 'Parent FAQ Category',
            'parent_item_colon' => 'Parent FAQ Category:',
            'edit_item'         => 'Edit FAQ Category',
            'update_item'       => 'Update FAQ Category',
            'add_new_item'      => 'New FAQ Category Name',
            'menu_name'         => 'FAQ Categories'
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array(
                'slug'          => 'faq-category'
            )
        );
        register_taxonomy('faq_category', array('faq'), $args);
    }

    //非階層のタグを追加する
    public function register_faq_tags() {
        $labels = array(
            'name'              => 'FAQ Tags',
            'singular_name'     => 'FAQ Tag',
            'search_items'      => 'Search FAQ Tags',
            'all_items'         => 'All FAQ Tags',
            'edit_item'         => 'Edit FAQ Tag',
            'update_item'       => 'Update FAQ Tag',
            'add_new_item'      => 'New FAQ Tag',
            'new_item_name'     => 'New FAQ Tag Name',
            'menu_name'         => 'FAQ Tags'
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'faq-tag')
        );
        register_taxonomy('faq_tag', array('faq'), $args);
    }

    //FAQのレンダリング
    public function faq_shortcode($atts) {
        $atts = shortcode_atts(array(
            'categories' => '',
            'limit' => -1,
            'order' => 'ASC',
            'orderby' => 'menu_order'
        ), $atts, 'faq');

        $args = array(
            'post_type' => 'faq',
            'posts_per_page' => $atts['limit'],
            'order' => $atts['order'],
            'orderby' => $atts['orderby']
        );

        if (!empty($atts['categories'])) {
            $category_slugs = explode(',', $atts['categories']);
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'faq_category',
                    'field' => 'slug',
                    'terms' => explode(',', $atts['categories'])
                )
            );
        }

        $faq_query = new WP_Query($args);

        ob_start();

        if ($faq_query->have_posts()):
            $current_category = '';
            echo '<div class="faq-container">';
            while ($faq_query->have_posts()) : $faq_query->the_post();
                $content = wp_trim_words(get_the_content(), 200, '...');
                $categories = get_the_terms(get_the_ID(), 'faq_category');
                if ($categories && !is_wp_error($categories)){
                    $category = $categories[0];
                    if($current_category !== $category->name) {
                        if(!empty($current_category)) {
                            echo '</div>';
                        }
                        echo '<h2 class="faq-category-title">' . esc_html($category->name) . '</h2>';
                        echo '<div class="faq-category-container">';
                        $current_category = $category->name;
                    }
                }
                echo '<div class="faq-item" data-categories="' . esc_attr(implode(' ', $category_slugs)) . '">';
                echo '<h3 class="faq-question">' . get_the_title() . '</h3>';
                echo '<div class="faq-answer">' . esc_html($content);
                echo '<a href="' . get_permalink() . '" class="faq-detail-link">詳細を見る</a>';
                echo '</div>';
                echo '</div>';
            endwhile;
            if (!empty($current_category)){
                echo '</div>';
            }
            echo '</div>';
        else :
            echo 'No FAQs found.';
        endif;

        wp_reset_postdata();

        return ob_get_clean();
    }

    //FAQのシングルページを表示
    public function display_faq_single(){
        if (is_singular('faq')) {
            global $post;

            get_header();
            echo '<div class="site-main">';
            echo '<div class="faq-single-container">';
            echo '<h1 class="faq-question">' . get_the_title() . '</h1>';
            echo '<div class="faq-answer">' . $post->post_content . '</div>';
            echo '<a href="' . home_url('/faq-p') . '">戻る</a>';
            echo '</div>';
            echo '</div>';

            get_footer();
            exit;
        }
    }

    public function faq_search_form_shortcode() {
        return '
            <div class="faq-search-form">
                <input type="text" id="faq-search-input" placeholder="キーワードを入力してください">
                <!-- select id="faq-category-select">
                    <option value="">All Categories</option>' . $this->get_faq_category_options() . '
                </select -->
            </div>';
    }
    private function get_faq_category_options() {
        $categories = get_terms('faq_category');
        $options = "";
        foreach ($categories as $category) {
            $options .= '<option value="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</option>';
        }
        return $options;
    }
    public function enqueue_faq_scripts() {
        wp_enqueue_script('faq-scripts', plugins_url('js/searchable-faq-scripts.js', __FILE__), array('jquery'), '1.0', true);
    }

    //CSS読み込み
    public function enqueue_faq_styles() {
        // プラグインのCSSをテーマより後に読み込む
        wp_enqueue_style('theme-styles', get_stylesheet_uri());
        wp_enqueue_style('faq-styles', plugins_url('css/searchable-faq-style.css', __FILE__), array('theme-styles'), '1.0', 'all');
    
    }

}

function searchable_faq_init() {
    $searchable_faq = new SearchableFAQ();
}



add_action('plugins_loaded', 'SearchableFAQ\\searchable_faq_init');
register_activation_hook(__FILE__, 'SearchableFAQ\\activate_searchable_faq');
register_deactivation_hook(__FILE__, 'SearchableFAQ\\deactivate_searchable_faq');

function activate_searchable_faq(){
}

function deactivate_searchable_faq(){
}
