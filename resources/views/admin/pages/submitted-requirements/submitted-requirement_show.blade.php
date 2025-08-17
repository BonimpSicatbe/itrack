<x-admin.app-layout>
    @livewire('admin.submitted-requirements.submitted-requirements-show', [
    'submittedRequirement' => $submittedRequirement,
    'initialFileId' => $initialFileId ?? null,
])
</x-admin.app-layout>
