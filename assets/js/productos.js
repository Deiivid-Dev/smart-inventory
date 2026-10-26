/**
 * Interacciones de la página de Productos: abrir el modal para crear/editar
 * y eliminar productos por AJAX sin recargar la página.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('modalProducto');
        if (!modalEl) {
            return;
        }
        var modal = new bootstrap.Modal(modalEl);
        var form = document.getElementById('formProducto');
        var titulo = document.getElementById('modalProductoTitulo');
        var campoStock = document.getElementById('campoStock');
        var etiquetaStock = document.getElementById('etiquetaStock');
        var ayudaStock = document.getElementById('ayudaStock');

        function limpiarAvisoBorrador() {
            var aviso = modalEl.querySelector('.aviso-borrador');
            if (aviso) {
                aviso.remove();
            }
        }

        function prepararModoCrear() {
            form.reset();
            limpiarAvisoBorrador();
            document.getElementById('campoAccion').value = 'crear';
            document.getElementById('campoId').value = '';
            titulo.textContent = 'Nuevo producto';
            campoStock.readOnly = false;
            etiquetaStock.textContent = 'Stock inicial';
            ayudaStock.classList.add('d-none');
        }

        function prepararModoEditar(fila) {
            limpiarAvisoBorrador();
            document.getElementById('campoAccion').value = 'actualizar';
            document.getElementById('campoId').value = fila.dataset.id;
            document.getElementById('campoCodigo').value = fila.dataset.codigo;
            document.getElementById('campoNombre').value = fila.dataset.nombre;
            document.getElementById('campoDescripcion').value = fila.dataset.descripcion;
            document.getElementById('campoCategoria').value = fila.dataset.categoriaId;
            document.getElementById('campoUnidad').value = fila.dataset.unidad;
            document.getElementById('campoPrecioCompra').value = fila.dataset.precioCompra;
            document.getElementById('campoPrecioVenta').value = fila.dataset.precioVenta;
            document.getElementById('campoStockMinimo').value = fila.dataset.stockMinimo;

            campoStock.value = fila.dataset.stock;
            campoStock.readOnly = true;
            etiquetaStock.textContent = 'Stock actual';
            ayudaStock.classList.remove('d-none');

            titulo.textContent = 'Editar: ' + fila.dataset.nombre;
        }

        var btnNuevo = document.getElementById('btnNuevoProducto');
        if (btnNuevo) {
            btnNuevo.addEventListener('click', prepararModoCrear);
        }

        document.querySelectorAll('.btn-editar-producto').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var fila = btn.closest('tr');
                prepararModoEditar(fila);
                modal.show();
            });
        });

        document.querySelectorAll('.btn-eliminar-producto').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var fila = btn.closest('tr');
                var nombre = fila.dataset.nombre;

                window.confirmarAccion({
                    title: '¿Eliminar este producto?',
                    html: 'Se eliminará <strong>' + nombre + '</strong>. Esta acción no se puede deshacer.',
                    textoPlano: '¿Eliminar el producto "' + nombre + '"? Esta acción no se puede deshacer.',
                    icon: 'warning',
                    confirmButtonText: 'Sí, eliminar',
                    confirmButtonColor: '#dc3545',
                }).then(function (confirmado) {
                    if (!confirmado) {
                        return;
                    }

                    btn.disabled = true;

                    window.peticionJSON('api/eliminar_producto.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            id: fila.dataset.id,
                            csrf_token: window.obtenerCsrfToken(),
                        }),
                    }).then(function (datos) {
                        fila.remove();
                        window.notificar({
                            title: 'Producto eliminado',
                            text: datos.mensaje,
                            textoPlano: datos.mensaje,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                        });
                    }).catch(function (error) {
                        window.notificar({
                            title: 'No se pudo eliminar',
                            text: error.message,
                            textoPlano: 'No se pudo eliminar: ' + error.message,
                            icon: 'error',
                        });
                        btn.disabled = false;
                    });
                });
            });
        });
    });
})();
