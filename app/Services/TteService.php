<?php

namespace App\Services;

use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanTtePlacement;
use Illuminate\Support\Str;
use setasign\Fpdi\Tcpdf\Fpdi;

class TteService
{
    /**
     * Generate QR Code PNG — endroid/qr-code v6, pure GD tanpa imagick
     */
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

        // ← Ambil logo dari perusahaan yang terkait dengan TTE ini
        // bukan dari user->perusahaan (departemen user)
        $logoPath = null;

        if ($placement->tte->perusahaan?->logo) {
            $candidate = storage_path('app/public/' . $placement->tte->perusahaan->logo);
            if (file_exists($candidate)) {
                $logoPath = $candidate;
            }
        }

        \Log::info('TTE Logo', [
            'tte_id'      => $placement->tte->id,
            'perusahaan'  => $placement->tte->perusahaan?->nama,
            'logo_field'  => $placement->tte->perusahaan?->logo,
            'logo_path'   => $logoPath,
            'file_exists' => $logoPath ? file_exists($logoPath) : false,
        ]);

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

                // Padding background di sekitar logo
                $padding = 12;
                $bgX     = $logoX - $padding;
                $bgY     = $logoY - $padding;
                $bgSize  = $logoSize + ($padding * 2);

                $white = imagecolorallocate($qrImage, 255, 255, 255);

                // ── Pilih salah satu: ──────────────────────────

                // OPSI A — Background KOTAK (persegi dengan sudut rounded)
                imagefilledrectangle(
                    $qrImage,
                    $bgX,
                    $bgY,
                    $bgX + $bgSize,
                    $bgY + $bgSize,
                    $white
                );

                //test

                imagecopyresampled(
                    $qrImage,
                    $logoImage,
                    $logoX,
                    $logoY,
                    0,
                    0,
                    $logoSize,
                    $logoSize,
                    imagesx($logoImage),
                    imagesy($logoImage)
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
    /**
     * Inject semua QR Code TTE ke PDF
     * Koordinat dari DB dalam satuan PDF points (pt)
     * FPDI butuh satuan mm → konversi: 1pt = 0.352778mm
     */
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

        \Log::info('TTE: PDF source loaded', [
            'pengajuan_id' => $pengajuan->id,
            'total_pages'  => $totalPages,
            'placements'   => $pengajuan->ttePlacements->count(),
        ]);

        for ($pageNo = 1; $pageNo <= $totalPages; $pageNo++) {
            $templateId  = $pdf->importPage($pageNo);
            $size        = $pdf->getTemplateSize($templateId);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';

            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height']);

            $placements = $pengajuan->ttePlacements->where('halaman', $pageNo);

            foreach ($placements as $placement) {
                // Konversi pt → mm (1 pt = 0.352778 mm)
                $ptToMm = 0.352778;

                $qrWidthMm  = $placement->lebar  * $ptToMm;
                $qrHeightMm = $placement->tinggi * $ptToMm;

                // pos_x = pojok kiri QR dalam pt → langsung konversi ke mm
                $xMm = $placement->pos_x * $ptToMm;

                // pos_y = pojok bawah QR dari bottom halaman (PDF origin)
                // FPDI origin top-left:
                // yMm = pageHeight - pos_y_mm - qrHeight_mm
                // tapi pos_y sudah = bottom QR, jadi:
                // yMm = pageHeight - (pos_y + qrHeight) * ptToMm
                // = pageHeight - pos_y_mm - qrHeightMm
                $posYMm = $placement->pos_y * $ptToMm;
                $yMm    = $size['height'] - $posYMm - $qrHeightMm;

                // Clamp
                $xMm = max(0, min($xMm, $size['width']  - $qrWidthMm));
                $yMm = max(0, min($yMm, $size['height'] - $qrHeightMm));

                \Log::info('TTE coordinate', [
                    'pos_x_pt'  => $placement->pos_x,
                    'pos_y_pt'  => $placement->pos_y,
                    'xMm'       => round($xMm, 2),
                    'yMm'       => round($yMm, 2),
                    'pageH_mm'  => $size['height'],
                    'qrH_mm'    => $qrHeightMm,
                ]);

                \Log::info('TTE: Placement coordinate', [
                    'placement_id' => $placement->id,
                    'page'         => $pageNo,
                    'pos_x_pt'     => $placement->pos_x,
                    'pos_y_pt'     => $placement->pos_y,
                    'lebar_pt'     => $placement->lebar,
                    'tinggi_pt'    => $placement->tinggi,
                    'xMm'          => round($xMm, 2),
                    'yMm'          => round($yMm, 2),
                    'qrWidthMm'    => round($qrWidthMm, 2),
                    'qrHeightMm'   => round($qrHeightMm, 2),
                    'pageW_mm'     => $size['width'],
                    'pageH_mm'     => $size['height'],
                ]);

                // Generate QR PNG
                $qrPng = $this->generateQrCode($placement);

                // Simpan ke temp file
                $tmpQr = tempnam(sys_get_temp_dir(), 'tte_') . '.png';
                file_put_contents($tmpQr, $qrPng);

                // Tempel QR ke PDF
                $pdf->Image($tmpQr, $xMm, $yMm, $qrWidthMm, $qrHeightMm, 'PNG');

                $placement->update(['signed_at' => now()]);
                @unlink($tmpQr);

                \Log::info('TTE: Placement injected', ['placement_id' => $placement->id]);
            }
        }

        $pdf->Output($signedPath, 'F');

        \Log::info('TTE: Signed PDF saved', [
            'path'   => $signedPath,
            'exists' => file_exists($signedPath),
            'size'   => file_exists($signedPath) ? filesize($signedPath) : 0,
        ]);

        if (!file_exists($signedPath) || filesize($signedPath) === 0) {
            throw new \RuntimeException('Signed PDF was not created or is empty.');
        }

        return $signedRelative;
    }
}
