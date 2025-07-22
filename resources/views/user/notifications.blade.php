<x-user.app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Notifications
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Your Notifications</h3>
                        <form method="POST" action="{{ route('notifications.markAllRead') }}">
                            @csrf
                            <button type="submit" class="text-sm text-blue-500 hover:text-blue-700">
                                Mark all as read
                            </button>
                        </form>
                    </div>

                    <div class="divide-y">
                        @forelse(auth()->user()->notifications as $notification)
                            <a href="{{ isset($notification->data['type']) && $notification->data['type'] === 'new_requirement' ? route('user.requirements') : '#' }}"
                            class="block">
                                <x-notification-item :notification="$notification" />
                            </a>
                        @empty
                            <p class="text-gray-500 text-center py-4">No notifications found</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-user.app-layout>