<?php

namespace App\Livewire\Admin\Management;

use Livewire\Component;
use App\Models\User;
use App\Models\College;
use App\Models\Course;
use App\Models\Program;
use App\Models\Semester;
use App\Models\CourseAssignment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCredentialsMail;
use App\Mail\AccountSetupMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

class UserManagement extends Component
{
    public $search = '';
    public $collegeFilter = '';
    
    public $sortField = 'lastname';
    public $sortDirection = 'asc';
    public $selectedUser = null;
    
    public $showAddUserModal = false;
    public $newUser = [
        'firstname' => '',
        'middlename' => '',
        'lastname' => '',
        'extensionname' => '',
        'email' => '',
        'college_id' => '',
        'role' => ''
    ];

    // Edit User Modal Properties
    public $showEditUserModal = false;
    public $editingUser = [
        'id' => '',
        'firstname' => '',
        'middlename' => '',
        'lastname' => '',
        'extensionname' => '',
        'email' => '',
        'college_id' => '',
        'role' => '',
        'password' => '',
        'password_confirmation' => ''
    ];

    // Delete Confirmation Properties
    public $showDeleteConfirmationModal = false;
    public $userToDelete = null;

    // Assign Course Modal Properties
    public $showAssignCourseModal = false;
    public $userToAssignCourse = null;
    public $assignCourseData = [
        'course_ids' => [],
        'semester_id' => ''
    ];
    public $availableCourses = [];
    public $allAvailableCourses = [];
    public $availableSemesters = [];

    public $statusFilter = '';
    public $showDeactivateConfirmationModal = false;
    public $userToDeactivate = null;
    public $showActivateConfirmationModal = false;
    public $userToActivate = null;
    
    // Course Search Property
    public $courseSearch = '';
    public $activeAssignCourseTab = 'existing';

    public function showUser($userId)
    {
        $this->selectedUser = User::with([
            'college',
            'courseAssignments.course.program',
            'courseAssignments.semester'
        ])->find($userId);
    }

    public function closeUserDetail()
    {
        $this->selectedUser = null;
    }

    public function openAddUserModal()
    {
        $this->showAddUserModal = true;
        $this->reset('newUser');
        $this->resetErrorBag();
    }

    public function closeAddUserModal()
    {
        $this->showAddUserModal = false;
        $this->reset('newUser');
        $this->resetErrorBag();
    }

    // Edit User Methods
    public function openEditUserModal($userId)
    {
        $user = User::find($userId);
        
        $this->editingUser = [
            'id' => $user->id,
            'firstname' => $user->firstname,
            'middlename' => $user->middlename,
            'lastname' => $user->lastname,
            'extensionname' => $user->extensionname,
            'email' => $user->email,
            'college_id' => $user->college_id,
            'role' => $user->roles->first() ? $user->roles->first()->id : '',
            'password' => '',
            'password_confirmation' => ''
        ];
        
        $this->showEditUserModal = true;
        $this->resetErrorBag();
    }

    public function closeEditUserModal()
    {
        $this->showEditUserModal = false;
        $this->reset('editingUser');
        $this->resetErrorBag();
    }

    // Delete User Methods
    public function openDeleteConfirmationModal($userId)
    {
        $this->userToDelete = User::find($userId);
        $this->showDeleteConfirmationModal = true;
    }

    public function closeDeleteConfirmationModal()
    {
        $this->showDeleteConfirmationModal = false;
        $this->userToDelete = null;
    }

    // Assign Course Methods
    public function openAssignCourseModal($userId)
    {
        $this->userToAssignCourse = User::with(['courseAssignments.course.program', 'courseAssignments.semester'])->find($userId);
        
        // Reset search and tab when opening modal
        $this->courseSearch = '';
        $this->activeAssignCourseTab = 'existing';
        
        // Load all available courses (without search filter)
        $this->loadAllAvailableCourses();
        
        // Load filtered available courses
        $this->loadAvailableCourses();
            
        $this->availableSemesters = Semester::orderBy('start_date', 'desc')
            ->get();
            
        $this->assignCourseData = [
            'course_ids' => [],
            'semester_id' => ''
        ];
        
        $this->showAssignCourseModal = true;
        $this->resetErrorBag();
    }

    public function switchAssignCourseTab($tab)
    {
        $this->activeAssignCourseTab = $tab;
    }

    public function closeAssignCourseModal()
    {
        $this->showAssignCourseModal = false;
        $this->userToAssignCourse = null;
        $this->reset('assignCourseData');
        $this->reset('courseSearch');
        $this->activeAssignCourseTab = 'existing';
        $this->resetErrorBag();
    }

    // Load all available courses without search filter
    public function loadAllAvailableCourses()
    {
        // Get already assigned course IDs for this user
        $assignedCourseIds = CourseAssignment::where('professor_id', $this->userToAssignCourse->id)
            ->pluck('course_id')
            ->toArray();
        
        $this->allAvailableCourses = Course::with('program')
            ->whereNotIn('id', $assignedCourseIds)
            ->orderBy('course_code')
            ->get();
    }

    // Load available courses with search filter
    public function loadAvailableCourses()
    {
        // Get already assigned course IDs for this user
        $assignedCourseIds = CourseAssignment::where('professor_id', $this->userToAssignCourse->id)
            ->pluck('course_id')
            ->toArray();
        
        $query = Course::with('program')
            ->whereNotIn('id', $assignedCourseIds);
        
        // Apply search filter if provided
        if (!empty($this->courseSearch)) {
            $searchTerm = '%' . $this->courseSearch . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('course_code', 'like', $searchTerm)
                  ->orWhere('course_name', 'like', $searchTerm)
                  ->orWhereHas('program', function($programQuery) use ($searchTerm) {
                      $programQuery->where('program_code', 'like', $searchTerm)
                                 ->orWhere('program_name', 'like', $searchTerm);
                  });
            });
        }
        
        $this->availableCourses = $query->orderBy('course_code')->get();
    }

    // Update available courses when search changes
    public function updatedCourseSearch()
    {
        $this->loadAvailableCourses();
    }

    // Force update when course selection changes
    public function updatedAssignCourseDataCourseIds()
    {
        // This forces the component to re-render when courses are selected/deselected
        $this->dispatch('course-selection-updated');
    }

    // Get course details from all available courses
    public function getSelectedCourses()
    {
        if (empty($this->assignCourseData['course_ids'])) {
            return collect();
        }

        return $this->allAvailableCourses->whereIn('id', $this->assignCourseData['course_ids']);
    }

    public function assignCourse()
    {
        try {
            \Log::info('Starting course assignment process');
            
            $this->validate([
                'assignCourseData.course_ids' => 'required|array|min:1',
                'assignCourseData.course_ids.*' => 'exists:courses,id',
                'assignCourseData.semester_id' => 'required|exists:semesters,id',
            ], [
                'assignCourseData.course_ids.required' => 'Please select at least one course.',
                'assignCourseData.course_ids.min' => 'Please select at least one course.',
                'assignCourseData.semester_id.required' => 'Please select a semester.',
            ]);

            \Log::info('Validation passed', [
                'course_ids' => $this->assignCourseData['course_ids'],
                'semester_id' => $this->assignCourseData['semester_id'],
                'user_id' => $this->userToAssignCourse->id
            ]);

            $assignedCourses = [];
            $alreadyAssignedCourses = [];

            foreach ($this->assignCourseData['course_ids'] as $courseId) {
                \Log::info('Processing course', ['course_id' => $courseId]);
                
                // Check if the course is already assigned to this professor in the same semester
                $existingAssignment = CourseAssignment::where('professor_id', $this->userToAssignCourse->id)
                    ->where('course_id', $courseId)
                    ->where('semester_id', $this->assignCourseData['semester_id'])
                    ->first();

                if ($existingAssignment) {
                    $course = Course::find($courseId);
                    if ($course) {
                        $alreadyAssignedCourses[] = $course->course_code . ' - ' . $course->course_name;
                    } else {
                        $alreadyAssignedCourses[] = 'Course ID: ' . $courseId;
                    }
                    \Log::info('Course already assigned', ['course_id' => $courseId]);
                    continue;
                }

                // Create the course assignment
                $assignment = CourseAssignment::create([
                    'professor_id' => $this->userToAssignCourse->id,
                    'course_id' => $courseId,
                    'semester_id' => $this->assignCourseData['semester_id'],
                    'assignment_date' => now()->format('Y-m-d'),
                ]);

                \Log::info('Course assignment created', ['assignment_id' => $assignment->assignment_id]);

                $course = Course::find($courseId);
                if ($course) {
                    $assignedCourses[] = $course->course_code . ' - ' . $course->course_name;
                } else {
                    $assignedCourses[] = 'Course ID: ' . $courseId;
                }
            }

            $userName = $this->userToAssignCourse->firstname . ' ' . $this->userToAssignCourse->lastname;

            // Prepare notification message
            $notificationMessage = '';
            $notificationType = 'success';

            if (!empty($assignedCourses)) {
                $notificationMessage = count($assignedCourses) . " course(s) assigned to '{$userName}' successfully!";
                if (count($assignedCourses) <= 3) {
                    $notificationMessage .= " Assigned: " . implode(', ', $assignedCourses);
                }
                
                // If there are also already assigned courses, show warning
                if (!empty($alreadyAssignedCourses)) {
                    $notificationType = 'warning';
                    $notificationMessage .= " | " . count($alreadyAssignedCourses) . " course(s) were already assigned: " . implode(', ', $alreadyAssignedCourses);
                }
            } else {
                // No courses were assigned (all were already assigned)
                $notificationType = 'warning';
                $notificationMessage = count($alreadyAssignedCourses) . " course(s) were already assigned to '{$userName}' for the selected semester: " . implode(', ', $alreadyAssignedCourses);
            }

            \Log::info('Course assignment completed', [
                'assigned_count' => count($assignedCourses),
                'already_assigned_count' => count($alreadyAssignedCourses),
                'notification_type' => $notificationType
            ]);

            // Store user ID before closing modal
            $selectedUserId = $this->selectedUser ? $this->selectedUser->id : null;
            $assignedUserId = $this->userToAssignCourse->id;

            $this->closeAssignCourseModal();
            
            $this->dispatch('showNotification', 
                type: $notificationType, 
                content: $notificationMessage
            );

            // Refresh the selected user if it's the same user - do this after closing modal
            if ($selectedUserId && $selectedUserId == $assignedUserId) {
                $this->selectedUser = User::with([
                    'college',
                    'courseAssignments.course.program',
                    'courseAssignments.semester'
                ])->find($selectedUserId);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to assign courses: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Failed to assign courses. Please try again. Error: ' . $e->getMessage()
            );
        }
    }

    public function deleteUser()
    {
        if ($this->userToDelete) {
            $userName = $this->userToDelete->firstname . ' ' . $this->userToDelete->lastname;
            $userId = $this->userToDelete->id;
            $this->userToDelete->delete();
            
            $this->closeDeleteConfirmationModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "User '{$userName}' deleted successfully!"
            );
            
            if ($this->selectedUser && $this->selectedUser->id == $userId) {
                $this->closeUserDetail();
            }
        }
    }

    public function updateUser()
    {
        try {
            $this->validate([
                'editingUser.firstname' => 'required|string|max:255',
                'editingUser.middlename' => 'nullable|string|max:255',
                'editingUser.lastname' => 'required|string|max:255',
                'editingUser.extensionname' => 'nullable|string|max:255',
                'editingUser.email' => 'required|string|email|max:255|unique:users,email,' . $this->editingUser['id'],
                'editingUser.college_id' => 'nullable|exists:colleges,id',
                'editingUser.password' => 'nullable|confirmed|min:8',
                'editingUser.role' => 'required|exists:roles,id',
            ], [
                'editingUser.firstname.required' => 'First name is required.',
                'editingUser.lastname.required' => 'Last name is required.',
                'editingUser.email.required' => 'Email is required.',
                'editingUser.email.unique' => 'This email is already in use.',
                'editingUser.password.confirmed' => 'Password confirmation does not match.',
                'editingUser.password.min' => 'Password must be at least 8 characters.',
                'editingUser.role.required' => 'Role is required.',
            ]);

            // Check if another user with same first and last name already exists
            $existingUser = User::where('firstname', $this->editingUser['firstname'])
                ->where('lastname', $this->editingUser['lastname'])
                ->where('id', '!=', $this->editingUser['id'])
                ->first();
                
            if ($existingUser) {
                $this->dispatch('showNotification', 
                    type: 'warning', 
                    content: "Another user with the name '{$this->editingUser['firstname']} {$this->editingUser['lastname']}' already exists."
                );
                return;
            }

            $user = User::find($this->editingUser['id']);
            $userName = $user->firstname . ' ' . $user->lastname;
            
            $updateData = [
                'firstname' => $this->editingUser['firstname'],
                'middlename' => $this->editingUser['middlename'],
                'lastname' => $this->editingUser['lastname'],
                'extensionname' => $this->editingUser['extensionname'],
                'email' => $this->editingUser['email'],
                'college_id' => $this->editingUser['college_id'],
            ];

            // Only update password if provided
            if (!empty($this->editingUser['password'])) {
                $updateData['password'] = Hash::make($this->editingUser['password']);
            }

            $user->update($updateData);

            // Update role
            $role = Role::find($this->editingUser['role']);
            $user->syncRoles([$role->name]);

            $this->closeEditUserModal();
            
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "User '{$userName}' updated successfully!"
            );

            // Refresh the selected user if it's the same user
            if ($this->selectedUser && $this->selectedUser->id == $user->id) {
                $this->selectedUser = $user->fresh(['college']);
            }

        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Failed to update user. Please try again.'
            );
        }
    }

    public function addUser()
    {
        try {
            $this->validate([
                'newUser.firstname' => 'required|string|max:255',
                'newUser.middlename' => 'nullable|string|max:255',
                'newUser.lastname' => 'required|string|max:255',
                'newUser.extensionname' => 'nullable|string|max:255',
                'newUser.email' => 'required|string|email|max:255|unique:users,email',
                'newUser.college_id' => 'nullable|exists:colleges,id',
                'newUser.role' => 'required|exists:roles,id',
            ], [
                'newUser.firstname.required' => 'First name is required.',
                'newUser.lastname.required' => 'Last name is required.',
                'newUser.email.required' => 'Email is required.',
                'newUser.email.unique' => 'This email is already in use.',
                'newUser.role.required' => 'Role is required.',
            ]);

            // Check if user with same first and last name already exists
            $existingUser = User::where('firstname', $this->newUser['firstname'])
                ->where('lastname', $this->newUser['lastname'])
                ->first();
                
            if ($existingUser) {
                $this->dispatch('showNotification', 
                    type: 'warning', 
                    content: "A user with the name '{$this->newUser['firstname']} {$this->newUser['lastname']}' already exists."
                );
                return;
            }

            // Generate a random password
            $generatedPassword = Str::password(12);

            // Create the user
            $user = User::create([
                'firstname' => $this->newUser['firstname'],
                'middlename' => $this->newUser['middlename'],
                'lastname' => $this->newUser['lastname'],
                'extensionname' => $this->newUser['extensionname'],
                'email' => $this->newUser['email'],
                'college_id' => $this->newUser['college_id'],
                'password' => Hash::make($generatedPassword),
            ]);

            // Assign role
            $role = Role::find($this->newUser['role']);
            $user->assignRole($role->name);

            // Send email with credentials
            try {
                Mail::to($user->email)->send(new AccountSetupMail($user, $generatedPassword));
            } catch (\Exception $mailException) {
                // Log mail error but don't fail the user creation
                \Log::error('Failed to send account setup email: ' . $mailException->getMessage());
            }

            $this->closeAddUserModal();
            
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "User '{$user->firstname} {$user->lastname}' created successfully! Account setup email sent."
            );

        } catch (\Exception $e) {
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Failed to create user. Please try again.'
            );
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    public function openDeactivateConfirmationModal($userId)
    {
        $this->userToDeactivate = User::find($userId);
        $this->showDeactivateConfirmationModal = true;
    }

    public function closeDeactivateConfirmationModal()
    {
        $this->showDeactivateConfirmationModal = false;
        $this->userToDeactivate = null;
    }

    public function deactivateUser()
    {
        if ($this->userToDeactivate) {
            $this->userToDeactivate->update([
                'is_active' => false,
                'deactivated_at' => now(),
            ]);

            $userName = $this->userToDeactivate->firstname . ' ' . $this->userToDeactivate->lastname;
            $userId = $this->userToDeactivate->id;
            
            $this->closeDeactivateConfirmationModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "User '{$userName}' has been deactivated successfully!"
            );

            // Store the ID before clearing the user object
            if ($this->selectedUser && $this->selectedUser->id == $userId) {
                $this->selectedUser = $this->selectedUser->fresh();
            }
        }
    }

    public function openActivateConfirmationModal($userId)
    {
        $this->userToActivate = User::find($userId);
        $this->showActivateConfirmationModal = true;
    }

    public function closeActivateConfirmationModal()
    {
        $this->showActivateConfirmationModal = false;
        $this->userToActivate = null;
    }

    public function activateUser()
    {
        if ($this->userToActivate) {
            $this->userToActivate->update([
                'is_active' => true,
                'deactivated_at' => null,
            ]);

            $userName = $this->userToActivate->firstname . ' ' . $this->userToActivate->lastname;
            $userId = $this->userToActivate->id;
            
            $this->closeActivateConfirmationModal();
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "User '{$userName}' has been activated successfully!"
            );

            // Store the ID before clearing the user object
            if ($this->selectedUser && $this->selectedUser->id == $userId) {
                $this->selectedUser = $this->selectedUser->fresh();
            }
        }
    }

    public function removeAssignedCourse($assignmentId)
    {
        try {
            \Log::info("Removing course assignment with ID: {$assignmentId}");
            
            $assignment = CourseAssignment::where('assignment_id', $assignmentId)->first();
            
            if (!$assignment) {
                \Log::error("Course assignment not found with ID: {$assignmentId}");
                $this->dispatch('showNotification', 
                    type: 'error', 
                    content: 'Course assignment not found.'
                );
                return;
            }

            // Load the course relationship to ensure we can access course details
            $assignment->load('course');
            
            if (!$assignment->course) {
                \Log::error("Course not found for assignment ID: {$assignmentId}");
                $this->dispatch('showNotification', 
                    type: 'error', 
                    content: 'Course details not found.'
                );
                return;
            }

            $courseName = $assignment->course->course_code . ' - ' . $assignment->course->course_name;
            \Log::info("Removing course: {$courseName}");
            
            $assignment->delete();
            
            // Refresh the user data
            if ($this->selectedUser) {
                $this->selectedUser = $this->selectedUser->fresh(['courseAssignments.course.program', 'courseAssignments.semester']);
            }
            
            // Also refresh the user in assign course modal if it's open
            if ($this->showAssignCourseModal && $this->userToAssignCourse) {
                $this->userToAssignCourse = $this->userToAssignCourse->fresh(['courseAssignments.course.program', 'courseAssignments.semester']);
                // Reload available courses since one was removed
                $this->loadAllAvailableCourses();
                $this->loadAvailableCourses();
            }
            
            $this->dispatch('showNotification', 
                type: 'success', 
                content: "Course '{$courseName}' removed successfully!"
            );
            
        } catch (\Exception $e) {
            \Log::error('Failed to remove course assignment: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Failed to remove course. Please try again. Error: ' . $e->getMessage()
            );
        }
    }


    public function render()
    {
        $query = User::with(['college', 'roles'])
            ->where(function ($q) {
                $q->where('firstname', 'like', '%' . $this->search . '%')
                ->orWhere('middlename', 'like', '%' . $this->search . '%')
                ->orWhere('lastname', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->orWhereHas('college', function ($collegeQuery) {
                    $collegeQuery->where('name', 'like', '%' . $this->search . '%');
                });
            });

        if ($this->collegeFilter) {
            $query->where('college_id', $this->collegeFilter);
        }

        // Add status filter
        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $users = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $colleges = College::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('livewire.admin.management.user-management', compact('users', 'colleges', 'roles'));
    }
}