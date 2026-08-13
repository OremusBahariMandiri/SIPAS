<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Models\DataMaster\Departemen;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartemenController extends Controller
{
    private string $menu = 'master.departemen';

    public function index(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        $query = Departemen::query()->orderBy('nama');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', "%{$s}%")
                  ->orWhere('kode', 'like', "%{$s}%")
                  ->orWhere('singkatan', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(15)->withQueryString();

        return view('master.departemen.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeAccess($this->menu, 'create_access');

        return view('master.departemen.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $request->validate([
            'kode'      => ['required', 'string', 'max:20', 'unique:a02_departemen,kode'],
            'nama'      => ['required', 'string', 'max:100'],
            'singkatan' => ['nullable', 'string', 'max:20'],
            'status'    => ['required', 'in:1,0'],
        ], $this->messages());

        Departemen::create([
            'kode'      => strtoupper($request->kode),
            'nama'      => $request->nama,
            'singkatan' => $request->singkatan ? strtoupper($request->singkatan) : null,
            'status'    => $request->status,
        ]);

        return redirect()->route('master.departemen.index')
            ->with('success', 'Departemen berhasil ditambahkan.');
    }

    public function edit(Departemen $departemen): View
    {
        $this->authorizeAccess($this->menu, 'update_access');

        return view('master.departemen.edit', compact('departemen'));
    }

    public function update(Request $request, Departemen $departemen): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'update_access');

        $request->validate([
            'kode'      => ['required', 'string', 'max:20', 'unique:a02_departemen,kode,' . $departemen->id],
            'nama'      => ['required', 'string', 'max:100'],
            'singkatan' => ['nullable', 'string', 'max:20'],
            'status'    => ['required', 'in:1,0'],
        ], $this->messages());

        $departemen->update([
            'kode'      => strtoupper($request->kode),
            'nama'      => $request->nama,
            'singkatan' => $request->singkatan ? strtoupper($request->singkatan) : null,
            'status'    => $request->status,
        ]);

        return redirect()->route('master.departemen.index')
            ->with('success', 'Data departemen berhasil diperbarui.');
    }

    public function destroy(Departemen $departemen): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'delete_access');

        if ($departemen->users()->exists()) {
            return back()->with('error', 'Departemen tidak dapat dihapus karena masih memiliki pengguna terdaftar.');
        }

        $departemen->delete();

        return redirect()->route('master.departemen.index')
            ->with('success', 'Departemen berhasil dihapus.');
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
            'kode.required'   => 'Kode departemen wajib diisi.',
            'kode.unique'     => 'Kode departemen sudah terdaftar.',
            'nama.required'   => 'Nama departemen wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
        ];
    }
}