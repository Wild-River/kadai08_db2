// 緑の単色ランプ（淡→濃）で「未予約在庫 → 予約中・未出荷 → 出荷済み」という
// 進行段階を表す。1つの色相・単調な明度差でordinalチェックを通過済みの3色。
const stageColors = ['#f59f00', '#4299e1', '#1abb9c'];

new ApexCharts(document.querySelector('#statusDonutChart'), {
    chart: {
        type: 'donut',
        height: 260,
        fontFamily: 'inherit'
    },
    series: [totalUnreserved, totalPending, totalShipped],
    labels: ['未予約在庫', '予約中・未出荷', '出荷済み'],
    colors: stageColors,
    stroke: { width: 2, colors: ['#ffffff'] },
    legend: { position: 'bottom', fontSize: '12px' },
    dataLabels: {
        enabled: true,
        formatter: (val) => Math.round(val) + '%'
    },
    plotOptions: {
        pie: {
            donut: {
                size: '65%',
                labels: {
                    show: true,
                    name: { show: true, fontSize: '13px' },
                    value: {
                        show: true,
                        fontSize: '20px',
                        fontWeight: 700,
                        formatter: (val) => val + ' 袋'
                    },
                    total: {
                        show: true,
                        label: '予約中・未出荷',
                        fontSize: '14px',
                        color: '#1abb9c',
                        formatter: () => totalPending + ' 袋'
                    }
                }
            }
        }
    },
    tooltip: {
        y: { formatter: (val) => val + ' 袋' }
    },
    states: {
        hover: { filter: { type: 'none' } },
        active: { filter: { type: 'none' } }
    }
}).render();

new ApexCharts(document.querySelector('#stockChart'), {
    chart: {
        type: 'bar',
        height: Math.max(240, chartLabels.length * 44),
        stacked: true,
        toolbar: { show: false },
        fontFamily: 'inherit'
    },
    series: [
        { name: '未予約在庫', data: chartUnreserved },
        { name: '予約中・未出荷', data: chartPending },
        { name: '出荷済み', data: chartShipped }
    ],
    colors: stageColors,
    plotOptions: {
        bar: {
            horizontal: true,
            barHeight: '60%',
            borderRadius: 4,
            borderRadiusApplication: 'end' // 積み上げの内側の境目は角丸にしない
        }
    },
    xaxis: {
        categories: chartLabels,
        labels: { formatter: (val) => Math.round(val) }
    },
    // 積み上げセグメントの間に白い2pxの隙間を作り、区切りを線ではなく余白で表現
    stroke: { width: 2, colors: ['#ffffff'] },
    legend: { position: 'bottom', fontSize: '13px' },
    dataLabels: { enabled: false },
    grid: { borderColor: '#e3e8e4' },
    tooltip: {
        y: { formatter: (val) => val + ' 袋' }
    },
    states: {
        hover: { filter: { type: 'none' } },
        active: { filter: { type: 'none' } }
    }
}).render();
