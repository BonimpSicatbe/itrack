<x-admin.app-layout>
    <div class="h-[calc(100vh-6rem)] flex flex-col">

        <!-- Fixed Header with Gradient -->
        <div class="rounded-xl p-5 shadow flex flex-col lg:flex-row lg:items-center lg:justify-between"
             style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">

            <!-- Title -->
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-gears text-white text-xl"></i>
                <h2 class="text-xl font-semibold text-white">System Management</h2>
            </div>

            <!-- Tabs Navigation -->
            <div class="mt-4 lg:mt-0">
                <nav class="flex gap-2">
                    @foreach($tabs as $tabKey => $tab)
                        <a
                            href="{{ route('admin.management.index', ['tab' => $tabKey]) }}"
                            class="px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 ease-in-out border
                                {{ $activeTab === $tabKey
                                    ? 'bg-white text-green-800 shadow-md border-transparent'
                                    : 'bg-transparent text-white border-white/50 hover:bg-white hover:text-green-700 hover:border-white hover:shadow-lg' }}"
                        >
                            <i class="fa-solid fa-{{ $tab['icon'] }} mr-2"></i>
                            {{ $tab['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto bg-white rounded-xl shadow mt-4">
            @switch($activeTab)
                @case('semesters')
                    <div>
                        @livewire('admin.management.semester-management')
                    </div>
                @break
                @case('users')
                    <div>
                        @livewire('admin.management.user-management')
                    </div>
                @break
                @case('colleges')
                    <div>
                        @livewire('admin.management.college-management')
                    </div>
                @break
                @case('programs')
                    <div>
                        @livewire('admin.management.program-management')
                    </div>
                @break
                @case('courses')
                    <div>
                        @livewire('admin.management.course-management')
                    </div>
                @break
                @case('signatures')
                    <div>
                        @livewire('admin.management.signature-management')
                    </div>
                @break
            @endswitch
        </div>
    </div>
</x-admin.app-layout>
