/**
 * admin-global.js — Scripts compartidos para todo el panel admin
 * Se carga en TODAS las páginas admin via config/adminlte.php plugins
 */

document.addEventListener('DOMContentLoaded', function () {

    // =========================================================
    // SCROLL AUTOMÁTICO AL PRIMER CAMPO CON ERROR DE VALIDACIÓN
    // Se activa cuando Laravel vuelve con $errors después de un POST
    // Soporta formularios multi-paso: solo hace scroll si el campo
    // está visible en pantalla (no en un step oculto)
    // =========================================================
    function isVisible(el) {
        return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
    }

    var allInvalid = document.querySelectorAll('.is-invalid');
    var firstInvalid = Array.from(allInvalid).find(isVisible);

    if (firstInvalid) {
        // Pequeño delay para que select2, acordeones o animaciones ya estén listos
        setTimeout(function () {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Intentar hacer focus si es un campo nativo (input, select, textarea)
            var tag = firstInvalid.tagName.toLowerCase();
            if (tag === 'input' || tag === 'select' || tag === 'textarea') {
                firstInvalid.focus({ preventScroll: true });
            }

            // Marcar el campo con un ring visual extra para que el ojo lo encuentre rápido
            firstInvalid.style.transition = 'box-shadow 0.3s';
            firstInvalid.style.boxShadow = '0 0 0 4px rgba(233, 69, 96, 0.3)';
            setTimeout(function () {
                firstInvalid.style.boxShadow = '';
            }, 2500);
        }, 150);
    }

    // =========================================================
    // BANNER DE ERRORES — añade contador de errores al texto
    // =========================================================
    var errorBanners = document.querySelectorAll(
        '.alert-custom.danger, .alert-danger, .alert.alert-danger'
    );
    errorBanners.forEach(function (banner) {
        var invalids = document.querySelectorAll('.is-invalid').length;
        if (invalids > 0 && !banner.dataset.countAdded) {
            var badge = document.createElement('span');
            badge.style.cssText =
                'display:inline-block;background:#e94560;color:#fff;border-radius:12px;' +
                'padding:1px 8px;font-size:0.78em;font-weight:700;margin-left:6px;vertical-align:middle;';
            badge.textContent = invalids + (invalids === 1 ? ' campo' : ' campos');
            banner.querySelector('strong, div') && banner.querySelector('strong, div').appendChild(badge);
            banner.dataset.countAdded = '1';

            // Botón "Ir al error" si la página es larga
            if (document.body.scrollHeight > window.innerHeight * 1.5) {
                var btnIr = document.createElement('button');
                btnIr.type = 'button';
                btnIr.style.cssText =
                    'margin-left:10px;background:transparent;border:2px solid #e94560;color:#e94560;' +
                    'border-radius:8px;padding:2px 10px;font-size:0.8em;font-weight:600;cursor:pointer;vertical-align:middle;';
                btnIr.textContent = 'Ir al error ↓';
                btnIr.addEventListener('click', function () {
                    var el = document.querySelector('.is-invalid');
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        var t = el.tagName.toLowerCase();
                        if (t === 'input' || t === 'select' || t === 'textarea') el.focus({ preventScroll: true });
                    }
                });
                banner.appendChild(btnIr);
            }
        }
    });

    // =========================================================
    // PREVENCIÓN DE DOBLE SUBMIT — spinner en botón de guardar
    // Aplica a cualquier form con id que empiece en "form"
    // No aplica si la validación JS del propio formulario falló
    // =========================================================
    document.querySelectorAll('form[id^="form"]').forEach(function (form) {
        form.addEventListener('submit', function () {
            // Si el formulario tiene campos inválidos nativos, no bloquear
            if (form.querySelectorAll(':invalid').length > 0) return;

            var btn = form.querySelector('[type="submit"]:not(.no-loader)');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                var icon = btn.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-spinner fa-spin';
                }
            }
        });
    });
});
