<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Fire Monitoring Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-database-compat.js"></script>

<style>
  .toast {
    transition: opacity 0.5s ease, transform 0.5s ease;
  }
  .toast.show {
    display: block !important;
    opacity: 1;
    transform: translateY(0);
  }
  .spinner {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid #ef4444;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
  }
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
</style>

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
     <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600">
      🚪 Logout
    </button>
  </form>
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
      <a href="{{ route('fire_feed')}}" class="nav-link px-4 py-2 rounded-lg"  data-page="fire_feed">🖼️ Image Feed</a>
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
         <a href="{{ route('fire_feed')}}" class="nav-link px-4 py-2 rounded-lg"  data-page="fire_feed">🖼️ Image Feed</a>
    @endif

    @if(session('role') === 'OSHO')
      <a href="{{ route('AdminOSHO') }}" class="nav-link block px-4 py-2 rounded-lg hover:bg-red-100" data-page="AdminOSHO">👤 New Admin Registration</a>
    @endif
  </div>
</nav>

<!-- Dashboard -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
  <div class="bg-white p-6 rounded-2xl shadow-md mb-4 border-l-4 border-red-600">
    <h2 class="text-xl font-semibold mb-4">🔥 Fire Status</h2>
    <p id="fire-status" class="text-2xl font-bold text-green-600">✅ No Fire Detected</p>
    <div class="flex items-center gap-3 mt-2">
      <div id="fire-light" class="w-6 h-6 rounded-full bg-gray-400"></div>
      <span class="text-gray-700 font-medium">Fire Indicator</span>
    </div>
  </div>

  <div class="bg-white p-6 rounded-2xl shadow-md mb-4 border-l-4 border-red-600" id="smoke-card">
    <h2 class="text-xl font-semibold mb-2">⚠️ Smoke Alerts</h2>
    <p class="text-sm text-gray-500 mb-2">Smoke detected but not confirmed as fire.</p>
    <p id="smoke-alerts" class="text-3xl font-extrabold text-gray-800">0</p>
  </div>

  <div class="bg-white p-6 rounded-2xl shadow-md mb-4 border-l-4 border-red-600">
    <h2 class="text-xl font-semibold mb-4">📅 Last Fire Incident</h2>
    <p id="last-fire" class="text-2xl font-bold text-gray-700">None</p>
  </div>
</div>

<!-- live data graph Table -->
    <div class="bg-gray-50 p-6 rounded-2xl shadow-inner mb-6">
  <main class="max-w-7xl mx-auto">
    <section class="bg-white p-8 shadow-2xl rounded-2xl border border-gray-200">
      <header class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-red-600 flex items-center gap-2">
          🔥 IoT Chart Sensor Dashboard
        </h2>
        <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
          Real-Time Monitoring
        </span>
      </header>

      <div class="relative bg-gradient-to-b from-gray-50 to-white p-4 rounded-xl shadow-inner border border-gray-100">
        <canvas id="sensorChart" class="w-full h-[400px]"></canvas>
        <div id="loadingSpinner"
             class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 spinner"></div>
      </div>

      <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div id="alerts"
             class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-lg font-medium shadow-sm"></div>
        <p id="lastUpdate" class="text-gray-600 text-sm italic">
          Last updated: <span id="updateTime">N/A</span>
        </p>
      </div>
    </section>
  </main>
  <div id="toast"
       class="toast bg-red-600 text-white px-5 py-3 rounded-lg shadow-lg fixed top-5 right-5 hidden z-50">
  </div>
</div>

<!-- Event Log Table -->
  <div class="bg-white p-6 rounded-2xl shadow-md mb-4 border-l-4 border-red-600">
    <h2 class="text-xl font-semibold text-red-900 mb-4">Current Alerts</h2>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200" id="alerts-table">
        <thead class="bg-red-600 text-white">
      <tr>
        <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Time</th>
        <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Temp (°C)</th>
        <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Smoke (ppm)</th>
        <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Flame</th>
        <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Event</th>
        <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Fire Type</th>
        <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Extinguisher</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100 bg-white" id="event-log">
  </table>
</div>

<!-- Alarm Sound -->
<audio id="alarm-sound" src="https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg" preload="auto" loop></audio>
<!-- Warning Sound -->
<audio id="warning-sound" src="https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg" preload="auto" loop></audio>


<!-- Minor Event Modal -->
<div id="minor-modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
  <div class="bg-white rounded-2xl shadow-lg p-6 max-w-2xl w-full critical-ring">
    <div class="flex items-center gap-3 mb-3">
      <span class="text-2xl">⚠️</span>
      <h2 class="text-xl font-bold modal-title">Suspicious Activity Detected</h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
      <div>
        <p class="text-sm text-gray-500">Device ID</p>
        <p id="modal-device" class="font-semibold text-gray-800">-</p>
      </div>
      <div>
        <p class="text-sm text-gray-500">Location</p>
        <p id="modal-location" class="font-semibold text-gray-800">-</p>
      </div>
    </div>

    <p id="minor-message" class="mb-4 text-gray-700 font-medium">Reading details...</p>

    
      <img id="minor-camera" class="w-full h-96 bg-transparent rounded-xl overflow-hidden mb-6 object-fill border object-center" allow="autoplay" alt="IoT Camera"/>
    

    <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
      <button id="minor-false-btn" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg shadow w-full sm:w-auto">False Alarm</button>
      <button id="minor-confirm-btn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow w-full sm:w-auto">Confirm Fire</button>
    </div>
  </div>
</div>

<!-- ===== Firebase Logic ===== -->
<script type="module">
import { initializeApp } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js";
import { getDatabase, ref, onValue, update, push, serverTimestamp } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-database.js";

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


const CAMERA_FALLBACK = "http://esp32cam.local/stream";
const app = initializeApp(firebaseConfig);
const db  = getDatabase(app);




// DOM elements
const fireStatusEl  = document.getElementById("fire-status");
const fireLightEl   = document.getElementById("fire-light");
const smokeAlertsEl = document.getElementById("smoke-alerts");
const lastFireEl    = document.getElementById("last-fire");
const eventLogEl    = document.getElementById("event-log");
const alarmSound    = document.getElementById("alarm-sound");
const alarmOverlay  = document.getElementById("alarm-overlay");
const warningSound  = document.getElementById("warning-sound");

const modal         = document.getElementById("minor-modal");
const modalMsg      = document.getElementById("minor-message");
const modalCam      = document.getElementById("minor-camera");
const modalDevice   = document.getElementById("modal-device");
const modalLocation = document.getElementById("modal-location");
const btnFalse      = document.getElementById("minor-false-btn");
const btnConfirm    = document.getElementById("minor-confirm-btn");

let lastSnapshotByDevice = {}; // tracks last row per device
let forceFireUntil = 0;
let blinkInterval = null;



// --- Helpers ---
function playAlarm(){ alarmSound.play().catch(()=>{}); alarmOverlay.classList.remove("hidden"); }
function stopAlarm(){ alarmSound.pause(); alarmSound.currentTime=0; alarmOverlay.classList.add("hidden"); }
function startBlink(){
  if(blinkInterval) return;
  blinkInterval = setInterval(()=>{
    fireLightEl.classList.toggle("bg-red-600");
    fireLightEl.classList.toggle("bg-gray-400");
  },500);
}
function stopBlink(){ clearInterval(blinkInterval); blinkInterval=null; fireLightEl.classList.remove("bg-red-600"); fireLightEl.classList.add("bg-gray-400"); }
function playWarning(){ warningSound.play().catch(()=>{}); }
function stopWarning(){ warningSound.pause(); warningSound.currentTime=0; }
function formatDate(ts){ return new Date(ts).toLocaleString(); }



// --- Table ---
function appendLogRow({device_id,time,temperature,smoke,flame,event,fire_type,extinguisher,location_name}){
  // only append if not exists
  if(eventLogEl.querySelector(`tr[data-device="${device_id}"]`)) return;
  const tr=document.createElement("tr");
  tr.dataset.device = device_id;
  tr.dataset.location_name = location_name;      // pag click mo ung btn conform or false alert dito nila kinukuha ung data and diyan mo ung count ng cell
  tr.innerHTML=`
    <td>${formatDate(time)}</td>
    <td>${temperature}</td>
    <td>${smoke}</td>
    <td>${flame?"🔥 Yes":"❌ No"}</td>
    <td>${event}</td>
    <td>${fire_type||"-"}</td>
    <td>${extinguisher||"-"}</td>`;

  eventLogEl.prepend(tr);
}

function updateRow(device_id,status,fireType="-",extinguisher="-"){
  const row = eventLogEl.querySelector(`tr[data-device="${device_id}"]`);
  if(!row) return;
  row.cells[4].innerText = status;
  row.cells[5].innerText = fireType;
  row.cells[6].innerText = extinguisher;
  row.cells[5].innerText = fireType;
  row.cells[6].innerText = extinguisher;
  row.classList.remove("bg-red-200","bg-yellow-200");
  if(status==="Confirm Fire") row.classList.add("bg-red-200");
  if(status==="False Alarm") row.classList.add("bg-yellow-200");
}




// --- Modal ---
function showMinorModal({device_id,location_name,temperature,smoke,flame,camera_url}){
  modal.classList.remove("critical");
  if(temperature>50 || smoke>300) modal.classList.add("critical");
  modalMsg.textContent=`Temp: ${temperature}°C | Smoke: ${smoke} ppm | Flame: ${flame?"Yes":"No"}`;
  modalDevice.textContent=device_id||"-";
  modalLocation.textContent=location_name||"-";
  modalCam.src=camera_url||CAMERA_FALLBACK;
  modal.dataset.lastDevice = JSON.stringify({device_id});
  modal.classList.remove("hidden");
}



async function sendEmail(status, fireType, extinguisher, location_name, temperature, smoke, now) {
  try {
    const response = await fetch("/fire-alert/send", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
      },
      body: JSON.stringify({
        status: status,
        fire_type: fireType,
        temperature: temperature,
        smoke: smoke,
        location_name: location_name,
        extinguisher: extinguisher,
        time: new Date(now).toLocaleString(),
      })
    });

    const result = await response.json();
    if (result.success) {
      console.log("✅ Email sent:", status);
    } else {
      console.error("❌ Email failed:", result);
    }
  } catch (err) {
    console.error("❌ Email error:", err);
  }
}


// --- Confirm / False Alarm ---
btnConfirm.onclick = async ()=>{
  const last = JSON.parse(modal.dataset.lastDevice||"{}");
  if(!last.device_id) return modal.classList.add("hidden");

  const row = eventLogEl.querySelector(`tr[data-device="${last.device_id}"]`);
  const fireType = row?.cells[5]?.innerText || "-";
  const extinguisher = row?.cells[6]?.innerText || "-";
  const location_name = row.dataset.location_name || "-";
  const temperature = row?.cells[1]?.innerText || "-";
  const smoke = row?.cells[2]?.innerText || "-";

  const now = Date.now();
  updateRow(last.device_id,"Confirm Fire", fireType, extinguisher);

  await update(ref(db,`fire_logs/${last.device_id}`),{
    fire_detected:1,
    created_at: new Date().toISOString(), // ✅ update created_at
    updated_at:now
  });

// 🔔 Send Email dito
  sendEmail("🔥 Confirm Fire", fireType, extinguisher, location_name, temperature, smoke, now);


  fireStatusEl.textContent="🔥 Fire Ongoing!";
  fireStatusEl.classList.add("text-red-600"); fireStatusEl.classList.remove("text-green-600");
  startBlink(); playAlarm(); lastFireEl.textContent=formatDate(now);
  forceFireUntil=now+60000;

  setTimeout(()=>{
    if(Date.now()>=forceFireUntil){
      fireStatusEl.textContent="✅ No Fire Detected";
      fireStatusEl.classList.add("text-green-600"); fireStatusEl.classList.remove("text-red-600","text-yellow-600");
      stopBlink(); stopAlarm();
      updateRow(last.device_id,"Confirmed fire", fireType, extinguisher);
    }
  },60000);

  modal.classList.add("hidden");
};

btnFalse.onclick = async ()=>{
  const last = JSON.parse(modal.dataset.lastDevice||"{}");
  if(!last.device_id) return modal.classList.add("hidden");

  const row = eventLogEl.querySelector(`tr[data-device="${last.device_id}"]`);
 const fireType = row?.cells[5]?.innerText || "-";
  const extinguisher = row?.cells[6]?.innerText || "-";
  const location_name = row.dataset.location_name || "-";
  const temperature = row?.cells[1]?.innerText || "-";
  const smoke = row?.cells[2]?.innerText || "-";

  const now = Date.now();
  updateRow(last.device_id,"False Alarm", fireType, extinguisher);

  await update(ref(db,`fire_logs/${last.device_id}`),{
    fire_detected:2,
    status:"Resolved",
    created_at: new Date().toISOString(), // ✅ update created_at
    updated_at:now
  });

  // 🔔 Send Email dito
  sendEmail("🚨 False Alarm", fireType, extinguisher, location_name, temperature, smoke,now);

    forceFireUntil=0;
  fireStatusEl.textContent="✅ No Fire Detected";
  fireStatusEl.classList.add("text-green-600"); fireStatusEl.classList.remove("text-red-600","text-yellow-600");
  stopBlink(); stopAlarm();
  modal.classList.add("hidden");
};





// --- Firebase Listener ---
onValue(ref(db,"fire_logs"),snap=>{
  const sensors = snap.val(); if(!sensors) return;
  let anyFire=false,smokeCount=0,hasWarning=false;

  Object.keys(sensors).forEach(device_id=>{
    const s = sensors[device_id] || {};
    const t = Number(s.temperature||0);
    const sm = Number(s.smoke||0);
    const fl = !!s.flame;
    const loc = s.location_name||"-";
    const fd = Number(s.fire_detected||0);
    const cam = s.camera_url||"";

    if(fd===0) hasWarning=true;
    if(sm>0 && fd===0) smokeCount++;

    // append log once
    appendLogRow({device_id,time:Date.now(),temperature:t,smoke:sm,flame:fl,event:fd===0?"Under Review":(fd===1?"Fire":"False Alarm"),fire_type:s.fire_type,extinguisher:s.extinguisher, location_name: loc});

    // show modal only Under Review
    if(fd===0 && (!lastSnapshotByDevice[device_id] || lastSnapshotByDevice[device_id]!=="under_review")){
      showMinorModal({device_id,location_name:loc,temperature:t,smoke:sm,flame:fl,camera_url:cam});
      lastSnapshotByDevice[device_id]="under_review";
      playAlarm();
    }

    if((t>50 || sm>300 || fd===1) && s.status!=="Resolved") anyFire=true;
  });

  if(Date.now()<forceFireUntil) anyFire=true;

  // UI
  if(anyFire){
    fireStatusEl.textContent="🔥 Fire Ongoing!";
    fireStatusEl.classList.add("text-red-600"); fireStatusEl.classList.remove("text-green-600","text-yellow-600");
    startBlink(); playAlarm(); stopWarning();
  }else if(hasWarning){
    fireStatusEl.textContent="⚠️ Warning: Possible Fire Detected";
    fireStatusEl.classList.add("text-yellow-600"); fireStatusEl.classList.remove("text-green-600","text-red-600");
    stopBlink(); stopAlarm(); playWarning();
  }else{
    fireStatusEl.textContent="✅ No Fire Detected";
    fireStatusEl.classList.add("text-green-600"); fireStatusEl.classList.remove("text-red-600","text-yellow-600");
    stopBlink(); stopAlarm(); stopWarning();
  }

  smokeAlertsEl.textContent=smokeCount;
});
</script>

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

};

  // Initialize Firebase
  firebase.initializeApp(firebaseConfig);
    const db = firebase.database();
    const sensorRef = db.ref('fire_logs');

    // ✅ Chart setup
    const ctx = document.getElementById('sensorChart').getContext('2d');
    const sensorChart = new Chart(ctx, {
      type: 'line',
      amination: true,
      data: {
        labels: [],
        datasets: [
          {
            label: 'Temperature (°C)',
            data: [],
            borderColor: '#ef4444',
            backgroundColor: '#ef4444',
            fill: false,
            tension: 0.3,
            pointRadius: 5,
            pointHoverRadius: 8
          },
          {
            label: 'Smoke',
            data: [],
            borderColor: '#3b82f6',
            backgroundColor: '#3b82f6',
            fill: false,
            tension: 0.3,
            pointRadius: 5,
            pointHoverRadius: 8
          },
          {
            label: 'Air Quality',
            data: [],
            borderColor: '#22c55e',
            backgroundColor: '#22c55e',
            fill: false,
            tension: 0.3,
            pointRadius: 5,
            pointHoverRadius: 8
          },
        ]
      },
      options: {
        responsive: true,
        animation: false,
        plugins: {
          legend: { labels: { font: { size: 14 } } },
          tooltip: {
            callbacks: {
              label: function(context) {
                const log = context.raw;
                if (log.device_id) {
                  return `${log.device_id}: ${context.dataset.label} = ${log.y}`;
                }
                return `${context.dataset.label}: ${log.y}`;
              }
            }
          }
        },
        scales: {
          x: {
            type: 'time',
            time: {
              unit: 'day',
              tooltipFormat: 'yyyy-MM-dd',
              displayFormats: { day: 'yyyy-MM-dd' }
            },
            title: { display: true, text: 'Date' }
          },
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Value' }
          }
        }
      }
    });

    // ✅ Real-time data from all devices combined per type
    sensorRef.on('value', snapshot => {
      const data = snapshot.val();
      if (!data) return;

      const allLogs = Object.values(data)
        .filter(log => log.created_at)
        .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

      // Prepare data arrays
      const tempData = [];
      const smokeData = [];
      const aqiData = [];
      const timeLabels = [];

      allLogs.forEach(log => {
        const time = new Date(log.created_at);
        timeLabels.push(time);
        tempData.push({ x: time, y: Number(log.temperature) || 0, device_id: log.device_id });
        smokeData.push({ x: time, y: Number(log.smoke) || 0, device_id: log.device_id });
        aqiData.push({ x: time, y: Number(log.air_quality) || 0, device_id: log.device_id });
      });

      // ✅ Update chart
      sensorChart.data.labels = timeLabels;
      sensorChart.data.datasets[0].data = tempData;
      sensorChart.data.datasets[1].data = smokeData;
      sensorChart.data.datasets[2].data = aqiData;
      sensorChart.update();

      document.getElementById('updateTime').textContent = new Date().toLocaleString();
      document.getElementById('loadingSpinner').style.display = 'none';
    });
</script>


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

<script type="text/javascript"
        src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>
<script>
  (function(){
    emailjs.init("YOUR_PUBLIC_KEY"); // galing sa EmailJS dashboard
  })();
</script>



</body>
</html>