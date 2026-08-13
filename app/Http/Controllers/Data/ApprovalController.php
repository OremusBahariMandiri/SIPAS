<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanTerusan;
use App\Models\Data\PengajuanApproval;
use App\Models\Data\PengajuanTtePlacement;
use App\Services\TteService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class ApprovalController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $terusans = PengajuanTerusan::with(['pengajuan.perusahaan', 'pengajuan.user', 'departemen'])
            ->where('id_departemen', $user->id_departemen)
            ->where('status', 'waiting')
            ->whereHas('pengajuan', function ($q) {
                $q->whereIn('status', ['waiting', 'in_review']);
            })
            ->get()
            ->filter(function ($terusan) {
                $prev = $terusan->pengajuan->terusans()
                    ->where('urutan', '<', $terusan->urutan)
                    ->where('status', '!=', 'approved')
                    ->exists();
                return !$prev;
            });

        $kepadas = PengajuanSurat::with(['perusahaan', 'user', 'jenisDokumen'])
            ->where('id_kepada', $user->id)
            ->whereIn('status', ['waiting', 'in_review'])
            ->whereDoesntHave('terusans', function ($q) {
                $q->where('status', 'waiting');
            })
            ->get();

        return view('data.approval.index', compact('terusans', 'kepadas'));
    }

    public function review(PengajuanSurat $submission): View|RedirectResponse
    {
        $user = auth()->user();

        $submission->load([
            'perusahaan', 'jenisDokumen', 'kepada',
            'user', 'terusans.departemen',
        ]);

        $tahap   = null;
        $idRef   = 0;
        $needTte = false;

        $activeTerusan = $submission->terusans()
            ->where('id_departemen', $user->id_departemen)
            ->where('status', 'waiting')
            ->first();

        if ($activeTerusan) {
            $tahap   = 'terusan';
            $idRef   = $activeTerusan->id;
            $needTte = $activeTerusan->require_tte;
        } elseif ($submission->id_kepada === $user->id) {
            $tahap   = 'kepada';
            $idRef   = 0;
            $needTte = true;
        } else {
            abort(403, 'You are not authorized to review this submission.');
        }

        // ← Ambil TTE sesuai perusahaan pengajuan
        $tte = $user->tteForPerusahaan($submission->id_perusahaan);

        if ($needTte && (!$tte || !$tte->isValid())) {
            return redirect()->route('data.approval.index')
                ->with('error', 'You do not have an active TTE for company "' .
                    ($submission->perusahaan->nama ?? '-') .
                    '". Please contact administrator.');
        }

        return view('data.approval.review', compact(
            'submission', 'tahap', 'idRef', 'needTte', 'tte'
        ));
    }

    public function approve(Request $request, PengajuanSurat $submission): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'tahap'   => ['required', 'in:terusan,kepada'],
            'id_ref'  => ['required', 'integer'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'halaman' => ['nullable', 'integer', 'min:1'],
            'pos_x'   => ['nullable', 'numeric'],
            'pos_y'   => ['nullable', 'numeric'],
            'lebar'   => ['nullable', 'numeric'],
            'tinggi'  => ['nullable', 'numeric'],
        ]);

        // ← Ambil TTE sesuai perusahaan pengajuan
        $tte = $user->tteForPerusahaan($submission->id_perusahaan);

        // Simpan placement jika ada koordinat TTD
        if ($request->filled('pos_x') && $request->filled('pos_y') && $tte) {
            PengajuanTtePlacement::create([
                'id_pengajuan' => $submission->id,
                'id_tte'       => $tte->id, // ← TTE sesuai perusahaan
                'tahap'        => $request->tahap,
                'id_ref'       => $request->id_ref,
                'halaman'      => $request->halaman ?? 1,
                'pos_x'        => $request->pos_x,
                'pos_y'        => $request->pos_y,
                'lebar'        => $request->lebar  ?? 71,
                'tinggi'       => $request->tinggi ?? 71,
                'qr_token'     => Str::random(64),
            ]);
        }

        // Catat approval log
        PengajuanApproval::create([
            'id_pengajuan' => $submission->id,
            'tahap'        => $request->tahap,
            'id_ref'       => $request->id_ref,
            'id_approver'  => $user->id,
            'aksi'         => 'approve',
            'catatan'      => $request->catatan,
            'acted_at'     => now(),
        ]);

        // ── Tahap Terusan ────────────────────────────────────
        if ($request->tahap === 'terusan') {
            PengajuanTerusan::where('id', $request->id_ref)->update([
                'status'      => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $submission->update(['status' => 'in_review']);

            return redirect()->route('data.approval.index')
                ->with('success', 'Forwarding approval has been submitted.');
        }

        // ── Tahap Kepada (Final) ─────────────────────────────
        if ($request->tahap === 'kepada') {
            $submission->update(['status' => 'approved']);

            $submission->load([
                'ttePlacements.tte.perusahaan', // ← relasi perusahaan langsung dari TTE
                'ttePlacements.tte.user',
            ]);

            if ($submission->ttePlacements->isNotEmpty()) {
                try {
                    $signedPath = (new TteService())->injectTteToPdf($submission);
                    $submission->update(['file_signed' => $signedPath]);
                } catch (\Throwable $e) {
                    \Log::error('TTE PDF inject failed', [
                        'pengajuan_id' => $submission->id,
                        'error'        => $e->getMessage(),
                        'trace'        => $e->getTraceAsString(),
                    ]);

                    return redirect()->route('data.approval.index')
                        ->with('success', 'Submission approved, but TTE injection failed. Please contact administrator.');
                }
            }

            return redirect()->route('data.approval.index')
                ->with('success', 'Submission has been fully approved and signed.');
        }

        return redirect()->route('data.approval.index')
            ->with('success', 'Submission has been approved.');
    }

    public function reject(Request $request, PengajuanSurat $submission): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'tahap'   => ['required', 'in:terusan,kepada'],
            'id_ref'  => ['required', 'integer'],
            'catatan' => ['required', 'string', 'max:500'],
        ], [
            'catatan.required' => 'Rejection reason is required.',
        ]);

        PengajuanApproval::create([
            'id_pengajuan' => $submission->id,
            'tahap'        => $request->tahap,
            'id_ref'       => $request->id_ref,
            'id_approver'  => $user->id,
            'aksi'         => 'reject',
            'catatan'      => $request->catatan,
            'acted_at'     => now(),
        ]);

        if ($request->tahap === 'terusan') {
            PengajuanTerusan::where('id', $request->id_ref)->update([
                'status'      => 'rejected',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'catatan'     => $request->catatan,
            ]);
        }

        $submission->update(['status' => 'rejected']);

        return redirect()->route('data.approval.index')
            ->with('success', 'Submission has been rejected.');
    }
}