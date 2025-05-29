$(document).ready(function () {
  // Flights Chart
  const flightsCtx = document.getElementById("flightsChart").getContext("2d");
  new Chart(flightsCtx, {
    type: "line",
    data: {
      labels: chartData.flights.labels,
      datasets: [
        {
          label: "Flights Scheduled",
          data: chartData.flights.data,
          backgroundColor: "rgba(34, 197, 94, 0.2)",
          borderColor: "#22c55e",
          borderWidth: 2,
          fill: true,
          // tension: 0.4,
        },
      ],
    },
    options: {
      responsive: true,
      interaction: {
        mode: "index",
        intersect: false,
      },
      plugins: {
        legend: {
          position: "top",
          labels: { font: { size: 14, family: "Arial" } },
        },
        title: {
          display: true,
          text: "Monthly Flights Scheduled",
          font: { size: 16, family: "Arial" },
        },
        tooltip: { enabled: true },
      },
      scales: {
        y: {
          beginAtZero: true,
          suggestedMax: 20,
          grid: { color: "#e5e7eb" },
        },
        x: { grid: { display: false } },
      },
      animation: { duration: 1000 },
    },
  });

  // Bookings Chart
  const bookingsCtx = document.getElementById("bookingsChart").getContext("2d");
  new Chart(bookingsCtx, {
    type: "bar",
    data: {
      labels: chartData.bookings.labels,
      datasets: [
        {
          label: "Bookings",
          data: chartData.bookings.data,
          backgroundColor: "rgba(59, 130, 246, 0.2)",
          barPercentage: 0.5,
          borderColor: "#3b82f6",
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "top",
          labels: { font: { size: 14, family: "Arial" } },
        },
        title: {
          display: true,
          text: "Monthly Bookings",
          font: { size: 16, family: "Arial" },
        },
        tooltip: { enabled: true },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
          },
          suggestedMax: 10,
          grid: { color: "#e5e7eb" },
        },
        x: { grid: { display: false } },
      },
      animation: { duration: 1000 },
    },
  });

  // Users Chart
  const usersCtx = document.getElementById("usersChart").getContext("2d");
  new Chart(usersCtx, {
    type: "bar",
    data: {
      labels: chartData.users.labels,
      datasets: [
        {
          label: "New Users",
          data: chartData.users.data,
          backgroundColor: "rgba(239, 68, 68, 0.5)",
          barPercentage: 0.5,
          borderColor: "#ef4444",
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "top",
          labels: { font: { size: 14, family: "Arial" } },
        },
        title: {
          display: true,
          text: "Monthly New Users",
          font: { size: 16, family: "Arial" },
        },
        tooltip: { enabled: true },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
          },
          suggestedMax: 10,
          grid: { color: "#e5e7eb" },
        },
        x: {
          grid: { display: false },
        },
      },
      animation: { duration: 1000 },
    },
  });
});
