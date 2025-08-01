<x-admin.app-layout>
    <div class="flex flex-col gap-4">
        {{-- Overview section (always visible) --}}
        @livewire('admin.dashboard.overview')

        <div class="flex flex-col gap-2">
            <div class="collapse collapse-arrow bg-base-100 border border-base-300">
                <input type="checkbox" name="dashboard-accordion" checked /> 
                <div class="collapse-title font-semibold hover:bg-base-200 cursor-pointer">
                    Requirements
                </div>
                <div class="collapse-content text-sm">
                    @livewire('admin.dashboard.requirement')
                </div>
            </div>

            <div class="collapse collapse-arrow bg-base-100 border border-base-300">
                <input type="checkbox" name="dashboard-accordion" />
                <div class="collapse-title font-semibold">Pendings</div>

                <div class="collapse-content text-sm">
                    @livewire('admin.dashboard.pending')
                </div>
            </div>
            <div class="collapse collapse-arrow bg-base-100 border border-base-300">
                <input type="checkbox" name="dashboard-accordion" />
                <div class="collapse-title font-semibold">Files</div>

                <div class="collapse-content text-sm">
                    @livewire('admin.dashboard.file')
                </div>
            </div>
            <div class="collapse collapse-arrow bg-base-100 border border-base-300">
                <input type="checkbox" name="dashboard-accordion" />
                <div class="collapse-title font-semibold">Submitted Files</div>

                <div class="collapse-content text-sm">
                    @livewire('admin.dashboard.file')
                </div>
            </div>
        </div>
    </div>
</x-admin.app-layout>
