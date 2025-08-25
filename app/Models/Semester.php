<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    public static function getActiveSemester()
    {
        return self::where('is_active', true)->first();
    }

    public static function getArchivedSemester()
    {
        return self::where('is_active', false)
            ->orderBy('end_date', 'desc')
            ->first();
    }

    public static function getAllArchivedSemesters()
    {
        return self::where('is_active', false)
            ->orderBy('end_date', 'desc')
            ->get();
    }

    public static function getPreviousSemester()
    {
        return self::where('is_active', false)
            ->orderBy('end_date', 'desc')
            ->first();
    }

    public function requirements()
    {
        return $this->hasMany(Requirement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeDuring($query, $date)
    {
        return $query->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);
    }

    public function setActive()
    {
        // Deactivate all semesters first
        self::query()->update(['is_active' => false]);

        // Activate this semester
        $this->update(['is_active' => true]);

        return $this;
    }

    // NEW: Check if semester should be auto-archived
    public function shouldAutoArchive()
    {
        return $this->is_active && now()->greaterThan($this->end_date);
    }

    // NEW: Auto-archive this semester
    public function autoArchive()
    {
        if ($this->shouldAutoArchive()) {
            $this->update(['is_active' => false]);
            Log::info("Semester auto-archived: {$this->name}");
            return true;
        }
        return false;
    }

    public function submittedRequirements()
    {
        return SubmittedRequirement::whereBetween('submitted_at', [$this->start_date, $this->end_date]);
    }

    public function archiveSemesterWithFiles()
    {
        // Deactivate semester
        $this->update(['is_active' => false]);

        // For each submission → move files
        foreach ($this->requirements as $requirement) {
            foreach ($requirement->media as $media) {
                $media->move("archives/semesters/{$this->name}", $media->file_name);
            }
        }
    }

    public function downloadArchive()
    {
        if (!Auth::user() || !Auth::user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized');
        }

        $zipFileName = "semester_{$this->id}_archive.zip";
        $dir = storage_path("app/archives/zips");
        $zipPath = "{$dir}/{$zipFileName}";

        // Ensure directory exists
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($this->requirements as $requirement) {
                foreach ($requirement->media as $media) {
                    $filePath = $media->getPath();
                    if (file_exists($filePath)) {
                        $zip->addFile($filePath, "{$requirement->name}/{$media->file_name}");
                    } else {
                        \Log::warning("File missing in media", ['id' => $media->id, 'path' => $filePath]);
                    }
                }
            }
            $zip->close();
        } else {
            abort(500, 'Could not create archive.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
