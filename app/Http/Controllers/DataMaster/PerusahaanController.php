<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Models\DataMaster\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class PerusahaanController extends Controller
{
    private string $menu = 'master.perusahaan';

    public function index(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        $query = Perusahaan::query()->orderBy('nama');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'like', "%{$s}%")
                  ->orWhere('singkatan', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(15)->withQueryString();

        return view('master.perusahaan.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeAccess($this->menu, 'create_access');

        return view('master.perusahaan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $request->validate([
            'nama'      => ['required', 'string', 'max:100', 'unique:a01_perusahaan,nama'],
            'singkatan' => ['required', 'string', 'max:20'],
            'status'    => ['required', 'in:1,0'],
            'logo'      => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ], $this->messages());

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        Perusahaan::create([
            'nama'      => $request->nama,
            'singkatan' => strtoupper($request->singkatan),
            'status'    => $request->status,
            'logo'      => $logoPath,
        ]);

        return redirect()->route('master.perusahaan.index')
            ->with('success', 'Company successfully added.');
    }

    public function edit(Perusahaan $perusahaan): View
    {
        $this->authorizeAccess($this->menu, 'update_access');

        return view('master.perusahaan.edit', compact('perusahaan'));
    }

    public function update(Request $request, Perusahaan $perusahaan): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'update_access');

        $request->validate([
            'nama'      => ['required', 'string', 'max:100', 'unique:a01_perusahaan,nama,' . $perusahaan->id],
            'singkatan' => ['required', 'string', 'max:20'],
            'status'    => ['required', 'in:1,0'],
            'logo'      => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ], $this->messages());

        $data = [
            'nama'      => $request->nama,
            'singkatan' => strtoupper($request->singkatan),
            'status'    => $request->status,
        ];

        if ($request->hasFile('logo')) {
            if ($perusahaan->logo) {
                Storage::disk('public')->delete($perusahaan->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        if ($request->boolean('hapus_logo') && $perusahaan->logo) {
            Storage::disk('public')->delete($perusahaan->logo);
            $data['logo'] = null;
        }

        $perusahaan->update($data);

        return redirect()->route('master.perusahaan.index')
            ->with('success', 'Company data successfully updated.');
    }

    public function destroy(Perusahaan $perusahaan): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'delete_access');

        if ($perusahaan->users()->exists()) {
            return back()->with('error', 'Company cannot be deleted because it still has registered users.');
        }

        if ($perusahaan->logo) {
            Storage::disk('public')->delete($perusahaan->logo);
        }

        $perusahaan->delete();

        return redirect()->route('master.perusahaan.index')
            ->with('success', 'Company successfully deleted.');
    }

    // ─── Helpers ─────────────────────────────────────────────

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
            'nama.required'      => 'Company name is required.',
            'nama.unique'        => 'Company name is already registered.',
            'singkatan.required' => 'Abbreviation is required.',
            'status.required'    => 'Status is required.',
            'logo.image'         => 'Logo must be an image file.',
            'logo.mimes'         => 'Logo format must be PNG, JPG, or JPEG.',
            'logo.max'           => 'Logo size must not exceed 2MB.',
        ];
    }
}