<?php

namespace App\Services;

use Smalot\PdfParser\Parser as PdfParser;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\Tcpdf\Fpdi as TcpdfFpdi;
use Illuminate\Support\Facades\Log;
use setasign\Fpdi\PdfReader\PageBoundaries;
use TCPDF;

class DocumentProcessorService
{
    /**
     * Add signature to document with optional manual positioning
     */
    public function addSignatureToDocument(
        string $originalFilePath,
        string $signatureImagePath,
        float $xPosition = null,
        float $yPosition = null,
        float $width = 60,
        float $height = 30,
        int $pageNumber = 1,
        bool $useTcpdf = true
    ): ?string {
        $extension = strtolower(pathinfo($originalFilePath, PATHINFO_EXTENSION));
        
        // Ensure temp directory exists
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        switch ($extension) {
            case 'pdf':
                if ($useTcpdf) {
                    return $this->processPdfWithTcpdfFpdi(
                        $originalFilePath, 
                        $signatureImagePath, 
                        $xPosition,
                        $yPosition,
                        $width,
                        $height,
                        $pageNumber
                    );
                } else {
                    return $this->processPdfDocumentWithManualPositioning(
                        $originalFilePath, 
                        $signatureImagePath, 
                        $xPosition,
                        $yPosition,
                        $width,
                        $height,
                        $pageNumber
                    );
                }
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                return $this->processImageDocumentWithTcpdf(
                    $originalFilePath, 
                    $signatureImagePath, 
                    $xPosition,
                    $yPosition,
                    $width,
                    $height
                );
            default:
                Log::warning("Unsupported document type for e-signature: {$extension}");
                return null;
        }
    }

    /**
     * Process PDF document with TCPDF-FPDI for accurate positioning
     * Updated to properly handle page selection
     */
    protected function processPdfWithTcpdfFpdi(
        string $pdfPath,
        string $signatureImagePath,
        ?float $xPosition = null,
        ?float $yPosition = null,
        float $width = 60,
        float $height = 30,
        int $pageNumber = 1
    ): ?string {
        try {
            \Log::info("=== STARTING E-SIGNATURE PROCESS WITH TCPDF-FPDI ===");
            \Log::info("PDF: {$pdfPath}");
            \Log::info("Target Page: {$pageNumber}");
            \Log::info("Position - X: {$xPosition}, Y: {$yPosition}, Width: {$width}, Height: {$height}");
            
            if (!file_exists($signatureImagePath)) {
                \Log::error("Signature image not found at: {$signatureImagePath}");
                return null;
            }
            
            if (!file_exists($pdfPath)) {
                \Log::error("PDF file not found at: {$pdfPath}");
                return null;
            }

            // Create TCPDF-FPDI instance
            $pdf = new TcpdfFpdi();
            
            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetAutoPageBreak(false, 0);

            // Set source file
            $pageCount = $pdf->setSourceFile($pdfPath);
            
            \Log::info("Total pages in PDF: {$pageCount}");
            
            // Validate page number
            if ($pageNumber < 1 || $pageNumber > $pageCount) {
                \Log::warning("Invalid page number {$pageNumber}, using last page instead");
                $pageNumber = $pageCount;
            }

            // Import all pages and add signature to the target page
            for ($currentPage = 1; $currentPage <= $pageCount; $currentPage++) {
                // Import page
                $templateId = $pdf->importPage($currentPage);
                $size = $pdf->getTemplateSize($templateId);
                
                // Handle both key naming conventions
                $pageWidth = $size['w'] ?? $size['width'] ?? 595.28;
                $pageHeight = $size['h'] ?? $size['height'] ?? 841.89;
                $orientation = ($size['orientation'] ?? ($pageWidth > $pageHeight ? 'L' : 'P'));
                
                // Add page with same orientation and size
                $pdf->AddPage($orientation, [$pageWidth, $pageHeight]);
                $pdf->useTemplate($templateId);
                
                // Add signature ONLY to the target page
                if ($currentPage == $pageNumber) {
                    // Use the provided xPosition and yPosition directly
                    // Ensure signature fits on page (safety check)
                    $signatureX = max(10, min($xPosition, $pageWidth - $width - 10));
                    $signatureY = max(10, min($yPosition, $pageHeight - $height - 10));
                    
                    \Log::info("Placing signature on page {$pageNumber} at - X: {$signatureX}, Y: {$signatureY}");
                    \Log::info("Page dimensions - Width: {$pageWidth}, Height: {$pageHeight}");
                    
                    // Add signature image with TCPDF
                    $pdf->Image(
                        $signatureImagePath,
                        $signatureX,
                        $signatureY,
                        $width,
                        $height,
                        'PNG',
                        '',
                        '',
                        false,
                        300,
                        '',
                        false,
                        false,
                        0,
                        false,
                        false,
                        false
                    );
                    
                    \Log::info("Signature added to page {$pageNumber}");
                }
            }
            
            // Generate output filename
            $outputFilename = 'signed_tcpdf_' . time() . '_' . basename($pdfPath);
            $outputPath = storage_path('app/temp/' . $outputFilename);
            
            // Output PDF
            $pdf->Output($outputPath, 'F');
            
            // Verify file was created
            if (!file_exists($outputPath)) {
                \Log::error("Failed to create signed PDF at: {$outputPath}");
                return null;
            }
            
            $fileSize = filesize($outputPath);
            \Log::info("Signed document saved: {$outputPath} ({$fileSize} bytes)");
            \Log::info("=== TCPDF-FPDI E-SIGNATURE PROCESS COMPLETE ===");
            
            return $outputPath;
            
        } catch (\Exception $e) {
            \Log::error('TCPDF-FPDI signature processing failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Process image document with TCPDF
     */
    protected function processImageDocumentWithTcpdf(
        string $imagePath,
        string $signatureImagePath,
        ?float $xPosition = null,
        ?float $yPosition = null,
        float $width = 60,
        float $height = 30
    ): ?string {
        try {
            \Log::info("Processing image document with TCPDF: {$imagePath}");
            
            if (!file_exists($signatureImagePath)) {
                \Log::error("Signature image not found");
                return null;
            }
            
            if (!file_exists($imagePath)) {
                \Log::error("Image file not found");
                return null;
            }
            
            // Get image dimensions
            $imageInfo = getimagesize($imagePath);
            if (!$imageInfo) {
                \Log::error("Invalid image file");
                return null;
            }
            
            list($imageWidthPx, $imageHeightPx) = $imageInfo;
            
            // Convert pixels to points (72 points per inch, assuming 96 DPI)
            $pixelsPerPoint = 96 / 72;
            $pdfWidth = $imageWidthPx / $pixelsPerPoint;
            $pdfHeight = $imageHeightPx / $pixelsPerPoint;
            
            // Create a new TCPDF
            $pdf = new TCPDF('P', 'pt', [$pdfWidth, $pdfHeight], true, 'UTF-8', false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetAutoPageBreak(false, 0);
            $pdf->AddPage();
            
            // Add the original image as background
            $pdf->Image(
                $imagePath,
                0,
                0,
                $pdfWidth,
                $pdfHeight,
                '',
                '',
                '',
                false,
                300,
                '',
                false,
                false,
                0,
                false,
                false,
                false
            );
            
            // Calculate signature position
            if ($xPosition !== null && $yPosition !== null) {
                // Use manual position (already in points)
                $signatureX = $xPosition;
                $signatureY = $yPosition;
            } else {
                // Default position: bottom right
                $signatureX = $pdfWidth - $width - 20;
                $signatureY = $pdfHeight - $height - 20;
            }
            
            // Ensure signature fits on image
            $signatureX = max(10, min($signatureX, $pdfWidth - $width - 10));
            $signatureY = max(10, min($signatureY, $pdfHeight - $height - 10));
            
            \Log::info("Placing signature at - X: {$signatureX}, Y: {$signatureY}");
            
            // Add signature image
            $pdf->Image(
                $signatureImagePath,
                $signatureX,
                $signatureY,
                $width,
                $height,
                'PNG',
                '',
                '',
                false,
                300,
                '',
                false,
                false,
                0,
                false,
                false,
                false
            );
            
            // Save the PDF
            $outputFilename = 'signed_image_' . time() . '_' . basename($imagePath, '.jpg') . '.pdf';
            $outputPath = storage_path('app/temp/' . $outputFilename);
            $pdf->Output($outputPath, 'F');
            
            // Verify file was created
            if (!file_exists($outputPath)) {
                \Log::error("Failed to create signed PDF from image");
                return null;
            }
            
            $fileSize = filesize($outputPath);
            \Log::info("Signed image saved as PDF: {$outputPath} ({$fileSize} bytes)");
            
            return $outputPath;
            
        } catch (\Exception $e) {
            \Log::error('Image signature processing failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Get PDF dimensions in points using TCPDF-FPDI
     * Returns array with width, height, orientation, and page_count
     */
    public function getPdfDimensions(string $pdfPath): array
    {
        try {
            if (!file_exists($pdfPath)) {
                \Log::error("PDF file does not exist: {$pdfPath}");
                return $this->getDefaultDimensions();
            }
            
            $pdf = new TcpdfFpdi();
            $pageCount = $pdf->setSourceFile($pdfPath);
            
            if ($pageCount === 0) {
                \Log::error("Failed to read PDF or PDF is empty: {$pdfPath}");
                return $this->getDefaultDimensions();
            }
            
            // Get dimensions of the first page
            $templateId = $pdf->importPage(1);
            $size = $pdf->getTemplateSize($templateId);
            
            // Handle different return formats from getTemplateSize()
            if (isset($size['width']) && isset($size['height'])) {
                $width = (float)$size['width'];
                $height = (float)$size['height'];
                $orientation = isset($size['orientation']) ? $size['orientation'] : ($width > $height ? 'L' : 'P');
            } elseif (isset($size['w']) && isset($size['h'])) {
                $width = (float)$size['w'];
                $height = (float)$size['h'];
                $orientation = isset($size['orientation']) ? $size['orientation'] : ($width > $height ? 'L' : 'P');
            } elseif (isset($size[0]) && isset($size[1])) {
                $width = (float)$size[0];
                $height = (float)$size[1];
                $orientation = $width > $height ? 'L' : 'P';
            } else {
                \Log::warning("Unexpected size array format, using defaults");
                return $this->getDefaultDimensions();
            }
            
            // Validate dimensions are reasonable
            if ($width < 10 || $height < 10 || $width > 10000 || $height > 10000) {
                \Log::warning("Suspicious PDF dimensions: {$width}x{$height}, using defaults");
                return $this->getDefaultDimensions();
            }
            
            \Log::info("PDF dimensions determined:", [
                'width' => $width,
                'height' => $height,
                'orientation' => $orientation,
                'page_count' => $pageCount,
                'pdf_path' => basename($pdfPath)
            ]);
            
            return [
                'width' => $width,
                'height' => $height,
                'orientation' => $orientation,
                'page_count' => $pageCount
            ];
            
        } catch (\Exception $e) {
            \Log::error('Failed to get PDF dimensions: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return $this->getDefaultDimensions();
        }
    }

    /**
     * Get default dimensions (A4)
     */
    protected function getDefaultDimensions(): array
    {
        return [
            'width' => 595.28,  // A4 width in points (210mm)
            'height' => 841.89, // A4 height in points (297mm)
            'orientation' => 'P',
            'page_count' => 1
        ];
    }

    /**
     * Convert display pixels to PDF points
     */
    public function pixelsToPoints(float $pixels, float $dpi = 96): float
    {
        return ($pixels / $dpi) * 72;
    }
    
    /**
     * Convert PDF points to display pixels
     */
    public function pointsToPixels(float $points, float $dpi = 96): float
    {
        return ($points / 72) * $dpi;
    }

    /**
     * Process PDF with manual positioning (legacy FPDI method)
     * Updated to handle page selection
     */
    protected function processPdfDocumentWithManualPositioning(
        string $pdfPath,
        string $signatureImagePath,
        ?float $xPosition = null,
        ?float $yPosition = null,
        float $width = 60,
        float $height = 30,
        int $pageNumber = 1
    ): ?string {
        try {
            \Log::info("=== USING LEGACY FPDI METHOD ===");
            \Log::info("Target Page: {$pageNumber}");
            
            // Create FPDI instance
            $fpdi = new Fpdi();
            
            // Set source file
            $pageCount = $fpdi->setSourceFile($pdfPath);
            
            \Log::info("Total pages: {$pageCount}");
            
            // Validate page number
            if ($pageNumber < 1 || $pageNumber > $pageCount) {
                \Log::warning("Invalid page number {$pageNumber}, using last page instead");
                $pageNumber = $pageCount;
            }

            // Import all pages and add signature to the target page
            for ($currentPage = 1; $currentPage <= $pageCount; $currentPage++) {
                $templateId = $fpdi->importPage($currentPage, PageBoundaries::MEDIA_BOX);
                $size = $fpdi->getTemplateSize($templateId);
                
                // Add page with same orientation and size
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($templateId);
                
                // Add signature to the target page
                if ($currentPage == $pageNumber) {
                    $pageWidth = $size['width'];
                    $pageHeight = $size['height'];
                    
                    // FPDI coordinates: 0,0 is top-left, Y increases downward
                    $signatureX = $xPosition;
                    $signatureY = $pageHeight - $yPosition - $height; // Convert from bottom-left to top-left
                    
                    // Ensure signature fits on page
                    $signatureX = max(10, min($signatureX, $pageWidth - $width - 10));
                    $signatureY = max(10, min($signatureY, $pageHeight - $height - 10));
                    
                    \Log::info("Legacy FPDI - Placing signature on page {$pageNumber} at - X: {$signatureX}, Y: {$signatureY}");
                    
                    // Add signature image
                    $fpdi->Image(
                        $signatureImagePath,
                        $signatureX,
                        $signatureY,
                        $width,
                        $height,
                        'PNG'
                    );
                    
                    \Log::info("Signature added to page {$pageNumber}");
                }
            }
            
            // Generate output filename
            $outputFilename = 'signed_fpdi_' . time() . '_' . basename($pdfPath);
            $outputPath = storage_path('app/temp/' . $outputFilename);
            
            // Output PDF
            $fpdi->Output($outputPath, 'F');
            
            if (!file_exists($outputPath)) {
                \Log::error("Failed to create signed PDF with FPDI");
                return null;
            }
            
            return $outputPath;
            
        } catch (\Exception $e) {
            \Log::error('Legacy FPDI processing failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Quick method for manual positioning (simplified interface)
     */
    public function signDocumentManually(
        string $originalFilePath,
        int $signatoryId,
        float $xPosition,
        float $yPosition,
        float $width = 60,
        float $height = 30,
        int $pageNumber = 1
    ): ?string {
        // Get signatory details
        $signatory = \App\Models\Signatory::with('media')->find($signatoryId);
        
        if (!$signatory || !$signatory->has_signature) {
            \Log::error("Signatory not found or has no signature: {$signatoryId}");
            return null;
        }
        
        return $this->addSignatureToDocument(
            originalFilePath: $originalFilePath,
            signatureImagePath: $signatory->signature_path,
            xPosition: $xPosition,
            yPosition: $yPosition,
            width: $width,
            height: $height,
            pageNumber: $pageNumber,
            useTcpdf: true
        );
    }

    /**
     * Simple TCPDF-only method for adding signatures
     */
    public function addSignatureWithSimpleTcpdf(
        string $pdfPath,
        string $signatureImagePath,
        float $x,
        float $y,
        float $width,
        float $height
    ): ?string {
        try {
            // Create a simple TCPDF document
            $pdf = new TCPDF('P', 'pt', 'A4', true, 'UTF-8', false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetAutoPageBreak(false, 0);
            $pdf->AddPage();
            
            // Add signature
            $pdf->Image($signatureImagePath, $x, $y, $width, $height, 'PNG');
            
            $outputPath = storage_path('app/temp/simple_signed_' . time() . '.pdf');
            $pdf->Output($outputPath, 'F');
            
            return file_exists($outputPath) ? $outputPath : null;
            
        } catch (\Exception $e) {
            \Log::error('Simple TCPDF error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get dimensions for a specific page in a PDF
     * Useful when different pages have different sizes
     */
    public function getPdfPageDimensions(string $pdfPath, int $pageNumber = 1): array
    {
        try {
            if (!file_exists($pdfPath)) {
                \Log::error("PDF file does not exist: {$pdfPath}");
                return $this->getDefaultDimensions();
            }
            
            $pdf = new TcpdfFpdi();
            $pageCount = $pdf->setSourceFile($pdfPath);
            
            if ($pageCount === 0) {
                \Log::error("Failed to read PDF or PDF is empty: {$pdfPath}");
                return $this->getDefaultDimensions();
            }
            
            // Validate page number
            if ($pageNumber < 1 || $pageNumber > $pageCount) {
                \Log::warning("Invalid page number {$pageNumber}, using first page instead");
                $pageNumber = 1;
            }
            
            // Get dimensions of the specific page
            $templateId = $pdf->importPage($pageNumber);
            $size = $pdf->getTemplateSize($templateId);
            
            // Handle different return formats
            if (isset($size['width']) && isset($size['height'])) {
                $width = (float)$size['width'];
                $height = (float)$size['height'];
                $orientation = isset($size['orientation']) ? $size['orientation'] : ($width > $height ? 'L' : 'P');
            } elseif (isset($size['w']) && isset($size['h'])) {
                $width = (float)$size['w'];
                $height = (float)$size['h'];
                $orientation = isset($size['orientation']) ? $size['orientation'] : ($width > $height ? 'L' : 'P');
            } elseif (isset($size[0]) && isset($size[1])) {
                $width = (float)$size[0];
                $height = (float)$size[1];
                $orientation = $width > $height ? 'L' : 'P';
            } else {
                \Log::warning("Unexpected size array format, using defaults");
                return $this->getDefaultDimensions();
            }
            
            \Log::info("PDF page dimensions:", [
                'page' => $pageNumber,
                'width' => $width,
                'height' => $height,
                'orientation' => $orientation,
                'total_pages' => $pageCount,
                'pdf_path' => basename($pdfPath)
            ]);
            
            return [
                'width' => $width,
                'height' => $height,
                'orientation' => $orientation,
                'page_count' => $pageCount
            ];
            
        } catch (\Exception $e) {
            \Log::error('Failed to get PDF page dimensions: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return $this->getDefaultDimensions();
        }
    }

    /**
     * Extract a specific page from PDF for preview purposes
     */
    public function extractPdfPage(string $pdfPath, int $pageNumber = 1): ?string
    {
        try {
            if (!file_exists($pdfPath)) {
                \Log::error("PDF file does not exist: {$pdfPath}");
                return null;
            }
            
            $pdf = new TcpdfFpdi();
            $pageCount = $pdf->setSourceFile($pdfPath);
            
            if ($pageCount === 0) {
                \Log::error("Failed to read PDF or PDF is empty: {$pdfPath}");
                return null;
            }
            
            // Validate page number
            if ($pageNumber < 1 || $pageNumber > $pageCount) {
                \Log::warning("Invalid page number {$pageNumber}, using first page instead");
                $pageNumber = 1;
            }
            
            // Import only the specified page
            $templateId = $pdf->importPage($pageNumber);
            $size = $pdf->getTemplateSize($templateId);
            
            $pageWidth = $size['w'] ?? $size['width'] ?? 595.28;
            $pageHeight = $size['h'] ?? $size['height'] ?? 841.89;
            $orientation = ($size['orientation'] ?? ($pageWidth > $pageHeight ? 'L' : 'P'));
            
            // Add page with same orientation and size
            $pdf->AddPage($orientation, [$pageWidth, $pageHeight]);
            $pdf->useTemplate($templateId);
            
            // Generate output filename
            $outputFilename = 'page_' . $pageNumber . '_' . time() . '_' . basename($pdfPath);
            $outputPath = storage_path('app/temp/' . $outputFilename);
            
            // Output PDF
            $pdf->Output($outputPath, 'F');
            
            if (!file_exists($outputPath)) {
                \Log::error("Failed to extract PDF page");
                return null;
            }
            
            \Log::info("Extracted page {$pageNumber} to: {$outputPath}");
            return $outputPath;
            
        } catch (\Exception $e) {
            \Log::error('Failed to extract PDF page: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Add signature to multiple pages
     * Useful for signing all pages or specific multiple pages
     */
    public function addSignatureToMultiplePages(
        string $originalFilePath,
        string $signatureImagePath,
        array $pages = [], // Array of page numbers to sign, empty means all pages
        float $xPosition = null,
        float $yPosition = null,
        float $width = 60,
        float $height = 30,
        bool $useTcpdf = true
    ): ?string {
        try {
            $extension = strtolower(pathinfo($originalFilePath, PATHINFO_EXTENSION));
            
            if ($extension !== 'pdf') {
                \Log::warning("Multi-page signing only supported for PDF files");
                return null;
            }
            
            if (!file_exists($originalFilePath)) {
                \Log::error("Original file not found: {$originalFilePath}");
                return null;
            }
            
            if (!file_exists($signatureImagePath)) {
                \Log::error("Signature image not found: {$signatureImagePath}");
                return null;
            }
            
            // If no specific pages provided, sign all pages
            $signAllPages = empty($pages);
            
            if ($useTcpdf) {
                return $this->processPdfWithTcpdfFpdiMultiPage(
                    $originalFilePath,
                    $signatureImagePath,
                    $pages,
                    $xPosition,
                    $yPosition,
                    $width,
                    $height,
                    $signAllPages
                );
            } else {
                return $this->processPdfDocumentWithManualPositioningMultiPage(
                    $originalFilePath,
                    $signatureImagePath,
                    $pages,
                    $xPosition,
                    $yPosition,
                    $width,
                    $height,
                    $signAllPages
                );
            }
            
        } catch (\Exception $e) {
            \Log::error('Multi-page signing failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Process PDF with TCPDF-FPDI for multiple pages
     */
    protected function processPdfWithTcpdfFpdiMultiPage(
        string $pdfPath,
        string $signatureImagePath,
        array $targetPages,
        ?float $xPosition = null,
        ?float $yPosition = null,
        float $width = 60,
        float $height = 30,
        bool $signAllPages = false
    ): ?string {
        try {
            $pdf = new TcpdfFpdi();
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetAutoPageBreak(false, 0);

            $pageCount = $pdf->setSourceFile($pdfPath);
            
            \Log::info("Multi-page signing - Total pages: {$pageCount}, Target pages: " . implode(',', $targetPages));
            
            // Process each page
            for ($currentPage = 1; $currentPage <= $pageCount; $currentPage++) {
                $templateId = $pdf->importPage($currentPage);
                $size = $pdf->getTemplateSize($templateId);
                
                $pageWidth = $size['w'] ?? $size['width'] ?? 595.28;
                $pageHeight = $size['h'] ?? $size['height'] ?? 841.89;
                $orientation = ($size['orientation'] ?? ($pageWidth > $pageHeight ? 'L' : 'P'));
                
                $pdf->AddPage($orientation, [$pageWidth, $pageHeight]);
                $pdf->useTemplate($templateId);
                
                // Check if we should sign this page
                $shouldSign = $signAllPages || in_array($currentPage, $targetPages);
                
                if ($shouldSign) {
                    // Use provided position or default to bottom right
                    $signatureX = $xPosition ?? ($pageWidth - $width - 20);
                    $signatureY = $yPosition ?? ($pageHeight - $height - 20);
                    
                    // Ensure signature fits on page
                    $signatureX = max(10, min($signatureX, $pageWidth - $width - 10));
                    $signatureY = max(10, min($signatureY, $pageHeight - $height - 10));
                    
                    \Log::info("Adding signature to page {$currentPage} at - X: {$signatureX}, Y: {$signatureY}");
                    
                    $pdf->Image(
                        $signatureImagePath,
                        $signatureX,
                        $signatureY,
                        $width,
                        $height,
                        'PNG',
                        '',
                        '',
                        false,
                        300,
                        '',
                        false,
                        false,
                        0,
                        false,
                        false,
                        false
                    );
                }
            }
            
            $outputFilename = 'signed_multi_' . time() . '_' . basename($pdfPath);
            $outputPath = storage_path('app/temp/' . $outputFilename);
            $pdf->Output($outputPath, 'F');
            
            if (!file_exists($outputPath)) {
                \Log::error("Failed to create multi-page signed PDF");
                return null;
            }
            
            return $outputPath;
            
        } catch (\Exception $e) {
            \Log::error('Multi-page TCPDF-FPDI processing failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process PDF with manual positioning for multiple pages
     */
    protected function processPdfDocumentWithManualPositioningMultiPage(
        string $pdfPath,
        string $signatureImagePath,
        array $targetPages,
        ?float $xPosition = null,
        ?float $yPosition = null,
        float $width = 60,
        float $height = 30,
        bool $signAllPages = false
    ): ?string {
        try {
            $fpdi = new Fpdi();
            $pageCount = $fpdi->setSourceFile($pdfPath);
            
            \Log::info("Multi-page legacy signing - Total pages: {$pageCount}");
            
            for ($currentPage = 1; $currentPage <= $pageCount; $currentPage++) {
                $templateId = $fpdi->importPage($currentPage, PageBoundaries::MEDIA_BOX);
                $size = $fpdi->getTemplateSize($templateId);
                
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($templateId);
                
                // Check if we should sign this page
                $shouldSign = $signAllPages || in_array($currentPage, $targetPages);
                
                if ($shouldSign) {
                    $pageWidth = $size['width'];
                    $pageHeight = $size['height'];
                    
                    // Use provided position or default to bottom right
                    $signatureX = $xPosition ?? ($pageWidth - $width - 20);
                    $signatureY = $pageHeight - ($yPosition ?? 20) - $height;
                    
                    $signatureX = max(10, min($signatureX, $pageWidth - $width - 10));
                    $signatureY = max(10, min($signatureY, $pageHeight - $height - 10));
                    
                    \Log::info("Legacy - Adding signature to page {$currentPage}");
                    
                    $fpdi->Image(
                        $signatureImagePath,
                        $signatureX,
                        $signatureY,
                        $width,
                        $height,
                        'PNG'
                    );
                }
            }
            
            $outputFilename = 'signed_multi_fpdi_' . time() . '_' . basename($pdfPath);
            $outputPath = storage_path('app/temp/' . $outputFilename);
            $fpdi->Output($outputPath, 'F');
            
            if (!file_exists($outputPath)) {
                \Log::error("Failed to create multi-page signed PDF with FPDI");
                return null;
            }
            
            return $outputPath;
            
        } catch (\Exception $e) {
            \Log::error('Multi-page legacy FPDI processing failed: ' . $e->getMessage());
            return null;
        }
    }
}