<div class="flex flex-col gap-4">
    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl">
                <h3 class="text-lg font-bold mb-4">Confirm Deletion</h3>
                <p class="mb-6">Are you sure you want to delete this user? This action cannot be undone.</p>
                
                <div class="flex justify-end gap-3">
                    <button wire:click="closeModal" 
                            class="btn btn-ghost hover:bg-gray-100">
                        Cancel
                    </button>
                    <button wire:click="deleteUser" 
                            class="btn btn-error"
                            wire:loading.attr="disabled"
                            wire:target="deleteUser">
                        <span wire:loading.remove wire:target="deleteUser">Delete</span>
                        <span wire:loading wire:target="deleteUser">
                            <i class="fa-solid fa-spinner fa-spin"></i> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col gap-4 bg-white p-6 w-full rounded-lg shadow-md">
        <div class="bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
                <!-- Add User Button -->
                <button 
                    wire:click="openModal"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md flex items-center"
                >
                    <i class="fas fa-plus mr-2"></i> Add User
                </button>
            </div>

            <!-- Filters -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input 
                        type="text" 
                        wire:model.live="search" 
                        id="search"
                        placeholder="Search users..." 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                </div>

                <!-- Department Filter -->
                <div>
                    <label for="departmentFilter" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select 
                        wire:model.live="departmentFilter" 
                        id="departmentFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- College Filter -->
                <div>
                    <label for="collegeFilter" class="block text-sm font-medium text-gray-700 mb-1">College</label>
                    <select 
                        wire:model.live="collegeFilter" 
                        id="collegeFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">All Colleges</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}">{{ $college->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">Items per page</label>
                    <select 
                        wire:model.live="perPage" 
                        id="perPage"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">College</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($user->hasMedia('profile_picture'))
                                                <img class="h-10 w-10 rounded-full object-cover" src="{{ $user->getFirstMediaUrl('profile_picture') }}" alt="{{ $user->full_name }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <span class="text-gray-500 font-medium">{{ substr($user->firstname, 0, 1) }}{{ substr($user->lastname, 0, 1) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $user->firstname }} 
                                                @if($user->middlename)
                                                    {{ $user->middlename[0] }}.
                                                @endif
                                                {{ $user->lastname }}
                                                @if($user->extensionname)
                                                    {{ $user->extensionname }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $user->department->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $user->college->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @foreach($user->roles as $role)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <!-- View Button -->
                                        <a href="#" 
                                        onclick="showUser({{ $user->id }}); return false;" 
                                        class="btn btn-xs btn-ghost btn-success tooltip" data-tip="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Edit Button -->
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                        class="btn-xs btn-ghost btn-info tooltip"
                                            data-tip="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- Delete Button -->
                                        <a href="#" 
                                        wire:click="confirmDelete({{ $user->id }})" 
                                        class="text-red-600 hover:text-red-900btn btn-xs btn-ghost btn-error tooltip"
                                                data-tip="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
            <div class="relative mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                <!-- Modal header -->
                <div class="flex items-center justify-between pb-4 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">Add New User</h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Modal body -->
                <form wire:submit.prevent="createUser" class="space-y-4 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- First Name -->
                        <div>
                            <label for="firstname" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input 
                                type="text" 
                                wire:model="firstname" 
                                id="firstname"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            @error('firstname') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Last Name -->
                        <div>
                            <label for="lastname" class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input 
                                type="text" 
                                wire:model="lastname" 
                                id="lastname"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            @error('lastname') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Middle Name -->
                        <div>
                            <label for="middlename" class="block text-sm font-medium text-gray-700">Middle Name</label>
                            <input 
                                type="text" 
                                wire:model="middlename" 
                                id="middlename"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            @error('middlename') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Extension Name -->
                        <div>
                            <label for="extensionname" class="block text-sm font-medium text-gray-700">Extension Name</label>
                            <input 
                                type="text" 
                                wire:model="extensionname" 
                                id="extensionname"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            @error('extensionname') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input 
                            type="email" 
                            wire:model="email" 
                            id="email"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input 
                                type="password" 
                                wire:model="password" 
                                id="password"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input 
                                type="password" 
                                wire:model="password_confirmation" 
                                id="password_confirmation"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            @error('password_confirmation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Role Dropdown -->
                    <div>
                        <label for="selectedRole" class="block text-sm font-medium text-gray-700">Role</label>
                        <select 
                            wire:model="selectedRole" 
                            id="selectedRole"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Department -->
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                            <select 
                                wire:model="department_id" 
                                id="department_id"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- College -->
                        <div>
                            <label for="college_id" class="block text-sm font-medium text-gray-700">College</label>
                            <select 
                                wire:model="college_id" 
                                id="college_id"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">Select College</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                            @error('college_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <!-- Modal footer -->
                    <div class="flex justify-end pt-4 border-t mt-6">
                        <button 
                            type="button" 
                            wire:click="closeModal" 
                            class="mr-3 px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                        >
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>