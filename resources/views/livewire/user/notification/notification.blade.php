<div class="flex h-full">
    {{-- Notifications List (Left) --}}
    <div class="w-1/3 border-r border-gray-200 bg-white rounded-xl overflow-y-auto">
        <div class="sticky top-0 z-10 p-4 rounded-t-xl" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-bell text-white text-xl"></i>
                    <h3 class="text-lg font-semibold text-white">Notifications</h3>
                </div>
                <button 
                    wire:click="toggleAllReadStatus" 
                    class="text-sm text-white hover:text-gray-200 font-medium px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 transition-colors border border-white/20"
                >
                    @if($hasUnreadNotifications)
                        <i class="fa-solid fa-check mr-1 text-xs"></i>
                        Mark all read
                    @else
                        <i class="fa-solid fa-rotate-left mr-1 text-xs"></i>
                        Mark all unread
                    @endif
                </button>
            </div>

            {{-- Filter Tabs --}}
            <div class="mt-4 flex space-x-1 bg-white/10 rounded-xl p-1">
                <button 
                    wire:click="$set('activeTab', 'all')"
                    class="flex-1 py-2 px-3 text-sm font-medium rounded-lg transition-all duration-200 {{ $activeTab === 'all' ? 'bg-white text-green-800 shadow-sm' : 'text-white hover:bg-white/20' }}"
                >
                    All
                </button>
                <button 
                    wire:click="$set('activeTab', 'unread')"
                    class="flex-1 py-2 px-3 text-sm font-medium rounded-lg transition-all duration-200 {{ $activeTab === 'unread' ? 'bg-white text-green-800 shadow-sm' : 'text-white hover:bg-white/20' }}"
                >
                    Unread
                </button>
                <button 
                    wire:click="$set('activeTab', 'read')"
                    class="flex-1 py-2 px-3 text-sm font-medium rounded-lg transition-all duration-200 {{ $activeTab === 'read' ? 'bg-white text-green-800 shadow-sm' : 'text-white hover:bg-white/20' }}"
                >
                    Read
                </button>
            </div>
        </div>

        <div class="p-4 space-y-2">
            @forelse($filteredNotifications as $notification)
                @php
                    $isUnread = $notification->unread();
                    $isSelected = $selectedNotification === $notification->id;
                    
                    if ($isSelected) {
                        $classes = 'bg-[#DEF4C6]/40 border-l-7 border-[#1C7C54] shadow-md';
                    } elseif ($isUnread) {
                        $classes = 'bg-[#DEF4C6]/20 border-l-7 border-[#1C7C54]';
                    } else {
                        $classes = 'bg-white hover:bg-gray-50';
                    }
                @endphp

                <div 
                    wire:click="selectNotification('{{ $notification->id }}')"
                    class="block p-4 rounded-2xl border-2 border-green-600 {{ $classes }} cursor-pointer transition-all duration-200"
                >
                    <div class="flex justify-between items-start">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $notification->data['message'] ?? 'New notification' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1 flex items-center">
                                <i class="fa-regular fa-clock mr-1 text-xs"></i>
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                        @if($isUnread)
                            <span class="ml-2 h-2 w-2 rounded-full bg-[#1C7C54] flex-shrink-0 mt-1.5 animate-pulse"></span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="mx-auto w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                        <i class="fa-regular fa-bell text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 text-sm">
                        @if($activeTab === 'unread')
                            No unread notifications
                        @elseif($activeTab === 'read')
                            No read notifications
                        @else
                            No notifications
                        @endif
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Notification Detail (Right) --}}
    <div class="w-2/3 p-6 overflow-y-auto bg-white rounded-xl">
        @if ($selectedNotification && $selectedNotificationData)
            <div class="max-w-3xl mx-auto space-y-4">
                {{-- Header --}}
                <div class="flex justify-between items-start p-4 rounded-xl" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                    <div>
                        <h2 class="text-xl font-bold text-white">Notification Details</h2>
                        <p class="text-white/80 text-sm mt-1 flex items-center">
                            <i class="fa-regular fa-clock mr-1"></i>
                            Received: {{ $selectedNotificationData['created_at']->format('M d, Y g:i A') }}
                        </p>
                    </div>
                    <button 
                        wire:click="$set('selectedNotification', null)" 
                        class="p-2 text-white/70 hover:text-white hover:bg-white/20 rounded-xl transition-all duration-200"
                        title="Close details"
                    >
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                {{-- Notification Message --}}
                <div class="bg-[#DEF4C6]/30 p-4 rounded-xl border border-[#73E2A7]/50">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-info-circle text-[#1C7C54] mt-0.5"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">{{ $selectedNotificationData['message'] }}</p>
                        </div>
                    </div>
                </div>

                {{-- Requirement Details --}}
                @isset($selectedNotificationData['requirement'])
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex items-center mb-4 p-3 rounded-xl" style="background: linear-gradient(to right, #DEF4C6/20, #73E2A7/10);">
                        <div class="w-8 h-8 bg-gradient-to-br from-[#1C7C54] to-[#1B512D] rounded-xl flex items-center justify-center mr-3">
                            <i class="fa-solid fa-clipboard-list text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Requirement Details</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="font-medium text-gray-500 text-xs uppercase tracking-wide mb-1">Name</p>
                            <p class="text-gray-800 font-medium">{{ $selectedNotificationData['requirement']['name'] ?? 'N/A' }}</p>
                        </div>
                        
                        @isset($selectedNotificationData['requirement']['description'])
                        <div class="md:col-span-2">
                            <p class="font-medium text-gray-500 text-xs uppercase tracking-wide mb-1">Description</p>
                            <p class="text-gray-600 bg-gray-50 p-3 rounded-xl border">{{ $selectedNotificationData['requirement']['description'] }}</p>
                        </div>
                        @endisset
                        
                        @isset($selectedNotificationData['requirement']['due'])
                        <div>
                            <p class="font-medium text-gray-500 text-xs uppercase tracking-wide mb-1">Due Date</p>
                            <div class="flex items-center bg-orange-100 px-3 py-2 rounded-xl">
                                <i class="fa-regular fa-calendar text-orange-600 mr-2"></i>
                                <p class="text-gray-800 font-medium">
                                    {{ $selectedNotificationData['requirement']['due']->format('M d, Y g:i A') }}
                                    @if($selectedNotificationData['requirement']['due']->isPast())
                                        <span class="ml-2 text-xs text-red-500">(Overdue)</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endisset
                    </div>
                </div>
                @endisset

                {{-- Submission Action Button --}}
                @isset($selectedNotificationData['requirement'])
                <div class="bg-gradient-to-r from-[#DEF4C6]/20 to-[#73E2A7]/10 p-5 rounded-xl border border-[#73E2A7]/30">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fa-solid fa-upload text-[#1C7C54]"></i>
                                <h3 class="text-lg font-semibold text-gray-800">Ready to Submit?</h3>
                            </div>
                            <p class="text-sm text-gray-600">
                                Review the requirement details above and proceed to submit your response.
                            </p>
                        </div>
                        <button 
                            wire:click="submitRequirement" 
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-[#1C7C54] to-[#1B512D] text-white rounded-xl hover:from-[#1B512D] hover:to-[#1C7C54] transition-all duration-200 font-medium text-sm shadow-lg hover:shadow-xl transform hover:scale-[1.02] justify-center"
                        >
                            <i class="fa-solid fa-upload mr-2"></i>
                            Go to Requirements
                            <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
                @endisset

                {{-- Files Section --}}
                @if(count($selectedNotificationData['files'] ?? []))
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4 p-3 rounded-xl" style="background: linear-gradient(to right, #DEF4C6/20, #73E2A7/10);">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-[#DEF4C6]/50 rounded-xl flex items-center justify-center mr-3">
                                <i class="fa-solid fa-paperclip text-[#1C7C54] text-sm"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">Attached Files</h3>
                        </div>
                        <span class="px-2 py-1 rounded-xl text-sm text-gray-500 font-medium bg-[#1C7C54]/10">
                            {{ count($selectedNotificationData['files']) }} {{ count($selectedNotificationData['files']) === 1 ? 'file' : 'files' }}
                        </span>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($selectedNotificationData['files'] as $file)
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-xl hover:border-[#73E2A7]/60 hover:bg-gray-50 transition-all duration-200">
                            <div class="flex items-center space-x-3 min-w-0 flex-1">
                                <div class="w-10 h-10 bg-[#DEF4C6]/30 rounded-xl flex items-center justify-center">
                                    @switch($file['extension'] ?? '')
                                        @case('pdf') <i class="fa-regular fa-file-pdf text-red-500"></i> @break
                                        @case('doc') @case('docx') <i class="fa-regular fa-file-word text-blue-500"></i> @break
                                        @case('xls') @case('xlsx') <i class="fa-regular fa-file-excel text-green-500"></i> @break
                                        @case('jpg') @case('jpeg') @case('png') @case('gif') <i class="fa-regular fa-file-image text-purple-500"></i> @break
                                        @default <i class="fa-regular fa-file text-[#1C7C54]"></i>
                                    @endswitch
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $file['name'] ?? 'Unknown file' }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-lg">
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
                                   class="p-2 text-[#1C7C54] hover:bg-[#1C7C54] hover:text-white rounded-xl transition-all duration-200"
                                   title="Preview">
                                    <i class="fa-solid fa-eye text-sm"></i>
                                </a>
                                @endif
                                @if(isset($file['id']))
                                <a href="{{ route('user.file.download', $file['id']) }}" 
                                   class="p-2 text-[#1C7C54] hover:bg-[#1C7C54] hover:text-white rounded-xl transition-all duration-200"
                                   title="Download">
                                    <i class="fa-solid fa-download text-sm"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Admin Review Section --}}
                @isset($selectedNotificationData['admin_review'])
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex items-center mb-4 p-3 rounded-xl" style="background: linear-gradient(to right, #DEF4C6/20, #73E2A7/10);">
                        <div class="w-8 h-8 bg-gradient-to-br from-[#1C7C54] to-[#1B512D] rounded-xl flex items-center justify-center mr-3">
                            <i class="fa-solid fa-clipboard-check text-white text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Submission Review</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="font-medium text-gray-500 text-xs uppercase tracking-wide mb-1">Status</p>
                            <div class="flex items-center">
                                @php
                                    $status = $selectedNotificationData['admin_review']['status'] ?? '';
                                    $statusLabel = $selectedNotificationData['admin_review']['status_label'] ?? '';
                                    $statusColor = match($status) {
                                        'approved' => 'bg-green-100 text-green-800 border-green-200',
                                        'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                        'revision_needed' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                        'under_review' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        default => 'bg-gray-100 text-gray-800 border-gray-200'
                                    };
                                @endphp
                                <span class="px-3 py-1.5 rounded-full border text-sm font-medium {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>
                        
                        @isset($selectedNotificationData['admin_review']['reviewed_at'])
                        <div>
                            <p class="font-medium text-gray-500 text-xs uppercase tracking-wide mb-1">Reviewed At</p>
                            <p class="text-gray-800 font-medium">
                                {{ \Carbon\Carbon::parse($selectedNotificationData['admin_review']['reviewed_at'])->format('M d, Y g:i A') }}
                            </p>
                        </div>
                        @endisset
                        
                        @isset($selectedNotificationData['admin_review']['admin_notes'])
                        <div class="md:col-span-2">
                            <p class="font-medium text-gray-500 text-xs uppercase tracking-wide mb-1">Admin Notes</p>
                            <p class="text-gray-600 bg-gray-50 p-3 rounded-xl border">{{ $selectedNotificationData['admin_review']['admin_notes'] }}</p>
                        </div>
                        @endisset
                    </div>
                </div>
                @endisset
            </div>
        @else
            <div class="h-full flex flex-col items-center justify-center text-center py-12">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                    <i class="fa-regular fa-bell text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No notification selected</h3>
                <p class="text-sm text-gray-500">Select a notification from the list to view details</p>
            </div>
        @endif
    </div>
</div>