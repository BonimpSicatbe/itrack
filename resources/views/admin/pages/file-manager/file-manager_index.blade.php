<x-admin.app-layout>
    <div class="flex flex-col lg:flex-row gap-4">
        <!-- Collapsible Left Side - Semester View -->
        <div class="w-full lg:w-90 order-2 lg:order-1 transition-all duration-300 ease-in-out" 
             :class="{ '-ml-64': !$wire.showSemesterPanel }">
            @livewire('admin.file-manager.semester-view')
        </div>
        
        <!-- Right Side - File Manager with toggle button -->
        <div class="flex-1 order-1 lg:order-2">
            @livewire('admin.file-manager.file-manager-index')
        </div>
    </div>
</x-admin.app-layout>