<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>

  <!-- Firebase -->
  <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-auth-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- CSRF Token for Laravel -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

  <div class="max-w-5xl w-full flex flex-col md:flex-row items-center justify-between gap-12">

    <!-- LEFT SIDE -->
    <header class="flex-1">
      <h1 class="text-4xl font-bold text-red-600 flex items-center gap-2">
        🔥 Fire Monitoring Dashboard
      </h1>
      <p class="text-gray-600 mt-2 text-base max-w-md">
        Monitor real-time data, view alerts, and manage fire safety operations from one clean interface.
      </p>
    </header>

    <!-- LOGIN CARD -->
    <div class="bg-white/90 backdrop-blur-md p-8 rounded-2xl shadow-xl w-full max-w-sm">
      <h2 class="text-2xl font-semibold text-center mb-6">Admin Login</h2>

      <!-- EMAIL -->
      <input type="email" id="email" placeholder="Enter your email"
        class="w-full p-3 mb-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />

      <!-- PASSWORD -->
      <input type="password" id="password" placeholder="Enter your password"
        class="w-full p-3 mb-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />

      <!-- LOGIN BUTTON -->
      <button id="loginBtn"
        class="w-full py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-medium rounded-lg shadow-lg">
        Login
      </button>


      <!-- FORGIT BTN BUTTON -->
     <p class="text-center text-sm mt-3">
     <a href="{{ url('/forgot-password') }}" class="text-blue-500 hover:underline">Forgot Password?</a>
     </p>


      <!-- ERROR -->
      <p id="errorMsg" class="text-red-500 text-center mt-3 text-sm"></p>
    </div>
  </div>

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

    // Login
    document.getElementById('loginBtn').addEventListener('click', async () => {
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value.trim();
      const errorMsg = document.getElementById('errorMsg');
      errorMsg.textContent = '';

      if (!email || !password) {
        errorMsg.textContent = 'Please enter email and password';
        return;
      }

      try {
        const snapshot = await db.ref('admins').once('value');
        const admins = snapshot.val();
        let found = false;

        for (let key in admins) {
          const admin = admins[key];
          if (admin.email === email) {
            found = true;

            // Check password
            if (admin.password !== password) {
              errorMsg.textContent = 'Incorrect password or the email are not register';
              return;
            }

            // Check verification codes
            if (admin.verification_code !== admin.confirm_verification_code) {
              errorMsg.textContent = 'Verification code mismatch';
              return;
            }

            // Get role automatically from Firebase
            const role = admin.role;

            // Save to localStorage
            localStorage.setItem('firebase_user', key);
            localStorage.setItem('role', role);
            localStorage.setItem('name', admin.name);

            // Send to Laravel session
            await fetch('/set-session', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                uid: key,
                role: role,
                name: admin.name,
                email: email
              })
            });

            // Redirect
            window.location.href = '/Homepage';
            return;
          }
        }

        if (!found) {
          errorMsg.textContent = 'No admin account found with this email';
        }

      } catch (err) {
        console.error(err);
        errorMsg.textContent = 'Error: ' + err.message;
      }
    });
  </script>
</body>
</html>