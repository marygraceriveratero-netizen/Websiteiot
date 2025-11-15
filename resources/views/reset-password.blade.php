<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-sm">
    <h2 class="text-2xl font-semibold text-center mb-4">🔥Reset Password</h2>
     <h1 class="text-1xl font-semibold text-left mb-1">Enter your email</h1>
    <input id="email" type="email" placeholder="Enter your email"
      class="w-full p-3 mb-3 border border-gray-300 rounded-lg">
    <h1 class="text-1xl font-semibold text-left mb-1">Enter reset code</h1>
    <input id="code" type="text" placeholder="Enter reset code"
      class="w-full p-3 mb-3 border border-gray-300 rounded-lg">
    <h1 class="text-1xl font-semibold text-left mb-1">Enter new password</h1>
    <input id="newPass" type="password" placeholder="Enter new password"
      class="w-full p-3 mb-1 border border-gray-300 rounded-lg">

    <!-- Password strength message -->
    <p id="strengthMsg" class="text-sm text-center mb-4"></p>

    <button id="resetBtn"
      class="w-full py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">Reset Password</button>

    <p id="msg" class="text-center text-sm mt-3 text-gray-600"></p>
  </div>

  <script>
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

    const newPassInput = document.getElementById("newPass");
    const strengthMsg = document.getElementById("strengthMsg");

    // ✅ Password strength checker
    newPassInput.addEventListener("input", () => {
      const pass = newPassInput.value;
      const strength = checkPasswordStrength(pass);
      if (!pass) {
        strengthMsg.textContent = "";
        return;
      }

      switch (strength) {
        case "Weak":
          strengthMsg.textContent = "Password Strength: Weak 🔴";
          strengthMsg.className = "text-sm text-center mb-4 text-red-500";
          break;
        case "Medium":
          strengthMsg.textContent = "Password Strength: Medium 🟠";
          strengthMsg.className = "text-sm text-center mb-4 text-yellow-500";
          break;
        case "Strong":
          strengthMsg.textContent = "Password Strength: Strong 🟢";
          strengthMsg.className = "text-sm text-center mb-4 text-green-600";
          break;
      }
    });

    // Function that calculates strength
    function checkPasswordStrength(password) {
      let score = 0;
      if (password.length >= 8) score++;
      if (/[A-Z]/.test(password)) score++;
      if (/[0-9]/.test(password)) score++;
      if (/[^A-Za-z0-9]/.test(password)) score++;

      if (score <= 1) return "Weak";
      if (score === 2 || score === 3) return "Medium";
      return "Strong";
    }

    // ✅ Reset password logic
    document.getElementById("resetBtn").addEventListener("click", async () => {
      const email = document.getElementById("email").value.trim();
      const code = document.getElementById("code").value.trim();
      const newPass = newPassInput.value.trim();
      const msg = document.getElementById("msg");

      if (!email || !code || !newPass) {
        msg.textContent = "Please fill out all fields.";
        msg.className = "text-center text-sm mt-3 text-red-500";
        return;
      }

      if (checkPasswordStrength(newPass) === "Weak") {
        msg.textContent = "⚠️ Please choose a stronger password.";
        msg.className = "text-center text-sm mt-3 text-yellow-500";
        return;
      }

      const snapshot = await db.ref("admins").once("value");
      const admins = snapshot.val();
      let foundKey = null;

      for (let key in admins) {
        if (admins[key].email === email && admins[key].reset_code === code) {
          foundKey = key;
          break;
        }
      }

      if (!foundKey) {
        msg.textContent = "Invalid email or reset code.";
        msg.className = "text-center text-sm mt-3 text-red-500";
        return;
      }

      await db.ref("admins/" + foundKey).update({
        password: newPass,
        reset_code: null
      });

      msg.textContent = "✅ Password updated successfully!";
      msg.className = "text-center text-sm mt-3 text-green-600";
      setTimeout(() => (window.location.href = "/"), 2000);
    });
  </script>
</body>
</html>