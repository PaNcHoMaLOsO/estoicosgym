/**
 * JavaScript vanilla para el cliente
 * Sin dependencias de Node.js - solo JavaScript puro
 */

// Validaciones de formulario simples
document.addEventListener('DOMContentLoaded', function() {
    // Validar formularios antes de enviar
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // CSRF token para peticiones AJAX
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        window.csrfToken = token.getAttribute('content');
    }
});

// Función auxiliar para peticiones AJAX simple
function fetchRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': window.csrfToken || ''
        }
    };

    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
        options.headers['Content-Type'] = 'application/json';
    }

    return fetch(url, options)
        .then(response => response.json())
        .catch(error => console.error('Error:', error));
}

// Confirmación antes de eliminar
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('btn-delete')) {
        e.preventDefault();
        if (!confirm('¿Estás seguro de que deseas eliminar este elemento?')) {
            return false;
        }
        e.target.closest('form').submit();
    }
});
