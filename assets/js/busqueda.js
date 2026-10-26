/**
 * Búsqueda de productos en vivo (sin recargar la página), con respaldo
 * normal por GET si JavaScript no está disponible.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('formBusqueda');
        var campo = document.getElementById('campoBusqueda');
        var contenedor = document.getElementById('resultadosBusqueda');
        if (!form || !campo || !contenedor) {
            return;
        }

        var temporizador = null;

        function buscar() {
            var q = campo.value.trim();

            if (window.history && window.history.replaceState) {
                var url = new URL(window.location.href);
                if (q) {
                    url.searchParams.set('q', q);
                } else {
                    url.searchParams.delete('q');
                }
                window.history.replaceState({}, '', url);
            }

            fetch('api/buscar_productos.php?q=' + encodeURIComponent(q))
                .then(function (respuesta) { return respuesta.text(); })
                .then(function (html) {
                    contenedor.innerHTML = html;
                })
                .catch(function () {
                    // Si falla la petición en vivo, se deja que el usuario use Enter
                    // para buscar con el envío normal del formulario.
                });
        }

        campo.addEventListener('input', function () {
            clearTimeout(temporizador);
            temporizador = setTimeout(buscar, 300);
        });

        form.addEventListener('submit', function (evento) {
            evento.preventDefault();
            clearTimeout(temporizador);
            buscar();
        });
    });
})();
