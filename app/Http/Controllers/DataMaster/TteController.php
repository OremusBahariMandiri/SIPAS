<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Models\DataMaster\Tte;
use App\Models\DataMaster\Perusahaan;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use phpseclib3\Crypt\RSA;


class TteController extends Controller
{
    private string $menu = 'master.tte';

    private array $logFields = ['id_user', 'id_perusahaan', 'is_active', 'expired_at'];

    public function index(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        $query = User::whereHas('ttes')
            ->with([
                'perusahaan',
                'departemen',
                'ttes' => fn($q) => $q->with('perusahaan'),
            ])
            ->orderBy('nrk');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nrk', 'like', "%{$s}%")
                    ->orWhere('nama_karyawan', 'like', "%{$s}%")
                    ->orWhere('jabatan', 'like', "%{$s}%")
                    ->orWhereHas(
                        'perusahaan',
                        fn($q) =>
                        $q->where('nama', 'like', "%{$s}%")
                            ->orWhere('singkatan', 'like', "%{$s}%")
                    );
            });
        }

        if ($request->filled('status')) {
            $query->whereHas('ttes', function ($q) use ($request) {
                match ($request->status) {
                    'active'   => $q->valid(),
                    'inactive' => $q->inactive(),
                    'expired'  => $q->expired(),
                    default    => null,
                };
            });
        }

        if ($request->filled('perusahaan')) {
            $query->where('id_perusahaan', $request->perusahaan);
        }

        $perPage = in_array((int) $request->get('per_page'), [10, 15, 25, 50])
            ? (int) $request->get('per_page') : 15;

        $items          = $query->paginate($perPage)->withQueryString();
        $perusahaanList = Perusahaan::where('status', 1)->orderBy('nama')->get();

        return view('master.tte.index', compact('items', 'perusahaanList'));
    }

    public function create(Request $request): View
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $users       = User::orderBy('nrk')->get();
        $perusahaans = Perusahaan::where('status', 1)->orderBy('nama')->get();

        $existingTte = collect();
        if ($request->filled('user_id')) {
            $existingTte = Tte::where('id_user', $request->user_id)->get();
        }

        return view('master.tte.create', compact('users', 'perusahaans', 'existingTte'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'create_access');

        $request->validate([
            'id_user'         => ['required', 'exists:users,id'],
            'id_perusahaan'   => ['required', 'array', 'min:1'],
            'id_perusahaan.*' => ['exists:a01_perusahaan,id'],
            'expired_at'      => ['nullable', 'date', 'after:today'],
        ], $this->messages());

        $targetUser = User::find($request->id_user);
        $generated  = 0;
        $skipped    = 0;

        foreach ($request->id_perusahaan as $idPerusahaan) {
            $exists = Tte::where('id_user', $request->id_user)
                ->where('id_perusahaan', $idPerusahaan)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $privateKey = RSA::createKey(2048);
            $publicKey  = $privateKey->getPublicKey();

            $tte = Tte::create([
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

            $tte->load('perusahaan');

            ActivityLogService::masterCreated(
                $this->menu,
                $tte,
                "{$targetUser->nrk} – {$targetUser->nama_karyawan} @ {$tte->perusahaan?->singkatan}",
                $this->logFields,
            );

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

    public function showUser(User $user): View
    {
        $this->authorizeAccess($this->menu, 'index_access');

        $user->load([
            'ttes.perusahaan',
            'ttes.createdBy',
            'ttes.updatedBy',
            'departemen',
            'perusahaan',
        ]);

        return view('master.tte.show_user', compact('user'));
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

        $original = $tte->toArray();

        $tte->update([
            'is_active'  => $request->is_active,
            'expired_at' => $request->expired_at,
            'updated_by' => auth()->id(),
        ]);

        $tte->load('user', 'perusahaan');

        ActivityLogService::masterUpdated(
            $this->menu,
            $tte,
            $original,
            "{$tte->user?->nrk} – {$tte->user?->nama_karyawan} @ {$tte->perusahaan?->singkatan}",
            $this->logFields,
        );

        return redirect()->route('master.tte.index')
            ->with('success', 'Data TTE berhasil diperbarui.');
    }

    public function destroy(Tte $tte): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'delete_access');

        $tte->load('user', 'perusahaan');
        $label = "{$tte->user?->nrk} – {$tte->user?->nama_karyawan} @ {$tte->perusahaan?->singkatan}";

        ActivityLogService::masterDeleted(
            $this->menu,
            $tte,
            $label,
            $this->logFields,
        );

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

        $tte->load('user', 'perusahaan');

        ActivityLogService::masterAction(
            $this->menu,
            'update',
            $tte,
            "{$tte->user?->nrk} – {$tte->user?->nama_karyawan} @ {$tte->perusahaan?->singkatan}",
            'Keypair TTE di-regenerate. Dokumen lama tidak dapat diverifikasi.',
            ['action' => 'regenerate_keypair'],
        );

        return redirect()->route('master.tte.show', $tte)
            ->with('success', 'Keypair TTE berhasil di-regenerate.');
    }

    public function toggleActive(Tte $tte): RedirectResponse
    {
        $this->authorizeAccess($this->menu, 'update_access');

        $statusBefore = $tte->is_active;

        $tte->update([
            'is_active'  => !$tte->is_active,
            'updated_by' => auth()->id(),
        ]);

        $tte->load('user', 'perusahaan');
        $statusAfter = $tte->fresh()->is_active;
        $statusLabel = $statusAfter ? 'diaktifkan' : 'dinonaktifkan';

        ActivityLogService::masterAction(
            $this->menu,
            'update',
            $tte,
            "{$tte->user?->nrk} – {$tte->user?->nama_karyawan} @ {$tte->perusahaan?->singkatan}",
            "Status TTE {$statusLabel}.",
            ['is_active' => ['before' => $statusBefore, 'after' => $statusAfter]],
        );

        return back()->with('success', "TTE berhasil {$statusLabel}.");
    }

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
            'id_user.required'       => 'User wajib dipilih.',
            'id_user.exists'         => 'User tidak ditemukan.',
            'id_perusahaan.required' => 'Perusahaan wajib dipilih.',
            'id_perusahaan.exists'   => 'Perusahaan tidak ditemukan.',
            'expired_at.date'        => 'Format tanggal tidak valid.',
            'expired_at.after'       => 'Tanggal expired harus setelah hari ini.',
            'is_active.required'     => 'Status wajib dipilih.',
        ];
    }
}
