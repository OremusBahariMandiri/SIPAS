@extends('layouts.app')
@section('title', 'Generate TTE')
@section('page-title', 'TTE Master')

@section('content')



    <div class="sdv-header" style="align-items:center;">
        <a href="{{ route('master.tte.index') }}" class="sdv-back" title="Back">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="sdv-header-title" style="margin:0;">Generate TTE</h1>
    </div>

    <div>
        <div class="card card-body">

            @if ($errors->any())
                <div class="flash-error">
                    <i class="bi bi-exclamation-circle-fill" style="color:#dc2626;flex-shrink:0;"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul style="margin:0.25rem 0 0 1rem;padding:0;">
                            @foreach ($errors->all() as $e)
                                <li style="font-size:0.82rem;">{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('master.tte.store') }}" method="POST">
                @csrf
                <div class="form-grid">

                    {{-- User --}}
                    <div class="form-group form-span-2">
                        <label class="form-label">User <span class="req">*</span></label>
                        <select name="id_user" id="selectUser" class="tte-select2 @error('id_user') is-invalid @enderror">
                            <option value="">— Select User —</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ old('id_user', request('user_id')) == $user->id ? 'selected' : '' }}>
                                    {{ $user->nama_karyawan ?? '-' }} | {{ $user->departemen->singkatan ?? '-' }} |
                                    {{ $user->jabatan ?? '-' }} | {{ $user->perusahaan->singkatan ?? '-' }} |  {{ $user->wilker ?? '-' }} | {{ $user->nrk ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_user')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Company checkboxes --}}
                    <div class="form-group form-span-2">
                        <label class="form-label">Company <span class="req">*</span></label>
                        <small class="form-hint" style="display:block;margin-bottom:.5rem;">
                            Check the companies to generate a TTE for. Companies that already have a TTE cannot be selected.
                        </small>

                        <div id="perusahaanList" style="display:flex;flex-direction:column;gap:.5rem;">
                            @foreach ($perusahaans as $p)
                                @php
                                    $sudahAda = $existingTte->where('id_perusahaan', $p->id)->count() > 0;
                                @endphp
                                <label
                                    style="display:flex;align-items:center;gap:.75rem;padding:.6rem .75rem;
                            border:1px solid var(--border,#e5e7eb);border-radius:8px;
                            cursor:{{ $sudahAda ? 'not-allowed' : 'pointer' }};
                            background:{{ $sudahAda ? '#f9fafb' : '#fff' }};">
                                    <input type="checkbox" name="id_perusahaan[]" value="{{ $p->id }}"
                                        {{ in_array($p->id, old('id_perusahaan', [])) ? 'checked' : '' }}
                                        {{ $sudahAda ? 'disabled' : '' }} style="width:16px;height:16px;flex-shrink:0;">
                                    <div style="flex:1;">
                                        <div
                                            style="font-size:.875rem;font-weight:600;{{ $sudahAda ? 'color:#9ca3af;' : '' }}">
                                            {{ $p->nama }}
                                            <span style="font-weight:400;color:#6b7280;">({{ $p->singkatan }})</span>
                                        </div>
                                        @if ($sudahAda)
                                            <div style="font-size:.75rem;color:#9ca3af;">
                                                <i class="bi bi-shield-check"></i> Already has a TTE
                                            </div>
                                        @endif
                                    </div>
                                    @if ($p->logo)
                                        <img src="{{ Storage::url($p->logo) }}" alt="{{ $p->singkatan }}"
                                            style="width:32px;height:32px;object-fit:contain;flex-shrink:0;">
                                    @endif
                                </label>
                            @endforeach
                        </div>
                        @error('id_perusahaan')
                            <div class="invalid-msg" style="margin-top:.5rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Expiry Date --}}
                    <div class="form-group form-span-2">
                        <label class="form-label">Expiry Date (Optional)</label>
                        <input type="date" name="expired_at" value="{{ old('expired_at') }}"
                            min="{{ now()->addDay()->toDateString() }}"
                            class="form-control @error('expired_at') is-invalid @enderror">
                        @error('expired_at')
                            <div class="invalid-msg">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-shield-check"></i> Generate TTE
                    </button>
                    <a href="{{ route('master.tte.index') }}" class="btn-cancel">Cancel</a>
                </div>
            </form>

        </div>
    </div>

@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        /* ════════════════════════════════════════════════
               SELECT2 OVERRIDES  (same as submission/create)
            ════════════════════════════════════════════════ */
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid var(--border) !important;
            border-radius: 8px !important;
            background: var(--card) !important;
            display: flex !important;
            align-items: center !important;
            outline: none !important;
            position: relative !important;
            transition: border-color .15s, box-shadow .15s !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            padding-left: .8rem !important;
            padding-right: 3.2rem !important;
            color: var(--text) !important;
            font-size: .845rem !important;
            font-family: inherit !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: var(--muted) !important;
            opacity: .7 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            width: 30px !important;
            right: 4px !important;
            top: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: var(--muted) transparent transparent transparent !important;
            border-width: 5px 4px 0 4px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            position: absolute !important;
            right: 28px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            margin: 0 !important;
            float: none !important;
            font-size: 1.1rem !important;
            line-height: 1 !important;
            color: var(--muted) !important;
            font-weight: 400 !important;
            transition: color .13s !important;
            z-index: 1 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear:hover {
            color: #DC2626 !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(63, 93, 120, .12) !important;
        }

        .select2-container--default.select2-container--open.select2-container--above .select2-selection--single {
            border-radius: 0 0 8px 8px !important;
        }

        .select2-container--default.select2-container--open.select2-container--below .select2-selection--single {
            border-radius: 8px 8px 0 0 !important;
        }

        .is-invalid+.select2-container--default .select2-selection--single,
        .select2-container.is-invalid .select2-selection--single {
            border-color: #E53935 !important;
        }

        .select2-dropdown {
            border: 1px solid var(--border) !important;
            border-radius: 10px !important;
            box-shadow: 0 8px 28px rgba(13, 32, 64, .12) !important;
            font-family: inherit !important;
            font-size: .845rem !important;
            overflow: hidden !important;
            z-index: 9999 !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid var(--border) !important;
            border-radius: 7px !important;
            padding: .4rem .65rem !important;
            font-size: .82rem !important;
            font-family: inherit !important;
            outline: none !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(63, 93, 120, .1) !important;
        }

        .select2-results__option {
            padding: .5rem .85rem !important;
            font-size: .84rem !important;
            color: var(--text) !important;
            transition: background .1s !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: var(--primary-light) !important;
            color: var(--primary) !important;
        }

        .select2-container--default .select2-results__option[aria-selected="true"] {
            background: var(--primary) !important;
            color: #fff !important;
            font-weight: 600 !important;
        }

        .select2-search--dropdown {
            padding: .6rem .6rem .4rem !important;
            border-bottom: 1px solid var(--border) !important;
        }

        .select2-results__options {
            max-height: 220px !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function() {

            /* ══════════════════════════════════════════
               SELECT2 INIT
            ══════════════════════════════════════════ */
            function initSelect2(selector, placeholder) {
                $(selector).select2({
                    placeholder,
                    allowClear: true,
                    width: '100%',
                    language: {
                        noResults: () =>
                            '<span style="padding:.5rem .85rem;display:block;' +
                            'font-size:.82rem;color:var(--muted);">No results found</span>',
                    },
                    escapeMarkup: m => m,
                });
            }

            initSelect2('#selectUser', '— Select User —');

            /* ══════════════════════════════════════════
               RELOAD PAGE ON USER CHANGE
               (to refresh which companies already have a TTE)
            ══════════════════════════════════════════ */
            $('#selectUser').on('change', function() {
                const userId = $(this).val();
                if (!userId) return;
                const url = new URL(window.location.href);
                url.searchParams.set('user_id', userId);
                window.location.href = url.toString();
            });

            /* ══════════════════════════════════════════
               RESTORE USER SELECTION AFTER RELOAD
            ══════════════════════════════════════════ */
            document.addEventListener('DOMContentLoaded', function() {
                const params = new URLSearchParams(window.location.search);
                const userId = params.get('user_id');
                if (userId) {
                    $('#selectUser').val(userId).trigger('change.select2');
                }
            });

        })();
    </script>
@endpush
