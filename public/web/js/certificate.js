$(document).ready(function() {
    
    $(window).scroll(function() {
        if ($(window).scrollTop() > 50) {
            $('.navbar').addClass('shadow-sm');
        } else {
            $('.navbar').removeClass('shadow-sm');
        }
    });

    $('#downloadCertBtn').click(function(e) {
        e.preventDefault();
        
        let $btn = $(this);
        let originalText = $btn.html();
        
        $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Generating PDF...');
        $btn.addClass('opacity-75').prop('disabled', true);
        
        setTimeout(function() {
            $btn.html(originalText).removeClass('opacity-75').prop('disabled', false);
            
            alert("Your certificate has been downloaded successfully!");
        }, 1500);
    });

});
