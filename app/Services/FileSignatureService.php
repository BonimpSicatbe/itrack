<?php

namespace App\Services;

use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;
use Intervention\Image\Image;

class FileSignatureService
{
    /**
     * Add e-signature to any file (PDF or Image).
     */
    public function applySignature($filePath, $signaturePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $outputPath = $this->generateOutputPath($filePath);

        if ($extension === 'pdf') {
            return $this->stampPdf($filePath, $signaturePath, $outputPath);
        }

        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            return $this->stampImage($filePath, $signaturePath, $outputPath);
        }

        throw new \Exception("Unsupported file type: $extension");
    }

    /**
     * PDF stamping using FPDI
     */
    private function stampPdf($pdfPath, $signaturePath, $outputPath)
    {
        $pdf = new FPDI();
        $pageCount = $pdf->setSourceFile($pdfPath);

        for ($i = 1; $i <= $pageCount; $i++) {
            $pdf->AddPage();
            $template = $pdf->importPage($i);
            $pdf->useTemplate($template, 0, 0);

            // Position: bottom-right
            // X=160, Y=250 (adjust based on page size)
            $pdf->Image($signaturePath, 160, 250, 40);
        }

        $pdf->Output('F', $outputPath);

        return $outputPath;
    }

    /**
     * Image stamping using Intervention Image
     */
    private function stampImage($imagePath, $signaturePath, $outputPath)
    {
        $image = Image::make($imagePath);

        // Resize signature (optional)
        $signature = Image::make($signaturePath)->resize(150, null, function ($c) {
            $c->aspectRatio();
        });

        // Insert at bottom-right
        $image->insert($signature, 'bottom-right', 20, 20);

        $image->save($outputPath);

        return $outputPath;
    }


    /**
     * Generate new filename for stamped file
     */
    private function generateOutputPath($originalPath)
    {
        $dir = dirname($originalPath);
        $name = pathinfo($originalPath, PATHINFO_FILENAME);
        $ext = pathinfo($originalPath, PATHINFO_EXTENSION);

        return $dir . '/' . $name . '_signed.' . $ext;
    }
}
