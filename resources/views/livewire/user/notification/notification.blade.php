<div class="flex h-full gap-0 rounded-xl overflow-hidden shadow-lg">
    {{-- Notifications List (Left) --}}
    <div class="w-1/3 border-r border-gray-200 bg-gray-50 flex flex-col">
        <!-- Header -->
        <div class="flex-shrink-0 p-6" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center">
                    <i class="fa-solid fa-bell text-white text-2xl mr-3"></i>
                    <h3 class="text-xl font-semibold text-white">Notifications</h3>
                </div>
            </div>

            {{-- Filter Tabs --}}
            <div class="flex space-x-1 bg-white/10 rounded-xl p-1">
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

            {{-- Mark All Buttons in respective sections --}}
            <div>
                @if($activeTab === 'unread' && $notifications->where('read_at', null)->count() > 0)
                    <button wire:click="markAllAsRead"
                        class="mt-4 w-full text-sm font-semibold text-green-700 bg-white hover:bg-green-50 hover:text-green-800 px-4 py-2 rounded-xl shadow-sm transition-all duration-200">
                        <i class="fa-regular fa-envelope-open mr-2"></i>Mark all as read
                    </button>
                @elseif($activeTab === 'read' && $notifications->where('read_at', '!=', null)->count() > 0)
                    <button wire:click="markAllAsUnread"
                        class="mt-4 w-full text-sm font-semibold text-green-700 bg-white hover:bg-blue-50 hover:text-green-800 px-4 py-2 rounded-xl shadow-sm transition-all duration-200">
                        <i class="fa-regular fa-envelope mr-2"></i>Mark all as unread
                    </button>
                @endif
            </div>
        </div>

        <!-- Notifications List -->
        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            @forelse($filteredNotifications as $notification)
                @php
                    $highlight = $notification->unread()
                        ? 'bg-white border-l-5 border-green-500 shadow-md'
                        : 'bg-gray-50 hover:bg-gray-100 border border-gray-300';
                @endphp

                <div class="relative group">
                    <div wire:click="selectNotification('{{ $notification->id }}')"
                        class="block p-4 rounded-xl {{ $highlight }} cursor-pointer transition-all duration-200 {{ $selectedNotification === $notification->id ? 'ring-2 ring-green-500 ring-opacity-50 shadow-lg' : '' }}">
                        <div class="flex justify-between items-start">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900 truncate mb-1">
                                    {{ $notification->data['message'] ?? 'New notification' }}
                                </p>
                                <div class="flex items-center">
                                    <p class="text-xs text-gray-500">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="p-8">
                        <i class="fa-regular fa-bell-slash text-green-600 text-7xl mb-4"></i>
                        <p class="text-gray-600 text-sm font-semibold">
                            @if($activeTab === 'unread')
                                No unread notifications
                            @elseif($activeTab === 'read')
                                No read notifications
                            @else
                                No notifications
                            @endif
                        </p>
                        <p class="text-gray-500 text-xs mt-1">You're all caught up!</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Notification Detail (Right) --}}
    <div class="w-2/3 bg-gray-50 overflow-y-auto">
        @if ($selectedNotification && $selectedNotificationData)
            <div class="p-3 space-y-3">

                {{-- Header --}}
                <div class=" p-6 rounded-xl shadow-sm border border-gray-100" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-semibold text-white mb-2">Notification Details</h2>
                            <div class="flex items-center text-sm text-gray-100">
                                <i class="fa-regular fa-clock mr-2"></i>
                                <span class="font-semibold">Received:</span>
                                <span class="ml-2">{{ $selectedNotificationData['created_at']->format('M d, Y g:i A') }}</span>
                            </div>
                        </div>
                        {{-- Mark as Unread Button --}}
                        @if(!$selectedNotificationData['unread'])
                            <button wire:click="markAsUnread('{{ $selectedNotification }}')"
                                class="flex items-center px-4 py-2 text-sm font-semibold text-green-700 bg-white hover:bg-green-50 rounded-xl shadow-sm transition-all duration-200">
                                <i class="fa-regular fa-envelope mr-2"></i>Mark as Unread
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Notification Message --}}
                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-info-circle text-green-600 mt-0.5"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-800">{{ $selectedNotificationData['message'] }}</p>
                        </div>
                    </div>
                </div>

                {{-- Display error if requirement data is missing --}}
                @if(!isset($selectedNotificationData['requirement']))
                    <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center shadow-sm">
                        <div class="bg-red-100 p-4 rounded-xl inline-block mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-red-800 mb-2">Requirement Data Unavailable</h3>
                        <p class="text-sm text-red-600 font-semibold">The requirement data for this notification is no longer available. It may have been deleted.</p>
                    </div>
                @else
                    {{-- Requirement Details --}}
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-md">
                        <!-- Header -->
                        <div class="flex items-center mb-6 border-b border-gray-200 pb-4">
                            <div class="rounded-xl mr-2">
                                <i class="fa-solid fa-clipboard-list text-green-700 text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Requirement Details</h3>
                        </div>

                        <!-- Grid Layout -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div class="space-y-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</p>
                                <div class="bg-gray-50 p-3 rounded-xl text-sm font-medium text-gray-900 shadow-inner">
                                    {{ $selectedNotificationData['requirement']['name'] }}
                                </div>
                            </div>

                            <!-- Program -->
                            <div class="space-y-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Program</p>
                                <div class="bg-gray-50 p-3 rounded-xl text-sm text-gray-900 shadow-inner">
                                    @if(isset($selectedNotificationData['requirement']['assigned_to']['display_programs']) && count($selectedNotificationData['requirement']['assigned_to']['display_programs']) > 0)
                                        @if(count($selectedNotificationData['requirement']['assigned_to']['display_programs']) <= 2)
                                            @foreach($selectedNotificationData['requirement']['assigned_to']['display_programs'] as $program)
                                                <div class="flex items-center py-1">
                                                    <i class="fa-solid fa-graduation-cap mr-2 text-gray-500 w-4"></i>
                                                    <span>{{ $program }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            {{-- Show first 2 programs with new lines --}}
                                            @foreach(array_slice($selectedNotificationData['requirement']['assigned_to']['display_programs'], 0, 2) as $program)
                                                <div class="flex items-center py-1">
                                                    <i class="fa-solid fa-graduation-cap mr-2 text-gray-500 w-4"></i>
                                                    <span>{{ $program }}</span>
                                                </div>
                                            @endforeach
                                            {{-- Show "etc." for remaining programs --}}
                                            <div class="flex items-center py-1 text-gray-600">
                                                <i class="fa-solid fa-ellipsis mr-2 text-gray-500 w-4"></i>
                                                <span>and {{ count($selectedNotificationData['requirement']['assigned_to']['display_programs']) - 2 }} more programs</span>
                                            </div>
                                        @endif
                                    @else
                                        <div class="flex items-center">
                                            <i class="fa-solid fa-graduation-cap mr-2 text-gray-500"></i>
                                            {{ $selectedNotificationData['requirement']['program_display'] ?? 
                                            ($selectedNotificationData['course']['program']['program_name'] ?? 
                                            ($selectedNotificationData['requirement']['assigned_to']['program'] ?? 'Not assigned')) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Due Date -->
                            <div class="space-y-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Due Date</p>
                                <div class="bg-gray-50 p-3 rounded-xl flex items-center text-sm text-gray-900 shadow-inner">
                                    <i class="fa-regular fa-calendar mr-2 text-gray-500"></i>
                                    {{ \Carbon\Carbon::parse($selectedNotificationData['requirement']['due'])->format('M d, Y g:i A') }}
                                    @if(\Carbon\Carbon::parse($selectedNotificationData['requirement']['due'])->isPast())
                                        <span class="ml-2 px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                            Overdue
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</p>
                                <div class="bg-gray-50 p-3 rounded-xl flex items-center text-sm text-gray-900 shadow-inner">
                                    <i class="fa-solid fa-circle-info mr-2 text-gray-500"></i>
                                    {{ ucfirst($selectedNotificationData['requirement']['status']) }}
                                </div>
                            </div>

                            <!-- Description -->
                            @if(isset($selectedNotificationData['requirement']['description']))
                            <div class="md:col-span-2 space-y-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Description</p>
                                <div class="bg-gray-50 p-3 rounded-xl text-sm text-gray-900 shadow-inner">
                                    {{ $selectedNotificationData['requirement']['description'] }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Dynamic Action Button Section Based on Notification Type --}}
                    @if(isset($selectedNotificationData['type']))
                        @if($selectedNotificationData['type'] === 'submission_status_updated')
                            {{-- Requirement Folder Action Button for Status Updates --}}
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-xl border border-green-200 shadow-sm">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="fa-solid fa-folder text-green-700"></i>
                                            <h3 class="text-lg font-semibold text-gray-800">Requirement Folder</h3>
                                        </div>
                                        <p class="text-sm text-gray-600">
                                            Access the requirement folder to view details and manage your submissions.
                                        </p>
                                    </div>
                                    <button 
                                        wire:click="openRequirementFolder" 
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-200 font-medium text-sm shadow-lg hover:shadow-xl transform hover:scale-[1.02] justify-center"
                                    >
                                        Open Requirement Folder
                                        <i class="fa-solid fa-arrow-up-right-from-square ml-2"></i>
                                    </button>
                                </div>
                            </div>
                        @else
                            {{-- Submission Action Button for New Requirements --}}
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-xl border border-green-200 shadow-sm">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="fa-solid fa-upload text-green-700"></i>
                                            <h3 class="text-lg font-semibold text-gray-800">Ready to Submit?</h3>
                                        </div>
                                        <p class="text-sm text-gray-600">
                                            Review the requirement details above and proceed to submit your response.
                                        </p>
                                    </div>
                                    <button 
                                        wire:click="submitRequirement" 
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-200 font-medium text-sm shadow-lg hover:shadow-xl transform hover:scale-[1.02] justify-center"
                                    >
                                        Go to Requirements
                                        <i class="fa-solid fa-arrow-up-right-from-square ml-2"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- Default Action Button (fallback) --}}
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-xl border border-green-200 shadow-sm">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fa-solid fa-upload text-green-700"></i>
                                        <h3 class="text-lg font-semibold text-gray-800">Ready to Submit?</h3>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        Review the requirement details above and proceed to submit your response.
                                    </p>
                                </div>
                                <button 
                                    wire:click="submitRequirement" 
                                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-200 font-medium text-sm shadow-lg hover:shadow-xl transform hover:scale-[1.02] justify-center"
                                >
                                    Go to Requirements
                                    <i class="fa-solid fa-arrow-up-right-from-square ml-2"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Files Section --}}
                    @if(count($selectedNotificationData['files'] ?? []))
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                        
                        {{-- Main Header --}}
                        <div class="flex items-center mb-4">
                            <div class="rounded-xl mr-2">
                                <i class="fa-solid fa-folder-open text-green-700 text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Submitted Files</h3>
                        </div>

                        {{-- Review Information --}}
                        @if(isset($selectedNotificationData['status_update']))
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <p class="text-sm font-semibold text-blue-700">Reviewed By</p>
                                    <div class="bg-white p-3 rounded-xl border border-blue-200 text-sm text-gray-700 flex items-center">
                                        <i class="fa-solid fa-user-check text-blue-600 mr-2"></i>
                                        {{ $selectedNotificationData['status_update']['reviewed_by'] ?? 'N/A' }}
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <p class="text-sm font-semibold text-blue-700">Reviewed At</p>
                                    <div class="bg-white p-3 rounded-xl border border-blue-200 text-sm text-gray-700 flex items-center">
                                        <i class="fa-regular fa-calendar-check text-blue-600 mr-2"></i>
                                        {{ $selectedNotificationData['status_update']['reviewed_at'] ? \Carbon\Carbon::parse($selectedNotificationData['status_update']['reviewed_at'])->format('M d, Y g:i A') : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Files List --}}
                        <div class="space-y-4">
                            @foreach($selectedNotificationData['files'] as $file)
                            @php
                                $fileStatus = $file['status'];
                                $fileStatusLabel = match($fileStatus) {
                                    'under_review' => 'Under Review',
                                    'revision_needed' => 'Revision Required',
                                    'rejected' => 'Rejected',
                                    'approved' => 'Approved',
                                    default => ucfirst($fileStatus),
                                };
                                $statusColor = match($fileStatus) {
                                    'approved' => 'bg-green-100 text-green-700 border-green-300',
                                    'rejected' => 'bg-red-100 text-red-700 border-red-300',
                                    'revision_needed' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
                                    default => 'bg-blue-100 text-blue-700 border-blue-300',
                                };
                            @endphp

                            {{-- File Card --}}
                            <div class="border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                                
                                {{-- File Header --}}
                                <div class="flex justify-between items-center mb-4">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center w-10 h-10 bg-green-50 rounded-lg mr-3">
                                            @switch($file['extension'] ?? '')
                                                @case('pdf') <i class="fa-regular fa-file-pdf text-red-500"></i> @break
                                                @case('doc') @case('docx') <i class="fa-regular fa-file-word text-blue-500"></i> @break
                                                @case('xls') @case('xlsx') <i class="fa-regular fa-file-excel text-green-500"></i> @break
                                                @case('jpg') @case('jpeg') @case('png') @case('gif') <i class="fa-regular fa-file-image text-purple-500"></i> @break
                                                @default <i class="fa-regular fa-file text-green-600"></i>
                                            @endswitch
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $file['name'] }}</h4>
                                            <p class="text-xs text-gray-500">{{ $file['file_name'] }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }} border">
                                            {{ $fileStatusLabel }}
                                        </span>
                                        <div class="flex space-x-2">
                                            {{-- Download button with correct route --}}
                                            <a href="{{ route('file.download', ['submission' => $file['submission_id']]) }}" 
                                                class="flex items-center px-3 py-1.5 text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200">
                                                <i class="fa-solid fa-download mr-1"></i> Download
                                            </a>
                                            @if($file['is_previewable'])
                                            {{-- Preview button with correct route --}}
                                            <a href="{{ route('file.preview', ['submission' => $file['submission_id']]) }}" target="_blank"
                                                class="flex items-center px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200">
                                                <i class="fa-solid fa-eye mr-1"></i> Preview
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- File Details --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div class="space-y-1">
                                        <p class="text-xs font-semibold text-gray-500">File Size</p>
                                        <p class="text-gray-700">{{ $file['size'] }}</p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-xs font-semibold text-gray-500">Submitted</p>
                                        <p class="text-gray-700">{{ \Carbon\Carbon::parse($file['created_at'])->format('M d, Y g:i A') }}</p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-xs font-semibold text-gray-500">MIME Type</p>
                                        <p class="text-gray-700">{{ $file['mime_type'] }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Correction Feedback Section (Moved after Files) --}}
                    @if(isset($selectedNotificationData['correction_notes']) && count($selectedNotificationData['correction_notes']))
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                        {{-- Section Header with Expand/Collapse Controls --}}
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <i class="fa-solid fa-message-pen text-blue-600 text-2xl mr-3"></i>
                                <h3 class="text-xl font-semibold text-gray-900">Feedback & Correction History</h3>
                                <span class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full ml-3">
                                    {{ count($selectedNotificationData['correction_notes']) }} entries
                                </span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button 
                                    wire:click="toggleAllCorrectionNotes"
                                    class="text-sm font-medium text-blue-600 hover:text-blue-800 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors duration-200"
                                    title="{{ empty($expandedCorrectionNotes) ? 'Expand All' : 'Collapse All' }}"
                                >
                                    <i class="fa-solid fa-{{ empty($expandedCorrectionNotes) ? 'expand' : 'compress' }} mr-1.5"></i>
                                    {{ empty($expandedCorrectionNotes) ? 'Expand All' : 'Collapse All' }}
                                </button>
                            </div>
                        </div>
                        
                        {{-- Timeline-style correction notes --}}
                        <div class="space-y-3">
                            @foreach($selectedNotificationData['correction_notes'] as $index => $note)
                                @php
                                    $isExpanded = isset($expandedCorrectionNotes[$note['id']]);
                                    $isLast = $loop->last;
                                @endphp
                                
                                <div class="border border-gray-200 rounded-xl overflow-hidden transition-all duration-200 hover:shadow-md {{ $isLast ? 'mb-0' : 'mb-2' }}">
                                    {{-- Note Header (Always Visible) --}}
                                    <div class="bg-gradient-to-r from-gray-50 to-white px-5 py-4 border-b border-gray-200 flex justify-between items-center cursor-pointer"
                                        wire:click="toggleCorrectionNote('{{ $note['id'] }}')">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-semibold text-sm">
                                                    {{ count($selectedNotificationData['correction_notes']) - $index }}
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center">
                                                    <span class="text-sm font-semibold text-gray-800 truncate">
                                                        Feedback from {{ $note['admin_name'] }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center text-xs text-gray-500 mt-1">
                                                    <i class="fa-regular fa-clock mr-1.5"></i>
                                                    <span>{{ \Carbon\Carbon::parse($note['created_at'])->format('M j, Y g:i A') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3 ml-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $note['status_badge'] }}">
                                                {{ $note['status_label'] }}
                                            </span>
                                            <div class="text-gray-400">
                                                <i class="fa-solid fa-chevron-{{ $isExpanded ? 'up' : 'down' }} transition-transform duration-200"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Collapsible Note Content --}}
                                    @if($isExpanded)
                                    <div class="p-5 bg-white animate-fade-in">
                                        <div class="space-y-4">
                                            {{-- File Information --}}
                                            @if($note['file_name'])
                                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="flex-shrink-0">
                                                            <i class="fa-regular fa-file text-gray-500 text-lg"></i>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <span class="text-sm font-semibold text-gray-700 block">Original File</span>
                                                            <p class="text-sm text-gray-600 truncate">{{ $note['file_name'] }}</p>
                                                        </div>
                                                    </div>
                                                    @if($note['has_file_been_replaced'])
                                                        <div class="flex items-center space-x-2 text-green-600 ml-4">
                                                            <i class="fa-solid fa-arrow-right text-xs"></i>
                                                            <span class="text-sm font-semibold whitespace-nowrap">Updated</span>
                                                            <span class="text-xs bg-green-100 px-2 py-1 rounded truncate max-w-xs">
                                                                {{ $note['current_file_name'] }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                            
                                            {{-- Correction Notes --}}
                                            <div class="space-y-3">
                                                <div class="flex items-center text-sm font-semibold text-gray-700">
                                                    <i class="fa-solid fa-comment-dots text-blue-500 mr-2"></i>
                                                    <span>Correction Notes:</span>
                                                </div>
                                                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                                    <p class="text-sm text-gray-700 leading-relaxed">
                                                        {{ $note['notes'] }}
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            {{-- Status Information --}}
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                                <div class="space-y-1">
                                                    <p class="text-xs font-semibold text-gray-500">Current Status</p>
                                                    <div class="flex items-center">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $note['status_badge'] }}">
                                                            {{ $note['status_label'] }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="space-y-1">
                                                    <p class="text-xs font-semibold text-gray-500">Reviewer</p>
                                                    <p class="text-sm text-gray-700">{{ $note['admin_name'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @elseif(isset($selectedNotificationData['submissions'][0]['admin_notes']) && $selectedNotificationData['submissions'][0]['admin_notes'])
                    {{-- Fallback to original admin notes if no correction notes exist --}}
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                        <div class="flex items-start">
                            <i class="fa-solid fa-comment-dots text-yellow-600 mt-0.5 mr-3 text-lg"></i>
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-yellow-800 mb-2">Admin Feedback</h4>
                                <p class="text-sm text-yellow-700">{{ $selectedNotificationData['submissions'][0]['admin_notes'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                @endif
            </div>
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center h-full p-8">
                <div class="text-center max-w-md">
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Notification Selected</h3>
                    <p class="text-gray-500 text-sm">Select a notification from the list to view its details</p>
                </div>
            </div>
        @endif
    </div>
</div>