<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6 text-red-500">🔥 Verify Your Account</h2>

        @if(session('error'))
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-center">
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4 text-center">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.submit') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your email"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                    required>
            </div>

            <div>
                <label for="verification_code" class="block text-gray-700 font-medium mb-1">Verification Code</label>
                <input type="text" name="verification_code" id="verification_code" placeholder="6-digit code"
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
                    maxlength="6" required>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-200 font-semibold">
                Verify Account
            </button>
        </form>


    </div>

</body>
</html>