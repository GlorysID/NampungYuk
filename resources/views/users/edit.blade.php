@extends('layouts.app')

@section('title', 'Pengaturan Profil — NampungYuk')

@section('content')
<div class="max-w-2xl mx-auto space-y-5" x-data="{ preview: null }">

    <!-- Back -->
    <a href="{{ route('profile.show', $user->username) }}"
       class="inline-flex items-center gap-1.5 text-xs text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#0e9c8b] dark:hover:text-[#50d2c1] transition font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke Profil</span>
    </a>

    <div class="flex items-center gap-2">
        <h1 class="text-lg font-bold text-[#10161f] dark:text-[#eaecf0]">Pengaturan Profil</h1>
        <span class="hl-label">Edit</span>
    </div>

    @if (session('success'))
        <div class="p-3 rounded-lg bg-[#d9fbf4] dark:bg-[#50d2c1]/12 border border-[#50d2c1]/30 text-[#0e9c8b] dark:text-[#6ee7d5] text-xs flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <!-- Avatar panel -->
        <div class="hl-panel p-5 space-y-4">
            <p class="hl-label">Foto Profil</p>
            <div class="flex items-center gap-4">
                <div class="relative shrink-0">
                    <img :src="preview || '{{ $user->avatar ?: 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode($user->name) }}'"
                         alt="{{ $user->name }}"
                         class="w-16 h-16 rounded-lg object-cover ring-1 ring-[#d5dbe2] dark:ring-[#262d3a]">
                </div>
                <div class="min-w-0 flex-1 space-y-1.5">
                    <label for="avatar" class="btn-secondary text-xs py-1.5 px-3 cursor-pointer inline-flex">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Pilih gambar</span>
                        <input type="file" id="avatar" name="avatar" accept="image/*" class="hidden"
                               @change="const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)">
                    </label>
                    <p class="text-[10px] text-[#5c6979] dark:text-[#7e8a9a]">JPG, PNG, WEBP · maks 2MB. Rasio kotak disarankan.</p>
                    @error('avatar')
                        <p class="text-rose-500 font-medium text-[11px]">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Identity panel -->
        <div class="hl-panel p-5 space-y-4">
            <p class="hl-label">Identitas</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="name" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0]">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="ny-input text-sm">
                    @error('name') <p class="text-rose-500 text-[11px]">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1">
                    <label for="username" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0]">Username</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#5c6979] dark:text-[#7e8a9a] font-mono text-sm">@</span>
                        <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" class="ny-input text-sm font-mono pl-7">
                    </div>
                    @error('username') <p class="text-rose-500 text-[11px]">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <label for="bio" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0]">Bio</label>
                    <span class="text-[10px] font-mono text-[#5c6979] dark:text-[#7e8a9a]">maks 280</span>
                </div>
                <textarea id="bio" name="bio" rows="3" maxlength="280" class="ny-input text-sm resize-none"
                          placeholder="Ceritakan spesialisasimu...">{{ old('bio', $user->bio) }}</textarea>
                @error('bio') <p class="text-rose-500 text-[11px]">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1">
                <label for="github_url" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0]">URL GitHub</label>
                <input type="url" id="github_url" name="github_url" value="{{ old('github_url', $user->github_url) }}" placeholder="https://github.com/username" class="ny-input text-sm">
                @error('github_url') <p class="text-rose-500 text-[11px]">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('profile.show', $user->username) }}" class="btn-secondary text-xs py-2 px-4">Batal</a>
            <button type="submit" class="btn-primary text-xs py-2 px-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>

</div>
@endsection
