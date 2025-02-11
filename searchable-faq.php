<?php
/*
* Plugin Name: Searchable FAQ
Plugin URI: http://github.com/masomi79/sarchable-faq
Description: A simple FAQ plugin that allow you to create, search and display FAQs.
Version: 1.0
Author: masomi79
Author URI: https://massumifukuda.work/wp/
License: GPL2
Copyright 2025 masomi79 (email : masomi79@gmail.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
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
        add_action('template_redirect', array($this, 'load_taxonomy_template'));
        add_action('wp_insert_post', array($this, 'set_default_view_count'), 10, 3);
        remove_action('template_redirect', array($this, 'redirect_archive_to_page_by_slug'));
        add_action('pre_get_posts', array($this, 'redirect_archive_to_page_by_slug'));
        add_filter('template_include', array($this, 'load_custom_taxonomy_template'));
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
            'order' => 'DESC',
            'orderby' => 'meta_value_num',
            'meta_key' => 'faq_view_count'
        ), $atts, 'faq');
    
        $category_slugs = !empty($atts['categories']) ? explode(',', $atts['categories']) : array();
    
        ob_start();
        echo '<div class="faq-container">';
    
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
    
            if ($faq_query->have_posts()) {
                $category = get_term_by('slug', $category_slug, 'faq_category');
                echo '<h2 class="faq-category-title">' . esc_html($category->name) . '</h2>';
                echo '<div class="faq-category-container">';
    
                while ($faq_query->have_posts()) : $faq_query->the_post();
                    $content = get_the_content();
                    $view_count = get_post_meta(get_the_ID(), 'faq_view_count', true);
                    $view_count = $view_count ? $view_count : '0';
    
                    echo '<div class="faq-item" data-post-id="' . esc_attr(get_the_ID()) . '">';
                    echo '<h3 class="faq-question">' . esc_html(get_the_title());
                    echo '<span class="faq-view-count">(' . esc_html($view_count) . ')</span>';
                    echo '</h3>';
                    echo '<div class="faq-answer">' . esc_html($content) . '</div>';
                    echo '</div>';
                endwhile;
    
                echo '</div>';
            }
    
            wp_reset_postdata();
        }
    
        echo '</div>';
        return ob_get_clean();
    }

    //FAQのシングルページを表示
    public function display_faq_single(){
        if (is_singular('faq')) {
            global $post;

            //閲覧回数を処理
            $view_count = get_post_meta($post->ID, 'faq_view_count', true);
            $view_count = $view_count ? $view_count + 1 : 1;
            update_post_meta($post->ID, 'faq_view_count', $view_count);

            get_header();
            echo '<div class="site-main entry-content has-global-padding">';
            echo '<div class="faq-single-container">';
            echo '<h1 class="faq-question">' . esc_html(get_the_title()) . '</h1>';
            echo '<div class="faq-answer">' . esc_html($post->post_content) . '</div>';

            //タグの処理
            echo '<div class="faq-tags">';

            $tags = get_the_terms($post->ID, 'faq_tag');

            if ($tags && !is_wp_error($tags)) {
                $tag_links = array();
                foreach($tags as $tag){
                    $tag_links[] = '<a href="' . esc_url(get_term_link($tag)) . '">' . esc_html($tag->name) . '</a>';
                }

                echo '<p>Tags :' . implode(', ', array_map('esc_url', $tag_links)) . '</p>';
            }else{
                echo '<br class="notags">';
            }

            echo '</div>';

            echo '<p><a href="' . esc_url(home_url('/faq-p')) . '">戻る</a></p>';
            echo '<p>閲覧数：' . esc_html($view_count) . '</p>';
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

    //閲覧数カウントをセットする
    public function set_default_view_count($post_id, $post, $update) {
        if($post->post_type === 'faq' && !$update) {
            add_post_meta($post_id, 'faq_view_count', 0, true);
        }
    }

    //tagごとの一覧ページのテンプレートを読み込む
    public function load_taxonomy_template() {
        if (is_tax('faq_tag')) {
            include(plugin_dir_path(__FILE__) . 'taxonomy-faq_tag.php');
            exit;
        }
    }

    //カテゴリ用カスタムテンプレートを読み込む
    public function load_custom_taxonomy_template($template) {
        if (is_tax('faq_category')) {
            $custom_template = plugin_dir_path(__FILE__) . 'taxonomy-faq_category.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }

    //JS読み込み
    public function enqueue_faq_scripts() {
        wp_enqueue_script('faq-scripts', plugins_url('js/searchable-faq-scripts.js', __FILE__), array('jquery'), '1.0', true);
        wp_localize_script('faq-scripts', 'faqAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    }

    //CSS読み込み
    public function enqueue_faq_styles() {
        // プラグインのCSSをテーマより後に読み込む
        wp_enqueue_style('theme-styles', get_stylesheet_uri());
        wp_enqueue_style('faq-styles', plugins_url('css/searchable-faq-style.css', __FILE__), array('theme-styles'), '1.0', 'all');
    
    }

    //アーカイブページをカスタムテンプレートにリダイレクト
    public function redirect_archive_to_page_by_slug($query) {
        if (!is_admin() && $query->is_main_query() && is_post_type_archive('faq')) {
            $template = plugin_dir_path(__FILE__) . 'archive-faq.php';
            if (file_exists($template)) {
                include($template);
                exit;
            }
        }
    }
}

function searchable_faq_init() {
    $searchable_faq = new SearchableFAQ();
}

add_action('plugins_loaded', 'SearchableFAQ\\searchable_faq_init');
add_action('wp_ajax_increment_faq_view_count', 'SearchableFAQ\\increment_faq_view_count');
add_action('wp_ajax_nopriv_increment_faq_view_count', 'SearchableFAQ\\increment_faq_view_count');

function increment_faq_view_count() {
    if (isset($_POST['post_id'])) {
        $post_id = intval($_POST['post_id']);
        $view_count = get_post_meta($post_id, 'faq_view_count', true);
        $view_count = $view_count ? $view_count + 1 : 1;
        update_post_meta($post_id, 'faq_view_count', $view_count);
        echo esc_html($view_count);
    }
    wp_die();
}

register_activation_hook(__FILE__, 'SearchableFAQ\\activate_searchable_faq');
register_deactivation_hook(__FILE__, 'SearchableFAQ\\deactivate_searchable_faq');

function activate_searchable_faq(){
}

function deactivate_searchable_faq(){
}
