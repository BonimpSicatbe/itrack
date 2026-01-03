<?php

namespace App\Livewire\Admin\Notification;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\SubmittedRequirement;
use App\Models\AdminCorrectionNote;
use App\Models\User;
use App\Models\Signatory;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
use App\Services\DocumentProcessorService;

class Notifications extends Component
{
    public $notifications = [];
    public $selectedNotification = null;
    public $selectedNotificationData = null;
    public $activeTab = 'all';
    public $newRegisteredUser = null;
    public $notificationNotFound = false;
    public $expandedCorrectionFiles = [];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = Auth::user()
            ->notifications()
            ->whereIn('type', [
                'App\Notifications\NewSubmissionNotification',
                'App\Notifications\SubmissionStatusUpdated',
                'App\Notifications\SemesterEndedWithMissingSubmissions',
                'App\Notifications\NewRegisteredUserNotification',
            ])
            ->latest()
            ->get();
    }

    public function getFilteredNotifications()
    {
        if ($this->activeTab === 'unread') {
            return $this->notifications->filter(fn($notification) => $notification->unread());
        } elseif ($this->activeTab === 'read') {
            return $this->notifications->filter(fn($notification) => !$notification->unread());
        }

        return $this->notifications;
    }

    public function updatedActiveTab(): void
    {
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
        $this->notificationNotFound = false;
    }

    public function selectNotification($id)
    {
        $id = urldecode($id);
        $this->selectedNotification = $id;
        $this->notificationNotFound = false;

        $notification = DatabaseNotification::find($id);

        if (!$notification) {
            $this->notificationNotFound = true;
            $this->selectedNotificationData = null;
            $this->newRegisteredUser = null;
            return;
        }

        if ($notification->data['type'] === 'new_registered_user') {
            Log::info('Notification Data:', $notification->data);

            if ($notification->unread()) {
                $notification->markAsRead();
                $this->dispatch('notification-read');
                $this->loadNotifications();
            }

            $this->selectedNotificationData = [
                'type' => $notification->data['type'] ?? null,
                'message' => $notification->data['message'] ?? '',
                'user_name' => $notification->data['user_name'] ?? 'Unknown User',
                'created_at' => $notification->created_at,
                'unread' => $notification->unread(),
            ];

            $this->loadDetails($notification->data);
        } else if ($notification) {
            \Log::info('Notification Data:', $notification->data);

            if ($notification->unread()) {
                $notification->markAsRead();
                $this->dispatch('notification-read');
                $this->loadNotifications();
            }

            $this->selectedNotificationData = [
                'type' => $notification->data['type'] ?? null,
                'message' => $notification->data['message'] ?? '',
                'created_at' => $notification->created_at,
                'unread' => $notification->unread(),
            ];

            $this->loadDetails($notification->data);
        }
    }

    protected function loadDetails($notificationData)
    {
        // Load requirement details for both notification types
        $requirementId = $notificationData['requirement_id'] ?? null;
        if ($requirementId) {
            $requirement = \App\Models\Requirement::with(['creator', 'updater', 'archiver'])
                ->find($requirementId);

            if ($requirement) {
                $this->selectedNotificationData['requirement'] = [
                    'id' => $requirement->id,
                    'name' => $requirement->name,
                    'description' => $requirement->description,
                    'due' => $requirement->due,
                    'assigned_to' => $requirement->assigned_to_display,
                    'status' => $requirement->status,
                    'created_at' => $requirement->created_at,
                    'updated_at' => $requirement->updated_at,
                    'creator' => $requirement->creator ? [
                        'id' => $requirement->creator->id,
                        'name' => $requirement->creator->name,
                        'email' => $requirement->creator->email,
                    ] : null,
                    'updater' => $requirement->updater ? [
                        'id' => $requirement->updater->id,
                        'name' => $requirement->updater->name,
                        'email' => $requirement->updater->email,
                    ] : null,
                    'archiver' => $requirement->archiver ? [
                        'id' => $requirement->archiver->id,
                        'name' => $requirement->archiver->name,
                        'email' => $requirement->archiver->email,
                    ] : null,
                ];
            }
        }

        // Handle different notification types
        $notificationType = $notificationData['type'] ?? null;

        if ($notificationType === 'submission_status_updated') {
            $this->loadStatusUpdateDetails($notificationData);
        }
        else if ($notificationType === 'semester_ended_missing_submissions') {
            $this->loadSemesterEndedDetails($notificationData);
        }
        else if ($notificationType === 'new_registered_user') {
            $this->loadNewRegisteredUserDetails($notificationData);
        }
        else {
            $this->loadNewSubmissionDetails($notificationData);
        }
    }

    protected function loadStatusUpdateDetails($notificationData)
    {
        // For status update notifications
        $submissionId = $notificationData['submission_id'] ?? null;

        if ($submissionId) {
            $submission = SubmittedRequirement::with(['reviewer', 'course.program', 'user', 'media'])
                ->find($submissionId);

            if ($submission) {
                $this->selectedNotificationData['submissions'] = [$this->formatSubmission($submission)];
                $this->loadFiles([$submission]);

                // Add status update specific data
                $this->selectedNotificationData['status_update'] = [
                    'old_status' => $notificationData['old_status'],
                    'new_status' => $notificationData['new_status'],
                    'reviewed_by' => $notificationData['reviewed_by'],
                    'reviewed_at' => $notificationData['reviewed_at'],
                ];
            }
        }

        // Load user data
        $userId = $notificationData['user_id'] ?? null;
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $this->selectedNotificationData['submitter'] = [
                    'id' => $user->id,
                    'name' => $user->full_name ?? $user->name,
                    'email' => $user->email,
                ];
            }
        }

        // Load correction notes for this submission
        $this->loadCorrectionNotes($notificationData);
    }

    protected function loadNewSubmissionDetails($notificationData)
    {
        // Original logic for new submission notifications
        $submissionIds = [];

        if (isset($notificationData['submission_ids'])) {
            $submissionIds = $notificationData['submission_ids'];
        } elseif (isset($notificationData['submission_id'])) {
            $submissionIds = [$notificationData['submission_id']];
        }

        $submissions = SubmittedRequirement::with(['reviewer', 'course.program', 'user', 'media'])
            ->whereIn('id', $submissionIds)
            ->get();

        if ($submissions->count() > 0) {
            $this->selectedNotificationData['submissions'] = $submissions->map(function ($submission) {
                return $this->formatSubmission($submission);
            })->toArray();

            $this->loadFiles($submissions);
        }

        $userId = $notificationData['user_id'] ?? null;
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $this->selectedNotificationData['submitter'] = [
                    'id' => $user->id,
                    'name' => $user->full_name ?? $user->name,
                    'email' => $user->email,
                ];
            }
        }

        if (isset($notificationData['course_id']) && $notificationData['course_id']) {
            $course = \App\Models\Course::with('program')->find($notificationData['course_id']);
            if ($course) {
                $this->selectedNotificationData['course'] = [
                    'id' => $course->id,
                    'course_code' => $course->course_code,
                    'course_name' => $course->course_name,
                    'program' => $course->program ? [
                        'id' => $course->program->id,
                        'program_code' => $course->program->program_code,
                        'program_name' => $course->program->program_name,
                    ] : null,
                ];
            }
        }

        // Load correction notes for this submission
        $this->loadCorrectionNotes($notificationData);
    }

    protected function loadCorrectionNotes($notificationData)
    {
        $submissionIds = [];

        if (isset($notificationData['submission_ids'])) {
            $submissionIds = $notificationData['submission_ids'];
        } elseif (isset($notificationData['submission_id'])) {
            $submissionIds = [$notificationData['submission_id']];
        }

        if (!empty($submissionIds)) {
            $correctionNotes = AdminCorrectionNote::with(['admin'])
                ->whereIn('submitted_requirement_id', $submissionIds)
                ->orderBy('created_at', 'desc')
                ->get();

            // Group correction notes by submitted_requirement_id
            $this->selectedNotificationData['correction_notes_by_submission'] = $correctionNotes->groupBy('submitted_requirement_id')->map(function ($notes) {
                return $notes->map(function ($note) {
                    return [
                        'id' => $note->id,
                        'correction_notes' => $note->correction_notes,
                        'file_name' => $note->file_name,
                        'status' => $note->status,
                        'status_label' => $this->formatCorrectionNoteStatus($note->status),
                        'created_at' => $note->created_at,
                        'admin' => $note->admin ? [
                            'id' => $note->admin->id,
                            'name' => $note->admin->full_name ?? $note->admin->name,
                            'email' => $note->admin->email,
                        ] : null,
                        'addressed_at' => $note->addressed_at,
                    ];
                })->toArray();
            })->toArray();
        } else {
            $this->selectedNotificationData['correction_notes_by_submission'] = [];
        }
    }

    protected function formatCorrectionNoteStatus($status)
    {
        return match($status) {
            AdminCorrectionNote::STATUS_UPLOADED => 'Uploaded',
            AdminCorrectionNote::STATUS_UNDER_REVIEW => 'Under Review',
            AdminCorrectionNote::STATUS_REVISION_NEEDED => 'Revision Required',
            AdminCorrectionNote::STATUS_REJECTED => 'Rejected',
            AdminCorrectionNote::STATUS_APPROVED => 'Approved',
            default => ucfirst($status),
        };
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
            'signed_document_path' => $submission->signed_document_path,
            'signed_at' => $submission->signed_at,
            'signatory_id' => $submission->signatory_id,
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
            'needs_review' => $submission->status === SubmittedRequirement::STATUS_UNDER_REVIEW,
            'is_approved' => $submission->status === SubmittedRequirement::STATUS_APPROVED,
            'user_id' => $submission->user_id,
            'requirement_id' => $submission->requirement_id,
        ];
    }

    protected function loadFiles($submissions)
    {
        $files = [];

        foreach ($submissions as $submission) {
            foreach ($submission->getMedia('submission_files') as $media) {
                $extension = strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION));
                $isPreviewable = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
                
                // Use signed document if approved and available
                $signedMedia = $submission->getMedia('signed_documents')->first();
                $isApproved = $submission->status === 'approved' && $signedMedia;

                $files[] = [
                    'id' => $media->id,
                    'name' => $isApproved ? 'SIGNED - ' . $media->name : $media->name,
                    'file_name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'mime_type' => $media->mime_type,
                    'size' => $this->formatFileSize($media->size),
                    'created_at' => $media->created_at,
                    'extension' => $isApproved ? 'pdf' : $extension,
                    'is_previewable' => $isPreviewable || $isApproved,
                    'status' => $submission->status,
                    'admin_notes' => $submission->admin_notes,
                    'submission_id' => $submission->id,
                    'is_approved' => $submission->status === SubmittedRequirement::STATUS_APPROVED,
                    'is_approved_with_signature' => $isApproved,
                    'has_signed_document' => (bool) $signedMedia,
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

        $this->selectedNotificationData['files'] = $files;
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

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()
            ->whereIn('type', [
                'App\Notifications\NewSubmissionNotification',
                'App\Notifications\SubmissionStatusUpdated',
                'App\Notifications\SemesterEndedWithMissingSubmissions'
            ])
            ->update(['read_at' => now()]);

        $this->loadNotifications();
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;

        $this->dispatch('notifications-marked-read');

        $this->dispatch('showNotification',
            type: 'success',
            content: 'All notifications marked as read!',
            duration: 3000
        );
    }

    public function markAllAsUnread()
    {
        $readNotifications = Auth::user()->readNotifications()
            ->whereIn('type', [
                'App\Notifications\NewSubmissionNotification',
                'App\Notifications\SubmissionStatusUpdated',
                'App\Notifications\SemesterEndedWithMissingSubmissions'
            ])
            ->get();

        $readNotifications->each(function ($notification) {
            $notification->markAsUnread();
        });

        $this->loadNotifications();
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;

        $this->dispatch('notifications-marked-unread', count: $readNotifications->count());

        $this->dispatch('showNotification',
            type: 'success',
            content: 'All notifications marked as unread!',
            duration: 3000
        );
    }

    public function markAsUnread($id)
    {
        $id = urldecode($id);
        $notification = DatabaseNotification::find($id);

        if ($notification && !$notification->unread()) {
            $notification->markAsUnread();
            $this->loadNotifications();

            if ($this->selectedNotification === $id) {
                $this->selectedNotificationData['unread'] = true;
            }

            $this->dispatch('notification-unread');

            $this->dispatch('showNotification',
                type: 'success',
                content: 'Notification marked as unread!',
                duration: 3000
            );
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

    protected function loadNewRegisteredUserDetails($notificationData)
    {
        $this->selectedNotificationData['new_user'] = [
            'id' => $notificationData['user_id'] ?? null,
            'name' => $notificationData['user_name'] ?? $notificationData['name'] ?? 'Unknown User',
            'email' => $notificationData['user_email'] ?? $notificationData['email'] ?? 'No Email Provided',
            'registered_at' => $notificationData['registered_at'] ?? $notificationData['created_at'] ?? null,
        ];

        $this->newRegisteredUser = User::where('id', $this->selectedNotificationData['new_user']['id'])->get();
    }

    protected function loadSemesterEndedDetails($notificationData)
    {
        $semesterId = $notificationData['semester_id'] ?? null;
        if ($semesterId) {
            $semester = \App\Models\Semester::find($semesterId);
            if ($semester) {
                $this->selectedNotificationData['semester'] = [
                    'id' => $semester->id,
                    'name' => $semester->name,
                    'start_date' => $semester->start_date,
                    'end_date' => $semester->end_date,
                    'is_active' => $semester->is_active,
                ];
            } else {
                $this->selectedNotificationData['semester'] = [
                    'id' => $semesterId,
                    'name' => $notificationData['semester_name'] ?? 'Unknown Semester',
                    'start_date' => null,
                    'end_date' => $notificationData['ended_at'] ?? null,
                    'is_active' => false,
                ];
            }
        }

        $this->selectedNotificationData['missing_submissions'] = [
            'total_count' => $notificationData['missing_submissions_count'] ?? 0,
            'submissions' => $notificationData['missing_submissions'] ?? [],
            'ended_at' => $notificationData['ended_at'] ?? null,
        ];
    }

    public function verifyUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            $this->dispatch('showNotification', type: 'error', content: 'User not found.', duration: 3000);
            return;
        }

        if (!empty($user->email_verified_at)) {
            $this->dispatch('showNotification', type: 'info', content: 'User is already verified.', duration: 3000);
            return;
        }

        try {
            $user->markEmailAsVerified();
            $user->save();

            $this->newRegisteredUser = User::where('id', $user->id)->get();
            if (isset($this->selectedNotificationData['new_user'])) {
                $this->selectedNotificationData['new_user']['verified_at'] = $user->email_verified_at;
            }

            $this->loadNotifications();

            $this->dispatch('showNotification', type: 'success', content: 'User verified successfully!', duration: 3000);
        } catch (\Throwable $e) {
            Log::error('Failed to verify user: ' . $e->getMessage(), ['user_id' => $userId]);
            $this->dispatch('showNotification', type: 'error', content: 'Failed to verify user.', duration: 3000);
        }
    } 

    public function toggleCorrectionNote($fileIndex)
    {
        if (in_array($fileIndex, $this->expandedCorrectionFiles)) {
            // Remove from array (collapse)
            $this->expandedCorrectionFiles = array_diff($this->expandedCorrectionFiles, [$fileIndex]);
            $expanded = false;
        } else {
            // Add to array (expand)
            $this->expandedCorrectionFiles[] = $fileIndex;
            $expanded = true;
        }
        
        // Dispatch event to update the UI
        $this->dispatch('correction-notes-toggled', [
            'fileIndex' => $fileIndex,
            'expanded' => $expanded
        ]);
    }

    public function render()
    {
        $filteredNotifications = $this->getFilteredNotifications();

        return view('livewire.admin.notification.notifications', [
            'filteredNotifications' => $filteredNotifications,
            'activeTab' => $this->activeTab,
        ]);
    }
}