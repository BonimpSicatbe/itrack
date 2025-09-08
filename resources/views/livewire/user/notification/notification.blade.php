{{-- Fixed container layout for full height display --}}
<div class="h-screen flex flex-col lg:flex-row transition-all duration-300 ease-in-out">
    {{-- Notifications List (Left Panel) --}}
    <div class="@if($selectedNotification && $selectedNotificationData) flex-1 @else w-full @endif bg-white rounded-lg shadow-sm overflow-hidden mb-4 lg:mb-0 @if($selectedNotification && $selectedNotificationData) lg:mr-4 @endif transition-all duration-300 ease-in-out">
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-6 border-b border-gray-200 flex-shrink-0" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-bell text-white text-2xl"></i>
                <h2 class="text-2xl font-bold text-white">Your Notifications</h2>
            </div>
            <button 
                wire:click="markAllAsRead" 
                class="inline-flex items-center px-3 py-1.5 bg-white/20 border border-white/30 text-white text-xs font-medium rounded-lg hover:bg-white/30 transition-all duration-200"
            >
                <i class="fa-solid fa-check mr-1 text-xs"></i>
                Mark all read
            </button>
        </div>

        {{-- Notifications List - Removed redundant wrapper and simplified scrolling --}}
        <div class="flex-1 overflow-y-auto p-4" style="max-height: calc(100vh - 160px);">
            <div class="space-y-2">
                @forelse($notifications as $notification)
                    @php
                        $isUnread = $notification->unread();
                        $isSelected = $selectedNotification === $notification->id;
                        
                        if ($isSelected) {
                            $classes = 'block p-3 rounded-lg cursor-pointer transition-all duration-300 ease-in-out bg-[#DEF4C6]/40 border-2 border-[#1C7C54] shadow-md transform scale-[1.02]';
                        } elseif ($isUnread) {
                            $classes = 'block p-3 rounded-lg cursor-pointer transition-all duration-300 ease-in-out bg-[#DEF4C6]/20 border-l-4 border-[#1C7C54] hover:bg-[#DEF4C6]/30 hover:shadow-md hover:transform hover:scale-[1.01]';
                        } else {
                            $classes = 'block p-3 rounded-lg cursor-pointer transition-all duration-300 ease-in-out bg-gray-50 hover:bg-[#DEF4C6]/20 border border-gray-200 hover:shadow-md hover:transform hover:scale-[1.01]';
                        }
                    @endphp

                    <div wire:click="selectNotification('{{ $notification->id }}')" class="{{ $classes }}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-[#1B512D] line-clamp-2 transition-all duration-200">
                                    {{ $notification->data['message'] ?? 'New notification' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1 flex items-center transition-all duration-200">
                                    <i class="fa-regular fa-clock mr-1"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                            @if($isUnread)
                                <span class="h-2 w-2 bg-[#1C7C54] rounded-full flex-shrink-0 ml-2 mt-1 animate-pulse"></span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fa-regular fa-bell text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">No notifications</h3>
                        <p class="text-xs text-gray-500">You're all caught up!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Notification Detail (Right Panel) - Only show when notification is selected --}}
    @if ($selectedNotification && $selectedNotificationData)
    <div class="flex-1 bg-white rounded-lg shadow-sm flex flex-col animate-in slide-in-from-left-5 fade-in duration-500">
        {{-- Detail Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 flex-shrink-0" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-bell text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">{{ $selectedNotificationData['message'] }}</h2>
                    <p class="text-white/80 text-xs flex items-center">
                        <i class="fa-regular fa-clock mr-1"></i>
                        Received: {{ $selectedNotificationData['created_at']->format('M d, Y g:i A') }}
                    </p>
                </div>
            </div>
            {{-- Close button --}}
            <button 
                wire:click="$set('selectedNotification', null)" 
                class="p-2 text-white/70 hover:text-white hover:bg-white/20 rounded-lg transition-all duration-200"
                title="Close details"
            >
                <i class="fa-solid fa-times text-lg"></i>
            </button>
        </div>

        {{-- Detail Content - Single scrollable area --}}
        <div class="flex-1 overflow-y-auto notification-scroll">
            <div class="p-6">
                <div class="max-w-4xl mx-auto space-y-6">

                    {{-- Combined Requirement Details & Action Card --}}
                    @isset($selectedNotificationData['requirement'])
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 animate-in fade-in slide-in-from-top-3 duration-300 delay-150">
                        <div class="flex items-center px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-[#DEF4C6]/20 to-[#73E2A7]/10">
                            <div class="w-8 h-8 bg-gradient-to-br from-[#1C7C54] to-[#1B512D] rounded-lg flex items-center justify-center mr-3">
                                <i class="fa-solid fa-clipboard-list text-white text-sm"></i>
                            </div>
                            <h3 class="font-bold text-[#1B512D]">Requirement Details</h3>
                        </div>
                        
                        <div class="p-6">
                            {{-- Requirement Info --}}
                            <div class="space-y-4 mb-6">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Requirement Name</p>
                                    <p class="text-[#1B512D] font-bold text-lg">{{ $selectedNotificationData['requirement']['name'] ?? 'N/A' }}</p>
                                </div>
                                
                                @isset($selectedNotificationData['requirement']['description'])
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Description</p>
                                    <div class="bg-gray-50 rounded-lg p-4 border">
                                        <p class="text-[#1B512D] text-sm leading-relaxed">{{ $selectedNotificationData['requirement']['description'] }}</p>
                                    </div>
                                </div>
                                @endisset
                                
                                @isset($selectedNotificationData['requirement']['assigned_to'])
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Assigned To</p>
                                    <div class="inline-flex items-center gap-2 bg-[#73E2A7]/20 px-3 py-2 rounded-lg">
                                        <i class="fa-solid fa-user text-[#1C7C54] text-sm"></i>
                                        <span class="text-[#1B512D] font-medium text-sm">{{ $selectedNotificationData['requirement']['assigned_to'] }}</span>
                                    </div>
                                </div>
                                @endisset

                                @isset($selectedNotificationData['requirement']['due'])
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Due Date</p>
                                    <div class="inline-flex items-center gap-2 bg-orange-100 px-3 py-2 rounded-lg">
                                        <i class="fa-solid fa-calendar text-orange-600 text-sm"></i>
                                        <span class="text-orange-800 font-medium text-sm">
                                            {{ $selectedNotificationData['requirement']['due']->format('M d, Y g:i A') }}
                                        </span>
                                    </div>
                                </div>
                                @endisset
                            </div>
                            
                            {{-- Divider --}}
                            <div class="border-t border-gray-200 my-6"></div>
                            
                            {{-- Action Section --}}
                            <div class="bg-gradient-to-r from-[#DEF4C6]/20 to-[#73E2A7]/10 rounded-lg p-4 border border-[#73E2A7]/30">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="fa-solid fa-upload text-[#1C7C54]"></i>
                                            <h4 class="font-bold text-[#1B512D]">Ready to Submit?</h4>
                                        </div>
                                        <p class="text-[#1B512D]/80 text-sm">
                                            Review the requirement details above and proceed to submit your response.
                                        </p>
                                    </div>
                                    
                                    <div class="flex-shrink-0">
                                        <button 
                                            wire:click="submitRequirement" 
                                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-[#1C7C54] to-[#1B512D] text-white rounded-lg hover:from-[#1B512D] hover:to-[#1C7C54] transition-all duration-200 font-medium text-sm shadow-lg hover:shadow-xl transform hover:scale-[1.02] w-full sm:w-auto justify-center"
                                        >
                                            <i class="fa-solid fa-upload mr-2"></i>
                                            Go to Requirements
                                            <i class="fa-solid fa-arrow-right ml-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endisset

                    {{-- Files Section --}}
                    @if(count($selectedNotificationData['files'] ?? []))
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 animate-in fade-in slide-in-from-top-3 duration-300 delay-300">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-[#DEF4C6]/50 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fa-solid fa-paperclip text-[#1C7C54] text-sm"></i>
                                </div>
                                <h3 class="font-bold text-[#1B512D]">Attached Files</h3>
                            </div>
                            <span class="px-2 py-1 rounded text-xs font-medium bg-[#1C7C54]/10 text-[#1C7C54]">
                                {{ count($selectedNotificationData['files']) }} {{ count($selectedNotificationData['files']) === 1 ? 'file' : 'files' }}
                            </span>
                        </div>
                        
                        <div class="p-4">
                            <div class="space-y-3">
                                @foreach($selectedNotificationData['files'] as $file)
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:border-[#73E2A7]/60 hover:bg-gray-50 transition-all duration-200">
                                    <div class="flex items-center space-x-3 min-w-0 flex-1">
                                        <div class="w-10 h-10 bg-[#DEF4C6]/30 rounded-lg flex items-center justify-center">
                                            @switch($file['extension'] ?? '')
                                                @case('pdf') <i class="fa-regular fa-file-pdf text-red-500"></i> @break
                                                @case('doc') @case('docx') <i class="fa-regular fa-file-word text-blue-500"></i> @break
                                                @case('xls') @case('xlsx') <i class="fa-regular fa-file-excel text-green-500"></i> @break
                                                @case('jpg') @case('jpeg') @case('png') @case('gif') <i class="fa-regular fa-file-image text-purple-500"></i> @break
                                                @default <i class="fa-regular fa-file text-[#1C7C54]"></i>
                                            @endswitch
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-[#1B512D] truncate">{{ $file['name'] ?? 'Unknown file' }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                    {{ strtoupper($file['extension'] ?? 'FILE') }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{ $file['size'] ?? '0 KB' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex space-x-1">
                                        @if(($file['is_previewable'] ?? false) && isset($file['id']))
                                        <a href="{{ route('user.file.preview', $file['id']) }}" 
                                           target="_blank"
                                           class="p-2 text-[#1C7C54] hover:bg-[#1C7C54] hover:text-white rounded-lg transition-all duration-200"
                                           title="Preview">
                                            <i class="fa-solid fa-eye text-sm"></i>
                                        </a>
                                        @endif
                                        @if(isset($file['id']))
                                        <a href="{{ route('user.file.download', $file['id']) }}" 
                                           class="p-2 text-[#1C7C54] hover:bg-[#1C7C54] hover:text-white rounded-lg transition-all duration-200"
                                           title="Download">
                                            <i class="fa-solid fa-download text-sm"></i>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Admin Review Section (if exists) --}}
                    @isset($selectedNotificationData['admin_review'])
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 animate-in fade-in slide-in-from-top-3 duration-300 delay-400">
                        <div class="flex items-center px-4 py-3 border-b border-gray-200 bg-gray-50">
                            <div class="w-8 h-8 bg-[#DEF4C6]/50 rounded-lg flex items-center justify-center mr-3">
                                <i class="fa-solid fa-clipboard-check text-[#1C7C54] text-sm"></i>
                            </div>
                            <h3 class="font-bold text-[#1B512D]">Admin Review</h3>
                        </div>
                        
                        <div class="p-4">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status:</span>
                                    <span class="px-2 py-1 rounded text-xs font-medium 
                                        @if($selectedNotificationData['admin_review']['status'] === 'approved') bg-green-100 text-green-800
                                        @elseif($selectedNotificationData['admin_review']['status'] === 'rejected') bg-red-100 text-red-800
                                        @elseif($selectedNotificationData['admin_review']['status'] === 'revision_needed') bg-yellow-100 text-yellow-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ $selectedNotificationData['admin_review']['status_label'] }}
                                    </span>
                                </div>
                                
                                @if($selectedNotificationData['admin_review']['admin_notes'])
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Admin Notes</p>
                                    <div class="bg-gray-50 rounded-lg p-3 border">
                                        <p class="text-[#1B512D] text-sm">{{ $selectedNotificationData['admin_review']['admin_notes'] }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endisset

                </div>
            </div>
        </div>
    </div>
    @endif
</div>