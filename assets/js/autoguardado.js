/**
 * Guardado automático en el navegador (localStorage).
 * Cualquier <form data-autoguardado="clave"> guarda su contenido mientras
 * el usuario escribe, y lo restaura si vuelve a abrir la página sin haber
 * enviado el formulario (por ejemplo, tras cerrar la pestaña por accidente).
 */
(function () {
    'use strict';

    var CAMPOS_IGNORADOS = ['csrf_token'];

    function clavePara(form) {
        return 'autoguardado_' + form.getAttribute('data-autoguardado');
    }

    function recolectarValores(form) {
        var datos = {};
        Array.prototype.forEach.call(form.elements, function (campo) {
            if (!campo.name || CAMPOS_IGNORADOS.indexOf(campo.name) !== -1) {
                return;
            }
            if (campo.type === 'file' || campo.type === 'password') {
                return;
            }
            datos[campo.name] = campo.type === 'checkbox' || campo.type === 'radio'
                ? campo.checked
                : campo.value;
        });
        return datos;
    }

    function hayContenido(datos) {
        return Object.keys(datos).some(function (clave) {
            var valor = datos[clave];
            return valor !== '' && valor !== false && valor !== null && typeof valor !== 'undefined';
        });
    }

    function guardarBorrador(form) {
        try {
            var datos = recolectarValores(form);
            if (hayContenido(datos)) {
                localStorage.setItem(clavePara(form), JSON.stringify(datos));
            } else {
                localStorage.removeItem(clavePara(form));
            }
        } catch (error) {
            // localStorage puede no estar disponible (modo privado, cuota llena, etc.)
        }
    }

    function limpiarBorrador(form) {
        try {
            localStorage.removeItem(clavePara(form));
        } catch (error) {
            // Nada que hacer si localStorage no está disponible.
        }
    }

    function restaurarBorrador(form) {
        var crudo;
        try {
            crudo = localStorage.getItem(clavePara(form));
        } catch (error) {
            return;
        }
        if (!crudo) {
            return;
        }

        var datos;
        try {
            datos = JSON.parse(crudo);
        } catch (error) {
            limpiarBorrador(form);
            return;
        }

        var seRestauroAlgo = false;
        Object.keys(datos).forEach(function (nombre) {
            var campo = form.elements.namedItem(nombre);
            if (!campo || datos[nombre] === '' || datos[nombre] === false) {
                return;
            }
            if (campo.type === 'checkbox' || campo.type === 'radio') {
                campo.checked = !!datos[nombre];
            } else {
                campo.value = datos[nombre];
            }
            seRestauroAlgo = true;
        });

        if (seRestauroAlgo) {
            mostrarAvisoBorrador(form);
            // Avisamos a otros scripts (como movimientos.js) que los valores cambiaron.
            form.dispatchEvent(new Event('borrador-restaurado'));
        }
    }

    function mostrarAvisoBorrador(form) {
        var aviso = document.createElement('div');
        aviso.className = 'alert alert-info alert-dismissible fade show aviso-borrador';
        aviso.setAttribute('role', 'alert');
        aviso.innerHTML =
            '<i class="bi bi-floppy2-fill"></i> ' +
            'Se restauró automáticamente lo que estabas escribiendo antes de salir de esta página. ' +
            '<button type="button" class="btn btn-sm btn-outline-secondary ms-2" data-accion="descartar-borrador">Descartar</button>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>';
        form.insertAdjacentElement('beforebegin', aviso);

        aviso.querySelector('[data-accion="descartar-borrador"]').addEventListener('click', function () {
            limpiarBorrador(form);
            form.reset();
            aviso.remove();
        });
    }

    function debounce(fn, esperaMs) {
        var temporizador = null;
        return function () {
            var args = arguments;
            clearTimeout(temporizador);
            temporizador = setTimeout(function () {
                fn.apply(null, args);
            }, esperaMs);
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        var formularios = document.querySelectorAll('form[data-autoguardado]');
        Array.prototype.forEach.call(formularios, function (form) {
            restaurarBorrador(form);

            var guardarConRetraso = debounce(function () {
                guardarBorrador(form);
            }, 400);

            form.addEventListener('input', guardarConRetraso);
            form.addEventListener('change', guardarConRetraso);
            form.addEventListener('submit', function () {
                limpiarBorrador(form);
            });
        });
    });
})();
