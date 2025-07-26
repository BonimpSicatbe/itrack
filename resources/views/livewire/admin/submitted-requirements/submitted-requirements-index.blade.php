<div class="flex flex-col gap-4">
    {{--  --}}
    <div class="flex flex-col gap-4 bg-white p-6 w-full rounded-lg shadow-md">
        <h2 class="text-2xl font-bold">Submitted Requirements</h2>
        <div class="flex flex-col gap-4 max-h-[500px] overflow-y-auto">
            @forelse ($submittedRequirements as $submittedRequirement)
                <div class="flex items-center justify-between p-4 border-b">
                    <div>
                        <h3 class="font-semibold">{{ $submittedRequirement->requirement->name }}</h3>
                        <p class="text-sm text-gray-600">Submitted by: {{ $submittedRequirement->user->full_name }}</p>
                    </div>
                    <div class="flex items-center">
                        @foreach ($submittedRequirement->getMedia() as $media)
                            <img src="{{ $media->getUrl() }}" alt="Media" class="w-12 h-12 object-cover rounded-full ml-2">
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No submitted requirements found.</p>
            @endforelse
        </div>
    </div>
</div>
