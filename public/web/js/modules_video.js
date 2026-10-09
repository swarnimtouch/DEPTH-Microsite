$(document).ready(function() {
    
    $(window).scroll(function() {
        if ($(window).scrollTop() > 50) {
            $('.navbar').addClass('shadow-sm');
        } else {
            $('.navbar').removeClass('shadow-sm');
        }
    });

    $('#playVideoBtn').click(function() {
        $(this).css('transform', 'translate(-50%, -50%) scale(0.9)');
        
        setTimeout(() => {
            $(this).css('transform', 'translate(-50%, -50%) scale(1)');
            alert("Video player interface would initialize here.");
        }, 150);
    });

});
