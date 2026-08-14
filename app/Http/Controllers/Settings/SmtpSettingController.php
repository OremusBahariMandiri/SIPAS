<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\SmtpSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;

class SmtpSettingController extends Controller
{
    // ── Tampilkan form (create jika belum ada, edit jika sudah) ──
    public function index(): View
    {
        $setting = SmtpSetting::latest()->first();

        return view('settings.smtp.index', compact('setting'));
    }

    // ── Simpan atau update ─────────────────────────────────────
    public function save(Request $request): RedirectResponse
    {
        $existing = SmtpSetting::latest()->first();

        $rules = [
            'mailer'       => ['required', 'in:smtp'],
            'host'         => ['required', 'string', 'max:150'],
            'port'         => ['required', 'integer', 'in:25,465,587,2525'],
            'encryption'   => ['required', 'in:ssl,tls,none'],
            'username'     => ['required', 'email', 'max:150'],
            'from_address' => ['required', 'email', 'max:150'],
            'from_name'    => ['required', 'string', 'max:100'],
            // Password wajib hanya saat pertama kali simpan
            'password'     => $existing
                                ? ['nullable', 'string', 'min:4']
                                : ['required', 'string', 'min:4'],
        ];

        $request->validate($rules, $this->messages());

        $data = $request->only([
            'mailer', 'host', 'port', 'encryption',
            'username', 'from_address', 'from_name',
        ]);

        // Hanya update password jika diisi
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        if ($existing) {
            $existing->update($data);
        } else {
            SmtpSetting::create($data);
        }

        return redirect()->route('settings.smtp.index')
            ->with('success', 'Konfigurasi SMTP berhasil disimpan.');
    }

    // ── Tes kirim email ────────────────────────────────────────
    public function test(Request $request): RedirectResponse
    {
        $setting = SmtpSetting::latest()->first();

        if (!$setting) {
            return back()->with('error', 'Konfigurasi SMTP belum disimpan.');
        }

        $request->validate([
            'test_email' => ['required', 'email'],
        ], [
            'test_email.required' => 'Email tujuan tes wajib diisi.',
            'test_email.email'    => 'Format email tidak valid.',
        ]);

        // Terapkan config runtime sebelum kirim
        $setting->applyToMailer();

        try {
            Mail::raw(
                'Ini adalah email tes dari ' . config('app.name') . '. '
                . 'Konfigurasi SMTP Anda berfungsi dengan baik.',
                fn ($msg) => $msg
                    ->to($request->test_email)
                    ->subject('[TES] ' . config('app.name') . ' — SMTP Test')
            );

            $setting->update([
                'tested_at'   => now(),
                'test_result' => true,
            ]);

            return back()->with('success', 'Email tes berhasil dikirim ke ' . $request->test_email . '.');

        } catch (\Exception $e) {
            $setting->update([
                'tested_at'   => now(),
                'test_result' => false,
            ]);

            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }

    // ─── Validation messages ──────────────────────────────────
    private function messages(): array
    {
        return [
            'host.required'         => 'SMTP Host wajib diisi.',
            'port.required'         => 'Port wajib dipilih.',
            'port.in'               => 'Port tidak valid.',
            'encryption.required'   => 'Enkripsi wajib dipilih.',
            'username.required'     => 'Username (email) wajib diisi.',
            'username.email'        => 'Username harus berupa alamat email.',
            'password.required'     => 'Password wajib diisi saat pertama kali konfigurasi.',
            'password.min'          => 'Password minimal 4 karakter.',
            'from_address.required' => 'From Address wajib diisi.',
            'from_address.email'    => 'From Address harus berupa alamat email.',
            'from_name.required'    => 'From Name wajib diisi.',
        ];
    }
}