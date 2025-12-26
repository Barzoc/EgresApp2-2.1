$(document).ready(function () {
    const defaultFontSize = 14;

    function calculatePercentage(value, total) {
        return ((value / total) * 100).toFixed(1);
    }

    // Obtener datos de género
    $.ajax({
        url: '../controlador/DashboardController.php',
        type: 'POST',
        data: { funcion: 'obtenerDatosGenero' },
        success: function (response) {
            const datos = JSON.parse(response);
            const processedLabels = [];
            const processedData = [];
            const processedColors = [];

            if (datos['Masculino'] !== undefined) {
                processedLabels.push('Masculino');
                processedData.push(datos['Masculino']);
                processedColors.push('#36A2EB');
            }
            if (datos['Femenino'] !== undefined) {
                processedLabels.push('Femenino');
                processedData.push(datos['Femenino']);
                processedColors.push('#FF6384');
            }

            Object.keys(datos).forEach(key => {
                if (key !== 'Masculino' && key !== 'Femenino') {
                    processedLabels.push(key);
                    processedData.push(datos[key]);
                    processedColors.push('#FFCE56');
                }
            });

            const total = processedData.reduce((a, b) => parseInt(a) + parseInt(b), 0);

            let summaryHtml = `<strong>Total Egresados: ${total}</strong><br>`;
            processedLabels.forEach((label, index) => {
                const count = processedData[index];
                const percentage = calculatePercentage(count, total);
                summaryHtml += `${label}: ${count} (${percentage}%)<br>`;
            });
            $('#genderSummary').html(summaryHtml);

            var ctxGender = document.getElementById('genderChart').getContext('2d');
            new Chart(ctxGender, {
                type: 'pie',
                data: {
                    labels: processedLabels,
                    datasets: [{
                        data: processedData,
                        backgroundColor: processedColors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    rotation: Math.PI,
                    plugins: {
                        legend: { display: true },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    let value = context.raw;
                                    let percentage = calculatePercentage(value, total);
                                    return label + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        },
        error: function (xhr, status, error) { console.log(error); }
    });

    // Obtener datos de títulos
    $.ajax({
        url: '../controlador/DashboardController.php',
        type: 'POST',
        data: { funcion: 'obtenerDatosTitulo' },
        success: function (response) {
            const datos = JSON.parse(response);
            const labels = Object.keys(datos);
            const data = Object.values(datos);

            var ctxTitle = document.getElementById('titleChart').getContext('2d');
            new Chart(ctxTitle, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Cantidad de Egresados',
                        data: data,
                        backgroundColor: '#36A2EB'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        },
        error: function (xhr, status, error) { console.log(error); }
    });

    // Obtener datos de año de graduación
    $.ajax({
        url: '../controlador/DashboardController.php',
        type: 'POST',
        data: { funcion: 'obtenerDatosGraduacion' },
        success: function (response) {
            const datos = JSON.parse(response);
            const labels = Object.keys(datos);
            const data = Object.values(datos);

            let summaryHtml = '';
            if (labels.length > 0) {
                const primerAnio = labels[0];
                const ultimoAnio = labels[labels.length - 1];
                const totalPeriodo = data.reduce((a, b) => parseInt(a) + parseInt(b), 0);
                summaryHtml = `Total graduados entre ${primerAnio} y ${ultimoAnio}: <strong>${totalPeriodo}</strong>`;
            }
            $('#graduacionSummary').html(summaryHtml);

            var ctxGraduacion = document.getElementById('graduacionChart').getContext('2d');
            new Chart(ctxGraduacion, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Egresados por Año',
                        data: data,
                        borderColor: '#FF6384',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        },
        error: function (xhr, status, error) { console.log(error); }
    });

    // Obtener datos de mes de graduación
    $.ajax({
        url: '../controlador/DashboardController.php',
        type: 'POST',
        data: { funcion: 'obtenerDatosMes' },
        success: function (response) {
            const datos = JSON.parse(response);
            const labels = Object.keys(datos);
            const data = Object.values(datos);

            // Encontrar mes con más graduaciones
            let maxGraduados = Math.max(...data);
            let mesMaxIndex = data.indexOf(maxGraduados);
            let mesMax = labels[mesMaxIndex];

            let summaryHtml = `Mes con más graduados: <strong>${mesMax} (${maxGraduados} egresados)</strong>`;
            $('#mesSummary').html(summaryHtml);

            var ctxMes = document.getElementById('mesChart').getContext('2d');
            new Chart(ctxMes, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Egresados por Mes',
                        data: data,
                        backgroundColor: '#17a2b8',
                        borderColor: '#117a8b',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        },
        error: function (xhr, status, error) { console.log(error); }
    });
});
