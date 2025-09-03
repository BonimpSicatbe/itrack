@if (session()->has('message'))
    <div class="fixed top-4 right-4 z-50">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    </div>
@endif

<div class="flex h-full" >
    {{-- Notifications List (Left) --}}
    <div class="w-1/3 border-r overflow-y-auto rounded-tl-2xl rounded-bl-2xl" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
        <div class="p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-white">Notifications</h3>
                @if($notifications->where('read_at', null)->count() > 0)
                    <button wire:click="markAllAsRead" class="text-sm text-blue-500 hover:text-white hover:bg-blue-500 bg-white p-3 pl-4 pr-4 rounded-full">
                        Mark all read
                    </button>
                @else
                    <button wire:click="markAllAsUnread" class="text-sm text-blue-500 hover:text-white hover:bg-blue-500 bg-white p-3 pl-4 pr-4 rounded-full">
                        Mark all unread
                    </button>
                @endif
            </div>

            <div class="space-y-2">
                @forelse($notifications as $notification)
                    @php
                        $highlight = $notification->unread() ? 'bg-blue-50 border-l-4 border-blue-500' : 'bg-white';
                    @endphp

                    <div class="relative group">
                        <div wire:click="selectNotification('{{ $notification->id }}')"
                            class="block p-3 pr-8 rounded-lg {{ $highlight }} hover:shadow cursor-pointer {{ $selectedNotification === $notification->id ? 'ring-2 ring-blue-500' : '' }}">
                            <div class="flex justify-between">
                                <p class="text-sm font-medium truncate">
                                    {{ $notification->data['message'] ?? 'New notification' }}
                                </p>
                                @if($notification->unread())
                                    <span class="ml-2 h-2 w-2 rounded-full bg-blue-500"></span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                        
                        {{-- Three dots menu --}}
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <div class="dropdown dropdown-left">
                                <label tabindex="0" class="btn btn-xs btn-ghost text-gray-500 hover:text-gray-700">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </label>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-40">
                                    <li>
                                        <a wire:click.stop="toggleNotificationReadStatus('{{ $notification->id }}')" class="text-xs py-2 cursor-pointer">
                                            @if($notification->unread())
                                                <i class="fa-regular fa-envelope-open mr-2"></i> Mark as read
                                            @else
                                                <i class="fa-regular fa-envelope mr-2"></i> Mark as unread
                                            @endif
                                        </a>
                                    </li>
                                    <li>
                                        <a wire:click.stop="openDeleteConfirmationModal('{{ $notification->id }}')" 
                                        class="text-xs py-2 text-red-600 cursor-pointer">
                                            <i class="fa-regular fa-trash-can mr-2"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No notifications</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Notification Detail (Right) --}}
    <div class="w-2/3 p-6 overflow-y-auto h-[calc(100vh-6rem)]">
        @if ($selectedNotification && $selectedNotificationData)
            <div class="space-y-6">

                {{-- Header --}}
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500">
                            Received: {{ $selectedNotificationData['created_at']->format('M d, Y g:i A') }}
                        </p>
                    </div>
                </div>

                {{-- Display error if submission data is missing --}}
                @if(!isset($selectedNotificationData['submission']))
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-red-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-red-800 mb-2">Submission Data Unavailable</h3>
                        <p class="text-red-600">The submission data for this notification is no longer available. It may have been deleted.</p>
                    </div>
                @else
                    {{-- Requirement Details --}}
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                        <h3 class="text-xl font-semibold mb-5 text-1B512D">Requirement Details</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">
                            <div>
                                <p class="font-semibold text-gray-500">Name</p>
                                <p class="text-gray-800 mt-1">{{ $selectedNotificationData['requirement']['name'] }}</p>
                            </div>
                            
                            <div>
                                <p class="font-semibold text-gray-500">Description</p>
                                <p class="text-gray-800 mt-1">{{ $selectedNotificationData['requirement']['description'] }}</p>
                            </div>
                            
                            <div>
                                <p class="font-semibold text-gray-500">Due Date</p>
                                <p class="text-gray-800 mt-1">
                                    {{ \Carbon\Carbon::parse($selectedNotificationData['requirement']['due'])->format('M d, Y g:i A') }}
                                    @if($selectedNotificationData['requirement']['due']->isPast())
                                        <span class="ml-2 text-xs text-red-500 font-semibold">(Overdue)</span>
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <p class="font-semibold text-gray-500">Assigned To</p>
                                <p class="text-gray-800 mt-1">{{ $selectedNotificationData['requirement']['assigned_to'] }}</p>
                            </div>
                            
                            <div>
                                <p class="font-semibold text-gray-500">Status</p>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold mt-1 inline-block
                                    @if($selectedNotificationData['requirement']['status'] === 'completed') bg-green-100 text-green-700 
                                    @elseif($selectedNotificationData['requirement']['status'] === 'pending') bg-yellow-100 text-yellow-700 
                                    @else bg-blue-100 text-blue-700 @endif">
                                    {{ ucfirst($selectedNotificationData['requirement']['status']) }}
                                </span>
                            </div>
                            
                            <div>
                                <p class="font-semibold text-gray-500">Priority</p>
                                <p class="text-gray-800 capitalize mt-1">{{ $selectedNotificationData['requirement']['priority'] }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Submission Information --}}
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                        <h3 class="text-xl font-semibold mb-5 text-1B512D">Submission Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 text-sm">
                            <div>
                                <p class="font-semibold text-gray-500">Submitted By</p>
                                <p class="text-gray-800 mt-1">{{ $selectedNotificationData['submitter']['name'] }}</p>
                                <p class="text-gray-600 text-sm mt-1">{{ $selectedNotificationData['submitter']['email'] }}</p>
                            </div>
                            
                            <div>
                                <p class="font-semibold text-gray-500">Submitted At</p>
                                <p class="text-gray-800 mt-1">
                                    {{ $selectedNotificationData['submission']['submitted_at']->format('M d, Y g:i A') }}
                                </p>
                            </div>
                            
                            @if($selectedNotificationData['submission']['reviewed_at'])
                            <div>
                                <p class="font-semibold text-gray-500">Reviewed At</p>
                                <p class="text-gray-800 mt-1">
                                    {{ $selectedNotificationData['submission']['reviewed_at']->format('M d, Y g:i A') }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Files Section --}}
                    @if(count($selectedNotificationData['files'] ?? []))
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                        <h3 class="text-xl font-semibold mb-5 text-1B512D">
                            Submitted Files ({{ count($selectedNotificationData['files']) }})
                        </h3>
                        
                        <div class="space-y-5">
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
                            
                            <div class="border rounded-xl overflow-hidden shadow-sm">
                                <div class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center space-x-4 min-w-0">
                                        <div class="flex-shrink-0 p-3 bg-gray-100 rounded-xl">
                                            @switch($file['extension'])
                                                @case('pdf') <i class="fa-regular fa-file-pdf text-red-500 text-xl"></i> @break
                                                @case('doc') @case('docx') <i class="fa-regular fa-file-word text-blue-500 text-xl"></i> @break
                                                @case('xls') @case('xlsx') <i class="fa-regular fa-file-excel text-green-500 text-xl"></i> @break
                                                @case('jpg') @case('jpeg') @case('png') @case('gif') <i class="fa-regular fa-file-image text-purple-500 text-xl"></i> @break
                                                @default <i class="fa-regular fa-file text-gray-500 text-xl"></i>
                                            @endswitch
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $file['name'] }}</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-xs text-gray-500">
                                                    {{ strtoupper($file['extension']) }} • {{ $file['size'] }}
                                                </span>
                                                <span class="text-xs px-2 py-1 rounded-full {{ $statusColor }} font-semibold">
                                                    {{ $fileStatusLabel }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        @if($file['is_previewable'])
                                        <a href="{{ route('file.preview', ['submission' => $file['submission_id'], 'file' => $file['id']]) }}" 
                                        target="_blank"
                                        class="p-2 text-1C7C54 hover:text-1B512D rounded-full hover:bg-73E2A7/20 transition-colors"
                                        title="Preview">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('file.download', ['submission' => $file['submission_id'], 'file' => $file['id']]) }}" 
                                        class="p-2 text-1C7C54 hover:text-1B512D rounded-full hover:bg-73E2A7/20 transition-colors"
                                        title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                {{-- Status update form --}}
                                <div class="p-4 border-t bg-gray-50">
                                    <form wire:submit.prevent="updateFileStatus('{{ $file['submission_id'] }}')" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                        
                                        {{-- Dropdown --}}
                                        <div class="w-full md:w-1/2">
                                            <label for="newStatus" class="block text-sm font-semibold text-gray-700 mb-2">Update Status</label>
                                            <select wire:model="newStatus" id="newStatus" 
                                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-1C7C54 focus:ring-1C7C54 text-sm py-2.5">
                                                @foreach(\App\Models\SubmittedRequirement::statuses() as $value => $label)
                                                    <option value="{{ $value }}" {{ $fileStatus == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Buttons --}}
                                        <div class="flex items-center space-x-2">
                                            <button type="button" wire:click="$set('newStatus', '')" 
                                                class="px-4 py-2 border border-gray-300 rounded-full text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                                Cancel
                                            </button>
                                            <button type="submit" 
                                                class="px-5 py-2 border border-transparent rounded-full text-sm font-semibold text-white bg-1C7C54 hover:bg-1B512D transition-colors">
                                                Update
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
            <div class="h-full flex items-center justify-center">
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <h3 class="mt-3 text-lg font-semibold text-gray-900">No notification selected</h3>
                    <p class="mt-2 text-sm text-gray-500">Click on a notification from the list to view details</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteConfirmationModal && $notificationToDelete)
        <x-modal name="delete-notification-confirmation-modal" :show="$showDeleteConfirmationModal" maxWidth="md">
            <!-- Header -->
            <div class="bg-red-600 text-white rounded-t-2xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Confirm Deletion</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-2xl">
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Are you sure you want to delete this notification?
                    </p>
                    <p class="text-sm text-gray-600">
                        This action cannot be undone. The notification will be permanently removed.
                    </p>
                </div>

                <!-- Action buttons -->
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeDeleteConfirmationModal" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="confirmDeleteNotification" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 cursor-pointer">
                        <i class="fa-solid fa-trash mr-2"></i> Delete Notification
                    </button>
                </div>
            </div>
        </x-modal>
    @endif
</div>