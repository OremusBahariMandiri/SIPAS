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
    public function index(Request $request): View
    {
        $user      = auth()->user();
        $activeTab = $request->get('tab', 'inbox');

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

        $perusahaanList = \App\Models\DataMaster\Perusahaan::where('status', 1)
            ->orderBy('nama')->get();

        $histories = null;

        if ($activeTab === 'history') {
            $query = PengajuanApproval::with(['pengajuan.perusahaan', 'pengajuan.jenisDokumen'])
                ->where('id_approver', $user->id);

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

            $sortable  = ['acted_at', 'aksi', 'tahap'];
            $sortCol   = in_array($request->get('sort'), $sortable)
                ? $request->get('sort') : 'acted_at';
            $sortDir   = $request->get('dir') === 'asc' ? 'asc' : 'desc';
            $perPage   = in_array((int) $request->get('per_page'), [10, 15, 25, 50])
                ? (int) $request->get('per_page') : 15;

            $histories = $query->orderBy($sortCol, $sortDir)
                ->paginate($perPage)
                ->withQueryString();
        }

        return view('data.approval.index', compact(
            'terusans', 'kepadas', 'histories', 'activeTab', 'perusahaanList'
        ));
    }

    // =========================================================================
    // REVIEW — tampilkan file_current (sudah mengandung TTE sebelumnya)
    // =========================================================================

    public function review(PengajuanSurat $submission): View|RedirectResponse
    {
        $user = auth()->user();

        $submission->load([
            'perusahaan',
            'jenisDokumen',
            'kepada',
            'user',
            'terusans.departemen',
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

        $tte = $user->tteForPerusahaan($submission->id_perusahaan);

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
            'tahap'                  => ['required', 'in:terusan,kepada'],
            'id_ref'                 => ['required', 'integer'],
            'catatan'                => ['nullable', 'string', 'max:500'],
            'placements'             => ['nullable', 'array'],
            'placements.*.halaman'   => ['required_with:placements', 'integer', 'min:1'],
            'placements.*.pos_x'     => ['required_with:placements', 'numeric'],
            'placements.*.pos_y'     => ['required_with:placements', 'numeric'],
            'placements.*.lebar'     => ['nullable', 'numeric'],
            'placements.*.tinggi'    => ['nullable', 'numeric'],
        ]);

        // ── Validasi jumlah TTE yang ditempatkan ────────────────────────────
        // Hitung requirement berdasarkan tahap
        $placedCount   = count($request->input('placements', []));
        $requiredCount = 0;

        if ($request->tahap === 'kepada') {
            $requiredCount = (int) ($submission->require_tte_kepada ?? 1);
        } elseif ($request->tahap === 'terusan') {
            $terusanReq    = PengajuanTerusan::find($request->id_ref);
            $requiredCount = $terusanReq ? (int) ($terusanReq->require_tte_count ?? 1) : 1;
        }
        if ($requiredCount < 1) $requiredCount = 1;

        if ($placedCount < $requiredCount) {
            $label = $requiredCount === 1 ? 'signature' : 'signatures';
            return back()
                ->withInput()
                ->withErrors([
                    'placements' => "You must place {$requiredCount} {$label} on the document. Only {$placedCount} placed.",
                ])
                ->with('_scroll_to_error', true);
        }

        $tte = $user->tteForPerusahaan($submission->id_perusahaan);

        // ── Simpan placements & langsung inject ke PDF ───────────────────────
        if ($request->filled('placements') && $tte) {
            $newPlacements = collect();

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

                    // Load relasi tte.perusahaan yang diperlukan untuk generate QR
                    $freshPlacements = PengajuanTtePlacement::with('tte.perusahaan')
                        ->whereIn('id', $newPlacements->pluck('id'))
                        ->get();

                    // inject ke file_current (menumpuk di atas QR sebelumnya)
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

        // ── Catat approval log ───────────────────────────────────────────────
        PengajuanApproval::create([
            'id_pengajuan' => $submission->id,
            'tahap'        => $request->tahap,
            'id_ref'       => $request->id_ref,
            'id_approver'  => $user->id,
            'aksi'         => 'approve',
            'catatan'      => $request->catatan,
            'acted_at'     => now(),
        ]);

        // ── Tahap Terusan ────────────────────────────────────────────────────
        if ($request->tahap === 'terusan') {
            $terusan        = PengajuanTerusan::find($request->id_ref);
            $approvedUrutan = $terusan ? $terusan->urutan : 0;

            PengajuanTerusan::where('id', $request->id_ref)->update([
                'status'      => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
            $submission->update(['status' => 'in_review']);

            (new \App\Services\NotificationService())
                ->notifyOnTerusanApproved($submission, $approvedUrutan);

            return redirect()->route('data.approval.index')
                ->with('success', 'Forwarding approval has been submitted.');
        }

        // ── Tahap Kepada (Final) ─────────────────────────────────────────────
        if ($request->tahap === 'kepada') {
            $submission->update(['status' => 'approved']);

            // file_signed = snapshot final = file_current saat ini
            // (sudah mengandung semua QR karena inject bertahap)
            $submission->refresh();
            if ($submission->file_current) {
                $submission->update(['file_signed' => $submission->file_current]);
            }

            (new \App\Services\NotificationService())->notifyOnFinalApproved($submission);

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

        (new \App\Services\NotificationService())->notifyOnRejected(
            $submission,
            $request->catatan,
            $user->nama_karyawan ?? $user->nrk
        );

        return redirect()->route('data.approval.index')
            ->with('success', 'Submission has been rejected.');
    }
}