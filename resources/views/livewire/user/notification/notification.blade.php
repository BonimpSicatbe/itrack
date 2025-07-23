<div class="flex h-full bg-white rounded-lg">
    {{-- Notifications List (Left) --}}
    <div class="w-1/3 border-r overflow-y-auto">
        <div class="flex flex-col p-4 gap-4">
            @forelse ($notifications as $notification)
                <div wire:click="selectNotification('{{ $notification->id }}')"
                    wire:key="notif-{{ $notification->id }}"
                    class="p-3 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors duration-200
                           @if ($selectedNotification === $notification->id) bg-blue-50 border-l-4 border-blue-500 @endif
                           @if ($notification->unread()) bg-blue-50 @endif">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $notification->data['requirement']['name'] ?? 'New Notification' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1 truncate">
                                {{ $notification->data['message'] ?? '' }}
                            </p>
                        </div>
                        @if($notification->unread())
                            <span class="ml-2 inline-block h-2 w-2 rounded-full bg-blue-500"></span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $notification->created_at->diffForHumans() }}
                    </p>
                </div>
            @empty
                <div class="text-gray-500 text-center py-4">No notifications found</div>
            @endforelse
        </div>
    </div>

    {{-- Notification Detail (Right) --}}
    <div class="w-2/3 p-6">
        @if ($selectedNotificationData)
            <div class="space-y-4">
                {{-- Header --}}
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-bold">
                            {{ $selectedNotificationData['data']['requirement']['name'] ?? 'Notification Details' }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ $selectedNotificationData['created_at']->format('F d, Y, g:ia') }}
                        </p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full {{ $selectedNotificationData['unread'] ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $selectedNotificationData['unread'] ? 'New' : 'Viewed' }}
                    </span>
                </div>

                <div class="border-t border-gray-200 my-3"></div>

                {{-- Body --}}
                <div class="space-y-6">
                    {{-- Message --}}
                    <div>
                        <p class="text-sm">{{ $selectedNotificationData['message'] }}</p>
                    </div>

                    {{-- Requirement Details --}}
                    @if(isset($selectedNotificationData['data']['requirement']))
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="font-medium">Description</p>
                                <p class="text-gray-600">{{ $selectedNotificationData['data']['requirement']['description'] ?? 'No description' }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Due Date</p>
                                <p class="text-gray-600">
                                    @if(isset($selectedNotificationData['data']['requirement']['due']))
                                        {{ \Carbon\Carbon::parse($selectedNotificationData['data']['requirement']['due'])->format('F d, Y, g:ia') }}
                                    @else
                                        No due date
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="font-medium">Assigned To</p>
                                <p class="text-gray-600">{{ $selectedNotificationData['data']['requirement']['assigned_to'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Created By</p>
                                <p class="text-gray-600">
                                    {{ App\Models\User::find($selectedNotificationData['data']['requirement']['created_by'])->full_name ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        {{-- Files --}}
                        @if(!empty($selectedNotificationData['files']))
                            <div>
                                <h3 class="text-sm font-bold mb-2">Required Files</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">File Name</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Type</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Size</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($selectedNotificationData['files'] as $file)
                                                <tr>
                                                    <td class="px-3 py-2 whitespace-nowrap">
                                                        <a href="{{ $file['url'] }}" target="_blank" class="text-blue-600 hover:underline">
                                                            {{ $file['name'] }}
                                                        </a>
                                                    </td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">
                                                        {{ $file['type'] }} ({{ strtoupper($file['extension']) }})
                                                    </td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">
                                                        {{ $file['size'] }}
                                                    </td>
                                                    <td class="px-3 py-2 whitespace-nowrap">
                                                        <a href="{{ $file['url'] }}" download class="text-blue-600 hover:underline">Download</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @else
            <div class="flex items-center justify-center h-full">
                <p class="text-gray-400">Select a notification to view details</p>
            </div>
        @endif
    </div>
</div>