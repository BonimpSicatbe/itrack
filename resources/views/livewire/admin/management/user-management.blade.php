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
            class="px-5 py-2 bg-green-600 text-white font-semibold rounded-xl text-sm cursor-pointer">
            <i class="fa-solid fa-plus mr-2"></i>Add User
        </button>
    </div>

    <!-- Divider -->
    <div class="border-b border-gray-200 mb-4"></div>

    <!-- Search, Total Users, and Filters -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4 px-6">
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <div class="flex items-center gap-2">
                <select wire:model.live="statusFilter" class="rounded-xl border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="pl-10 block w-sm rounded-xl border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 sm:text-sm"
                    placeholder="Search user name, email, or college..">
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 bg-green-50 border border-green-600 px-4 py-2 rounded-xl shadow-sm">
                <i class="fa-solid fa-users text-green-700"></i>
                <span class="text-sm font-semibold text-green-700">
                    Total Users: {{ $users->total() }}
                </span>
            </div>      
        </div>
    </div>

    <!-- Users Table -->
    <div class="max-h-[500px] overflow-auto border border-gray-200 shadow-sm">
        <table class="table table-auto table-striped table-pin-rows table-sm w-full rounded-xl">
            <thead>
                <tr class="bg-base-300 font-bold uppercase">
                    <th class="cursor-pointer hover:bg-green-800 bg-green-700 p-4 text-left"
                        wire:click="sortBy('lastname')" style="color: white; width: 25%;">
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
                    <th class="p-4 text-left bg-green-700" style="color: white; width: 15%;">College</th>
                    <th class="p-4 text-left bg-green-700" style="color: white; width: 15%;">Roles</th>
                    <th class="p-4 text-left bg-green-700" style="color: white; width: 10%;">Status</th>
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
                            @if ($user->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap p-4">
                            <div class="flex justify-center space-x-2 text-base" onclick="event.stopPropagation()">
                                <button class="{{ $user->is_active ? 'text-indigo-800 hover:bg-blue-100' : 'text-gray-400 cursor-not-allowed' }} rounded-xl p-2 tooltip cursor-pointer"
                                    data-tip="{{ $user->is_active ? 'Manage Course' : 'User must be active to manage courses' }}" 
                                    wire:click="{{ $user->is_active ? "openAssignCourseModal($user->id)" : '' }}"
                                    {{ !$user->is_active ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-book"></i>
                                </button>
                                <button class="text-amber-500 hover:bg-amber-100 rounded-xl p-2 tooltip cursor-pointer"
                                    data-tip="Edit" wire:click="openEditUserModal({{ $user->id }})">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <a href="{{ route('admin.users.report', $user) }}"
                                    class="text-purple-500 hover:bg-purple-100 rounded-xl p-2 tooltip cursor-pointer"
                                    data-tip="Report">
                                    <i class="fa-solid fa-file-chart-column"></i>
                                </a>
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
    <div class="mt-4 px-6">
        {{ $users->links() }}
    </div>

    <!-- User Detail -->
    @if ($selectedUser)
        <x-modal name="user-details-modal" :show="!!$selectedUser" maxWidth="4xl"> 
            <!-- Header -->
            <div class="text-white rounded-t-xl px-6 py-4 flex items-center justify-between" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
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

                        <!-- Role and Status badges positioned on the far right -->
                        <div class="flex flex-col items-end space-y-2">
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
                            
                            <!-- Status Badge -->
                            @if ($selectedUser->is_active)
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Inactive
                                </span>
                                @if ($selectedUser->deactivated_at)
                                    <span class="text-xs text-gray-500">
                                        Since {{ $selectedUser->deactivated_at->format('M d, Y') }}
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Main content with two columns -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left column: User details -->
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
                                <div class="sm:flex sm:items-center">
                                    <dt class="text-sm text-gray-500 font-medium sm:w-32 sm:flex-none sm:pr-4">Account Status
                                    </dt>
                                    <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                        @if ($selectedUser->is_active)
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Inactive
                                            </span>
                                            @if ($selectedUser->deactivated_at)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Deactivated on {{ $selectedUser->deactivated_at->format('M d, Y') }}
                                                </div>
                                            @endif
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

                    <!-- Right column: Assigned Courses -->
                    <div class="space-y-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Assigned Courses</h4>
                            
                            @if($selectedUser->courseAssignments && $selectedUser->courseAssignments->count() > 0)
                                @php
                                    // Group course assignments by program
                                    $groupedAssignments = $selectedUser->courseAssignments->groupBy(function($assignment) {
                                        return $assignment->course->program->id ?? 'no-program';
                                    });
                                @endphp
                                
                                <div class="space-y-4 max-h-80 overflow-y-auto">
                                    @foreach($groupedAssignments as $programId => $assignments)
                                        @php
                                            $program = $assignments->first()->course->program ?? null;
                                        @endphp
                                        
                                        <div class="border border-gray-200 rounded-xl bg-white shadow-sm">
                                            <!-- Program Header -->
                                            @if($program)
                                                <div class="bg-green-100 px-4 py-3 border-b border-gray-200 rounded-t-xl">
                                                    <h5 class="font-semibold text-gray-900 text-xs">
                                                        {{ $program->program_name }} ({{ $program->program_code }})
                                                    </h5>
                                                </div>
                                            @else
                                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                                    <h5 class="font-semibold text-gray-900 text-sm">No Program Assigned</h5>
                                                </div>
                                            @endif

                                            <!-- Courses List -->
                                            <div class="p-3 space-y-2">
                                                @foreach($assignments as $assignment)
                                                    <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                                        <div class="flex-1">
                                                            <div class="flex items-center gap-2">
                                                                <span class="font-medium text-xs text-gray-900">
                                                                    {{ $assignment->course->course_code }}
                                                                </span>
                                                                <span class="text-xs text-gray-600">-</span>
                                                                <span class="text-xs text-gray-700 flex-1">
                                                                    {{ $assignment->course->course_name }}
                                                                </span>
                                                            </div>
                                                            <div class="text-xs text-gray-500 mt-1">
                                                                Semester: {{ $assignment->semester->name ?? 'N/A' }} | 
                                                                Assigned: {{ $assignment->assignment_date ? \Carbon\Carbon::parse($assignment->assignment_date)->format('M d, Y') : 'N/A' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-xl">
                                    <i class="fa-solid fa-book-open text-gray-400 text-3xl mb-2"></i>
                                    <p class="text-gray-500 text-sm">No courses assigned to this user</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeUserDetail" class="bg-white py-2 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 cursor-pointer">
                        Close
                    </button>
                    
                    <!-- Activation/Deactivation Button -->
                    @if ($selectedUser->is_active)
                        <button type="button" wire:click="openDeactivateConfirmationModal({{ $selectedUser->id }})" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 cursor-pointer">
                            <i class="fa-solid fa-user-slash mr-2"></i> Deactivate User
                        </button>
                    @else
                        <button type="button" wire:click="openActivateConfirmationModal({{ $selectedUser->id }})" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 cursor-pointer">
                            <i class="fa-solid fa-user-check mr-2"></i> Activate User
                        </button>
                    @endif
                    
                    <button type="button" 
                        wire:click="{{ $selectedUser->is_active ? "openAssignCourseModal($selectedUser->id)" : '' }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white {{ $selectedUser->is_active ? 'bg-indigo-800 hover:bg-indigo-900' : 'bg-gray-400 cursor-not-allowed' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer"
                        {{ !$selectedUser->is_active ? 'disabled' : '' }}>
                        <i class="fa-solid fa-book mr-2"></i> Manage Course
                    </button>
                    <button type="button" wire:click="openEditUserModal({{ $selectedUser->id }})" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 cursor-pointer">
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
            <div class=" text-white rounded-t-xl px-6 py-4 flex items-center space-x-3" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <i class="fa-solid fa-user-plus text-lg"></i>
                <h3 class="text-xl font-semibold">Add New User</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="space-y-6">
                    <!-- First/Last Name -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 tracking-wide uppercase">First Name</label>
                            <input type="text" wire:model="newUser.firstname"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600"
                                placeholder="Enter first name">
                            @error('newUser.firstname')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 tracking-wide uppercase">Last Name</label>
                            <input type="text" wire:model="newUser.lastname"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600"
                                placeholder="Enter last name">
                            @error('newUser.lastname')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Middle/Extension -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 tracking-wide uppercase">Middle Name</label>
                            <input type="text" wire:model="newUser.middlename"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600"
                                placeholder="Enter middle name">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 tracking-wide uppercase">Extension Name</label>
                            <input type="text" wire:model="newUser.extensionname"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600"
                                placeholder="e.g., Jr., Sr., III">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Email Address</label>
                        <input type="email" wire:model="newUser.email"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600"
                            placeholder="Enter email address">
                        @error('newUser.email')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- College -->
                    <div>
                        <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">College</label>
                        <select wire:model="newUser.college_id"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600">
                            <option value="">Select College</option>
                            @foreach ($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs tracking-wide uppercase font-semibold text-gray-700">Role</label>
                        <select wire:model="newUser.role"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600">
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
                                <h3 class="text-sm font-medium text-blue-800">Account Activation and Password Setup</h3>
                                <div class="mt-1 text-sm text-blue-700">
                                    <p>An email will be sent to the user to verify their email address and allow them to set their new password.</p>
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
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">First Name</label>
                            <input type="text" wire:model="editingUser.firstname"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600"
                                placeholder="Enter first name">
                            @error('editingUser.firstname')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">Last Name</label>
                            <input type="text" wire:model="editingUser.lastname"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600"
                                placeholder="Enter last name">
                            @error('editingUser.lastname')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Middle/Extension -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">Middle Name</label>
                            <input type="text" wire:model="editingUser.middlename"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600"
                                placeholder="Enter middle name">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">Extension Name</label>
                            <input type="text" wire:model="editingUser.extensionname"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600"
                                placeholder="e.g., Jr., Sr., III">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">Email Address</label>
                        <input type="email" wire:model="editingUser.email"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600"
                            placeholder="Enter email address">
                        @error('editingUser.email')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- College -->
                    <div>
                        <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">College</label>
                        <select wire:model="editingUser.college_id"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600">
                            <option value="">Select College</option>
                            @foreach ($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">Role</label>
                        <select wire:model="editingUser.role"
                            class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600">
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
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">New Password (Optional)</label>
                            <input type="password" wire:model="editingUser.password"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600"
                                placeholder="Enter new password">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-700">Confirm Password</label>
                            <input type="password" wire:model="editingUser.password_confirmation"
                                class="mt-2 block w-full rounded-xl border-gray-300 sm:text-sm focus:ring-green-600"
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
    @if ($showDeleteConfirmationModal)
        <x-modal name="delete-confirmation-modal" :show="$showDeleteConfirmationModal" maxWidth="md">
            <!-- Header -->
            <div class="bg-red-600 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-trash text-lg"></i>
                <h3 class="text-xl font-semibold">Confirm Deletion</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="text-center">
                    <i class="fa-solid fa-triangle-exclamation text-red-500 text-4xl mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Are you sure you want to delete this user?</h4>
                    <p class="text-sm text-gray-500 mb-4">
                        This action cannot be undone. This will permanently delete the user account and all associated data.
                    </p>
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
                        <p class="text-sm font-medium text-red-800">
                            User: <strong>{{ $userToDelete->firstname }} {{ $userToDelete->lastname }}</strong>
                        </p>
                        <p class="text-sm text-red-600">{{ $userToDelete->email }}</p>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" wire:click="closeDeleteConfirmationModal"
                        class="bg-white py-2 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteUser"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-xl text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 cursor-pointer">
                        <i class="fa-solid fa-trash mr-2"></i> Delete User
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Deactivate Confirmation Modal -->
    @if ($showDeactivateConfirmationModal)
        <x-modal name="deactivate-confirmation-modal" :show="$showDeactivateConfirmationModal" maxWidth="md">
            <!-- Header -->
            <div class="bg-yellow-600 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-user-slash text-lg"></i>
                <h3 class="text-xl font-semibold">Deactivate User</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="text-center">
                    <i class="fa-solid fa-triangle-exclamation text-yellow-500 text-4xl mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Deactivate User Account?</h4>
                    <p class="text-sm text-gray-500 mb-4">
                        This user will no longer be able to access the system. You can reactivate them later.
                    </p>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                        <p class="text-sm font-medium text-yellow-800">
                            User: <strong>{{ $userToDeactivate->firstname }} {{ $userToDeactivate->lastname }}</strong>
                        </p>
                        <p class="text-sm text-yellow-600">{{ $userToDeactivate->email }}</p>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" wire:click="closeDeactivateConfirmationModal"
                        class="bg-white py-2 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="deactivateUser"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-xl text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 cursor-pointer">
                        <i class="fa-solid fa-user-slash mr-2"></i> Deactivate User
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Activate Confirmation Modal -->
    @if ($showActivateConfirmationModal)
        <x-modal name="activate-confirmation-modal" :show="$showActivateConfirmationModal" maxWidth="md">
            <!-- Header -->
            <div class="bg-green-600 text-white rounded-t-xl px-6 py-4 flex items-center space-x-3">
                <i class="fa-solid fa-user-check text-lg"></i>
                <h3 class="text-xl font-semibold">Activate User</h3>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6 rounded-b-xl">
                <div class="text-center">
                    <i class="fa-solid fa-user-check text-green-500 text-4xl mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Activate User Account?</h4>
                    <p class="text-sm text-gray-500 mb-4">
                        This user will regain access to the system with their previous permissions.
                    </p>
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4">
                        <p class="text-sm font-medium text-green-800">
                            User: <strong>{{ $userToActivate->firstname }} {{ $userToActivate->lastname }}</strong>
                        </p>
                        <p class="text-sm text-green-600">{{ $userToActivate->email }}</p>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" wire:click="closeActivateConfirmationModal"
                        class="bg-white py-2 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="activateUser"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-xl text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 cursor-pointer">
                        <i class="fa-solid fa-user-check mr-2"></i> Activate User
                    </button>
                </div>
            </div>
        </x-modal>
    @endif

    <!-- Manage Course Modal -->
    @if ($showAssignCourseModal)
        <x-modal name="assign-course-modal" :show="$showAssignCourseModal" maxWidth="4xl">
            <!-- Header -->
            <div class="text-white rounded-t-xl px-6 py-4" style="background: linear-gradient(148deg,rgba(18, 67, 44, 1) 0%, rgba(30, 119, 77, 1) 54%, rgba(55, 120, 64, 1) 100%);">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-book-medical text-lg"></i>
                        <div>
                            <h3 class="text-xl font-semibold">Manage Courses</h3>
                            <p class="text-green-100 text-sm">{{ $userToAssignCourse->firstname }} {{ $userToAssignCourse->lastname }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-b-xl">
                <!-- Tabs for Existing vs New Assignments -->
                <div class="mb-6 border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <button type="button"
                            wire:click="switchAssignCourseTab('existing')"
                            class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeAssignCourseTab === 'existing' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Existing Assignments
                            <span class="ml-2 {{ $activeAssignCourseTab === 'existing' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }} text-xs py-0.5 px-2 rounded-full">
                                {{ $userToAssignCourse->courseAssignments->count() }}
                            </span>
                        </button>
                        <button type="button"
                            wire:click="switchAssignCourseTab('new')"
                            class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeAssignCourseTab === 'new' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            New Assignments
                            <span class="ml-2 {{ $activeAssignCourseTab === 'new' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }} text-xs py-0.5 px-2 rounded-full">
                                {{ count($assignCourseData['course_ids'] ?? []) }}
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- Existing Assignments Tab -->
                @if($activeAssignCourseTab === 'existing')
                    <div>
                        <div class="mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Current Course Assignments</h4>
                            <p class="text-sm text-gray-600">Manage existing course assignments for this faculty member.</p>
                        </div>

                        @if($userToAssignCourse->courseAssignments->count() > 0)
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                @foreach($userToAssignCourse->courseAssignments->groupBy('semester_id') as $semesterId => $assignments)
                                    @php
                                        $semester = $assignments->first()->semester;
                                    @endphp
                                    <div class="border border-gray-200 rounded-xl">
                                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 rounded-t-xl">
                                            <h5 class="font-semibold text-gray-900 text-sm">
                                                {{ $semester->name ?? 'No Semester' }}
                                                <span class="text-gray-600 font-normal">({{ $assignments->count() }} courses)</span>
                                            </h5>
                                        </div>
                                        <div class="p-4 space-y-2">
                                            @foreach($assignments as $assignment)
                                                <div class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors">
                                                    <div class="flex items-center space-x-3 flex-1">
                                                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                                            <i class="fa-solid fa-book text-blue-600 text-sm"></i>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-center space-x-2">
                                                                <span class="font-medium text-gray-900 text-sm">
                                                                    {{ $assignment->course->course_code }}
                                                                </span>
                                                                <span class="text-gray-400">•</span>
                                                                <span class="text-gray-600 text-sm truncate">
                                                                    {{ $assignment->course->course_name }}
                                                                </span>
                                                            </div>
                                                            @if($assignment->course->program)
                                                                <div class="text-gray-500 text-xs mt-1">
                                                                    {{ $assignment->course->program->program_name }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <button type="button" 
                                                            wire:click="removeAssignedCourse({{ $assignment->assignment_id }})"
                                                            class="flex-shrink-0 text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition-colors"
                                                            title="Remove this course assignment">
                                                        <i class="fa-solid fa-trash text-sm"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                                <i class="fa-solid fa-book-open text-gray-400 text-4xl mb-3"></i>
                                <p class="text-gray-500 text-lg font-medium">No courses assigned</p>
                                <p class="text-gray-400 text-sm mt-1">This faculty member doesn't have any course assignments yet.</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- New Assignments Tab -->
                @if($activeAssignCourseTab === 'new')
                    <div class="space-y-6">
                        <!-- Semester Selection -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Select Semester for New Assignments</label>
                            <select wire:model.live="assignCourseData.semester_id"
                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm p-3">
                                <option value="">Choose a semester...</option>
                                @foreach($availableSemesters as $semester)
                                    <option value="{{ $semester->id }}">
                                        {{ $semester->name }} 
                                        ({{ \Carbon\Carbon::parse($semester->start_date)->format('M Y') }} - 
                                        {{ \Carbon\Carbon::parse($semester->end_date)->format('M Y') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('assignCourseData.semester_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Two Column Layout for Course Selection -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Available Courses -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-lg font-semibold text-gray-900">List of Courses</h4>
                                </div>

                                <!-- Search -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                                    </div>
                                    <input type="text" 
                                        wire:model.live.debounce.300ms="courseSearch"
                                        placeholder="Search courses..."
                                        class="pl-10 block w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm p-3">
                                </div>

                                <!-- Course List -->
                                @if($availableCourses->count() > 0)
                                    <div class="border border-gray-200 rounded-xl max-h-80 overflow-y-auto">
                                        @foreach($availableCourses as $course)  
                                            <label class="flex items-start space-x-3 p-3 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors cursor-pointer">
                                                <input type="checkbox" 
                                                    wire:model.live="assignCourseData.course_ids"
                                                    value="{{ $course->id }}"
                                                    class="mt-1 rounded border-gray-300 text-green-600 focus:ring-green-500 w-4 h-4 flex-shrink-0">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-medium text-gray-900 text-sm">
                                                            {{ $course->course_code }}
                                                        </span>
                                                        @if(in_array($course->id, $assignCourseData['course_ids'] ?? []))
                                                            <i class="fa-solid fa-check text-green-500 text-sm"></i>
                                                        @endif
                                                    </div>
                                                    <p class="text-gray-600 text-sm mt-1">{{ $course->course_name }}</p>
                                                    @if($course->program)
                                                        <div class="text-amber-500 text-xs font-semibold mt-1 flex items-center">
                                                            {{ $course->program->program_code }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                                        <i class="fa-solid fa-search text-gray-400 text-3xl mb-3"></i>
                                        <p class="text-gray-500 font-medium">
                                            @if($courseSearch)
                                                No courses found for "{{ $courseSearch }}"
                                            @else
                                                All courses are already assigned
                                            @endif
                                        </p>
                                        @if($courseSearch)
                                        <button type="button" wire:click="$set('courseSearch', '')"
                                            class="text-green-600 hover:text-green-800 text-sm font-medium mt-2">
                                            Clear search
                                        </button>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Selected Courses Preview -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-semibold text-gray-900">
                                    Selected Courses
                                    <span class="text-gray-500 font-normal text-sm">
                                        ({{ count($assignCourseData['course_ids'] ?? []) }})
                                    </span>
                                </h4>

                                @if(count($assignCourseData['course_ids'] ?? []) > 0)
                                    <div class="border border-gray-200 rounded-xl max-h-95 overflow-y-auto">
                                        @foreach($this->getSelectedCourses() as $course)
                                            <div class="flex items-center justify-between p-3 border-b border-gray-100 last:border-b-0 bg-green-50 hover:bg-green-100 transition-colors">
                                                <div class="flex items-center space-x-3 flex-1">
                                                    <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                                        <i class="fa-solid fa-plus text-green-600 text-sm"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="font-medium text-green-900 text-sm">
                                                            {{ $course->course_code }}
                                                        </div>
                                                        <div class="text-green-700 text-sm truncate">
                                                            {{ $course->course_name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50">
                                        <i class="fa-solid fa-hand-pointer text-gray-400 text-3xl mb-3"></i>
                                        <p class="text-gray-500 font-medium">No courses selected</p>
                                        <p class="text-gray-400 text-sm mt-1">Select courses from the left panel</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" wire:click="closeAssignCourseModal"
                        class="px-6 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors cursor-pointer">
                        Cancel
                    </button>
                    
                    @if($activeAssignCourseTab === 'new')
                        <button type="button"
                            wire:click="assignCourse"
                            class="px-6 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                            {{ count($assignCourseData['course_ids'] ?? []) === 0 || empty($assignCourseData['semester_id']) ? 'disabled' : '' }}>
                            <i class="fa-solid fa-user-check mr-2"></i>
                            Assign Selected Courses
                        </button>
                    @endif
                </div>
            </div>
        </x-modal>
    @endif
</div>