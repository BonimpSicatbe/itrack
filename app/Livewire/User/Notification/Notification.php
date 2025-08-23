<?php

namespace App\Livewire\User\Notification;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Requirement;
use App\Models\SubmittedRequirement;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Notification extends Component
{
    /** @var \Illuminate\Support\Collection */
    public $notifications;

    /** @var string|null */
    public $selectedNotification = null;

    /** @var array|null */
    public $selectedNotificationData = null;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $this->notifications = Auth::user()
            ->notifications()
            ->latest()
            ->take(100)
            ->get();
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
        $this->selectedNotification = null;
        $this->selectedNotificationData = null;
        session()->flash('message', 'All notifications marked as read.');
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
        }

        // Base info
        $data = [
            'type'       => data_get($notification->data, 'type'),
            'message'    => data_get($notification->data, 'message', ''),
            'created_at' => $notification->created_at,
            'unread'     => false,
        ];

        // IDs may be in different keys
        $requirementId = data_get($notification->data, 'requirement_id')
            ?? data_get($notification->data, 'requirement.id');

        $submissionId  = data_get($notification->data, 'submission_id');

        // ---------------- REQUIREMENT (details + FILES FROM REQUIREMENT) ----------------
        $requirement = null;
        if ($requirementId) {
            $requirement = Requirement::find($requirementId);
        }

        if ($requirement) {
            $dueRaw = $requirement->due ?? null;

            $data['requirement'] = [
                'id'          => $requirement->id,
                'name'        => $requirement->name,
                'description' => $requirement->description,
                'due'         => $dueRaw instanceof Carbon ? $dueRaw : ($dueRaw ? Carbon::parse($dueRaw) : null),
                'assigned_to' => $requirement->assigned_to ?? data_get($notification->data, 'requirement.assigned_to'),
                'status'      => $requirement->status ?? null,
                'priority'    => $requirement->priority ?? null,
                'created_at'  => $requirement->created_at,
                'updated_at'  => $requirement->updated_at,
            ];

            // ⬇️ Pull ADMIN/REQUIREMENT files (NOT submission_files)
            // We’ll grab ALL media on Requirement so it works for either 'guides' or 'requirements' collection.
            $reqMedia = $requirement->media; // requires InteractsWithMedia on Requirement
            if ($reqMedia->isEmpty()) {
                // Fallbacks if you want to be explicit about collections
                $reqMedia = $requirement->getMedia('guides');
                if ($reqMedia->isEmpty()) {
                    $reqMedia = $requirement->getMedia('requirements');
                }
            }

            $data['files'] = $reqMedia->map(function ($m) {
                $ext = Str::lower(pathinfo($m->file_name, PATHINFO_EXTENSION));
                return [
                    'id'             => $m->id,
                    'submission_id'  => null, // not from submission
                    'name'           => $m->file_name,
                    'extension'      => $ext,
                    'size'           => $this->formatFileSize($m->size),
                    'is_previewable' => $this->isPreviewable($ext),
                    'status'         => null, // requirement files have no review status
                ];
            })->toArray();
        }

        // ---------------- USER'S OWN SUBMISSION (info only; no files pulled) -----------
        // We still show "Submission Information" if present, but do NOT show submission_files.
        $submission = null;

        if ($submissionId) {
            $submission = SubmittedRequirement::query()
                ->where('id', $submissionId)
                ->where('user_id', Auth::id())
                ->first();
        }

        if (!$submission && $requirementId) {
            $submission = SubmittedRequirement::query()
                ->where('requirement_id', $requirementId)
                ->where('user_id', Auth::id())
                ->latest()
                ->first();
        }

        if ($submission) {
            $data['submitter'] = [
                'id'    => Auth::id(),
                'name'  => Auth::user()->name,
                'email' => Auth::user()->email,
            ];

            $data['submission'] = [
                'id'           => $submission->id,
                'status'       => $submission->status,
                'status_label' => $this->statusLabel($submission->status),
                'admin_notes'  => $submission->admin_notes,
                'submitted_at' => $submission->submitted_at ?? $submission->created_at,
                'reviewed_at'  => $submission->reviewed_at,
            ];
        }

        $this->selectedNotificationData = $data;
        $this->loadNotifications(); // refresh dots
    }

    protected function statusLabel(?string $status): string
    {
        return match ($status) {
            SubmittedRequirement::STATUS_UNDER_REVIEW     => 'Under Review',
            SubmittedRequirement::STATUS_REVISION_NEEDED  => 'Revision Needed',
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

    /** Intl-free file size formatter */
    protected function formatFileSize($bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)       return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' bytes';
    }

    public function render()
    {
        return view('livewire.user.notification.notification');
    }
}
