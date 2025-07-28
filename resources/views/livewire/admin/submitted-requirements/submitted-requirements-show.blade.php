<div class="flex flex-col gap-4">
    {{-- requirement details --}}
    <div class="flex flex-col gap-4 bg-white shadow-md rounded-lg p-6 w-full">
        <h2 class="text-xl font-bold">Requirement Details</h2>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-8 divide-gray-500 divide-2">
            <div class="py-2 flex flex-col md:flex-row justify-between col-span-1 md:col-span-3">
                <dt class="font-bold uppercase">Name</dt>
                <dd>{{ $submittedRequirement->requirement->name }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between col-span-1 md:col-span-3">
                <dt class="font-bold uppercase">Description</dt>
                <dd>{{ $submittedRequirement->requirement->description ?? '-' }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Due Date</dt>
                <dd>{{ $submittedRequirement->requirement->due->format('d/m/Y h:i a') }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Assigned To</dt>
                <dd>{{ $submittedRequirement->requirement->assigned_to }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Status</dt>
                <dd>{{ $submittedRequirement->requirement->status }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Priority</dt>
                <dd>{{ $submittedRequirement->requirement->priority }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Created By</dt>
                <dd>{{ $submittedRequirement->requirement->createdBy->full_name }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Updated By</dt>
                <dd>{{ $submittedRequirement->requirement->updatedBy->full_name ?? '-' }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Archived By</dt>
                <dd>{{ $submittedRequirement->requirement->archived_by ?? '-' }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Created At</dt>
                <dd>{{ $submittedRequirement->requirement->created_at->format('d/m/Y h:i a') }}</dd>
            </div>
            <div class="py-2 flex flex-col md:flex-row justify-between">
                <dt class="font-bold uppercase">Updated At</dt>
                <dd>{{ $submittedRequirement->requirement->updated_at->format('d/m/Y h:i a') }}</dd>
            </div>
        </dl>

        <table class="table table-fixed table-sm table-striped min-w-[500px]">
            <thead>
                <tr class="bg-gray-200">
                    <th>File Name</th>
                    <th>File Type</th>
                    <th>File Size</th>
                    <th>Date Modified</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($submittedRequirement->requirement->first()->getMedia('submission_files') as $file)
                    <tr>
                        <td class="truncate">{{ $file->file_name }}</td>
                        <td class="truncate">{{ $file->extension }}</td>
                        <td class="truncate">{{ $file->humanReadableSize }}</td>
                        <td class="truncate">{{ $file->updated_at->format('d/m/Y h:i a') }}</td>
                        <td class="space-x-2">
                            <button type="button" class="text-green-500 hover:text-green-700 hover:link">view</button>
                            <button wire:click='downloadFile({{ $file->id }})' type="button"
                                class="text-blue-500 hover:text-blue-700 hover:link">download</button>
                            <button wire:click='removeFile({{ $file->id }})' type="button"
                                class="text-red-500 hover:text-red-700 hover:link">remove</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No required files attached to this requirement.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- submitted requirement details --}}
    {{--  --}}
    <div class="flex flex-col gap-4 bg-white rounded-lg shadow-lg p-6 w-full">
        <h2 class="text-2xl font-bold">Submitted Requirements</h2>
        <div class="max-h-[500px] overflow-y-auto">
        <table class="table table-fixed table-sm table-striped">
            <thead>
                <tr class="bg-gray-200">
                    <th>File Name</th>
                    <th>File Type</th>
                    <th>File Size</th>
                    <th>Date Modified</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($submittedRequirement->first()->getMedia('submission_files') as $file)
                    <tr>
                        <td class="truncate">{{ $file->file_name }}</td>
                        <td class="truncate">{{ $file->extension }}</td>
                        <td class="truncate">{{ $file->humanReadableSize }}</td>
                        <td class="truncate">{{ $file->updated_at->format('d/m/Y h:i a') }}</td>
                        <td class="space-x-2">
                            <button type="button" class="text-green-500 hover:text-green-700 hover:link">view</button>
                            <button wire:click='downloadFile({{ $file->id }})' type="button"
                                class="text-blue-500 hover:text-blue-700 hover:link">download</button>
                            <button wire:click='removeFile({{ $file->id }})' type="button"
                                class="text-red-500 hover:text-red-700 hover:link">remove</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No required files attached to this requirement.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
