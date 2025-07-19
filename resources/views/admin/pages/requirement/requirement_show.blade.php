@php
    $requirement_id = request()->requirement_id ?? null;
@endphp

<x-admin.app-layout>
    {{-- header (action header) --}}
    <div class="bg-white shadow-lg rounded-lg">
        @livewire('admin.requirement.show.requirement-show', ['requirement_id' => $requirement_id])
    </div>

    <div class="bg-white shadow-lg rounded-lg">
        @livewire('admin.requirement.show.requirement-assigned-users', ['requirement_id' => $requirement_id])
    </div>

    <div class="bg-white shadow-lg rounded-lg">
        @livewire('admin.requirement.show.requirement-uploaded-files', ['requirement_id' => $requirement_id])
    </div>
</x-admin.app-layout>
