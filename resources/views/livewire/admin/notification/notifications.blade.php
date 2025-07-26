<div class="flex h-full bg-white rounded-lg">
    {{-- Notifications List (Left) --}}
    <div class="w-1/3 border-r overflow-y-auto">
        <div class="p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Notifications</h3>
                <button wire:click="markAllAsRead" class="text-sm text-blue-500 hover:text-blue-700">
                    Mark all read
                </button>
            </div>

            <div class="space-y-2">
                @forelse($notifications as $notification)
                    @php
                        $highlight = $notification->unread() ? 'bg-blue-50 border-l-4 border-blue-500' : 'bg-white';
                        $type = $notification->data['type'] ?? null;
                    @endphp

                    <div wire:click="selectNotification('{{ $notification->id }}')"
                         class="block p-3 rounded-lg {{ $highlight }} hover:shadow cursor-pointer {{ $selectedNotification === $notification->id ? 'ring-2 ring-blue-500' : '' }}">
                        <div class="flex justify-between">
                            <p class="text-sm font-medium truncate">
                                {{ $notification->data['message'] ?? 'New notification' }}
                            </p>
                            @if($notification->unread())
                                <span class="ml-2 h-2 w-2 rounded-full bg-blue-500"></span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No notifications</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Notification Detail (Right) --}}
    <div class="w-2/3 p-6">
        @if ($selectedNotification && $selectedNotificationData)
            <div class="space-y-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-bold">
                            {{ $selectedNotificationData['type'] === 'new_submission' ? 'Submission' : 'Requirement' }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ $selectedNotificationData['created_at']->format('M d, Y g:i A') }}
                        </p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full {{ $selectedNotificationData['unread'] ? 'bg-blue-100 text-blue-800' : 'bg-gray-100' }}">
                        {{ $selectedNotificationData['unread'] ? 'New' : 'Viewed' }}
                    </span>
                </div>

                <div class="border-t border-gray-200 my-2"></div>

                <div class="space-y-4">
                    <p class="text-sm">{{ $selectedNotificationData['message'] }}</p>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        @foreach(['name', 'description', 'due', 'user_name', 'assigned_to'] as $key)
                            @if(isset($selectedNotificationData[$key]))
                                <div>
                                    <p class="font-medium">{{ str_replace('_', ' ', ucfirst($key)) }}</p>
                                    <p class="text-gray-600">
                                        @if($key === 'due')
                                            {{ $selectedNotificationData[$key]->format('M d, Y g:i A') }}
                                        @else
                                            {{ $selectedNotificationData[$key] }}
                                        @endif
                                    </p>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if(count($selectedNotificationData['files'] ?? []))
                        <div>
                            <h3 class="text-sm font-bold mb-2">Files</h3>
                            <div class="space-y-2">
                                @foreach($selectedNotificationData['files'] as $file)
                                    <div class="flex items-center justify-between p-2 border rounded">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-blue-600">{{ $file['name'] }}</span>
                                            <span class="text-xs text-gray-500">{{ strtoupper($file['extension']) }}</span>
                                        </div>
                                        <a href="{{ $file['url'] }}" download class="text-sm text-blue-600 hover:underline">
                                            Download
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <p class="text-gray-400">Select a notification</p>
        @endif
    </div>
</div>