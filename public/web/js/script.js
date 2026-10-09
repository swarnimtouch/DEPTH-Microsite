$(document).ready(function() {
    
    $('.feature-card').hover(
        function() {
            
            $(this).css('border', '1px solid #e3f5ee');
            $(this).find('.feature-icon-box').css('background-color', '#c9ede0'); 
        }, 
        function() {
            
            $(this).css('border', 'none');
            $(this).find('.feature-icon-box').css('background-color', 'var(--light-green-bg)');
        }
    );
    
    $(window).scroll(function() {
        if ($(window).scrollTop() > 50) {
            $('.navbar').addClass('shadow-sm');
        } else {
            $('.navbar').removeClass('shadow-sm');
        }
    });

    $('.floating-card').css({
        'opacity': 0,
        'margin-bottom': '-20px'
    }).animate({
        'opacity': 1,
        'margin-bottom': '0px'
    }, 1000);
});
