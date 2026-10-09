$(document).ready(function() {
    
    $('.custom-form-control').focus(function() {
        $(this).parent().find('.custom-form-label').css('color', 'var(--primary-green)');
    }).blur(function() {
        $(this).parent().find('.custom-form-label').css('color', '#4a5568');
    });

    $('#doctorLoginForm').validate({
        rules: {
            loginEmail: { required: true, email: true }
        },
        
        messages: {
            loginEmail: "Please enter a valid email address"
        },
    });

    $('.profile-info').on('click', function(e) {
        e.stopPropagation();
        $(this).toggleClass('active'); 
    });

    $(document).on('click', function() {
        $('.profile-info').removeClass('active');
    });

    $('.profile-dropdown').on('click', function(e) {
        e.stopPropagation();
    });
});