const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
const monthFormat = new Intl.DateTimeFormat('en-KE', { month: 'long', year: 'numeric', timeZone: 'UTC' });
const dayFormat = new Intl.DateTimeFormat('en-KE', { dateStyle: 'full', timeZone: 'UTC' });
const stepHeadings = ['Choose a Service', 'Select Date and Time', 'Your Details', 'Review Your Request'];

export function initBookingWizard(form) {
    const steps = [...form.querySelectorAll('[data-booking-step]')];
    const progress = document.querySelector('[data-booking-progress]');
    const back = form.querySelector('[data-booking-back]');
    const next = form.querySelector('[data-booking-next]');
    const submit = form.querySelector('[data-booking-submit]');
    const heading = form.querySelector('[data-booking-heading]');
    const stepLabel = form.querySelector('[data-booking-step-label]');
    const calendar = form.querySelector('[data-calendar]');
    const grid = calendar.querySelector('[data-calendar-grid]');
    const message = calendar.querySelector('[data-calendar-message]');
    const today = form.dataset.today;
    const date = form.elements.preferred_date;
    const originalTime = form.elements.preferred_time;
    const packageSelect = form.elements.price_package_id;
    const quotePhotos = form.querySelector('[data-quote-photos]');
    const packageOptions = [...packageSelect.querySelectorAll('[data-package-service]')];
    const time = document.createElement('select');
    time.name = originalTime.name;
    time.required = true;
    originalTime.replaceWith(time);

    let stepIndex = 0;
    let month = new Date((date.value || today).slice(0, 7) + '-01T00:00:00Z');
    let dates = {};
    let request;
    const selectedService = () => form.querySelector('[name="service_id"]:checked');
    const monthKey = () => month.toISOString().slice(0, 7);

    function showStep(index, focus = true) {
        stepIndex = index;
        steps.forEach((step, position) => { step.hidden = position !== index; });
        heading.textContent = stepHeadings[index];
        stepLabel.textContent = `Step ${index + 1} of ${steps.length}`;
        [...progress.children].forEach((item, position) => {
            if (position === index) item.setAttribute('aria-current', 'step');
            else item.removeAttribute('aria-current');
        });
        back.hidden = index === 0;
        next.hidden = index === steps.length - 1;
        if (submit) submit.hidden = index !== steps.length - 1;
        if (index === 1) loadCalendar();
        if (index === 3) renderReview();
        if (focus) {
            const legend = steps[index].querySelector('legend');
            legend.tabIndex = -1;
            legend.focus();
        }
    }

    function prepareService(keepPackage = false) {
        const service = selectedService();
        const oldPackage = keepPackage ? packageSelect.value : '';
        packageSelect.replaceChildren(new Option('Request a Quote', ''));
        for (const option of packageOptions) {
            if (option.dataset.packageService === service?.value) packageSelect.add(option);
        }
        packageSelect.value = [...packageSelect.options].some((option) => option.value === oldPackage) ? oldPackage : '';
        updateQuotePhotosVisibility();
        const moving = service?.dataset.serviceSlug === 'moving-services';
        const location = form.querySelector('[data-location]');
        const addresses = form.querySelector('[data-moving-addresses]');
        location.hidden = moving;
        form.elements.location.required = !moving;
        form.elements.location.disabled = moving;
        addresses.hidden = !moving;
        for (const name of ['pickup_location', 'destination']) {
            form.elements[name].required = moving;
            form.elements[name].disabled = !moving;
        }
        form.querySelector('[data-instructions-label]').textContent = {
            'moving-services': 'Access, stairs, packing or other moving requirements',
            'fumigation-services': 'Pest problem and property details',
            'house-deep-cleaning': 'Property size and cleaning requirements',
            'sofa-mattress-cleaning': 'Items and upholstery to clean',
        }[service?.dataset.serviceSlug] ?? 'Additional instructions';
    }

    function updateQuotePhotosVisibility() {
        const isQuoteRequest = !packageSelect.value || packageSelect.selectedOptions[0]?.dataset.pricingType === 'quote';
        quotePhotos.hidden = !isQuoteRequest;
        quotePhotos.querySelector('input[type="file"]').disabled = !isQuoteRequest;
    }

    function renderCalendar() {
        calendar.querySelector('[data-month-title]').textContent = monthFormat.format(month);
        calendar.querySelector('[data-month-offset="-1"]').disabled = monthKey() <= today.slice(0, 7);
        calendar.querySelector('[data-month-offset="1"]').disabled =
            monthKey() >= new Date(Date.parse(today + 'T00:00:00Z') + 120 * 864e5).toISOString().slice(0, 7);
        grid.replaceChildren();
        for (const name of dayNames) {
            const heading = document.createElement('span');
            heading.textContent = name;
            grid.append(heading);
        }
        for (let i = 0; i < (month.getUTCDay() + 6) % 7; i++) grid.append(document.createElement('span'));

        const day = new Date(month);
        while (day.getUTCMonth() === month.getUTCMonth()) {
            const key = day.toISOString().slice(0, 10);
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = day.getUTCDate();
            button.disabled = !dates[key]?.length;
            button.dataset.date = key;
            button.setAttribute('aria-label', dayFormat.format(day));
            button.setAttribute('aria-pressed', String(key === date.value));
            if (key === today) button.setAttribute('aria-current', 'date');
            grid.append(button);
            day.setUTCDate(day.getUTCDate() + 1);
        }
    }

    function renderTimes(preferred = time.value) {
        const slots = dates[date.value] ?? [];
        time.replaceChildren(new Option(slots.length ? 'Select a time' : 'Choose an available date', ''));
        for (const slot of slots) {
            const [hour, minute] = slot.split(':').map(Number);
            const period = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            const label = minute === 0 ? `${displayHour} ${period}` : `${displayHour}:${String(minute).padStart(2, '0')} ${period}`;
            time.add(new Option(label, slot));
        }
        time.value = slots.includes(preferred) ? preferred : '';
    }

    async function loadCalendar() {
        const service = selectedService();
        if (!service) return;
        request?.abort();
        const controller = new AbortController();
        request = controller;
        const previousTime = time.value || originalTime.value;
        dates = {};
        renderCalendar();
        message.textContent = 'Checking available dates…';
        const url = new URL(form.dataset.availabilityUrl);
        url.search = new URLSearchParams({ service_id: service.value, month: monthKey() });
        try {
            const response = await fetch(url, { signal: controller.signal, headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error();
            dates = (await response.json()).dates;
            message.textContent = Object.values(dates).some((slots) => slots.length) ? '' : 'No times are available this month.';
            renderCalendar();
            renderTimes(previousTime);
        } catch (error) {
            if (error.name === 'AbortError') return;
            message.textContent = 'Availability couldn’t load. Go back and try again.';
            renderTimes();
        }
    }

    function renderReview() {
        const data = new FormData(form);
        const review = form.querySelector('[data-review]');
        review.replaceChildren();
        const entries = [
            ['Service', selectedService()?.closest('label').querySelector('strong').textContent],
            ['Package', packageSelect.selectedOptions[0]?.textContent.trim()],
            ['Requested date and time', data.get('preferred_date') + ' at ' + data.get('preferred_time') + ' (Nairobi time)'],
            ['Name', data.get('customer_name')], ['Phone', data.get('phone')], ['Email', data.get('email')],
            ['Service location', data.get('location')], ['Pickup', data.get('pickup_location')], ['Destination', data.get('destination')],
            ['Instructions', data.get('instructions')],
            ['Quote photos', data.getAll('quote_photos[]').filter((file) => file instanceof File && file.name).map((file) => file.name).join(', ')],
        ];
        for (const [label, value] of entries) {
            if (!value) continue;
            const term = document.createElement('dt');
            const detail = document.createElement('dd');
            term.textContent = label;
            detail.textContent = value;
            review.append(term, detail);
        }
    }

    form.addEventListener('change', (event) => {
        if (event.target.name === 'price_package_id') {
            updateQuotePhotosVisibility();
            return;
        }
        if (event.target.name !== 'service_id') return;
        window.dispatchEvent(new CustomEvent('site-analytics-event', { detail: { name: 'service_selected' } }));
        date.value = '';
        time.value = '';
        prepareService();
    });
    calendar.addEventListener('click', (event) => {
        const button = event.target.closest('button');
        if (!button || button.disabled) return;
        if (button.dataset.monthOffset) {
            month.setUTCMonth(month.getUTCMonth() + Number(button.dataset.monthOffset));
            date.value = '';
            renderTimes();
            loadCalendar();
        } else if (button.dataset.date) {
            date.value = button.dataset.date;
            renderTimes('');
            grid.querySelectorAll('[aria-pressed]').forEach((day) => {
                day.setAttribute('aria-pressed', String(day === button));
            });
        }
    });
    next.addEventListener('click', () => {
        if (stepIndex === 1 && !date.value) {
            message.textContent = 'Choose an available date to continue.';
            return;
        }
        const invalid = steps[stepIndex].querySelector(':invalid:not(fieldset)');
        if (invalid) {
            invalid.reportValidity();
            return;
        }
        showStep(stepIndex + 1);
    });
    back.addEventListener('click', () => showStep(stepIndex - 1));
    form.addEventListener('form-invalid', ({ detail }) => {
        const field = form.querySelector('[name="' + CSS.escape(detail) + '"]')
            ?? form.querySelector('[name="' + CSS.escape(detail) + '[]"]');
        const index = steps.indexOf(field?.closest('fieldset'));
        if (index >= 0) showStep(index, false);
    });

    form.noValidate = true;
    progress.hidden = false;
    calendar.hidden = false;
    form.querySelector('[data-date-field]').hidden = true;
    prepareService(true);
    showStep(0, false);
}
