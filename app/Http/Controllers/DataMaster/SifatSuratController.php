<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Models\DataMaster\SifatSurat;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SifatSuratController extends Controller
{
    private string $menu = 'master.sifat-surat';

    public function index(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        $query = SifatSurat::query()->orderBy('nama');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', "%{$s}%")
                  ->orWhere('kode', 'like', "%{$s}%")
                  ->orWhere('keterangan', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(15)->withQueryString();

        return view('master.sifat-surat.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeAccess($this->menu, 'create_access');

        return view('master.sifat-surat.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $request->validate([
            'kode'        => ['required', 'string', 'max:20', 'unique:a07_sifat_surat,kode'],
            'nama'        => ['required', 'string', 'max:100'],
            'keterangan'  => ['nullable', 'string', 'max:255'],
            'status'      => ['required', 'in:1,0'],
        ], $this->messages());

        SifatSurat::create([
            'kode'       => strtoupper($request->kode),
            'nama'       => $request->nama,
            'keterangan' => $request->keterangan,
            'status'     => $request->status,
        ]);

        return redirect()->route('master.sifat-surat.index')
            ->with('success', 'Sifat surat berhasil ditambahkan.');
    }

    public function edit(SifatSurat $sifatSurat): View
    {
        $this->authorizeAccess($this->menu, 'update_access');

        return view('master.sifat-surat.edit', compact('sifatSurat'));
    }

    public function update(Request $request, SifatSurat $sifatSurat): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'update_access');

        $request->validate([
            'kode'       => ['required', 'string', 'max:20', 'unique:a07_sifat_surat,kode,' . $sifatSurat->id],
            'nama'       => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'status'     => ['required', 'in:1,0'],
        ], $this->messages());

        $sifatSurat->update([
            'kode'       => strtoupper($request->kode),
            'nama'       => $request->nama,
            'keterangan' => $request->keterangan,
            'status'     => $request->status,
        ]);

        return redirect()->route('master.sifat-surat.index')
            ->with('success', 'Data sifat surat berhasil diperbarui.');
    }

    public function destroy(SifatSurat $sifatSurat): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'delete_access');

        if ($sifatSurat->pengajuans()->exists()) {
            return back()->with('error', 'Sifat surat tidak dapat dihapus karena masih digunakan pada pengajuan surat.');
        }

        $sifatSurat->delete();

        return redirect()->route('master.sifat-surat.index')
            ->with('success', 'Sifat surat berhasil dihapus.');
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function authorizeAccess(string $menu, string $tipe): void
    {
        $user = auth()->user();

        if (!$user) abort(403, 'Silakan login terlebih dahulu.');
        if ($user->isAdmin()) return;
        if (!$user->hasAccess($menu, $tipe)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }
    }

    private function messages(): array
    {
        return [
            'kode.required'   => 'Kode sifat surat wajib diisi.',
            'kode.unique'     => 'Kode sifat surat sudah terdaftar.',
            'nama.required'   => 'Nama sifat surat wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
        ];
    }
}