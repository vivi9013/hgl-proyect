import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js';
Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('canvasGraficaBarras');
    
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: window.chartLabels || [],
                datasets: [{
                    label: 'Cantidad de Módulos',
                    data: window.chartValues || [],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)', // Azul corporativo plano
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1.5,
                    borderRadius: 4 // Bordes suavizados para las barras (Estilo Premium)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Ocultamos la leyenda superior ya que el título es descriptivo
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1 // Asegura valores enteros al ser contadores de módulos
                        }
                    }
                }
            }
        });
    }
});
