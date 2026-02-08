$(document).ready(function() {
    var $form = $('#rsvp_form');
    var $submit = $('#submit_rsvp');
    var $results = $('#contact_results');

    if (!$form.length) {
        return;
    }

    function setLoading(isLoading) {
        $submit.prop('disabled', isLoading);
    }

    $form.on('submit', function(e) {
        e.preventDefault();
        $results.hide().html('');
        setLoading(true);

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        })
            .done(function(resp) {
                var ok = resp && resp.success;
                var message = (resp && resp.message)
                    ? resp.message
                    : (ok ? 'Confirmación registrada. ¡Gracias!' : 'No fue posible registrar tu confirmación.');

                var html = ok
                    ? '<div class="alert alert-success">' + message + '</div>'
                    : '<div class="alert alert-danger">' + message + '</div>';

                $results.hide().html(html).slideDown();

                if (ok) {
                    $form.trigger('reset');
                }
            })
            .fail(function() {
                $results.hide().html('<div class="alert alert-danger">No fue posible registrar tu confirmación.</div>').slideDown();
            })
            .always(function() {
                setLoading(false);
            });
    });
});
