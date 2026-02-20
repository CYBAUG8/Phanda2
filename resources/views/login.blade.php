<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phanda Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6 text-gray-800">Login</h1>

        @if(session('error'))
            <p class="text-red-500 text-sm mb-4 text-center">{{ session('error') }}</p>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
            @csrf

            <div class="flex flex-col">
                <label class="mb-1 text-gray-700 font-medium">Email</label>
                <input type="email" name="email" required
                       class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>

            <div class="flex flex-col">
                <label class="mb-1 text-gray-700 font-medium">Password</label>
                <input type="password" name="password" required
                       class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>

            <button type="submit"
                    class="w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition duration-200 font-semibold">
                Login
            </button>
        </form>

        <p class="text-center text-gray-500 text-sm mt-4">
            Don't have an account? <a href="#" class="text-purple-600 hover:underline">Sign up</a>
        </p>
    </div>

</body>
</html>
