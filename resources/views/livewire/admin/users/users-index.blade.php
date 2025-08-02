<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <!-- Search and Filter Section -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
            <div class="w-full sm:w-1/2">
                <input type="text" 
                        wire:model.debounce.300ms="search" 
                        placeholder="Search users..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <select wire:model="departmentFilter" class="px-4 py-2 border border-gray-300 rounded-md">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
                <select wire:model="collegeFilter" class="px-4 py-2 border border-gray-300 rounded-md">
                    <option value="">All Colleges</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->id }}">{{ $college->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Users Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Department/College
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Roles
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($user->hasMedia('profile_picture'))
                                            <img class="h-10 w-10 rounded-full" 
                                                    src="{{ $user->getFirstMediaUrl('profile_picture') }}" 
                                                    alt="{{ $user->full_name }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                <span class="text-gray-600">{{ strtoupper(substr($user->firstname, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $user->full_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $user->formatted_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $user->department->name ?? 'N/A' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $user->college->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        <span class="px-2 py-1 text-xs rounded-full 
                                                    {{ $role->name === 'super-admin' ? 'bg-purple-100 text-purple-800' : 
                                                    ($role->name === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                        class="text-blue-600 hover:text-blue-900"
                                        title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                        class="text-indigo-600 hover:text-indigo-900"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button wire:click="confirmDelete({{ $user->id }})" 
                                            class="text-red-600 hover:text-red-900"
                                            title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                No users found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(method_exists($users, 'links'))
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>


<!-- Delete Confirmation Modal -->
@if($confirmingDeletion)
    <div class="fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Delete User
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to delete this user? This action cannot be undone.
                        </p>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="deleteUser" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button wire:click="$set('confirmingDeletion', false)" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif