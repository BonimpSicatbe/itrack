<x-admin.app-layout>
    <div class="w-[92%] mx-auto h-[calc(100vh-6rem)] flex flex-col">

        <!-- Fixed Header with Gradient -->
        <div class="rounded-2xl p-5 shadow flex flex-col lg:flex-row lg:items-center lg:justify-between"
             style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
            
            <!-- Title -->
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-gears text-white text-xl"></i>
                <h2 class="text-xl font-semibold text-white">System Management</h2>
            </div>

            <!-- Tabs Navigation -->
            <div class="mt-4 lg:mt-0">
                <nav class="flex gap-3">
                    @foreach($tabs as $tabKey => $tab)
                        <button
                            wire:click="setActiveTab('{{ $tabKey }}')"
                            class="px-4 py-2 rounded-full text-sm font-semibold transition-colors duration-200 shadow-sm
                                {{ $activeTab === $tabKey 
                                    ? 'bg-white text-1C7C54' 
                                    : 'bg-white/20 text-white hover:bg-white hover:text-1C7C54' }}"
                        >
                            <i class="fa-solid fa-{{ $tab['icon'] }}"></i>
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </nav>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto bg-white rounded-2xl shadow p-6 mt-4">
            @switch($activeTab)
                @case('users')
                    <div>
                        @livewire('admin.management.user-management')
                    </div>
                @break

                @case('colleges')
                    <div>
                        <h3 class="text-xl font-semibold text-1C7C54 mb-2">College Management</h3>
                        <p class="text-sm text-gray-500">Manage colleges and their departments.</p>
                    </div>
                @break

                @case('departments')
                    <div>
                        <h3 class="text-xl font-semibold text-1C7C54 mb-2">Department Management</h3>
                        <p class="text-sm text-gray-500">Manage departments and their programs.</p>
                    </div>
                @break

                @case('settings')
                    <div>
                        <h3 class="text-xl font-semibold text-1C7C54 mb-2">System Settings</h3>
                        <p class="text-sm text-gray-500">Configure system-wide settings and preferences.</p>
                    </div>
                @break

                @default
                    <div>
                        <h3 class="text-xl font-semibold text-1C7C54 mb-2">Welcome</h3>
                        <p class="text-sm text-gray-500">Select a tab to manage different aspects of the system.</p>
                    </div>
            @endswitch
        </div>
    </div>
</x-admin.app-layout>
