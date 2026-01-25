/**
 * Admin Panel JavaScript - 13Bodas
 * Dependencies: jQuery, Bootstrap 5, Select2, Flatpickr, Bootstrap Table, SweetAlert2
 */

$(document).ready(function() {
    // Initialize components
    initSidebar();
    initSelect2();
    initFlatpickr();
    initFormValidation();
    initDeleteConfirmation();
    initAjaxSetup();
});

/**
 * Sidebar Toggle
 */
function initSidebar() {
    const sidebar = $('#sidebar');
    const overlay = $('#sidebarOverlay');
    const toggleBtn = $('#toggleSidebar');
    const closeBtn = $('#closeSidebar');

    toggleBtn.on('click', function() {
        sidebar.addClass('active');
        overlay.addClass('active');
    });

    closeBtn.on('click', closeSidebar);
    overlay.on('click', closeSidebar);

    function closeSidebar() {
        sidebar.removeClass('active');
        overlay.removeClass('active');
    }

    // Close sidebar on window resize (desktop)
    $(window).on('resize', function() {
        if ($(window).width() >= 992) {
            closeSidebar();
        }
    });
}

/**
 * Initialize Select2
 */
function initSelect2() {
    $('.select2').each(function() {
        $(this).select2({
            theme: 'bootstrap-5',
            language: 'es',
            placeholder: $(this).data('placeholder') || 'Seleccionar...',
            allowClear: $(this).data('allow-clear') !== false,
            dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : $('body')
        });
    });
}

/**
 * Initialize Flatpickr
 */
function initFlatpickr() {
    // Date picker
    $('.datepicker').flatpickr({
        locale: 'es',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        allowInput: true
    });

    // DateTime picker
    $('.datetimepicker').flatpickr({
        locale: 'es',
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'd/m/Y H:i',
        time_24hr: true,
        allowInput: true
    });

    // Time picker
    $('.timepicker').flatpickr({
        locale: 'es',
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        time_24hr: true,
        allowInput: true
    });
}

/**
 * Form Validation (jQuery Validate)
 */
function initFormValidation() {
    // Global defaults
    $.validator.setDefaults({
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        highlight: function(element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
        errorPlacement: function(error, element) {
            if (element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            } else if (element.hasClass('select2-hidden-accessible')) {
                error.insertAfter(element.next('.select2-container'));
            } else {
                error.insertAfter(element);
            }
        }
    });

    // Validate all forms with .needs-validation class
    $('.needs-validation').each(function() {
        $(this).validate({
            submitHandler: function(form) {
                form.submit();
            }
        });
    });
}

/**
 * Delete Confirmation
 */
function initDeleteConfirmation() {
    $(document).on('click', '.btn-delete, [data-confirm-delete]', function(e) {
        e.preventDefault();
        const url = $(this).attr('href') || $(this).data('url');
        const title = $(this).data('title') || '¿Estás seguro?';
        const text = $(this).data('text') || 'Esta acción no se puede deshacer.';

        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                if (url) {
                    // Submit via AJAX or redirect
                    $.post(url, { csrf_token: CSRF_TOKEN })
                        .done(function(response) {
                            if (response.success) {
                                Toast.fire({ icon: 'success', title: response.message || 'Eliminado correctamente' });
                                // Reload table or page
                                if ($('#dataTable').length) {
                                    $('#dataTable').bootstrapTable('refresh');
                                } else {
                                    setTimeout(() => location.reload(), 1000);
                                }
                            } else {
                                Toast.fire({ icon: 'error', title: response.message || 'Error al eliminar' });
                            }
                        })
                        .fail(function() {
                            Toast.fire({ icon: 'error', title: 'Error de conexión' });
                        });
                }
            }
        });
    });
}

/**
 * AJAX Setup
 */
function initAjaxSetup() {
    $.ajaxSetup({
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        beforeSend: function(xhr, settings) {
            if (settings.type === 'POST' || settings.type === 'PUT' || settings.type === 'DELETE') {
                if (!settings.data) {
                    settings.data = {};
                }
                if (typeof settings.data === 'string') {
                    settings.data += '&csrf_token=' + CSRF_TOKEN;
                } else {
                    settings.data.csrf_token = CSRF_TOKEN;
                }
            }
        }
    });
}

/**
 * Bootstrap Table - Response Handler
 */
function responseHandler(res) {
    if (res.rows) {
        return {
            total: res.total,
            rows: res.rows
        };
    }
    return {
        total: res.length,
        rows: res
    };
}

/**
 * Bootstrap Table - Status Formatter
 */
function statusFormatter(value, row) {
    const statusClasses = {
        'active': 'status-active',
        'inactive': 'status-inactive',
        'pending': 'status-pending',
        'draft': 'status-draft',
        '1': 'status-active',
        '0': 'status-inactive'
    };
    
    const statusLabels = {
        'active': 'Activo',
        'inactive': 'Inactivo',
        'pending': 'Pendiente',
        'draft': 'Borrador',
        '1': 'Activo',
        '0': 'Inactivo'
    };

    const cls = statusClasses[value] || 'status-draft';
    const label = statusLabels[value] || value;
    
    return `<span class="status-badge ${cls}">${label}</span>`;
}

/**
 * Bootstrap Table - Date Formatter
 */
function dateFormatter(value, row) {
    if (!value) return '-';
    const date = new Date(value);
    return date.toLocaleDateString('es-MX', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

/**
 * Bootstrap Table - DateTime Formatter
 */
function dateTimeFormatter(value, row) {
    if (!value) return '-';
    const date = new Date(value);
    return date.toLocaleDateString('es-MX', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Show Loading
 */
function showLoading(container) {
    const html = `
        <div class="loading-overlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    $(container).css('position', 'relative').append(html);
}

/**
 * Hide Loading
 */
function hideLoading(container) {
    $(container).find('.loading-overlay').remove();
}

/**
 * Format Currency
 */
function formatCurrency(amount, currency = 'MXN') {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

/**
 * Slug Generator
 */
function generateSlug(text) {
    return text
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w\-]+/g, '')
        .replace(/\-\-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
}

/**
 * Copy to Clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        Toast.fire({ icon: 'success', title: 'Copiado al portapapeles' });
    }).catch(function() {
        Toast.fire({ icon: 'error', title: 'Error al copiar' });
    });
}
