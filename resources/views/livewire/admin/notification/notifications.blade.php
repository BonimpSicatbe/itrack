@if (session()->has('message'))
    <div class="fixed top-4 right-4 z-50">
        <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl shadow-lg" role="alert">
            <div class="flex items-center">
                <i class="fa-solid fa-check-circle mr-3 text-green-500"></i>
                <span class="text-sm font-semibold">{{ session('message') }}</span>
            </div>
        </div>
    </div>
@endif

<div class="flex h-full gap-0 rounded-2xl overflow-hidden shadow-lg border border-gray-100">
    {{-- Notifications List (Left) --}}
    <div class="w-1/3 border-r border-gray-200 overflow-y-auto" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center">
                    <i class="fa-solid fa-bell text-white text-lg mr-3"></i>
                    <h3 class="text-xl font-semibold text-white">Notifications</h3>
                </div>
                @if($notifications->where('read_at', null)->count() > 0)
                    <button wire:click="markAllAsRead" class="text-sm font-semibold text-green-700 bg-white hover:bg-green-50 hover:text-green-800 px-4 py-2 rounded-full shadow-sm transition-all duration-200">
                        <i class="fa-regular fa-envelope-open mr-2"></i>Mark all read
                    </button>
                @else
                    <button wire:click="markAllAsUnread" class="text-sm font-semibold text-blue-700 bg-white hover:bg-blue-50 hover:text-blue-800 px-4 py-2 rounded-full shadow-sm transition-all duration-200">
                        <i class="fa-regular fa-envelope mr-2"></i>Mark all unread
                    </button>
                @endif
            </div>

            <div class="space-y-3">
                @forelse($notifications as $notification)
                    @php
                        $highlight = $notification->unread() ? 'bg-white/95 border-l-4 border-blue-500 shadow-md' : 'bg-white/80 hover:bg-white/90';
                    @endphp

                    <div class="relative group">
                        <div wire:click="selectNotification('{{ $notification->id }}')"
                            class="block p-4 pr-12 rounded-xl {{ $highlight }} hover:shadow-lg cursor-pointer transition-all duration-200 {{ $selectedNotification === $notification->id ? 'ring-2 ring-blue-500 ring-opacity-50 shadow-lg' : '' }}">
                            <div class="flex justify-between items-start">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-900 truncate mb-1">
                                        {{ $notification->data['message'] ?? 'New notification' }}
                                    </p>
                                    <div class="flex items-center">
                                        <p class="text-xs text-gray-500">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                        @if($notification->unread())
                                            <span class="ml-2 h-2.5 w-2.5 rounded-full bg-blue-500 animate-pulse"></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Three dots menu --}}
                        <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <div class="dropdown dropdown-left">
                                <label tabindex="0" class="btn btn-xs btn-ghost text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </label>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-white rounded-xl border border-gray-200 w-44">
                                    <li>
                                        <a wire:click.stop="toggleNotificationReadStatus('{{ $notification->id }}')" class="text-xs py-2.5 px-3 cursor-pointer hover:bg-gray-50 rounded-xl font-semibold">
                                            @if($notification->unread())
                                                <i class="fa-regular fa-envelope-open mr-2 text-green-600"></i> Mark as read
                                            @else
                                                <i class="fa-regular fa-envelope mr-2 text-blue-600"></i> Mark as unread
                                            @endif
                                        </a>
                                    </li>
                                    <li>
                                        <a wire:click.stop="openDeleteConfirmationModal('{{ $notification->id }}')" 
                                        class="text-xs py-2.5 px-3 text-red-600 cursor-pointer hover:bg-red-50 rounded-xl font-semibold">
                                            <i class="fa-regular fa-trash-can mr-2"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="bg-white/20 rounded-2xl p-8">
                            <i class="fa-regular fa-bell-slash text-white/70 text-4xl mb-4"></i>
                            <p class="text-white/80 text-sm font-semibold">No notifications</p>
                            <p class="text-white/60 text-xs mt-1">You're all caught up!</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Notification Detail (Right) --}}
    <div class="w-2/3 bg-gray-50 overflow-y-auto h-[calc(100vh-6rem)]">
        @if ($selectedNotification && $selectedNotificationData)
            <div class="p-6 space-y-6">

                {{-- Header --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">Notification Details</h2>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fa-regular fa-clock mr-2"></i>
                                <span class="font-semibold">Received:</span>
                                <span class="ml-2">{{ $selectedNotificationData['created_at']->format('M d, Y g:i A') }}</span>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-green-100 to-blue-100 p-3 rounded-full">
                            <i class="fa-solid fa-bell text-green-700 text-lg"></i>
                        </div>
                    </div>
                </div>

                {{-- Display error if submission data is missing --}}
                @if(!isset($selectedNotificationData['submission']))
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-8 text-center shadow-sm">
                        <div class="bg-red-100 p-4 rounded-2xl inline-block mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-red-800 mb-2">Submission Data Unavailable</h3>
                        <p class="text-sm text-red-600 font-semibold">The submission data for this notification is no longer available. It may have been deleted.</p>
                    </div>
                @else
                    {{-- Requirement Details --}}
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                        <div class="flex items-center mb-6">
                            <div class="bg-gradient-to-r from-green-100 to-green-200 p-3 rounded-xl mr-4">
                                <i class="fa-solid fa-clipboard-list text-green-700 text-lg"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Requirement Details</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-500">Name</p>
                                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-xl">{{ $selectedNotificationData['requirement']['name'] }}</p>
                            </div>
                            
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-500">Description</p>
                                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-xl">{{ $selectedNotificationData['requirement']['description'] }}</p>
                            </div>
                            
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-500">Due Date</p>
                                <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded-xl flex items-center">
                                    <i class="fa-regular fa-calendar mr-2 text-gray-500"></i>
                                    {{ \Carbon\Carbon::parse($selectedNotificationData['requirement']['due'])->format('M d, Y g:i A') }}
                                    @if($selectedNotificationData['requirement']['due']->isPast())
                                        <span class="ml-2 px-2 py-1 text-xs text-red-700 bg-red-100 font-semibold rounded-full">Overdue</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-500">Assigned To</p>
                                <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded-xl flex items-center">
                                    <i class="fa-regular fa-user mr-2 text-gray-500"></i>
                                    {{ $selectedNotificationData['requirement']['assigned_to'] }}
                                </div>
                            </div>
                            
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-500">Status</p>
                                <div class="flex">
                                    <span class="px-4 py-2 rounded-full text-xs font-semibold inline-flex items-center
                                        @if($selectedNotificationData['requirement']['status'] === 'completed') bg-green-100 text-green-700 
                                        @elseif($selectedNotificationData['requirement']['status'] === 'pending') bg-yellow-100 text-yellow-700 
                                        @else bg-blue-100 text-blue-700 @endif">
                                        @if($selectedNotificationData['requirement']['status'] === 'completed')
                                            <i class="fa-solid fa-check mr-1"></i>
                                        @elseif($selectedNotificationData['requirement']['status'] === 'pending')
                                            <i class="fa-solid fa-clock mr-1"></i>
                                        @else
                                            <i class="fa-solid fa-circle-info mr-1"></i>
                                        @endif
                                        {{ ucfirst($selectedNotificationData['requirement']['status']) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-500">Priority</p>
                                <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded-xl flex items-center capitalize">
                                    <i class="fa-solid fa-flag mr-2 text-gray-500"></i>
                                    {{ $selectedNotificationData['requirement']['priority'] }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submission Information --}}
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                        <div class="flex items-center mb-6">
                            <div class="bg-gradient-to-r from-blue-100 to-blue-200 p-3 rounded-xl mr-4">
                                <i class="fa-solid fa-paper-plane text-blue-700 text-lg"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Submission Information</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-500">Submitted By</p>
                                <div class="bg-gray-50 p-3 rounded-xl">
                                    <p class="text-sm text-gray-900 font-semibold">{{ $selectedNotificationData['submitter']['name'] }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $selectedNotificationData['submitter']['email'] }}</p>
                                </div>
                            </div>
                            
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-500">Submitted At</p>
                                <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded-xl flex items-center">
                                    <i class="fa-regular fa-calendar-check mr-2 text-gray-500"></i>
                                    {{ $selectedNotificationData['submission']['submitted_at']->format('M d, Y g:i A') }}
                                </div>
                            </div>
                            
                            @if($selectedNotificationData['submission']['reviewed_at'])
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-gray-500">Reviewed At</p>
                                <div class="text-sm text-gray-900 bg-gray-50 p-3 rounded-xl flex items-center">
                                    <i class="fa-regular fa-eye mr-2 text-gray-500"></i>
                                    {{ $selectedNotificationData['submission']['reviewed_at']->format('M d, Y g:i A') }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Files Section --}}
                    @if(count($selectedNotificationData['files'] ?? []))
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                        <div class="flex items-center mb-6">
                            <div class="bg-gradient-to-r from-purple-100 to-purple-200 p-3 rounded-xl mr-4">
                                <i class="fa-solid fa-folder-open text-purple-700 text-lg"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">
                                Submitted Files 
                                <span class="bg-gray-100 text-gray-700 text-sm px-3 py-1 rounded-full ml-2">{{ count($selectedNotificationData['files']) }}</span>
                            </h3>
                        </div>
                        
                        <div class="space-y-4">
                            @foreach($selectedNotificationData['files'] as $file)
                            @php
                                $fileStatus = $file['status'] ?? $selectedNotificationData['submission']['status'];
                                $fileStatusLabel = match($fileStatus) {
                                    'under_review' => 'Under Review',
                                    'revision_needed' => 'Revision Needed',
                                    'rejected' => 'Rejected',
                                    'approved' => 'Approved',
                                    default => ucfirst($fileStatus),
                                };
                                $statusColor = match($fileStatus) {
                                    'approved' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    'revision_needed' => 'bg-yellow-100 text-yellow-700',
                                    default => 'bg-blue-100 text-blue-700',
                                };
                            @endphp
                            
                            <div class="border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-center justify-between p-5 bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                                    <div class="flex items-center space-x-4 min-w-0">
                                        <div class="flex-shrink-0 p-4 bg-white rounded-xl shadow-sm">
                                            @switch($file['extension'])
                                                @case('pdf') <i class="fa-regular fa-file-pdf text-red-500 text-2xl"></i> @break
                                                @case('doc') @case('docx') <i class="fa-regular fa-file-word text-blue-500 text-2xl"></i> @break
                                                @case('xls') @case('xlsx') <i class="fa-regular fa-file-excel text-green-500 text-2xl"></i> @break
                                                @case('jpg') @case('jpeg') @case('png') @case('gif') <i class="fa-regular fa-file-image text-purple-500 text-2xl"></i> @break
                                                @default <i class="fa-regular fa-file text-gray-500 text-2xl"></i>
                                            @endswitch
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate mb-1">{{ $file['name'] }}</p>
                                            <div class="flex items-center gap-3 mt-2">
                                                <span class="text-xs text-gray-500 font-semibold bg-gray-200 px-2 py-1 rounded-full">
                                                    {{ strtoupper($file['extension']) }} • {{ $file['size'] }}
                                                </span>
                                                <span class="text-xs px-3 py-1 rounded-full {{ $statusColor }} font-semibold">
                                                    {{ $fileStatusLabel }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        @if($file['is_previewable'])
                                        <a href="{{ route('file.preview', ['submission' => $file['submission_id'], 'file' => $file['id']]) }}" 
                                        target="_blank"
                                        class="p-3 text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-full transition-all duration-200 shadow-sm hover:shadow"
                                        title="Preview">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('file.download', ['submission' => $file['submission_id'], 'file' => $file['id']]) }}" 
                                        class="p-3 text-green-600 hover:text-green-700 bg-green-50 hover:bg-green-100 rounded-full transition-all duration-200 shadow-sm hover:shadow"
                                        title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                {{-- Status update form --}}
                                <div class="p-5 border-t bg-white">
                                    <form wire:submit.prevent="updateFileStatus('{{ $file['submission_id'] }}')" class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                                        
                                        {{-- Dropdown --}}
                                        <div class="w-full lg:w-1/2 space-y-2">
                                            <label for="newStatus" class="block text-sm font-semibold text-gray-700">Update Status</label>
                                            <select wire:model="newStatus" id="newStatus" 
                                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm py-3 px-4 bg-gray-50">
                                                @foreach(\App\Models\SubmittedRequirement::statuses() as $value => $label)
                                                    <option value="{{ $value }}" {{ $fileStatus == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Buttons --}}
                                        <div class="flex items-center space-x-3">
                                            <button type="button" wire:click="$set('newStatus', '')" 
                                                class="px-6 py-3 border border-gray-300 rounded-full text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 shadow-sm hover:shadow transition-all duration-200">
                                                <i class="fa-solid fa-times mr-2"></i>Cancel
                                            </button>
                                            <button type="submit" 
                                                class="px-6 py-3 border border-transparent rounded-full text-sm font-semibold text-white bg-green-600 hover:bg-green-700 shadow-sm hover:shadow transition-all duration-200">
                                                <i class="fa-solid fa-check mr-2"></i>Update
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif

            </div>
        @else
            <div class="h-full flex items-center justify-center p-6">
                <div class="text-center bg-white rounded-2xl p-12 shadow-sm border border-gray-100">
                    <div class="bg-gradient-to-r from-gray-100 to-gray-200 p-6 rounded-2xl inline-block mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">No notification selected</h3>
                    <p class="text-sm text-gray-500 font-semibold">Click on a notification from the list to view details</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirmationModal && $notificationToDelete)
        <x-modal name="delete-notification-confirmation-modal" :show="$showDeleteConfirmationModal" maxWidth="md">
            <!-- Header -->
            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white rounded-t-2xl px-6 py-5 flex items-center space-x-3">
                <div class="bg-white/20 p-2 rounded-full">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                </div>
                <h3 class="text-xl font-semibold">Confirm Deletion</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-2xl">
                <div class="space-y-4">
                    <div class="bg-red-50 p-4 rounded-xl border border-red-100">
                        <p class="text-sm text-gray-700 font-semibold">
                            Are you sure you want to delete this notification?
                        </p>
                        <p class="text-xs text-gray-600 mt-2">
                            This action cannot be undone. The notification will be permanently removed from your system.
                        </p>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeDeleteConfirmationModal" 
                        class="px-6 py-3 border border-gray-300 rounded-full text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 shadow-sm hover:shadow transition-all duration-200">
                        <i class="fa-solid fa-arrow-left mr-2"></i>Cancel
                    </button>
                    <button type="button" wire:click="confirmDeleteNotification" 
                        class="px-6 py-3 border border-transparent text-sm font-semibold rounded-full text-white bg-red-600 hover:bg-red-700 shadow-sm hover:shadow transition-all duration-200">
                        <i class="fa-solid fa-trash mr-2"></i>Delete Notification
                    </button>
                </div>
            </div>
        </x-modal>
    @endif
</div>