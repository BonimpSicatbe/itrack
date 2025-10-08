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
    <div class="w-1/3 border-r border-gray-200 overflow-y-auto bg-gray-50">
        <!-- Header -->
        <div class="p-6" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fa-solid fa-bell text-white text-2xl mr-3"></i>
                    <h3 class="text-xl font-semibold text-white">Notifications</h3>
                </div>
                @if($notifications->where('read_at', null)->count() > 0)
                    <button wire:click="markAllAsRead"
                        class="text-sm font-semibold text-green-700 bg-white hover:bg-green-50 hover:text-green-800 px-4 py-1.5 rounded-full shadow-sm transition-all duration-200">
                        <i class="fa-regular fa-envelope-open mr-2"></i>Mark all as read
                    </button>
                @else
                    <button wire:click="markAllAsUnread"
                        class="text-sm font-semibold text-green-700 bg-white hover:bg-blue-50 
                        hover:text-green-800 px-4 py-1.5 rounded-full shadow-sm transition-all duration-200">
                        <i class="fa-regular fa-envelope mr-2"></i>Mark all as unread
                    </button>
                @endif
            </div>
        </div>

        <!-- Notifications List -->
        <div class="p-3 space-y-2">
            @forelse($notifications as $notification)
                @php
                    $highlight = $notification->unread()
                        ? 'bg-white border-l-5 border-green-500 shadow-md'
                        : 'bg-gray-50 hover:bg-gray-100 border border-gray-300';
                @endphp

                <div class="relative group">
                    <div wire:click="selectNotification('{{ $notification->id }}')"
                        class="block p-4 pr-12 rounded-xl {{ $highlight }} cursor-pointer transition-all duration-200 {{ $selectedNotification === $notification->id ? 'ring-2 ring-green-500 ring-opacity-50 shadow-lg' : '' }}">
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

                    {{-- Three dots menu --}}
                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100">
                        <div class="dropdown dropdown-left">
                            <label tabindex="0"
                                class="btn btn-xs text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </label>
                            <ul tabindex="0"
                                class="dropdown-content z-[1] menu p-1 shadow-lg bg-white rounded-xl border border-gray-200 w-44">
                                <li>
                                    <a wire:click.stop="toggleNotificationReadStatus('{{ $notification->id }}')"
                                        class="text-sm py-2.5 px-3 cursor-pointer hover:bg-gray-100 rounded-lg font-semibold focus:outline-none">
                                        @if($notification->unread())
                                            <i class="fa-regular fa-envelope-open mr-2 text-green-600"></i> Mark as read
                                        @else
                                            <i class="fa-regular fa-envelope mr-2 text-blue-600"></i> Mark as unread
                                        @endif
                                    </a>
                                </li>
                                <li>
                                    <a wire:click.stop="openDeleteConfirmationModal('{{ $notification->id }}')"
                                        class="text-sm py-2.5 px-3 text-red-600 cursor-pointer hover:bg-red-100 rounded-lg font-semibold focus:outline-none">
                                        <i class="fa-regular fa-trash-can mr-2"></i> Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="p-8">
                        <i class="fa-regular fa-bell-slash text-green-600 text-7xl mb-4"></i>
                        <p class="text-gray-600 text-sm font-semibold">No notifications</p>
                        <p class="text-gray-500 text-xs mt-1">You're all caught up!</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>


    {{-- Notification Detail (Right) --}}
    <div class="w-2/3 bg-gray-50 overflow-y-auto h-[calc(100vh-6rem)]">
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
                        
                    </div>
                </div>

                {{-- Display error if submission data is missing --}}
                @if(!isset($selectedNotificationData['submissions']) && !isset($selectedNotificationData['submission']))
                    <div class="bg-red-50 border border-red-200 rounded-xl p-8 text-center shadow-sm">
                        <div class="bg-red-100 p-4 rounded-xl inline-block mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-red-800 mb-2">Submission Data Unavailable</h3>
                        <p class="text-sm text-red-600 font-semibold">The submission data for this notification is no longer available. It may have been deleted.</p>
                    </div>
                @else
                    {{-- Requirement Details --}}
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-md">
                        <!-- Header -->
                        <div class="flex items-center mb-6 border-b border-gray-100 pb-4">
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

                            <!-- Description -->
                            <div class="space-y-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Description</p>
                                <div class="bg-gray-50 p-3 rounded-xl text-sm text-gray-700 leading-relaxed shadow-inner">
                                    {{ $selectedNotificationData['requirement']['description'] }}
                                </div>
                            </div>

                            <!-- Due Date -->
                            <div class="space-y-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Due Date</p>
                                <div class="bg-gray-50 p-3 rounded-xl flex items-center text-sm text-gray-900 shadow-inner">
                                    <i class="fa-regular fa-calendar mr-2 text-gray-500"></i>
                                    {{ \Carbon\Carbon::parse($selectedNotificationData['requirement']['due'])->format('M d, Y g:i A') }}
                                    @if($selectedNotificationData['requirement']['due']->isPast())
                                        <span class="ml-2 px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                            Overdue
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Assigned To -->
                            <div class="space-y-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Assigned To</p>
                                <div class="bg-gray-50 p-3 rounded-xl flex items-center text-sm text-gray-900 shadow-inner">
                                    <i class="fa-regular fa-user mr-2 text-gray-500"></i>
                                    {{ $selectedNotificationData['requirement']['assigned_to'] }}
                                </div>
                            </div>
                        </div>
                    </div>


                    {{-- Submissions Section --}}
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                        <div class="flex items-center mb-6">
                            <div class="rounded-xl mr-2">
                                <i class="fa-solid fa-folder-open text-green-700 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Submissions</h3>
                                <p class="text-sm text-gray-600 mt-1">Submitted by: 
                                    <span class="font-semibold">{{ $selectedNotificationData['submitter']['name'] }}</span>
                                    ({{ $selectedNotificationData['submitter']['email'] }})
                                </p>
                            </div>
                        </div>
                        
                        {{-- Files List --}}
                        @if(count($selectedNotificationData['files'] ?? []))
                        <div class="space-y-4">
                            @foreach($selectedNotificationData['files'] as $file)
                            @php
                                // Get the submission status for this file
                                $fileStatus = $file['status'];
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
                                
                                // Get submission details
                                $submission = collect($selectedNotificationData['submissions'] ?? [])->firstWhere('id', $file['submission_id']);
                            @endphp
                            
                            <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200">
                                {{-- File Header with Submission Info --}}
                                <div class="bg-green-50 px-7 py-3 border-b border-gray-300">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-700">Submitted At</p>
                                            <p class="text-sm text-gray-900 flex items-center mt-1">
                                                <i class="fa-regular fa-calendar-check mr-2 text-gray-500"></i>
                                                {{ $submission['submitted_at']->format('M d, Y g:i A') ?? 'N/A' }}
                                            </p>
                                        </div>
                                        @if($submission['reviewed_at'] ?? null)
                                        <div>
                                            <p class="text-sm font-semibold text-gray-700">Reviewed At</p>
                                            <p class="text-sm text-gray-900 flex items-center mt-1">
                                                <i class="fa-regular fa-eye mr-2 text-gray-500"></i>
                                                {{ $submission['reviewed_at']->format('M d, Y g:i A') }}
                                            </p>
                                        </div>
                                        @endif
                                    </div>
                                    {{-- Course Information --}}
                                    @if($file['course'] ?? null)
                                    <div class="mt-2 pt-2 border-t border-gray-200">
                                        <p class="text-sm font-semibold text-gray-700">Course</p>
                                        <p class="text-sm text-gray-900 flex items-center mt-1">
                                            <i class="fa-solid fa-book-open mr-2 text-gray-500"></i>
                                            {{ $file['course']['course_code'] }} - {{ $file['course']['course_name'] }}
                                        </p>
                                    </div>
                                    @endif
                                </div>
                                
                                {{-- File Details --}}
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
                                    <div class="flex space-x-1">
                                        @if($file['is_previewable'])
                                        <a href="{{ route('file.preview', ['submission' => $file['submission_id'], 'file' => $file['id']]) }}" 
                                        target="_blank"
                                        class="p-3 text-blue-600 hover:text-blue-700 hover:bg-blue-100 rounded-xl"
                                        title="Preview">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('file.download', ['submission' => $file['submission_id'], 'file' => $file['id']]) }}" 
                                        class="p-3 text-green-600 hover:text-green-700 hover:bg-green-100 rounded-xl"
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
                                                class="px-5 py-2 border border-gray-300 rounded-full text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 shadow-sm hover:shadow transition-all duration-200">
                                                <i class="fa-solid fa-times mr-2"></i>Cancel
                                            </button>
                                            <button type="submit" 
                                                class="px-5 py-2 border border-transparent rounded-full text-sm font-semibold text-white bg-green-600 hover:bg-green-700 shadow-sm hover:shadow transition-all duration-200">
                                                <i class="fa-solid fa-check mr-2"></i>Update
                                            </button>
                                        </div>
                                    </form>
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
                                    <p class="text-gray-500 text-xs mt-1">This submission doesn't contain any files</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        @else
            <div class="h-full flex items-center justify-center p-6">
                <div class="text-center rounded-xl p-12">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">No notification selected</h3>
                    <p class="text-sm text-gray-500 font-semibold">Click on a notification from the list to view details</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirmationModal && $notificationToDelete)
        <x-modal name="delete-notification-confirmation-modal" :show="$showDeleteConfirmationModal" maxWidth="md">
            <div class="bg-red-600 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Confirm Deletion</h3>
            </div>

            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Are you sure you want to delete this notification?
                    </p>
                    <p class="text-sm text-gray-600">
                        This action cannot be undone. The notification will be permanently removed.
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeDeleteConfirmationModal" 
                            class="px-4 py-2 border border-gray-300 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="confirmDeleteNotification" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-full text-sm font-medium cursor-pointer"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="confirmDeleteNotification">
                            <i class="fa-solid fa-trash mr-2"></i> Delete
                        </span>
                        <span wire:loading wire:target="confirmDeleteNotification">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif
</div>