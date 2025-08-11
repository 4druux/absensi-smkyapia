import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from "chart.js";
import { Bar } from "react-chartjs-2";

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend
);

const BarChart = ({ title, labels, data, backgroundColor }) => {
    const options = {
        responsive: true,
        plugins: {
            legend: {
                display: false,
            },
            title: {
                display: true,
                text: title,
                font: {
                    size: 14,
                },
                color: "#334155",
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0,
                },
                grid: {
                    color: "#e2e8f0",
                },
                border: {
                    display: false,
                },
            },
            x: {
                grid: {
                    display: false,
                },
            },
        },
    };

    const chartData = {
        labels,
        datasets: [
            {
                label: "Jumlah",
                data: data,
                backgroundColor: backgroundColor || "rgba(59, 130, 246, 0.5)",
                borderColor: backgroundColor
                    ? backgroundColor.replace("0.5", "1")
                    : "rgba(59, 130, 246, 1)",
                borderWidth: 1,
                borderRadius: 8,
                borderSkipped: false,
            },
        ],
    };

    return <Bar options={options} data={chartData} />;
};

export default BarChart;
