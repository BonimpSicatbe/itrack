<x-admin.app-layout>
    <div class="h-[calc(100vh-6rem)] flex flex-col">

        <!-- Fixed Header with Gradient -->
        <div class="rounded-xl p-5 shadow flex flex-col lg:flex-row lg:items-center lg:justify-between"
             style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            
            <!-- Title -->
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-file-lines text-white text-xl"></i>
                <h2 class="text-xl font-semibold text-white">Reports & Analytics</h2>
            </div>

            <!-- Tabs Navigation -->
            <div class="mt-4 lg:mt-0">
                <nav class="flex gap-3">
                    @foreach($tabs as $tabKey => $tab)
                        <a
                            href="{{ route('admin.reports.index', ['tab' => $tabKey]) }}"
                            class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors duration-200 shadow-sm
                                {{ $activeTab === $tabKey 
                                    ? 'bg-white text-1C7C54' 
                                    : 'bg-white/20 hover:bg-white text-1C7C54' }}"
                        >
                            <i class="fa-solid fa-{{ $tab['icon'] }}"></i>
                            {{ $tab['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto bg-white rounded-xl shadow mt-4">
            @switch($activeTab)
                @case('overview')
                    <div>
                        @livewire('admin.report.report-index')
                    </div>
                @break
                @case('users')
                    <div>
                        @livewire('admin.report.user-report')
                    </div>
                @break
                @case('requirements')
                    <div>
                        @livewire('admin.report.requirement-report')
                    </div>
                @break
                @case('yearly')
                    <div>
                        @livewire('admin.report.custom-report')
                    </div>
                @break
            @endswitch
        </div>
    </div>
</x-admin.app-layout>