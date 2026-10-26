/**
 * Lógica compartida por los formularios de Entradas y Salidas:
 * muestra el stock actual del producto elegido y, en salidas,
 * evita enviar una cantidad mayor a la disponible.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('formMovimiento');
        if (!form) {
            return;
        }

        var selectProducto = document.getElementById('selectProducto');
        var campoCantidad = document.getElementById('campoCantidad');
        var infoStock = document.getElementById('infoStockActual');
        var validarStock = form.dataset.validarStock === '1';

        function stockDisponible() {
            var opcion = selectProducto.options[selectProducto.selectedIndex];
            if (!opcion || !opcion.value) {
                return null;
            }
            return {
                stock: parseInt(opcion.dataset.stock || '0', 10),
                unidad: opcion.dataset.unidad || 'unidad',
            };
        }

        function actualizarInfoStock() {
            var info = stockDisponible();
            if (!info) {
                infoStock.textContent = '';
                campoCantidad.removeAttribute('max');
                return;
            }

            infoStock.textContent = 'Stock actual: ' + info.stock + ' ' + info.unidad;

            if (validarStock) {
                campoCantidad.setAttribute('max', info.stock);
            }
        }

        selectProducto.addEventListener('change', actualizarInfoStock);
        form.addEventListener('borrador-restaurado', actualizarInfoStock);
        actualizarInfoStock();

        form.addEventListener('submit', function (evento) {
            if (!validarStock) {
                return;
            }
            var info = stockDisponible();
            var cantidad = parseInt(campoCantidad.value || '0', 10);

            if (info && cantidad > info.stock) {
                evento.preventDefault();
                var mensaje = 'No puedes retirar ' + cantidad + ' unidades: solo hay ' + info.stock + ' disponibles.';
                window.notificar({
                    title: 'Stock insuficiente',
                    text: mensaje,
                    textoPlano: mensaje,
                    icon: 'warning',
                });
            }
        });
    });
})();
