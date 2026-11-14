@extends('layouts.app')

@section('title', 'Pamerkan Karya Codingan — NampungYuk')
@section('meta_description', 'Bagikan karya project codingan kamu kepada komunitas developer.')
@section('header_title', 'Pamerkan Karya')

@section('content')
<div class="space-y-4 max-w-2xl mx-auto"
     x-data="{
         title: @js(old('title', '')),
         tagline: @js(old('tagline', '')),
         categoryId: @js(old('category_id', '')),
         projectType: @js(old('project_type', 'web')),
         status: @js(old('status', 'beta')),
         techInput: '',
         techList: @js(old('tech_stacks') ? array_filter(array_map('trim', explode(',', old('tech_stacks')))) : ['Laravel', 'TailwindCSS']),
         previewImage: '',
         showLinks: {{ old('demo_url') || old('github_url') ? 'true' : 'false' }},

         handleFileSelect(event) {
             const file = event.target.files[0];
             if (file) {
                 this.previewImage = URL.createObjectURL(file);
             } else {
                 this.previewImage = '';
             }
         },
         removeThumbnail() {
             this.previewImage = '';
             const input = document.getElementById('thumbnail_input');
             if (input) input.value = '';
         },
         addTech(tech) {
             const t = (tech || this.techInput).trim();
             if (t && !this.techList.includes(t)) {
                 this.techList.push(t);
             }
             this.techInput = '';
         },
         removeTech(index) {
             this.techList.splice(index, 1);
         },
         get techStacksJoined() {
             return this.techList.join(', ');
         },
         submitForm(e) {
             if (!this.title.trim() || this.title.trim().length < 2) {
                 if (window.notify) notify('Judul project wajib diisi (minimal 2 karakter).');
                 document.getElementById('title')?.focus();
                 return;
             }
             if (!this.tagline.trim() || this.tagline.trim().length < 3) {
                 if (window.notify) notify('Tagline project wajib diisi (minimal 3 karakter).');
                 document.getElementById('tagline')?.focus();
                 return;
             }
             if (!this.categoryId) {
                 if (window.notify) notify('Pilih kategori project terlebih dahulu.');
                 document.getElementById('category_id')?.focus();
                 return;
             }
             if (!this.techList.length) {
                 if (window.notify) notify('Minimal sertakan 1 teknologi (tech stack).');
                 document.getElementById('tech_input')?.focus();
                 return;
             }
             e.target.submit();
         }
     }">

    <!-- Back to Feed Link -->
    <a href="{{ route('projects.index') }}" 
       class="inline-flex items-center gap-1.5 text-xs text-[var(--text-muted)] hover:text-[var(--primary)] transition font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke Feed</span>
    </a>

    <!-- Error Summary if any -->
    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 text-xs space-y-1" role="alert">
            <p class="font-bold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Mohon periksa data berikut:</span>
            </p>
            <ul class="list-disc list-inside ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Quick Twitter-style Composer Card -->
    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 sm:p-6 shadow-xs">
        <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" novalidate @submit.prevent="submitForm($event)" class="space-y-4">
            @csrf

            <!-- Hidden inputs -->
            <input type="hidden" name="tech_stacks" :value="techStacksJoined">

            <!-- Author header & visibility row -->
            <div class="flex items-center justify-between pb-3 border-b border-[var(--border-color)]">
                <div class="flex items-center gap-3 min-w-0">
                    <x-user-avatar :user="Auth::user()" size="sm" />
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-[var(--primary)] truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[11px] font-mono text-[var(--text-muted)] truncate">{{ '@' . Auth::user()->username }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <select name="visibility" class="text-[11px] font-medium rounded-lg bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)] px-2.5 py-1 outline-none">
                        <option value="public" {{ old('visibility') === 'public' ? 'selected' : '' }}>Publik</option>
                        <option value="private" {{ old('visibility') === 'private' ? 'selected' : '' }}>Privat</option>
                    </select>

                    <select name="status" x-model="status" class="text-[11px] font-medium rounded-lg bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)] px-2.5 py-1 outline-none">
                        <option value="beta">Beta</option>
                        <option value="production">Rilis Publik</option>
                        <option value="prototype">Prototipe</option>
                        <option value="idea">Ide</option>
                    </select>
                </div>
            </div>

            <!-- Title Input -->
            <div class="space-y-1">
                <input type="text" 
                       name="title" 
                       id="title" 
                       required 
                       maxlength="150"
                       x-model="title"
                       placeholder="Apa nama karya codinganmu?" 
                       class="composer-seamless w-full text-base sm:text-lg font-bold text-[var(--primary)] placeholder:text-[var(--text-muted)] bg-transparent border-0 border-none focus:border-none focus:outline-none focus:ring-0 px-0 outline-none"
                       style="border: none !important; outline: none !important; box-shadow: none !important;">
                @error('title') <p class="text-rose-500 text-[11px]">{{ $message }}</p> @enderror
            </div>

            <!-- Tagline Input -->
            <div class="space-y-1">
                <textarea name="tagline" 
                          id="tagline" 
                          required 
                          maxlength="255"
                          rows="2"
                          x-model="tagline"
                          placeholder="Ceritakan ringkas fungsi atau keunggulannya dalam 1-2 kalimat (tagline)..." 
                          class="composer-seamless w-full text-xs sm:text-sm text-[var(--primary)] placeholder:text-[var(--text-muted)] bg-transparent border-0 border-none focus:border-none focus:outline-none focus:ring-0 px-0 outline-none resize-none leading-relaxed"
                          style="border: none !important; outline: none !important; box-shadow: none !important;"></textarea>
                @error('tagline') <p class="text-rose-500 text-[11px]">{{ $message }}</p> @enderror
            </div>

            <!-- Thumbnail Preview if attached -->
            <template x-if="previewImage">
                <div class="relative w-full aspect-video max-h-[260px] rounded-xl overflow-hidden border border-[var(--border-color)] bg-[var(--surface-deep)]">
                    <img :src="previewImage" alt="Preview Thumbnail" class="w-full h-full object-cover">
                    <button type="button" 
                            @click="removeThumbnail()"
                            class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-black/75 hover:bg-black text-white flex items-center justify-center transition cursor-pointer"
                            title="Hapus media">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>

            <!-- Metadata Row: Category & Type -->
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-[var(--border-color)]">
                <!-- Category Select -->
                <div class="inline-flex items-center gap-1.5">
                    <label for="category_id" class="text-xs text-[var(--text-muted)] font-medium">Kategori <span class="text-rose-500">*</span>:</label>
                    <select name="category_id" 
                            id="category_id" 
                            required 
                            x-model="categoryId"
                            class="text-xs rounded-lg bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)] px-2.5 py-1.5 outline-none focus:border-[var(--primary)] transition">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Project Type -->
                <div class="inline-flex items-center gap-1.5">
                    <label for="project_type" class="text-xs text-[var(--text-muted)] font-medium">Tipe:</label>
                    <select name="project_type" 
                            id="project_type" 
                            x-model="projectType"
                            class="text-xs rounded-lg bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)] px-2.5 py-1.5 outline-none focus:border-[var(--primary)] transition">
                        <option value="web">Web App</option>
                        <option value="mobile">Mobile App</option>
                        <option value="developer_tool">Developer Tool / CLI</option>
                        <option value="library_package">Library / Package</option>
                        <option value="game">Game</option>
                        <option value="hardware_iot">Hardware / IoT</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
            </div>

            <!-- Tech Stack Row -->
            <div class="space-y-2 pt-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-[var(--text-muted)] font-medium">Tech Stack <span class="text-rose-500">*</span>:</span>
                    <span class="text-[11px] font-mono text-[var(--text-faint)]" x-text="techList.length + ' dipilih'"></span>
                </div>

                <!-- Chips container -->
                <div class="flex flex-wrap items-center gap-1.5">
                    <template x-for="(tech, index) in techList" :key="index">
                        <span class="inline-flex items-center gap-1 font-mono text-xs px-2.5 py-1 rounded-md bg-[#161619] dark:bg-[#19191D] text-[#EAE8E1] border border-[#242426]">
                            <span x-text="'#' + tech"></span>
                            <button @click="removeTech(index)" type="button" class="text-[var(--text-muted)] hover:text-rose-500 transition cursor-pointer ml-0.5" aria-label="Hapus stack">&times;</button>
                        </span>
                    </template>

                    <!-- Quick Input -->
                    <div class="inline-flex items-center gap-1">
                        <input type="text" 
                               id="tech_input"
                               x-model="techInput"
                               @keydown.enter.prevent="addTech()"
                               placeholder="+ Tambah stack..." 
                               class="text-xs px-2.5 py-1 rounded-md bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] outline-none focus:border-[#EAE8E1] font-mono w-36">
                        <button type="button" @click="addTech()" class="text-xs px-2 py-1 rounded-md bg-[var(--surface-muted)] text-[var(--primary)] hover:bg-[var(--surface-hover)] border border-[var(--border-color)] cursor-pointer">
                            +
                        </button>
                    </div>
                </div>

                <!-- Popular Suggestions -->
                <div class="flex flex-wrap items-center gap-1 pt-0.5">
                    <span class="text-[10px] font-mono text-[var(--text-muted)] mr-1">Cepat:</span>
                    @foreach(['Laravel', 'Vue.js', 'React', 'TailwindCSS', 'TypeScript', 'Next.js', 'Python', 'Go', 'Flutter', 'PostgreSQL', 'Docker'] as $suggestion)
                        <button @click="addTech('{{ $suggestion }}')" 
                                type="button" 
                                class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-[var(--surface-deep)] hover:bg-[var(--surface-muted)] text-[var(--text-muted)] hover:text-[var(--primary)] border border-[var(--border-color)] transition cursor-pointer">
                            + {{ $suggestion }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Expandable Links (Live Demo & GitHub) -->
            <div x-show="showLinks" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <div class="space-y-1">
                    <label for="demo_url" class="text-xs text-[var(--text-muted)] flex items-center gap-1 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Live Demo (opsional)</span>
                    </label>
                    <input type="url" name="demo_url" id="demo_url" value="{{ old('demo_url') }}" placeholder="https://projectmu.com"
                           class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs outline-none focus:border-[#EAE8E1]">
                </div>

                <div class="space-y-1">
                    <label for="github_url" class="text-xs text-[var(--text-muted)] flex items-center gap-1 font-medium">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                        <span>GitHub Repo (opsional)</span>
                    </label>
                    <input type="url" name="github_url" id="github_url" value="{{ old('github_url') }}" placeholder="https://github.com/user/repo"
                           class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs outline-none focus:border-[#EAE8E1]">
                </div>
            </div>

            <!-- Optional Knowledge & Setup collapsible details -->
            <details class="group pt-2 border-t border-[var(--border-color)]">
                <summary class="text-xs text-[var(--text-muted)] hover:text-[var(--primary)] cursor-pointer select-none flex items-center justify-between py-1 transition">
                    <span class="font-medium">+ Tambah Cerita Teknis & Cara Setup (opsional)</span>
                    <svg class="w-4 h-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="space-y-3 pt-3">
                    <div class="space-y-1">
                        <label for="description" class="text-xs text-[var(--text-muted)] font-medium">Deskripsi Lengkap / Fitur</label>
                        <textarea name="description" id="description" rows="3" placeholder="Ceritakan fitur utama atau arsitekturnya..."
                                  class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs outline-none focus:border-[#EAE8E1]">{{ old('description') }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label for="challenges" class="text-xs text-[var(--text-muted)] font-medium">Tantangan Teknis</label>
                            <textarea name="challenges" id="challenges" rows="2" placeholder="Masalah tersulit yang ditemui..."
                                      class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs outline-none focus:border-[#EAE8E1]">{{ old('challenges') }}</textarea>
                        </div>
                        <div class="space-y-1">
                            <label for="learnings" class="text-xs text-[var(--text-muted)] font-medium">Pelajaran Berharga</label>
                            <textarea name="learnings" id="learnings" rows="2" placeholder="Solusi atau hal baru yang dipelajari..."
                                      class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs outline-none focus:border-[#EAE8E1]">{{ old('learnings') }}</textarea>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label for="setup_instructions" class="text-xs text-[var(--text-muted)] font-medium">Cara Run di Lokal</label>
                        <textarea name="setup_instructions" id="setup_instructions" rows="2" placeholder="git clone ...&#10;composer install&#10;npm run dev"
                                  class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] font-mono text-xs outline-none focus:border-[#EAE8E1]">{{ old('setup_instructions') }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div class="space-y-1">
                            <label for="prototype_url" class="text-xs text-[var(--text-muted)] font-medium">Figma / Prototype URL</label>
                            <input type="url" name="prototype_url" id="prototype_url" value="{{ old('prototype_url') }}" placeholder="https://figma.com/proto/..."
                                   class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs outline-none focus:border-[#EAE8E1]">
                        </div>
                        <div class="space-y-1">
                            <label for="thumbnail_url" class="text-xs text-[var(--text-muted)] font-medium">Atau URL Gambar Eksternal</label>
                            <input type="url" name="thumbnail_url" id="thumbnail_url" value="{{ old('thumbnail_url') }}" placeholder="https://images.unsplash.com/..."
                                   class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs outline-none focus:border-[#EAE8E1]">
                        </div>
                    </div>
                </div>
            </details>

            <!-- Bottom Toolbar: Attachments + Submit -->
            <div class="flex items-center justify-between pt-3 border-t border-[var(--border-color)]">
                <div class="flex items-center gap-1">
                    <!-- Upload Media Button -->
                    <label class="p-2 rounded-lg text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-muted)] transition cursor-pointer flex items-center gap-1.5 text-xs select-none" title="Unggah tangkapan layar / thumbnail">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="hidden sm:inline font-medium">Foto / Media</span>
                        <input type="file" id="thumbnail_input" name="thumbnail" accept="image/*" @change="handleFileSelect($event)" class="hidden">
                    </label>

                    <!-- Toggle Links Button -->
                    <button type="button" @click="showLinks = !showLinks" 
                            :class="showLinks ? 'text-[var(--primary)] bg-[var(--surface-muted)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-muted)]'"
                            class="p-2 rounded-lg transition cursor-pointer flex items-center gap-1.5 text-xs select-none" title="Tautkan Demo / GitHub">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <span class="hidden sm:inline font-medium">Link Demo</span>
                    </button>
                </div>

                <x-button type="submit" variant="primary" size="md">
                    <span>Pamerkan Karya</span>
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection
