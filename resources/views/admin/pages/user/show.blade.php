{{--
    folder:     admin/pages/user/show
    name:       admin.user.show
    route:      admin/requirements/{requirementId}
    - show specific details of a user
--}}

<x-admin.app-layout>
    {{-- user requirement --}}
    <div class="flex flex-col gap-4 w-full bg-white rounded-lg p-4">
        <div class="text-lg font-bold uppercase">User Details</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-600 font-semibold">First Name</label>
                <div class="text-gray-900">{{ $user->firstname }}</div>
            </div>
            <div>
                <label class="block text-gray-600 font-semibold">Middle Name</label>
                <div class="text-gray-900">{{ $user->middlename ?? '-' }}</div>
            </div>
            <div>
                <label class="block text-gray-600 font-semibold">Last Name</label>
                <div class="text-gray-900">{{ $user->lastname }}</div>
            </div>
            <div>
                <label class="block text-gray-600 font-semibold">Extension Name</label>
                <div class="text-gray-900">{{ $user->extensionname ?? '-' }}</div>
            </div>
            <div>
                <label class="block text-gray-600 font-semibold">Email</label>
                <div class="text-gray-900">{{ $user->email }}</div>
            </div>
            <div>
                <label class="block text-gray-600 font-semibold">Email Verified At</label>
                <div class="text-gray-900">
                    {{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y H:i') : 'Not Verified' }}
                </div>
            </div>
            <div>
                <label class="block text-gray-600 font-semibold">Department</label>
                <div class="text-gray-900">{{ $user->department->name ?? '-' }}</div>
            </div>
            <div>
                <label class="block text-gray-600 font-semibold">College</label>
                <div class="text-gray-900">{{ $user->college->name ?? '-' }}</div>
            </div>
            <div>
                <label class="block text-gray-600 font-semibold">Created At</label>
                <div class="text-gray-900">{{ $user->created_at->format('M d, Y H:i') }}</div>
            </div>
            <div>
                <label class="block text-gray-600 font-semibold">Updated At</label>
                <div class="text-gray-900">{{ $user->updated_at->format('M d, Y H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- user requirement/s --}}
    @livewire('admin.requirement.show.requirement-list', ['requirements' => $user->requirements])
</x-admin.app-layout>
