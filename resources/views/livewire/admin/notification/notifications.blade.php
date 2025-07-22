<div class="flex flex-row grow bg-white rounded-lg overflow-hidden">
    {{-- Notifications List (Left) --}}
    <div class="w-full py-6">
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
                            @php
                                $link = '#';
                                $iconClass = 'text-gray-400';
                                $bgColor = 'bg-gray-50';
                                
                                if (isset($notification->data['type'])) {
                                    switch($notification->data['type']) {
                                        case 'new_submission':
                                            $link = isset($notification->data['submission_id']) 
                                                ? route('admin.submitted-requirements.show', $notification->data['submission_id']) 
                                                : '#';
                                            $iconClass = 'text-green-500';
                                            $bgColor = 'bg-green-50';
                                            break;
                                        case 'new_requirement':
                                            $link = isset($notification->data['requirement_id']) 
                                                ? route('admin.requirements.show', $notification->data['requirement_id']) 
                                                : '#';
                                            $iconClass = 'text-blue-500';
                                            $bgColor = 'bg-blue-50';
                                            break;
                                    }
                                }
                            @endphp

                            <a href="{{ $link }}" class="block hover:bg-gray-100 transition-colors duration-200 {{ $bgColor }}">
                                <div class="p-3 flex items-start">
                                    <div class="flex-shrink-0 pt-1 mr-3">
                                        @if(isset($notification->data['type']) && $notification->data['type'] === 'new_submission')
                                            <svg class="h-5 w-5 {{ $iconClass }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        @elseif(isset($notification->data['type']) && $notification->data['type'] === 'new_requirement')
                                            <svg class="h-5 w-5 {{ $iconClass }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 {{ $iconClass }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $notification->data['message'] ?? 'New notification' }}
                                            </p>
                                            @if($notification->unread())
                                                <span class="ml-2 inline-block h-2 w-2 rounded-full bg-blue-500"></span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                        @if(isset($notification->data['type']) && $notification->data['type'] === 'new_submission')
                                            <div class="mt-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    New Submission
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="p-4 text-center">
                                <p class="text-gray-500">No notifications found</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>