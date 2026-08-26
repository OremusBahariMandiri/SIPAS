<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Models\DataMaster\Jabatan;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JabatanController extends Controller
{
    private string $menu = 'master.jabatan';

    private array $logFields = ['kode', 'nama', 'singkatan', 'status'];

    public function index(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        $query = Jabatan::query()->orderBy('nama');

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

        return view('master.jabatan.index', compact('items'));
    }

    public function create(): View
    {
        $this->authorizeAccess($this->menu, 'create_access');

        return view('master.jabatan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $request->validate([
            'kode'      => ['required', 'string', 'max:20', 'unique:a03_jabatan,kode'],
            'nama'      => ['required', 'string', 'max:100'],
            'singkatan' => ['nullable', 'string', 'max:20'],
            'status'    => ['required', 'in:1,0'],
        ], $this->messages());

        $jabatan = Jabatan::create([
            'kode'      => strtoupper($request->kode),
            'nama'      => $request->nama,
            'singkatan' => $request->singkatan ? strtoupper($request->singkatan) : null,
            'status'    => $request->status,
        ]);

        ActivityLogService::masterCreated(
            $this->menu,
            $jabatan,
            "{$jabatan->nama} ({$jabatan->kode})",
            $this->logFields,
        );

        return redirect()->route('master.jabatan.index')
            ->with('success', 'Position successfully added.');
    }

    public function edit(Jabatan $jabatan): View
    {
        $this->authorizeAccess($this->menu, 'update_access');

        return view('master.jabatan.edit', compact('jabatan'));
    }

    public function update(Request $request, Jabatan $jabatan): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'update_access');

        $request->validate([
            'kode'      => ['required', 'string', 'max:20', 'unique:a03_jabatan,kode,' . $jabatan->id],
            'nama'      => ['required', 'string', 'max:100'],
            'singkatan' => ['nullable', 'string', 'max:20'],
            'status'    => ['required', 'in:1,0'],
        ], $this->messages());

        $original = $jabatan->toArray();

        $jabatan->update([
            'kode'      => strtoupper($request->kode),
            'nama'      => $request->nama,
            'singkatan' => $request->singkatan ? strtoupper($request->singkatan) : null,
            'status'    => $request->status,
        ]);

        ActivityLogService::masterUpdated(
            $this->menu,
            $jabatan,
            $original,
            "{$jabatan->nama} ({$jabatan->kode})",
            $this->logFields,
        );

        return redirect()->route('master.jabatan.index')
            ->with('success', 'Position data successfully updated.');
    }

    public function destroy(Jabatan $jabatan): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'delete_access');

        $userCount = \App\Models\User::where('jabatan', $jabatan->nama)->count();

        if ($userCount > 0) {
            return back()->with('error', "Position \"{$jabatan->nama}\" cannot be deleted because it is currently assigned to {$userCount} user(s).");
        }

        ActivityLogService::masterDeleted(
            $this->menu,
            $jabatan,
            "{$jabatan->nama} ({$jabatan->kode})",
            $this->logFields,
        );

        $jabatan->delete();

        return redirect()->route('master.jabatan.index')
            ->with('success', 'Position successfully deleted.');
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
            'kode.required'   => 'Position code is required.',
            'kode.unique'     => 'Position code is already registered.',
            'nama.required'   => 'Position name is required.',
            'status.required' => 'Status is required.',
        ];
    }
}