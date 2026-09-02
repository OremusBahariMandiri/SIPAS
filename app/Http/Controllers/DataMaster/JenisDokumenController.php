<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Models\DataMaster\JenisDokumen;
use App\Models\DataMaster\Departemen;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JenisDokumenController extends Controller
{
    private string $menu = 'master.jenis-dokumen';

    private array $logFields = ['kode_dokumen', 'kategori_dokumen', 'jenis_dokumen', 'departemen_pemilik'];

    public function index(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        $query = JenisDokumen::with('departemen')->orderBy('kode_dokumen');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('kode_dokumen',      'like', "%{$s}%")
                    ->orWhere('kategori_dokumen', 'like', "%{$s}%")
                    ->orWhere('jenis_dokumen',    'like', "%{$s}%");
            });
        }

        if ($request->filled('departemen')) {
            $query->where('departemen_pemilik', $request->departemen);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_dokumen', $request->kategori);
        }

        $items       = $query->paginate(15)->withQueryString();
        $departemens = Departemen::aktif()->orderBy('nama')->get();
        $kategoris   = JenisDokumen::select('kategori_dokumen')
            ->distinct()
            ->orderBy('kategori_dokumen')
            ->pluck('kategori_dokumen');

        return view('master.jenis-dokumen.index', compact('items', 'departemens', 'kategoris'));
    }

    public function create(): View
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $departemens = Departemen::aktif()->orderBy('nama')->get();

        return view('master.jenis-dokumen.create', compact('departemens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $request->validate([
            'kode_dokumen'       => ['required', 'string', 'max:20', 'unique:a06_jenis_dokumen,kode_dokumen'],
            'kategori_dokumen'   => ['required', 'string', 'max:100'],
            'jenis_dokumen'      => ['required', 'string', 'max:150'],
            'departemen_pemilik' => ['required', 'exists:a02_departemen,id'],
        ], $this->messages());

        $jenisDokumen = JenisDokumen::create([
            'kode_dokumen'       => strtoupper($request->kode_dokumen),
            'kategori_dokumen'   => $request->kategori_dokumen,
            'jenis_dokumen'      => $request->jenis_dokumen,
            'departemen_pemilik' => $request->departemen_pemilik,
        ]);

        ActivityLogService::masterCreated(
            $this->menu,
            $jenisDokumen,
            "{$jenisDokumen->jenis_dokumen} ({$jenisDokumen->kode_dokumen})",
            $this->logFields,
        );

        return redirect()->route('master.jenis-dokumen.index')
            ->with('success', 'Document type successfully added.');
    }

    public function edit(JenisDokumen $jenisDokumen): View
    {
        $this->authorizeAccess($this->menu, 'update_access');

        $departemens = Departemen::aktif()->orderBy('nama')->get();

        return view('master.jenis-dokumen.edit', compact('jenisDokumen', 'departemens'));
    }

    public function update(Request $request, JenisDokumen $jenisDokumen): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'update_access');

        $request->validate([
            'kode_dokumen'       => ['required', 'string', 'max:20', 'unique:a06_jenis_dokumen,kode_dokumen,' . $jenisDokumen->id],
            'kategori_dokumen'   => ['required', 'string', 'max:100'],
            'jenis_dokumen'      => ['required', 'string', 'max:150'],
            'departemen_pemilik' => ['required', 'exists:a02_departemen,id'],
        ], $this->messages());

        $original = $jenisDokumen->toArray();

        $jenisDokumen->update([
            'kode_dokumen'       => strtoupper($request->kode_dokumen),
            'kategori_dokumen'   => $request->kategori_dokumen,
            'jenis_dokumen'      => $request->jenis_dokumen,
            'departemen_pemilik' => $request->departemen_pemilik,
        ]);

        ActivityLogService::masterUpdated(
            $this->menu,
            $jenisDokumen,
            $original,
            "{$jenisDokumen->jenis_dokumen} ({$jenisDokumen->kode_dokumen})",
            $this->logFields,
        );

        return redirect()->route('master.jenis-dokumen.index')
            ->with('success', 'Document type successfully updated.');
    }

    public function destroy(JenisDokumen $jenisDokumen): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'delete_access');

        $count = $jenisDokumen->pengajuans()->count();

        if ($count > 0) {
            return back()->with('error', "Document type \"{$jenisDokumen->jenis_dokumen}\" cannot be deleted because it is used in {$count} letter submission(s).");
        }

        ActivityLogService::masterDeleted(
            $this->menu,
            $jenisDokumen,
            "{$jenisDokumen->jenis_dokumen} ({$jenisDokumen->kode_dokumen})",
            $this->logFields,
        );

        $jenisDokumen->delete();

        return redirect()->route('master.jenis-dokumen.index')
            ->with('success', 'Document type successfully deleted.');
    }

    private function authorizeAccess(string $menu, string $tipe): void
    {
        $user = auth()->user();
        if (!$user) abort(403, 'Please log in first.');
        if ($user->isAdmin()) return;
        if (!$user->hasAccess($menu, $tipe)) abort(403, 'You do not have permission to access this page.');
    }

    private function messages(): array
    {
        return [
            'kode_dokumen.required'       => 'Document code is required.',
            'kode_dokumen.unique'         => 'Document code is already registered.',
            'kode_dokumen.max'            => 'Document code must not exceed 20 characters.',
            'kategori_dokumen.required'   => 'Document category is required.',
            'jenis_dokumen.required'      => 'Document type is required.',
            'departemen_pemilik.required' => 'Owner department is required.',
            'departemen_pemilik.exists'   => 'Department not found.',
        ];
    }
}