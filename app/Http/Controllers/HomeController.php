<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanApproval;
use App\Models\Data\PengajuanTerusan;
use App\Models\Data\PengajuanTtePlacement;
use App\Models\DataMaster\Perusahaan;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user   = auth()->user();
        $userId = $user->id;

        // ── Stat cards ───────────────────────────────────────────────────────
        $total    = PengajuanSurat::byUser($userId)->count();
        $draft    = PengajuanSurat::byUser($userId)->byStatus('draft')->count();
        $waiting  = PengajuanSurat::byUser($userId)->byStatus('waiting')->count();
        $inReview = PengajuanSurat::byUser($userId)->byStatus('in_review')->count();
        $approved = PengajuanSurat::byUser($userId)->byStatus('approved')->count();
        $rejected = PengajuanSurat::byUser($userId)->byStatus('rejected')->count();
        $needAction = $draft + $rejected;

        // ── 5 surat terbaru ──────────────────────────────────────────────────
        $recents = PengajuanSurat::with(['perusahaan', 'jenisDokumen'])
            ->byUser($userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // ── 5 approval terbaru ───────────────────────────────────────────────
        $recentApprovals = PengajuanApproval::with([
                'pengajuan.perusahaan',
                'pengajuan.user',
            ])
            ->where('id_approver', $userId)
            ->orderByDesc('acted_at')
            ->limit(5)
            ->get();

        // ── Pending CC (terusan) ─────────────────────────────────────────────
        $pendingTerusans = PengajuanTerusan::with(['pengajuan.user', 'pengajuan.perusahaan', 'pengajuan.terusans'])
            ->where('id_user', $userId)
            ->where('status', 'waiting')
            ->whereHas('pengajuan', fn($q) => $q->whereIn('status', ['waiting', 'in_review']))
            ->get()
            ->filter(function ($terusan) {
                return !$terusan->pengajuan->terusans
                    ->where('urutan', '<', $terusan->urutan)
                    ->where('status', '!=', 'approved')
                    ->isNotEmpty();
            });

        // ── Pending recipient (final approver) ───────────────────────────────
        $pendingKepadas = PengajuanSurat::with(['user', 'perusahaan'])
            ->where('id_kepada', $userId)
            ->whereIn('status', ['waiting', 'in_review'])
            ->whereDoesntHave('terusans', fn($q) => $q->where('status', 'waiting'))
            ->orderByDesc('created_at')
            ->get();

        // ── Approval history stats ───────────────────────────────────────────
        $totalApproved = PengajuanApproval::where('id_approver', $userId)
            ->where('aksi', 'approve')
            ->count();

        $totalRejected = PengajuanApproval::where('id_approver', $userId)
            ->where('aksi', 'reject')
            ->count();

        $totalTteUsed = PengajuanTtePlacement::whereHas('tte', fn($q) => $q->where('id_user', $userId))
            ->count();

        // ── Semua perusahaan aktif sebagai base ──────────────────────────────
        $allPerusahaan = Perusahaan::aktif()->orderBy('nama')->get();

        // ── Stats per perusahaan (submission) ───────────────────────────────
        $submissionsByPerusahaan = PengajuanSurat::byUser($userId)
            ->get()
            ->groupBy('id_perusahaan');

        $perusahaanStats = $allPerusahaan->map(function ($p) use ($submissionsByPerusahaan) {
            $group = $submissionsByPerusahaan->get($p->id, collect());
            return [
                'nama'      => $p->nama,
                'singkatan' => $p->singkatan ?? $p->nama,
                'total'     => $group->count(),
                'approved'  => $group->where('status', 'approved')->count(),
                'rejected'  => $group->where('status', 'rejected')->count(),
                'waiting'   => $group->whereIn('status', ['waiting', 'in_review'])->count(),
                'draft'     => $group->where('status', 'draft')->count(),
            ];
        });

        // ── Approval stats per perusahaan ────────────────────────────────────
        $approvalsByPerusahaan = PengajuanApproval::where('id_approver', $userId)
            ->with('pengajuan')
            ->get()
            ->groupBy(fn($a) => $a->pengajuan?->id_perusahaan);

        $approvalPerusahaanStats = $allPerusahaan->map(function ($p) use ($approvalsByPerusahaan) {
            $group = $approvalsByPerusahaan->get($p->id, collect());
            return [
                'nama'      => $p->nama,
                'singkatan' => $p->singkatan ?? $p->nama,
                'approved'  => $group->where('aksi', 'approve')->count(),
                'rejected'  => $group->where('aksi', 'reject')->count(),
                'total'     => $group->count(),
            ];
        });

        return view('home', compact(
            'total',
            'draft',
            'waiting',
            'inReview',
            'approved',
            'rejected',
            'needAction',
            'recents',
            'recentApprovals',
            'pendingTerusans',
            'pendingKepadas',
            'totalApproved',
            'totalRejected',
            'totalTteUsed',
            'perusahaanStats',
            'approvalPerusahaanStats',
        ));
    }
}