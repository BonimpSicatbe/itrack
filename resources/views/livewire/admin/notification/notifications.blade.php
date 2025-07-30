@if (session()->has('message'))
    <div class="fixed top-4 right-4 z-50">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    </div>
@endif
<div class="flex h-full">
    {{-- Notifications List (Left) --}}
    <div class="w-1/3 border-r overflow-y-auto">
        <div class="p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Notifications</h3>
                <button wire:click="markAllAsRead" class="text-sm text-blue-500 hover:text-blue-700">
                    Mark all read
                </button>
            </div>

            <div class="space-y-2">
                @forelse($notifications as $notification)
                    @php
                        $highlight = $notification->unread() ? 'bg-blue-50 border-l-4 border-blue-500' : 'bg-white';
                    @endphp

                    <div wire:click="selectNotification('{{ $notification->id }}')"
                         class="block p-3 rounded-lg {{ $highlight }} hover:shadow cursor-pointer {{ $selectedNotification === $notification->id ? 'ring-2 ring-blue-500' : '' }}">
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
                @empty
                    <p class="text-gray-500 text-center py-4">No notifications</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Notification Detail (Right) --}}
    <div class="w-2/3 p-6 overflow-y-auto h-[calc(100vh-12rem)]">
        @if ($selectedNotification && $selectedNotificationData)
            <div class="space-y-6">
                {{-- Header --}}
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-xl font-bold">Requirement Submission</h2>
                        <p class="text-sm text-gray-500">
                            Notification received: {{ $selectedNotificationData['created_at']->format('M d, Y g:i A') }}
                        </p>
                    </div>
                </div>

                <div class="border-t border-gray-200 my-2"></div>

                {{-- Notification Message --}}
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm font-medium text-blue-800">{{ $selectedNotificationData['message'] }}</p>
                </div>

                {{-- Requirement Details --}}
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Requirement Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="font-medium text-gray-500">Name</p>
                            <p class="text-gray-800">{{ $selectedNotificationData['requirement']['name'] }}</p>
                        </div>
                        
                        <div>
                            <p class="font-medium text-gray-500">Description</p>
                            <p class="text-gray-800">{{ $selectedNotificationData['requirement']['description'] }}</p>
                        </div>
                        
                        <div>
                            <p class="font-medium text-gray-500">Due Date</p>
                            <p class="text-gray-800">
                                {{ \Carbon\Carbon::parse($selectedNotificationData['requirement']['due'])->format('M d, Y g:i A') }}
                                @if($selectedNotificationData['requirement']['due']->isPast())
                                    <span class="ml-2 text-xs text-red-500">(Overdue)</span>
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <p class="font-medium text-gray-500">Assigned To</p>
                            <p class="text-gray-800">{{ $selectedNotificationData['requirement']['assigned_to'] }}</p>
                        </div>
                        
                        <div>
                            <p class="font-medium text-gray-500">Status</p>
                            <p class="text-gray-800 capitalize">{{ $selectedNotificationData['requirement']['status'] }}</p>
                        </div>
                        
                        <div>
                            <p class="font-medium text-gray-500">Priority</p>
                            <p class="text-gray-800 capitalize">{{ $selectedNotificationData['requirement']['priority'] }}</p>
                        </div>
                    </div>
                </div>

                

                {{-- User Information --}}
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Submission Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="font-medium text-gray-500">Submitted By</p>
                            <p class="text-gray-800">{{ $selectedNotificationData['submitter']['name'] }}</p>
                            <p class="text-gray-600 text-sm">{{ $selectedNotificationData['submitter']['email'] }}</p>
                        </div>
                        
                        <div>
                            <p class="font-medium text-gray-500">Submitted At</p>
                            <p class="text-gray-800">
                                {{ $selectedNotificationData['submission']['submitted_at']->format('M d, Y g:i A') }}
                            </p>
                        </div>
                        
                        @if($selectedNotificationData['submission']['reviewed_at'])
                        <div>
                            <p class="font-medium text-gray-500">Reviewed At</p>
                            <p class="text-gray-800">
                                {{ $selectedNotificationData['submission']['reviewed_at']->format('M d, Y g:i A') }}
                            </p>
                        </div>
                        @endif
                        
                        @if($selectedNotificationData['submission']['admin_notes'])
                        <div class="col-span-3">
                            <p class="font-medium text-gray-500">Admin Notes</p>
                            <p class="text-gray-800 whitespace-pre-line">{{ $selectedNotificationData['submission']['admin_notes'] }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Files Section --}}
                @if(session('message'))
                    <div class="alert alert-success">
                        {{ session('message') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if(count($selectedNotificationData['files'] ?? []))
                <div class="bg-white p-4 rounded-lg border border-gray-200 mt-4">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">
                        Submitted Files ({{ count($selectedNotificationData['files']) }})
                    </h3>
                    
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
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'revision_needed' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-blue-100 text-blue-800',
                            };
                        @endphp
                        
                        <div class="border rounded-lg overflow-hidden">
                            <div class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-3 min-w-0">
                                    <div class="flex-shrink-0 p-2 bg-gray-100 rounded-lg">
                                        @switch($file['extension'])
                                            @case('pdf') <i class="fa-regular fa-file-pdf text-red-500"></i> @break
                                            @case('doc') @case('docx') <i class="fa-regular fa-file-word text-blue-500"></i> @break
                                            @case('xls') @case('xlsx') <i class="fa-regular fa-file-excel text-green-500"></i> @break
                                            @case('jpg') @case('jpeg') @case('png') @case('gif') <i class="fa-regular fa-file-image text-purple-500"></i> @break
                                            @default <i class="fa-regular fa-file text-gray-500"></i>
                                        @endswitch
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $file['name'] }}</p>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">
                                                {{ strtoupper($file['extension']) }} • {{ $file['size'] }}
                                            </span>
                                            <span class="text-xs px-1.5 py-0.5 rounded-full {{ $statusColor }}">
                                                {{ $fileStatusLabel }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    @if($file['is_previewable'])
                                    <a href="{{ route('file.preview', ['submission' => $file['submission_id'], 'file' => $file['id']]) }}" 
                                    target="_blank"
                                    class="p-2 text-blue-600 hover:text-blue-800 rounded hover:bg-blue-50"
                                    title="Preview">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @endif
                                    <a href="{{ route('file.download', ['submission' => $file['submission_id'], 'file' => $file['id']]) }}" 
                                    class="p-2 text-blue-600 hover:text-blue-800 rounded hover:bg-blue-50"
                                    title="Download">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                </div>
                            </div>
                            
                            {{-- Status update form --}}
                            <div class="p-4 border-t">
                                <form wire:submit.prevent="updateFileStatus('{{ $file['submission_id'] }}')">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label for="newStatus" class="block text-sm font-medium text-gray-700 mb-1">Update Status</label>
                                            <select wire:model="newStatus" id="newStatus" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                                @foreach(\App\Models\SubmittedRequirement::statuses() as $value => $label)
                                                    <option value="{{ $value }}" {{ $fileStatus == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="md:col-span-2">
                                            <label for="adminNotes" class="block text-sm font-medium text-gray-700 mb-1">Admin Notes</label>
                                            <textarea wire:model="adminNotes" id="adminNotes" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Add review comments...">{{ $file['admin_notes'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 flex justify-end space-x-2">
                                        <button type="button" wire:click="$set('newStatus', '')" class="px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Cancel
                                        </button>
                                        <button type="submit" class="px-3 py-1.5 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Update Status
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        @else
            <div class="h-full flex items-center justify-center">
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No notification selected</h3>
                    <p class="mt-1 text-sm text-gray-500">Click on a notification from the list to view details</p>
                </div>
            </div>
        @endif
    </div>
</div>