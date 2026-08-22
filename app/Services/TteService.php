<?php

namespace App\Services;

use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanTtePlacement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Tcpdf\Fpdi;

class TteService
{
    // =========================================================================
    // GENERATE QR CODE PNG (dengan logo perusahaan di tengah)
    // =========================================================================

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
                imagefilledrectangle($qrImage, $bgX, $bgY, $bgX + $bgSize, $bgY + $bgSize, $white);
                imagecopyresampled(
                    $qrImage, $logoImage,
                    $logoX, $logoY, 0, 0,
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

    // =========================================================================
    // INJECT STAGE — inject placement dari tahap & id_ref tertentu.
    //
    // Digunakan setelah setiap approve agar file_current selalu up-to-date.
    //
    // Alur:
    //   1. Baca PDF sumber: file_current (jika ada) → file_original (fallback)
    //   2. Inject hanya placement yang dikirim ($placements)
    //   3. Simpan sebagai file_current yang baru
    //   4. Hapus file_current lama (jika bukan file_original)
    //   5. Update kolom file_current di DB
    //   6. Tandai placement yang diinjeksi dengan signed_at
    // =========================================================================

    /**
     * @param  PengajuanSurat                                $pengajuan
     * @param  \Illuminate\Database\Eloquent\Collection      $placements  Subset placement yang akan diinjeksi sekarang
     * @return string  Path relatif file_current yang baru
     */
    public function injectStageTteToPdf(PengajuanSurat $pengajuan, $placements): string
    {
        // Tentukan PDF sumber: selalu pakai file_current jika sudah ada,
        // sehingga QR dari tahap-tahap sebelumnya tetap terbawa.
        $sourcePath = $pengajuan->file_current
            ? storage_path('app/' . $pengajuan->file_current)
            : storage_path('app/' . $pengajuan->file_original);

        if (!file_exists($sourcePath)) {
            throw new \RuntimeException('Source PDF not found: ' . $sourcePath);
        }

        // Kelompokkan placement per halaman untuk efisiensi
        $byPage = $placements->groupBy('halaman');

        // Buat path output baru
        $newFilename = Str::uuid() . '_signed.pdf';
        $newRelative = 'submissions/signed/' . $newFilename;
        $newPath     = storage_path('app/' . $newRelative);

        $signedDir = dirname($newPath);
        if (!is_dir($signedDir)) {
            mkdir($signedDir, 0755, true);
        }

        // ── Proses PDF dengan FPDI ──────────────────────────────────────────
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

            // Inject hanya placement di halaman ini
            $pagePlacements = $byPage->get($pageNo, collect());

            foreach ($pagePlacements as $placement) {
                $ptToMm     = 0.352778;
                $qrWidthMm  = $placement->lebar  * $ptToMm;
                $qrHeightMm = $placement->tinggi * $ptToMm;
                $xMm        = $placement->pos_x  * $ptToMm;
                $yMm        = (float) $size['height']
                              - ($placement->pos_y * $ptToMm)
                              - $qrHeightMm;

                // Clamp agar tidak keluar halaman
                $xMm = max(0.0, min((float) $size['width']  - $qrWidthMm,  $xMm));
                $yMm = max(0.0, min((float) $size['height'] - $qrHeightMm, $yMm));

                $qrPng = $this->generateQrCode($placement);
                $tmpQr = tempnam(sys_get_temp_dir(), 'tte_') . '.png';
                file_put_contents($tmpQr, $qrPng);

                $pdf->Image($tmpQr, $xMm, $yMm, $qrWidthMm, $qrHeightMm, 'PNG');

                @unlink($tmpQr);
            }
        }

        $pdf->Output($newPath, 'F');

        if (!file_exists($newPath) || filesize($newPath) === 0) {
            throw new \RuntimeException('Signed PDF was not created or is empty.');
        }

        // ── Hapus file_current lama (bukan file_original) ──────────────────
        $oldCurrent = $pengajuan->file_current;
        if ($oldCurrent && $oldCurrent !== $pengajuan->file_original) {
            Storage::disk('local')->delete($oldCurrent);
        }

        // ── Tandai placement sebagai sudah diinjeksi ────────────────────────
        foreach ($placements as $placement) {
            $placement->update(['signed_at' => now()]);
        }

        // ── Update DB ───────────────────────────────────────────────────────
        $pengajuan->update(['file_current' => $newRelative]);

        return $newRelative;
    }

    // =========================================================================
    // INJECT FINAL — inject SEMUA placement yang belum ditandatangani.
    // Tetap dipertahankan untuk backward-compatibility / emergency re-inject.
    // =========================================================================

    public function injectTteToPdf(PengajuanSurat $pengajuan): string
    {
        $unsignedPlacements = $pengajuan->ttePlacements
            ->whereNull('signed_at');

        if ($unsignedPlacements->isEmpty()) {
            // Tidak ada yang perlu diinjeksi — kembalikan file_current yang ada
            return $pengajuan->file_current ?? $pengajuan->file_original;
        }

        return $this->injectStageTteToPdf($pengajuan, $unsignedPlacements);
    }
}