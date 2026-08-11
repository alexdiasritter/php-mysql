/**
 * Gráficos do dashboard (earnings.php).
 * Os dados vêm do <script type="application/json" id="earnings-data">
 * renderizado pelo PHP — nada de PHP misturado com JS aqui.
 */

const dataTag = document.getElementById("earnings-data");

if (dataTag && typeof Chart !== "undefined") {
  const charts = JSON.parse(dataTag.textContent);

  // Tema escuro
  Chart.defaults.color = "#94a3b8";
  Chart.defaults.borderColor = "rgba(255,255,255,0.1)";

  const CORES = {
    azul: "#3b82f6",
    ciano: "rgba(6, 182, 212, 0.6)",
    cianoBorda: "rgba(6, 182, 212, 1)",
    verde: "rgba(16, 185, 129, 0.6)",
    verdeBorda: "rgba(16, 185, 129, 1)",
    azulFill: "rgba(59, 130, 246, 0.6)",
    azulBorda: "rgba(59, 130, 246, 1)",
    indigo: "rgba(99, 102, 241, 0.6)",
    indigoBorda: "rgba(99, 102, 241, 1)",
    ambar: "#f59e0b",
  };

  const PALETA_ORIGENS = [
    "rgba(59, 130, 246, 0.6)",
    "rgba(6, 182, 212, 0.6)",
    "rgba(16, 185, 129, 0.6)",
    "rgba(99, 102, 241, 0.6)",
    "rgba(14, 165, 233, 0.6)",
  ];

  const PALETA_DESTINOS = [
    "rgba(239, 68, 68, 0.6)",
    "rgba(249, 115, 22, 0.6)",
    "rgba(234, 179, 8, 0.6)",
    "rgba(244, 63, 94, 0.6)",
    "rgba(217, 119, 6, 0.6)",
  ];

  /** Só cria o gráfico se o canvas existir na página */
  const criarGrafico = (canvasId, config) => {
    const canvas = document.getElementById(canvasId);
    if (canvas) new Chart(canvas.getContext("2d"), config);
  };

  const linhaBase = (label, valores, cor, fundo) => ({
    label,
    data: valores,
    borderColor: cor,
    backgroundColor: fundo,
    fill: true,
    tension: 0.4,
    borderWidth: 3,
    pointBackgroundColor: cor,
    pointRadius: 4,
  });

  const barraBase = (label, valores, fundo, borda, eixo) => ({
    label,
    data: valores,
    backgroundColor: fundo,
    borderColor: borda,
    borderWidth: 2,
    borderRadius: 8,
    yAxisID: eixo,
  });

  const duploEixo = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: { position: "left", beginAtZero: true },
      y1: { position: "right", beginAtZero: true, grid: { drawOnChartArea: false } },
    },
  };

  const linhaOpcoes = {
    responsive: true,
    maintainAspectRatio: false,
    scales: { y: { beginAtZero: true } },
  };

  // 1. Tendência dos últimos 30 dias
  criarGrafico("trendChart", {
    type: "line",
    data: {
      labels: charts.days.labels,
      datasets: [linhaBase("Ganhos (R$)", charts.days.values, CORES.azul, "rgba(59, 130, 246, 0.2)")],
    },
    options: linhaOpcoes,
  });

  // 2. Visão geral: semana vs mês
  criarGrafico("earningsChart", {
    type: "bar",
    data: {
      labels: ["Esta Semana", "Este Mês"],
      datasets: [
        barraBase(
          "Ganhos (R$)",
          [charts.overview.week_total, charts.overview.month_total],
          CORES.ciano,
          CORES.cianoBorda,
          "y",
        ),
        barraBase(
          "Qtd. Corridas",
          [charts.overview.week_rides, charts.overview.month_rides],
          CORES.verde,
          CORES.verdeBorda,
          "y1",
        ),
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: { y: { position: "left" }, y1: { position: "right", grid: { drawOnChartArea: false } } },
    },
  });

  // 3. Comparativo mensal: valor x volume
  criarGrafico("monthlyChart", {
    type: "bar",
    data: {
      labels: charts.months.labels,
      datasets: [
        barraBase("Valor Total (R$)", charts.months.values, CORES.azulFill, CORES.azulBorda, "y"),
        barraBase("Qtd. Corridas", charts.months.counts, CORES.verde, CORES.verdeBorda, "y1"),
      ],
    },
    options: duploEixo,
  });

  // 4. Bairros de origem
  criarGrafico("originsChart", {
    type: "polarArea",
    data: {
      labels: charts.origins.labels,
      datasets: [
        {
          data: charts.origins.values,
          backgroundColor: PALETA_ORIGENS,
          borderColor: "#1e293b",
          borderWidth: 2,
        },
      ],
    },
    options: { responsive: true, scales: { r: { ticks: { backdropColor: "transparent" } } } },
  });

  // 5. Bairros de destino
  criarGrafico("destinationsChart", {
    type: "polarArea",
    data: {
      labels: charts.destinations.labels,
      datasets: [
        {
          data: charts.destinations.values,
          backgroundColor: PALETA_DESTINOS,
          borderColor: "#1e293b",
          borderWidth: 2,
        },
      ],
    },
    options: { responsive: true, scales: { r: { ticks: { backdropColor: "transparent" } } } },
  });

  // 6. Clínicas mais acionadas (barra horizontal)
  criarGrafico("clinicsChart", {
    type: "bar",
    data: {
      labels: charts.clinics.labels,
      datasets: [
        {
          label: "Corridas",
          data: charts.clinics.values,
          backgroundColor: CORES.indigo,
          borderColor: CORES.indigoBorda,
          borderWidth: 2,
          borderRadius: 8,
        },
      ],
    },
    options: {
      indexAxis: "y",
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } },
    },
  });

  // 7. Média de valor por corrida
  criarGrafico("avgChart", {
    type: "line",
    data: {
      labels: charts.months.labels,
      datasets: [linhaBase("Média (R$)", charts.months.avg, CORES.ambar, "rgba(245, 158, 11, 0.2)")],
    },
    options: linhaOpcoes,
  });
}
