'use strict';

(function () {
  let cardColor, headingColor, labelColor, shadeColor, borderColor, legendColor;

  if (isDarkStyle) {
    cardColor = config.colors_dark.cardColor;
    headingColor = config.colors_dark.headingColor;
    labelColor = config.colors_dark.textMuted;
    borderColor = config.colors_dark.borderColor;
    legendColor = config.colors_dark.bodyColor;
    shadeColor = 'dark';
  } else {
    cardColor = config.colors.cardColor;
    headingColor = config.colors.headingColor;
    labelColor = config.colors.textMuted;
    borderColor = config.colors.borderColor;
    legendColor = config.colors.bodyColor;
    shadeColor = '';
  }

  const monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];

  function loadTotalIncomeData() {
    // Obtener el valor del filtro de tiempo seleccionado
    const timeRange = document.querySelector('select[name="period"]').value;
    const storeId = document.querySelector('select[name="store_id"]').value; // Agregar esto

    fetch(`/admin/api/monthly-income?time_range=${timeRange}&store_id=${storeId}`) // Incluir store_id en la URL
        .then(response => response.json())
        .then(data => {
        let labels = [];
        let totalIncomes = [];

        if (timeRange === 'always') {
            labels = data.map(item => `${monthNames[item.month - 1]} ${item.year}`);
            totalIncomes = data.map(item => parseInt(item.total));
        } else if (timeRange === 'year') {
            labels = monthNames; // Asegúrate de que solo se representen los meses
            totalIncomes = Array(12).fill(0);
            data.forEach(item => {
                const monthIndex = item.month - 1;
                totalIncomes[monthIndex] = parseInt(item.total);
            });
        } else if (timeRange === 'month') {
          const today = new Date();
          const daysInMonth = today.getDate(); // Obtener el día actual del mes
          // Crear un array con todos los días desde el 1 hasta el día actual
          for (let i = 1; i <= daysInMonth; i++) {
              labels.push(`${i}/${today.getMonth() + 1}`);
              totalIncomes.push(0); // Inicializar con 0
          }
          // Asignar los ingresos a los días correspondientes
          data.forEach(item => {
              const label = `${item.day}/${item.month}`;
              const index = labels.indexOf(label);
              if (index !== -1) {
                  totalIncomes[index] = parseInt(item.total);
              }
          });
        } else if (timeRange === 'week') {
            // Crear un array de 7 días empezando desde hoy (o el primer día de la semana)
            const today = new Date();
            for (let i = 6; i >= 0; i--) {
                const day = new Date(today);
                day.setDate(today.getDate() - i);
                labels.push(`${day.getDate()}/${day.getMonth() + 1}`);
                totalIncomes.push(0); // Inicializar con 0
            }

            // Asignar los ingresos a los días correspondientes
            data.forEach(item => {
                const label = `${item.day}/${item.month}`;
                const index = labels.indexOf(label);
                if (index !== -1) {
                    totalIncomes[index] = parseInt(item.total);
                }
            });
        } else if (timeRange === 'today') {
            labels = Array.from({length: 24}, (_, i) => `${i}:00`);
            totalIncomes = Array(24).fill(0);
            data.forEach(item => {
                totalIncomes[item.hour] = parseInt(item.total);
            });
        }

        const totalIncomeConfig = {
          chart: {
            height: 400,
            type: 'area',
            toolbar: false,
            dropShadow: {
              enabled: true,
              top: 14,
              left: 2,
              blur: 3,
              color: config.colors.primary,
              opacity: 0.15
            }
          },
          series: [{
            name: "Ingresos",
            data: totalIncomes
          }],
          labels: labels,
          dataLabels: {
            enabled: false
          },
          stroke: {
            width: 2,
            curve: 'smooth'
          },
          colors: [config.colors.primary],
          fill: {
            type: 'gradient',
            gradient: {
              shade: 'light',
              shadeIntensity: 0.8,
              opacityFrom: 0.7,
              opacityTo: 0.25,
              stops: [0, 95, 100]
            }
          },
          grid: {
            show: true,
            borderColor: '#90A4AE',
            padding: {
              top: -15,
              bottom: -10,
              left: 0,
              right: 0
            }
          },
          xaxis: {
            categories: labels,
            labels: {
                offsetX: 0,
                rotate: -45, // Mantener los labels rotados si hay muchos días
                style: {
                    colors: '#757575',
                    fontSize: '13px'
                }
            },
            tickAmount: labels.length, // Asegura que se muestre un tick por cada label
          },
          yaxis: {
            labels: {
              offsetX: -15,
              formatter: function (val) {
                return '$' + val.toLocaleString();
              },
              style: {
                fontSize: '13px',
                colors: '#757575'
              }
            },
            min: 0,
            tickAmount: 10,
            forceNiceScale: true,
          },
          tooltip: {
            x: {
              show: true,
              format: 'MMM yyyy',
              formatter: function (value, { series, seriesIndex, dataPointIndex, w }) {
                return labels[dataPointIndex];
              }
            },
            y: {
              formatter: function (val, opts) {
                return '$' + val.toLocaleString();
              }
            }
          }
        };

        const totalIncome = new ApexCharts(document.querySelector('#totalIncomeChart'), totalIncomeConfig);
        totalIncome.render();
      })
      .catch(error => console.error('Error loading the data: ', error));
  }

  loadTotalIncomeData();

  document.querySelector('select[name="period"]').addEventListener('change', function() {
    loadTotalIncomeData();
  });

})();
