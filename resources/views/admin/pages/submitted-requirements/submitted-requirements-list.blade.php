<x-admin.app-layout>
    <div class="bg-white rounded-lg shadow p-4">
        @forelse ($submittedRequirements as $submission)
            <div class="border-b border-gray-200 py-2">
                <h3 class="font-semibold">{{ $submission->requirement->title }}</h3>
                <p class="text-sm text-gray-600">Submitted by: {{ $submission->user->full_name }}</p>
                <p class="text-sm text-gray-600">Status: <span
                        style="color: {{ \App\Models\SubmittedRequirement::getStatusColor($submission->status) }}">{{ \App\Models\SubmittedRequirement::statuses()[$submission->status] }}</span>
                </p>
            </div>
        @empty
            <p>No submitted requirements found.</p>
        @endforelse
    </div>
</x-admin.app-layout>
