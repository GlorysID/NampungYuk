@extends('layouts.app')

@section('title', 'Pamerkan Karya Codingan Baru — NampungYuk')

@section('content')
<div class="space-y-6">

    <!-- Header & Back link -->
    <div>
        <a href="{{ route('projects.index') }}" 
           class="inline-flex items-center gap-1.5 text-xs font-mono text-neutral-500 dark:text-neutral-400 hover:text-orange-500 transition mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Feed</span>
        </a>
        <div class="flex items-center gap-2 mb-1">
            <span class="px-2 py-0.5 rounded-md bg-orange-500/10 text-orange-500 text-[10px] font-mono font-bold border border-orange-500/20">
                [ DEV SHOWCASE // NEW BUILD ]
            </span>
            <span class="text-xs text-neutral-400 font-mono">Buka untuk umum</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-neutral-900 dark:text-white">
            Pamerkan Karya Codinganmu
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 mt-1">
            Tunjukkan project yang kamu bangun, bagikan link GitHub atau live demo, dan dapatkan feedback teknis dari sesama developer.
        </p>
    </div>

    <!-- Upload Form Card (Vercel / Supabase Style) -->
    <div class="spotlight-card p-6 sm:p-8 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b]">
            
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 text-xs space-y-1">
                    <p class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Terjadi kesalahan input:</span>
                    </p>
                    <ul class="list-disc list-inside ml-5 font-mono">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                @guest
                    <!-- Author Name -->
                    <div>
                        <label for="guest_name" class="flex items-center gap-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5 font-mono">
                            <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Nama / Alias Developer</span>
                            <span class="text-orange-500">*</span>
                        </label>
                        <input type="text" name="guest_name" id="guest_name" required
                               value="{{ old('guest_name') }}"
                               placeholder="Contoh: Rian (Fullstack Dev)" 
                               class="w-full px-4 py-2.5 text-xs sm:text-sm bg-neutral-50 dark:bg-[#111114] border border-neutral-300 dark:border-white/[0.08] rounded-xl focus:border-orange-500 text-neutral-900 dark:text-white">
                    </div>
                @endguest

                <!-- Project Title -->
                <div>
                    <label for="title" class="flex items-center gap-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5 font-mono">
                        <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        <span>Nama / Judul Project Codingan</span>
                        <span class="text-orange-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" required
                           value="{{ old('title') }}"
                           placeholder="Contoh: KetikCepat.id — Platform Tes Mengetik Korpus Indonesia" 
                           class="w-full px-4 py-2.5 text-xs sm:text-sm bg-neutral-50 dark:bg-[#111114] border border-neutral-300 dark:border-white/[0.08] rounded-xl focus:border-orange-500 text-neutral-900 dark:text-white font-medium">
                </div>

                <!-- Tagline -->
                <div>
                    <label for="tagline" class="flex items-center gap-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5 font-mono">
                        <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <span>Tagline: 1 Kalimat Kenapa Codingan Ini Keren / Solutif</span>
                        <span class="text-orange-500">*</span>
                    </label>
                    <input type="text" name="tagline" id="tagline" required
                           value="{{ old('tagline') }}"
                           placeholder="Jelaskan fungsi utama codingan ini dalam 1 kalimat padat dan menarik" 
                           class="w-full px-4 py-2.5 text-xs sm:text-sm bg-neutral-50 dark:bg-[#111114] border border-neutral-300 dark:border-white/[0.08] rounded-xl focus:border-orange-500 text-neutral-900 dark:text-white">
                </div>

                <!-- Category & Tech Stacks Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Category Select -->
                    <div>
                        <label for="category_id" class="flex items-center gap-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5 font-mono">
                            <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <span>Kategori Codingan</span>
                            <span class="text-orange-500">*</span>
                        </label>
                        <select name="category_id" id="category_id" required
                                class="w-full px-4 py-2.5 text-xs sm:text-sm bg-neutral-50 dark:bg-[#111114] border border-neutral-300 dark:border-white/[0.08] rounded-xl focus:border-orange-500 text-neutral-900 dark:text-white">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tech Stacks -->
                    <div>
                        <label for="tech_stacks" class="flex items-center gap-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5 font-mono">
                            <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                            <span>Tech Stacks (Pisahkan koma)</span>
                            <span class="text-orange-500">*</span>
                        </label>
                        <input type="text" name="tech_stacks" id="tech_stacks" required
                               value="{{ old('tech_stacks') }}"
                               placeholder="Laravel 12, Vue 3, TailwindCSS, Go, Docker" 
                               class="w-full px-4 py-2.5 text-xs sm:text-sm bg-neutral-50 dark:bg-[#111114] border border-neutral-300 dark:border-white/[0.08] rounded-xl focus:border-orange-500 text-neutral-900 dark:text-white font-mono">
                    </div>
                </div>

                <!-- URLs: Demo, GitHub, & Prototype -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="demo_url" class="flex items-center gap-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5 font-mono">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            <span>Live Demo (Opsional)</span>
                        </label>
                        <input type="url" name="demo_url" id="demo_url"
                               value="{{ old('demo_url') }}"
                               placeholder="https://projectkamu.com" 
                               class="w-full px-4 py-2.5 text-xs sm:text-sm bg-neutral-50 dark:bg-[#111114] border border-neutral-300 dark:border-white/[0.08] rounded-xl focus:border-orange-500 text-neutral-900 dark:text-white">
                    </div>

                    <div>
                        <label for="github_url" class="flex items-center gap-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5 font-mono">
                            <svg class="w-3.5 h-3.5 text-neutral-400" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                            </svg>
                            <span>Repo GitHub (Opsional)</span>
                        </label>
                        <input type="url" name="github_url" id="github_url"
                               value="{{ old('github_url') }}"
                               placeholder="https://github.com/..." 
                               class="w-full px-4 py-2.5 text-xs sm:text-sm bg-neutral-50 dark:bg-[#111114] border border-neutral-300 dark:border-white/[0.08] rounded-xl focus:border-orange-500 text-neutral-900 dark:text-white">
                    </div>

                    <div>
                        <label for="prototype_url" class="flex items-center gap-1.5 text-xs font-semibold text-purple-600 dark:text-purple-400 mb-1.5 font-mono">
                            <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                            </svg>
                            <span>Link Prototype (Opsional)</span>
                        </label>
                        <input type="url" name="prototype_url" id="prototype_url"
                               value="{{ old('prototype_url') }}"
                               placeholder="https://figma.com/proto/... atau framer" 
                               class="w-full px-4 py-2.5 text-xs sm:text-sm bg-neutral-50 dark:bg-[#111114] border border-neutral-300 dark:border-white/[0.08] rounded-xl focus:border-purple-500 text-neutral-900 dark:text-white">
                    </div>
                </div>

                <!-- Thumbnail Upload or URL -->
                <div class="p-4 rounded-xl bg-neutral-50 dark:bg-[#111114] border border-neutral-200 dark:border-white/[0.08] space-y-3">
                    <span class="flex items-center gap-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300 font-mono">
                        <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Screenshot Aplikasi / Output Terminal (Opsional)</span>
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <span class="block text-[11px] text-neutral-400 mb-1 font-mono">Upload file screenshot:</span>
                            <input type="file" name="thumbnail" accept="image/*"
                                   class="text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-neutral-200 dark:file:bg-white/10 file:text-neutral-900 dark:file:text-white hover:file:bg-neutral-300 font-mono">
                        </div>
                        <div>
                            <span class="block text-[11px] text-neutral-400 mb-1 font-mono">Atau masukkan URL gambar:</span>
                            <input type="url" name="thumbnail_url" 
                                   value="{{ old('thumbnail_url') }}"
                                   placeholder="https://images.unsplash.com/..." 
                                   class="w-full px-3 py-1.5 text-xs bg-white dark:bg-[#09090b] border border-neutral-300 dark:border-white/[0.08] rounded-lg focus:border-orange-500 text-neutral-900 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Long Description / Markdown -->
                <div>
                    <label for="description" class="flex items-center gap-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5 font-mono">
                        <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Bedah Arsitektur & Catatan Teknis (Fitur Utama & Cara Install)</span>
                    </label>
                    <textarea name="description" id="description" rows="6"
                              placeholder="Ceritakan fitur unggulan, arsitektur kode yang kamu terapkan, tantangan saat ngoding, atau command cara menjalankannya..."
                              class="w-full px-4 py-3 text-xs sm:text-sm bg-neutral-50 dark:bg-[#111114] border border-neutral-300 dark:border-white/[0.08] rounded-xl focus:border-orange-500 text-neutral-900 dark:text-white placeholder-neutral-400 leading-relaxed font-mono">{{ old('description') }}</textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-neutral-100 dark:border-white/[0.08]">
                    <a href="{{ route('projects.index') }}" 
                       class="px-4 py-2 text-xs text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 font-mono transition">
                        Batal
                    </a>
                    <button type="submit" 
                            class="btn-vercel-primary inline-flex items-center gap-2 px-6 py-2.5 text-xs sm:text-sm font-semibold rounded-xl shadow-xs active:scale-95 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span>Publikasikan Karya Codingan</span>
                    </button>
                </div>

            </form>
        </div>

</div>
@endsection
