/**
 * Renderiza las gráficas de la página de Estadísticas usando Chart.js
 * con los datos agregados que entrega api/datos_estadisticas.php.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var elMovimientos = document.getElementById('graficoMovimientos');
        if (!elMovimientos || typeof Chart === 'undefined') {
            return;
        }

        fetch('api/datos_estadisticas.php')
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (datos) {
                if (!datos.ok) {
                    return;
                }

                new Chart(elMovimientos, {
                    type: 'line',
                    data: {
                        labels: datos.movimientos_por_dia.etiquetas,
                        datasets: [
                            {
                                label: 'Entradas',
                                data: datos.movimientos_por_dia.entradas,
                                borderColor: '#2e7d32',
                                backgroundColor: 'rgba(46, 125, 50, 0.15)',
                                tension: 0.3,
                                fill: true,
                            },
                            {
                                label: 'Salidas',
                                data: datos.movimientos_por_dia.salidas,
                                borderColor: '#ed6c02',
                                backgroundColor: 'rgba(237, 108, 2, 0.15)',
                                tension: 0.3,
                                fill: true,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true } },
                    },
                });

                var elCategorias = document.getElementById('graficoCategorias');
                if (elCategorias && datos.por_categoria.etiquetas.length) {
                    new Chart(elCategorias, {
                        type: 'doughnut',
                        data: {
                            labels: datos.por_categoria.etiquetas,
                            datasets: [{
                                data: datos.por_categoria.valores,
                                backgroundColor: [
                                    '#1976d2', '#2e7d32', '#ed6c02', '#8e24aa',
                                    '#c62828', '#00838f', '#6d4c41', '#546e7a',
                                    '#9e9d24', '#5e35b1',
                                ],
                            }],
                        },
                        options: { responsive: true, maintainAspectRatio: false },
                    });
                }

                var elTop = document.getElementById('graficoTopProductos');
                if (elTop) {
                    var hayTop = datos.top_productos.etiquetas.length > 0;
                    new Chart(elTop, {
                        type: 'bar',
                        data: {
                            labels: hayTop ? datos.top_productos.etiquetas : ['Sin datos todavía'],
                            datasets: [{
                                label: 'Unidades retiradas',
                                data: hayTop ? datos.top_productos.valores : [0],
                                backgroundColor: '#1976d2',
                            }],
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { x: { beginAtZero: true } },
                        },
                    });
                }
            })
            .catch(function () {
                elMovimientos.replaceWith(document.createTextNode('No fue posible cargar las estadísticas.'));
            });
    });
})();
