<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
 <!-- Firebase SDK -->
  <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-database-compat.js"></script>
    <style>
        #alarm-overlay {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                border-color: transparent;
            }

            50% {
                border-color: red;
            }
        }

        #minor-modal .modal-title {
            color: #d97706;
        }

        #minor-modal.critical .modal-title {
            color: #dc2626;
        }

        #minor-modal.critical .critical-ring {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .35);
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen p-4 sm:p-6">


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



    <div class="container mx-auto p-2 grid grid-cols-1 lg:grid-cols-3 gap-2">
        <!-- LEFT SIDE: Admins Table -->
        <div class="lg:col-span-4 bg-white shadow-lg rounded-2xl p-6 sm:p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl sm:text-2xl font-bold text-red-600">👥 Admins List</h2>

                <!-- just the update or add admin success-->
                @if (session('success'))
                    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Add Admin Button -->
                <button onclick="openRegisterModal()"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"> ➕ Add Admin
                </button>
            </div>


            <div class="overflow-x-auto">
                <table class="w-full border-collapse  rounded-lg text-sm sm:text-base">
                    <thead class="bg-red-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase">
                            Pnumber <br>
                            <span class="text-xs font-normal text-gray-500">Only 10 Users can have number</span>
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $id => $admin)
                            <tr>
                                <td class="px-6 py-4">{{ $admin['name'] ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $admin['email'] ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $admin['role'] ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $admin['pnumber'] ?? '-' }}</td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <!-- Edit Button -->
                                    <button
                                        onclick="openEditModal('{{ $id }}', '{{ $admin['name'] ?? '' }}', '{{ $admin['email'] ?? '' }}', '{{ $admin['role'] ?? '' }}','{{ $admin['pnumber'] ?? '' }}')"
                                        class="bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700">
                                        Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('admins.destroy', $id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="openDeleteModal('{{ $id }}')"
                                            class="bg-red-600 text-white px-3 py-1 rounded-lg hover:bg-red-700">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center p-4 text-gray-500">No admins found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>




        <!-- REGISTER form MODAL -->
<div id="registerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white p-6 rounded-2xl w-full max-w-md">
        <h2 class="text-xl sm:text-2xl font-bold text-center text-red-600 mb-6">🔥 Register New Admin</h2>

        <form id="registerForm" method="POST" action="{{ route('admins.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-gray-700 text-sm">Name</label>
                <input type="text" name="name" class="w-full border rounded-lg p-2" required>
            </div>

            <div>
                <label class="block text-gray-700 text-sm">Email</label>
                <input type="email" name="email" class="w-full border rounded-lg p-2" required>
            </div>

            <!-- ✅ Phone number input (will hide if 10 users already have one) coz it was limited only -->
            <div id="phoneField">
        <label class="block text-gray-700 text-sm">Phone Number</label>
        <div class="flex">
            <span class="inline-flex items-center px-3 bg-gray-200 border border-r-0 rounded-l-lg text-gray-600">+63</span>
            <input type="text" name="pnumber" id="pnumber"
                class="w-full border rounded-r-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="9123456789" maxlength="10" pattern="[0-9]{10}">
        </div>
        <small class="text-gray-500 text-xs">Enter 10-digit mobile number only (e.g. 9123456789)</small>
    </div>

            <div>
                <label class="password text-gray-700 text-sm">Password</label>
                <input type="password" name="password" class="w-full border rounded-lg p-2" required>
            </div>

            <div>
                <label class="block text-gray-700 text-sm">Role</label>
                <select name="role" class="w-full border rounded-lg p-2" required>
                    <option value="OSHO">OSHO (SUPER ADMIN)</option>
                    <option value="SECURITY">SECURITY</option>
                    <option value="FACULTY">FACULTY</option>
                </select>
            </div>

            <!-- Hidden Verification Code -->
            <div class="hidden">
                <label class="block text-gray-700 text-sm">Verification Code</label>
                <input type="text" name="verification_code" id="verification_code"
                    class="w-full border rounded-lg p-2" readonly>
            </div>

            <input type="hidden" name="confirm_verification_code" value="0">

            <div class="flex justify-end space-x-2 mt-4">
                <button type="button" onclick="closeRegisterModal()"
                    class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Register
                </button>
            </div>
        </form>
    </div>
</div>





 <!-- EDIT MODAL -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
  <div class="bg-white p-6 rounded-2xl w-full max-w-md">
    <h2 class="text-xl font-bold mb-4">✏️ Edit Admin</h2>
    <form id="editForm" method="POST">
      @csrf
      @method('PUT')
      <input type="hidden" name="id" id="editId">

      <div class="mb-3">
        <label class="block text-sm">Name</label>
        <input type="text" id="editName" name="name" class="w-full border p-2 rounded-lg">
      </div>

      <div class="mb-3">
        <label class="block text-sm">Email</label>
        <input type="email" id="editEmail" name="email" class="w-full border p-2 rounded-lg">
      </div>

      <!-- ✅ PHONE NUMBER (auto-hide if empty) -->
        <div class="mb-3">
        <label class="block text-sm">Phone Number</label>
        <input type="text" id="editpnumber" name="pnumber" class="w-full border p-2 rounded-lg" placeholder="+639123456789">
      </div>


      <div class="mb-3">
        <label class="block text-sm">Role</label>
        <select id="editRole" name="role" class="w-full border p-2 rounded-lg">
          <option value="OSHO">OSHO</option>
          <option value="SECURITY">SECURITY</option>
          <option value="FACULTY">FACULTY</option>
          <option value="USER">USER</option>
        </select>
      </div>

      <div class="flex justify-end space-x-2">
        <button type="button" onclick="closeEditModal()" class="px-3 py-1 bg-gray-300 rounded-lg">Cancel</button>
        <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
      </div>
    </form>
  </div>
</div>


        <!-- DELETE CONFIRM MODAL -->
        <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
            <div class="bg-white p-6 rounded-2xl w-full max-w-sm shadow-lg">
                <h2 class="text-xl font-bold text-red-600 mb-4">⚠️ Confirm Delete</h2>
                <p class="text-gray-700 mb-6">
                    This account will be permanently deleted. Are you sure you want to proceed?
                </p>

                <div class="flex justify-end space-x-3">
                    <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>

                    <form id="deleteForm" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Proceed
                        </button>
                    </form>
                </div>
            </div>
        </div>


        <script>
            // Generate a 6-character alphanumeric code
            function generateVerificationCode(length = 6) {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                let code = '';
                for (let i = 0; i < length; i++) {
                    code += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return code;
            }

            // Open modal and set verification code
            function openRegisterModal() {
                const code = generateVerificationCode();
                document.getElementById('verification_code').value = code;
                document.querySelector('input[name="confirm_verification_code"]').value = code;
                document.getElementById('registerModal').classList.remove('hidden');
                document.getElementById('registerModal').classList.add('flex');
            }


            function closeRegisterModal() {
                document.getElementById('registerModal').classList.remove('flex');
                document.getElementById('registerModal').classList.add('hidden');
            }

            // Example: call this function when clicking "Register New Admin" button
            // <button onclick="openRegisterModal()">Register New Admin</button>
        </script>

        <!-- Scripts for edit  -->
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

  const app = firebase.initializeApp(firebaseConfig);
  const db = firebase.database();

  // Open modal and populate fields
  function openEditModal(id) {
    firebase.database().ref("admins/" + id).once("value").then((snapshot) => {
      const data = snapshot.val();
      if (!data) return alert("❌ Admin not found!");

      document.getElementById("editId").value = id;
      document.getElementById("editName").value = data.name || "";
      document.getElementById("editEmail").value = data.email || "";
      document.getElementById("editRole").value = data.role || "USER";

      // Phone number: always show, add +63 if needed
      let number = data.pnumber || "";
      number = number.trim();
      if (number && !number.startsWith("+63")) {
        number = number.replace(/^0+/, "");
        number = "+63" + number;
      }
      document.getElementById("editpnumber").value = number;

      // Show modal
      document.getElementById("editModal").classList.remove("hidden");
      document.getElementById("editModal").classList.add("flex");
    });
  }

  function closeEditModal() {
    document.getElementById("editModal").classList.remove("flex");
    document.getElementById("editModal").classList.add("hidden");
  }

  // Save updates
  document.getElementById("editForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const id = document.getElementById("editId").value;
    const name = document.getElementById("editName").value;
    const email = document.getElementById("editEmail").value;
    const role = document.getElementById("editRole").value;
    let pnumber = document.getElementById("editpnumber").value.trim();

    if (pnumber && !pnumber.startsWith("+63")) {
      pnumber = pnumber.replace(/^0+/, "");
      pnumber = "+63" + pnumber;
    }

    firebase.database().ref("admins/" + id).update({
      name,
      email,
      role,
      pnumber
    }).then(() => {
      alert("✅ Admin updated successfully!");
      closeEditModal();
      location.reload();
    }).catch(err => {
      console.error(err);
      alert("❌ Failed to update admin!");
    });
  });
</script>






        <!-- Scripts for the alarm -->
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
            const logsRef = db.ref('fire_logs');

            const alarmOverlay = document.getElementById('alarm-overlay');
            const alarmSound = document.getElementById('alarm-sound');

            // Listen for real-time updates
            logsRef.on('value', snapshot => {
                const data = snapshot.val();
                let hasActiveAlert = false;

                if (data) {
                    Object.values(data).forEach(log => {
                        if (log.status && log.status.toLowerCase() !== 'resolved') {
                            hasActiveAlert = true;
                        }
                    });
                }

                if (hasActiveAlert) {
                    alarmOverlay.classList.remove('hidden');
                    alarmSound.play().catch(e => console.log('Alarm play error:', e));
                } else {
                    alarmOverlay.classList.add('hidden');
                    alarmSound.pause();
                    alarmSound.currentTime = 0;
                }
            });
        </script>
        <!-- detlete modal -->
        <script>
            function openDeleteModal(adminId) {
                const form = document.getElementById('deleteForm');
                form.action = `/admins/${adminId}`; // Laravel route
                document.getElementById('deleteModal').classList.remove('hidden');
                document.getElementById('deleteModal').classList.add('flex');
            }

            function closeDeleteModal() {
                document.getElementById('deleteModal').classList.add('hidden');
                document.getElementById('deleteModal').classList.remove('flex');
            }
        </script>


        <script>
            // Highlight current page
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




<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
    import { getDatabase, ref, get, child, push, set } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-database.js";

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

    const app = initializeApp(firebaseConfig);
    const db = getDatabase(app);

    async function checkPhoneLimit() {
        const dbRef = ref(db);
        const snapshot = await get(child(dbRef, 'admins'));
        if (snapshot.exists()) {
            const admins = snapshot.val();
            let count = 0;
            Object.values(admins).forEach(admin => {
                if (admin.pnumber && admin.pnumber.trim() !== '') count++;
            });
            if (count >= 10) {
                document.getElementById('phoneField').style.display = 'none';
                console.log("📱 Hidden phone input (10 users already have a number)");
            }
        }
    }

    checkPhoneLimit();

    // ✅ Add +63 automatically before saving
    const form = document.getElementById('registerForm');
    form.addEventListener('submit', (e) => {
        const phoneInput = document.getElementById('pnumber');
        if (phoneInput && phoneInput.value.trim() !== '') {
            // Prepend +63 if not already added
            if (!phoneInput.value.startsWith('+63')) {
                phoneInput.value = '+63' + phoneInput.value.trim();
            }
        }
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