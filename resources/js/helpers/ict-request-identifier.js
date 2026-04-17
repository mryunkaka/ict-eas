export function wireIctRequestIdentifier({
    dateInput,
    subjectInput,
    endpointUrl,
}) {
    if (!dateInput || !subjectInput || !endpointUrl) {
        return () => {};
    }

    const initialSubject = subjectInput.value || '';
    let subjectDirty = false;

    const onSubjectInput = () => {
        subjectDirty = subjectInput.value !== initialSubject;
    };

    subjectInput.addEventListener('input', onSubjectInput);

    let lastRequestedDate = null;

    async function refresh() {
        const dateValue = String(dateInput.value || '');
        if (!dateValue) return;

        if (subjectDirty && String(subjectInput.value || '') !== initialSubject) {
            return;
        }

        lastRequestedDate = dateValue;

        try {
            const url = new URL(endpointUrl, window.location.origin);
            url.searchParams.set('needed_at', dateValue);

            const res = await fetch(url.toString(), {
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) return;

            const data = await res.json();
            if (!data?.identifier) return;

            // avoid race: only apply last request
            if (lastRequestedDate !== dateValue) return;

            subjectInput.value = String(data.identifier).toUpperCase();
        } catch (_) {
            // ignore
        }
    }

    const onDateChange = () => refresh();
    dateInput.addEventListener('change', onDateChange);
    dateInput.addEventListener('input', onDateChange);

    // initial run
    refresh();

    return () => {
        subjectInput.removeEventListener('input', onSubjectInput);
        dateInput.removeEventListener('change', onDateChange);
        dateInput.removeEventListener('input', onDateChange);
    };
}

