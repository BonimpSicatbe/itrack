<x-user.app-layout>
    <div class="p-4 space-y-4">
        <div class="text-xl font-bold uppercase">All Recent Submissions</div>

        <livewire:user.dashboard.recent :showAll="true" :listView="true" />
    </div>
</x-user.app-layout>