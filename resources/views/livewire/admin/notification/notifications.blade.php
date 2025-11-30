@if (session()->has('message'))
    <div class="fixed top-4 right-4 z-50">
        <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl shadow-lg" role="alert">
            <div class="flex items-center">
                <i class="fa-solid fa-check-circle mr-3 text-green-500"></i>
                <span class="text-sm font-semibold">{{ session('message') }}</span>
            </div>
        </div>
    </div>
@endif

<div class="flex h-full gap-0 rounded-xl overflow-hidden shadow-lg">
    {{-- Notifications List (Left) --}}
    <div class="w-1/3 border-r border-gray-200 bg-gray-50 flex flex-col">
        <!-- Header -->
        <div class="flex-shrink-0 p-6"
            style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center">
                    <i class="fa-solid fa-bell text-white text-2xl mr-3"></i>
                    <h3 class="text-xl font-semibold text-white">Notifications</h3>
                </div>
            </div>

            {{-- Filter Tabs --}}
            <div class="flex space-x-1 bg-white/10 rounded-xl p-1">
                <button wire:click="$set('activeTab', 'all')"
                    class="flex-1 py-2 px-3 text-sm font-medium rounded-lg transition-all duration-200 {{ $activeTab === 'all' ? 'bg-white text-green-800 shadow-sm' : 'text-white hover:bg-white/20' }}">
                    All
                </button>
                <button wire:click="$set('activeTab', 'unread')"
                    class="flex-1 py-2 px-3 text-sm font-medium rounded-lg transition-all duration-200 {{ $activeTab === 'unread' ? 'bg-white text-green-800 shadow-sm' : 'text-white hover:bg-white/20' }}">
                    Unread
                </button>
                <button wire:click="$set('activeTab', 'read')"
                    class="flex-1 py-2 px-3 text-sm font-medium rounded-lg transition-all duration-200 {{ $activeTab === 'read' ? 'bg-white text-green-800 shadow-sm' : 'text-white hover:bg-white/20' }}">
                    Read
                </button>
            </div>

            {{-- Mark All Buttons in respective sections --}}
            <div>
                @if ($activeTab === 'unread' && $notifications->where('read_at', null)->count() > 0)
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
                            @if ($activeTab === 'unread')
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
    <div class="w-2/3 bg-gray-50 overflow-y-auto h-[calc(100vh-6rem)]">
        @if ($selectedNotification && $selectedNotificationData)
            @if ($notificationNotFound)
                {{-- Notification Not Available Message --}}
                <div class="h-full flex items-center justify-center p-6">
                    <div class="text-center rounded-xl p-12 bg-white shadow-md border border-gray-200 max-w-md">
                        <div class="bg-red-100 p-4 rounded-xl inline-block mb-4">
                            <i class="fa-regular fa-bell-slash text-red-500 text-4xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-red-800 mb-3">Notification Not Available</h3>
                        <p class="text-sm text-red-600 font-semibold">This notification is no longer available. It may have been deleted or the associated data has been removed.</p>
                        <button wire:click="$set('selectedNotification', null)" 
                            class="mt-4 px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-all duration-200">
                            Back to Notifications
                        </button>
                    </div>
                </div>
            @else
                <div class="p-3 space-y-3">

                    {{-- Header --}}
                    <div class=" p-6 rounded-xl shadow-sm border border-gray-100"
                        style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-xl font-semibold text-white mb-2">Notification Details</h2>
                                <div class="flex items-center text-sm text-gray-100">
                                    <i class="fa-regular fa-clock mr-2"></i>
                                    <span class="font-semibold">Received:</span>
                                    <span
                                        class="ml-2">{{ $selectedNotificationData['created_at']->format('M d, Y g:i A') }}</span>
                                </div>
                            </div>
                            {{-- Mark as Unread Button --}}
                            @if (!$selectedNotificationData['unread'])
                                <button wire:click="markAsUnread('{{ $selectedNotification }}')"
                                    class="flex items-center px-4 py-2 text-sm font-semibold text-green-700 bg-white hover:bg-green-50 rounded-xl shadow-sm transition-all duration-200">
                                    <i class="fa-regular fa-envelope mr-2"></i>Mark as Unread
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Display based on notification type --}}
                    @if ($selectedNotificationData['type'] === 'semester_ended_missing_submissions')
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-md">
                            <div class="flex items-center mb-6 border-b border-gray-200 pb-4">
                                <div class="rounded-xl mr-2">
                                    <i class="fa-solid fa-calendar-times text-red-600 text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">Semester Ended - Missing Submissions</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Semester Name -->
                                <div class="space-y-2">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Semester</p>
                                    <div class="bg-gray-50 p-3 rounded-xl text-sm font-medium text-gray-900 shadow-inner">
                                        {{ $selectedNotificationData['semester']['name'] ?? 'N/A' }}
                                    </div>
                                </div>

                                <!-- End Date -->
                                <div class="space-y-2">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">End Date</p>
                                    <div class="bg-gray-50 p-3 rounded-xl text-sm font-medium text-gray-900 shadow-inner">
                                        @if (isset($selectedNotificationData['semester']['end_date']))
                                            {{ \Carbon\Carbon::parse($selectedNotificationData['semester']['end_date'])->format('M d, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </div>

                                <!-- Total Missing -->
                                <div class="space-y-2">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Missing
                                        Submissions</p>
                                    <div
                                        class="bg-red-50 p-3 rounded-xl text-sm font-medium text-red-700 shadow-inner border border-red-200">
                                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                                        {{ $selectedNotificationData['missing_submissions']['total_count'] ?? 0 }} missing
                                        submissions
                                    </div>
                                </div>

                                <!-- Days Since Ended -->
                                <div class="space-y-2">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Days Since
                                        Semester Ended</p>
                                    <div class="bg-gray-50 p-3 rounded-xl text-sm font-medium text-gray-900 shadow-inner">
                                        @if (isset($selectedNotificationData['semester']['end_date']))
                                            @php
                                                $endDate = \Carbon\Carbon::parse(
                                                    $selectedNotificationData['semester']['end_date'],
                                                );
                                                $daysSinceEnded = (int) $endDate->diffInDays(now()); // Force integer conversion
                                            @endphp
                                            {{ $daysSinceEnded }} days
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Missing Submissions List --}}
                        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                            <div class="flex items-center mb-6">
                                <div class="rounded-xl mr-2">
                                    <i class="fa-solid fa-clipboard-list text-orange-600 text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900">Missing Submissions</h3>
                                <span class="ml-3 px-3 py-1 bg-red-100 text-red-700 text-sm font-semibold rounded-full">
                                    {{ $selectedNotificationData['missing_submissions']['total_count'] ?? 0 }} total
                                </span>
                            </div>

                            @if (($selectedNotificationData['missing_submissions']['total_count'] ?? 0) > 0)
                                <div class="space-y-4">
                                    @foreach ($selectedNotificationData['missing_submissions']['submissions'] ?? [] as $missing)
                                        <div class="border border-orange-200 rounded-xl p-4 bg-orange-50 shadow-sm">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    {{-- Requirement and Course on same line --}}
                                                    <div class="flex items-center justify-between mb-2">
                                                        <h4 class="font-semibold text-gray-900 text-sm">
                                                            {{ $missing['requirement_name'] ?? 'Unknown Requirement' }}
                                                        </h4>
                                                        <div
                                                            class="flex items-center text-gray-600 text-xs bg-white px-3 py-1 rounded-full border border-orange-200">
                                                            <span class="ml-1">
                                                                @if (isset($missing['course_code']) && isset($missing['course_name']))
                                                                    {{ $missing['course_code'] }} -
                                                                    {{ $missing['course_name'] }}
                                                                @else
                                                                    No course assigned
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>

                                                    {{-- Other details in grid --}}
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                                                        <div class="flex items-center text-gray-600">
                                                            <i class="fa-solid fa-user mr-2 text-gray-500"></i>
                                                            <span class="font-semibold">Faculty:</span>
                                                            <span
                                                                class="ml-1">{{ $missing['user_name'] ?? 'Unknown User' }}</span>
                                                        </div>
                                                        <div class="flex items-center text-gray-600">
                                                            <i class="fa-solid fa-envelope mr-2 text-gray-500"></i>
                                                            <span class="font-semibold">Email:</span>
                                                            <span
                                                                class="ml-1">{{ $missing['user_email'] ?? 'N/A' }}</span>
                                                        </div>
                                                        <div class="flex items-center text-gray-600">
                                                            <i class="fa-regular fa-calendar mr-2 text-gray-500"></i>
                                                            <span class="font-semibold">Due Date:</span>
                                                            <span class="ml-1">
                                                                @if (isset($missing['due_date']))
                                                                    {{ \Carbon\Carbon::parse($missing['due_date'])->format('M d, Y g:i A') }}
                                                                @else
                                                                    N/A
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="flex items-center text-red-600">
                                                            <i class="fa-solid fa-clock mr-2 text-red-500"></i>
                                                            <span class="font-semibold">Status:</span>
                                                            <span class="ml-1">Not Submitted</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    @if (($selectedNotificationData['missing_submissions']['total_count'] ?? 0) > 10)
                                        <div class="text-center py-4">
                                            <p class="text-sm text-gray-500 font-semibold">
                                                Showing first 10 of
                                                {{ $selectedNotificationData['missing_submissions']['total_count'] }}
                                                missing submissions
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-12 bg-gray-50 rounded-xl">
                                    <div class="bg-gray-100 rounded-xl p-8 inline-block">
                                        <i class="fa-regular fa-circle-check text-green-500 text-4xl mb-4"></i>
                                        <p class="text-gray-600 text-sm font-semibold">No missing submissions</p>
                                        <p class="text-gray-500 text-xs mt-1">All requirements have been submitted for this
                                            semester</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @elseif ($selectedNotificationData['type'] === 'new_registered_user')
                        {{-- New Registered User Section - Revamped Layout --}}
                        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                            <div class="flex items-center mb-6">
                                <div class="rounded-xl mr-3 bg-green-50 p-3">
                                    <i class="fa-solid fa-user-plus text-green-600 text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900">New Registered User</h3>
                                    <p class="text-sm text-gray-500">A new account has been created — details below are for
                                        admin review</p>
                                </div>
                            </div>

                            {{-- User Information Grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                {{-- Personal Information --}}
                                <div class="space-y-4">
                                    <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Personal Information</h4>
                                    
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-sm font-medium text-gray-600">First Name:</span>
                                            <span class="text-sm font-semibold text-gray-900">{{ $this->newRegisteredUser->first()->firstname ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-sm font-medium text-gray-600">Middle Name:</span>
                                            <span class="text-sm font-semibold text-gray-900">{{ $this->newRegisteredUser->first()->middlename ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-sm font-medium text-gray-600">Last Name:</span>
                                            <span class="text-sm font-semibold text-gray-900">{{ $this->newRegisteredUser->first()->lastname ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-sm font-medium text-gray-600">Extension Name:</span>
                                            <span class="text-sm font-semibold text-gray-900">{{ $this->newRegisteredUser->first()->extensionname ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Account Information --}}
                                <div class="space-y-4">
                                    <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">Account Information</h4>
                                    
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-sm font-medium text-gray-600">Email:</span>
                                            <span class="text-sm font-semibold text-gray-900">{{ $this->newRegisteredUser->first()->email ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-sm font-medium text-gray-600">Email Verified:</span>
                                            <span class="text-sm font-semibold {{ $this->newRegisteredUser->first()->email_verified_at ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $this->newRegisteredUser->first()->email_verified_at ? \Carbon\Carbon::parse($this->newRegisteredUser->first()->email_verified_at)->format('M d, Y g:i A') : 'Not Verified' }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-sm font-medium text-gray-600">Registration Date:</span>
                                            <span class="text-sm font-semibold text-gray-900">{{ $this->newRegisteredUser->first()->created_at->format('M d, Y g:i A') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                            <span class="text-sm font-medium text-gray-600">Account Status:</span>
                                            <span class="text-sm font-semibold {{ $this->newRegisteredUser->first()->email_verified_at ? 'text-green-600' : 'text-yellow-600' }}">
                                                {{ $this->newRegisteredUser->first()->email_verified_at ? 'Verified' : 'Pending Verification' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Verify User Button --}}
                            <div class="flex justify-end pt-4 border-t border-gray-200">
                                @if ($this->newRegisteredUser->first()->email_verified_at)
                                    <button disabled
                                        class="px-6 py-2 text-sm font-semibold text-white bg-gray-400 rounded-xl cursor-not-allowed">
                                        <i class="fa-solid fa-check mr-2"></i>User Already Verified
                                    </button>
                                @else
                                    <button wire:click="verifyUser('{{ $this->newRegisteredUser->first()->id }}')" 
                                        onclick="return confirm('Are you sure you want to verify this user?')"
                                        class="px-6 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-xl shadow-sm transition-all duration-200">
                                        <i class="fa-solid fa-user-check mr-2"></i>Verify User
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Existing Submission/Status Update View --}}
                        {{-- Display error if submission data is missing --}}
                        @if (!isset($selectedNotificationData['submissions']) && !isset($selectedNotificationData['submission']))
                            <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center shadow-sm">
                                <div class="bg-red-100 p-4 rounded-xl inline-block mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-red-800 mb-2">Submission Data Unavailable</h3>
                                <p class="text-sm text-red-600 font-semibold">The submission data for this notification is
                                    no longer available. It may have been deleted.</p>
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

                                {{-- Status Update Information --}}
                                @if (isset($selectedNotificationData['status_update']))
                                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 shadow-sm">
                                        <div class="flex items-center mb-4">
                                            <i class="fa-solid fa-arrows-rotate text-blue-600 text-xl mr-3"></i>
                                            <h3 class="text-lg font-semibold text-blue-900">Status Update</h3>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <p class="text-sm font-semibold text-blue-700">Previous Status</p>
                                                <div class="bg-white p-3 rounded-xl border border-blue-200">
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                                    @if ($selectedNotificationData['status_update']['old_status'] === 'approved') bg-green-100 text-green-800
                                                    @elseif($selectedNotificationData['status_update']['old_status'] === 'rejected') bg-red-100 text-red-800
                                                    @elseif($selectedNotificationData['status_update']['old_status'] === 'revision_needed') bg-yellow-100 text-yellow-800
                                                    @else bg-blue-100 text-blue-800 @endif">
                                                        {{ $this->formatStatus($selectedNotificationData['status_update']['old_status']) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="space-y-2">
                                                <p class="text-sm font-semibold text-blue-700">New Status</p>
                                                <div class="bg-white p-3 rounded-xl border border-blue-200">
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                                                    @if ($selectedNotificationData['status_update']['new_status'] === 'approved') bg-green-100 text-green-800
                                                    @elseif($selectedNotificationData['status_update']['new_status'] === 'rejected') bg-red-100 text-red-800
                                                    @elseif($selectedNotificationData['status_update']['new_status'] === 'revision_needed') bg-yellow-100 text-yellow-800
                                                    @else bg-blue-100 text-blue-800 @endif">
                                                        {{ $this->formatStatus($selectedNotificationData['status_update']['new_status']) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="space-y-2">
                                                <p class="text-sm font-semibold text-blue-700">Reviewed By</p>
                                                <div
                                                    class="bg-white p-3 rounded-xl border border-blue-200 text-sm text-gray-700">
                                                    {{ $selectedNotificationData['status_update']['reviewed_by'] }}
                                                </div>
                                            </div>

                                            <div class="space-y-2">
                                                <p class="text-sm font-semibold text-blue-700">Reviewed At</p>
                                                <div
                                                    class="bg-white p-3 rounded-xl border border-blue-200 text-sm text-gray-700">
                                                    {{ \Carbon\Carbon::parse($selectedNotificationData['status_update']['reviewed_at'])->format('M d, Y g:i A') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Grid Layout -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Name -->
                                    <div class="space-y-2">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</p>
                                        <div
                                            class="bg-gray-50 p-3 rounded-xl text-sm font-medium text-gray-900 shadow-inner">
                                            {{ $selectedNotificationData['requirement']['name'] }}
                                        </div>
                                    </div>

                                    <!-- Program -->
                                    <div class="space-y-2">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Program</p>
                                        <div
                                            class="bg-gray-50 p-3 rounded-xl flex items-center text-sm text-gray-900 shadow-inner">
                                            <i class="fa-solid fa-graduation-cap mr-2 text-gray-500"></i>
                                            @if (isset($selectedNotificationData['course']['program']))
                                                {{ $selectedNotificationData['course']['program']['program_code'] }} -
                                                {{ $selectedNotificationData['course']['program']['program_name'] }}
                                            @elseif(isset($selectedNotificationData['files'][0]['course']['program']))
                                                {{ $selectedNotificationData['files'][0]['course']['program']['program_code'] }}
                                                -
                                                {{ $selectedNotificationData['files'][0]['course']['program']['program_name'] }}
                                            @else
                                                <span class="text-gray-400">No program assigned</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Due Date -->
                                    <div class="space-y-2">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Due Date</p>
                                        <div
                                            class="bg-gray-50 p-3 rounded-xl flex items-center text-sm text-gray-900 shadow-inner">
                                            <i class="fa-regular fa-calendar mr-2 text-gray-500"></i>
                                            {{ \Carbon\Carbon::parse($selectedNotificationData['requirement']['due'])->format('M d, Y g:i A') }}
                                            @if ($selectedNotificationData['requirement']['due']->isPast())
                                                <span
                                                    class="ml-2 px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                                    Overdue
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div class="space-y-2">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</p>
                                        <div
                                            class="bg-gray-50 p-3 rounded-xl flex items-center text-sm text-gray-900 shadow-inner">
                                            <i class="fa-solid fa-circle-info mr-2 text-gray-500"></i>
                                            {{ ucfirst($selectedNotificationData['requirement']['status']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Submissions Section --}}
                            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">

                                {{-- Main Header --}}
                                <div class="flex items-center mb-4">
                                    <div class="rounded-xl mr-2">
                                        <i class="fa-solid fa-folder-open text-green-700 text-2xl"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900">Submissions</h3>
                                </div>

                                {{-- Submission Context Block (Submitted by and Program) --}}
                                <div class="mb-6 pb-4 border-b border-gray-100">
                                    <p class="text-sm text-gray-600 mb-1">
                                        <span class="font-bold text-gray-700 mr-1">Submitted By:</span>
                                        <span
                                            class="font-semibold">{{ $selectedNotificationData['submitter']['name'] }}</span>
                                        ({{ $selectedNotificationData['submitter']['email'] }})
                                    </p>
                                    @if (isset($selectedNotificationData['course']['program']))
                                        <p class="text-sm text-gray-600">
                                            <span class="font-bold text-gray-700 mr-1">Program:</span>
                                            <span class="font-semibold">
                                                {{ $selectedNotificationData['course']['program']['program_code'] }} -
                                                {{ $selectedNotificationData['course']['program']['program_name'] }}
                                            </span>
                                        </p>
                                    @endif
                                </div>

                                {{-- Files List --}}
                                @if (count($selectedNotificationData['files'] ?? []))
                                    <div class="space-y-6">
                                        @foreach ($selectedNotificationData['files'] as $file)
                                            @php
                                                $submission = collect(
                                                    $selectedNotificationData['submissions'] ?? [],
                                                )->firstWhere('id', $file['submission_id']);
                                                $fileStatus = $file['status'];
                                                $fileStatusLabel = match ($fileStatus) {
                                                    'under_review' => 'Under Review',
                                                    'revision_needed' => 'Revision Required',
                                                    'rejected' => 'Rejected',
                                                    'approved' => 'Approved',
                                                    default => ucfirst($fileStatus),
                                                };
                                                $statusColor = match ($fileStatus) {
                                                    'approved' => 'bg-green-100 text-green-700 border-green-300',
                                                    'rejected' => 'bg-red-100 text-red-700 border-red-300',
                                                    'revision_needed' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
                                                    default => 'bg-blue-100 text-blue-700 border-blue-300',
                                                };
                                            @endphp

                                            {{-- Submission Card --}}
                                            <div class="border border-gray-200 rounded-xl overflow-hidden shadow-md">
                                                {{-- Submission Header/Context --}}
                                                <div class="bg-green-700 px-5 py-3 border-b border-gray-200">
                                                    <div
                                                        class="flex flex-wrap justify-between items-center gap-2 text-sm text-white">
                                                        @if ($file['course'] ?? null)
                                                            <p class="font-medium flex items-center">
                                                                <i class="fa-solid fa-book-open mr-2 text-white"></i>
                                                                Course: {{ $file['course']['course_code'] }} -
                                                                {{ $file['course']['course_name'] }}
                                                            </p>
                                                        @endif
                                                        @if ($submission['reviewed_at'] ?? null)
                                                            <p class="font-medium flex items-center">
                                                                Reviewed:
                                                                {{ $submission['reviewed_at']->format('M d, Y g:i A') }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- File Detail (More compact) --}}
                                                <div class="p-5 flex items-center justify-between bg-white">
                                                    <div class="flex items-center space-x-4 min-w-0">
                                                        <div class="flex-shrink-0 p-3 bg-gray-100 rounded-xl shadow-inner">
                                                            @switch($file['extension'])
                                                                @case('pdf')
                                                                    <i class="fa-regular fa-file-pdf text-red-500 text-xl"></i>
                                                                @break

                                                                @case('doc')
                                                                @case('docx')
                                                                    <i class="fa-regular fa-file-word text-blue-500 text-xl"></i>
                                                                @break

                                                                @case('xls')
                                                                @case('xlsx')
                                                                    <i class="fa-regular fa-file-excel text-green-500 text-xl"></i>
                                                                @break

                                                                @case('jpg')
                                                                @case('jpeg')

                                                                @case('png')
                                                                @case('gif')
                                                                    <i
                                                                        class="fa-regular fa-file-image text-purple-500 text-xl"></i>
                                                                @break

                                                                @default
                                                                    <i class="fa-regular fa-file text-gray-500 text-xl"></i>
                                                            @endswitch
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="text-sm font-semibold text-gray-900 truncate mb-1">
                                                                {{ $file['name'] }}</p>
                                                            <div class="flex items-center gap-3">
                                                                <span
                                                                    class="text-xs text-gray-500 font-semibold bg-gray-200 px-2 py-0.5 rounded-full">
                                                                    {{ strtoupper($file['extension']) }} •
                                                                    {{ $file['size'] }}
                                                                </span>
                                                                <span
                                                                    class="text-xs px-3 py-1 rounded-full {{ $statusColor }} font-semibold">
                                                                    {{ $fileStatusLabel }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex space-x-1 flex-shrink-0">
                                                        <a href="{{ route('file.download', ['submission' => $file['submission_id'], 'file' => $file['id']]) }}"
                                                            class="p-2 text-blue-500 hover:text-blue-700 hover:bg-blue-100 rounded-lg transition-colors"
                                                            title="Download">
                                                            <i class="fa-solid fa-download"></i>
                                                        </a>
                                                        @if ($file['is_previewable'])
                                                            <a href="{{ route('file.preview', ['submission' => $file['submission_id'], 'file' => $file['id']]) }}"
                                                                target="_blank"
                                                                class="p-2 text-green-600 hover:text-green-700 hover:bg-green-100 rounded-lg transition-colors"
                                                                title="Preview">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Status Update and Review History (Always Visible) --}}
                                                <div class="p-5 border-t bg-gray-50">

                                                    {{-- Current Review Notes Display (for context) --}}
                                                    <div class="space-y-2 mb-6">
                                                        <p class="text-sm font-semibold text-gray-700">Current Review Notes
                                                        </p>
                                                        <p
                                                            class="text-sm text-gray-600 bg-white p-6 rounded-xl border border-gray-200 shadow-inner ">
                                                            {{ $submission['admin_notes'] ?? 'No notes provided for this status.' }}
                                                        </p>
                                                    </div>
                                            {{-- Display correction notes specific to this submission --}}
                                            @php
                                                $submissionCorrectionNotes = $selectedNotificationData['correction_notes_by_submission'][$file['submission_id']] ?? [];
                                            @endphp

                                                    {{-- Status Update Form (Always Visible) --}}
                                                    <form
                                                        wire:submit.prevent="updateFileStatus('{{ $file['submission_id'] }}')"
                                                        class="space-y-4">
                                                        <h4 class="text-md font-bold text-gray-900 mb-4 border-b pb-2">
                                                            Update Submission Status</h4>

                                                        <div class="flex flex-col md:flex-row gap-4">
                                                            {{-- Status Dropdown --}}
                                                            <div class="w-full md:w-1/2 space-y-2">
                                                                <label for="newStatus-{{ $file['submission_id'] }}"
                                                                    class="block text-sm font-semibold text-gray-700">Select
                                                                    New Status</label>
                                                                <select
                                                                    wire:model="newStatus.{{ $file['submission_id'] }}"
                                                                    id="newStatus-{{ $file['submission_id'] }}"
                                                                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm py-2.5 px-4 bg-white">
                                                                    <option value="">Select new status</option>
                                                                    @foreach (\App\Models\SubmittedRequirement::statusesForReview() as $value => $label)
                                                                        <option value="{{ $value }}">
                                                                            {{ $label }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error("newStatus.{$file['submission_id']}")
                                                                    <p class="text-red-500 text-xs mt-1">{{ $message }}
                                                                    </p>
                                                                @enderror
                                                            </div>

                                                    {{-- Admin Notes Textarea --}}
                                                    <div class="w-full md:w-1/2 space-y-2">
                                                        <label for="adminNotes-{{ $file['submission_id'] }}" class="block text-sm font-semibold text-gray-700">Suggested Corrections (Optional)</label>
                                                        <textarea wire:model="adminNotes.{{ $file['submission_id'] }}" id="adminNotes-{{ $file['submission_id'] }}" rows="1"
                                                            placeholder="Write correction notes for the submitter..."
                                                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm py-2.5 px-4 bg-white resize-none"></textarea>
                                                        @error("adminNotes.{$file['submission_id']}")
                                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                </div>

                                                {{-- Update Button --}}
                                                <div class="flex justify-end">
                                                    <button type="submit"
                                                        class="px-5 py-2 border border-transparent rounded-xl text-sm font-semibold text-white bg-green-600 hover:bg-green-700 shadow-lg hover:shadow-xl transition-all duration-200">
                                                        <i class="fa-solid fa-check mr-2"></i>Apply Status Update
                                                    </button>
                                                </div>
                                            </form>

                                            @if(count($submissionCorrectionNotes) > 0)
                                            <div class=" pt-6 pb-2 rounded-xl">
                                                <div class="flex items-center mb-6">
                                                    <h3 class="text-lg pl-3 font-semibold text-gray-900">Previous Correction Notes</h3>
                                                    <span class="ml-3 px-3 py-1 bg-blue-100 text-blue-700 text-sm font-semibold rounded-full">
                                                        {{ count($submissionCorrectionNotes) }} notes
                                                    </span>
                                                </div>

                                                <div class="space-y-4">
                                                    @foreach($submissionCorrectionNotes as $note)
                                                    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                                        <div class="bg-gray-50 px-5 py-3 border-b border-gray-200 flex justify-between items-center">
                                                            <div class="flex items-center">
                                                                <span class="text-sm font-semibold text-gray-700">
                                                                    Note from {{ $note['admin']['name'] ?? 'Admin' }}
                                                                </span>
                                                                <span class="ml-3 text-xs text-gray-500">
                                                                    {{ \Carbon\Carbon::parse($note['created_at'])->format('M d, Y g:i A') }}
                                                                </span>
                                                            </div>
                                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                                @if($note['status'] === 'approved') bg-green-100 text-green-800
                                                                @elseif($note['status'] === 'rejected') bg-red-100 text-red-800
                                                                @elseif($note['status'] === 'revision_needed') bg-yellow-100 text-yellow-800
                                                                @elseif($note['status'] === 'under_review') bg-blue-100 text-blue-800
                                                                @else bg-gray-100 text-gray-800 @endif">
                                                                {{ $note['status_label'] }}
                                                            </span>
                                                        </div>

                                                        <div class="p-5 bg-white">
                                                            <div class="space-y-3">
                                                                @if($note['file_name'])
                                                                <div class="flex items-center text-sm text-gray-600">
                                                                    <i class="fa-regular fa-file mr-2 text-gray-500"></i>
                                                                    <span class="font-semibold">Original File:</span>
                                                                    <span class="ml-2 text-gray-700">{{ $note['file_name'] }}</span>
                                                                </div>
                                                                @endif

                                                                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                                                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $note['correction_notes'] }}</p>
                                                                </div>

                                                                @if($note['addressed_at'])
                                                                <div class="flex items-center text-sm text-green-600">
                                                                    <i class="fa-regular fa-clock mr-2"></i>
                                                                    <span class="font-semibold">Addressed on:</span>
                                                                    <span class="ml-2">{{ \Carbon\Carbon::parse($note['addressed_at'])->format('M d, Y g:i A') }}</span>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                                {{-- No files message --}}
                                <div class="text-center py-12 bg-gray-50 rounded-xl">
                                    <div class="bg-gray-100 rounded-xl p-8 inline-block">
                                        <i class="fa-regular fa-file-excel text-gray-400 text-4xl mb-4"></i>
                                        <p class="text-gray-600 text-sm font-semibold">No files submitted</p>
                                        <p class="text-gray-500 text-xs mt-1">This submission doesn't contain any files
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            @endif
            </div>
        @else
            <div class="h-full flex items-center justify-center p-6">
                <div class="text-center rounded-xl p-12">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">No notification selected</h3>
                    <p class="text-sm text-gray-500 font-semibold">Click on a notification from the list to view
                        details</p>
                </div>
            </div>
        @endif
    </div>
</div>