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
        var icon = document.createElement('i');
        icon.className = 'bi ' + (icons[type] || icons.info);
        var text = document.createElement('span');
        text.textContent = message;
        toast.appendChild(icon);
        toast.appendChild(document.createTextNode(' '));
        toast.appendChild(text);
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
                    ],
                    drawCallback: function() {
                        initTooltips();
                    }
                });
            }
        });
    }

    var alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(a) {
        setTimeout(function() {
            var bs = bootstrap.Alert.getOrCreateInstance(a);
            bs.close();
        }, 5000);
    });

    initTooltips();
    initSidebarToggle();
    initNavbarCollapse();
    initLoadingOverlay();
});

// ─── TOOLTIPS ───
function initTooltips() {
    var tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(function(el) { return new bootstrap.Tooltip(el); });
}

// ─── SIDEBAR TOGGLE ───
function initSidebarToggle() {
    var toggleBtn = document.getElementById('sidebarToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('show');
            }
        });
    }
}

// ─── NAVBAR COLLAPSE ───
function initNavbarCollapse() {
    document.querySelectorAll('.navbar .nav-link, .navbar .dropdown-item').forEach(function(el) {
        el.addEventListener('click', function() {
            var navbar = document.querySelector('.navbar-collapse.show');
            if (navbar) {
                var bs = bootstrap.Collapse.getInstance(navbar);
                if (bs) bs.hide();
            }
        });
    });
}

// ─── LOADING OVERLAY ───
function initLoadingOverlay() {
    var overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(overlay);
    window.showLoading = function() { overlay.classList.add('active'); };
    window.hideLoading = function() { overlay.classList.remove('active'); };
}

// ─── AJAX FORM SUBMISSION ───
document.addEventListener('submit', function(e) {
    var form = e.target;
    if (form.classList.contains('ajax-form')) {
        e.preventDefault();
        var btn = form.querySelector('[type="submit"]');
        if (btn) {
            if (!btn.getAttribute('data-original-text')) {
                btn.setAttribute('data-original-text', btn.innerHTML);
            }
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando...';
        }
        var formData = new FormData(form);
        fetch(form.action, {
            method: form.method || 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) {
            var contentType = res.headers.get('content-type') || '';
            if (contentType.indexOf('application/json') === -1) {
                throw new Error('Respuesta no JSON');
            }
            return res.json();
        })
        .then(function(data) {
            if (data.success) {
                showToast(data.message || 'Operación exitosa', 'success');
                if (data.redirect) { setTimeout(function() { window.location.href = data.redirect; }, 500); }
            } else {
                showToast(data.message || 'Error en la operación', 'error');
            }
        })
        .catch(function() {
            showToast('Error de conexión', 'error');
        })
        .finally(function() {
            if (btn) { btn.disabled = false; btn.innerHTML = btn.getAttribute('data-original-text') || 'Enviar'; }
        });
    }
});

// ─── CONFIRM DIALOG ───
function confirmDelete(msg) {
    return confirm(msg || '¿Estás seguro de eliminar este registro? Esta acción no se puede deshacer.');
}

// ─── FORMAT CURRENCY ───
function formatCurrency(amount, currency) {
    currency = currency || 'MXN';
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2
    }).format(amount);
}

// ─── FORMAT DATE ───
function formatDate(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('es-MX', {
        year: 'numeric', month: '2-digit', day: '2-digit'
    });
}

// ─── EXPORT TABLE TO CSV ───
function exportTableToCSV(tableId, filename) {
    var table = document.getElementById(tableId);
    if (!table) return;
    var rows = table.querySelectorAll('tr');
    var csv = [];
    rows.forEach(function(row) {
        var cols = row.querySelectorAll('th, td');
        var rowData = [];
        cols.forEach(function(col) {
            var text = col.innerText.replace(/"/g, '""');
            rowData.push('"' + text + '"');
        });
        csv.push(rowData.join(','));
    });
    var blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = (filename || 'export') + '.csv';
    link.click();
    URL.revokeObjectURL(link.href);
}

// ─── PRINT TABLE ───
function printTable(tableId, title) {
    var table = document.getElementById(tableId);
    if (!table) return;
    var win = window.open('', '_blank');
    win.document.write('<html><head><title>' + (title || 'Reporte') + '</title>');
    win.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
    win.document.write('<style>body { padding: 20px; } @media print { @page { size: landscape; } }</style>');
    win.document.write('</head><body>');
    win.document.write('<h3 class="mb-3">' + (title || 'Reporte') + '</h3>');
    win.document.write('<table class="table table-bordered">' + table.innerHTML + '</table>');
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    setTimeout(function() { win.print(); }, 300);
}
