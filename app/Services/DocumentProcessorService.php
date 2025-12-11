<?php

namespace App\Services;

use Smalot\PdfParser\Parser as PdfParser;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Log;
use setasign\Fpdi\PdfReader\PageBoundaries;

class DocumentProcessorService
{
    /**
     * Add signature to document with optional manual positioning
     * 
     * @param string $originalFilePath Path to the original document
     * @param string $signatoryName Name of the signatory
     * @param string $signatureImagePath Path to signature image
     * @param float|null $xPosition Manual X position (points from left)
     * @param float|null $yPosition Manual Y position (points from bottom for PDF)
     * @param float $width Signature width in points (default: 60)
     * @param float $height Signature height in points (default: 30)
     * @param int $pageNumber Page number to place signature on (default: 1)
     * @return string|null Path to signed document or null on failure
     */
    public function addSignatureToDocument(
        string $originalFilePath,
        string $signatoryName,
        string $signatureImagePath,
        float $xPosition = null,
        float $yPosition = null,
        float $width = 60,
        float $height = 30,
        int $pageNumber = 1
    ): ?string {
        $extension = strtolower(pathinfo($originalFilePath, PATHINFO_EXTENSION));
        
        // Ensure temp directory exists
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        switch ($extension) {
            case 'pdf':
                return $this->processPdfDocumentWithManualPositioning(
                    $originalFilePath, 
                    $signatoryName, 
                    $signatureImagePath, 
                    $xPosition,
                    $yPosition,
                    $width,
                    $height,
                    $pageNumber
                );
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                return $this->processImageDocumentWithManualPositioning(
                    $originalFilePath, 
                    $signatoryName, 
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
     * Process PDF document with manual or auto positioning
     */
    protected function processPdfDocumentWithManualPositioning(
        string $pdfPath,
        string $signatoryName,
        string $signatureImagePath,
        ?float $xPosition = null,
        ?float $yPosition = null,
        float $width = 60,
        float $height = 30,
        int $pageNumber = 1
    ): ?string {
        try {
            \Log::info("=== STARTING E-SIGNATURE PROCESS ===");
            \Log::info("PDF: {$pdfPath}");
            \Log::info("Signatory: {$signatoryName}");
            
            if (!file_exists($signatureImagePath)) {
                \Log::error("Signature image not found at: {$signatureImagePath}");
                return null;
            }
            
            if (!file_exists($pdfPath)) {
                \Log::error("PDF file not found at: {$pdfPath}");
                return null;
            }

            // Create FPDI instance
            $fpdi = new Fpdi();
            
            // Set source file
            $pageCount = $fpdi->setSourceFile($pdfPath);
            
            // Validate page number
            if ($pageNumber < 1 || $pageNumber > $pageCount) {
                \Log::warning("Invalid page number {$pageNumber}, using last page instead");
                $pageNumber = $pageCount;
            }

            // If manual position is provided, use it directly
            if ($xPosition !== null && $yPosition !== null) {
                \Log::info("Using manual positioning - X: {$xPosition}, Y: {$yPosition}");
                
                $bestMatch = [
                    'x' => $xPosition,
                    'y' => $yPosition,
                    'page' => $pageNumber
                ];
                
            } else {
                // Auto-detect position (original logic)
                \Log::info("Auto-detecting signature position...");
                $bestMatch = $this->findAutoSignaturePosition($pdfPath, $signatoryName, $pageNumber);
            }

            \Log::info("=== FINAL SIGNATURE POSITION ===");
            \Log::info("Page: {$bestMatch['page']}");
            \Log::info("Signature Position - X: {$bestMatch['x']}, Y: {$bestMatch['y']}");
            \Log::info("Signature Dimensions - Width: {$width}, Height: {$height}");

            // Import all pages and add signature to the target page
            for ($currentPage = 1; $currentPage <= $pageCount; $currentPage++) {
                $templateId = $fpdi->importPage($currentPage, PageBoundaries::MEDIA_BOX);
                $size = $fpdi->getTemplateSize($templateId);
                
                // Add page with same orientation and size
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($templateId);
                
                // Add signature to the target page
                if ($currentPage == $bestMatch['page']) {
                    $pageWidth = $size['width'];
                    $pageHeight = $size['height'];
                    
                    // Adjust coordinates for FPDI
                    // FPDI coordinates: 0,0 is top-left, Y increases downward
                    // PDF coordinates: 0,0 is bottom-left, Y increases upward
                    
                    $signatureX = $bestMatch['x'];
                    $signatureY = $bestMatch['y'];
                    
                    // If Y coordinate is from bottom (PDF standard), convert to top-left
                    if ($signatureY < $pageHeight) {
                        // Assume Y is from bottom, convert to top-left
                        $signatureY = $pageHeight - $signatureY - $height;
                    }
                    
                    // Ensure signature fits on page
                    $signatureX = max(10, min($signatureX, $pageWidth - $width - 10));
                    $signatureY = max(10, min($signatureY, $pageHeight - $height - 10));
                    
                    \Log::info("Placing signature at - X: {$signatureX}, Y: {$signatureY} (page coords)");
                    \Log::info("Page dimensions - Width: {$pageWidth}, Height: {$pageHeight}");
                    
                    // Add signature image
                    $fpdi->Image(
                        $signatureImagePath,
                        $signatureX,
                        $signatureY,
                        $width,
                        $height,
                        'PNG'
                    );
                    
                    // Optional: Add signatory name text below signature
                    $this->addSignatoryText($fpdi, $signatureX, $signatureY, $width, $height, $signatoryName);
                }
            }
            
            // Generate output filename
            $outputFilename = 'signed_' . time() . '_' . basename($pdfPath);
            $outputPath = storage_path('app/temp/' . $outputFilename);
            
            // Output PDF
            $fpdi->Output($outputPath, 'F');
            
            // Verify file was created
            if (!file_exists($outputPath)) {
                \Log::error("Failed to create signed PDF at: {$outputPath}");
                return null;
            }
            
            $fileSize = filesize($outputPath);
            \Log::info("Signed document saved: {$outputPath} ({$fileSize} bytes)");
            \Log::info("=== E-SIGNATURE PROCESS COMPLETE ===");
            
            return $outputPath;
            
        } catch (\Exception $e) {
            \Log::error('PDF signature processing failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }
    
    /**
     * Find automatic signature position by scanning text
     */
    protected function findAutoSignaturePosition(string $pdfPath, string $signatoryName, int $targetPage = 1): array
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($pdfPath);
            $pages = $pdf->getPages();
            
            $bestMatch = null;
            $bestPage = $targetPage;
            
            // Search for the EXACT position of the signatory name
            foreach ($pages as $pageIndex => $page) {
                $currentPage = $pageIndex + 1;
                
                // Only search on target page if specified
                if ($targetPage > 0 && $currentPage != $targetPage) {
                    continue;
                }
                
                \Log::info("=== Scanning Page {$currentPage} for '{$signatoryName}' ===");
                
                // Get detailed text data with coordinates
                $data = $page->getDataTm();
                
                foreach ($data as $itemIndex => $item) {
                    if (!is_array($item) || !isset($item[0]) || !is_string($item[0])) {
                        continue;
                    }
                    
                    $text = $item[0];
                    $x = isset($item[1]) ? floatval($item[1]) : 0;
                    $y = isset($item[2]) ? floatval($item[2]) : 0;
                    
                    // Log text near potential matches
                    if (stripos($text, substr($signatoryName, 0, 5)) !== false) {
                        \Log::info("Partial match found - Text: '{$text}', X: {$x}, Y: {$y}");
                    }
                    
                    // Check for exact or partial name match
                    if ($this->textContainsName($text, $signatoryName)) {
                        \Log::info("FOUND NAME: '{$text}' at X: {$x}, Y: {$y}");
                        
                        // Calculate signature position (LEFT of the name)
                        $signatureX = $x - 70; // Position signature 70 points left of the name
                        $signatureY = $y; // Same Y level
                        
                        $bestMatch = [
                            'x' => $signatureX,
                            'y' => $signatureY,
                            'text_x' => $x,
                            'text_y' => $y,
                            'text' => $text
                        ];
                        $bestPage = $currentPage;
                        break 2; // Found best match, stop searching
                    }
                }
            }
            
            // If exact name not found, look for signature lines or labels
            if (!$bestMatch) {
                \Log::info("Exact name not found, looking for signature fields...");
                
                foreach ($pages as $pageIndex => $page) {
                    $currentPage = $pageIndex + 1;
                    
                    // Only search on target page if specified
                    if ($targetPage > 0 && $currentPage != $targetPage) {
                        continue;
                    }
                    
                    $data = $page->getDataTm();
                    
                    foreach ($data as $item) {
                        if (!is_array($item) || !isset($item[0]) || !is_string($item[0])) {
                            continue;
                        }
                        
                        $text = strtolower(trim($item[0]));
                        $x = isset($item[1]) ? floatval($item[1]) : 0;
                        $y = isset($item[2]) ? floatval($item[2]) : 0;
                        
                        // Look for signature-related text
                        $signatureKeywords = [
                            'signature', 'sign', 'signed', 'sign here', 
                            'approved by', 'prepared by', 'noted by', 'submitted by',
                            'signature:', 'signature of', 'signature line'
                        ];
                        
                        foreach ($signatureKeywords as $keyword) {
                            if (stripos($text, $keyword) !== false) {
                                \Log::info("Found signature field: '{$text}' at X: {$x}, Y: {$y}");
                                
                                // Place signature to the right of the label
                                $bestMatch = [
                                    'x' => $x + 50, // Right of the label
                                    'y' => $y,
                                    'text_x' => $x,
                                    'text_y' => $y,
                                    'text' => $text
                                ];
                                $bestPage = $currentPage;
                                break 3;
                            }
                        }
                    }
                }
            }
            
            // Fallback: Place on target page, bottom right
            if (!$bestMatch) {
                \Log::info("No signature field found, using fallback position");
                
                // Get page dimensions for fallback positioning
                $parser = new PdfParser();
                $pdf = $parser->parseFile($pdfPath);
                $pages = $pdf->getPages();
                
                if (isset($pages[$bestPage - 1])) {
                    $page = $pages[$bestPage - 1];
                    $data = $page->getDataTm();
                    
                    // Try to find max Y coordinate on page
                    $maxY = 0;
                    foreach ($data as $item) {
                        if (is_array($item) && isset($item[2])) {
                            $maxY = max($maxY, floatval($item[2]));
                        }
                    }
                    
                    $bestMatch = [
                        'x' => 400, // Right side
                        'y' => $maxY > 0 ? $maxY - 100 : 100, // Near bottom
                    ];
                } else {
                    $bestMatch = ['x' => 400, 'y' => 100];
                }
            }
            
            return [
                'x' => $bestMatch['x'],
                'y' => $bestMatch['y'],
                'page' => $bestPage
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error finding signature position: ' . $e->getMessage());
            return [
                'x' => 400,
                'y' => 100,
                'page' => $targetPage
            ];
        }
    }
    
    /**
     * Add signatory name text below signature
     */
    protected function addSignatoryText($fpdi, $x, $y, $width, $height, $signatoryName): void
    {
        try {
            // Set font for signatory name
            $fpdi->SetFont('Helvetica', '', 8);
            $fpdi->SetTextColor(0, 0, 0); // Black
            
            // Position text below signature
            $textX = $x;
            $textY = $y + $height + 2;
            
            // Add a line above the name
            $lineY = $textY - 1;
            $fpdi->SetDrawColor(0, 0, 0);
            $fpdi->SetLineWidth(0.1);
            $fpdi->Line($x, $lineY, $x + $width, $lineY);
            
            // Add the name
            $fpdi->SetXY($textX, $textY);
            $fpdi->Cell($width, 4, $signatoryName, 0, 0, 'C');
            
        } catch (\Exception $e) {
            \Log::warning('Could not add signatory text: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if text contains the signatory name
     */
    protected function textContainsName(string $text, string $signatoryName): bool
    {
        // Clean the text
        $cleanText = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $text);
        $cleanText = preg_replace('/\s+/', ' ', trim($cleanText));
        
        // Clean the name
        $cleanName = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $signatoryName);
        $cleanName = preg_replace('/\s+/', ' ', trim($cleanName));
        
        // Split name into parts
        $nameParts = explode(' ', $cleanName);
        
        // Check if ALL name parts are in the text (case-insensitive)
        foreach ($nameParts as $part) {
            if (strlen($part) > 2 && stripos($cleanText, $part) === false) {
                return false;
            }
        }
        
        // Also check for full name match
        if (stripos($cleanText, $cleanName) !== false) {
            return true;
        }
        
        // Check for last name only (common in signatures)
        $lastName = end($nameParts);
        if (strlen($lastName) > 2 && stripos($cleanText, $lastName) !== false) {
            return true;
        }
        
        return false;
    }

    /**
     * Process image document with manual positioning
     */
    protected function processImageDocumentWithManualPositioning(
        string $imagePath,
        string $signatoryName,
        string $signatureImagePath,
        ?float $xPosition = null,
        ?float $yPosition = null,
        float $width = 60,
        float $height = 30
    ): ?string {
        try {
            \Log::info("Processing image document for e-signature: {$imagePath}");
            
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
            
            list($imageWidth, $imageHeight) = $imageInfo;
            
            // Create a new PDF and add the image to it
            $fpdi = new Fpdi();
            
            // Convert pixels to points (1 inch = 72 points = 96 pixels typically)
            $pointsPerPixel = 72 / 96;
            $pdfWidth = $imageWidth * $pointsPerPixel;
            $pdfHeight = $imageHeight * $pointsPerPixel;
            
            // Create a PDF with proportional dimensions
            $fpdi->AddPage('P', [$pdfWidth, $pdfHeight]);
            
            // Add the original image as background
            $fpdi->Image($imagePath, 0, 0, $pdfWidth, $pdfHeight);
            
            // Calculate signature position
            if ($xPosition !== null && $yPosition !== null) {
                // Use manual position (convert from pixels to points if needed)
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
            $fpdi->Image(
                $signatureImagePath,
                $signatureX,
                $signatureY,
                $width,
                $height,
                'PNG'
            );
            
            // Add signatory name text
            $this->addSignatoryText($fpdi, $signatureX, $signatureY, $width, $height, $signatoryName);
            
            // Save the PDF
            $outputFilename = 'signed_' . time() . '_' . basename($imagePath, '.jpg') . '.pdf';
            $outputPath = storage_path('app/temp/' . $outputFilename);
            $fpdi->Output($outputPath, 'F');
            
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
            signatoryName: $signatory->name,
            signatureImagePath: $signatory->signature_path,
            xPosition: $xPosition,
            yPosition: $yPosition,
            width: $width,
            height: $height,
            pageNumber: $pageNumber
        );
    }
}