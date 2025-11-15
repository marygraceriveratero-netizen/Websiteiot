<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

  <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-sm">
    <h2 class="text-2xl font-semibold text-center mb-4">🔥Forgot Password</h2>

    <input id="email" type="email" placeholder="Enter your email"
      class="w-full p-3 mb-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400" />

    <button id="sendCodeBtn"
      class="w-full py-2 bg-blue-600 text-white font-medium rounded-lg">Send Reset Code</button>

    <p id="msg" class="text-center text-sm mt-3 text-gray-600"></p>
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

    document.getElementById("sendCodeBtn").addEventListener("click", async () => {
      const email = document.getElementById("email").value.trim();
      const msg = document.getElementById("msg");

      if (!email) {
        msg.textContent = "Please enter your email.";
        return;
      }

      msg.textContent = "Checking email...";

      try {
        const snapshot = await db.ref("admins").once("value");
        const admins = snapshot.val();
        let foundKey = null;

        for (let key in admins) {
          if (admins[key].email === email) {
            foundKey = key;
            break;
          }
        }

        if (!foundKey) {
          msg.textContent = "No admin found with this email.";
          return;
        }

        // Create random reset code
        const resetCode = Math.random().toString(36).substring(2, 8).toUpperCase();

        await db.ref("admins/" + foundKey).update({ reset_code: resetCode });

        // Send email through Laravel
        const response = await fetch("/send-reset-code", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name=\"csrf-token\"]').getAttribute("content"),
          },
          body: JSON.stringify({ email, resetCode }),
        });

        if (response.ok) {
          msg.textContent = "✅ Reset code sent to your email.";
        } else {
          msg.textContent = "❌ Failed to send email.";
        }
      } catch (err) {
        msg.textContent = "Error: " + err.message;
      }
    });
  </script>
</body>
</html>