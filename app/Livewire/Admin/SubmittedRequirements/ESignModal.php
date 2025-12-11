<?php

namespace App\Livewire\Admin\SubmittedRequirements;

use Livewire\Component;
use App\Models\SubmittedRequirement;
use App\Models\Signatory;
use App\Services\DocumentProcessorService;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class ESignModal extends Component
{
    use WithFileUploads;

    public $submissionId;
    public $fileUrl;
    public $fileExtension;
    
    // Modal state
    public $showModal = false;
    
    // Signatory selection
    public $signatoryId;
    public $signatories = [];
    public $selectedSignatory;
    
    // Signature positioning - ALL AS SLIDERS
    public $signatureX = 100;
    public $signatureY = 100;
    public $signatureScale = 1.0;
    public $signatureOpacity = 1.0;
    public $signatureRotation = 0;
    
    // Slider ranges
    public $minX = 0;
    public $maxX = 800;
    public $minY = 0;
    public $maxY = 600;
    
    // Document preview
    public $documentWidth = 800;
    public $documentHeight = 600;
    public $zoomLevel = 1.0;
    public $panX = 0;
    public $panY = 0;
    
    // UI state
    public $isDragging = false;
    public $isResizing = false;
    public $showPreview = false;
    public $isProcessing = false; 

    public $pdfPointWidth = 595; // Default A4 width in points
    public $pdfPointHeight = 842; // Default A4 height in points
    
    // Temporary signature file
    public $signatureImage;

    public function mount($submissionId = null, $fileUrl = null, $fileExtension = null)
    {
        $this->submissionId = $submissionId;
        $this->fileUrl = $fileUrl;
        $this->fileExtension = $fileExtension;
        
        // Load active signatories
        $this->signatories = Signatory::where('is_active', true)
            ->with('media')
            ->get();
            
        if ($this->signatories->isNotEmpty()) {
            $this->signatoryId = $this->signatories->first()->id;
            $this->selectedSignatory = $this->signatories->first();
        }
    }

    #[On('open-esign-modal')]
    public function openModal($submissionId, $fileUrl, $fileExtension)
    {
        $this->submissionId = $submissionId;
        $this->fileUrl = $fileUrl;
        $this->fileExtension = $fileExtension;
        $this->showModal = true;
        
        // Get actual PDF dimensions for positioning
        $submission = SubmittedRequirement::find($submissionId);
        if ($submission && $fileExtension === 'pdf') {
            $pdfPath = $submission->getOriginalFilePath();
            if (file_exists($pdfPath)) {
                $dimensions = $this->detectPdfDimensions($pdfPath);
                $this->documentWidth = $dimensions['pixels']['width'];
                $this->documentHeight = $dimensions['pixels']['height'];
                
                // Store point dimensions for later use
                $this->pdfPointWidth = $dimensions['points']['width'];
                $this->pdfPointHeight = $dimensions['points']['height'];
            }
        } else {
            // For images, use image dimensions
            $this->setDefaultDocumentDimensions();
        }
        
        // Reset positioning
        $this->signatureX = $this->documentWidth * 0.7; // Default to 70% from left
        $this->signatureY = $this->documentHeight * 0.85; // Default to 85% from top (near bottom)
        $this->signatureScale = 0.8; // Start with 80% scale
        $this->signatureOpacity = 1.0;
        $this->signatureRotation = 0;
        $this->zoomLevel = 1.0;
        $this->panX = 0;
        $this->panY = 0;
        $this->showPreview = false;
        $this->isDragging = false;
        $this->isResizing = false;
        
        // Set slider ranges based on document size
        $this->minX = 0;
        $this->maxX = $this->documentWidth - (100 * $this->signatureScale);
        $this->minY = 0;
        $this->maxY = $this->documentHeight - (40 * $this->signatureScale);
    }

    public function updatedSignatoryId($value)
    {
        $this->selectedSignatory = Signatory::with('media')->find($value);
    }

    public function updatedSignatureScale()
    {
        // When scale changes, update max X and Y to prevent signature going out of bounds
        $this->maxX = max(0, $this->documentWidth - (60 * $this->signatureScale));
        $this->maxY = max(0, $this->documentHeight - (30 * $this->signatureScale));
        
        // Adjust current position if needed
        $this->signatureX = min($this->signatureX, $this->maxX);
        $this->signatureY = min($this->signatureY, $this->maxY);
    }

    public function startDrag()
    {
        $this->isDragging = true;
    }

    public function startResize()
    {
        $this->isResizing = true;
    }

    public function stopInteractions()
    {
        $this->isDragging = false;
        $this->isResizing = false;
    }

    public function updateSignaturePosition($x, $y)
    {
        if (!$this->isDragging && !$this->isResizing) {
            return;
        }

        // Convert screen coordinates to document coordinates
        $docX = ($x - $this->panX) / $this->zoomLevel;
        $docY = ($y - $this->panY) / $this->zoomLevel;
        
        if ($this->isResizing) {
            // Calculate new scale based on distance from center
            $centerX = $this->signatureX + (60 * $this->signatureScale) / 2;
            $centerY = $this->signatureY + (30 * $this->signatureScale) / 2;
            
            $distanceX = abs($docX - $centerX);
            $distanceY = abs($docY - $centerY);
            
            // Use average distance for scaling
            $avgDistance = ($distanceX + $distanceY) / 2;
            $baseDistance = (60 + 30) / 4; // Average of half width and half height
            
            $newScale = max(0.5, min(3.0, $avgDistance / $baseDistance));
            $this->signatureScale = round($newScale, 1);
            
            // Update slider ranges when scale changes
            $this->updatedSignatureScale();
            
        } else if ($this->isDragging) {
            // Update position
            $signatureWidth = 60 * $this->signatureScale;
            $signatureHeight = 30 * $this->signatureScale;
            
            // Adjust for drag handle offset (handle is at -2px from edge)
            $adjustedX = $docX - 8; // Half of handle width + border
            $adjustedY = $docY - 8; // Half of handle height + border
            
            // Update the slider values
            $this->signatureX = max(0, min($adjustedX, $this->maxX));
            $this->signatureY = max(0, min($adjustedY, $this->maxY));
        }
    }

    public function zoomIn()
    {
        $this->zoomLevel = min(3.0, $this->zoomLevel + 0.25);
    }

    public function zoomOut()
    {
        $this->zoomLevel = max(0.5, $this->zoomLevel - 0.25);
    }

    public function resetZoom()
    {
        $this->zoomLevel = 1.0;
        $this->panX = 0;
        $this->panY = 0;
    }

    public function togglePreview()
    {
        $this->showPreview = !$this->showPreview;
    }

    public function applySignature()
    {
        $this->validate([
            'signatoryId' => 'required|exists:signatories,id',
        ]);

        $this->isProcessing = true;

        try {
            // Get the submission
            $submission = SubmittedRequirement::findOrFail($this->submissionId);
            
            // Get the selected signatory
            $signatory = Signatory::with('media')->find($this->signatoryId);
            
            if (!$signatory) {
                throw new \Exception('Selected signatory not found.');
            }
            
            // Get signature media
            $signatureMedia = $signatory->getFirstMedia('signatures');
            if (!$signatureMedia) {
                throw new \Exception('Signature image not found in media library.');
            }
            
            // Get the absolute file path for processing
            $signatureImagePath = $signatureMedia->getPath();
            
            // Verify signature file exists
            if (!file_exists($signatureImagePath)) {
                throw new \Exception('Signature file not found.');
            }
            
            // Get the original file path
            $originalFilePath = $submission->getOriginalFilePath();
            
            if (!$originalFilePath || !file_exists($originalFilePath)) {
                throw new \Exception('Original document file not found.');
            }
            
            // Convert preview coordinates (pixels, top-left origin) to PDF coordinates (points, bottom-left origin)
            $pixelsPerPoint = 96 / 72; // Standard conversion
            
            // Get document dimensions in points
            $pdfDimensions = $this->detectPdfDimensions($originalFilePath);
            $pageWidthPoints = $pdfDimensions['points']['width'];
            $pageHeightPoints = $pdfDimensions['points']['height'];
            
            // Convert preview coordinates to points
            $pdfX = $this->signatureX / $pixelsPerPoint;
            
            // IMPORTANT: Convert Y coordinate from top-left origin to bottom-left origin
            // Preview Y is from top, PDF Y is from bottom
            $pdfY = ($this->documentHeight - $this->signatureY) / $pixelsPerPoint;
            
            // Convert signature dimensions from pixels to points
            $signatureWidthPixels = 100 * $this->signatureScale; // Base width 100px
            $signatureHeightPixels = 40 * $this->signatureScale; // Base height 40px
            
            $pdfWidth = $signatureWidthPixels / $pixelsPerPoint;
            $pdfHeight = $signatureHeightPixels / $pixelsPerPoint;
            
            \Log::info('Applying signature with coordinates:', [
                'preview_pixels' => ['x' => $this->signatureX, 'y' => $this->signatureY],
                'pdf_points' => ['x' => $pdfX, 'y' => $pdfY, 'width' => $pdfWidth, 'height' => $pdfHeight],
                'page_dimensions' => ['width' => $pageWidthPoints, 'height' => $pageHeightPoints],
                'scale' => $this->signatureScale,
                'document_pixels' => ['width' => $this->documentWidth, 'height' => $this->documentHeight]
            ]);
            
            // Use DocumentProcessorService to add signature
            $documentProcessor = app(DocumentProcessorService::class);
            
            $signedDocumentPath = $documentProcessor->addSignatureToDocument(
                originalFilePath: $originalFilePath,
                signatoryName: $signatory->name,
                signatureImagePath: $signatureImagePath,
                xPosition: $pdfX,
                yPosition: $pdfY,
                width: $pdfWidth,
                height: $pdfHeight,
                pageNumber: 1
            );
            
            if (!$signedDocumentPath || !file_exists($signedDocumentPath)) {
                throw new \Exception('Failed to create signed document.');
            }
            
            // Add signed document to submission
            $media = $submission->addSignedDocument($signedDocumentPath, $signatory->id);
            
            // Update submission status to approved
            $submission->update([
                'status' => 'approved',
                'admin_notes' => ($submission->admin_notes ?? '') . "\n\n[Digitally signed by " . $signatory->name . "]",
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
            
            // Create correction note
            \App\Models\AdminCorrectionNote::create([
                'submitted_requirement_id' => $submission->id,
                'requirement_id' => $submission->requirement_id,
                'course_id' => $submission->course_id,
                'user_id' => $submission->user_id,
                'admin_id' => auth()->id(),
                'correction_notes' => $submission->admin_notes ?: 'Document approved with digital signature.',
                'file_name' => $submission->getFirstMedia('submission_files')->file_name ?? 'Unknown',
                'status' => 'approved',
            ]);
            
            // Send notification
            $user = \App\Models\User::find($submission->user_id);
            if ($user) {
                $user->notify(new \App\Notifications\SubmissionStatusUpdated(
                    $submission, 
                    $submission->getOriginal('status'), 
                    'approved'
                ));
            }
            
            // Clean up temporary file
            if (file_exists($signedDocumentPath)) {
                unlink($signedDocumentPath);
            }
            
            $this->isProcessing = false;
            $this->showModal = false;
            
            // Emit event to refresh parent component
            $this->dispatch('signature-applied', submissionId: $submission->id);
            $this->dispatch('showNotification', 
                type: 'success', 
                content: 'Document approved and digitally signed successfully!'
            );
            
        } catch (\Exception $e) {
            $this->isProcessing = false;
            \Log::error('E-signature error: ' . $e->getMessage());
            $this->dispatch('showNotification', 
                type: 'error', 
                content: 'Failed to apply signature: ' . $e->getMessage()
            );
        }
    }

    public function cancel()
    {
        $this->showModal = false;
        $this->resetExcept(['submissionId', 'fileUrl', 'fileExtension']);
    } 

    public function debugSignatureLoading()
    {
        if (!$this->selectedSignatory) {
            \Log::info('No signatory selected');
            return;
        }
        
        \Log::info('Selected signatory:', ['id' => $this->selectedSignatory->id, 'name' => $this->selectedSignatory->name]);
        
        $signatureMedia = $this->selectedSignatory->getFirstMedia('signatures');
        
        if (!$signatureMedia) {
            \Log::warning('No signature media found');
            return;
        }
        
        \Log::info('Signature media found:', [
            'id' => $signatureMedia->id,
            'file_name' => $signatureMedia->file_name,
            'collection_name' => $signatureMedia->collection_name,
            'disk' => $signatureMedia->disk,
            'path' => $signatureMedia->getPath(),
            'url' => $signatureMedia->getUrl(),
            'exists' => file_exists($signatureMedia->getPath()) ? 'yes' : 'no'
        ]);
        
        // Test direct access
        $testPath = storage_path('app/public/' . $signatureMedia->id . '/' . $signatureMedia->file_name);
        \Log::info('Alternative path:', ['path' => $testPath, 'exists' => file_exists($testPath) ? 'yes' : 'no']);
    } 

    protected function detectPdfDimensions($pdfPath)
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            $pages = $pdf->getPages();
            
            if (count($pages) > 0) {
                $page = $pages[0];
                $details = $page->getDetails();
                
                // Get page dimensions (usually in points)
                $width = isset($details['MediaBox'][2]) ? floatval($details['MediaBox'][2]) : 595; // Default A4 width
                $height = isset($details['MediaBox'][3]) ? floatval($details['MediaBox'][3]) : 842; // Default A4 height
                
                // Convert points to pixels for display (72 points per inch, 96 pixels per inch)
                $pixelsPerPoint = 96 / 72;
                $pixelWidth = $width * $pixelsPerPoint;
                $pixelHeight = $height * $pixelsPerPoint;
                
                return [
                    'points' => ['width' => $width, 'height' => $height],
                    'pixels' => ['width' => $pixelWidth, 'height' => $pixelHeight]
                ];
            }
        } catch (\Exception $e) {
            \Log::error('PDF dimension detection failed: ' . $e->getMessage());
        }
        
        // Default to A4 if detection fails
        return [
            'points' => ['width' => 595, 'height' => 842],
            'pixels' => ['width' => 794, 'height' => 1123] // A4 at 96 DPI
        ];
    }

    public function render()
    {
        return view('livewire.admin.submitted-requirements.e-sign-modal');
    }
}