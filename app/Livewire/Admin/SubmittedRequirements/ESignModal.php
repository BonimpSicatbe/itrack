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
    
    // Signature positioning
    public $signatureX = 100;
    public $signatureY = 100;
    public $signatureScale = 1.0;
    public $signatureOpacity = 1.0;
    public $signatureRotation = 0;
    
    // Page selection for multi-page PDFs
    public $pageNumber = 1;
    public $totalPages = 1;
    public $showPageSelector = false;
    public $currentPagePreview = 1;
    
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

    public $pdfPointWidth = 595;
    public $pdfPointHeight = 842;
    
    // Page dimensions array (for multi-page support)
    public $pageDimensions = [];
    
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
        
        // Reset dimensions
        $this->documentWidth = 0;
        $this->documentHeight = 0;
        $this->pdfPointWidth = 0;
        $this->pdfPointHeight = 0;
        $this->pageDimensions = [];
        
        // Get actual PDF dimensions for positioning
        $submission = SubmittedRequirement::find($submissionId);
        if ($submission && in_array($fileExtension, ['pdf', 'jpg', 'jpeg', 'png', 'gif'])) {
            $pdfPath = $submission->getOriginalFilePath();
            
            if (file_exists($pdfPath)) {
                $documentProcessor = app(DocumentProcessorService::class);
                $dimensions = $documentProcessor->getPdfDimensions($pdfPath);
                
                // Get total pages
                $this->totalPages = $dimensions['page_count'] ?? 1;
                $this->showPageSelector = $this->totalPages > 1;
                
                // Store dimensions for all pages
                for ($i = 1; $i <= $this->totalPages; $i++) {
                    $this->pageDimensions[$i] = [
                        'width' => $dimensions['width'],
                        'height' => $dimensions['height']
                    ];
                }
                
                $this->pdfPointWidth = $dimensions['width'];
                $this->pdfPointHeight = $dimensions['height'];
                
                // Apply margin correction
                $viewerMarginCorrection = 0.95;
                $pixelsPerPoint = 96 / 72;
                $contentWidthPx = $this->pdfPointWidth * $pixelsPerPoint * $viewerMarginCorrection;
                $contentHeightPx = $this->pdfPointHeight * $pixelsPerPoint * $viewerMarginCorrection;
                
                $this->documentWidth = $contentWidthPx;
                $this->documentHeight = $contentHeightPx;
                
            } else {
                $this->setDefaultDocumentDimensions();
            }
        } else {
            $this->setDefaultDocumentDimensions();
        }
        
        // Validate dimensions
        $this->validateDimensions();
        
        // Set slider ranges
        $this->updateSliderRangesWithMargin();
        
        // Adjust PDF viewer zoom
        $this->adjustPdfViewer();
        
        // Set default signature position
        $contentMarginX = ($this->documentWidth * 0.025);
        $contentMarginY = ($this->documentHeight * 0.025);
        
        $this->signatureX = ($this->documentWidth / 2) - (50 * $this->signatureScale);
        $this->signatureY = ($this->documentHeight / 2) - (20 * $this->signatureScale);
        
        $this->signatureX = max($contentMarginX, min($this->signatureX, $this->documentWidth - (100 * $this->signatureScale) - $contentMarginX));
        $this->signatureY = max($contentMarginY, min($this->signatureY, $this->documentHeight - (40 * $this->signatureScale) - $contentMarginY));
        
        // Reset other properties
        $this->signatureScale = 1.0;
        $this->signatureOpacity = 1.0;
        $this->signatureRotation = 0;
        $this->panX = 0;
        $this->panY = 0;
        $this->showPreview = false;
        $this->isDragging = false;
        $this->isResizing = false;
        $this->currentPagePreview = 1;
        $this->pageNumber = 1;
    }

    /**
     * When page number changes, update the document dimensions
     */
    public function updatedPageNumber($value)
    {
        $this->pageNumber = (int)$value;
        $this->currentPagePreview = $this->pageNumber;
        
        // Update document dimensions for the selected page
        if (isset($this->pageDimensions[$this->pageNumber])) {
            $dimensions = $this->pageDimensions[$this->pageNumber];
            $this->pdfPointWidth = $dimensions['width'];
            $this->pdfPointHeight = $dimensions['height'];
            
            // Convert to display pixels
            $viewerMarginCorrection = 0.95;
            $pixelsPerPoint = 96 / 72;
            $contentWidthPx = $this->pdfPointWidth * $pixelsPerPoint * $viewerMarginCorrection;
            $contentHeightPx = $this->pdfPointHeight * $pixelsPerPoint * $viewerMarginCorrection;
            
            $this->documentWidth = $contentWidthPx;
            $this->documentHeight = $contentHeightPx;
            
            // Update slider ranges
            $this->updateSliderRangesWithMargin();
            
            // Reset signature position to center of new page
            $contentMarginX = ($this->documentWidth * 0.025);
            $contentMarginY = ($this->documentHeight * 0.025);
            
            $this->signatureX = ($this->documentWidth / 2) - (50 * $this->signatureScale);
            $this->signatureY = ($this->documentHeight / 2) - (20 * $this->signatureScale);
            
            $this->signatureX = max($contentMarginX, min($this->signatureX, $this->documentWidth - (100 * $this->signatureScale) - $contentMarginX));
            $this->signatureY = max($contentMarginY, min($this->signatureY, $this->documentHeight - (40 * $this->signatureScale) - $contentMarginY));
        }
    }

    protected function updateSliderRangesWithMargin()
    {
        if ($this->documentWidth <= 0 || $this->documentHeight <= 0) {
            $this->documentWidth = 794;
            $this->documentHeight = 1123;
        }
        
        $baseWidthPx = 100;
        $baseHeightPx = 40;
        
        $currentWidthPx = $baseWidthPx * $this->signatureScale;
        $currentHeightPx = $baseHeightPx * $this->signatureScale;
        
        $contentMarginX = $this->documentWidth * 0.05;
        $contentMarginY = $this->documentHeight * 0.05;
        
        $this->minX = $contentMarginX;
        $this->maxX = max($this->minX, $this->documentWidth - $currentWidthPx - $contentMarginX);
        $this->minY = $contentMarginY;
        $this->maxY = max($this->minY, $this->documentHeight - $currentHeightPx - $contentMarginY);
        
        if ($this->signatureX < $this->minX) {
            $this->signatureX = $this->minX;
        }
        
        if ($this->signatureX > $this->maxX) {
            $this->signatureX = $this->maxX;
        }
        
        if ($this->signatureY < $this->minY) {
            $this->signatureY = $this->minY;
        }
        
        if ($this->signatureY > $this->maxY) {
            $this->signatureY = $this->maxY;
        }
    }

    public function updatedSignatoryId($value)
    {
        $this->selectedSignatory = Signatory::with('media')->find($value);
    }

    public function updatedSignatureScale()
    {
        $this->updateSliderRangesWithMargin();
        
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

        $docX = ($x - $this->panX) / $this->zoomLevel;
        $docY = ($y - $this->panY) / $this->zoomLevel;
        
        if ($this->isResizing) {
            $centerX = $this->signatureX + (100 * $this->signatureScale) / 2;
            $centerY = $this->signatureY + (40 * $this->signatureScale) / 2;
            
            $distanceX = abs($docX - $centerX);
            $distanceY = abs($docY - $centerY);
            
            $avgDistance = ($distanceX + $distanceY) / 2;
            $baseDistance = (100 + 40) / 4;
            
            $newScale = max(0.2, min(3.0, $avgDistance / $baseDistance));
            $this->signatureScale = round($newScale, 1);
            
            $this->updatedSignatureScale();
            
        } else if ($this->isDragging) {
            $signatureWidthPx = 100 * $this->signatureScale;
            $signatureHeightPx = 40 * $this->signatureScale;
            
            $adjustedX = $docX - ($signatureWidthPx / 2);
            $adjustedY = $docY - ($signatureHeightPx / 2);
            
            $this->signatureX = max($this->minX, min($adjustedX, $this->maxX));
            $this->signatureY = max($this->minY, min($adjustedY, $this->maxY));
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
        if (in_array($this->fileExtension, ['pdf']) && $this->documentWidth > 0) {
            $containerWidth = 600;
            $this->zoomLevel = min(1.0, $containerWidth / $this->documentWidth);
        } else {
            $this->zoomLevel = 1.0;
        }
        
        $this->panX = 0;
        $this->panY = 0;
    }

    public function fitToWidth()
    {
        if (in_array($this->fileExtension, ['pdf'])) {
            $containerWidth = 600;
            
            if ($this->documentWidth > 0) {
                $this->zoomLevel = $containerWidth / $this->documentWidth;
                $this->zoomLevel = max(0.25, min(3.0, $this->zoomLevel));
                $this->panX = 0;
                $this->panY = 0;
            }
        }
    }

    public function togglePreview()
    {
        $this->showPreview = !$this->showPreview;
    }

    /**
     * Navigate to previous page
     */
    public function previousPage()
    {
        if ($this->currentPagePreview > 1) {
            $this->currentPagePreview--;
        }
    }

    /**
     * Navigate to next page
     */
    public function nextPage()
    {
        if ($this->currentPagePreview < $this->totalPages) {
            $this->currentPagePreview++;
        }
    }

    /**
     * Apply signature to the selected page
     */
    public function applySignature()
    {
        $this->validate([
            'signatoryId' => 'required|exists:signatories,id',
            'pageNumber' => 'required|integer|min:1|max:' . $this->totalPages,
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
            
            // Get document processor service
            $documentProcessor = app(DocumentProcessorService::class);
            
            // Convert display coordinates to PDF coordinates for the selected page
            $pdfCoords = $this->convertDisplayToPdfCoordinates(
                $this->signatureX,
                $this->signatureY,
                $this->signatureScale
            );
            
            \Log::info('Applying signature to page:', [
                'page_number' => $this->pageNumber,
                'total_pages' => $this->totalPages,
                'display_pixels' => [
                    'x' => $this->signatureX, 
                    'y' => $this->signatureY,
                    'scale' => $this->signatureScale
                ],
                'pdf_points' => $pdfCoords,
                'page_dimensions_points' => [
                    'width' => $this->pdfPointWidth, 
                    'height' => $this->pdfPointHeight
                ]
            ]);
            
            // Use DocumentProcessorService to add signature with TCPDF
            $signedDocumentPath = $documentProcessor->addSignatureToDocument(
                originalFilePath: $originalFilePath,
                signatureImagePath: $signatureImagePath,
                xPosition: $pdfCoords['x'],
                yPosition: $pdfCoords['y'],
                width: $pdfCoords['width'],
                height: $pdfCoords['height'],
                pageNumber: $this->pageNumber, // Use selected page number
                useTcpdf: true
            );
            
            if (!$signedDocumentPath || !file_exists($signedDocumentPath)) {
                throw new \Exception('Failed to create signed document.');
            }
            
            // Add signed document to submission
            $media = $submission->addSignedDocument($signedDocumentPath, $signatory->id);
            
            // Update submission status to approved
            $submission->update([
                'status' => 'approved',
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
                'signed_page' => $this->pageNumber, // Store which page was signed
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
                content: 'Document approved and digitally signed successfully on page ' . $this->pageNumber . '!'
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

    protected function convertDisplayToPdfCoordinates(
        float $displayX,
        float $displayY,
        float $displayScale
    ): array {
        if ($this->documentWidth <= 0 || $this->documentHeight <= 0) {
            \Log::error("Invalid document dimensions: {$this->documentWidth}x{$this->documentHeight}");
            $this->documentWidth = 794;
            $this->documentHeight = 1123;
            $this->pdfPointWidth = 595.28;
            $this->pdfPointHeight = 841.89;
        }
        
        $contentMarginXPx = $this->documentWidth * 0.05;
        $contentMarginYPx = $this->documentHeight * 0.05;
        
        $contentDisplayX = $displayX - $contentMarginXPx;
        $contentDisplayY = $displayY - $contentMarginYPx;
        
        $contentWidthPx = $this->documentWidth * 0.9;
        $contentHeightPx = $this->documentHeight * 0.9;
        
        $scaleX = $this->pdfPointWidth / $contentWidthPx;
        $scaleY = $this->pdfPointHeight / $contentHeightPx;
        
        $pdfX = max(0, $contentDisplayX * $scaleX);
        $pdfY = max(0, $contentDisplayY * $scaleY);
        
        $baseWidthPx = 100;
        $baseHeightPx = 40;
        
        $signatureWidthPx = $baseWidthPx * $displayScale;
        $signatureHeightPx = $baseHeightPx * $displayScale;
        
        $signatureWidthPoints = $signatureWidthPx * $scaleX;
        $signatureHeightPoints = $signatureHeightPx * $scaleY;
        
        $maxX = $this->pdfPointWidth - $signatureWidthPoints;
        $maxY = $this->pdfPointHeight - $signatureHeightPoints;
        
        if ($pdfX > $maxX) {
            $pdfX = max(0, $maxX);
        }
        
        if ($pdfY > $maxY) {
            $pdfY = max(0, $maxY);
        }
        
        return [
            'x' => $pdfX,
            'y' => $pdfY,
            'width' => $signatureWidthPoints,
            'height' => $signatureHeightPoints
        ];
    }

    public function cancel()
    {
        $this->showModal = false;
        $this->resetExcept(['submissionId', 'fileUrl', 'fileExtension']);
    } 

    protected function setDefaultDocumentDimensions()
    {
        $this->documentWidth = 794;
        $this->documentHeight = 1123;
        $this->pdfPointWidth = 595.28;
        $this->pdfPointHeight = 841.89;
        $this->totalPages = 1;
    }

    protected function validateDimensions()
    {
        $issues = [];
        
        if ($this->documentWidth <= 0) {
            $issues[] = "documentWidth is {$this->documentWidth}";
        }
        
        if ($this->documentHeight <= 0) {
            $issues[] = "documentHeight is {$this->documentHeight}";
        }
        
        if ($this->pdfPointWidth <= 0) {
            $issues[] = "pdfPointWidth is {$this->pdfPointWidth}";
        }
        
        if ($this->pdfPointHeight <= 0) {
            $issues[] = "pdfPointHeight is {$this->pdfPointHeight}";
        }
        
        if (!empty($issues)) {
            $this->setDefaultDocumentDimensions();
        }
    }

    public function adjustPdfViewer()
    {
        if (in_array($this->fileExtension, ['pdf'])) {
            $containerWidth = 800;
            
            if ($this->documentWidth > 0) {
                $fitZoom = $containerWidth / $this->documentWidth;
                $this->zoomLevel = max(0.5, min(2.0, $fitZoom));
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.submitted-requirements.e-sign-modal');
    }
}