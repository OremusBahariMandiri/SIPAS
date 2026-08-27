<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UsersAccess;
use App\Models\DataMaster\Perusahaan;
use App\Models\DataMaster\Departemen;
use App\Models\DataMaster\Jabatan;
use App\Models\DataMaster\WilayahKerja;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UsersController extends Controller
{
    public static array $menuList = [
        'master.perusahaan'    => 'Master – Perusahaan',
        'master.departemen'    => 'Master – Departemen',
        'master.jabatan'       => 'Master – Jabatan',
        'master.wilker'        => 'Master – Wilayah Kerja',
        'master.jenis-dokumen' => 'Master – Jenis Dokumen',
        'master.tte'           => 'Master – TTE',
        'users'                => 'Manajemen – Pengguna',
        'users.akses'          => 'Manajemen – Hak Akses',
        'data.submission'      => 'Dokumen – Pengajuan Surat',
        'activity_log'         => 'Log Aktivitas',          // ← tambah menu baru
        'settings.smtp'        => 'SMTP Konfigurasi',
        'settings.queue_monitor' => 'Queue Monitor',
    ];

    public static array $accessTypes = [
        'index_access'        => 'Lihat',
        'create_access'       => 'Tambah',
        'update_access'       => 'Ubah',
        'show_access'         => 'Detail',
        'delete_access'       => 'Hapus',
        'download_access'     => 'Unduh',
        'export_pdf_access'   => 'Export PDF',
        'export_excel_access' => 'Export Excel',
        'approval_access'     => 'Approval',
    ];

    // ─────────────────────────────────────────
    //  CRUD PENGGUNA
    // ─────────────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorize_access('users', 'index_access');

        $perPage = in_array((int) $request->get('per_page'), [10, 15, 25, 50])
            ? (int) $request->get('per_page') : 15;

        $query = User::with(['perusahaan', 'departemen'])->orderBy('nrk');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nrk', 'like', '%' . $request->search . '%')
                    ->orWhere('nama_karyawan', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('perusahaan')) {
            $query->where('id_perusahaan', $request->perusahaan);
        }

        if ($request->filled('role')) {
            $query->where('is_admin', $request->role === 'admin' ? 1 : 0);
        }

        $users      = $query->paginate($perPage)->withQueryString();
        $perusahaan = Perusahaan::aktif()->orderBy('nama')->get();

        return view('users.index', compact('users', 'perusahaan'));
    }

    public function create(): View
    {
        $this->authorize_access('users', 'create_access');

        return view('users.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize_access('users', 'create_access');

        $request->validate([
            'nrk'           => ['required', 'string', 'max:50', 'unique:users,nrk'],
            'email'         => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'nama_karyawan' => ['required', 'string', 'max:100'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'id_perusahaan' => ['required', 'exists:a01_perusahaan,id'],
            'id_departemen' => ['required', 'exists:a02_departemen,id'],
            'jabatan'       => ['required', 'string'],
            'wilker'        => ['required', 'string'],
            'is_admin'      => ['sometimes', 'boolean'],
        ], $this->messages());

        $user = User::create([
            'nrk'           => $request->nrk,
            'email'         => $request->email ?: null,
            'nama_karyawan' => $request->nama_karyawan,
            'password'      => Hash::make($request->password),
            'id_perusahaan' => $request->id_perusahaan,
            'id_departemen' => $request->id_departemen,
            'jabatan'       => $request->jabatan,
            'wilker'        => $request->wilker,
            'is_admin'      => $request->boolean('is_admin') ? 1 : 0,
        ]);

        // ── Log ─────────────────────────────────────────────────
        ActivityLogService::userCreated($user);

        return redirect()->route('users.index')
            ->with('success', 'User has been added successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize_access('users', 'show_access');

        $user->load(['perusahaan', 'departemen', 'akses']);
        $menuList    = self::$menuList;
        $accessTypes = self::$accessTypes;

        return view('users.show', compact('user', 'menuList', 'accessTypes'));
    }

    public function edit(User $user): View
    {
        $this->authorize_access('users', 'update_access');

        return view('users.edit', array_merge(['user' => $user], $this->formData()));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize_access('users', 'update_access');

        $request->validate([
            'nrk'           => ['required', 'string', 'max:50', 'unique:users,nrk,' . $user->id],
            'email'         => ['nullable', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'nama_karyawan' => ['required', 'string', 'max:100'],
            'password'      => ['nullable', 'string', 'min:8', 'confirmed'],
            'id_perusahaan' => ['required', 'exists:a01_perusahaan,id'],
            'id_departemen' => ['required', 'exists:a02_departemen,id'],
            'jabatan'       => ['required', 'string'],
            'wilker'        => ['required', 'string'],
            'is_admin'      => ['sometimes', 'boolean'],
        ], $this->messages());

        // Simpan data lama sebelum diupdate
        $original = $user->toArray();

        $data = [
            'nrk'           => $request->nrk,
            'email'         => $request->email ?: null,
            'nama_karyawan' => $request->nama_karyawan,
            'id_perusahaan' => $request->id_perusahaan,
            'id_departemen' => $request->id_departemen,
            'jabatan'       => $request->jabatan,
            'wilker'        => $request->wilker,
            'is_admin'      => $request->boolean('is_admin') ? 1 : 0,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // ── Log ─────────────────────────────────────────────────
        ActivityLogService::userUpdated($user, $original);

        return redirect()->route('users.index')
            ->with('success', 'User data has been updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize_access('users', 'delete_access');

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $related = [];

        if ($user->pengajuanSurats()->exists()) {
            $related[] = 'Letter submissions (' . $user->pengajuanSurats()->count() . ')';
        }

        if ($user->pengajuanTerusans()->exists()) {
            $related[] = 'Forwarding approvals (' . $user->pengajuanTerusans()->count() . ')';
        }

        if ($user->tteList()->exists()) {
            $related[] = 'Electronic signatures (' . $user->tteList()->count() . ')';
        }

        if (!empty($related)) {
            return back()->with('error_related', [
                'user'  => $user->nama_karyawan,
                'items' => $related,
            ]);
        }

        // ── Log sebelum dihapus ──────────────────────────────────
        ActivityLogService::userDeleted($user);

        $user->akses()->delete();
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User "' . $user->nama_karyawan . '" has been deleted successfully.');
    }

    // ─────────────────────────────────────────
    //  HAK AKSES
    // ─────────────────────────────────────────

    public function editAkses(User $user): View
    {
        $this->authorize_access('users.akses', 'update_access');

        $user->load('akses');

        $aksesMap = [];
        foreach ($user->akses as $akses) {
            $aksesMap[$akses->menu_access] = $akses->toArray();
        }

        $menuList    = self::$menuList;
        $accessTypes = self::$accessTypes;

        return view('users.akses', compact('user', 'aksesMap', 'menuList', 'accessTypes'));
    }

    public function updateAkses(Request $request, User $user): RedirectResponse
    {
        $this->authorize_access('users.akses', 'update_access');

        $aksesInput = $request->input('akses', []);

        DB::transaction(function () use ($request, $user, $aksesInput) {
            $user->akses()->delete();

            foreach (array_keys(self::$menuList) as $menu) {
                $menuData = $aksesInput[$menu] ?? [];

                $row = ['id_users' => $user->id, 'menu_access' => $menu];

                foreach (array_keys(self::$accessTypes) as $tipe) {
                    $row[$tipe] = isset($menuData[$tipe]) ? 1 : 0;
                }

                UsersAccess::create($row);
            }
        });

        // ── Log ─────────────────────────────────────────────────
        ActivityLogService::userAccessUpdated($user, $aksesInput);

        return redirect()->route('users.index')
            ->with('success', 'Access rights for "' . $user->nama_karyawan . '" have been saved successfully.');
    }

    public function aksesIndex(): View
    {
        $this->authorize_access('users.akses', 'index_access');

        $users       = User::with(['perusahaan', 'departemen', 'akses'])->orderBy('nrk')->get();
        $menuList    = self::$menuList;
        $accessTypes = self::$accessTypes;

        return view('users.akses-index', compact('users', 'menuList', 'accessTypes'));
    }

    // ─────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────

    private function authorize_access(string $menu, string $tipe): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Please log in to continue.');
        }

        if ($user->isAdmin()) {
            return;
        }

        if (!$user->hasAccess($menu, $tipe)) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    private function formData(): array
    {
        return [
            'perusahaan'   => Perusahaan::aktif()->orderBy('nama')->get(),
            'departemen'   => Departemen::aktif()->orderBy('nama')->get(),
            'jabatan'      => Jabatan::aktif()->orderBy('nama')->get(),
            'wilayahKerja' => WilayahKerja::orderBy('wilayah_kerja')->get(),
        ];
    }

    private function messages(): array
    {
        return [
            'nrk.required'           => 'NRK is required.',
            'nrk.unique'             => 'This NRK is already registered.',
            'email.email'            => 'Please enter a valid email address.',
            'email.unique'           => 'This email is already used by another user.',
            'nama_karyawan.required' => 'Employee name is required.',
            'password.min'           => 'Password must be at least 8 characters.',
            'password.confirmed'     => 'Password confirmation does not match.',
            'id_perusahaan.required' => 'Please select a company.',
            'id_departemen.required' => 'Please select a department.',
            'jabatan.required'       => 'Please select a position.',
            'wilker.required'        => 'Please select a work area.',
        ];
    }
}