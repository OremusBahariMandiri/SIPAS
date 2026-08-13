<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DataMaster\Perusahaan;
use App\Models\DataMaster\Departemen;
use App\Models\DataMaster\Jabatan;
use App\Models\DataMaster\WilayahKerja;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct()
    {
        // Hanya guest yang boleh akses register
        $this->middleware('guest');
    }

    /**
     * Tampilkan form register dengan data master
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register', [
            'perusahaan'   => Perusahaan::aktif()->orderBy('nama')->get(),
            'departemen'   => Departemen::aktif()->orderBy('nama')->get(),
            'jabatan'      => Jabatan::aktif()->orderBy('nama')->get(),
            'wilayahKerja' => WilayahKerja::orderBy('wilayah_kerja')->get(),
        ]);
    }

    /**
     * Proses pendaftaran akun baru
     */
    public function register(Request $request): RedirectResponse
    {
        $this->validator($request->all())->validate();

        $user = User::create([
            'nrk'           => $request->nrk,
            'password'      => Hash::make($request->password),
            'id_perusahaan' => $request->id_perusahaan,
            'id_departemen' => $request->id_departemen,
            'jabatan'       => $request->jabatan,
            'wilker'        => $request->wilker,
            'is_admin'      => 0,
        ]);

        // Langsung login setelah register berhasil
        Auth::login($user);

        return redirect()->route('home');
    }

    /**
     * Aturan validasi form register
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nrk'           => ['required', 'string', 'max:50', 'unique:users,nrk'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'id_perusahaan' => ['required', 'exists:a01_perusahaan,id'],
            'id_departemen' => ['required', 'exists:a02_departemen,id'],
            'jabatan'       => ['required', 'string'],
            'wilker'        => ['required', 'string'],
        ], [
            'nrk.required'           => 'NRK wajib diisi.',
            'nrk.unique'             => 'NRK sudah terdaftar, silakan login.',
            'password.min'           => 'Password minimal 8 karakter.',
            'password.confirmed'     => 'Konfirmasi password tidak cocok.',
            'id_perusahaan.required' => 'Perusahaan wajib dipilih.',
            'id_departemen.required' => 'Departemen wajib dipilih.',
            'jabatan.required'       => 'Jabatan wajib dipilih.',
            'wilker.required'        => 'Wilayah kerja wajib dipilih.',
        ]);
    }
}