<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Models\DataMaster\WilayahKerja;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WilayahKerjaController extends Controller
{
    private string $menu = 'master.wilker';

    public function index(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        $query = WilayahKerja::query()->orderBy('wilayah_kerja')->orderBy('area_kerja');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('wilayah_kerja', 'like', "%{$s}%")
                  ->orWhere('area_kerja', 'like', "%{$s}%")
                  ->orWhere('kode', 'like', "%{$s}%");
            });
        }

        if ($request->filled('wilayah')) {
            $query->where('wilayah_kerja', $request->wilayah);
        }

        $items    = $query->paginate(15)->withQueryString();
        $wilayahs = WilayahKerja::select('wilayah_kerja')->distinct()->orderBy('wilayah_kerja')->pluck('wilayah_kerja');

        return view('master.wilker.index', compact('items', 'wilayahs'));
    }

    public function create(): View
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $wilayahs = WilayahKerja::select('wilayah_kerja')->distinct()->orderBy('wilayah_kerja')->pluck('wilayah_kerja');

        return view('master.wilker.create', compact('wilayahs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $request->validate([
            'kode'              => ['required', 'string', 'max:20', 'unique:a04_wilayah_kerja,kode'],
            'wilayah_kerja'     => ['required', 'string', 'max:100'],
            'skt_wilayah_kerja' => ['nullable', 'string', 'max:20'],
            'area_kerja'        => ['nullable', 'string', 'max:100'],
            'skt_area_kerja'    => ['nullable', 'string', 'max:20'],
        ], $this->messages());

        WilayahKerja::create([
            'kode'              => strtoupper($request->kode),
            'wilayah_kerja'     => $request->wilayah_kerja,
            'skt_wilayah_kerja' => $request->skt_wilayah_kerja ? strtoupper($request->skt_wilayah_kerja) : null,
            'area_kerja'        => $request->area_kerja,
            'skt_area_kerja'    => $request->skt_area_kerja ? strtoupper($request->skt_area_kerja) : null,
        ]);

        return redirect()->route('master.wilker.index')
            ->with('success', 'Wilayah kerja berhasil ditambahkan.');
    }

    public function edit(WilayahKerja $wilker): View
    {
        $this->authorizeAccess($this->menu, 'update_access');

        $wilayahs = WilayahKerja::select('wilayah_kerja')->distinct()->orderBy('wilayah_kerja')->pluck('wilayah_kerja');

        return view('master.wilker.edit', compact('wilker', 'wilayahs'));
    }

    public function update(Request $request, WilayahKerja $wilker): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'update_access');

        $request->validate([
            'kode'              => ['required', 'string', 'max:20', 'unique:a04_wilayah_kerja,kode,' . $wilker->id],
            'wilayah_kerja'     => ['required', 'string', 'max:100'],
            'skt_wilayah_kerja' => ['nullable', 'string', 'max:20'],
            'area_kerja'        => ['nullable', 'string', 'max:100'],
            'skt_area_kerja'    => ['nullable', 'string', 'max:20'],
        ], $this->messages());

        $wilker->update([
            'kode'              => strtoupper($request->kode),
            'wilayah_kerja'     => $request->wilayah_kerja,
            'skt_wilayah_kerja' => $request->skt_wilayah_kerja ? strtoupper($request->skt_wilayah_kerja) : null,
            'area_kerja'        => $request->area_kerja,
            'skt_area_kerja'    => $request->skt_area_kerja ? strtoupper($request->skt_area_kerja) : null,
        ]);

        return redirect()->route('master.wilker.index')
            ->with('success', 'Data wilayah kerja berhasil diperbarui.');
    }

    public function destroy(WilayahKerja $wilker): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'delete_access');

        $wilker->delete();

        return redirect()->route('master.wilker.index')
            ->with('success', 'Wilayah kerja berhasil dihapus.');
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function authorizeAccess(string $menu, string $tipe): void
    {
        $user = auth()->user();

        if (!$user) abort(403, 'Silakan login terlebih dahulu.');
        if ($user->isAdmin()) return;
        if (!$user->hasAccess($menu, $tipe)) abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
    }

    private function messages(): array
    {
        return [
            'kode.required'          => 'Kode wajib diisi.',
            'kode.unique'            => 'Kode sudah terdaftar.',
            'wilayah_kerja.required' => 'Wilayah kerja wajib diisi.',
        ];
    }
}