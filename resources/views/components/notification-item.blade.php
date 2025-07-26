@props(['notification'])

@php
    $bgColor = 'bg-gray-50'; // Default color
    if (isset($notification->data['type'])) {
        $bgColor = match($notification->data['type']) {
            'new_requirement' => 'bg-blue-50',
            'new_submission' => 'bg-green-50',
            default => 'bg-gray-50'
        };
    }
@endphp

<div class="p-3 border-b {{ $bgColor }} hover:bg-gray-100 transition-colors duration-200">
    <div class="flex justify-between items-start">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-900">
                {{ $notification->data['message'] ?? 'New notification' }}
            </p>
            <p class="text-xs text-gray-500">
                {{ $notification->created_at->diffForHumans() }}
            </p>
        </div>
        @if($notification->unread())
            <span class="ml-2 inline-block h-2 w-2 rounded-full bg-blue-500"></span>
        @endif
    </div>
</div>