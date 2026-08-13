<?php

namespace App\Http\Controllers;

use App\Models\Data\PengajuanTtePlacement;
use App\Models\DataMaster\Tte;
use Illuminate\View\View;

class VerifyTteController extends Controller
{
    public function show(string $token): View
    {
        // Cari placement berdasarkan qr_token
        $placement = PengajuanTtePlacement::with([
            'tte.user',
            'tte.perusahaan',
            'pengajuan.perusahaan',
            'pengajuan.jenisDokumen',
            'pengajuan.user',
            'pengajuan.kepada',
        ])->where('qr_token', $token)->first();

        // Jika tidak ditemukan, cek verify_token di tabel tte
        // (untuk QR dari halaman show TTE, bukan dari dokumen)
        if (!$placement) {
            $tte = Tte::with(['user', 'perusahaan'])
                      ->where('verify_token', $token)
                      ->first();

            return view('verify.tte', [
                'valid'     => $tte !== null && $tte->isValid(),
                'tte'       => $tte,
                'placement' => null,
                'pengajuan' => null,
                'token'     => $token,
            ]);
        }

        $tte       = $placement->tte;
        $pengajuan = $placement->pengajuan;
        $valid     = $tte && $tte->isValid() && $placement->signed_at !== null;

        return view('verify.tte', compact('valid', 'tte', 'placement', 'pengajuan', 'token'));
    }
}