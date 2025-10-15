{{-- Notification Bell Icon with Dropdown --}}
<div class="relative">
    {{-- Bell Icon Button --}}
    <button 
        wire:click="toggleDropdown"
        class="relative p-2 text-white hover:bg-white/10 rounded-lg transition-colors"
    >
        <i class="fas fa-bell text-xl"></i>
        
        {{-- Badge - Only show for unread --}}
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full border-2 border-green-700">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Floating Dropdown --}}
    @if($showDropdown)
        <div 
            wire:click.outside="toggleDropdown"
            class="absolute right-0 mt-3 w-[350px] bg-white rounded-xl shadow-2xl border border-gray-200 z-50 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200"
        >
            {{-- Header --}}
            <div class="px-5 py-3" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-bell text-white text-lg"></i>
                        <h3 class="font-semibold text-white text-base">Notifications</h3>
                    </div>
                    <button 
                        wire:click="toggleDropdown"
                        class="text-white/80 hover:text-white transition-colors"
                    >
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <p class="text-white/80 text-xs mt-1">
                    @if($unreadCount > 0)
                        {{ $unreadCount }} {{ $unreadCount === 1 ? 'new notification' : 'new notifications' }}
                    @else
                        All caught up!
                    @endif
                </p>
            </div>

            {{-- Notifications List --}}
            <div class="max-h-[400px] overflow-y-auto p-4">
                @forelse($notifications as $notification)
                    @php
                        // Ensure data is properly decoded
                        $data = is_array($notification->data) ? $notification->data : (json_decode($notification->data, true) ?? []);
                        $isUnread = $notification->unread();
                        $type = data_get($data, 'type', 'notification');
                        $message = data_get($data, 'message', 'New notification received');
                        
                        $iconClass = match($type) {
                            'requirement_assigned' => 'fa-file-circle-plus text-blue-500',
                            'requirement_approved' => 'fa-circle-check text-green-500',
                            'requirement_revision' => 'fa-triangle-exclamation text-yellow-500',
                            'requirement_rejected' => 'fa-circle-xmark text-red-500',
                            'requirement_deadline' => 'fa-clock text-orange-500',
                            default => 'fa-bell text-gray-500'
                        };
                        
                        // Apply styling similar to admin side
                        if ($isUnread) {
                            $containerClass = 'bg-white border-l-4 border-green-500 shadow-md hover:shadow-lg';
                            $iconBgClass = 'bg-green-50';
                            $textClass = 'text-gray-900';
                            $timeClass = 'text-gray-500';
                        } else {
                            $containerClass = 'bg-gray-50 border border-gray-200 hover:bg-gray-100';
                            $iconBgClass = 'bg-gray-100';
                            $textClass = 'text-gray-600';
                            $timeClass = 'text-gray-400';
                            $iconClass = str_replace(['text-blue-500', 'text-green-500', 'text-yellow-500', 'text-red-500', 'text-orange-500'], 'text-gray-400', $iconClass);
                        }
                    @endphp
                    
                    {{-- Update the wire:click to navigate to notification page --}}
                    <a 
                        href="{{ route('user.notifications', ['notification' => $notification->id]) }}"
                        wire:click="markAsReadAndNavigate('{{ $notification->id }}')"
                        class="block p-4 mb-2 rounded-xl {{ $containerClass }} cursor-pointer transition-all duration-200 last:mb-0 hover:no-underline"
                    >
                        <div class="flex gap-3">
                            {{-- Icon --}}
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-10 h-10 rounded-lg {{ $iconBgClass }} transition-colors">
                                    <i class="fas {{ $iconClass }} text-base"></i>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm {{ $textClass }} mb-1 {{ $isUnread ? 'font-semibold' : 'font-normal' }}">
                                    {{ $message }}
                                </p>
                                <p class="text-xs {{ $timeClass }} flex items-center">
                                    <i class="fa-regular fa-clock mr-1"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Unread indicator - only show for unread notifications --}}
                            @if($isUnread)
                                <div class="flex-shrink-0 flex items-start pt-1">
                                    <span class="inline-block w-2.5 h-2.5 bg-green-500 rounded-full"></span>
                                </div>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="py-12 text-center">
                        <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                            <i class="fa-regular fa-bell-slash text-gray-300 text-2xl"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-500">No notifications</p>
                        <p class="text-xs text-gray-400 mt-1">You're all caught up!</p>
                    </div>
                @endforelse
            </div>

            {{-- Footer --}}
            @if($notifications->isNotEmpty())
                <div class="px-4 py-3 border-t border-gray-200">
                    <a 
                        href="{{ route('user.notifications') }}" 
                        class="block w-full text-center text-sm font-medium text-[#1C7C54] hover:text-[#1B512D] hover:bg-[#DEF4C6]/20 transition-all py-2 rounded-lg"
                    >
                        View all notifications
                        <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>