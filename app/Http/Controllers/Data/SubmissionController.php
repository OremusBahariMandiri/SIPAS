<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanTerusan;
use App\Models\DataMaster\JenisDokumen;
use App\Models\DataMaster\Departemen;
use App\Models\DataMaster\Perusahaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubmissionController extends Controller
{
    private string $menu = 'data.submission';

    public function index(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        /* ── Whitelist sort ── */
        $sortable = ['nomor_surat', 'perihal', 'tanggal_surat', 'created_at'];
        $sort     = in_array($request->get('sort'), $sortable) ? $request->get('sort') : 'created_at';
        $dir      = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        /* ── Per-page ── */
        $perPage = in_array((int) $request->get('per_page'), [10, 15, 25, 50])
            ? (int) $request->get('per_page') : 15;

        /* ── Query ── */
        $query = PengajuanSurat::with(['perusahaan', 'jenisDokumen', 'kepada'])
            ->byUser(auth()->id())
            ->orderBy($sort, $dir);

        /* Filter: status */
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        /* Filter: perusahaan */
        if ($request->filled('perusahaan')) {
            $query->where('id_perusahaan', $request->perusahaan);
        }

        /* Filter: departemen (melalui jenis dokumen) */
        if ($request->filled('departemen')) {
            $query->whereHas('jenisDokumen', function ($q) use ($request) {
                $q->where('id_departemen', $request->departemen);
            });
        }

        /* Filter: rentang tanggal surat */
        if ($request->filled('date_from')) {
            $query->whereDate('tanggal_surat', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tanggal_surat', '<=', $request->date_to);
        }

        /* Filter: search (perihal + nomor_surat) */
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nomor_surat', 'like', "%{$s}%")
                    ->orWhere('perihal',   'like', "%{$s}%");
            });
        }

        /* Filter: tipe dokumen */
        if ($request->filled('dok_type')) {
            $query->whereHas('jenisDokumen', function ($q) use ($request) {
                $q->where('jenis_dokumen', 'like', '%' . $request->dok_type . '%');
            });
        }

        $items = $query->paginate($perPage)->withQueryString();

        /* ── Data untuk modal filter (dropdown) ── */
        $perusahaanList = Perusahaan::where('status', 1)->orderBy('nama')->get();
        $departemenList = Departemen::aktif()->orderBy('nama')->get();

        return view('data.submission.index', compact(
            'items',
            'perusahaanList',
            'departemenList'
        ));
    }

    public function create(): View
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $user        = auth()->user();
        $perusahaans = Perusahaan::where('status', 1)->orderBy('nama')->get();
        $kepadas     = User::where('id', '!=', $user->id)->orderBy('nrk')->get();
        $jenisDoks   = JenisDokumen::with('departemen')
            ->byDepartemen($user->id_departemen)
            ->orderBy('jenis_dokumen')
            ->get();
        $departemens = Departemen::aktif()->orderBy('nama')->get();

        return view('data.submission.create', compact(
            'perusahaans',
            'kepadas',
            'jenisDoks',
            'departemens',
            'user'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $request->validate([
            'tanggal_surat'     => ['required', 'date'],
            'id_perusahaan'     => ['required', 'exists:a01_perusahaan,id'],
            'id_kepada'         => ['required', 'exists:users,id'],
            'nomor_surat'       => ['required', 'string', 'max:100'],
            'id_jenis_dokumen'  => ['required', 'exists:a06_jenis_dokumen,id'],
            'perihal'           => ['required', 'string', 'max:255'],
            'file_dokumen'      => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'terusan'           => ['nullable', 'array'],
            'terusan.*.id_departemen' => ['required_with:terusan', 'exists:a02_departemen,id'],
            'terusan.*.require_tte'   => ['nullable', 'boolean'],
        ], $this->messages());

        // Upload PDF
        $file     = $request->file('file_dokumen');
        $filename = Str::uuid() . '.pdf';
        $path     = $file->storeAs('submissions/original', $filename, 'local');

        $pengajuan = PengajuanSurat::create([
            'tanggal_surat'    => $request->tanggal_surat,
            'id_perusahaan'    => $request->id_perusahaan,
            'id_kepada'        => $request->id_kepada,
            'nomor_surat'      => $request->nomor_surat,
            'id_jenis_dokumen' => $request->id_jenis_dokumen,
            'perihal'          => $request->perihal,
            'file_original'    => $path,
            'status'           => $request->action === 'submit' ? 'waiting' : 'draft',
            'id_user'          => auth()->id(),
        ]);

        // Simpan terusan
        if ($request->filled('terusan')) {
            foreach ($request->terusan as $urutan => $t) {
                PengajuanTerusan::create([
                    'id_pengajuan'  => $pengajuan->id,
                    'id_departemen' => $t['id_departemen'],
                    'urutan'        => $urutan + 1,
                    'require_tte'   => isset($t['require_tte']) ? 1 : 0,
                    'status'        => 'waiting',
                ]);
            }
        }

        $msg = $request->action === 'submit'
            ? 'Submission has been sent successfully.'
            : 'Submission saved as draft.';

        return redirect()->route('data.submission.index')->with('success', $msg);
    }

    public function show(PengajuanSurat $submission): View
    {
        $this->authorizeAccess($this->menu, 'index_access');
        $this->authorizeOwner($submission);

        $submission->load([
            'perusahaan',
            'jenisDokumen',
            'kepada',
            'user',
            'terusans.departemen',
            'terusans.approvedBy',
            'approvals.approver',
            'ttePlacements.tte',
        ]);

        return view('data.submission.show', compact('submission'));
    }

    public function edit(PengajuanSurat $submission): View
    {
        $this->authorizeAccess($this->menu, 'update_access');
        $this->authorizeOwner($submission);

        if (!$submission->isEditable()) {
            return redirect()->route('data.submission.show', $submission)
                ->with('error', 'This submission can no longer be edited.');
        }

        $user        = auth()->user();
        $perusahaans = Perusahaan::where('status', 1)->orderBy('nama')->get();
        $kepadas     = User::where('id', '!=', $user->id)->orderBy('nrk')->get();
        $jenisDoks   = JenisDokumen::with('departemen')
            ->byDepartemen($user->id_departemen)
            ->orderBy('jenis_dokumen')
            ->get();
        $departemens = Departemen::aktif()->orderBy('nama')->get();

        $submission->load('terusans');

        return view('data.submission.edit', compact(
            'submission',
            'perusahaans',
            'kepadas',
            'jenisDoks',
            'departemens'
        ));
    }

    public function update(Request $request, PengajuanSurat $submission): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'update_access');
        $this->authorizeOwner($submission);

        if (!$submission->isEditable()) {
            return redirect()->route('data.submission.show', $submission)
                ->with('error', 'This submission can no longer be edited.');
        }

        $request->validate([
            'tanggal_surat'    => ['required', 'date'],
            'id_perusahaan'    => ['required', 'exists:a01_perusahaan,id'],
            'id_kepada'        => ['required', 'exists:users,id'],
            'nomor_surat'      => ['required', 'string', 'max:100'],
            'id_jenis_dokumen' => ['required', 'exists:a06_jenis_dokumen,id'],
            'perihal'          => ['required', 'string', 'max:255'],
            'file_dokumen'     => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'terusan'          => ['nullable', 'array'],
            'terusan.*.id_departemen' => ['required_with:terusan', 'exists:a02_departemen,id'],
            'terusan.*.require_tte'   => ['nullable', 'boolean'],
        ], $this->messages());

        $data = [
            'tanggal_surat'    => $request->tanggal_surat,
            'id_perusahaan'    => $request->id_perusahaan,
            'id_kepada'        => $request->id_kepada,
            'nomor_surat'      => $request->nomor_surat,
            'id_jenis_dokumen' => $request->id_jenis_dokumen,
            'perihal'          => $request->perihal,
            'status'           => $request->action === 'submit' ? 'waiting' : 'draft',
        ];

        // Ganti file jika ada upload baru
        if ($request->hasFile('file_dokumen')) {
            Storage::disk('local')->delete($submission->file_original);
            $file     = $request->file('file_dokumen');
            $filename = Str::uuid() . '.pdf';
            $data['file_original'] = $file->storeAs('submissions/original', $filename, 'local');
        }

        $submission->update($data);

        // Reset dan simpan ulang terusan
        $submission->terusans()->delete();
        if ($request->filled('terusan')) {
            foreach ($request->terusan as $urutan => $t) {
                PengajuanTerusan::create([
                    'id_pengajuan'  => $submission->id,
                    'id_departemen' => $t['id_departemen'],
                    'urutan'        => $urutan + 1,
                    'require_tte'   => isset($t['require_tte']) ? 1 : 0,
                    'status'        => 'waiting',
                ]);
            }
        }

        $msg = $request->action === 'submit'
            ? 'Submission has been sent successfully.'
            : 'Draft has been updated.';

        return redirect()->route('data.submission.index')->with('success', $msg);
    }

    public function destroy(PengajuanSurat $submission): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'delete_access');
        $this->authorizeOwner($submission);

        if (!$submission->isEditable()) {
            return back()->with('error', 'Only draft submissions can be deleted.');
        }

        Storage::disk('local')->delete($submission->file_original);
        $submission->delete();

        return redirect()->route('data.submission.index')
            ->with('success', 'Submission has been deleted.');
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function authorizeAccess(string $menu, string $tipe): void
    {
        $user = auth()->user();
        if (!$user) abort(403, 'Please login first.');
        if ($user->isAdmin()) return;
        if (!$user->hasAccess($menu, $tipe)) abort(403, 'You do not have permission to access this page.');
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
            'tanggal_surat.required'    => 'Date & time is required.',
            'id_perusahaan.required'    => 'Company is required.',
            'id_kepada.required'        => 'Recipient is required.',
            'nomor_surat.required'      => 'Letter number is required.',
            'id_jenis_dokumen.required' => 'Document type is required.',
            'perihal.required'          => 'Subject is required.',
            'file_dokumen.required'     => 'Document file is required.',
            'file_dokumen.mimes'        => 'Document must be a PDF file.',
            'file_dokumen.max'          => 'Document size must not exceed 10MB.',
        ];
    }
}
