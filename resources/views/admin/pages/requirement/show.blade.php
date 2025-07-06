<x-admin.app-layout>
    @livewire('admin.dashboard.overview')

    {{-- requirement details --}}
    <div class="flex flex-col gap-4 w-full bg-white rounded-lg p-4">
        <div class="flex flex-row gap-4 justify-between items-center w-full">
            <div class="text-lg font-bold uppercase">Requirement Details</div>
            <a href="{{ route('admin.requirements.edit', ['requirement' => $requirement]) }}"
                class="btn btn-xs uppercase">edit</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="text-gray-500 text-xs uppercase mb-1">Name</div>
                <div class="text-lg font-semibold">{{ $requirement->name }}</div>
            </div>
            <div>
                <div class="text-gray-500 text-xs uppercase mb-1">Status</div>
                <span
                    class="inline-block px-2 py-1 rounded
                    @if ($requirement->status === 'completed') bg-green-100 text-green-800
                    @else bg-yellow-100 text-yellow-800 @endif">
                    {{ ucfirst($requirement->status) }}
                </span>
            </div>
            <div>
                <div class="text-gray-500 text-xs uppercase mb-1">Priority</div>
                <span
                    class="inline-block px-2 py-1 rounded
                    @if ($requirement->priority === 'high') bg-red-100 text-red-800
                    @elseif($requirement->priority === 'low') bg-blue-100 text-blue-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst($requirement->priority) }}
                </span>
            </div>
            <div>
                <div class="text-gray-500 text-xs uppercase mb-1">Due Date</div>
                <div>{{ \Carbon\Carbon::parse($requirement->due)->format('F d, Y') }}</div>
            </div>
            <div>
                <div class="text-gray-500 text-xs uppercase mb-1">Assigned To</div>
                <div>{{ $requirement->assigned_to }}</div>
            </div>
            <div>
                <div class="text-gray-500 text-xs uppercase mb-1">Created By</div>
                <div>{{ $requirement->createdBy->name ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-gray-500 text-xs uppercase mb-1">Last Updated By</div>
                <div>{{ $requirement->updatedBy->name ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-gray-500 text-xs uppercase mb-1">Archived By</div>
                <div>{{ $requirement->archivedBy->name ?? 'N/A' }}</div>
            </div>
            <div class="md:col-span-2">
                <div class="text-gray-500 text-xs uppercase mb-1">Description</div>
                <div class="whitespace-pre-line">{{ $requirement->description ?? 'No description provided.' }}</div>
            </div>
            <div>
                <div class="text-gray-500 text-xs uppercase mb-1">Created At</div>
                <div>{{ $requirement->created_at->format('F d, Y h:i A') }}</div>
            </div>
            <div>
                <div class="text-gray-500 text-xs uppercase mb-1">Updated At</div>
                <div>{{ $requirement->updated_at->format('F d, Y h:i A') }}</div>
            </div>
        </div>
    </div>

    {{-- requirement user lists --}}
    @livewire('admin.requirement.show.requirement-user-list', ['assignedUsers' => $requirement->assignedTargets()])

    {{-- requirement file lists --}}
    <div class="flex flex-col gap-4 w-full bg-white rounded-lg p-4">
        <div class="text-lg font-bold uppercase">Requirement File List</div>
        <div class="text-gray-500 italic">No files attached to this requirement.</div>
    </div>
</x-admin.app-layout>
