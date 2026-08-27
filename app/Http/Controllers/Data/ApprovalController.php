<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanTerusan;
use App\Models\Data\PengajuanApproval;
use App\Models\Data\PengajuanTtePlacement;
use App\Services\TteService;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ApprovalController extends Controller
{
    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request): View
    {
        $user      = auth()->user();
        $isAdmin   = $user->isAdmin();
        $activeTab = $request->get('tab', $isAdmin ? 'history' : 'inbox');

        if ($isAdmin) {
            $terusans = collect();
            $kepadas  = collect();
        } else {
            $terusans = PengajuanTerusan::with([
                'pengajuan.perusahaan',
                'pengajuan.jenisDokumen',
                'pengajuan.sifatSurat',
                'pengajuan.user.departemen',
                'pengajuan.user',
                'pengajuan.terusans',
            ])
                ->where('id_user', $user->id)
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

            $kepadas = PengajuanSurat::with([
                'perusahaan',
                'jenisDokumen',
                'sifatSurat',
                'user.departemen',
                'user',
            ])
                ->where('id_kepada', $user->id)
                ->whereIn('status', ['waiting', 'in_review'])
                ->whereDoesntHave('terusans', function ($q) {
                    $q->where('status', 'waiting');
                })
                ->get();
        }

        $perusahaanList = \App\Models\DataMaster\Perusahaan::where('status', 1)
            ->orderBy('nama')
            ->get();

        $histories = null;

        if ($activeTab === 'history') {
            $query = $isAdmin
                ? PengajuanApproval::with([
                    'pengajuan.perusahaan',
                    'pengajuan.jenisDokumen',
                    'approver',
                ])
                : PengajuanApproval::with([
                    'pengajuan.perusahaan',
                    'pengajuan.jenisDokumen',
                ])->where('id_approver', $user->id);

            if ($search = $request->get('search')) {
                $query->whereHas('pengajuan', function ($q) use ($search) {
                    $q->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('perihal', 'like', "%{$search}%");
                });
            }

            if ($status = $request->get('status')) {
                $query->where('aksi', $status);
            }

            if ($perusahaan = $request->get('perusahaan')) {
                $query->whereHas('pengajuan', function ($q) use ($perusahaan) {
                    $q->where('id_perusahaan', $perusahaan);
                });
            }

            if ($dokType = $request->get('dok_type')) {
                $query->whereHas('pengajuan.jenisDokumen', function ($q) use ($dokType) {
                    $q->where('jenis_dokumen', 'like', "%{$dokType}%");
                });
            }

            if ($dateFrom = $request->get('date_from')) {
                $query->whereDate('acted_at', '>=', $dateFrom);
            }

            if ($dateTo = $request->get('date_to')) {
                $query->whereDate('acted_at', '<=', $dateTo);
            }

            $sortable = ['acted_at', 'aksi', 'tahap'];
            $sortCol  = in_array($request->get('sort'), $sortable)
                ? $request->get('sort') : 'acted_at';
            $sortDir  = $request->get('dir') === 'asc' ? 'asc' : 'desc';
            $perPage  = in_array((int) $request->get('per_page'), [10, 15, 25, 50])
                ? (int) $request->get('per_page') : 15;

            $histories = $query
                ->orderBy($sortCol, $sortDir)
                ->paginate($perPage)
                ->withQueryString();
        }

        return view('data.approval.index', compact(
            'terusans',
            'kepadas',
            'histories',
            'activeTab',
            'perusahaanList',
            'isAdmin'
        ));
    }

    // =========================================================================
    // SHOW (Approval History Detail)
    // =========================================================================

    public function show(PengajuanApproval $approval): View
    {
        $user    = auth()->user();
        $isAdmin = $user->isAdmin();

        if (!$isAdmin && $approval->id_approver !== $user->id) {
            abort(403);
        }

        $approval->load([
            'pengajuan.perusahaan',
            'pengajuan.jenisDokumen',
            'pengajuan.sifatSurat',
            'pengajuan.user',
            'pengajuan.kepada',
            'pengajuan.terusans.user',
            'approver',
        ]);

        $pengajuan = $approval->pengajuan;

        return view('data.approval.show', compact('approval', 'pengajuan', 'isAdmin'));
    }

    // =========================================================================
    // SHOW FILE (Snapshot PDF per tahapan)
    // =========================================================================

    public function showFile(PengajuanApproval $approval): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user    = auth()->user();
        $isAdmin = $user->isAdmin();

        if (!$isAdmin && $approval->id_approver !== $user->id) {
            abort(403);
        }

        $filePath = $approval->file_snapshot;

        if (!$filePath) {
            $approval->loadMissing('pengajuan');
            $pengajuan = $approval->pengajuan;

            $filePath = $approval->tahap === 'kepada'
                ? ($pengajuan->file_signed ?? $pengajuan->file_current ?? $pengajuan->file_original)
                : ($pengajuan->file_current ?? $pengajuan->file_original);
        }

        if (!$filePath) abort(404);

        $fullPath = storage_path('app/' . $filePath);
        if (!file_exists($fullPath)) abort(404);

        return response()->stream(function () use ($fullPath) {
            readfile($fullPath);
        }, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
            'Cache-Control'       => 'no-store',
        ]);
    }

    // =========================================================================
    // REVIEW
    // =========================================================================

    public function review(PengajuanSurat $submission): View|RedirectResponse
    {
        $user = auth()->user();

        $submission->load([
            'perusahaan',
            'jenisDokumen',
            'kepada',
            'user',
            'terusans.user',
        ]);

        $tahap   = null;
        $idRef   = 0;
        $needTte = false;

        $activeTerusan = $submission->terusans()
            ->where('id_user', $user->id)
            ->where('status', 'waiting')
            ->first();

        if ($activeTerusan) {
            $prevPending = $submission->terusans()
                ->where('urutan', '<', $activeTerusan->urutan)
                ->where('status', '!=', 'approved')
                ->exists();

            if ($prevPending) {
                abort(403, 'Previous forwarding steps have not been approved yet.');
            }

            $tahap   = 'terusan';
            $idRef   = $activeTerusan->id;
            $needTte = (bool) $activeTerusan->require_tte;
        } elseif ($submission->id_kepada === $user->id) {
            $pendingTerusan = $submission->terusans()
                ->where('status', 'waiting')
                ->exists();

            if ($pendingTerusan) {
                abort(403, 'Forwarding steps are not yet complete.');
            }

            $tahap   = 'kepada';
            $idRef   = 0;
            $needTte = true;
        } else {
            abort(403, 'You are not authorized to review this submission.');
        }

        $tte = $needTte ? $user->tteForPerusahaan($submission->id_perusahaan) : null;

        if ($needTte && (!$tte || !$tte->isValid())) {
            return redirect()->route('data.approval.index')
                ->with('error', 'You do not have an active TTE for company "' .
                    ($submission->perusahaan->nama ?? '-') .
                    '". Please contact administrator.');
        }

        return view('data.approval.review', compact(
            'submission',
            'tahap',
            'idRef',
            'needTte',
            'tte'
        ));
    }

    // =========================================================================
    // APPROVE
    // =========================================================================

    public function approve(Request $request, PengajuanSurat $submission): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'tahap'                => ['required', 'in:terusan,kepada'],
            'id_ref'               => ['required', 'integer'],
            'catatan'              => ['nullable', 'string', 'max:500'],
            'placements'           => ['nullable', 'array'],
            'placements.*.halaman' => ['required_with:placements', 'integer', 'min:1'],
            'placements.*.pos_x'   => ['required_with:placements', 'numeric'],
            'placements.*.pos_y'   => ['required_with:placements', 'numeric'],
            'placements.*.lebar'   => ['nullable', 'numeric'],
            'placements.*.tinggi'  => ['nullable', 'numeric'],
        ]);

        // ── Tentukan apakah tahap ini butuh TTE ─────────────────────────────
        $needsTte      = false;
        $requiredCount = 0;
        $terusanReq    = null;

        if ($request->tahap === 'kepada') {
            $needsTte      = true;
            $requiredCount = max(1, (int) ($submission->require_tte_kepada ?? 1));
        } elseif ($request->tahap === 'terusan') {
            $terusanReq = PengajuanTerusan::find($request->id_ref);
            $needsTte   = $terusanReq && (bool) $terusanReq->require_tte;

            if ($needsTte) {
                $requiredCount = max(1, (int) ($terusanReq->require_tte_count ?? 1));
            }
        }

        // ── Validasi placement hanya jika butuh TTE ──────────────────────────
        if ($needsTte) {
            $placedCount = count($request->input('placements', []));

            if ($placedCount < $requiredCount) {
                $label = $requiredCount === 1 ? 'signature' : 'signatures';
                return back()
                    ->withInput()
                    ->withErrors([
                        'placements' => "You must place {$requiredCount} {$label} on the document. Only {$placedCount} placed.",
                    ])
                    ->with('_scroll_to_error', true);
            }
        }

        // ── Inject TTE ke PDF (hanya jika butuh TTE) ────────────────────────
        $newPlacements = collect();

        if ($needsTte && $request->filled('placements')) {
            $tte = $user->tteForPerusahaan($submission->id_perusahaan);

            if ($tte) {
                foreach ($request->placements as $pl) {
                    if (!isset($pl['pos_x'], $pl['pos_y'])) continue;

                    $placement = PengajuanTtePlacement::create([
                        'id_pengajuan' => $submission->id,
                        'id_tte'       => $tte->id,
                        'tahap'        => $request->tahap,
                        'id_ref'       => $request->id_ref,
                        'halaman'      => (int) ($pl['halaman'] ?? 1),
                        'pos_x'        => round((float) $pl['pos_x'], 4),
                        'pos_y'        => round((float) $pl['pos_y'], 4),
                        'lebar'        => (float) ($pl['lebar']  ?? 40),
                        'tinggi'       => (float) ($pl['tinggi'] ?? 40),
                        'qr_token'     => Str::random(64),
                    ]);

                    $newPlacements->push($placement);
                }

                if ($newPlacements->isNotEmpty()) {
                    try {
                        $submission->refresh();

                        $freshPlacements = PengajuanTtePlacement::with('tte.perusahaan')
                            ->whereIn('id', $newPlacements->pluck('id'))
                            ->get();

                        (new TteService())->injectStageTteToPdf($submission, $freshPlacements);
                    } catch (\Throwable $e) {
                        \Log::error('TTE inject failed on approve', [
                            'pengajuan_id' => $submission->id,
                            'tahap'        => $request->tahap,
                            'error'        => $e->getMessage(),
                            'trace'        => $e->getTraceAsString(),
                        ]);
                    }
                }
            }
        }

        // ── Buat snapshot PDF setelah TTE diinjeksi ──────────────────────────
        $submission->refresh();
        $snapshotPath = $this->createSnapshot($submission);

        // ── Catat approval ───────────────────────────────────────────────────
        PengajuanApproval::create([
            'id_pengajuan'  => $submission->id,
            'tahap'         => $request->tahap,
            'id_ref'        => $request->id_ref,
            'id_approver'   => $user->id,
            'aksi'          => 'approve',
            'catatan'       => $request->catatan,
            'acted_at'      => now(),
            'file_snapshot' => $snapshotPath,
        ]);

        // ── Terusan approved ─────────────────────────────────────────────────
        if ($request->tahap === 'terusan') {
            $terusan        = $terusanReq ?? PengajuanTerusan::find($request->id_ref);
            $approvedUrutan = $terusan ? $terusan->urutan : 0;

            PengajuanTerusan::where('id', $request->id_ref)->update([
                'status'      => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $submission->update(['status' => 'in_review']);
            $submission->refresh();

            // ── Log approve terusan ──────────────────────────────────────────
            $tahapLabel = "terusan-{$approvedUrutan}";
            ActivityLogService::approved($submission, $tahapLabel, $request->catatan);

            // ── Log TTE placed (jika ada) ────────────────────────────────────
            if ($newPlacements->isNotEmpty()) {
                ActivityLogService::ttePlaced($submission, $tahapLabel, $newPlacements->count());
                foreach ($newPlacements as $placement) {
                    ActivityLogService::tteSigned($submission, $placement);
                }
            }

            $notif = new NotificationService();

            // Notif ke CC berikutnya atau final approver
            $notif->notifyOnTerusanApproved($submission, $approvedUrutan);

            // Notif ke pengaju — info bahwa CC ini sudah approve
            $notif->notifySubmitterOnTerusanAction(
                $submission,
                'approve',
                $user->nama_karyawan ?? $user->nrk,
            );

            return redirect()->route('data.approval.index')
                ->with('success', 'Forwarding approval has been submitted.');
        }

        // ── Kepada approved (final) ──────────────────────────────────────────
        if ($request->tahap === 'kepada') {
            $submission->update(['status' => 'approved']);

            $submission->refresh();
            if ($submission->file_current) {
                $submission->update(['file_signed' => $submission->file_current]);
            }

            $submission->refresh();

            // ── Log approve final ────────────────────────────────────────────
            ActivityLogService::approved($submission, 'kepada', $request->catatan);

            // ── Log TTE placed & signed (jika ada) ──────────────────────────
            if ($newPlacements->isNotEmpty()) {
                ActivityLogService::ttePlaced($submission, 'kepada', $newPlacements->count());
                foreach ($newPlacements as $placement) {
                    ActivityLogService::tteSigned($submission, $placement);
                }
            }

            (new NotificationService())->notifyOnFinalApproved($submission);

            return redirect()->route('data.approval.index')
                ->with('success', 'Submission has been fully approved and signed.');
        }

        return redirect()->route('data.approval.index')
            ->with('success', 'Submission has been approved.');
    }

    // =========================================================================
    // REJECT
    // =========================================================================

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

        // ── Buat snapshot PDF kondisi saat rejection ─────────────────────────
        $snapshotPath = $this->createSnapshot($submission);

        // ── Catat rejection ──────────────────────────────────────────────────
        PengajuanApproval::create([
            'id_pengajuan'  => $submission->id,
            'tahap'         => $request->tahap,
            'id_ref'        => $request->id_ref,
            'id_approver'   => $user->id,
            'aksi'          => 'reject',
            'catatan'       => $request->catatan,
            'acted_at'      => now(),
            'file_snapshot' => $snapshotPath,
        ]);

        if ($request->tahap === 'terusan') {
            $terusan = PengajuanTerusan::find($request->id_ref);

            PengajuanTerusan::where('id', $request->id_ref)->update([
                'status'      => 'rejected',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'catatan'     => $request->catatan,
            ]);

            $tahapLabel = $terusan
                ? "terusan-{$terusan->urutan}"
                : "terusan-{$request->id_ref}";
        } else {
            $tahapLabel = 'kepada';
        }

        $submission->update(['status' => 'rejected']);
        $submission->refresh();

        // ── Log rejection ────────────────────────────────────────────────────
        ActivityLogService::rejected($submission, $tahapLabel, $request->catatan);

        // ── Notifikasi rejection ke pengaju ─────────────────────────────────
        (new NotificationService())->notifyOnRejected(
            $submission,
            $request->catatan,
            $user->nama_karyawan ?? $user->nrk
        );

        return redirect()->route('data.approval.index')
            ->with('success', 'Submission has been rejected.');
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function createSnapshot(PengajuanSurat $submission): ?string
    {
        $sourceFile = $submission->file_current ?? $submission->file_original;

        if (!$sourceFile) return null;

        $fullPath = storage_path('app/' . $sourceFile);
        if (!file_exists($fullPath)) return null;

        try {
            $snapshotPath = 'submissions/snapshots/' . Str::uuid() . '.pdf';
            Storage::disk('local')->copy($sourceFile, $snapshotPath);
            return $snapshotPath;
        } catch (\Throwable $e) {
            \Log::error('Snapshot creation failed', [
                'pengajuan_id' => $submission->id,
                'source'       => $sourceFile,
                'error'        => $e->getMessage(),
            ]);
            return null;
        }
    }
}