<x-admin.app-layout>
    <div class="flex flex-col md:flex-row gap-4">
        <!-- Users Table (Left Side) -->
        <div class="w-full transition-all duration-300" id="users-table-container">
            @livewire('admin.users.users')
        </div>
        
        <!-- User Show Component (Right Side) -->
        <div class="w-full md:w-1/3 hidden" id="user-show-container">
            <!-- This will be populated dynamically -->
        </div>
    </div>

    <script>
        function showUser(userId) {
            // Show the user details container and adjust the table width
            const userShowContainer = document.getElementById('user-show-container');
            const usersTableContainer = document.getElementById('users-table-container');
            
            userShowContainer.classList.remove('hidden');
            usersTableContainer.classList.remove('w-full');
            usersTableContainer.classList.add('w-full', 'md:w-2/3');
            
            // Show loading state
            userShowContainer.innerHTML = `
                <div class="bg-white p-6 rounded-lg shadow-md sticky top-4">
                    <div class="animate-pulse">
                        <div class="h-6 bg-gray-200 rounded w-1/2 mb-4"></div>
                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded w-2/3 mb-2"></div>
                    </div>
                </div>
            `;
            
            // Fetch user data
            fetch(`/admin/users/${userId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                userShowContainer.innerHTML = data.html;
                
                // Add event listener to the close button
                const closeButton = userShowContainer.querySelector('[data-close-user]');
                if (closeButton) {
                    closeButton.addEventListener('click', hideUserDetails);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                userShowContainer.innerHTML = `
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-red-500">Error loading user details. Please try again.</div>
                        <button onclick="hideUserDetails()" class="mt-4 px-4 py-2 bg-gray-200 rounded-md">Close</button>
                    </div>
                `;
            });
        }
        
        function hideUserDetails() {
            const userShowContainer = document.getElementById('user-show-container');
            const usersTableContainer = document.getElementById('users-table-container');
            
            userShowContainer.classList.add('hidden');
            usersTableContainer.classList.remove('w-full', 'md:w-2/3');
            usersTableContainer.classList.add('w-full');
            userShowContainer.innerHTML = '';
        }
        
        // Close user details when clicking outside
        document.addEventListener('click', function(event) {
            const userShowContainer = document.getElementById('user-show-container');
            
            if (!event.target.closest('#user-show-container') && 
                !event.target.closest('[onclick^="showUser"]') &&
                userShowContainer.innerHTML !== '') {
                hideUserDetails();
            }
        });
    </script>
</x-admin.app-layout>