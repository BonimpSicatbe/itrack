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

class Notification extends Component
{
    use WithFileUploads;
    
    public $notifications;
    public $selectedNotification = null;
    public $selectedNotificationData = null;
    public $activeTab = 'all'; // 'all', 'unread', 'read'
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
            // Reload notifications to update unread status
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
        
        if ($notificationType === 'submission_status_updated') {
            // Handle submission status update notifications
            $this->handleSubmissionStatusNotification($notification, $data);
        } else {
            // Handle existing requirement notifications (your existing code)
            $this->handleRequirementNotification($notification, $data);
        }

        $this->selectedNotificationData = $data;
    }

    protected function handleRequirementNotification($notification, &$data): void
    {
        // This is your existing notification handling code
        // IDs may be in different keys
        $requirementId = data_get($notification->data, 'requirement_id')
            ?? data_get($notification->data, 'requirement.id');

        $submissionId  = data_get($notification->data, 'submission_id');

        // ... rest of your existing notification handling code ...
    }

    protected function handleSubmissionStatusNotification($notification, &$data): void
    {
        $submissionId = data_get($notification->data, 'submission_id');
        $requirementId = data_get($notification->data, 'requirement_id');

        // Get submission details
        $submission = SubmittedRequirement::with(['requirement', 'user', 'course'])
            ->where('id', $submissionId)
            ->where('user_id', Auth::id())
            ->first();

        if ($submission) {
            $data['submission'] = [
                'id' => $submission->id,
                'status' => $submission->status,
                'status_label' => $this->statusLabel($submission->status),
                'admin_notes' => $submission->admin_notes,
                'reviewed_at' => $submission->reviewed_at,
                'submitted_at' => $submission->submitted_at ?? $submission->created_at,
            ];

            $data['requirement'] = [
                'id' => $submission->requirement->id,
                'name' => $submission->requirement->name,
                'description' => $submission->requirement->description,
            ];

            $data['status_update'] = [
                'old_status' => data_get($notification->data, 'old_status'),
                'new_status' => data_get($notification->data, 'new_status'),
                'old_status_label' => $this->statusLabel(data_get($notification->data, 'old_status')),
                'new_status_label' => $this->statusLabel(data_get($notification->data, 'new_status')),
                'reviewed_by' => data_get($notification->data, 'reviewed_by'),
                'reviewed_at' => data_get($notification->data, 'reviewed_at'),
            ];
        }
    }

    
    public function submitRequirement(): Redirector
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

    protected function formatAssignedToDisplay($assignedTo): array
    {
        $currentUser = Auth::user();
        $currentProgramId = $currentUser->program_id;
        
        $programDisplay = '';
        
        if (is_array($assignedTo)) {
            // Handle programs
            if (isset($assignedTo['selectAllPrograms']) && $assignedTo['selectAllPrograms']) {
                $programDisplay = 'All Programs';
            } elseif (isset($assignedTo['programs']) && is_array($assignedTo['programs'])) {
                $programIds = array_filter(array_map('intval', $assignedTo['programs']));
                
                if (!empty($programIds)) {
                    // Check if current user's program is in the list
                    $userProgramInList = in_array((int)$currentProgramId, $programIds);
                    $otherProgramsCount = count($programIds) - ($userProgramInList ? 1 : 0);
                    
                    if ($userProgramInList) {
                        $userProgram = Program::find($currentProgramId);
                        if ($userProgram) {
                            if ($otherProgramsCount > 0) {
                                $programDisplay = $userProgram->program_name . ' and ' . $otherProgramsCount . ' other program(s)';
                            } else {
                                $programDisplay = $userProgram->program_name;
                            }
                        } else {
                            $programDisplay = count($programIds) . ' program(s)';
                        }
                    } else {
                        $programDisplay = count($programIds) . ' program(s)';
                    }
                }
            }
        }
        
        return [
            'program' => $programDisplay ?: 'Not assigned to programs'
        ];
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

    protected function isPreviewable(?string $ext): bool
    {
        if (!$ext) return false;
        return in_array($ext, ['jpg','jpeg','png','gif','pdf'], true);
    }

    protected function formatFileSize($bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)       return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' bytes';
    }

    public function render()
    {
        $filteredNotifications = $this->getFilteredNotifications();
        
        return view('livewire.user.notification.notification', [
            'activeTab' => $this->activeTab,
            'filteredNotifications' => $filteredNotifications,
        ]);
    }
}