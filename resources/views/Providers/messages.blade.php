@extends('providers.layout')

@section('content')

<div class="h-[calc(100vh-120px)] flex bg-white rounded-lg shadow overflow-hidden">

    <!-- LEFT: Conversations List -->
    <div class="w-1/3 border-r bg-gray-50 flex flex-col">

        <!-- Header -->
        <div class="p-4 border-b bg-white">
            <h2 class="text-lg font-semibold text-orange-500">Messages</h2>
        </div>

        <!-- Search -->
        <div class="p-3 border-b">
            <input type="text"
                   placeholder="Search conversations..."
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
        </div>

        <!-- Conversation List -->
        <div class="flex-1 overflow-y-auto">

            <!-- Conversation Item -->
            <div class="p-4 border-b hover:bg-gray-100 cursor-pointer bg-orange-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold">
                        JM
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between">
                            <h3 class="font-medium text-gray-900">John Mokoena</h3>
                            <span class="text-xs text-gray-500">2:45 PM</span>
                        </div>
                        <p class="text-sm text-gray-600 truncate">
                            Hi, are you available tomorrow?
                        </p>
                    </div>
                </div>
            </div>

            <!-- Another Conversation -->
            <div class="p-4 border-b hover:bg-gray-100 cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-400 text-white flex items-center justify-center font-bold">
                        TS
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between">
                            <h3 class="font-medium text-gray-900">Thandi Sithole</h3>
                            <span class="text-xs text-gray-500">Yesterday</span>
                        </div>
                        <p class="text-sm text-gray-600 truncate">
                            Thank you for the great service!
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- RIGHT: Chat Area -->
    <div class="flex-1 flex flex-col">

        <!-- Chat Header -->
        <div class="p-4 border-b bg-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-orange-500 text-white flex items-center justify-center font-bold">
                    JM
                </div>
                <div>
                    <h3 class="font-medium text-gray-900">John Mokoena</h3>
                    <p class="text-xs text-green-500">Online</p>
                </div>
            </div>
        </div>

        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50">

            <!-- Incoming Message -->
            <div class="flex items-start gap-2">
                <div class="bg-white px-4 py-2 rounded-lg shadow max-w-xs">
                    <p class="text-sm">Hi, are you available tomorrow at 10am?</p>
                    <span class="text-xs text-gray-400 block mt-1">2:40 PM</span>
                </div>
            </div>

            <!-- Outgoing Message -->
            <div class="flex justify-end">
                <div class="bg-orange-500 text-white px-4 py-2 rounded-lg shadow max-w-xs">
                    <p class="text-sm">Yes, I am available. What service do you need?</p>
                    <span class="text-xs text-orange-100 block mt-1">2:42 PM</span>
                </div>
            </div>

            <!-- Incoming -->
            <div class="flex items-start gap-2">
                <div class="bg-white px-4 py-2 rounded-lg shadow max-w-xs">
                    <p class="text-sm">Garden maintenance.</p>
                    <span class="text-xs text-gray-400 block mt-1">2:44 PM</span>
                </div>
            </div>

        </div>

        <!-- Message Input -->
        <div class="p-4 border-t bg-white">
            <div class="flex items-center gap-3">

                <input type="text"
                       placeholder="Type your message..."
                       class="flex-1 px-4 py-2 border rounded-full focus:ring-2 focus:ring-orange-500 focus:outline-none">

                <button class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-full transition">
                    Send
                </button>

            </div>
        </div>

    </div>

</div>

@endsection
