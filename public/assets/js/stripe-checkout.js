(() => {
    const config = window.checkoutConfig || {};
    const button = document.getElementById('checkout-button');

    if (!button || !config.createSessionUrl) {
        return;
    }

    button.addEventListener('click', async () => {
        button.disabled = true;
        const originalText = button.textContent;
        button.textContent = 'Procesando...';

        try {
            const response = await fetch(config.createSessionUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const payload = await response.json();

            if (!response.ok || !payload.success || !payload.checkout_url) {
                throw new Error(payload.message || 'No fue posible crear la sesión.');
            }

            window.location.href = payload.checkout_url;
        } catch (error) {
            window.alert(error.message || 'Error inesperado');
            button.disabled = false;
            button.textContent = originalText;
        }
    });
})();
