/**
 * Comportamiento común a todas las páginas: menú lateral responsive
 * y pequeñas utilidades compartidas por el resto de los scripts.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var btnToggle = document.getElementById('btnToggleSidebar');
        var sidebar = document.getElementById('sidebar');

        if (btnToggle && sidebar) {
            btnToggle.addEventListener('click', function () {
                sidebar.classList.toggle('abierto');
            });
        }

        var enlaceSalir = document.getElementById('enlaceCerrarSesion');
        if (enlaceSalir) {
            enlaceSalir.addEventListener('click', function (evento) {
                evento.preventDefault();
                window.confirmarAccion({
                    title: '¿Cerrar sesión?',
                    icon: 'question',
                    confirmButtonText: 'Sí, cerrar sesión',
                    textoPlano: '¿Cerrar sesión?',
                }).then(function (confirmado) {
                    if (confirmado) {
                        window.location.href = enlaceSalir.href;
                    }
                });
            });
        }
    });

    /**
     * Muestra una ventana de confirmación con SweetAlert2 (con un botón de
     * "Cancelar" y otro de confirmar) y devuelve una promesa que resuelve en
     * true/false según lo que elija la persona. Si SweetAlert2 no cargó (por
     * ejemplo, sin conexión a internet), usa el confirm() nativo del navegador.
     */
    window.confirmarAccion = function (opciones) {
        if (typeof Swal === 'undefined') {
            return Promise.resolve(window.confirm(opciones.textoPlano || opciones.title || '¿Continuar?'));
        }

        var config = Object.assign({
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6c757d',
        }, opciones);
        delete config.textoPlano;

        return Swal.fire(config).then(function (resultado) {
            return resultado.isConfirmed;
        });
    };

    /**
     * Muestra un aviso (éxito, error, advertencia...) con SweetAlert2, o con
     * alert() nativo si SweetAlert2 no cargó.
     */
    window.notificar = function (opciones) {
        if (typeof Swal === 'undefined') {
            var texto = opciones.textoPlano || opciones.title || '';
            if (opciones.text) {
                texto += (texto ? ': ' : '') + opciones.text;
            }
            window.alert(texto);
            return;
        }

        var config = Object.assign({}, opciones);
        delete config.textoPlano;
        Swal.fire(config);
    };

    /** Token CSRF actual, leído del <meta> generado en includes/header.php. */
    window.obtenerCsrfToken = function () {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };

    /** Pequeño helper para peticiones JSON con fetch. */
    window.peticionJSON = function (url, opciones) {
        opciones = opciones || {};
        opciones.headers = Object.assign({ 'Content-Type': 'application/json' }, opciones.headers || {});
        return fetch(url, opciones).then(function (respuesta) {
            return respuesta.json().then(function (datos) {
                if (!respuesta.ok) {
                    throw new Error(datos.mensaje || 'Ocurrió un error inesperado.');
                }
                return datos;
            });
        });
    };
})();
