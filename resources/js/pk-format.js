function formatCnicPk(raw) {
    const digits = raw.replace(/\D/g, '').slice(0, 13);
    if (digits.length <= 5) return digits;
    if (digits.length <= 12) return digits.slice(0, 5) + '-' + digits.slice(5);
    return digits.slice(0, 5) + '-' + digits.slice(5, 12) + '-' + digits.slice(12);
}

function formatPhonePk(raw) {
    const digits = raw.replace(/\D/g, '').slice(0, 11);
    if (digits.length <= 4) return digits;
    return digits.slice(0, 4) + '-' + digits.slice(4);
}

function attachPkMask(el, formatter) {
    if (el.dataset.pkMaskAttached) return;
    el.dataset.pkMaskAttached = '1';

    const apply = () => {
        const formatted = formatter(el.value);
        if (formatted !== el.value) el.value = formatted;
    };
    apply();
    el.addEventListener('input', apply);
}

function initPkFormatMasks() {
    document.querySelectorAll('input[data-mask="cnic-pk"]').forEach((el) => attachPkMask(el, formatCnicPk));
    document.querySelectorAll('input[data-mask="phone-pk"]').forEach((el) => attachPkMask(el, formatPhonePk));
}

document.addEventListener('DOMContentLoaded', initPkFormatMasks);
