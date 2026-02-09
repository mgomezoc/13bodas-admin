/**
 * 13Bodas - Sistema de CRUD Homologado para Eventos
 * Maneja confirmaciones, AJAX y actualización de UI.
 */
class EventsCRUD {
    constructor() {
        this.init();
    }

    init() {
        this.bindDeleteButtons();
        this.bindModalForms();
        this.bindModalCleanup();
    }

    bindDeleteButtons() {
        document.addEventListener('click', (event) => {
            const deleteBtn = event.target.closest('.delete-item');
            if (!deleteBtn) {
                return;
            }

            event.preventDefault();

            const itemId = deleteBtn.dataset.id;
            const itemName = deleteBtn.dataset.name || 'este registro';
            const endpoint = deleteBtn.dataset.endpoint;
            const tableId = deleteBtn.dataset.tableId;
            const refreshTarget = deleteBtn.dataset.refreshTarget;

            if (!endpoint) {
                console.warn('Delete endpoint missing.');
                return;
            }

            Swal.fire({
                title: '¿Estás seguro?',
                html: `Vas a eliminar <strong>${itemName}</strong>.<br>Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash"></i> Sí, eliminar',
                cancelButtonText: 'Cancelar',
                background: '#1a1520',
                color: '#e0e0e0',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    this.executeDelete({
                        id: itemId,
                        endpoint,
                        tableId,
                        refreshTarget,
                        itemName
                    });
                }
            });
        });
    }

    executeDelete({ id, endpoint, tableId, refreshTarget, itemName }) {
        const csrfName = window.CSRF_NAME || null;
        const csrfToken = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.content;
        const formData = new FormData();

        if (csrfName && csrfToken) {
            formData.append(csrfName, csrfToken);
        }

        if (id) {
            formData.append('id', id);
        }

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: `${itemName} ha sido eliminado correctamente.`,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        background: '#1a1520',
                        color: '#e0e0e0'
                    });

                    this.refreshView({ tableId, refreshTarget });
                    return;
                }

                throw new Error(data.message || 'Error al eliminar');
            })
            .catch((error) => {
                Swal.fire({
                    title: 'Error',
                    text: error.message || 'No se pudo completar la eliminación',
                    icon: 'error',
                    background: '#1a1520',
                    color: '#e0e0e0'
                });
            });
    }

    refreshView({ tableId, refreshTarget }) {
        if (refreshTarget && typeof refreshModuleSection === 'function') {
            refreshModuleSection(refreshTarget);
            return;
        }

        if (!tableId) {
            location.reload();
            return;
        }

        const table = document.getElementById(tableId);
        if (!table) {
            location.reload();
            return;
        }

        if (window.$ && $.fn.bootstrapTable) {
            $(`#${tableId}`).bootstrapTable('refresh');
            return;
        }

        if (window.$ && $.fn.DataTable && $.fn.DataTable.isDataTable(`#${tableId}`)) {
            $(`#${tableId}`).DataTable().ajax.reload(null, false);
            return;
        }

        location.reload();
    }

    bindModalForms() {
        document.addEventListener('submit', (event) => {
            const form = event.target.closest('.modal-ajax-form');
            if (!form) {
                return;
            }

            event.preventDefault();

            const modal = form.closest('.modal');
            const modalId = modal?.id;
            const tableId = form.dataset.tableId;
            const refreshTarget = form.dataset.refreshTarget;
            const formData = new FormData(form);
            const csrfName = window.CSRF_NAME || null;
            const csrfToken = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.content;

            if (csrfName && csrfToken && !formData.has(csrfName)) {
                formData.append(csrfName, csrfToken);
            }

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        this.closeModal(modalId);

                        Swal.fire({
                            title: '¡Éxito!',
                            text: data.message || 'Operación completada',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                            background: '#1a1520',
                            color: '#e0e0e0'
                        });

                        this.refreshView({ tableId, refreshTarget });
                        return;
                    }

                    this.showFormErrors(form, data.errors || {});
                })
                .catch(() => {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo procesar la solicitud',
                        icon: 'error',
                        background: '#1a1520',
                        color: '#e0e0e0'
                    });
                });
        });
    }

    closeModal(modalId) {
        if (!modalId) {
            return;
        }

        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            return;
        }

        const modal = bootstrap.Modal.getInstance(modalElement) ?? new bootstrap.Modal(modalElement);
        modal.hide();

        setTimeout(() => {
            document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
                backdrop.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }, 300);
    }

    bindModalCleanup() {
        document.addEventListener('hidden.bs.modal', (event) => {
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                if (backdrops.length > 0) {
                    backdrops.forEach((backdrop) => backdrop.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('overflow');
                }
            }, 350);

            const form = event.target.querySelector('form');
            if (form) {
                form.reset();
                this.clearFormErrors(form);
            }
        });
    }

    showFormErrors(form, errors) {
        this.clearFormErrors(form);

        Object.keys(errors).forEach((field) => {
            const input = form.querySelector(`[name="${field}"]`);
            if (!input) {
                return;
            }

            input.classList.add('is-invalid');

            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = errors[field];
            input.parentNode.appendChild(errorDiv);
        });
    }

    clearFormErrors(form) {
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach((el) => el.remove());
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.eventsCRUD = new EventsCRUD();
    const tabsWrapper = document.querySelector('.event-tabs-wrapper');
    if (tabsWrapper) {
        tabsWrapper.scrollLeft = 0;
    }
});
