<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Fire Monitoring Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Firebase SDK -->
  <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-database-compat.js"></script>
  <!-- SheetJS for Excel export -->
  <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

  <style>
    #alarm-overlay { animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%,100%{border-color:transparent;} 50%{border-color:red;} }
    #minor-modal .modal-title { color:#d97706; }
    #minor-modal.critical .modal-title { color:#dc2626; }
    #minor-modal.critical .critical-ring { box-shadow:0 0 0 3px rgba(220,38,38,.35); }
  </style>
</head>
<body class="bg-gray-100 min-h-screen p-6">

<!-- 🔴 Alarm Overlay -->
<div id="alarm-overlay" class="hidden fixed inset-0 border-8 border-red-600 pointer-events-none z-40"></div>
<!-- Alarm sound -->
<audio id="alarm-sound" src="https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg" preload="auto" loop></audio>

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
                <a href="{{ route('Homepage') }}" class="nav-link px-4 py-2 rounded-lg" data-page="Homepage">📊
                    Dashboard</a>
                <a href="{{ route('Alert_type') }}" class="nav-link px-4 py-2 rounded-lg" data-page="Alert_type">🚨
                    Alert System</a>
                <a href="{{ route('Event_log_history') }}" class="nav-link px-4 py-2 rounded-lg"
                    data-page="Event_log_history">📜 Event Logs</a>
                <a href="{{ route('extinguisher') }}" class="nav-link px-4 py-2 rounded-lg" data-page="extinguisher">🧯
                    Extinguisher Guide</a>

                @if (session('role') === 'OSHO' || session('role') === 'SECURITY')
                 <a href="{{ route('fire_feed')}}" class="nav-link px-4 py-2 rounded-lg"  data-page="fire_feed">🖼️ Image Feed</a>
                @endif

                @if (session('role') === 'OSHO')
                    <a href="{{ route('AdminOSHO') }}" class="nav-link px-4 py-2 rounded-lg" data-page="AdminOSHO">👤
                        New Admin</a>
                @endif

            </div>

            <!-- Mobile Hamburger -->
            <button id="mobile-menu-btn" class="md:hidden text-gray-600 focus:outline-none text-2xl">
                ☰
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden flex-col px-4 pb-3 space-y-2 md:hidden text-gray-600 font-medium">
            <a href="{{ route('Homepage') }}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100"
                data-page="Homepage">📊 Dashboard</a>
            <a href="{{ route('Alert_type') }}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100"
                data-page="Alert_type">🚨 Alert System</a>
            <a href="{{ route('Event_log_history') }}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100"
                data-page="Event_log_history">📜 Event Logs</a>
            <a href="{{ route('extinguisher') }}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100"
                data-page="extinguisher">🧯 Extinguisher Guide</a>

            @if (session('role') === 'OSHO' || session('role') === 'SECURITY')
           <a href="{{ route('fire_feed')}}" class="nav-link px-4 py-2 rounded-lg  hover:bg-red-100"  data-page="fire_feed">🖼️ Image Feed</a>
            @endif

            @if (session('role') === 'OSHO')
                <a href="{{ route('AdminOSHO') }}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100"
                    data-page="AdminOSHO">👤 New Admin Registration</a>
            @endif
        </div>
    </nav>

<!-- Event Log History -->
<div class="bg-white p-6 rounded-2xl shadow-md mb-4 border-l-4 border-red-600">
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-semibold text-red-900">Event Log History</h2>
    @if(session('role') === 'OSHO')
      <button id="exportBtn" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        Export to Excel
      </button>
    @endif
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-red-600 text-white">
        <tr>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Timestamp</th>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Event</th>
          <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Status</th>
        </tr>
      </thead>
      <tbody id="logs-body">
        <tr>
          <td colspan="3" class="px-6 py-4 text-center text-gray-500">Loading logs...</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script>
  // Firebase config
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

  // Initialize Firebase
  firebase.initializeApp(firebaseConfig);
  const db = firebase.database();
  const logsRef = db.ref('fire_logs');
  const logsBody = document.getElementById('logs-body');

  const alarmOverlay = document.getElementById('alarm-overlay');
  const alarmSound = document.getElementById('alarm-sound');
  let currentLogs = [];

  // Listen for real-time updates
  logsRef.on('value', snapshot => {
    const data = snapshot.val();
    logsBody.innerHTML = '';
    currentLogs = [];

    let hasActiveAlert = false;

    if (data) {
      Object.values(data).forEach(log => {
        currentLogs.push(log);

        // Trigger alarm if status is not "resolved"
        if (log.status && log.status.toLowerCase() !== "resolved") {
          hasActiveAlert = true;
        }

        const row = `
          <tr class="hover:bg-gray-100">
            <td class="px-6 py-3 text-left text-sm font-semibold uppercase">
              ${log.created_at ? new Date(log.created_at).toLocaleString('en-PH', {
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
              }) : 'N/A'}
            </td>
            <td class="px-6 py-3 text-left text-sm font-semibold uppercase">
              ${(log.alert_type || 'N/A') + ' - ' + (log.location_name || 'N/A')}
            </td>
            <td class="px-6 py-3 text-left text-sm font-semibold uppercase">${log.status || 'N/A'}</td>
          </tr>
        `;
        logsBody.insertAdjacentHTML('beforeend', row);
      });
    } else {
      logsBody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No logs found.</td></tr>';
    }

    // Handle alarm overlay & sound
    if (hasActiveAlert) {
      alarmOverlay.classList.remove('hidden');
      alarmSound.play().catch(e => console.log("Alarm play error:", e));
    } else {
      alarmOverlay.classList.add('hidden');
      alarmSound.pause();
      alarmSound.currentTime = 0;
    }
  });

  // Export to Excel
  document.getElementById('exportBtn').addEventListener('click', () => {
    if (!currentLogs.length) return alert("No data to export!");

    const wsData = [
      ["Timestamp", "Event", "Status"],
      ...currentLogs.map(log => [
        log.created_at ? new Date(log.created_at).toLocaleString('en-PH') : 'N/A',
        (log.alert_type || 'N/A') + ' - ' + (log.location_name || 'N/A'),
        log.status || 'N/A'
      ])
    ];

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(wsData);
    XLSX.utils.book_append_sheet(wb, ws, "Fire Logs");

    XLSX.writeFile(wb, `fire_logs_${new Date().toISOString().slice(0,10)}.xlsx`);
  });

  // Navigation highlight
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

  // Mobile menu toggle
  const menuBtn = document.getElementById("mobile-menu-btn");
  const mobileMenu = document.getElementById("mobile-menu");
  menuBtn.addEventListener("click", () => {
    mobileMenu.classList.toggle("hidden");
  });
</script>

</body>
</html>