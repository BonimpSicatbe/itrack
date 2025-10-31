<?php

namespace App\Livewire\User\Notification;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Requirement;
use App\Models\SubmittedRequirement;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Features\SupportRedirects\Redirector;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends Component
{
    use WithFileUploads;
    
    public $notifications = [];
    public $selectedNotification = null;
    public $selectedNotificationData = null;
    public $activeTab = 'all';
    public $hasUnreadNotifications = false;

    public $notificationIdFromUrl = null;

    public function mount(): void
    {
        $this->loadNotifications();
        $this->updateUnreadStatus();
        
        // Check if there's a notification ID in the URL
        $this->notificationIdFromUrl = request()->query('notification');
        
        // If there's a notification ID in URL, automatically select it
        if ($this->notificationIdFromUrl) {
            $this->selectNotification($this->notificationIdFromUrl);
        }
    }

    public function markAsReadAndNavigate(string $notificationId): Redirector
    {
        // Mark as read first
        $notification = Auth::user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification && $notification->unread()) {
            $notification->markAsRead();
        }

        // Redirect to notification page with the specific notification selected
        return redirect()->route('user.notifications', ['notification' => $notificationId]);
    }

    public function viewAllNotifications(): Redirector
    {
        return redirect()->route('user.notifications');
    }

    public function loadNotifications(): void
    {
        // Get all notifications first
        $allNotifications = Auth::user()
            ->notifications()
            ->latest()
            ->take(100)
            ->get();

        // Filter notifications to only show those related to active semester requirements
        $this->notifications = $allNotifications->filter(function ($notification) {
            $requirementId = data_get($notification->data, 'requirement_id')
                ?? data_get($notification->data, 'requirement.id');

            if (!$requirementId) {
                // If no requirement ID, keep the notification (might be system notifications)
                return true;
            }

            // Check if the requirement belongs to an active semester
            $requirement = Requirement::where('id', $requirementId)
                ->whereHas('semester', function ($query) {
                    $query->where('is_active', true);
                })
                ->first();

            return $requirement !== null;
        })->values(); // Reset array keys
        
        // Update unread status after loading notifications
        $this->updateUnreadStatus();
    }

    public function updateUnreadStatus(): void
    {
        $this->hasUnreadNotifications = $this->notifications->contains(function ($notification) {
            return $notification->unread();
        });
    }

    public function getFilteredNotifications()
    {
        if ($this->activeTab === 'unread') {
            return $this->notifications->filter(function ($notification) {
                return $notification->unread();
            });
        } elseif ($this->activeTab === 'read') {
            return $this->notifications->filter(function ($notification) {
                return !$notification->unread();
            });
        }

        return $this->notifications;
    }

    public function updatedActiveTab(): void
    {
        // Reset selection when changing tabs
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
    }

    public function toggleAllReadStatus(): void
    {
        // Get all notification IDs from filtered notifications
        $notificationIds = $this->notifications->pluck('id')->toArray();
        
        if ($this->hasUnreadNotifications) {
            // Mark all as read
            Auth::user()->notifications()
                ->whereIn('id', $notificationIds)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
                
            session()->flash('message', 'All active semester notifications marked as read.');
        } else {
            // Mark all as unread
            Auth::user()->notifications()
                ->whereIn('id', $notificationIds)
                ->whereNotNull('read_at')
                ->update(['read_at' => null]);
                
            session()->flash('message', 'All active semester notifications marked as unread.');
        }
        
        $this->loadNotifications();
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
        
        // Update the unread status
        $this->updateUnreadStatus();
        
        // If we're on unread tab and marking all as read, switch to all tab
        if ($this->hasUnreadNotifications && $this->activeTab === 'unread') {
            $this->activeTab = 'all';
        }
    }

    public function selectNotification(string $id): void
    {
        $this->selectedNotification = $id;

        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        if ($notification->unread()) {
            $notification->markAsRead();
            $this->loadNotifications();
            $this->updateUnreadStatus();
        }

        // Base info
        $data = [
            'type'       => data_get($notification->data, 'type'),
            'message'    => data_get($notification->data, 'message', ''),
            'created_at' => $notification->created_at,
            'unread'     => false,
        ];

        // Handle different notification types
        $notificationType = data_get($notification->data, 'type');
        
        if ($notificationType === 'due_date_reminder') {
            $this->handleDueDateReminderNotification($notification, $data);
        } 
        elseif ($notificationType === 'submission_status_updated') {
            $this->handleSubmissionStatusNotification($notification, $data);
        } 
        else {
            $this->handleRequirementNotification($notification, $data);
        }

        $this->selectedNotificationData = $data;
    }

    protected function handleRequirementNotification($notification, &$data): void
    {
        // IDs may be in different keys
        $requirementId = data_get($notification->data, 'requirement_id')
            ?? data_get($notification->data, 'requirement.id');

        $submissionId  = data_get($notification->data, 'submission_id');

        // Load requirement details
        if ($requirementId) {
            $requirement = Requirement::with(['creator', 'updater', 'archiver'])
                ->find($requirementId);

            if ($requirement) {
                $assignedToData = $this->formatAssignedToDisplay($requirement->assigned_to);
                
                $data['requirement'] = [
                    'id' => $requirement->id,
                    'name' => $requirement->name,
                    'description' => $requirement->description,
                    'due' => $requirement->due,
                    'assigned_to' => $assignedToData,
                    'status' => $requirement->status,
                    'created_at' => $requirement->created_at,
                    'updated_at' => $requirement->updated_at,
                ];
                
                // For new requirement notifications, show all programs in the display
                if (!empty($assignedToData['all_programs'])) {
                    $data['requirement']['program_display'] = implode(', ', $assignedToData['all_programs']);
                } else {
                    $data['requirement']['program_display'] = $assignedToData['program'];
                }
            }
        }

        // Load submission details if available (for submission-related notifications)
        if ($submissionId) {
            $submission = SubmittedRequirement::with(['reviewer', 'course.program', 'user', 'media'])
                ->find($submissionId);

            if ($submission) {
                $data['submissions'] = [$this->formatSubmission($submission)];
                $this->loadFiles([$submission], $data);
                
                // Load submitter data
                $data['submitter'] = [
                    'id' => $submission->user->id,
                    'name' => $submission->user->full_name ?? $submission->user->name,
                    'email' => $submission->user->email,
                ];

                // Load course data with program
                if ($submission->course) {
                    $data['course'] = [
                        'id' => $submission->course->id,
                        'course_code' => $submission->course->course_code,
                        'course_name' => $submission->course->course_name,
                        'program' => $submission->course->program ? [
                            'id' => $submission->course->program->id,
                            'program_code' => $submission->course->program->program_code,
                            'program_name' => $submission->course->program->program_name,
                        ] : null,
                    ];
                    
                    // Add program to requirement data for display
                    if (!isset($data['requirement']['program_display']) && $submission->course->program) {
                        $data['requirement']['program_display'] = $submission->course->program->program_name;
                    }
                }
            }
        } else {
            // For new requirement notifications, check if there's user/course data in notification
            $userId = data_get($notification->data, 'user_id');
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $data['submitter'] = [
                        'id' => $user->id,
                        'name' => $user->full_name ?? $user->name,
                        'email' => $user->email,
                    ];
                }
            }

            // Load course data from notification
            if (isset($notification->data['course_id']) && $notification->data['course_id']) {
                $course = \App\Models\Course::with('program')->find($notification->data['course_id']);
                if ($course) {
                    $data['course'] = [
                        'id' => $course->id,
                        'course_code' => $course->course_code,
                        'course_name' => $course->course_name,
                        'program' => $course->program ? [
                            'id' => $course->program->id,
                            'program_code' => $course->program->program_code,
                            'program_name' => $course->program->program_name,
                        ] : null,
                    ];
                    
                    // Add program to requirement data for display
                    if (!isset($data['requirement']['program_display']) && $course->program) {
                        $data['requirement']['program_display'] = $course->program->program_name;
                    }
                }
            }
        }
        
        // If we still don't have program data, try to get it from the requirement's assigned_to
        if (!isset($data['requirement']['program_display']) && isset($data['requirement']['assigned_to'])) {
            $programData = $this->formatAssignedToDisplay($data['requirement']['assigned_to']);
            if (!empty($programData['all_programs'])) {
                $data['requirement']['program_display'] = implode(', ', $programData['all_programs']);
            } else {
                $data['requirement']['program_display'] = $programData['program'];
            }
        }
    }


    protected function handleSubmissionStatusNotification($notification, &$data): void
    {
        $submissionId = data_get($notification->data, 'submission_id');
        $requirementId = data_get($notification->data, 'requirement_id');

        // Get submission details
        $submission = SubmittedRequirement::with(['requirement', 'user', 'course.program', 'media'])
            ->where('id', $submissionId)
            ->where('user_id', Auth::id())
            ->first();

        if ($submission) {
            $data['submissions'] = [$this->formatSubmission($submission)];
            $this->loadFiles([$submission], $data);

            $data['requirement'] = [
                'id' => $submission->requirement->id,
                'name' => $submission->requirement->name,
                'description' => $submission->requirement->description,
                'due' => $submission->requirement->due,
                'status' => $submission->requirement->status,
            ];

            $data['status_update'] = [
                'old_status' => data_get($notification->data, 'old_status'),
                'new_status' => data_get($notification->data, 'new_status'),
                'old_status_label' => $this->statusLabel(data_get($notification->data, 'old_status')),
                'new_status_label' => $this->statusLabel(data_get($notification->data, 'new_status')),
                'reviewed_by' => data_get($notification->data, 'reviewed_by'),
                'reviewed_at' => data_get($notification->data, 'reviewed_at'),
            ];

            // Load submitter data
            $data['submitter'] = [
                'id' => $submission->user->id,
                'name' => $submission->user->full_name ?? $submission->user->name,
                'email' => $submission->user->email,
            ];

            // Load course data with program
            if ($submission->course) {
                $data['course'] = [
                    'id' => $submission->course->id,
                    'course_code' => $submission->course->course_code,
                    'course_name' => $submission->course->course_name,
                    'program' => $submission->course->program ? [
                        'id' => $submission->course->program->id,
                        'program_code' => $submission->course->program->program_code,
                        'program_name' => $submission->course->program->program_name,
                    ] : null,
                ];
                
                // Add program to requirement data for display
                if ($submission->course->program) {
                    $data['requirement']['program_display'] = $submission->course->program->program_name;
                }
            }
            
            // If we still don't have program data, try to get it from the requirement's assigned_to
            if (!isset($data['requirement']['program_display']) && $submission->requirement->assigned_to) {
                $programData = $this->formatAssignedToDisplay($submission->requirement->assigned_to);
                $data['requirement']['program_display'] = $programData['program'];
            }
        }
    }

    protected function formatSubmission($submission)
    {
        $statusLabel = match($submission->status) {
            SubmittedRequirement::STATUS_UNDER_REVIEW => 'Under Review',
            SubmittedRequirement::STATUS_REVISION_NEEDED => 'Revision Required',
            SubmittedRequirement::STATUS_REJECTED => 'Rejected',
            SubmittedRequirement::STATUS_APPROVED => 'Approved',
            default => ucfirst($submission->status),
        };

        return [
            'id' => $submission->id,
            'status' => $submission->status,
            'status_label' => $statusLabel,
            'admin_notes' => $submission->admin_notes,
            'submitted_at' => $submission->submitted_at,
            'reviewed_at' => $submission->reviewed_at,
            'course' => $submission->course ? [
                'id' => $submission->course->id,
                'course_code' => $submission->course->course_code,
                'course_name' => $submission->course->course_name,
                'program' => $submission->course->program ? [
                    'id' => $submission->course->program->id,
                    'program_code' => $submission->course->program->program_code,
                    'program_name' => $submission->course->program->program_name,
                ] : null,
            ] : null,
            'reviewer' => $submission->reviewer ? [
                'id' => $submission->reviewer->id,
                'name' => $submission->reviewer->name,
                'email' => $submission->reviewer->email,
            ] : null,
        ];
    }

    protected function loadFiles($submissions, &$data)
    {
        $files = [];
        
        foreach ($submissions as $submission) {
            foreach ($submission->getMedia('submission_files') as $media) {
                $extension = strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION));
                $isPreviewable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
                
                $files[] = [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'mime_type' => $media->mime_type,
                    'size' => $this->formatFileSize($media->size),
                    'created_at' => $media->created_at,
                    'extension' => $extension,
                    'is_previewable' => $isPreviewable,
                    'status' => $submission->status,
                    'admin_notes' => $submission->admin_notes,
                    'submission_id' => $submission->id,
                    'course' => $submission->course ? [
                        'id' => $submission->course->id,
                        'course_code' => $submission->course->course_code,
                        'course_name' => $submission->course->course_name,
                        'program' => $submission->course->program ? [
                            'id' => $submission->course->program->id,
                            'program_code' => $submission->course->program->program_code,
                            'program_name' => $submission->course->program->program_name,
                        ] : null,
                    ] : null,
                ];
            }
        }

        $data['files'] = $files;
    }

    public function markAllAsRead()
    {
        $notificationIds = $this->notifications->pluck('id')->toArray();
        
        Auth::user()->notifications()
            ->whereIn('id', $notificationIds)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        $this->loadNotifications();
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
        
        $this->dispatch('notifications-marked-read');
        
        session()->flash('message', 'All notifications marked as read!');
    }

    public function markAllAsUnread()
    {
        $notificationIds = $this->notifications->pluck('id')->toArray();
        
        $readNotifications = Auth::user()->notifications()
            ->whereIn('id', $notificationIds)
            ->whereNotNull('read_at')
            ->get();
        
        $readNotifications->each(function ($notification) {
            $notification->markAsUnread();
        });
        
        $this->loadNotifications();
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
        
        $this->dispatch('notifications-marked-unread', count: $readNotifications->count());
        
        session()->flash('message', 'All notifications marked as unread!');
    }

    public function markAsUnread($id)
    {
        $notification = DatabaseNotification::find($id);
        
        if ($notification && !$notification->unread()) {
            $notification->markAsUnread();
            $this->loadNotifications();
            
            // Update the current notification data if it's the selected one
            if ($this->selectedNotification === $id) {
                $this->selectedNotificationData['unread'] = true;
            }
            
            // Add this dispatch to update the navigation count
            $this->dispatch('notification-unread');
            
            session()->flash('message', 'Notification marked as unread!');
        }
    }

    public function formatStatus($status)
    {
        return match($status) {
            'under_review' => 'Under Review',
            'revision_needed' => 'Revision Required',
            'rejected' => 'Rejected',
            'approved' => 'Approved',
            default => ucfirst(str_replace('_', ' ', $status))
        };
    }

    protected function statusLabel(?string $status): string
    {
        return match ($status) {
            SubmittedRequirement::STATUS_UNDER_REVIEW     => 'Under Review',
            SubmittedRequirement::STATUS_REVISION_NEEDED  => 'Revision Required',
            SubmittedRequirement::STATUS_REJECTED         => 'Rejected',
            SubmittedRequirement::STATUS_APPROVED         => 'Approved',
            default                                        => $status ? ucfirst($status) : '',
        };
    }

    protected function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    protected function formatAssignedToDisplay($assignedTo): array
    {
        $currentUser = Auth::user();
        $currentProgramId = $currentUser->program_id;
        
        $programDisplay = '';
        $allPrograms = [];
        $displayPrograms = []; // Programs to display in the list
        
        if (is_array($assignedTo)) {
            // Handle programs
            if (isset($assignedTo['selectAllPrograms']) && $assignedTo['selectAllPrograms']) {
                $programDisplay = 'All Programs';
                // Get all program names for display
                $allPrograms = Program::pluck('program_name')->toArray();
                $displayPrograms = $allPrograms;
            } elseif (isset($assignedTo['programs']) && is_array($assignedTo['programs'])) {
                $programIds = array_filter(array_map('intval', $assignedTo['programs']));
                
                if (!empty($programIds)) {
                    // Get all program names for the assigned program IDs
                    $assignedPrograms = Program::whereIn('id', $programIds)->get();
                    $allPrograms = $assignedPrograms->pluck('program_name')->toArray();
                    $displayPrograms = $allPrograms;
                    
                    // For display, show at least 2 programs then "etc."
                    if (count($allPrograms) > 0) {
                        if (count($allPrograms) <= 2) {
                            $programDisplay = implode(', ', $allPrograms);
                        } else {
                            // Show first 2 programs + etc.
                            $shownPrograms = array_slice($allPrograms, 0, 2);
                            $programDisplay = implode(', ', $shownPrograms) . ', etc.';
                        }
                    } else {
                        $programDisplay = count($programIds) . ' program(s)';
                    }
                }
            }
        }
        
        return [
            'program' => $programDisplay ?: 'Not assigned to programs',
            'all_programs' => $allPrograms, // Include all program names for detailed display
            'display_programs' => $displayPrograms // Programs to show in the list view
        ];
    }

    protected function getProgramFromCourse($courseId): ?array
    {
        $course = \App\Models\Course::with('program')->find($courseId);
        
        if ($course && $course->program) {
            return [
                'id' => $course->program->id,
                'program_code' => $course->program->program_code,
                'program_name' => $course->program->program_name,
            ];
        }
        
        return null;
    }
    public function submitRequirement()
    {
        // Get the requirement ID from the selected notification data
        $requirementId = $this->selectedNotificationData['requirement']['id'] ?? null;
        
        if ($requirementId) {
            // Redirect to requirements page with the specific requirement highlighted/opened
            return redirect()->to("/user/requirements?requirement={$requirementId}");
        }
        
        // Fallback: redirect to general requirements page
        return redirect()->to('/user/requirements');
    }

    public function openRequirementFolder()
    {
        // Get the requirement ID from the selected notification data
        $requirementId = $this->selectedNotificationData['requirement']['id'] ?? null;
        $courseId = $this->selectedNotificationData['course']['id'] ?? null;
        
        if ($requirementId && $courseId) {
            // Redirect to requirements page with course and folder parameters
            return redirect()->to("/user/requirements?course={$courseId}&folder={$requirementId}");
        } elseif ($requirementId) {
            // Fallback: redirect with just the requirement folder
            return redirect()->to("/user/requirements?folder={$requirementId}");
        }
        
        // Final fallback: redirect to general requirements page
        return redirect()->to('/user/requirements');
    }

    protected function handleDueDateReminderNotification($notification, &$data): void
    {
        $requirementId = data_get($notification->data, 'requirement_id');
        
        if ($requirementId) {
            $requirement = Requirement::with(['creator', 'updater', 'archiver'])
                ->find($requirementId);

            if ($requirement) {
                $assignedToData = $this->formatAssignedToDisplay($requirement->assigned_to);
                
                $data['requirement'] = [
                    'id' => $requirement->id,
                    'name' => $requirement->name,
                    'description' => $requirement->description,
                    'due' => $requirement->due,
                    'assigned_to' => $assignedToData,
                    'status' => $requirement->status,
                ];
                
                $data['reminder_info'] = [
                    'days_remaining' => data_get($notification->data, 'days_remaining'),
                    'due_date' => data_get($notification->data, 'due_date'),
                ];
            }
        }
    }

    public function render()
    {
        $filteredNotifications = $this->getFilteredNotifications();
        
        return view('livewire.user.notification.notification', [
            'filteredNotifications' => $filteredNotifications,
            'activeTab' => $this->activeTab,
        ]);
    }
}