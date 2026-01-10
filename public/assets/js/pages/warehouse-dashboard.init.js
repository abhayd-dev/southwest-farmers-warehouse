document.addEventListener("DOMContentLoaded", function () {

    /* =======================
       STOCK MOVEMENT BAR CHART
    ======================== */
    var stockOptions = {
        chart: {
            type: 'bar',
            height: 320,
            toolbar: { show: false }
        },
        series: [{
            name: 'Stock In',
            data: [120, 150, 170, 160, 180, 200, 210, 190, 175, 185, 195, 220]
        }, {
            name: 'Stock Out',
            data: [100, 130, 150, 145, 160, 170, 180, 165, 155, 160, 170, 190]
        }],
        colors: ['#019934', '#ff4d4f'],
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: '45%'
            }
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
        },
        legend: {
            position: 'top'
        }
    };

    new ApexCharts(
        document.querySelector("#stockMovementChart"),
        stockOptions
    ).render();

    /* =======================
       ORDER STATUS PIE CHART
    ======================== */
    var orderOptions = {
        chart: {
            type: 'pie',
            height: 320
        },
        series: [34, 58, 108],
        labels: ['Pending', 'Dispatched', 'Delivered'],
        colors: ['#faad14', '#019934', '#1677ff'],
        legend: {
            position: 'bottom'
        }
    };

    new ApexCharts(
        document.querySelector("#orderStatusChart"),
        orderOptions
    ).render();

});
