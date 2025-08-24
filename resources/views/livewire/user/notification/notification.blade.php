<div class="flex h-full bg-gray-50">
    {{-- Notifications List (Left) --}}
    <div class="w-1/3 border-r border-gray-200 bg-white overflow-y-auto">
        <div class="p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Your Notifications</h3>
                <button wire:click="markAllAsRead" class="text-sm text-green-600 hover:text-green-800 font-medium">
                    Mark all read
                </button>
            </div>

            <div class="space-y-2">
                @forelse($notifications as $notification)
                    @php
                        $highlight = $notification->unread() ? 'bg-green-50 border-l-4 border-green-500' : 'bg-white border-l-4 border-transparent';
                    @endphp

                    <div wire:click="selectNotification('{{ $notification->id }}')"
                         class="block p-3 rounded-lg {{ $highlight }} hover:bg-green-50 cursor-pointer transition-colors duration-200 {{ $selectedNotification === $notification->id ? 'ring-2 ring-green-500' : '' }}">
                        <div class="flex justify-between items-start">
                            <p class="text-sm font-medium text-gray-800 line-clamp-2">
                                {{ $notification->data['message'] ?? 'New notification' }}
                            </p>
                            @if($notification->unread())
                                <span class="ml-2 h-2 w-2 rounded-full bg-green-500 flex-shrink-0 mt-1"></span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-3">
                            <i class="fa-regular fa-bell text-green-600 text-xl"></i>
                        </div>
                        <p class="text-gray-500">No notifications yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Notification Detail (Right) --}}
    <div class="w-2/3 p-6 overflow-y-auto bg-gray-50">
        @if ($selectedNotification && $selectedNotificationData)
            <div class="max-w-4xl mx-auto space-y-6">
                {{-- Header --}}
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Requirement Submission</h2>
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fa-regular fa-clock mr-1"></i>
                                Notification received: {{ $selectedNotificationData['created_at']->format('M d, Y g:i A') }}
                            </p>
                        </div>
                        <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                            <i class="fa-solid fa-bell mr-1"></i>
                            New Requirement
                        </div>
                    </div>
                </div>

                {{-- Notification Message --}}
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fa-solid fa-info-circle text-green-600"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ $selectedNotificationData['message'] }}</p>
                        </div>
                    </div>
                </div>

                {{-- Requirement Details --}}
                @isset($selectedNotificationData['requirement'])
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fa-solid fa-file-lines text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Requirement Details</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="space-y-1">
                            <p class="font-medium text-gray-500 text-xs uppercase tracking-wide">Name</p>
                            <p class="text-gray-800 font-medium">{{ $selectedNotificationData['requirement']['name'] ?? 'N/A' }}</p>
                        </div>
                        
                        @isset($selectedNotificationData['requirement']['due'])
                        <div class="space-y-1">
                            <p class="font-medium text-gray-500 text-xs uppercase tracking-wide">Due Date</p>
                            <p class="text-gray-800">
                                {{ \Carbon\Carbon::parse($selectedNotificationData['requirement']['due'])->format('M d, Y g:i A') }}
                                @if($selectedNotificationData['requirement']['due']->isPast())
                                    <span class="ml-2 text-xs text-red-500 font-medium">(Overdue)</span>
                                @else
                                    <span class="ml-2 text-xs text-green-500 font-medium">(Due in {{ $selectedNotificationData['requirement']['due']->diffForHumans() }})</span>
                                @endif
                            </p>
                        </div>
                        @endisset
                        
                        @isset($selectedNotificationData['requirement']['description'])
                        <div class="md:col-span-2 space-y-1">
                            <p class="font-medium text-gray-500 text-xs uppercase tracking-wide">Description</p>
                            <p class="text-gray-800">{{ $selectedNotificationData['requirement']['description'] }}</p>
                        </div>
                        @endisset
                        
                        @isset($selectedNotificationData['requirement']['assigned_to'])
                        <div class="md:col-span-2 space-y-1">
                            <p class="font-medium text-gray-500 text-xs uppercase tracking-wide">Assigned To</p>
                            <p class="text-gray-800">{{ $selectedNotificationData['requirement']['assigned_to'] }}</p>
                        </div>
                        @endisset
                    </div>
                </div>
                @endisset

                {{-- Files Section --}}
                @if(count($selectedNotificationData['files'] ?? []))
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fa-solid fa-paperclip text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">
                            Requirement File ({{ count($selectedNotificationData['files']) }})
                        </h3>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($selectedNotificationData['files'] as $file)
                        <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                                <div class="flex items-center space-x-4 min-w-0 flex-1">
                                    <div class="flex-shrink-0 w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                                        @switch($file['extension'] ?? '')
                                            @case('pdf') <i class="fa-regular fa-file-pdf text-red-500"></i> @break
                                            @case('doc') @case('docx') <i class="fa-regular fa-file-word text-blue-500"></i> @break
                                            @case('xls') @case('xlsx') <i class="fa-regular fa-file-excel text-green-500"></i> @break
                                            @case('jpg') @case('jpeg') @case('png') @case('gif') <i class="fa-regular fa-file-image text-purple-500"></i> @break
                                            @default <i class="fa-regular fa-file text-gray-500"></i>
                                        @endswitch
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $file['name'] ?? 'Unknown file' }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-gray-500">
                                                {{ strtoupper($file['extension'] ?? '') }} • {{ $file['size'] ?? '0 KB' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    @if(($file['is_previewable'] ?? false) && isset($file['id']))
                                    <a href="{{ route('user.file.preview', $file['id']) }}" 
                                       target="_blank"
                                       class="p-2 text-green-600 hover:text-green-800 rounded hover:bg-green-50 transition-colors"
                                       title="Preview">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @endif
                                    @if(isset($file['id']))
                                    <a href="{{ route('user.file.download', $file['id']) }}" 
                                       class="p-2 text-green-600 hover:text-green-800 rounded hover:bg-green-50 transition-colors"
                                       title="Download">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Submission Action Button --}}
                @isset($selectedNotificationData['requirement'])
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fa-solid fa-upload text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Submit Your Requirement</h3>
                    </div>
                    <p class="text-sm text-gray-600 mb-6">
                        Please upload the required files for this requirement. Make sure to review all instructions before submitting.
                    </p>
                    <a 
                        href="{{ route('user.requirements') }}" 
                        class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium shadow-sm hover:shadow-md"
                    >
                        <i class="fa-solid fa-upload mr-3"></i>
                        Submit Requirement
                    </a>
                </div>
                @endisset
            </div>
        @else
            <div class="h-full flex items-center justify-center">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                        <i class="fa-regular fa-bell text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No notification selected</h3>
                    <p class="text-gray-500 mt-1">Select a notification from the list to view details</p>
                </div>
            </div>
        @endif
    </div>
</div>