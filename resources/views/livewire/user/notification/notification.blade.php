<div class="flex h-full">
    {{-- Notifications List (Left) --}}
    <div class="w-1/3 border-r overflow-y-auto">
        <div class="p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Your Notifications</h3>
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
                @isset($selectedNotificationData['requirement'])
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Requirement Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="font-medium text-gray-500">Name</p>
                            <p class="text-gray-800">{{ $selectedNotificationData['requirement']['name'] ?? 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <p class="font-medium text-gray-500">Description</p>
                            <p class="text-gray-800">{{ $selectedNotificationData['requirement']['description'] ?? 'N/A' }}</p>
                        </div>
                        
                        @isset($selectedNotificationData['requirement']['due'])
                        <div>
                            <p class="font-medium text-gray-500">Due Date</p>
                            <p class="text-gray-800">
                                {{ \Carbon\Carbon::parse($selectedNotificationData['requirement']['due'])->format('M d, Y g:i A') }}
                                @if($selectedNotificationData['requirement']['due']->isPast())
                                    <span class="ml-2 text-xs text-red-500">(Overdue)</span>
                                @endif
                            </p>
                        </div>
                        @endisset
                        
                        @isset($selectedNotificationData['requirement']['assigned_to'])
                        <div>
                            <p class="font-medium text-gray-500">Assigned To</p>
                            <p class="text-gray-800">{{ $selectedNotificationData['requirement']['assigned_to'] }}</p>
                        </div>
                        @endisset
                    </div>
                </div>
                @endisset

                {{-- Submission Action Button --}}
                @isset($selectedNotificationData['requirement'])
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Submit Your Requirement</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Please upload the required files for this requirement. Make sure to review all instructions before submitting.
                    </p>
                    <button 
                        wire:click="openSubmissionModal" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center"
                    >
                        <i class="fa-solid fa-upload mr-2"></i>
                        Submit Requirement
                    </button>
                </div>
                @endisset

                {{-- Files Section --}}
                @if(count($selectedNotificationData['files'] ?? []))
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">
                        Requirement Files ({{ count($selectedNotificationData['files']) }})
                    </h3>
                    
                    <div class="space-y-4">
                        @foreach($selectedNotificationData['files'] as $file)
                        <div class="border rounded-lg overflow-hidden">
                            <div class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-3 min-w-0">
                                    <div class="flex-shrink-0 p-2 bg-gray-100 rounded-lg">
                                        @switch($file['extension'] ?? '')
                                            @case('pdf') <i class="fa-regular fa-file-pdf text-red-500"></i> @break
                                            @case('doc') @case('docx') <i class="fa-regular fa-file-word text-blue-500"></i> @break
                                            @case('xls') @case('xlsx') <i class="fa-regular fa-file-excel text-green-500"></i> @break
                                            @case('jpg') @case('jpeg') @case('png') @case('gif') <i class="fa-regular fa-file-image text-purple-500"></i> @break
                                            @default <i class="fa-regular fa-file text-gray-500"></i>
                                        @endswitch
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $file['name'] ?? 'Unknown file' }}</p>
                                        <div class="flex items-center gap-2">
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
                                       class="p-2 text-blue-600 hover:text-blue-800 rounded hover:bg-blue-50"
                                       title="Preview">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @endif
                                    @if(isset($file['id']))
                                    <a href="{{ route('user.file.download', $file['id']) }}" 
                                       class="p-2 text-blue-600 hover:text-blue-800 rounded hover:bg-blue-50"
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

    {{-- Submission Modal --}}
    @if($showSubmissionModal)
    <div class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-xl font-semibold text-gray-900">
                    Submit Requirement: {{ $currentRequirementName }}
                </h3>
                <button wire:click="closeSubmissionModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-6">
                {{-- File Upload --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Upload Files
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload files</span>
                                    <input wire:model="uploadedFiles" type="file" class="sr-only" multiple>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PDF, DOC, DOCX, JPG, PNG up to 10MB
                            </p>
                        </div>
                    </div>
                    
                    {{-- Uploaded Files List --}}
                    @if(count($uploadedFiles) > 0)
                    <div class="mt-4 space-y-2">
                        <p class="text-sm font-medium text-gray-700">Selected files:</p>
                        @foreach($uploadedFiles as $index => $file)
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-md">
                            <div class="flex items-center space-x-2">
                                <i class="fa-regular fa-file text-gray-400"></i>
                                <span class="text-sm text-gray-600">{{ $file->getClientOriginalName() }}</span>
                                <span class="text-xs text-gray-400">({{ round($file->getSize() / 1024, 1) }} KB)</span>
                            </div>
                            <button wire:click="removeUploadedFile({{ $index }})" class="text-red-500 hover:text-red-700">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    
                    @error('uploadedFiles.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label for="submissionNotes" class="block text-sm font-medium text-gray-700 mb-2">
                        Additional Notes (Optional)
                    </label>
                    <textarea 
                        wire:model="submissionNotes" 
                        id="submissionNotes" 
                        rows="4" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Add any additional information about your submission..."
                    ></textarea>
                    @error('submissionNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex justify-end space-x-3 p-6 border-t bg-gray-50 rounded-b-lg">
                <button 
                    wire:click="closeSubmissionModal" 
                    type="button" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Cancel
                </button>
                <button 
                    wire:click="submitRequirement" 
                    type="button" 
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                    @if(count($uploadedFiles) === 0) disabled @endif
                >
                    Submit Requirement
                </button>
            </div>
        </div>
    </div>
    @endif
</div>