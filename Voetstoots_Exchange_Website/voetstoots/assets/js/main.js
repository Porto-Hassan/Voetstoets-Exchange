// ============================================================
// assets/js/main.js — Voetstoots Exchange Client-Side Logic
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // ── Image preview on listing form ──
    const imgInput = document.getElementById('listing-image');
    const imgPreview = document.getElementById('image-preview');
    if (imgInput && imgPreview) {
        imgInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = e => {
                    imgPreview.src = e.target.result;
                    imgPreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ── Payment method selection on checkout ──
    const paymentOptions = document.querySelectorAll('.payment-option');
    paymentOptions.forEach(option => {
        option.addEventListener('click', function () {
            paymentOptions.forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    // ── Confirm delete actions ──
    const deleteForms = document.querySelectorAll('.confirm-delete');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const msg = this.dataset.confirm || 'Are you sure you want to delete this item?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // ── Auto-dismiss flash alerts after 5 seconds ──
    const alerts = document.querySelectorAll('.alert.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // ── Listing filter form: auto-submit on select change ──
    const filterSelects = document.querySelectorAll('.auto-submit-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function () {
            this.closest('form').submit();
        });
    });

    // ── Character counter for textarea fields ──
    const textareas = document.querySelectorAll('textarea[maxlength]');
    textareas.forEach(ta => {
        const counter = document.createElement('small');
        counter.className = 'text-muted d-block text-end mt-1';
        const max = parseInt(ta.getAttribute('maxlength'));
        const update = () => {
            counter.textContent = `${ta.value.length} / ${max} characters`;
        };
        update();
        ta.addEventListener('input', update);
        ta.parentNode.appendChild(counter);
    });

    // ── Quantity stepper on checkout ──
    document.querySelectorAll('.qty-decrease').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            if (input && parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        });
    });
    document.querySelectorAll('.qty-increase').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const max = parseInt(input.max) || 99;
            if (input && parseInt(input.value) < max) {
                input.value = parseInt(input.value) + 1;
            }
        });
    });

    // ── Smooth scroll for anchor links ──
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
