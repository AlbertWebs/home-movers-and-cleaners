export function initAjaxForm(form) {
    const status = form.querySelector('[data-form-status]');
    const button = form.querySelector('[type="submit"]');
    if (!status || !button) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (button.disabled) return;
        if (form.matches('[data-booking-wizard]') && button.hidden) return;

        const defaultLabel = button.textContent;
        form.querySelectorAll('[data-field-error]').forEach((node) => node.remove());
        form.querySelectorAll('[aria-invalid]').forEach((node) => {
            node.removeAttribute('aria-invalid');
            node.removeAttribute('aria-errormessage');
        });
        button.disabled = true;
        button.textContent = 'Sending…';
        status.textContent = '';
        form.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(form.action, {
                method: form.method,
                headers: { Accept: 'application/json' },
                body: new FormData(form),
            });
            const body = await response.json();
            if (response.status === 422 && body.errors) {
                const firstField = Object.keys(body.errors)[0];
                form.dispatchEvent(new CustomEvent('form-invalid', { detail: firstField }));
                for (const [name, messages] of Object.entries(body.errors)) {
                    const field = form.querySelector('[name="' + CSS.escape(name) + '"]');
                    if (!field || field.type === 'hidden') continue;
                    const error = document.createElement('small');
                    error.className = 'form-field-error';
                    error.dataset.fieldError = '';
                    error.id = 'error-' + name;
                    error.textContent = messages[0];
                    field.setAttribute('aria-invalid', 'true');
                    field.setAttribute('aria-errormessage', error.id);
                    (field.closest('label') ?? field).after(error);
                }
                status.textContent = body.errors[firstField][0];
                status.focus();
                return;
            }
            if (!response.ok) {
                status.textContent = response.status === 429
                    ? 'Too many requests. Wait a minute and try again.'
                    : response.status === 419
                        ? 'Your session expired. Refresh the page and try again.'
                        : 'We couldn’t complete your request. Please try again.';
                return;
            }
            if (body.redirect && new URL(body.redirect, location.href).origin === location.origin) {
                window.dispatchEvent(new CustomEvent('site-analytics-event', { detail: { name: 'booking_submitted' } }));
                location.assign(body.redirect);
                return;
            }
            status.textContent = body.message;
            window.dispatchEvent(new CustomEvent('site-analytics-event', { detail: { name: 'contact_form_submitted' } }));
            status.classList.add('form-status-success');
            form.reset();
        } catch {
            status.textContent = 'The connection was interrupted. Check your connection and try again.';
        } finally {
            button.disabled = false;
            button.textContent = defaultLabel;
            form.removeAttribute('aria-busy');
        }
    });
}
