<!DOCTYPE html> <!-- This page is for termination -->
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>IoT Chart Sensor Dashboard</title>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-database-compat.js"></script>

<style>
.toast { position: absolute; top: 10px; right: 10px; background-color: rgba(255, 99, 71, 0.9); color: white; padding: 15px; border-radius: 8px; max-width: 300px; display: none; z-index: 1000; }
.toast.show { display: block; }
.spinner { border: 4px solid rgba(255, 255, 255, 0.3); border-top: 4px solid #ff6347; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
</head>

<body class="bg-gray-100 min-h-screen p-6">

 <!-- Header -->
  <header class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
    <div>
      <h1 class="text-2xl md:text-3xl font-bold text-red-600">🔥 Fire Monitoring Dashboard</h1>
      <p class="text-gray-600 text-sm md:text-base">
        Monitor real-time data, view alerts, and manage fire safety operations from one clean interface.
      </p>
    </div>
  </header>

  <!-- Navigation -->
<nav class="bg-white shadow-md rounded-2xl mb-6">
  <div class="flex justify-between items-center px-4 py-3 md:px-6">

    <!-- Desktop Menu -->
    <div class="hidden md:flex space-x-4 text-gray-600 font-medium">
      <a href="{{ route('Homepage') }}" class="nav-link px-4 py-2 rounded-lg" data-page="Homepage">📊 Dashboard</a>
      <a href="{{ route('Alert_type') }}" class="nav-link px-4 py-2 rounded-lg" data-page="Alert_type">🚨 Alert System</a>
      <a href="{{ route('Event_log_history') }}" class="nav-link px-4 py-2 rounded-lg" data-page="Event_log_history">📜 Event Logs</a>
      <a href="{{ route('extinguisher') }}" class="nav-link px-4 py-2 rounded-lg" data-page="extinguisher">🧯 Extinguisher Guide</a>

     @if(session('role') === 'OSHO' || session('role') === 'SECURITY')
      <a href="{{ route('chart_sensor_dashboard')}}" class="nav-link px-4 py-2 rounded-lg" data-page="chart_sensor_dashboard">📡 Live Data</a>
      <a href="{{ route('fire-feed')}}" class="nav-link px-4 py-2 rounded-lg"  data-page="fire-feed">🖼️ Image Feed</a>
     @endif

     @if(session('role') === 'OSHO')
      <a href="{{ route('AdminOSHO') }}" class="nav-link px-4 py-2 rounded-lg" data-page="AdminOSHO">👤 New Admin</a>
     @endif

    </div>

    <!-- Mobile Hamburger -->
    <button id="mobile-menu-btn" class="md:hidden text-gray-600 focus:outline-none text-2xl">
      ☰
    </button>
  </div>

  <!-- Mobile Menu -->
  <div id="mobile-menu" class="hidden flex-col px-4 pb-3 space-y-2 md:hidden text-gray-600 font-medium">
    <a href="{{ route('Homepage') }}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100" data-page="Homepage">📊 Dashboard</a>
    <a href="{{ route('Alert_type') }}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100" data-page="Alert_type">🚨 Alert System</a>
    <a href="{{ route('Event_log_history') }}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100" data-page="Event_log_history">📜 Event Logs</a>
    <a href="{{ route('extinguisher') }}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100" data-page="extinguisher">🧯 Extinguisher Guide</a>

    @if(session('role') === 'OSHO' || session('role') === 'SECURITY')
      <a href="{{ route('chart_sensor_dashboard')}}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100" data-page="chart_sensor_dashboard">📡 Live Data</a>
          <a href="{{ route('fire-feed')}}" class="nav-link px-4 py-2 rounded-lg"  data-page="fire-feed">🖼️ Image Feed</a>
    @endif

    @if(session('role') === 'OSHO')
      <a href="{{ route('AdminOSHO') }}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100" data-page="AdminOSHO">👤 New Admin Registration</a>
    @endif
  </div>
</nav>

<main class="max-w-7xl mx-auto px-6">
  <section class="bg-white p-6 shadow-xl rounded-2xl">
    <h2 class="text-2xl font-semibold mb-4 text-red-600">🔥 IoT Chart Sensor Dashboard</h2>

    <div class="relative overflow-x-auto">
      <canvas id="sensorChart" class="w-full h-96"></canvas>
      <div id="loadingSpinner" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 spinner"></div>
    </div>

    <div id="alerts" class="alert text-red-700 text-lg mt-4"></div>
    <p id="lastUpdate" class="text-gray-600 text-sm mt-2">Last updated: <span id="updateTime">N/A</span></p>
  </section>
</main>

<div id="toast" class="toast"></div>

<script>
  // Firebase Config
  const firebaseConfig = {
  apiKey: "AIzaSyA24eplvEuIp0aKJE6oD0P9Angub1kLG5E",
  authDomain: "fire-monitoring-b1d3c.firebaseapp.com",
  databaseURL: "https://fire-monitoring-b1d3c-default-rtdb.firebaseio.com",
  projectId: "fire-monitoring-b1d3c",
  storageBucket: "fire-monitoring-b1d3c.firebasestorage.app",
  messagingSenderId: "1095677943838",
  appId: "1:1095677943838:web:48e9283d440d10f139ce0f",
  measurementId: "G-8Y97X997JH"
};

  firebase.initializeApp(firebaseConfig);
  const db = firebase.database();
  const sensorRef = db.ref('fire_logs'); // Firebase node

  // Chart setup
  let ctx = document.getElementById('sensorChart').getContext('2d');
  let sensorChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: [],
      datasets: [
        { label: 'Temperature (°C)', data: [], borderColor: 'red', fill: false },
        { label: 'Smoke Level (ppm)', data: [], borderColor: 'gray', fill: false },
        { label: 'Air Quality (AQI)', data: [], borderColor: 'green', fill: false }
      ]
    },
    options: {
      responsive: true,
      animation: false,
      plugins: { legend: { labels: { font: { size: 14 } } } },
      scales: {
        x: {
          type: 'time',
          time: { tooltipFormat: 'HH:mm:ss', unit: 'second' },
          title: { display: true, text: 'Time' }
        },
        y: { beginAtZero: true, title: { display: true, text: 'Sensor Value' } }
      }
    }
  });

  // Real-time Firebase listener
  sensorRef.on('value', snapshot => {
    const data = snapshot.val();
    if (!data) return;

    const logs = Object.values(data)
      .filter(log => log.created_at) // ensure timestamp exists
      .sort((a,b) => new Date(a.created_at) - new Date(b.created_at));

    sensorChart.data.labels = logs.map(log => new Date(log.created_at));
    sensorChart.data.datasets[0].data = logs.map(log => Number(log.temperature) || 0);
    sensorChart.data.datasets[1].data = logs.map(log => Number(log.smoke) || 0);
    sensorChart.data.datasets[2].data = logs.map(log => Number(log.air_quality) || 0);
    sensorChart.update();

    // Alerts for latest data
    const latest = logs[logs.length - 1];
    let alertText = '';
    if (latest.temperature > 50) alertText += '⚠️ High Temperature! ';
    if (latest.smoke > 300) alertText += '⚠️ High Smoke Level! ';
    if (latest.air_quality > 150) alertText += '⚠️ Poor Air Quality! ';

    if (alertText) {
      const toast = document.getElementById('toast');
      toast.innerText = alertText;
      toast.classList.add('show');
      setTimeout(() => toast.classList.remove('show'), 3000);
    }

    document.getElementById('updateTime').innerText = new Date().toLocaleTimeString();
    document.getElementById('loadingSpinner').style.display = 'none';
  });
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
      const currentPath = window.location.pathname.replace("/", "") || "Homepage";
      document.querySelectorAll(".nav-link").forEach(link => {
        if (link.dataset.page === currentPath) {
          link.classList.add("bg-blue-600", "text-white", "shadow");
        } else {
          link.classList.add("hover:bg-gray-200");
        }
      });
    });
  </script>

    <script>
  // Mobile menu toggle
  const menuBtn = document.getElementById("mobile-menu-btn");
  const mobileMenu = document.getElementById("mobile-menu");

  menuBtn.addEventListener("click", () => {
    mobileMenu.classList.toggle("hidden");
  });
</script>
</body>
</html>
