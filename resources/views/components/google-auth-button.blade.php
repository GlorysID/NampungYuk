@php
    $firebaseConfig = config('services.firebase', []);
    $firebaseReady = ! empty($firebaseConfig['api_key'])
        && ! empty($firebaseConfig['auth_domain'])
        && ! empty($firebaseConfig['project_id']);
    $clientConfig = [
        'api_key' => $firebaseConfig['api_key'] ?? null,
        'auth_domain' => $firebaseConfig['auth_domain'] ?? null,
        'project_id' => $firebaseConfig['project_id'] ?? null,
        'storage_bucket' => $firebaseConfig['storage_bucket'] ?? null,
        'messaging_sender_id' => $firebaseConfig['messaging_sender_id'] ?? null,
        'app_id' => $firebaseConfig['app_id'] ?? null,
    ];
@endphp

@if ($firebaseReady)
    <div x-data="firebaseGoogleAuth({ endpoint: {{ Js::from(route('auth.firebase.google')) }}, config: {{ Js::from($clientConfig) }} })">
        <button type="button"
                @click="login()"
                :disabled="loading"
                class="h-11 w-full border border-[var(--border-color)] bg-[var(--surface-subtle)] hover:bg-[var(--surface-hover)] hover:border-[var(--border-strong)] text-[14px] font-semibold text-[var(--primary)] flex items-center justify-center gap-2.5 rounded-xl transition cursor-pointer shadow-xs disabled:opacity-60 disabled:cursor-wait">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span x-show="!loading">Lanjutkan dengan Google</span>
            <span x-show="loading" x-cloak>Menghubungkan ke Google...</span>
        </button>

        <p x-show="error"
           x-text="error"
           x-cloak
           role="alert"
           class="mt-2.5 p-2.5 rounded-xl border border-rose-500/20 bg-rose-500/10 text-rose-400 text-[12.5px] leading-snug"></p>
    </div>
@else
    <a href="{{ route('auth.google') }}"
       class="h-11 w-full border border-[var(--border-color)] bg-[var(--surface-subtle)] hover:bg-[var(--surface-hover)] hover:border-[var(--border-strong)] text-[14px] font-semibold text-[var(--primary)] flex items-center justify-center gap-2.5 rounded-xl transition cursor-pointer shadow-xs">
        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
        </svg>
        <span>Lanjutkan dengan Google</span>
    </a>
@endif
