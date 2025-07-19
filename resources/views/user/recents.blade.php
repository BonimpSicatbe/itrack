<x-user.app-layout>
    <div class="flex flex-col gap-4 w-full px-6 py-4">
        {{-- Recent Submissions Header --}}
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">Recent Submissions</h1>
            
            {{-- Search Bar with Magnifying Glass Icon --}}
            <div class="relative w-1/3">
                <input 
                    type="text" 
                    id="search" 
                    placeholder="Search submissions..."
                    class="w-full py-2 px-4 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500">
                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a7 7 0 011 13.938V20h-2v-2.062A7.002 7.002 0 0111 4zM4 11a7 7 0 0113.939-1.062A7 7 0 0111 21a7 7 0 01-7-7z"></path>
                    </svg>
                </span>
            </div>
        </div>

        {{-- Filter Controls --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            {{-- Status Filter --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">
                    <i class="fas fa-flag mr-1.5 text-gray-500"></i>Status
                </label>
                <select wire:model.live="statusFilter" 
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white transition-all duration-200">
                    <option value="">All Statuses</option>
                    <option value="under-review">Under Review</option>
                    <option value="revision-needed">Revision Needed</option>
                    <option value="rejected">Rejected</option>
                    <option value="approved">Approved</option>
                </select>
            </div>
        </div>

        {{-- Main Submissions List --}}
        <div>
            @livewire('user.recents.recent-submissions-list')
        </div>
    </div>
</x-user.app-layout>
