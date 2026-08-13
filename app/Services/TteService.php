<?php

namespace App\Services;

use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanTtePlacement;
use Illuminate\Support\Str;
use setasign\Fpdi\Tcpdf\Fpdi;

class TteService
{
    public function generateQrCode(PengajuanTtePlacement $placement): string
    {
        $verifyUrl = url('/verify/tte/' . $placement->qr_token);

        $qrCode = new \Endroid\QrCode\QrCode(
            data: $verifyUrl,
            encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
            errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            foregroundColor: new \Endroid\QrCode\Color\Color(0, 0, 0),
            backgroundColor: new \Endroid\QrCode\Color\Color(255, 255, 255),
        );

        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $result = $writer->write($qrCode);
        $qrPng  = $result->getString();

        $qrImage = imagecreatefromstring($qrPng);
        if (!$qrImage) {
            throw new \RuntimeException('Failed to create QR image from PNG string.');
        }
        $qrSize = imagesx($qrImage);

        // Ambil logo dari perusahaan TTE
        $logoPath = null;
        if ($placement->tte->perusahaan?->logo) {
            $candidate = storage_path('app/public/' . $placement->tte->perusahaan->logo);
            if (file_exists($candidate)) {
                $logoPath = $candidate;
            }
        }

        if ($logoPath) {
            $ext       = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $logoImage = match ($ext) {
                'png'         => imagecreatefrompng($logoPath),
                'jpg', 'jpeg' => imagecreatefromjpeg($logoPath),
                default       => null,
            };

            if ($logoImage) {
                $logoSize = (int) ($qrSize * 0.25);
                $logoX    = (int) (($qrSize - $logoSize) / 2);
                $logoY    = (int) (($qrSize - $logoSize) / 2);

                $padding = 12;
                $bgX     = $logoX - $padding;
                $bgY     = $logoY - $padding;
                $bgSize  = $logoSize + ($padding * 2);

                $white = imagecolorallocate($qrImage, 255, 255, 255);

                imagefilledrectangle(
                    $qrImage,
                    $bgX, $bgY,
                    $bgX + $bgSize, $bgY + $bgSize,
                    $white
                );

                imagecopyresampled(
                    $qrImage, $logoImage,
                    $logoX, $logoY,
                    0, 0,
                    $logoSize, $logoSize,
                    imagesx($logoImage), imagesy($logoImage)
                );

                imagedestroy($logoImage);
            }
        }

        ob_start();
        imagepng($qrImage);
        $pngData = ob_get_clean();
        imagedestroy($qrImage);

        return $pngData;
    }

    public function injectTteToPdf(PengajuanSurat $pengajuan): string
    {
        $sourcePath = storage_path('app/' . $pengajuan->file_original);

        if (!file_exists($sourcePath)) {
            throw new \RuntimeException('Original PDF file not found: ' . $sourcePath);
        }

        $signedFilename = Str::uuid() . '_signed.pdf';
        $signedRelative = 'submissions/signed/' . $signedFilename;
        $signedPath     = storage_path('app/' . $signedRelative);

        $signedDir = dirname($signedPath);
        if (!is_dir($signedDir)) {
            mkdir($signedDir, 0755, true);
        }

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $totalPages = $pdf->setSourceFile($sourcePath);

        for ($pageNo = 1; $pageNo <= $totalPages; $pageNo++) {
            $templateId  = $pdf->importPage($pageNo);
            $size        = $pdf->getTemplateSize($templateId);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';

            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height']);

            $placements = $pengajuan->ttePlacements->where('halaman', $pageNo);

            foreach ($placements as $placement) {
                $ptToMm     = 0.352778;
                $qrWidthMm  = $placement->lebar  * $ptToMm;
                $qrHeightMm = $placement->tinggi * $ptToMm;
                $xMm        = $placement->pos_x  * $ptToMm;
                $posYMm     = $placement->pos_y  * $ptToMm;
                $yMm        = $size['height'] - $posYMm - $qrHeightMm;

                $xMm = max(0, min($xMm, $size['width']  - $qrWidthMm));
                $yMm = max(0, min($yMm, $size['height'] - $qrHeightMm));

                $qrPng = $this->generateQrCode($placement);
                $tmpQr = tempnam(sys_get_temp_dir(), 'tte_') . '.png';
                file_put_contents($tmpQr, $qrPng);

                $pdf->Image($tmpQr, $xMm, $yMm, $qrWidthMm, $qrHeightMm, 'PNG');

                $placement->update(['signed_at' => now()]);
                @unlink($tmpQr);
            }
        }

        $pdf->Output($signedPath, 'F');

        if (!file_exists($signedPath) || filesize($signedPath) === 0) {
            throw new \RuntimeException('Signed PDF was not created or is empty.');
        }

        return $signedRelative;
    }
}