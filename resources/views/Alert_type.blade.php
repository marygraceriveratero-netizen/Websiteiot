<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Fire Monitoring Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Firebase -->
  <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-database-compat.js"></script>

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
            <a href="{{ route('fire_feed')}}" class="nav-link px-4 py-2 rounded-lg  hover:bg-red-100 " data-page="fire_feed">🖼️ Image Feed</a>
            @endif

            @if (session('role') === 'OSHO')
                <a href="{{ route('AdminOSHO') }}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100"
                    data-page="AdminOSHO">👤 New Admin Registration</a>
            @endif
        </div>
    </nav>

  <!-- Event Log History -->
  <div class="bg-white p-6 rounded-2xl shadow-md mb-4 border-l-4 border-red-600">
    <h2 class="text-xl font-semibold text-red-900 mb-4">Current Alerts</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200" id="alerts-table">
        <thead class="bg-red-600 text-white">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Alert Type</th>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Location</th>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Severity</th>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Time</th>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Status</th>
            <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white" id="alerts-body">
          <tr>
            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading alerts...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Alarm sound -->
  <audio id="alarm-sound" src="https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg" preload="auto" loop></audio>

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
  firebase.initializeApp(firebaseConfig);
  const db = firebase.database();
  const alertsRef = db.ref('fire_logs'); // Your Firebase node

  const alertsBody = document.getElementById('alerts-body');
  const alarmSound = document.getElementById('alarm-sound');
  const alarmOverlay = document.getElementById('alarm-overlay');

  // Function to resolve an alert
  function resolveAlert(key) {
    alertsRef.child(key).update({ status: 'Resolved' });
  }

  // Listen for alert updates
  alertsRef.on('value', snapshot => {
    const data = snapshot.val();
    alertsBody.innerHTML = '';

    let hasUnresolved = false; // Flag if may hindi pa resolved

    if (data) {
      Object.entries(data).forEach(([key, alert]) => {
        const isResolved = alert.status === 'Resolved';
        if (!isResolved) hasUnresolved = true;

        const row = `
          <tr>
            <td class="px-6 py-4">${alert.alert_type || 'Unknown'}</td>
            <td class="px-6 py-4">${alert.location_name || 'Unknown'}</td>
            <td class="px-6 py-4">${alert.severity || 'Unknown'}</td>
            <td class="px-6 py-4">${alert.created_at ? new Date(alert.created_at).toLocaleString() : 'N/A'}</td>
            <td class="px-6 py-4">${alert.status || 'Unknown'}</td>
            <td class="px-6 py-4">
              @if(session('role') === 'OSHO')
                ${!isResolved ? `<button class="bg-blue-500 text-white px-2 py-1 rounded" onclick="resolveAlert('${key}')">Resolve</button>` : ''}
              @endif
            </td>
          </tr>
        `;
        alertsBody.insertAdjacentHTML('beforeend', row);
      });
    } else {
      alertsBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No alerts found.</td></tr>';
    }

    // 🔔 Control alarm sound and overlay
    if (hasUnresolved) {
      if (alarmSound.paused) {
        alarmSound.play().catch(() => {});
      }
      alarmOverlay.classList.remove('hidden');
    } else {
      alarmSound.pause();
      alarmSound.currentTime = 0;
      alarmOverlay.classList.add('hidden');
    }
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