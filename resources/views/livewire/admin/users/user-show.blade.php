<div class="bg-white p-6 rounded-lg shadow-md sticky top-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">User Details</h2>
        <button data-close-user class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <!-- Profile Picture -->
    <div class="flex justify-center mb-6">
        @if($user->hasMedia('profile_picture'))
            <img class="h-24 w-24 rounded-full object-cover" 
                 src="{{ $user->getFirstMediaUrl('profile_picture') }}" 
                 alt="{{ $user->full_name }}">
        @else
            <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center">
                <span class="text-gray-500 text-2xl font-medium">
                    {{ substr($user->firstname, 0, 1) }}{{ substr($user->lastname, 0, 1) }}
                </span>
            </div>
        @endif
    </div>
    
    <!-- User Information -->
    <div class="space-y-4">
        <div>
            <h3 class="text-sm font-medium text-gray-500">Full Name</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $user->formatted_name }}</p>
        </div>
        
        <div>
            <h3 class="text-sm font-medium text-gray-500">Email</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $user->email }}</p>
            @if($user->email_verified_at)
                <span class="text-xs text-green-600">Verified</span>
            @else
                <span class="text-xs text-red-600">Not Verified</span>
            @endif
        </div>
        
        <div>
            <h3 class="text-sm font-medium text-gray-500">Department</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $user->department_name }}</p>
        </div>
        
        <div>
            <h3 class="text-sm font-medium text-gray-500">College</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $user->college_name }}</p>
        </div>
        
        <div>
            <h3 class="text-sm font-medium text-gray-500">Roles</h3>
            <div class="mt-1 flex flex-wrap gap-2">
                @foreach($user->roles as $role)
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                        {{ $role->name }}
                    </span>
                @endforeach
                @if($user->roles->count() == 0)
                    <span class="text-xs text-gray-500">No roles assigned</span>
                @endif
            </div>
        </div>
        
        <div>
            <h3 class="text-sm font-medium text-gray-500">Account Created</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
        </div>
        
        <div>
            <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('M d, Y') }}</p>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="mt-6 flex space-x-2">
        <a href="{{ route('admin.users.edit', $user) }}" 
           class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none transition ease-in-out duration-150">
            <i class="fas fa-edit mr-2"></i> Edit
        </a>
        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="flex-1">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    onclick="return confirm('Are you sure you want to delete this user?')"
                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none transition ease-in-out duration-150">
                <i class="fas fa-trash mr-2"></i> Delete
            </button>
        </form>
    </div>
</div>