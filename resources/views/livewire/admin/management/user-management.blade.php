<!-- user-management.blade.php -->
<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-4 px-6 pt-6">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-xl font-semibold text-green-700">User Management</h3>
                <p class="text-sm text-gray-600">| Manage system users and their permissions.</p>
            </div>
        </div>
        <button wire:click="openAddUserModal"
            class="px-5 py-2 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 text-sm cursor-pointer">
            <i class="fa-solid fa-plus mr-2"></i>Add User
        </button>
    </div>

    <!-- Divider -->
    <div class="border-b border-gray-200 mb-4"></div>

    <!-- Search, Total Users, and Filters -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4 px-6">

        <!-- Total Users Badge -->
        <div class="flex items-center gap-2 bg-green-50 border border-green-600 px-4 py-2 rounded-xl shadow-sm">
            <i class="fa-solid fa-users text-green-700"></i>
            <span class="text-sm font-semibold text-green-700">
                Total Users: {{ $users->total() }}
            </span>
        </div>

        <!-- Search Box -->
        <div class="w-full sm:w-1/3">
            <label for="search" class="block text-sm font-semibold text-gray-700 mb-1">Search Users</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="pl-10 block w-full rounded-xl border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm"
                    placeholder="Search by name, email, or college">
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="max-h-[500px] overflow-auto border border-gray-200 shadow-sm">
        <table class="table table-auto table-striped table-pin-rows table-sm w-full rounded-xl">
            <thead>
                <tr class="bg-base-300 font-bold uppercase">
                    <th class="cursor-pointer hover:bg-green-800 bg-green-700 p-4 text-left"
                        wire:click="sortBy('lastname')" style="color: white; width: 30%;">
                        <div class="flex items-center pt-2 pb-2">
                            Name
                            <div class="ml-1">
                                @if ($sortField === 'lastname')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort opacity-30"></i>
                                @endif
                            </div>
                        </div>
                    </th>
                    <th class="p-4 text-left bg-green-700" style="color: white; width: 25%;">Email</th>
                    <th class="p-4 text-left bg-green-700" style="color: white; width: 20%;">College</th>
                    <th class="p-4 text-left bg-green-700" style="color: white; width: 15%;">Roles</th>
                    <th class="p-4 text-center bg-green-700" style="color: white; width: 10%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="hover:bg-green-50 transition-colors duration-150 cursor-pointer"
                        wire:click="showUser({{ $user->id }})">
                        <td class="whitespace-nowrap p-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                        <span
                                            class="font-semibold text-green-800">{{ substr($user->firstname, 0, 1) }}{{ substr($user->lastname, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $user->firstname }}
                                        {{ $user->middlename ? $user->middlename . ' ' : '' }}
                                        {{ $user->lastname }}
                                        {{ $user->extensionname ? $user->extensionname : '' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap p-4">
                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                            @if ($user->email_verified_at)
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Verified
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Unverified
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">
                            {{ $user->college->name ?? 'N/A' }}
                        </td>
                        <td class="whitespace-nowrap p-4">
                            @foreach ($user->roles as $role)
                                <span
                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if ($role->name === 'super-admin') bg-purple-100 text-purple-800
                                    @elseif($role->name === 'admin') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ ucfirst($role->name) }}
                                </span>
                            @endforeach
                        </td>
                        <td class="whitespace-nowrap p-4">
                            <div class="flex justify-center space-x-2 text-base" onclick="event.stopPropagation()">
                                <a href="{{ route('admin.users.report', $user) }}"
                                    class="text-green-500 hover:bg-green-100 rounded-xl p-2 tooltip cursor-pointer"
                                    data-tip="Generate Report">
                                    <i class="fa-solid fa-file-chart-column"></i>
                                </a>
                                <button class="text-amber-500 hover:bg-amber-100 rounded-xl p-2 tooltip cursor-pointer"
                                    data-tip="Edit" wire:click="openEditUserModal({{ $user->id }})">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="text-red-600 hover:bg-red-100 rounded-xl p-2 tooltip cursor-pointer"
                                    data-tip="Delete" wire:click="openDeleteConfirmationModal({{ $user->id }})">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center p-4">No users found matching your criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 px-6">
        {{ $users->links() }}
    </div>

    <!-- User Detail Sidebar -->
    @if ($selectedUser)
        <x-modal name="user-details-modal" :show="!!$selectedUser" maxWidth="2xl">
            <!-- Header -->
            <div class="bg-green-700 text-white rounded-t-xl px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-user text-lg"></i>
                    <h3 class="text-xl font-semibold">User Details</h3>
                </div>
                <button wire:click="closeUserDetail"
                    class="text-white hover:text-gray-200 focus:outline-none cursor-pointer">
                    <i class="fa-solid fa-xmark h-5 w-5"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-xl">
                <!-- User profile header -->
                <div class="mb-6 pb-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="h-16 w-16 flex-shrink-0">
                                <div
                                    class="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center text-xl font-medium text-green-800">
                                    {{ substr($selectedUser->firstname, 0, 1) }}{{ substr($selectedUser->lastname, 0, 1) }}
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ $selectedUser->firstname }}
                                    {{ $selectedUser->middlename ? $selectedUser->middlename . ' ' : '' }}
                                    {{ $selectedUser->lastname }}
                                    {{ $selectedUser->extensionname ? $selectedUser->extensionname : '' }}
                                </h3>
                                <p class="text-sm text-gray-500">{{ $selectedUser->email }}</p>
                            </div>
                        </div>

                        <!-- Role badge positioned on the far right -->
                        <div class="flex-shrink-0">
                            @if ($selectedUser->roles->count() > 0)
                                @foreach ($selectedUser->roles as $role)
                                    <span
                                        class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                        @if ($role->name === 'super-admin') bg-purple-100 text-purple-800
                                        @elseif($role->name === 'admin') bg-blue-100 text-blue-800
                                        @else bg-green-100 text-green-800 @endif">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            @else
                                <span
                                    class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    No Role
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- User details -->
                <div class="space-y-6">
                    <!-- Personal Information -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Personal Information</h4>
                        <dl class="space-y-2">
                            <div class="sm:flex sm:items-center">
                                <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Full Name
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $selectedUser->firstname }}
                                    {{ $selectedUser->middlename ? $selectedUser->middlename . ' ' : '' }}
                                    {{ $selectedUser->lastname }}
                                    {{ $selectedUser->extensionname ? $selectedUser->extensionname : '' }}
                                </dd>
                            </div>
                            <div class="sm:flex sm:items-center">
                                <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $selectedUser->email }}</dd>
                            </div>
                            <div class="sm:flex sm:items-center">
                                <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Email Status
                                </dt>
                                <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                    @if ($selectedUser->email_verified_at)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Verified
                                        </span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Unverified
                                        </span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Institutional Information -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Institutional Information</h4>
                        <dl class="space-y-2">
                            <div class="sm:flex sm:items-center">
                                <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">College</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $selectedUser->college->name ?? 'N/A' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Account Information -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Account Information</h4>
                        <dl class="space-y-2">
                            <div class="sm:flex sm:items-center">
                                <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Member Since
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $selectedUser->created_at->format('M d, Y') }}
                                </dd>
                            </div>
                            <div class="sm:flex sm:items-center">
                                <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Last Updated
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $selectedUser->updated_at->format('M d, Y') }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeUserDetail" class="bg-white py-2 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 cursor-pointer">
                        Close
                    </button>
                    <button type="button" wire:click="openEditUserModal({{ $selectedUser->id }})" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Edit User
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Add User Modal -->
    @if ($showAddUserModal)
        <x-modal name="add-user-modal" :show="$showAddUserModal" maxWidth="2xl">
            <!-- Header -->
            <div class=" text-white rounded-t-xl px-6 py-4 flex items-center space-x-3"
                style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <i class="fa-solid fa-user-plus text-lg"></i>
                <h3 class="text-xl font-semibold">Add New User</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-6">
                    <!-- First/Last Name -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">First Name *</label>
                            <input type="text" wire:model="newUser.firstname"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                                placeholder="Enter first name">
                            @error('newUser.firstname')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Last Name *</label>
                            <input type="text" wire:model="newUser.lastname"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                                placeholder="Enter last name">
                            @error('newUser.lastname')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Middle/Extension -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Middle Name</label>
                            <input type="text" wire:model="newUser.middlename"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                                placeholder="Enter middle name">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Extension Name</label>
                            <input type="text" wire:model="newUser.extensionname"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                                placeholder="e.g., Jr., Sr., III">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Email Address *</label>
                        <input type="email" wire:model="newUser.email"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter email address">
                        @error('newUser.email')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- College -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">College</label>
                        <select wire:model="newUser.college_id"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm">
                            <option value="">Select College</option>
                            @foreach ($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Role *</label>
                        <select wire:model="newUser.role"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm">
                            <option value="">Select Role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                        @error('newUser.role')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-info-circle text-blue-500 mt-1"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Auto-generated Password</h3>
                                <div class="mt-1 text-sm text-blue-700">
                                    <p>A secure password will be automatically generated and sent to the user's email address.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeAddUserModal"
                        class="bg-white py-2 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-500 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="addUser"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 cursor-pointer">
                        <i class="fa-solid fa-user-plus mr-2"></i> Add User
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Edit User Modal -->
    @if ($showEditUserModal)
        <x-modal name="edit-user-modal" :show="$showEditUserModal" maxWidth="2xl">
            <!-- Header -->
            <div class=" text-white rounded-t-xl px-6 py-4 flex items-center space-x-3"
                style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <i class="fa-solid fa-user-edit text-lg"></i>
                <h3 class="text-xl font-semibold">Edit User</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-6">
                    <!-- First/Last Name -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">First Name *</label>
                            <input type="text" wire:model="editingUser.firstname"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                                placeholder="Enter first name">
                            @error('editingUser.firstname')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Last Name *</label>
                            <input type="text" wire:model="editingUser.lastname"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                                placeholder="Enter last name">
                            @error('editingUser.lastname')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Middle/Extension -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Middle Name</label>
                            <input type="text" wire:model="editingUser.middlename"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                                placeholder="Enter middle name">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Extension Name</label>
                            <input type="text" wire:model="editingUser.extensionname"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                                placeholder="e.g., Jr., Sr., III">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Email Address *</label>
                        <input type="email" wire:model="editingUser.email"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                            placeholder="Enter email address">
                        @error('editingUser.email')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- College -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">College</label>
                        <select wire:model="editingUser.college_id"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm">
                            <option value="">Select College</option>
                            @foreach ($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Role *</label>
                        <select wire:model="editingUser.role"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm">
                            <option value="">Select Role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                        @error('editingUser.role')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password (Optional) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">New Password (Optional)</label>
                            <input type="password" wire:model="editingUser.password"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                                placeholder="Enter new password">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Confirm Password</label>
                            <input type="password" wire:model="editingUser.password_confirmation"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm"
                                placeholder="Confirm new password">
                        </div>
                    </div>
                    @error('editingUser.password')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Action buttons -->
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeEditUserModal"
                        class="bg-white py-2 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-500 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="updateUser"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 cursor-pointer">
                        <i class="fa-solid fa-user-check mr-2"></i> Update User
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteConfirmationModal && $userToDelete)
        <x-modal name="delete-user-confirmation-modal" :show="$showDeleteConfirmationModal" maxWidth="md">
            <!-- Header -->
            <div class="bg-red-600 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Confirm Deletion</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Are you sure you want to delete the user
                        <span class="font-semibold text-red-600">{{ $userToDelete->firstname }}
                            {{ $userToDelete->lastname }}</span>?
                    </p>
                    <p class="text-sm text-gray-600">
                        This action cannot be undone. All data associated with this user will be permanently removed.
                    </p>
                </div>

                <!-- Action buttons -->
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeDeleteConfirmationModal"
                        class="bg-white py-2 px-4 border border-gray-300 rounded-full shadow-sm text-sm font-medium text-gray-500 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteUser"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 cursor-pointer">
                        <i class="fa-solid fa-trash mr-2"></i> Delete User
                    </button>
                </div>
            </div>
        </x-modal>
    @endif
</div>