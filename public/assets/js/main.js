// ─── TOAST NOTIFICATIONS ───
(function() {
    function showToast(message, type) {
        type = type || 'info';
        var icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
        var container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        var toast = document.createElement('div');
        toast.className = 'custom-toast toast-' + type;
        toast.innerHTML = '<i class="bi ' + (icons[type] || icons.info) + '"></i> <span>' + message + '</span>';
        container.appendChild(toast);
        setTimeout(function() {
            toast.style.animation = 'fadeOut .3s ease forwards';
            setTimeout(function() { toast.remove(); }, 300);
        }, 4000);
        toast.addEventListener('click', function() {
            toast.style.animation = 'fadeOut .2s ease forwards';
            setTimeout(function() { toast.remove(); }, 200);
        });
    }

    // Convert flash messages from session to toasts
    var flashMsg = document.getElementById('flashData');
    if (flashMsg) {
        try {
            var data = JSON.parse(flashMsg.textContent);
            if (data.message) {
                showToast(data.message, data.type === 'error' ? 'error' : data.type);
            }
        } catch(e) {}
    }

    window.showToast = showToast;
})();

// ─── DATATABLES ───
document.addEventListener('DOMContentLoaded', function() {
    // Auto-init DataTables on tables with class 'datatable'
    if (typeof $.fn !== 'undefined' && $.fn.DataTable) {
        $('.datatable').each(function() {
            var dt = $(this);
            if (!dt.parent().hasClass('dataTables_wrapper')) {
                dt.DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    },
                    pageLength: 15,
                    lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, 'Todos']],
                    order: [],
                    columnDefs: [
                        { targets: 'no-sort', orderable: false }
                    ]
                });
            }
        });
    }

    // Auto-cerrar alertas bootstrap
    var alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(a) {
        setTimeout(function() {
            var bs = bootstrap.Alert.getOrCreateInstance(a);
            bs.close();
        }, 5000);
    });

    // Tooltips
    var tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(function(el) { return new bootstrap.Tooltip(el); });
});

// ─── UTILITY FUNCTIONS ───
function formatCurrency(amount, currency) {
    currency = currency || 'MXN';
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2
    }).format(amount);
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('es-MX', {
        year: 'numeric', month: '2-digit', day: '2-digit'
    });
}

function confirmDelete(msg) {
    return confirm(msg || '¿Estás seguro de eliminar este registro?');
}
