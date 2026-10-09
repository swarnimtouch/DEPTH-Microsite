$(document).ready(function() {
    let currentStep = 0;
    const steps = $('.quiz-question-step');
    const totalSteps = steps.length;
    const counter = $('#quizQuestionCounter');
    const nextBtn = $('#nextQuestionBtn');
    const prevBtn = $('#prevQuestionBtn');
    const submitBtn = $('#submitQuizBtn');
    const form = $('#quizForm');
    const feedbackSubmitted = form.data('feedback-submitted') === 1;

    function updateStep() {
        steps.addClass('d-none');
        steps.eq(currentStep).removeClass('d-none');
        counter.text(`Question ${currentStep + 1} of ${totalSteps}`);
        prevBtn.toggleClass('d-none', currentStep === 0);
        nextBtn.toggleClass('d-none', !canContinue() || currentStep === totalSteps - 1);
        submitBtn.toggleClass('d-none', !canContinue() || currentStep !== totalSteps - 1);
        $('#quizErrorMsg').addClass('d-none');
    }

    function currentQuestionAnswered() {
        const current = steps.eq(currentStep);
        const textarea = current.find('textarea[name^="answers"]');

        if (textarea.length) {
            return $.trim(textarea.val()).length > 0;
        }

        return current.find('input[type="radio"]:checked').length > 0;
    }

    function showQuestionError(message) {
        $('#quizErrorMsg').text(message).removeClass('d-none');
    }

    function canContinue() {
        return feedbackSubmitted || currentQuestionAnswered();
    }
    
    $(window).scroll(function() {
        if ($(window).scrollTop() > 50) {
            $('.navbar').addClass('shadow-sm');
        } else {
            $('.navbar').removeClass('shadow-sm');
        }
    });

    $('.quiz-option-card').click(function() {
        if (feedbackSubmitted) {
            return;
        }

        const step = $(this).closest('.quiz-question-step');
        step.find('.quiz-option-card').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
        updateStep();
    });

    $('textarea[name^="answers"]').on('input', updateStep);

    nextBtn.on('click', function() {
        if (!canContinue()) {
            showQuestionError('Please select an option before continuing.');
            return;
        }

        if (currentStep < totalSteps - 1) {
            currentStep++;
            updateStep();
        }
    });

    prevBtn.on('click', function() {
        if (currentStep > 0) {
            currentStep--;
            updateStep();
        }
    });

    form.submit(function(e) {
        let $errorMsg = $('#quizErrorMsg');
        let unanswered = false;

        steps.each(function() {
            const textarea = $(this).find('textarea[name^="answers"]');
            if (textarea.length) {
                if (!$.trim(textarea.val()).length) {
                    unanswered = true;
                }

                return;
            }

            if (!$(this).find('input[type="radio"]:checked').length) {
                unanswered = true;
            }
        });

        if (unanswered) {
            e.preventDefault();
            $errorMsg.text("Please answer all questions before submitting.").removeClass('d-none');
            return false;
        }

        $errorMsg.addClass('d-none');
    });

    if (totalSteps) {
        updateStep();
    }
});
