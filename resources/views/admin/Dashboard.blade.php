<x-admin.app-layout>
    {{-- overview --}}
    @livewire('admin.dashboard.overview')

    <div class="flex flex-col gap-2">
        {{-- requirements --}}
        <div class="collapse collapse-arrow bg-base-100 border border-base-300">
            <input type="radio" name="requirement_section" checked/>

            <div class="collapse-title font-semibold">Requirements</div>
            <div class="collapse-content text-sm p-0">
                @livewire('admin.dashboard.requirement')
            </div>
        </div>

        {{-- pendings --}}
        <div class="collapse collapse-arrow bg-base-100 border border-base-300">
            <input type="radio" name="requirement_section" />

            <div class="collapse-title font-semibold">Pendings</div>
            <div class="collapse-content text-sm p-0">
                @livewire('admin.dashboard.pending')
            </div>
        </div>

        {{-- files --}}
        <div class="collapse collapse-arrow bg-base-100 border border-base-300">
            <input type="radio" name="requirement_section" />

            <div class="collapse-title font-semibold">File Manager</div>
            <div class="collapse-content text-sm p-0">
                @livewire('admin.dashboard.file')
            </div>
        </div>
    </div>
</x-admin.app-layout>
