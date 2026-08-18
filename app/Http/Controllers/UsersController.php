<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UsersAccess;
use App\Models\DataMaster\Perusahaan;
use App\Models\DataMaster\Departemen;
use App\Models\DataMaster\Jabatan;
use App\Models\DataMaster\WilayahKerja;
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
        'settings.smtp'        => 'SMTP Konfigurasi',
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

        $users      = $query->paginate(15)->withQueryString();
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

        User::create([
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

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
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

        return redirect()->route('users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize_access('users', 'delete_access');

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->akses()->delete();
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
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

        DB::transaction(function () use ($request, $user) {
            $user->akses()->delete();

            $aksesInput = $request->input('akses', []);

            foreach (array_keys(self::$menuList) as $menu) {
                $menuData = $aksesInput[$menu] ?? [];

                $row = ['id_users' => $user->id, 'menu_access' => $menu];

                foreach (array_keys(self::$accessTypes) as $tipe) {
                    $row[$tipe] = isset($menuData[$tipe]) ? 1 : 0;
                }

                UsersAccess::create($row);
            }
        });

        return redirect()->route('users.index')
            ->with('success', 'Hak akses pengguna berhasil disimpan.');
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
            abort(403, 'Silakan login terlebih dahulu.');
        }

        if ($user->isAdmin()) {
            return;
        }

        if (!$user->hasAccess($menu, $tipe)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
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
            'nrk.required'           => 'NRK wajib diisi.',
            'nrk.unique'             => 'NRK sudah terdaftar.',
            'email.email'            => 'Format email tidak valid.',
            'email.unique'           => 'Email sudah terdaftar oleh pengguna lain.',
            'nama_karyawan.required' => 'Nama karyawan wajib diisi.',
            'password.min'           => 'Password minimal 8 karakter.',
            'password.confirmed'     => 'Konfirmasi password tidak cocok.',
            'id_perusahaan.required' => 'Perusahaan wajib dipilih.',
            'id_departemen.required' => 'Departemen wajib dipilih.',
            'jabatan.required'       => 'Jabatan wajib dipilih.',
            'wilker.required'        => 'Wilayah kerja wajib dipilih.',
        ];
    }
}