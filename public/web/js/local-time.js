(function () {
    function formatLocalDateTime(value) {
        if (!value) {
            return '';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat(undefined, {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        }).format(date);
    }

    function formatLocalDateRange(start, end) {
        const startText = formatLocalDateTime(start);
        const endText = formatLocalDateTime(end);

        if (startText && endText) {
            return `${startText} - ${endText}`;
        }

        return startText || endText || '';
    }

    function renderLocalTimes(root) {
        const scope = root || document;

        scope.querySelectorAll('[data-local-datetime]').forEach((element) => {
            const value = element.dataset.localDatetime;
            const fallback = element.dataset.fallback || element.textContent;
            element.textContent = formatLocalDateTime(value) || fallback;
        });

        scope.querySelectorAll('[data-local-datetime-range]').forEach((element) => {
            const value = formatLocalDateRange(element.dataset.localStart, element.dataset.localEnd);
            element.textContent = value || element.dataset.fallback || element.textContent;
        });
    }

    window.formatLocalDateTime = formatLocalDateTime;
    window.formatLocalDateRange = formatLocalDateRange;
    window.renderLocalTimes = renderLocalTimes;

    document.addEventListener('DOMContentLoaded', () => renderLocalTimes());
})();
