<div class="flex flex-col gap-4">
    <div class="flex flex-col gap-4 p-4 sm:p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-xl sm:text-2xl font-bold">Pending Requirements</h2>
        <div class="flex flex-col gap-4 max-h-[400px] sm:max-h-[500px] overflow-y-auto">
            @forelse($pendings as $pending)
                <a href="{{ route('admin.requirements.show', ['requirement' => $pending->id]) }}" class="flex flex-col gap-1 w-full p-3 sm:p-4 bg-gray-100 rounded-lg">
                    <h3 class="text-base sm:text-lg font-bold">{{$pending->name}}</h3>
                    <p class="text-xs sm:text-sm text-gray-600">{{ $pending->description }}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-y-2 sm:gap-x-8">
                        <p class="text-xs sm:text-sm text-gray-600">
                            <span class="font-bold">Created by: </span>{{ $pending->createdBy->full_name }}</p>
                        <p class="text-xs sm:text-sm text-gray-600">
                            <span class="font-bold">Created at: </span>{{ $pending->created_at->format('d/m/Y H:i a') }}</p>
                        <p class="text-xs sm:text-sm text-gray-600">
                            <span class="font-bold">Due date: </span>{{ $pending->due->format('d/m/Y H:i a') }}</p>
                    </div>
                </a>
            @empty
                <div class="p-3 sm:p-4 bg-gray-200 rounded-lg shadow-sm">
                    <p class="text-center text-xs sm:text-sm text-gray-600">No pending requirements found.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
