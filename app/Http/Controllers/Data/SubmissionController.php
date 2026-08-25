<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanTerusan;
use App\Models\Data\PengajuanTtePlacement;
use App\Models\Data\PengajuanApproval;
use App\Models\DataMaster\JenisDokumen;
use App\Models\DataMaster\Departemen;
use App\Models\DataMaster\Perusahaan;
use App\Models\DataMaster\SifatSurat;
use App\Models\User;
use App\Services\TteService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SubmissionController extends Controller
{
    private string $menu = 'data.submission';

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        $user    = auth()->user();
        $isAdmin = $user->isAdmin();

        $sortable = ['nomor_surat', 'perihal', 'tanggal_surat', 'created_at'];
        $sort     = in_array($request->get('sort'), $sortable) ? $request->get('sort') : 'created_at';
        $dir      = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $perPage = in_array((int) $request->get('per_page'), [10, 15, 25, 50])
            ? (int) $request->get('per_page') : 15;

        $query = PengajuanSurat::with(['perusahaan', 'jenisDokumen', 'kepada', 'sifatSurat', 'user'])
            ->orderBy($sort, $dir);

        if (!$isAdmin) {
            $query->byUser($user->id);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('perusahaan')) {
            $query->where('id_perusahaan', $request->perusahaan);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('tanggal_surat', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tanggal_surat', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nomor_surat', 'like', "%{$s}%")
                    ->orWhere('perihal', 'like', "%{$s}%");
            });
        }
        if ($request->filled('dok_type')) {
            $query->whereHas('jenisDokumen', function ($q) use ($request) {
                $q->where('jenis_dokumen', 'like', '%' . $request->dok_type . '%');
            });
        }

        $items          = $query->paginate($perPage)->withQueryString();
        $perusahaanList = Perusahaan::where('status', 1)->orderBy('nama')->get();

        return view('data.submission.index', compact(
            'items',
            'perusahaanList',
            'isAdmin'
        ));
    }

    // =========================================================================
    // CREATE
    // =========================================================================

    public function create(): View
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $user        = auth()->user();
        $perusahaans = Perusahaan::where('status', 1)->orderBy('nama')->get();

        $kepadas = User::where('id', '!=', $user->id)
            ->where('is_admin', '!=', 1)
            ->orderBy('nrk')
            ->get();

        $jenisDoks = JenisDokumen::with('departemen')
            ->byDepartemen($user->id_departemen)
            ->orderBy('jenis_dokumen')
            ->get();

        $sifatSurats = SifatSurat::aktif()->orderBy('nama')->get();
        $tteMap      = $this->buildTteMap($user, $perusahaans);

        return view('data.submission.create', compact(
            'perusahaans',
            'kepadas',
            'jenisDoks',
            'user',
            'sifatSurats',
            'tteMap'
        ));
    }

    // =========================================================================
    // TEMP UPLOAD
    // =========================================================================

    public function tempUpload(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $request->validate([
            'file_dokumen' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $key          = Str::random(40);
        $tmpPath      = 'submissions/tmp/' . $key . '.pdf';
        $originalName = $request->file('file_dokumen')->getClientOriginalName();

        $request->file('file_dokumen')->storeAs('submissions/tmp', $key . '.pdf', 'local');

        session([
            'tmp_pdf_' . $key      => $tmpPath,
            'tmp_filename_' . $key => $originalName,
        ]);

        return response()->json([
            'key'         => $key,
            'preview_url' => route('data.submission.tempPreview', $key),
        ]);
    }

    // =========================================================================
    // TEMP PREVIEW
    // =========================================================================

    public function tempPreview(string $key): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        if (!preg_match('/^[a-zA-Z0-9]{40}$/', $key)) abort(404);

        $tmpPath = session('tmp_pdf_' . $key);
        if (!$tmpPath) abort(404);

        $fullPath = storage_path('app/' . $tmpPath);
        if (!file_exists($fullPath)) abort(404);

        return response()->streamDownload(function () use ($fullPath) {
            readfile($fullPath);
        }, 'preview.pdf', [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview.pdf"',
            'Cache-Control'       => 'no-store',
        ]);
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $isDraft = $request->action !== 'submit';

        $hasTmpFile = $request->filled('tmp_key')
            && session('tmp_pdf_' . $request->tmp_key)
            && file_exists(storage_path('app/' . session('tmp_pdf_' . $request->tmp_key)));

        if ($isDraft) {
            $request->validate([
                'nomor_surat'                  => ['required', 'string', 'max:100'],
                'tanggal_surat'                => ['nullable', 'date'],
                'id_perusahaan'                => ['nullable', 'exists:a01_perusahaan,id'],
                'id_kepada'                    => ['nullable', 'exists:users,id'],
                'id_jenis_dokumen'             => ['nullable', 'exists:a06_jenis_dokumen,id'],
                'id_sifat_surat'               => ['nullable', 'exists:a07_sifat_surat,id'],
                'perihal'                      => ['nullable', 'string', 'max:255'],
                'file_dokumen'                 => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
                'require_tte_kepada'           => ['nullable', 'integer', 'min:0', 'max:10'],
                'terusan'                      => ['nullable', 'array'],
                'terusan.*.id_user'            => ['nullable', 'exists:users,id'],
                'terusan.*.require_tte'        => ['nullable', 'boolean'],
                'terusan.*.require_tte_count'  => ['nullable', 'integer', 'min:0', 'max:10'],
                'pengaju_placements'           => ['nullable', 'array'],
                'pengaju_placements.*.halaman' => ['nullable', 'integer', 'min:1'],
                'pengaju_placements.*.pos_x'   => ['nullable', 'numeric'],
                'pengaju_placements.*.pos_y'   => ['nullable', 'numeric'],
                'pengaju_placements.*.lebar'   => ['nullable', 'numeric'],
                'pengaju_placements.*.tinggi'  => ['nullable', 'numeric'],
            ], $this->messages());
        } else {
            $fileRule = $hasTmpFile
                ? ['nullable', 'file', 'mimes:pdf', 'max:10240']
                : ['required', 'file', 'mimes:pdf', 'max:10240'];

            $request->validate([
                'tanggal_surat'                => ['required', 'date'],
                'id_perusahaan'                => ['required', 'exists:a01_perusahaan,id'],
                'id_kepada'                    => ['required', 'exists:users,id'],
                'nomor_surat'                  => ['required', 'string', 'max:100'],
                'id_jenis_dokumen'             => ['required', 'exists:a06_jenis_dokumen,id'],
                'id_sifat_surat'               => ['required', 'exists:a07_sifat_surat,id'],
                'perihal'                      => ['required', 'string', 'max:255'],
                'file_dokumen'                 => $fileRule,
                'require_tte_kepada'           => ['nullable', 'integer', 'min:0', 'max:10'],
                'terusan'                      => ['nullable', 'array'],
                'terusan.*.id_user'            => ['required_with:terusan', 'exists:users,id'],
                'terusan.*.require_tte'        => ['nullable', 'boolean'],
                'terusan.*.require_tte_count'  => ['nullable', 'integer', 'min:0', 'max:10'],
                'pengaju_placements'           => ['nullable', 'array'],
                'pengaju_placements.*.halaman' => ['required_with:pengaju_placements', 'integer', 'min:1'],
                'pengaju_placements.*.pos_x'   => ['required_with:pengaju_placements', 'numeric'],
                'pengaju_placements.*.pos_y'   => ['required_with:pengaju_placements', 'numeric'],
                'pengaju_placements.*.lebar'   => ['nullable', 'numeric'],
                'pengaju_placements.*.tinggi'  => ['nullable', 'numeric'],
            ], $this->messages());
        }

        // Validasi CC
        if ($request->filled('terusan') && $request->filled('id_kepada')) {
            $ccUsers = collect($request->terusan)
                ->pluck('id_user')
                ->filter()
                ->values();

            if ($ccUsers->contains((string) $request->id_kepada)) {
                return back()->withInput()->withErrors([
                    'terusan' => 'Carbon Copy (CC) user cannot be the same as the recipient (final approver).',
                ]);
            }

            if ($ccUsers->unique()->count() !== $ccUsers->count()) {
                return back()->withInput()->withErrors([
                    'terusan' => 'Each Carbon Copy (CC) user must be unique.',
                ]);
            }
        }

        // ── Simpan file ──────────────────────────────────────────────────────
        if ($request->hasFile('file_dokumen')) {
            $filename = Str::uuid() . '.pdf';
            $path     = $request->file('file_dokumen')
                ->storeAs('submissions/original', $filename, 'local');
        } elseif ($hasTmpFile) {
            $tmpPath  = session('tmp_pdf_' . $request->tmp_key);
            $filename = Str::uuid() . '.pdf';
            $destPath = 'submissions/original/' . $filename;
            Storage::disk('local')->copy($tmpPath, $destPath);
            $path = $destPath;
        } else {
            $path = null;
        }

        // ── Buat pengajuan ───────────────────────────────────────────────────
        $ttePengajuCount = count($request->input('pengaju_placements', []));

        $pengajuan = PengajuanSurat::create([
            'tanggal_surat'       => $request->tanggal_surat,
            'id_perusahaan'       => $request->id_perusahaan,
            'id_kepada'           => $request->id_kepada,
            'nomor_surat'         => $request->nomor_surat,
            'id_jenis_dokumen'    => $request->id_jenis_dokumen,
            'id_sifat_surat'      => $request->id_sifat_surat,
            'perihal'             => $request->perihal,
            'file_original'       => $path,
            'file_current'        => null,
            'status'              => $isDraft ? 'draft' : 'waiting',
            'id_user'             => auth()->id(),
            'require_tte_pengaju' => $ttePengajuCount,
            'require_tte_kepada'  => (int) $request->input('require_tte_kepada', 1),
        ]);

        // ── Simpan terusan ───────────────────────────────────────────────────
        if ($request->filled('terusan')) {
            foreach ($request->terusan as $urutan => $t) {
                if (empty($t['id_user'])) continue;

                $targetUser = User::where('id', $t['id_user'])
                    ->where('is_admin', '!=', 1)
                    ->first();

                if (!$targetUser) continue;

                $requireTte = isset($t['require_tte']) ? 1 : 0;

                PengajuanTerusan::create([
                    'id_pengajuan'      => $pengajuan->id,
                    'id_user'           => $targetUser->id,
                    'urutan'            => $urutan + 1,
                    'require_tte'       => $requireTte,
                    'require_tte_count' => $requireTte ? (int) ($t['require_tte_count'] ?? 1) : 0,
                    'status'            => 'waiting',
                ]);
            }
        }

        // ── TTE Pengaju ──────────────────────────────────────────────────────
        if ($path && $request->filled('pengaju_placements')) {
            $user = auth()->user();
            $tte  = $user->tteForPerusahaan($request->id_perusahaan);

            if ($tte && $tte->isValid()) {
                $newPlacements = collect();

                foreach ($request->pengaju_placements as $pl) {
                    if (!isset($pl['pos_x'], $pl['pos_y'])) continue;

                    $placement = PengajuanTtePlacement::create([
                        'id_pengajuan' => $pengajuan->id,
                        'id_tte'       => $tte->id,
                        'tahap'        => 'pengaju',
                        'id_ref'       => 0,
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
                        $pengajuan->load('ttePlacements.tte.perusahaan');
                        $freshPlacements = PengajuanTtePlacement::with('tte.perusahaan')
                            ->whereIn('id', $newPlacements->pluck('id'))
                            ->get();

                        (new TteService())->injectStageTteToPdf($pengajuan, $freshPlacements);
                    } catch (\Throwable $e) {
                        \Log::error('TTE inject pengaju failed on store', [
                            'pengajuan_id' => $pengajuan->id,
                            'error'        => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        // ── Bersihkan tmp file ───────────────────────────────────────────────
        if ($request->filled('tmp_key')) {
            $tmpKey  = $request->tmp_key;
            $tmpPath = session('tmp_pdf_' . $tmpKey);
            if ($tmpPath) {
                Storage::disk('local')->delete($tmpPath);
                session()->forget('tmp_pdf_' . $tmpKey);
                session()->forget('tmp_filename_' . $tmpKey);
            }
        }

        // ── Notifikasi ───────────────────────────────────────────────────────
        if (!$isDraft) {
            (new \App\Services\NotificationService())->notifyOnSubmit($pengajuan);
        }

        $msg = $isDraft
            ? 'Submission saved as draft.'
            : 'Submission has been sent successfully.';

        return redirect()->route('data.submission.index')->with('success', $msg);
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show(PengajuanSurat $submission): View
    {
        $this->authorizeAccess($this->menu, 'index_access');
        $this->authorizeOwner($submission);

        $submission->load([
            'perusahaan',
            'jenisDokumen',
            'sifatSurat',
            'kepada',
            'user',
            'terusans.user',
            'terusans.approvedBy',
            'approvals.approver',
            'ttePlacements.tte',
        ]);

        return view('data.submission.show', compact('submission'));
    }

    // =========================================================================
    // EDIT
    // =========================================================================

    public function edit(PengajuanSurat $submission): View|RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'update_access');
        $this->authorizeOwner($submission);

        if (!$submission->isEditable()) {
            return redirect()->route('data.submission.show', $submission)
                ->with('error', 'This submission can no longer be edited.');
        }

        $user        = auth()->user();
        $perusahaans = Perusahaan::where('status', 1)->orderBy('nama')->get();

        // Exclude diri sendiri dan admin — sama persis dengan create()
        $kepadas = User::where('id', '!=', $user->id)
            ->where('is_admin', '!=', 1)
            ->orderBy('nrk')
            ->get();

        $jenisDoks = JenisDokumen::with('departemen')
            ->byDepartemen($user->id_departemen)
            ->orderBy('jenis_dokumen')
            ->get();

        $sifatSurats = SifatSurat::aktif()->orderBy('nama')->get();
        $tteMap      = $this->buildTteMap($user, $perusahaans);

        // Load terusan beserta relasi user-nya untuk di-restore di blade
        $submission->load('terusans.user');

        return view('data.submission.edit', compact(
            'submission',
            'perusahaans',
            'kepadas',
            'jenisDoks',
            'sifatSurats',
            'tteMap'
        ));
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, PengajuanSurat $submission): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'update_access');
        $this->authorizeOwner($submission);

        if (!$submission->isEditable()) {
            return redirect()->route('data.submission.show', $submission)
                ->with('error', 'This submission can no longer be edited.');
        }

        $isDraft = $request->action !== 'submit';

        // Simpan status awal SEBELUM update untuk keperluan pesan akhir
        $statusBefore = $submission->status;

        $hasTmpFile = $request->filled('tmp_key')
            && session('tmp_pdf_' . $request->tmp_key)
            && file_exists(storage_path('app/' . session('tmp_pdf_' . $request->tmp_key)));

        if ($isDraft) {
            $request->validate([
                'nomor_surat'                  => ['required', 'string', 'max:100'],
                'tanggal_surat'                => ['nullable', 'date'],
                'id_perusahaan'                => ['nullable', 'exists:a01_perusahaan,id'],
                'id_kepada'                    => ['nullable', 'exists:users,id'],
                'id_jenis_dokumen'             => ['nullable', 'exists:a06_jenis_dokumen,id'],
                'id_sifat_surat'               => ['nullable', 'exists:a07_sifat_surat,id'],
                'perihal'                      => ['nullable', 'string', 'max:255'],
                'file_dokumen'                 => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
                'require_tte_kepada'           => ['nullable', 'integer', 'min:0', 'max:10'],
                'terusan'                      => ['nullable', 'array'],
                'terusan.*.id_user'            => ['nullable', 'exists:users,id'],
                'terusan.*.require_tte'        => ['nullable', 'boolean'],
                'terusan.*.require_tte_count'  => ['nullable', 'integer', 'min:0', 'max:10'],
                'pengaju_placements'           => ['nullable', 'array'],
                'pengaju_placements.*.halaman' => ['nullable', 'integer', 'min:1'],
                'pengaju_placements.*.pos_x'   => ['nullable', 'numeric'],
                'pengaju_placements.*.pos_y'   => ['nullable', 'numeric'],
                'pengaju_placements.*.lebar'   => ['nullable', 'numeric'],
                'pengaju_placements.*.tinggi'  => ['nullable', 'numeric'],
            ], $this->messages());
        } else {
            // Untuk submit: jika rejected wajib upload file baru,
            // jika draft biasa cukup gunakan file yang sudah ada
            if ($submission->status === 'rejected') {
                $fileRule = ($request->hasFile('file_dokumen') || $hasTmpFile)
                    ? ['nullable', 'file', 'mimes:pdf', 'max:10240']
                    : ['required', 'file', 'mimes:pdf', 'max:10240'];
            } else {
                $fileRule = ['nullable', 'file', 'mimes:pdf', 'max:10240'];
            }

            $request->validate([
                'tanggal_surat'                => ['required', 'date'],
                'id_perusahaan'                => ['required', 'exists:a01_perusahaan,id'],
                'id_kepada'                    => ['required', 'exists:users,id'],
                'nomor_surat'                  => ['required', 'string', 'max:100'],
                'id_jenis_dokumen'             => ['required', 'exists:a06_jenis_dokumen,id'],
                'id_sifat_surat'               => ['required', 'exists:a07_sifat_surat,id'],
                'perihal'                      => ['required', 'string', 'max:255'],
                'file_dokumen'                 => $fileRule,
                'require_tte_kepada'           => ['nullable', 'integer', 'min:0', 'max:10'],
                'terusan'                      => ['nullable', 'array'],
                'terusan.*.id_user'            => ['required_with:terusan', 'exists:users,id'],
                'terusan.*.require_tte'        => ['nullable', 'boolean'],
                'terusan.*.require_tte_count'  => ['nullable', 'integer', 'min:0', 'max:10'],
                'pengaju_placements'           => ['nullable', 'array'],
                'pengaju_placements.*.halaman' => ['required_with:pengaju_placements', 'integer', 'min:1'],
                'pengaju_placements.*.pos_x'   => ['required_with:pengaju_placements', 'numeric'],
                'pengaju_placements.*.pos_y'   => ['required_with:pengaju_placements', 'numeric'],
                'pengaju_placements.*.lebar'   => ['nullable', 'numeric'],
                'pengaju_placements.*.tinggi'  => ['nullable', 'numeric'],
            ], array_merge($this->messages(), [
                'file_dokumen.required' => 'A new document file is required when resubmitting a rejected submission.',
            ]));
        }

        // Validasi CC
        if ($request->filled('terusan') && $request->filled('id_kepada')) {
            $ccUsers = collect($request->terusan)
                ->pluck('id_user')
                ->filter()
                ->values();

            if ($ccUsers->contains((string) $request->id_kepada)) {
                return back()->withInput()->withErrors([
                    'terusan' => 'Carbon Copy (CC) user cannot be the same as the recipient (final approver).',
                ]);
            }

            if ($ccUsers->unique()->count() !== $ccUsers->count()) {
                return back()->withInput()->withErrors([
                    'terusan' => 'Each Carbon Copy (CC) user must be unique.',
                ]);
            }
        }

        // ── Siapkan data update ──────────────────────────────────────────────
        $data = [
            'tanggal_surat'       => $request->tanggal_surat,
            'id_perusahaan'       => $request->id_perusahaan,
            'id_kepada'           => $request->id_kepada,
            'nomor_surat'         => $request->nomor_surat,
            'id_jenis_dokumen'    => $request->id_jenis_dokumen,
            'id_sifat_surat'      => $request->id_sifat_surat,
            'perihal'             => $request->perihal,
            'status'              => $isDraft ? 'draft' : 'waiting',
            'require_tte_pengaju' => count($request->input('pengaju_placements', [])),
            'require_tte_kepada'  => (int) $request->input('require_tte_kepada', 1),
        ];

        // Reset rejection_reason saat resubmit
        if (!$isDraft && $submission->status === 'rejected') {
            $data['rejection_reason'] = null;
        }

        // ── Ganti file jika ada upload baru ─────────────────────────────────
        if ($request->hasFile('file_dokumen') || $hasTmpFile) {
            // Hapus file lama
            if ($submission->file_original) {
                Storage::disk('local')->delete($submission->file_original);
            }
            if ($submission->file_current) {
                Storage::disk('local')->delete($submission->file_current);
            }

            $filename = Str::uuid() . '.pdf';

            if ($request->hasFile('file_dokumen')) {
                $data['file_original'] = $request->file('file_dokumen')
                    ->storeAs('submissions/original', $filename, 'local');
            } else {
                $tmpPath  = session('tmp_pdf_' . $request->tmp_key);
                $destPath = 'submissions/original/' . $filename;
                Storage::disk('local')->copy($tmpPath, $destPath);
                $data['file_original'] = $destPath;
            }

            $data['file_current'] = null;
        }

        $submission->update($data);

        // ── Rebuild terusan ──────────────────────────────────────────────────
        $submission->terusans()->delete();

        if ($request->filled('terusan')) {
            foreach ($request->terusan as $urutan => $t) {
                if (empty($t['id_user'])) continue;

                $targetUser = User::where('id', $t['id_user'])
                    ->where('is_admin', '!=', 1)
                    ->first();

                if (!$targetUser) continue;

                $requireTte = isset($t['require_tte']) ? 1 : 0;

                PengajuanTerusan::create([
                    'id_pengajuan'      => $submission->id,
                    'id_user'           => $targetUser->id,
                    'urutan'            => $urutan + 1,
                    'require_tte'       => $requireTte,
                    'require_tte_count' => $requireTte ? (int) ($t['require_tte_count'] ?? 1) : 0,
                    'status'            => 'waiting',
                ]);
            }
        }

        // ── Rebuild TTE placements pengaju ───────────────────────────────────
        $submission->ttePlacements()->where('tahap', 'pengaju')->delete();

        // Gunakan file_original dari $data jika baru diupload, fallback ke existing
        $currentFilePath = $data['file_original'] ?? $submission->file_original;

        if ($currentFilePath && $request->filled('pengaju_placements')) {
            $user = auth()->user();
            $tte  = $user->tteForPerusahaan($submission->id_perusahaan);

            if ($tte && $tte->isValid()) {
                $newPlacements = collect();

                foreach ($request->pengaju_placements as $pl) {
                    if (!isset($pl['pos_x'], $pl['pos_y'])) continue;

                    $placement = PengajuanTtePlacement::create([
                        'id_pengajuan' => $submission->id,
                        'id_tte'       => $tte->id,
                        'tahap'        => 'pengaju',
                        'id_ref'       => 0,
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
                        \Log::error('TTE inject pengaju failed on update', [
                            'pengajuan_id' => $submission->id,
                            'error'        => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        // ── Bersihkan tmp file ───────────────────────────────────────────────
        if ($request->filled('tmp_key')) {
            $tmpPath = session('tmp_pdf_' . $request->tmp_key);
            if ($tmpPath) {
                Storage::disk('local')->delete($tmpPath);
                session()->forget('tmp_pdf_' . $request->tmp_key);
                session()->forget('tmp_filename_' . $request->tmp_key);
            }
        }

        // ── Notifikasi ───────────────────────────────────────────────────────
        if (!$isDraft) {
            $submission->refresh();
            (new \App\Services\NotificationService())->notifyOnSubmit($submission);
        }

        // ── Pesan sukses ─────────────────────────────────────────────────────
        // Gunakan $statusBefore (disimpan sebelum update) bukan wasChanged()
        // karena refresh() akan mereset dirty state model
        $msg = match (true) {
            !$isDraft && $statusBefore === 'rejected' => 'Submission has been resubmitted successfully.',
            !$isDraft                                 => 'Submission has been sent successfully.',
            default                                   => 'Draft has been updated.',
        };

        return redirect()->route('data.submission.index')->with('success', $msg);
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(PengajuanSurat $submission): RedirectResponse
    {
        $user    = auth()->user();
        $isAdmin = $user->isAdmin();

        if ($isAdmin) {
            // Admin: bypass semua check
        } else {
            $this->authorizeAccess($this->menu, 'delete_access');
            $this->authorizeOwner($submission);

            if (!$submission->isEditable()) {
                return back()->with('error', 'Only draft or rejected submissions can be deleted.');
            }
        }

        if ($submission->file_original) {
            Storage::disk('local')->delete($submission->file_original);
        }
        if ($submission->file_current) {
            Storage::disk('local')->delete($submission->file_current);
        }
        if ($submission->file_signed) {
            Storage::disk('local')->delete($submission->file_signed);
        }

        $submission->ttePlacements()->delete();
        $submission->terusans()->delete();
        $submission->approvals()->delete();
        $submission->delete();

        return redirect()->route('data.submission.index')
            ->with('success', 'Submission has been deleted.');
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function buildTteMap(User $user, $perusahaans): array
    {
        $map = [];
        foreach ($perusahaans as $p) {
            $tte = $user->tteForPerusahaan($p->id);
            if ($tte) {
                $map[$p->id] = [
                    'nama'        => $tte->nama ?? $user->nrk,
                    'valid_until' => $tte->valid_until
                        ? Carbon::parse($tte->valid_until)->format('d/m/Y')
                        : null,
                    'valid'       => $tte->isValid(),
                ];
            }
        }
        return $map;
    }

    private function authorizeAccess(string $menu, string $tipe): void
    {
        $user = auth()->user();
        if (!$user) abort(403, 'Please login first.');
        if ($user->isAdmin()) return;
        if (!$user->hasAccess($menu, $tipe)) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    private function authorizeOwner(PengajuanSurat $submission): void
    {
        if ($submission->id_user !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'You do not have permission to access this submission.');
        }
    }

    private function messages(): array
    {
        return [
            'tanggal_surat.required'     => 'Date & time is required.',
            'id_perusahaan.required'     => 'Company is required.',
            'id_kepada.required'         => 'Recipient is required.',
            'nomor_surat.required'       => 'Letter number is required.',
            'id_jenis_dokumen.required'  => 'Document type is required.',
            'id_sifat_surat.required'    => 'Sifat surat is required.',
            'perihal.required'           => 'Subject is required.',
            'file_dokumen.required'      => 'Document file is required.',
            'file_dokumen.mimes'         => 'Document must be a PDF file.',
            'file_dokumen.max'           => 'Document size must not exceed 10 MB.',
            'require_tte_kepada.integer' => 'TTE recipient count must be a number.',
        ];
    }
}