<x-admin.app-layout>
    <div class="flex flex-col lg:flex-row gap-4">
        <!-- Left Side - Semester View (Hidden on mobile) -->
        <div class="w-full lg:w-1/4 order-2 lg:order-1">
            @livewire('admin.file-manager.semester-view')
        </div>
        
        <!-- Right Side - File Manager -->
        <div class="flex-1 order-1 lg:order-2">
            @livewire('admin.file-manager.file-manager-index')
        </div>
    </div>
</x-admin.app-layout>