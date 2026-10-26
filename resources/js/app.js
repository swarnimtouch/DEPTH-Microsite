import './bootstrap';
import './live-questions';

document.addEventListener('DOMContentLoaded', () => {
    const panel = document.querySelector('[data-comments-panel], [data-session-comments-panel]');

    if (!panel) {
        return;
    }

    const form = panel.querySelector('[data-comment-form], [data-session-comment-form]');
    const textarea = panel.querySelector('[data-comment-input], [data-session-comment-input]');
    const parentInput = panel.querySelector('[data-comment-parent-input]');
    const replyingBanner = panel.querySelector('[data-comment-replying]');
    const replyingName = panel.querySelector('[data-comment-replying-name]');
    const replyCancel = panel.querySelector('[data-comment-reply-cancel]');
    const list = panel.querySelector('[data-comments-list], [data-session-comments-list]');
    const counter = panel.querySelector('[data-comments-count], [data-session-comments-count]');
    const emptyState = panel.querySelector('[data-comments-empty], [data-session-comments-empty]');
    const loadMoreButton = panel.querySelector('[data-comments-load-more]');
    const loadMoreWrap = panel.querySelector('[data-comments-load-wrap]');
    const channel = panel.dataset.commentsChannel || (panel.dataset.sessionId ? `comments.${panel.dataset.sessionId}` : null);
    let totalComments = Number(panel.dataset.commentsTotal || 0);
    let visibleComments = Number(panel.dataset.commentsVisible || 0);
    let isLoadingMore = false;
    const comments = new Set(
        Array.from(panel.querySelectorAll('[data-comment-id]')).map((item) => item.dataset.commentId)
    );

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));

    const updateCount = () => {
        if (counter) {
            counter.textContent = String(totalComments);
        }

        loadMoreWrap?.classList.toggle('d-none', visibleComments >= totalComments);
    };

    const commentsLoadUrl = () => {
        if (panel.matches('[data-comments-panel]')) {
            return new URL(`${window.location.pathname.replace(/\/$/, '')}/comments`, window.location.origin);
        }

        return new URL(panel.dataset.commentsLoadUrl || window.location.href, window.location.href);
    };

    const commentUpvoteUrl = (commentId) => `${window.location.pathname.replace(/\/$/, '')}/comments/${encodeURIComponent(commentId)}/upvote`;

    const sortTopLevelComments = () => {
        if (!panel.matches('[data-comments-panel]') || !list) {
            return;
        }

        Array.from(list.querySelectorAll(':scope > .module-comment-item'))
            .sort((first, second) => {
                const firstVotes = Number(first.querySelector(':scope [data-comment-upvotes]')?.textContent || 0);
                const secondVotes = Number(second.querySelector(':scope [data-comment-upvotes]')?.textContent || 0);

                if (secondVotes !== firstVotes) {
                    return secondVotes - firstVotes;
                }

                return Number(second.dataset.commentId || 0) - Number(first.dataset.commentId || 0);
            })
            .forEach((item) => list.append(item));
    };

    const renderReply = (comment, highlight = false) => {
        const item = document.createElement('div');
        item.className = `module-comment-reply${highlight ? ' is-new' : ''}`;
        item.dataset.commentId = comment.id;
        item.innerHTML = `
            <div class="module-comment-avatar">${escapeHtml(comment.initial || 'U')}</div>
            <div class="module-comment-body">
                <div class="module-comment-topline">
                    <strong>${escapeHtml(comment.name || 'User')}</strong>
                    <span>${escapeHtml(comment.created_at || 'just now')}</span>
                </div>
                <p>${escapeHtml(comment.comment || '')}</p>
                ${panel.matches('[data-comments-panel]') ? `
                    <div class="module-comment-actions">
                        <button
                            type="button"
                            class="btn module-comment-upvote-btn${comment.upvoted_by_user ? ' is-upvoted' : ''}"
                            data-comment-upvote
                            data-upvote-url="${escapeHtml(comment.upvote_url || commentUpvoteUrl(comment.id))}"
                            aria-pressed="${comment.upvoted_by_user ? 'true' : 'false'}"
                        >
                            <i class="bi bi-arrow-up"></i>
                            <span>Upvote</span>
                            <span data-comment-upvotes>${escapeHtml(comment.upvotes_count || 0)}</span>
                        </button>
                    </div>
                ` : ''}
            </div>
        `;

        return item;
    };

    const renderComment = (comment, highlight = false) => {
        const item = document.createElement('div');
        item.className = `module-comment-item session-comment-item${highlight ? ' is-new' : ''}`;
        item.dataset.commentId = comment.id;
        item.innerHTML = `
            <div class="module-comment-avatar">${escapeHtml(comment.initial || 'U')}</div>
            <div class="module-comment-body">
                <div class="module-comment-topline">
                    <strong>${escapeHtml(comment.name || 'User')}</strong>
                    <span>${escapeHtml(comment.created_at || 'just now')}</span>
                </div>
                <p>${escapeHtml(comment.comment || '')}</p>
                ${panel.matches('[data-comments-panel]') ? `
                    <div class="module-comment-actions">
                        <button
                            type="button"
                            class="btn module-comment-upvote-btn${comment.upvoted_by_user ? ' is-upvoted' : ''}"
                            data-comment-upvote
                            data-upvote-url="${escapeHtml(comment.upvote_url || commentUpvoteUrl(comment.id))}"
                            aria-pressed="${comment.upvoted_by_user ? 'true' : 'false'}"
                        >
                            <i class="bi bi-arrow-up"></i>
                            <span>Upvote</span>
                            <span data-comment-upvotes>${escapeHtml(comment.upvotes_count || 0)}</span>
                        </button>
                        <button
                            type="button"
                            class="btn module-comment-reply-btn"
                            data-comment-reply
                            data-comment-id="${escapeHtml(comment.id)}"
                            data-comment-name="${escapeHtml(comment.name || 'User')}"
                        >
                            <i class="bi bi-reply"></i>
                            <span>Reply</span>
                        </button>
                    </div>
                    <div class="module-comment-replies${comment.replies?.length ? '' : ' d-none'}" data-comment-replies></div>
                ` : ''}
            </div>
        `;

        const replies = item.querySelector('[data-comment-replies]');
        comment.replies?.forEach((reply) => {
            comments.add(String(reply.id));
            replies?.append(renderReply(reply));
        });

        return item;
    };

    const showComment = (comment, highlight = false, placement = 'prepend') => {
        if (!comment?.id || comments.has(String(comment.id))) {
            return;
        }

        if (comment.parent_id) {
            const parent = list?.querySelector(`[data-comment-id="${CSS.escape(String(comment.parent_id))}"]`);
            const replies = parent?.querySelector('[data-comment-replies]');
            const item = renderReply(comment, highlight);

            if (!replies) {
                return;
            }

            comments.add(String(comment.id));
            replies.classList.remove('d-none');
            replies.append(item);

            if (highlight) {
                window.setTimeout(() => item.classList.remove('is-new'), 1400);
            }

            return;
        }

        comments.add(String(comment.id));
        emptyState?.remove();

        const item = renderComment(comment, highlight);

        if (placement === 'append') {
            list?.append(item);
        } else {
            list?.prepend(item);
            totalComments += 1;
        }

        visibleComments += 1;
        updateCount();
        sortTopLevelComments();

        if (highlight) {
            window.setTimeout(() => item.classList.remove('is-new'), 1400);
        }
    };

    const resetReplyTarget = () => {
        if (parentInput) {
            parentInput.value = '';
        }

        replyingBanner?.classList.add('d-none');

        if (textarea && panel.matches('[data-comments-panel]')) {
            textarea.placeholder = 'Write a comment...';
        }
    };

    list?.addEventListener('click', (event) => {
        const upvoteButton = event.target.closest('[data-comment-upvote]');

        if (upvoteButton) {
            event.preventDefault();

            if (upvoteButton.disabled || !upvoteButton.dataset.upvoteUrl) {
                return;
            }

            upvoteButton.disabled = true;

            window.axios.post(upvoteButton.dataset.upvoteUrl, {}, {
                headers: {Accept: 'application/json'},
            })
                .then((response) => {
                    const count = upvoteButton.querySelector('[data-comment-upvotes]');
                    upvoteButton.classList.toggle('is-upvoted', Boolean(response.data.upvoted));
                    upvoteButton.setAttribute('aria-pressed', response.data.upvoted ? 'true' : 'false');

                    if (count) {
                        count.textContent = String(response.data.upvotes_count ?? 0);
                    }

                    sortTopLevelComments();
                })
                .catch(() => window.toastr?.error('Unable to update upvote right now'))
                .finally(() => {
                    upvoteButton.disabled = false;
                });

            return;
        }

        const button = event.target.closest('[data-comment-reply]');

        if (!button || !parentInput || !textarea) {
            return;
        }

        parentInput.value = button.dataset.commentId || '';

        if (replyingName) {
            replyingName.textContent = button.dataset.commentName || 'this comment';
        }

        replyingBanner?.classList.remove('d-none');
        textarea.placeholder = `Reply to ${button.dataset.commentName || 'this comment'}...`;
        textarea.focus();
    });

    replyCancel?.addEventListener('click', resetReplyTarget);

    loadMoreButton?.addEventListener('click', async () => {
        if (isLoadingMore) {
            return;
        }

        const renderedComments = Array.from(list?.querySelectorAll(':scope > .module-comment-item[data-comment-id]') || []);
        const offset = renderedComments.length;

        if (!offset) {
            loadMoreWrap?.classList.add('d-none');
            return;
        }

        isLoadingMore = true;
        loadMoreButton.setAttribute('disabled', 'disabled');
        loadMoreButton.classList.add('is-loading');

        try {
            const url = commentsLoadUrl();
            url.searchParams.set('offset', offset);

            const response = await window.axios.get(url.toString(), {
                headers: {Accept: 'application/json'},
            });

            response.data.comments?.forEach((comment) => showComment(comment, false, 'append'));

            if (!response.data.has_more) {
                totalComments = visibleComments;
            }

            updateCount();
        } catch (error) {
            window.toastr?.error('Unable to load more comments right now');
        } finally {
            isLoadingMore = false;
            loadMoreButton.removeAttribute('disabled');
            loadMoreButton.classList.remove('is-loading');
        }
    });

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const comment = textarea?.value.trim();

        if (!comment) {
            textarea?.focus();
            return;
        }

        button?.setAttribute('disabled', 'disabled');
        panel.classList.add('is-posting');

        try {
            const response = await window.axios.post(form.action, new FormData(form), {
                headers: {
                    Accept: 'application/json',
                },
            });

            textarea.value = '';
            resetReplyTarget();
            showComment(response.data.comment, true);
        } catch (error) {
            const message = error.response?.data?.message || 'Unable to post comment right now';
            window.toastr?.error(message);
        } finally {
            button?.removeAttribute('disabled');
            panel.classList.remove('is-posting');
        }
    });

    if (window.Echo && channel) {
        window.Echo.channel(channel)
            .listen('.comment.created', (event) => showComment(event.comment, true));
    }

    updateCount();
});
