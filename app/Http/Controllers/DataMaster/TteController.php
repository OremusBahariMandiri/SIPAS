<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Models\DataMaster\Tte;
use App\Models\DataMaster\Perusahaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use phpseclib3\Crypt\RSA;

class TteController extends Controller
{
    private string $menu = 'master.tte';

    public function index(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        $query = Tte::with(['user', 'perusahaan', 'createdBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->whereHas(
                    'user',
                    fn($q) =>
                    $q->where('nrk', 'like', "%{$s}%")
                        ->orWhere('nama_karyawan', 'like', "%{$s}%")  // ← tambah
                        ->orWhere('jabatan', 'like', "%{$s}%")
                )->orWhereHas(
                    'perusahaan',
                    fn($q) =>
                    $q->where('nama', 'like', "%{$s}%")
                        ->orWhere('singkatan', 'like', "%{$s}%")
                );
            });
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'active'   => $query->valid(),
                'inactive' => $query->inactive(),
                'expired'  => $query->expired(),
                default    => null,
            };
        }

        if ($request->filled('perusahaan')) {
            $query->where('id_perusahaan', $request->perusahaan);
        }

        $perPage = in_array((int) $request->get('per_page'), [10, 15, 25, 50])
            ? (int) $request->get('per_page') : 15;

        $items         = $query->paginate($perPage)->withQueryString();
        $perusahaanList = \App\Models\DataMaster\Perusahaan::where('status', 1)->orderBy('nama')->get();

        return view('master.tte.index', compact('items', 'perusahaanList'));
    }

    public function create(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $users       = User::orderBy('nrk')->get();
        $perusahaans = Perusahaan::where('status', 1)->orderBy('nama')->get();

        // Ambil TTE yang sudah ada untuk user yang dipilih
        $existingTte = collect();
        if ($request->filled('user_id')) {
            $existingTte = Tte::where('id_user', $request->user_id)
                ->withTrashed() // termasuk yang soft deleted
                ->get();
        }

        return view('master.tte.create', compact('users', 'perusahaans', 'existingTte'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $request->validate([
            'id_user'        => ['required', 'exists:users,id'],
            'id_perusahaan'  => ['required', 'array', 'min:1'],
            'id_perusahaan.*' => ['exists:a01_perusahaan,id'],
            'expired_at'     => ['nullable', 'date', 'after:today'],
        ], $this->messages());

        $generated = 0;
        $skipped   = 0;

        foreach ($request->id_perusahaan as $idPerusahaan) {
            // Skip jika kombinasi sudah ada
            $exists = Tte::where('id_user', $request->id_user)
                ->where('id_perusahaan', $idPerusahaan)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $privateKey = RSA::createKey(2048);
            $publicKey  = $privateKey->getPublicKey();

            Tte::create([
                'id_user'       => $request->id_user,
                'id_perusahaan' => $idPerusahaan,
                'private_key'   => Crypt::encryptString($privateKey->toString('PKCS8')),
                'public_key'    => $publicKey->toString('PKCS8'),
                'verify_token'  => Str::random(64),
                'is_active'     => true,
                'expired_at'    => $request->expired_at,
                'created_by'    => auth()->id(),
                'updated_by'    => null,
            ]);

            $generated++;
        }

        $msg = "TTE berhasil digenerate untuk {$generated} perusahaan.";
        if ($skipped > 0) {
            $msg .= " {$skipped} perusahaan dilewati karena sudah memiliki TTE.";
        }

        return redirect()->route('master.tte.index')->with('success', $msg);
    }

    public function show(Tte $tte): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        $tte->load(['user', 'perusahaan', 'createdBy', 'updatedBy']);

        return view('master.tte.show', compact('tte'));
    }

    public function edit(Tte $tte): View
    {
        $this->authorizeAccess($this->menu, 'update_access');

        return view('master.tte.edit', compact('tte'));
    }

    public function update(Request $request, Tte $tte): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'update_access');

        $request->validate([
            'is_active'  => ['required', 'in:0,1'],
            'expired_at' => ['nullable', 'date'],
        ], $this->messages());

        $tte->update([
            'is_active'  => $request->is_active,
            'expired_at' => $request->expired_at,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('master.tte.index')
            ->with('success', 'Data TTE berhasil diperbarui.');
    }

    public function destroy(Tte $tte): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'delete_access');

        $tte->update(['updated_by' => auth()->id()]);
        $tte->delete();

        return redirect()->route('master.tte.index')
            ->with('success', 'TTE berhasil dihapus.');
    }

    public function regenerate(Tte $tte): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'update_access');

        $privateKey = RSA::createKey(2048);
        $publicKey  = $privateKey->getPublicKey();

        $tte->update([
            'private_key' => Crypt::encryptString($privateKey->toString('PKCS8')),
            'public_key'  => $publicKey->toString('PKCS8'),
            'updated_by'  => auth()->id(),
        ]);

        return redirect()->route('master.tte.show', $tte)
            ->with('success', 'Keypair TTE berhasil di-regenerate.');
    }

    public function toggleActive(Tte $tte): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'update_access');

        $tte->update([
            'is_active'  => !$tte->is_active,
            'updated_by' => auth()->id(),
        ]);

        $status = $tte->fresh()->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "TTE berhasil {$status}.");
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
            'id_user.required'          => 'User wajib dipilih.',
            'id_user.exists'            => 'User tidak ditemukan.',
            'id_perusahaan.required'    => 'Perusahaan wajib dipilih.',
            'id_perusahaan.exists'      => 'Perusahaan tidak ditemukan.',
            'expired_at.date'           => 'Format tanggal tidak valid.',
            'expired_at.after'          => 'Tanggal expired harus setelah hari ini.',
            'is_active.required'        => 'Status wajib dipilih.',
        ];
    }
}
