(() => {
    const config = window.checkoutConfig || {};
    const button = document.getElementById('checkout-button');
    const feedback = document.getElementById('checkout-feedback');

    if (!button || !config.createSessionUrl) {
        return;
    }

    const showFeedback = (message = '', isError = false) => {
        if (!feedback) {
            return;
        }

        feedback.textContent = message;
        feedback.classList.toggle('is-error', isError);
    };

    button.addEventListener('click', async () => {
        button.disabled = true;

        const label = button.querySelector('.checkout-btn-label');
        const originalText = label ? label.textContent : button.textContent;

        if (label) {
            label.textContent = 'Conectando con Stripe...';
        } else {
            button.textContent = 'Conectando con Stripe...';
        }

        showFeedback('Estamos preparando tu pago seguro...');

        try {
            const body = new URLSearchParams();

            if (config.csrfTokenName && config.csrfHash) {
                body.append(config.csrfTokenName, config.csrfHash);
            }

            const response = await fetch(config.createSessionUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: body.toString(),
            });

            const payload = await response.json();

            if (!response.ok || !payload.success || !payload.checkout_url) {
                const debugSuffix = payload.error_id ? ` (Ref: ${payload.error_id})` : '';
                const detailSuffix = payload.debug_detail ? ` · ${payload.debug_detail}` : '';
                throw new Error((payload.message || 'No fue posible crear la sesión de pago.') + debugSuffix + detailSuffix);
            }

            showFeedback('Redirigiendo a Stripe...');
            window.location.assign(payload.checkout_url);
        } catch (error) {
            const errorMessage = error instanceof Error ? error.message : 'Error inesperado al conectar con Stripe.';

            showFeedback(errorMessage, true);
            button.disabled = false;

            if (label) {
                label.textContent = originalText;
            } else {
                button.textContent = originalText;
            }
        }
    });
})();
