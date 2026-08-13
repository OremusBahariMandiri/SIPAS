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
    /**
     * Daftar menu yang tersedia untuk hak akses.
     * Tambahkan menu baru di sini agar muncul di form akses.
     */
    public static array $menuList = [
        // Master Data
        'master.perusahaan'   => 'Master – Perusahaan',
        'master.departemen'   => 'Master – Departemen',
        'master.jabatan'      => 'Master – Jabatan',
        'master.wilker'       => 'Master – Wilayah Kerja',
        'master.jenis-dokumen'=> 'Master – Jenis Dokumen',
        'master.tte'          => 'Master – TTE',

        // Manajemen Pengguna
        'users'               => 'Manajemen – Pengguna',
        'users.akses'         => 'Manajemen – Hak Akses',

        // Data Transaksi
        'data.submission'     => 'Dokumen – Pengajuan Surat',
    ];

    /**
     * Tipe akses yang bisa dikonfigurasi per menu.
     */
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

    /**
     * Daftar semua pengguna.
     */
    public function index(Request $request): View
    {
        $this->authorize_access('users', 'index_access');

        $query = User::with(['perusahaan', 'departemen'])
            ->orderBy('nrk');

        if ($request->filled('search')) {
            $query->where('nrk', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('perusahaan')) {
            $query->where('id_perusahaan', $request->perusahaan);
        }

        $users      = $query->paginate(15)->withQueryString();
        $perusahaan = Perusahaan::aktif()->orderBy('nama')->get();

        return view('users.index', compact('users', 'perusahaan'));
    }

    /**
     * Form tambah pengguna.
     */
    public function create(): View
    {
        $this->authorize_access('users', 'create_access');

        return view('users.create', $this->formData());
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize_access('users', 'create_access');

        $request->validate([
            'nrk'           => ['required', 'string', 'max:50', 'unique:users,nrk'],
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

    /**
     * Detail pengguna.
     */
    public function show(User $user): View
    {
        $this->authorize_access('users', 'show_access');

        $user->load(['perusahaan', 'departemen', 'akses']);
        $menuList    = self::$menuList;
        $accessTypes = self::$accessTypes;

        return view('users.show', compact('user', 'menuList', 'accessTypes'));
    }

    /**
     * Form edit pengguna.
     */
    public function edit(User $user): View
    {
        $this->authorize_access('users', 'update_access');

        return view('users.edit', array_merge(['user' => $user], $this->formData()));
    }

    /**
     * Update data pengguna.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize_access('users', 'update_access');

        $request->validate([
            'nrk'           => ['required', 'string', 'max:50', 'unique:users,nrk,' . $user->id],
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

    /**
     * Hapus pengguna.
     */
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

    /**
     * Form hak akses seorang pengguna.
     */
    public function editAkses(User $user): View
    {
        $this->authorize_access('users.akses', 'update_access');

        $user->load('akses');

        // Map akses ke array [menu => [tipe => value]]
        $aksesMap = [];
        foreach ($user->akses as $akses) {
            $aksesMap[$akses->menu_access] = $akses->toArray();
        }

        $menuList    = self::$menuList;
        $accessTypes = self::$accessTypes;

        return view('users.akses', compact('user', 'aksesMap', 'menuList', 'accessTypes'));
    }

    /**
     * Simpan / update hak akses pengguna.
     */
    public function updateAkses(Request $request, User $user): RedirectResponse
    {
        $this->authorize_access('users.akses', 'update_access');

        DB::transaction(function () use ($request, $user) {
            // Hapus semua akses lama
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

    /**
     * Halaman daftar semua pengguna beserta ringkasan akses mereka.
     */
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

    /**
     * Cek akses pengguna yang sedang login.
     * Admin selalu lolos. Non-admin dicek ke tabel users_access.
     */
    private function authorize_access(string $menu, string $tipe): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Silakan login terlebih dahulu.');
        }

        // Admin bypass semua akses
        if ($user->isAdmin()) {
            return;
        }

        if (!$user->hasAccess($menu, $tipe)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }
    }

    /**
     * Data form (dropdown).
     */
    private function formData(): array
    {
        return [
            'perusahaan'   => Perusahaan::aktif()->orderBy('nama')->get(),
            'departemen'   => Departemen::aktif()->orderBy('nama')->get(),
            'jabatan'      => Jabatan::aktif()->orderBy('nama')->get(),
            'wilayahKerja' => WilayahKerja::orderBy('wilayah_kerja')->get(),
        ];
    }

    /**
     * Pesan validasi.
     */
    private function messages(): array
    {
        return [
            'nrk.required'           => 'NRK wajib diisi.',
            'nrk.unique'             => 'NRK sudah terdaftar.',
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