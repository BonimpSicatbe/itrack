<!-- user-management.blade.php -->
<div class="w-full flex ">
    <!-- Main Content Area -->
    <div class="w-full transition-all duration-300 ease-in-out p-2">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h3 class="text-xl font-semibold text-1B512D">User Management</h3>
                <p class="text-sm text-gray-600">Manage system users and their permissions.</p>
            </div>
            <button 
                wire:click="openAddUserModal" 
                class="px-5 py-2 bg-1C7C54 text-white font-semibold rounded-full hover:bg-1B512D focus:outline-none focus:ring-2 focus:ring-73E2A7 focus:ring-offset-2 transition text-sm"
            >
                <i class="fa-solid fa-plus mr-2"></i>Add User
            </button>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-200 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-semibold text-gray-700 mb-1">Search Users</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            class="pl-10 block w-full rounded-xl border-gray-300 shadow-sm focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm" 
                            placeholder="Search by name or email"
                        >
                    </div>
                </div>

                <!-- College Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">College</label>
                    <select 
                        wire:model.live="collegeFilter" 
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                    >
                        <option value="">All Colleges</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}">{{ $college->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Department Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Department</label>
                    <select 
                        wire:model.live="departmentFilter" 
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                    >
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="max-h-[500px] overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
            <table class="table table-auto table-striped table-pin-rows table-sm min-w-[800px] rounded-lg">
                <thead>
                    <tr class="bg-base-300 font-bold uppercase">
                        <th class="cursor-pointer hover:bg-blue-50 p-4" wire:click="sortBy('lastname')" style="background-color: #1C7C54; color: white;">
                            <div class="flex items-center pt-2 pb-2">
                                Name
                                <div class="ml-1">
                                    @if($sortField === 'lastname')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort opacity-30"></i>
                                    @endif
                                </div>
                            </div>
                        </th>
                        <th class="p-4" style="background-color: #1C7C54; color: white;">Email</th>
                        <th class="p-4" style="background-color: #1C7C54; color: white;">College</th>
                        <th class="p-4" style="background-color: #1C7C54; color: white;">Department</th>
                        <th class="p-4" style="background-color: #1C7C54; color: white;">Roles</th>
                        <th class="p-4 text-right" style="background-color: #1C7C54; color: white;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="hover:bg-blue-50 transition-colors duration-150 cursor-pointer" 
                            wire:click="showUser({{ $user->id }})">
                            <td class="whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                            <span class="font-medium text-green-800">{{ substr($user->firstname, 0, 1) }}{{ substr($user->lastname, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $user->firstname }} 
                                            {{ $user->middlename ? $user->middlename . ' ' : '' }}
                                            {{ $user->lastname }}
                                            {{ $user->extensionname ? $user->extensionname : '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                @if($user->email_verified_at)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Verified
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Unverified
                                    </span>
                                @endif
                            </td>
                            <td class="text-sm text-gray-500">
                                {{ $user->college->name ?? 'N/A' }}
                            </td>
                            <td class="text-sm text-gray-500">
                                {{ $user->department->name ?? 'N/A' }}
                            </td>
                            <td class="text-sm text-gray-500">
                                @foreach($user->roles as $role)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($role->name === 'super-admin') bg-purple-100 text-purple-800
                                        @elseif($role->name === 'admin') bg-blue-100 text-blue-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="whitespace-nowrap text-right text-sm font-medium z-10">
                                <div class="flex justify-center space-x-2" onclick="event.stopPropagation()">
                                    <button class="text-amber-500 hover:bg-amber-100 rounded-lg p-2 tooltip" data-tip="Edit" wire:click="openEditUserModal({{ $user->id }})">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="text-red-600 hover:bg-red-100 rounded-lg p-2 tooltip"
                                                data-tip="Delete" wire:click="openDeleteConfirmationModal({{ $user->id }})">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-4">No users found matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="w-full flex justify-center py-2">
                {{ $users->links() }}
            </div>
        @endif

        <!-- Results Per Page Selector -->
        <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-sm text-gray-700 mr-2">Show</span>
                <select wire:model="perPage" class="rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-sm text-gray-700 ml-2">results per page</span>
            </div>
            <div class="text-sm text-gray-700">
                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
            </div>
        </div>
    </div>

    <!-- User Detail Sidebar -->
    @if($selectedUser)
        <x-modal name="user-details-modal" :show="!!$selectedUser" maxWidth="2xl">
            <!-- Header -->
            <div class="bg-1C7C54 text-white rounded-t-2xl px-6 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-user text-lg"></i>
                    <h3 class="text-xl font-semibold">User Details</h3>
                </div>
                <button wire:click="closeUserDetail" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fa-solid fa-xmark h-5 w-5"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-2xl">
                <!-- User profile header -->
                <div class="mb-6 pb-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="h-16 w-16 flex-shrink-0">
                            <div class="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center text-xl font-medium text-green-800">
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
                </div>
                
                <!-- User details -->
                <div class="space-y-6">
                    <!-- Personal Information -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Personal Information</h4>
                        <dl class="space-y-2">
                            <div class="sm:flex sm:items-center">
                                <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Full Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $selectedUser->firstname }} 
                                    {{ $selectedUser->middlename ? $selectedUser->middlename . ' ' : '' }}
                                    {{ $selectedUser->lastname }}
                                    {{ $selectedUser->extensionname ? $selectedUser->extensionname : '' }}
                                </dd>
                            </div>
                            <div class="sm:flex sm:items-center">
                                <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $selectedUser->email }}</dd>
                            </div>
                            <div class="sm:flex sm:items-center">
                                <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Email Status</dt>
                                <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                    @if($selectedUser->email_verified_at)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Verified
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
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
                            <div class="sm:flex sm:items-center">
                                <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Department</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $selectedUser->department->name ?? 'N/A' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                    
                    <!-- Account Information -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Account Information</h4>
                        <dl class="space-y-2">
                            <div class="sm:flex sm:items-center">
                                <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Member Since</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $selectedUser->created_at->format('M d, Y') }}
                                </dd>
                            </div>
                            <div class="sm:flex sm:items-center">
                                <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $selectedUser->updated_at->format('M d, Y') }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
                
                <!-- Action buttons -->
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeUserDetail" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Close
                    </button>
                    <button type="button" wire:click="openEditUserModal({{ $selectedUser->id }})" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Edit User
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Add User Modal -->
    @if($showAddUserModal)
        <x-modal name="add-user-modal" :show="$showAddUserModal" maxWidth="2xl">
            <!-- Header -->
            <div class="bg-1C7C54 text-white rounded-t-2xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-user-plus text-lg"></i>
                <h3 class="text-xl font-semibold">Add New User</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-2xl">
                <div class="space-y-6">
                    <!-- First/Last Name -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">First Name *</label>
                            <input type="text" wire:model="newUser.firstname"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                                placeholder="Enter first name">
                            @error('newUser.firstname') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Last Name *</label>
                            <input type="text" wire:model="newUser.lastname"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                                placeholder="Enter last name">
                            @error('newUser.lastname') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Middle/Extension -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Middle Name</label>
                            <input type="text" wire:model="newUser.middlename"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                                placeholder="Enter middle name">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Extension Name</label>
                            <input type="text" wire:model="newUser.extensionname"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                                placeholder="e.g., Jr., Sr., III">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Email Address *</label>
                        <input type="email" wire:model="newUser.email"
                            class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                            placeholder="Enter email address">
                    </div>

                    <!-- College/Department -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">College</label>
                            <select wire:model="newUser.college_id"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                                <option value="">Select College</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Department</label>
                            <select wire:model="newUser.department_id"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Password *</label>
                            <input type="password" wire:model="newUser.password"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                                placeholder="Enter password">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Confirm Password *</label>
                            <input type="password" wire:model="newUser.password_confirmation"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                                placeholder="Confirm password">
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" wire:click="closeAddUserModal"
                        class="px-5 py-2 rounded-full border border-1C7C54 text-1C7C54 bg-white hover:bg-73E2A7 hover:text-white font-semibold text-sm">
                        Cancel
                    </button>
                    <button type="button" wire:click="addUser" wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-full bg-1C7C54 text-white font-semibold text-sm shadow hover:bg-1B512D">
                        <span wire:loading.remove wire:target="addUser">Add User</span>
                        <span wire:loading wire:target="addUser">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Adding...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif


    <!-- Edit User Modal -->
    @if($showEditUserModal)
        <x-modal name="edit-user-modal" :show="$showEditUserModal" maxWidth="2xl">
            <!-- Header -->
            <div class="bg-1C7C54 text-white rounded-t-2xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-user-pen text-lg"></i>
                <h3 class="text-xl font-semibold">Edit User</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-2xl">
                <div class="space-y-6">
                    <!-- Name Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">First Name *</label>
                            <input type="text" wire:model="editingUser.firstname"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                                placeholder="Enter first name">
                            @error('editingUser.firstname') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Last Name *</label>
                            <input type="text" wire:model="editingUser.lastname"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                                placeholder="Enter last name">
                            @error('editingUser.lastname') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Middle / Extension -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Middle Name</label>
                            <input type="text" wire:model="editingUser.middlename"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                                placeholder="Enter middle name">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Extension Name</label>
                            <input type="text" wire:model="editingUser.extensionname"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                                placeholder="e.g., Jr., Sr., III">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Email Address *</label>
                        <input type="email" wire:model="editingUser.email"
                            class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                            placeholder="Enter email address">
                    </div>

                    <!-- College / Department -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">College</label>
                            <select wire:model="editingUser.college_id"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                                <option value="">Select College</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Department</label>
                            <select wire:model="editingUser.department_id"
                                class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Password Update -->
                    <div class="pt-5 mt-5 border-t border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-800 mb-3">Update Password (Optional)</h4>
                        <p class="text-xs text-gray-500 mb-4">Leave blank if you don’t want to change the password.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">New Password</label>
                                <input type="password" wire:model="editingUser.password"
                                    class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                                    placeholder="Enter new password">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700">Confirm Password</label>
                                <input type="password" wire:model="editingUser.password_confirmation"
                                    class="mt-2 block w-full rounded-xl border-gray-300 focus:border-1C7C54 focus:ring-1C7C54 sm:text-sm"
                                    placeholder="Confirm new password">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" wire:click="closeEditUserModal"
                        class="px-5 py-2 rounded-full border border-1C7C54 text-1C7C54 bg-white hover:bg-73E2A7 hover:text-white font-semibold text-sm">
                        Cancel
                    </button>
                    <button type="button" wire:click="updateUser" wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-full bg-1C7C54 text-white font-semibold text-sm shadow hover:bg-1B512D">
                        <span wire:loading.remove wire:target="updateUser">Update User</span>
                        <span wire:loading wire:target="updateUser">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Updating...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif


    <!-- Delete Confirmation Modal -->
    @if($showDeleteConfirmationModal && $userToDelete)
        <x-modal name="delete-user-modal" :show="$showDeleteConfirmationModal" maxWidth="md">
            <!-- Header -->
            <div class="bg-red-600 text-white rounded-t-2xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                <h3 class="text-xl font-semibold">Delete User</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-2xl">
                <div class="text-center">
                    <p class="text-sm text-gray-700">
                        Are you sure you want to delete  
                        <span class="font-semibold text-gray-900">
                            {{ $userToDelete->firstname }} {{ $userToDelete->lastname }}
                        </span>?  
                        <br>This action cannot be undone.
                    </p>
                </div>

                <!-- Footer -->
                <div class="mt-8 flex justify-center space-x-3">
                    <button 
                        type="button" 
                        wire:click="closeDeleteConfirmationModal" 
                        class="px-5 py-2 rounded-full border border-1C7C54 text-1C7C54 bg-white hover:bg-73E2A7 hover:text-white font-semibold text-sm"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        wire:click="deleteUser" 
                        wire:loading.attr="disabled"
                        class="px-5 py-2 rounded-full bg-red-600 text-white font-semibold text-sm shadow hover:bg-red-700"
                    >
                        <span wire:loading.remove wire:target="deleteUser">Delete</span>
                        <span wire:loading wire:target="deleteUser">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

</div>