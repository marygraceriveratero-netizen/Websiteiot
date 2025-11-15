<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>🔥 Fire Sensor Image Feed</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    #alarm-overlay { animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%,100%{border-color:transparent;} 50%{border-color:red;} }
  </style>

  <script type="module">
    import {
      initializeApp
    } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
    import {
      getDatabase,
      ref,
      query,
      limitToLast,
      onValue,
      get,
      set
    } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-database.js";

    // ✅ Firebase Config
    const firebaseConfig = {
      apiKey: "AIzaSyA24eplvEuIp0aKJE6oD0P9Angub1kLG5E",
      authDomain: "fire-monitoring-b1d3c.firebaseapp.com",
      databaseURL: "https://fire-monitoring-b1d3c-default-rtdb.firebaseio.com",
      projectId: "fire-monitoring-b1d3c",
      storageBucket: "fire-monitoring-b1d3c.appspot.com",
      messagingSenderId: "1095677943838",
      appId: "1:1095677943838:web:48e9283d440d10f139ce0f",
      measurementId: "G-8Y97X997JH"
    };

    // ✅ Initialize Firebase
    const app = initializeApp(firebaseConfig);
    const db = getDatabase(app);
    const logsRef = ref(db, "fire_logs");

    const alarmOverlay = document.getElementById("alarm-overlay");
    const alarmSound = document.getElementById("alarm-sound");
    const fireGrid = document.getElementById("fireGrid");

    // === 🔥 Real-Time Listener for Fire Logs ===
    onValue(logsRef, (snapshot) => {
      const data = snapshot.val();
      fireGrid.innerHTML = "";
      let hasActiveAlert = false;

      if (data) {
        Object.entries(data).forEach(([deviceKey, device]) => {
          if (device.status && device.status.toLowerCase() !== "resolved")
            hasActiveAlert = true;

          if (!device.image_url) return;

          // Card container for both live and image feed
          const card = document.createElement("div");
          card.className =
            "bg-white p-4 rounded-2xl shadow-md border-l-4 border-red-600 w-full max-w-md";

          // Create two sections: Live Feed + Image Feed
          card.innerHTML = `
            <h3 class="text-lg font-semibold text-red-700 mb-3 text-center">📍 ${device.device_id} (${device.location_name || "Unknown"})</h3>

            <!-- Two Feeds Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

              <!-- Live Feed -->
              <div class="relative">
                <img id="live-${device.device_id}" 
                     src="${device.image_url}?t=${new Date().getTime()}" 
                     alt="Live Feed ${device.device_id}" 
                     class="w-full h-56 object-cover rounded-lg border shadow">
                <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">LIVE</span>
              </div>

              <!-- Latest Image Feed -->
              <div class="relative">
                <img id="capture-${device.device_id}" 
                     src="" 
                     alt="Latest Capture ${device.device_id}" 
                     class="w-full h-56 object-cover rounded-lg border shadow">
                <span class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">IMAGE</span>
              </div>
            </div>
          `;

          fireGrid.appendChild(card);

          // === Fetch latest captured image for each device ===
          const latestImageRef = query(ref(db, `fire_logs/${device.device_id}/images`), limitToLast(1));
          onValue(latestImageRef, (imgSnap) => {
            let latestUrl = null;
            imgSnap.forEach((child) => (latestUrl = child.val().url));
            const captureImg = document.getElementById(`capture-${device.device_id}`);
            if (latestUrl) {
              captureImg.src = latestUrl + "?t=" + new Date().getTime();
            } else {
              captureImg.src =
                "https://via.placeholder.com/400x300?text=No+Captured+Image";
            }
          });
        });
      }

      // === Alarm Control ===
      if (hasActiveAlert) {
        alarmOverlay.classList.remove("hidden");
        alarmSound.play().catch((e) => console.log("Alarm play error:", e));
      } else {
        alarmOverlay.classList.add("hidden");
        alarmSound.pause();
        alarmSound.currentTime = 0;
      }
    });

    // === Add Device Modal Logic ===
    const addDeviceBtn = document.getElementById("addDeviceBtn");
    const deviceFormModal = document.getElementById("deviceFormModal");
    const cancelBtn = document.getElementById("cancelBtn");
    const deviceForm = document.getElementById("deviceForm");

    addDeviceBtn.addEventListener("click", () =>
      deviceFormModal.classList.remove("hidden")
    );
    cancelBtn.addEventListener("click", () => {
      deviceFormModal.classList.add("hidden");
      deviceForm.reset();
    });

    deviceForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const location_name = document.getElementById("locationName").value.trim();
      const image_url = document.getElementById("imageUrl").value.trim();

      const snapshot = await get(logsRef);
      const currentData = snapshot.val() || {};
      const nextNumber = Object.keys(currentData).length + 1;
      const nextDeviceId = "DEVICE" + String(nextNumber).padStart(3, "0");

      const newDevice = {
        id: nextDeviceId,
        device_id: nextDeviceId,
        location_name,
        image_url,
        air_quality: 0,
        alert_type: "smoke",
        created_at: new Date().toISOString(),
        extinguisher: "Water",
        fire_detected: 2,
        fire_type: "Class A",
        flame: 0,
        images: [],
        severity: "Low",
        smoke: 0,
        status: "Resolved",
        temperature: 0,
        updated_at: Date.now(),
      };

      await set(ref(db, "fire_logs/" + nextDeviceId), newDevice);
      alert("✅ Device added successfully!");
      deviceFormModal.classList.add("hidden");
      deviceForm.reset();
    });
  </script>
</head>

<body class="bg-gray-100 min-h-screen p-6">
  <!-- 🔴 Alarm Overlay -->
  <div id="alarm-overlay" class="hidden fixed inset-0 border-8 border-red-600 pointer-events-none z-40"></div>
  <audio id="alarm-sound" src="https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg" preload="auto" loop></audio>

  <!-- Header -->
  <header class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
    <div>
      <h1 class="text-2xl md:text-3xl font-bold text-red-600">🔥 Fire Monitoring Dashboard</h1>
      <p class="text-gray-600 text-sm md:text-base">
        Monitor live and captured feeds from IoT fire detection sensors.
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

                    <a href="{{ route('fire_feed') }}" class="nav-link px-4 py-2 rounded-lg" data-page="fire_feed">🖼️
                        Image Feed</a>
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

                 <a href="{{ route('fire_feed')}}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100"  data-page="fire_feed">🖼️ Image Feed</a>
            @endif

            @if (session('role') === 'OSHO')
                <a href="{{ route('AdminOSHO') }}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100"
                    data-page="AdminOSHO">👤 New Admin Registration</a>
            @endif
        </div>
    </nav>

  <!-- 🔥 Fire Feed Grid -->
  <div class="flex justify-center p-6 bg-gray-100 min-h-screen">
    <div class="w-full max-w-20xl">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-3xl font-bold mb-6 text-red-600 text-center flex-1">🔥 Fire Sensor Live & Image Feed</h1>
        <button id="addDeviceBtn" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">➕ Add Device</button>
      </div>

      <!-- All Device Feeds -->
      <div id="fireGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-5 justify-items-center"></div>
    </div>
  </div>

  <!-- Add Device Modal -->
  <div id="deviceFormModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-6 rounded-2xl w-full max-w-md">
      <h3 class="text-lg font-bold text-red-600 mb-4">Add New Device</h3>
      <form id="deviceForm" class="space-y-4">
        <div>
          <label class="block font-medium">Location Name</label>
          <input type="text" id="locationName" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
          <label class="block font-medium">Image URL</label>
          <input type="text" id="imageUrl" class="w-full border rounded px-3 py-2" placeholder="http://your-iot-camera-ip/snapshot.jpg" required>
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" id="cancelBtn" class="px-4 py-2 rounded border hover:bg-gray-100">Cancel</button>
          <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Add Device</button>
        </div>
      </form>
    </div>
  </div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  // Kunin last part ng URL (halimbawa /Homepage -> Homepage)
  let currentPath = window.location.pathname.split("/").pop();
  if (!currentPath) currentPath = "Homepage";

  document.querySelectorAll(".nav-link").forEach(link => {
    if (link.dataset.page === currentPath) {
      link.classList.add("bg-blue-600", "text-white", "shadow");
      link.classList.remove("hover:bg-gray-200", "hover:bg-red-100");
    } else {
      link.classList.add("hover:bg-red-100");
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
