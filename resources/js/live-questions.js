document.addEventListener('DOMContentLoaded', () => {
    const shell = document.querySelector('[data-live-questions]');
    const event = window.DEPTH_EVENT || {};
    const stateUrl = shell?.dataset.liveQuestionsUrl || event.liveQuestionsUrl;
    const channel = shell?.dataset.liveQuestionsChannel || event.liveQuestionsChannel;
    const answerUrlTemplate = shell?.dataset.liveQuestionAnswerUrl || window.DEPTH_LIVE_QUESTION_ANSWER_URL;
    if (!shell || !stateUrl || !answerUrlTemplate) return;

    const form = shell.querySelector('[data-live-question-form]');
    const options = shell.querySelector('[data-live-question-options]');
    const message = shell.querySelector('[data-live-question-message]');
    const submit = shell.querySelector('[data-live-question-submit]');
    let currentQuestion = null;
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));

    function render(payload) {
        currentQuestion = payload.question;
        if (!currentQuestion) { shell.hidden = true; return; }
        shell.hidden = false;
        shell.querySelector('[data-live-question-type]').textContent = currentQuestion.type === 'quiz' ? 'Live quiz' : 'Live poll';
        shell.querySelector('[data-live-question-text]').textContent = currentQuestion.question;
        options.innerHTML = currentQuestion.options.map(option => {
            const selected = Number(currentQuestion.selected_option_id) === Number(option.id);
            const resultClass = currentQuestion.answered && currentQuestion.type === 'quiz'
                ? (option.is_correct ? ' is-correct' : (selected ? ' is-wrong' : '')) : '';
            return `<label class="live-question-option${resultClass}">${currentQuestion.answered ? `<i class="live-question-result" style="width:${option.percent}%"></i>` : ''}<input type="radio" name="option_id" value="${option.id}" ${selected ? 'checked' : ''} ${currentQuestion.answered ? 'disabled' : ''}><span>${escapeHtml(option.text)}</span>${currentQuestion.answered ? `<strong>${option.percent}%</strong>` : ''}</label>`;
        }).join('');
        submit.hidden = currentQuestion.answered;
        message.hidden = !currentQuestion.answered;
        if (currentQuestion.answered) {
            message.textContent = currentQuestion.type === 'quiz'
                ? (currentQuestion.is_correct ? 'Correct answer!' : 'Answer submitted. The correct option is highlighted.')
                : `Vote submitted · ${currentQuestion.total_responses} response${currentQuestion.total_responses === 1 ? '' : 's'}`;
        }
    }

    async function refresh() {
        try { const response = await window.axios.get(stateUrl, {headers:{Accept:'application/json'}}); render(response.data); }
        catch (_) { /* keep the live room usable if the realtime panel is unavailable */ }
    }

    form.addEventListener('submit', async (submitEvent) => {
        submitEvent.preventDefault();
        const selected = form.querySelector('input[name="option_id"]:checked');
        if (!selected) { message.textContent = 'Please select an option.'; message.hidden = false; return; }
        submit.disabled = true;
        try {
            const url = answerUrlTemplate.replace('__QUESTION__', currentQuestion.id);
            const response = await window.axios.post(url, {option_id:selected.value}, {headers:{Accept:'application/json'}});
            render(response.data);
        } catch (error) { message.textContent = error.response?.data?.message || 'Unable to submit your answer.'; message.hidden = false; }
        finally { submit.disabled = false; }
    });

    if (window.Echo && channel) {
        window.Echo.channel(channel).listen('.live-question.changed', (change) => {
            if (change.action !== 'answered' || currentQuestion?.answered) refresh();
        });
    }
    refresh();
    window.setInterval(refresh, 4000);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') refresh();
    });
});
