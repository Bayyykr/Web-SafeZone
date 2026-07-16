import Chart from "chart.js/auto";

const percentLabelPlugin = {
    id: "percentLabelPlugin",
    afterDatasetsDraw(chart) {
        if (chart.config.type !== "doughnut") return;

        const { ctx } = chart;
        const meta = chart.getDatasetMeta(0);
        const data = chart.data.datasets[0].data;

        ctx.save();
        ctx.fillStyle = "#1e293b";
        ctx.font = "bold 10px Figtree, sans-serif";
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";

        meta.data.forEach((arc, index) => {
            const value = data[index];
            if (value > 5) {
                const { x, y } = arc.tooltipPosition();
                ctx.fillText(`${value}%`, x, y);
            }
        });

        ctx.restore();
    },
};

Chart.register(percentLabelPlugin);

function baseOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 1200,
            easing: "easeOutQuart",
        },
        plugins: {
            legend: {
                position: "bottom",
                labels: {
                    boxWidth: 8,
                    boxHeight: 8,
                    usePointStyle: true,
                    pointStyle: "circle",
                    padding: 16,
                    font: { size: 11, weight: "bold", family: "Figtree" },
                    color: "#64748b",
                },
            },
            tooltip: {
                backgroundColor: "#0f172a",
                titleColor: "#ffffff",
                bodyColor: "#e2e8f0",
                padding: 10,
                cornerRadius: 8,
                displayColors: true,
                boxWidth: 6,
                boxHeight: 6,
                usePointStyle: true,
            },
        },
    };
}

function initializeInfografikCharts() {
    const dataElement = document.getElementById("infografik-chart-data");
    if (!dataElement) return;

    const chartData = JSON.parse(dataElement.textContent || "{}");
    const statistikCanvas = document.getElementById("infografikStatistikChart");
    const kecamatanCanvas = document.getElementById("infografikKecamatanChart");
    const kategoriCanvas = document.getElementById("infografikKategoriChart");

    if (statistikCanvas) {
        const ctx = statistikCanvas.getContext("2d");
        const grad1 = ctx.createLinearGradient(0, 0, 0, 300);
        grad1.addColorStop(0, "rgba(41, 82, 227, 0.2)");
        grad1.addColorStop(1, "rgba(41, 82, 227, 0)");

        const grad2 = ctx.createLinearGradient(0, 0, 0, 300);
        grad2.addColorStop(0, "rgba(69, 184, 169, 0.2)");
        grad2.addColorStop(1, "rgba(69, 184, 169, 0)");

        new Chart(statistikCanvas, {
            type: "line",
            data: {
                labels: chartData.monthly?.labels || [],
                datasets: [
                    {
                        label: "Kejahatan",
                        data: chartData.monthly?.kejahatan || [],
                        borderColor: "#2952e3",
                        backgroundColor: grad1,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: "#ffffff",
                        pointBorderColor: "#2952e3",
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointHoverBorderWidth: 3,
                    },
                    {
                        label: "Kecelakaan",
                        data: chartData.monthly?.kecelakaan || [],
                        borderColor: "#45b8a9",
                        backgroundColor: grad2,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: "#ffffff",
                        pointBorderColor: "#45b8a9",
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointHoverBorderWidth: 3,
                    },
                ],
            },
            options: {
                ...baseOptions(),
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: "#64748b", font: { size: 11, family: "Figtree" } },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: "#64748b",
                            font: { size: 11, family: "Figtree" },
                        },
                        grid: {
                            color: "#f1f5f9",
                            borderDash: [5, 5],
                        },
                    },
                },
            },
        });
    }

    if (kecamatanCanvas) {
        new Chart(kecamatanCanvas, {
            type: "bar",
            data: {
                labels: chartData.locations?.labels || [],
                datasets: [
                    {
                        label: "Kejahatan",
                        data: chartData.locations?.kejahatan || [],
                        backgroundColor: "#2952e3",
                        borderRadius: 4,
                        borderSkipped: false,
                        barThickness: 6,
                    },
                    {
                        label: "Kecelakaan",
                        data: chartData.locations?.kecelakaan || [],
                        backgroundColor: "#45b8a9",
                        borderRadius: 4,
                        borderSkipped: false,
                        barThickness: 6,
                    },
                ],
            },
            options: {
                ...baseOptions(),
                indexAxis: "y",
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: "#64748b",
                            font: { size: 11, family: "Figtree" },
                        },
                        grid: {
                            color: "#f1f5f9",
                            borderDash: [5, 5],
                        },
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: "#64748b", font: { size: 11, family: "Figtree" } },
                    },
                },
            },
        });
    }

    if (kategoriCanvas) {
        new Chart(kategoriCanvas, {
            type: "bar",
            data: {
                labels: chartData.categories?.labels || [],
                datasets: [
                    {
                        label: "Total Kasus",
                        data: chartData.categories?.totals || [],
                        backgroundColor: chartData.categories?.colors || "#2952e3",
                        borderRadius: 6,
                        borderSkipped: false,
                        barThickness: 20,
                    },
                ],
            },
            options: {
                ...baseOptions(),
                plugins: {
                    ...baseOptions().plugins,
                    legend: { display: false },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: "#64748b", font: { size: 10, family: "Figtree" } },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: "#64748b",
                            font: { size: 11, family: "Figtree" },
                        },
                        grid: {
                            color: "#f1f5f9",
                            borderDash: [5, 5],
                        },
                    },
                },
            },
        });
    }
}

function parseDashboardChartData() {
    const dataElement = document.getElementById("dashboard-chart-data");
    if (!dataElement) return null;

    try {
        return JSON.parse(dataElement.textContent || "{}");
    } catch (error) {
        console.error("Invalid dashboard chart data", error);
        return null;
    }
}

function initializeCharts() {
    initializeInfografikCharts();

    const chartData = parseDashboardChartData();
    const statistikCanvas = document.getElementById("statistikChart");
    const laporanCanvas = document.getElementById("laporanChart");
    const pieCanvas = document.getElementById("kejahatanKecelakaanChart");

    if (!chartData) return;

    const maxMonthlyValue = Math.max(
        ...(chartData.monthly?.kejahatan || [0]),
        ...(chartData.monthly?.kecelakaan || [0]),
        1,
    );
    const maxLocationValue = Math.max(
        ...(chartData.locations?.totals || [0]),
        1,
    );
    const categoryTotals = chartData.categories?.totals || [];
    const categorySum = categoryTotals.reduce(
        (total, value) => total + value,
        0,
    );
    const categoryPercentages = categoryTotals.map((value) =>
        categorySum > 0 ? Number(((value / categorySum) * 100).toFixed(1)) : 0,
    );

    if (statistikCanvas) {
        const ctx = statistikCanvas.getContext("2d");
        const grad1 = ctx.createLinearGradient(0, 0, 0, 200);
        grad1.addColorStop(0, "rgba(33, 140, 198, 0.2)");
        grad1.addColorStop(1, "rgba(33, 140, 198, 0)");

        const grad2 = ctx.createLinearGradient(0, 0, 0, 200);
        grad2.addColorStop(0, "rgba(69, 184, 169, 0.2)");
        grad2.addColorStop(1, "rgba(69, 184, 169, 0)");

        new Chart(statistikCanvas, {
            type: "line",
            data: {
                labels: chartData.monthly?.labels || [],
                datasets: [
                    {
                        label: "Kejahatan",
                        data: chartData.monthly?.kejahatan || [],
                        borderColor: "#218cc6",
                        backgroundColor: grad1,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: "#ffffff",
                        pointBorderColor: "#218cc6",
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                    {
                        label: "Kecelakaan",
                        data: chartData.monthly?.kecelakaan || [],
                        borderColor: "#45b8a9",
                        backgroundColor: grad2,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: "#ffffff",
                        pointBorderColor: "#45b8a9",
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                ],
            },
            options: {
                ...baseOptions(),
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: "#64748b", font: { size: 10, family: "Figtree" } },
                    },
                    y: {
                        min: 0,
                        suggestedMax: maxMonthlyValue + 1,
                        ticks: {
                            precision: 0,
                            color: "#64748b",
                            font: { size: 10, family: "Figtree" },
                        },
                        grid: {
                            color: "#f1f5f9",
                            borderDash: [5, 5],
                        },
                    },
                },
            },
        });
    }

    if (laporanCanvas) {
        new Chart(laporanCanvas, {
            type: "bar",
            data: {
                labels: chartData.locations?.labels || [],
                datasets: [
                    {
                        label: "Total Laporan",
                        data: chartData.locations?.totals || [],
                        backgroundColor: "#2952e3",
                        borderRadius: 4,
                        borderSkipped: false,
                        barThickness: 8,
                    },
                ],
            },
            options: {
                ...baseOptions(),
                indexAxis: "y",
                scales: {
                    x: {
                        min: 0,
                        suggestedMax: maxLocationValue + 1,
                        ticks: {
                            precision: 0,
                            color: "#64748b",
                            font: { size: 10, family: "Figtree" },
                        },
                        grid: {
                            color: "#f1f5f9",
                            borderDash: [5, 5],
                        },
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: "#64748b", font: { size: 10, family: "Figtree" } },
                    },
                },
            },
        });
    }

    if (pieCanvas) {
        new Chart(pieCanvas, {
            type: "doughnut",
            data: {
                labels: chartData.categories?.labels || [],
                datasets: [
                    {
                        data: categoryPercentages,
                        backgroundColor: chartData.categories?.colors || "#2952e3",
                        borderWidth: 2,
                        borderColor: "#ffffff",
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "75%",
                animation: {
                    duration: 1200,
                    easing: "easeOutQuart",
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                const count = categoryTotals[context.dataIndex] || 0;
                                return `${context.label}: ${count} laporan (${context.parsed}%)`;
                            },
                        },
                    },
                },
            },
        });
    }
}

window.addEventListener("DOMContentLoaded", initializeCharts);
