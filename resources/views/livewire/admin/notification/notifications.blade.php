<div class="flex flex-row grow bg-white rounded-lg overflow-hidden">
    {{-- Notifications List (Left) --}}
    <div class="flex flex-col gap-4 grow overflow-auto">
        @forelse ($notifications as $notification)
            <a href="#" wire:click.prevent="selectNotification('{{ $notification->id }}')"
                wire:key="notif-{{ $notification->id }}"
                class="p-2 shadow rounded-lg cursor-pointer @if ($selectedNotification === $notification->id) bg-blue-100 @endif">
                <div class="font-semibold text-sm">
                    {{ $notification->data['requirement']['name'] ?? 'Unknown Project Name' }}</div>
                <div class="text-xs text-gray-500 font-bold truncate">{{ $notification->data['message'] ?? '' }}
                </div>
            </a>
        @empty
            <div class="text-gray-500">No new notifications.</div>
        @endforelse
    </div>

    <div class="divider divider-horizontal p-0 m-0"></div>

    {{-- Requirement Detail (Right) --}}
    <div class="min-w-2/3 h-full">
        @if ($selectedNotification)
            @php
                $selected = collect($notifications)->firstWhere('id', $selectedNotification);
            @endphp

            @if ($selected)
                <div class="flex flex-col gap-4 px-4 h-full">
                    {{-- header --}}
                    <div class="flex flex-col gap-1">
                        <div class="flex flex-row items-center justify-between">
                            <div class="text-lg font-bold">
                                {{ $selected->data['requirement']['name'] ?? 'Unknown Requirement Name' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($selected->created_at)->format('F d, Y, g:ia') }}
                            </div>
                        </div>
                        <div class="flex flex-row items-center justify-between">
                            <div class="text-xs text-gray-500">
                                <span class="font-bold">To:</span>
                                {{ $selected->data['requirement']['assigned_to'] ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                <span class="font-bold">From:</span>
                                {{ App\Models\User::where('id', $selected->data['requirement']['created_by'])->first()->full_name ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <div class="divider p-0 m-0"></div>

                    {{-- body --}}
                    <div class="flex flex-col gap-8 h-full overflow-y-auto">
                        {{-- requirement details --}}
                        <div>
                            <div class="text-lg font-bold">
                                {{ $selected->data['requirement']['name'] ?? 'Requirement Name' }}
                            </div>
                            <div class="text-sm">
                                {{ $selected->data['requirement']['description'] ?? 'No description provided.' }}
                            </div>
                            <div class="text-xs">
                                Due:
                                {{ isset($selected->data['requirement']['due']) ? \Carbon\Carbon::parse($selected->data['requirement']['due'])->format('F d, Y, g:ia') : 'No due date' }}
                            </div>
                        </div>

                        {{-- requirement required files --}}
                        <div>
                            <div class="mt-4 text-sm font-bold">Required Files:</div>
                            <table class="table table-sm w-full">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $selectedRequirement = $selected->data['requirement']['id'] ?? [];
                                        $mediaFiles = [];
                                        if ($selectedRequirement) {
                                            $requirementModel = \App\Models\Requirement::find($selectedRequirement);
                                            if ($requirementModel && method_exists($requirementModel, 'getMedia')) {
                                                $mediaFiles = $requirementModel->getMedia('requirements');
                                            }
                                        }
                                    @endphp
                                    @forelse ($mediaFiles as $media)
                                        <tr>
                                            <td>
                                                <a href="{{ $media->getFullUrl() }}" target="_blank"
                                                    class="text-indigo-500 hover:underline">
                                                    {{ $media->name }}
                                                </a>
                                            </td>
                                            <td class="text-xs text-gray-400">
                                                {{ $media->mime_type }}
                                                ({{ \Illuminate\Support\Str::upper($media->extension) }})
                                            </td>
                                            <td class="text-xs text-gray-400">
                                                {{ (function ($size) {
                                                    if ($size >= 1073741824) {
                                                        return number_format($size / 1073741824, 2) . ' GB';
                                                    } elseif ($size >= 1048576) {
                                                        return number_format($size / 1048576, 2) . ' MB';
                                                    } elseif ($size >= 1024) {
                                                        return number_format($size / 1024, 2) . ' KB';
                                                    }
                                                    return $size . ' bytes';
                                                })($media->size) }}
                                            </td>
                                            <td>
                                                <a href="{{ $media->getFullUrl() }}" download
                                                    class="text-blue-500 hover:underline">Download</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-sm text-gray-400 text-center">No required
                                                files listed.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- requirement submission field --}}
                        <div>
                            <div class="mt-4 text-sm font-bold">Submit Files:</div>
                            @forelse ($selected->data['requirement']['files'] ?? [] as $idx => $file)
                                <fieldset class="fieldset mb-2">
                                    <legend class="fieldset-legend font-normal">
                                        <span class="font-bold">{{ $file['label'] ?? 'File' }}:</span>
                                        {{ $file['description'] ?? 'File Name' }}
                                    </legend>
                                    <input type="file" name="submitted_files[{{ $idx }}]"
                                        id="submitted_file_{{ $idx }}" class="file-input file-input-sm w-full"
                                        wire:model="submitted_files.{{ $idx }}" />
                                    @error('submitted_files.' . $idx)
                                        <label class="label text-red-500">{{ $message }}</label>
                                    @enderror
                                </fieldset>
                            @empty
                                <div class="text-xs text-gray-400">No files to submit.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @else
                <div class="text-gray-500 font-bold uppercase text-xl">Requirement details not found.</div>
            @endif
        @else
            <div class="text-gray-500 font-bold uppercase text-xl">Select a notification to view details.</div>
        @endif
    </div>
</div>
