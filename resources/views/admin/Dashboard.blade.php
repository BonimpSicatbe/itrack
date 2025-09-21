<x-admin.app-layout>
    <div class="flex flex-col gap-6">
        {{-- Overview section (always visible) --}}
        @livewire('admin.dashboard.overview')

        {{-- Semester Analytics --}}
        @livewire('admin.dashboard.semester-analytics')

        {{-- Accordion Sections --}}
        <div class="flex flex-col gap-4 pb-6">
            {{-- Requirements --}}
            <div class="card bg-base-100 shadow rounded-xl">
                <div class="card-body p-0">
                    <div class="collapse collapse-arrow">
                        <input type="checkbox" name="dashboard-accordion" checked /> 
                        <div class="collapse-title font-semibold text-xl px-6 py-4 hover:bg-base-200 text-gray-800">
                            Requirements
                        </div>
                        <div class="collapse-content p-0">
                            @livewire('admin.dashboard.requirement')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin.app-layout>