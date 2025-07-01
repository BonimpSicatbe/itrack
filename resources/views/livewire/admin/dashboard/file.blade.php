<div class="flex flex-col gap-4 w-full bg-white rounded-lg p-4">
    {{-- header title --}}
    <div class="text-lg font-bold uppercase">Files</div>
    {{-- header actions --}}
    <div class="flex flex-row gap-4 w-full">
        {{-- filters --}}
        <input type="text" wire:model.live="search" id="search" class="input input-sm input-bordered w-128"
            placeholder="Search pendings">
    </div>

    {{-- content --}}
    <div class="overflow-x-auto max-h-[500px] rounded-lg shadow-md">
        @forelse ($media as $item)
            <div class="p-4 w-full">{{ $item->name }}</div>
        @empty
        @endforelse
    </div>
</div>
