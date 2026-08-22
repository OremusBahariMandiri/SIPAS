<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanApproval;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $userId = auth()->id();

        // ── Stat cards: submission milik user ini ────────────────────────────
        $total      = PengajuanSurat::byUser($userId)->count();
        $draft      = PengajuanSurat::byUser($userId)->byStatus('draft')->count();
        $waiting    = PengajuanSurat::byUser($userId)->byStatus('waiting')->count();
        $inReview   = PengajuanSurat::byUser($userId)->byStatus('in_review')->count();
        $approved   = PengajuanSurat::byUser($userId)->byStatus('approved')->count();
        $rejected   = PengajuanSurat::byUser($userId)->byStatus('rejected')->count();

        // ── Perlu perhatian: draft + rejected (belum terselesaikan) ──────────
        $needAction = $draft + $rejected;

        // ── 5 surat terbaru milik user ───────────────────────────────────────
        $recents = PengajuanSurat::with(['perusahaan', 'jenisDokumen'])
            ->byUser($userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // ── 5 approval action terbaru yang dilakukan user ini ────────────────
        $recentApprovals = PengajuanApproval::with(['pengajuan.perusahaan'])
            ->where('id_approver', $userId)
            ->orderByDesc('acted_at')
            ->limit(5)
            ->get();

        return view('home', compact(
            'total',
            'draft',
            'waiting',
            'inReview',
            'approved',
            'rejected',
            'needAction',
            'recents',
            'recentApprovals'
        ));
    }
}