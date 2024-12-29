jQuery(document).ready(function($){
    //アコーディオンの処理
    $('.faq-question').click(function(){
        console.log('clicked');
        // $(this).parent('.faq-item').toggleClass('active');
    });

    //リアルタイム検索
    var $faqItems = $('.faq-item');
    function filterFAQs() {
        var searchTerm = $('#faq-search-input').val().toLowerCase();
//        var selectedCategory = $('#faq-category-select').val();

        $faqItems.each(function() {
            var $item = $(this);
            var question = $item.find('.faq-question').text().toLowerCase();
            var answer = $item.find('.faq-answer').text().toLowerCase();
  //          var categories = $item.data('categories').split(' ');

            var matchesSearch = question.includes(searchTerm) || answer.includes(searchTerm);
    //        var matchesCategory = selectedCategory === '' || categories.includes(selectedCategory);

            $item.toggle(matchesSearch/* && matchesCategory*/);
        });
    }

    $('#faq-search-input').on('input', filterFAQs);
//    $('#faq-category-select').on('change', filterFAQs);

    $('.faq-question').on('click', function() {
        var $answer = $(this).next('.faq-answer');
        $answer.slideToggle();

        if ($answer.is(':visible')) {
            var post_id = $(this).closest('.faq-item').data('post-id');
            $.post(faqAjax.ajaxurl, {
                action: 'increment_faq_view_count',
                post_id: post_id
            }, function(response) {
                $(this).find('.faq-view-count').text('(' + response + ')');
            }.bind(this));
        }
    });
});
