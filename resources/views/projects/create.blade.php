@extends('layouts.app')

@section('title', 'Pamerkan Karya Codingan — NampungYuk')
@section('meta_description', 'Bagikan karya project codingan kamu kepada komunitas developer. Jelaskan arsitektur, tantangan, dan apa yang kamu pelajari.')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto"
     x-data="{
         currentStep: 1,
         totalSteps: 5,
         title: '{{ old('title', '') }}',
         tagline: '{{ old('tagline', '') }}',
         categoryId: '{{ old('category_id', '') }}',
         categoryName: '',
         projectType: '{{ old('project_type', 'web') }}',
         status: '{{ old('status', 'beta') }}',
         thumbnailUrl: '{{ old('thumbnail_url', '') }}',
         previewImage: '',
         demoUrl: '{{ old('demo_url', '') }}',
         githubUrl: '{{ old('github_url', '') }}',
         prototypeUrl: '{{ old('prototype_url', '') }}',
         techInput: '',
         techList: {{ json_encode(old('tech_stacks') ? array_filter(array_map('trim', explode(',', old('tech_stacks')))) : ['Laravel', 'TailwindCSS']) }},
         description: `{{ old('description', '') }}`,
         challenges: `{{ old('challenges', '') }}`,
         learnings: `{{ old('learnings', '') }}`,
         setupInstructions: `{{ old('setup_instructions', '') }}`,

         handleFileSelect(event) {
             const file = event.target.files[0];
             if (file) {
                 this.previewImage = URL.createObjectURL(file);
             } else {
                 this.previewImage = '';
             }
         },
         get currentThumbnail() {
             return this.previewImage || this.thumbnailUrl || 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80';
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
         canProceed(step) {
             if (step === 1) {
                 return this.title.trim().length >= 2 && this.tagline.trim().length >= 3 && this.categoryId !== '';
             }
             if (step === 3) {
                 return this.techList.length > 0;
             }
             return true;
         },
         goToStep(step) {
             if (step > this.currentStep && !this.canProceed(this.currentStep)) {
                 if (window.notify) {
                     if (this.currentStep === 1) {
                         if (!this.title.trim() || this.title.trim().length < 2) notify('Judul project minimal 2 karakter.');
                         else if (!this.tagline.trim() || this.tagline.trim().length < 3) notify('Tagline project minimal 3 karakter.');
                         else if (!this.categoryId) notify('Pilih kategori project terlebih dahulu.');
                     } else if (this.currentStep === 3) {
                         notify('Minimal sertakan 1 teknologi (tech stack).');
                     } else {
                         notify('Lengkapi kolom wajib terlebih dahulu.');
                     }
                 }
                 return;
             }
             this.currentStep = Math.min(Math.max(step, 1), this.totalSteps);
             window.scrollTo({ top: 120, behavior: 'smooth' });
         },
         submitForm(e) {
             if (!this.title.trim() || this.title.trim().length < 2) {
                 this.goToStep(1);
                 if (window.notify) notify('Judul project wajib diisi (minimal 2 karakter).');
                 return;
             }
             if (!this.tagline.trim() || this.tagline.trim().length < 3) {
                 this.goToStep(1);
                 if (window.notify) notify('Tagline project wajib diisi (minimal 3 karakter).');
                 return;
             }
             if (!this.categoryId) {
                 this.goToStep(1);
                 if (window.notify) notify('Pilih kategori project terlebih dahulu.');
                 return;
             }
             if (!this.techList.length) {
                 this.goToStep(3);
                 if (window.notify) notify('Minimal sertakan 1 teknologi (tech stack).');
                 return;
             }
             e.target.submit();
         }
     }">

    <!-- Back to Feed Link -->
    <a href="{{ route('projects.index') }}" 
       class="inline-flex items-center gap-1.5 text-xs text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#0e9c8b] transition font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke Feed</span>
    </a>

    <!-- Header Section -->
    <div class="space-y-1">
        <h1 class="text-xl sm:text-2xl font-extrabold text-[#10161f] dark:text-[#eaecf0] tracking-tight">
            Pamerkan Karya Codingan
        </h1>
        <p class="text-xs sm:text-sm text-[#5c6979] dark:text-[#7e8a9a] leading-relaxed">
            Dokumentasikan karya digitalmu, bagikan teknologi yang digunakan, tantangan yang dihadapi, serta pelajaran berharga untuk developer lain.
        </p>
    </div>

    <!-- Progressive Stepper Header -->
    <nav class="ny-card p-3 bg-white dark:bg-[#141821] border border-[#d5dbe2] dark:border-[#262d3a]" aria-label="Tahapan Formulir">
        <div class="grid grid-cols-5 gap-1 sm:gap-2 text-center text-xs">
            
            <button @click="goToStep(1)" type="button" 
                    :class="{ 'text-[#0e9c8b] dark:text-[#50d2c1] font-bold border-b-2 border-[#0e9c8b] dark:border-[#50d2c1]': currentStep === 1, 'text-[#5c6979] dark:text-[#7e8a9a]': currentStep !== 1 }"
                    class="py-2 flex flex-col sm:flex-row items-center justify-center gap-1 transition">
                <span class="font-mono text-[10px] sm:text-xs">01</span>
                <span class="hidden sm:inline">Project</span>
            </button>

            <button @click="goToStep(2)" type="button" 
                    :class="{ 'text-[#0e9c8b] dark:text-[#50d2c1] font-bold border-b-2 border-[#0e9c8b] dark:border-[#50d2c1]': currentStep === 2, 'text-[#5c6979] dark:text-[#7e8a9a]': currentStep !== 2 }"
                    class="py-2 flex flex-col sm:flex-row items-center justify-center gap-1 transition">
                <span class="font-mono text-[10px] sm:text-xs">02</span>
                <span class="hidden sm:inline">Showcase</span>
            </button>

            <button @click="goToStep(3)" type="button" 
                    :class="{ 'text-[#0e9c8b] dark:text-[#50d2c1] font-bold border-b-2 border-[#0e9c8b] dark:border-[#50d2c1]': currentStep === 3, 'text-[#5c6979] dark:text-[#7e8a9a]': currentStep !== 3 }"
                    class="py-2 flex flex-col sm:flex-row items-center justify-center gap-1 transition">
                <span class="font-mono text-[10px] sm:text-xs">03</span>
                <span class="hidden sm:inline">Tech</span>
            </button>

            <button @click="goToStep(4)" type="button" 
                    :class="{ 'text-[#0e9c8b] dark:text-[#50d2c1] font-bold border-b-2 border-[#0e9c8b] dark:border-[#50d2c1]': currentStep === 4, 'text-[#5c6979] dark:text-[#7e8a9a]': currentStep !== 4 }"
                    class="py-2 flex flex-col sm:flex-row items-center justify-center gap-1 transition">
                <span class="font-mono text-[10px] sm:text-xs">04</span>
                <span class="hidden sm:inline">Knowledge</span>
            </button>

            <button @click="goToStep(5)" type="button" 
                    :class="{ 'text-[#0e9c8b] dark:text-[#50d2c1] font-bold border-b-2 border-[#0e9c8b] dark:border-[#50d2c1]': currentStep === 5, 'text-[#5c6979] dark:text-[#7e8a9a]': currentStep !== 5 }"
                    class="py-2 flex flex-col sm:flex-row items-center justify-center gap-1 transition">
                <span class="font-mono text-[10px] sm:text-xs">05</span>
                <span class="hidden sm:inline">Preview</span>
            </button>

        </div>
    </nav>

    <!-- Error Summary if any -->
    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 text-xs space-y-1.5" role="alert">
            <p class="font-bold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Mohon periksa input berikut:</span>
            </p>
            <ul class="list-disc list-inside ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Upload Form -->
    <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" novalidate @submit.prevent="submitForm($event)" class="space-y-6">
        @csrf

        <!-- Hidden input for tech_stacks bound to Alpine -->
        <input type="hidden" name="tech_stacks" :value="techStacksJoined">

        <!-- ============================================================
             SECTION 01: PROJECT IDENTITY
             ============================================================ -->
        <div x-show="currentStep === 1" class="ny-card p-6 sm:p-8 space-y-5 bg-white dark:bg-[#141821] border border-[#d5dbe2] dark:border-[#262d3a]">
            <div class="border-b border-[#d5dbe2] dark:border-[#262d3a] pb-3">
                <span class="text-[11px] font-mono font-bold text-[#0e9c8b] dark:text-[#50d2c1]">LANGKAH 01 DARI 05</span>
                <h2 class="text-base sm:text-lg font-bold text-[#10161f] dark:text-[#eaecf0]">Identitas Project</h2>
                <p class="text-xs text-[#5c6979] dark:text-[#7e8a9a]">Tentukan nama, pesan utama, dan kategori karya codinganmu.</p>
            </div>

            <!-- Title -->
            <div class="space-y-1.5">
                <label for="title" class="flex items-center gap-1 text-xs font-semibold text-[#10161f] dark:text-[#eaecf0]">
                    <span>Judul Project</span>
                    <span class="text-rose-500 font-bold">*</span>
                </label>
                <input type="text" 
                       name="title" 
                       id="title" 
                       required 
                       maxlength="150"
                       x-model="title"
                       placeholder="Contoh: KetikCepat.id — Platform Belajar Mengetik Cepat" 
                       class="ny-input font-medium text-sm">
                <p class="text-[11px] text-[#5c6979] dark:text-[#7e8a9a]">Nama karya yang jelas dan mudah diingat.</p>
            </div>

            <!-- Tagline -->
            <div class="space-y-1.5">
                <label for="tagline" class="flex items-center gap-1 text-xs font-semibold text-[#10161f] dark:text-[#eaecf0]">
                    <span>Tagline</span>
                    <span class="text-rose-500 font-bold">*</span>
                </label>
                <input type="text" 
                       name="tagline" 
                       id="tagline" 
                       required 
                       maxlength="255"
                       x-model="tagline"
                       placeholder="Contoh: Platform web open-source untuk melatih kecepatan mengetik dengan korpus Bahasa Indonesia" 
                       class="ny-input text-xs sm:text-sm">
                <p class="text-[11px] text-[#5c6979] dark:text-[#7e8a9a]">Jelaskan fungsi atau nilai utama project dalam 1 kalimat ringkas.</p>
            </div>

            <!-- Category, Type, & Status Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                
                <!-- Category -->
                <div class="space-y-1.5">
                    <label for="category_id" class="flex items-center gap-1 text-xs font-semibold text-[#10161f] dark:text-[#eaecf0]">
                        <span>Kategori</span>
                        <span class="text-rose-500 font-bold">*</span>
                    </label>
                    <select name="category_id" 
                            id="category_id" 
                            required 
                            x-model="categoryId"
                            class="ny-input text-xs">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Project Type -->
                <div class="space-y-1.5">
                    <label for="project_type" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0]">
                        Tipe Platform
                    </label>
                    <select name="project_type" 
                            id="project_type" 
                            x-model="projectType"
                            class="ny-input text-xs">
                        <option value="web">Web Application</option>
                        <option value="mobile">Mobile Application</option>
                        <option value="cli">CLI / Developer Tool</option>
                        <option value="library">Library / Package</option>
                        <option value="desktop">Desktop Application</option>
                        <option value="game">Game</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="space-y-1.5">
                    <label for="status" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0]">
                        Status Pengembangan
                    </label>
                    <select name="status" 
                            id="status" 
                            x-model="status"
                            class="ny-input text-xs">
                        <option value="idea">Konsep / Ide</option>
                        <option value="prototype">Prototipe</option>
                        <option value="beta">Versi Beta</option>
                        <option value="production">Rilis Publik (Live)</option>
                        <option value="archived">Arsip</option>
                    </select>
                </div>

            </div>

            <!-- Stepper Actions -->
            <div class="flex justify-end pt-4 border-t border-[#d5dbe2] dark:border-[#262d3a]">
                <x-button @click="goToStep(2)" type="button" variant="primary" size="md">
                    <span>Lanjut: Showcase</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </x-button>
            </div>
        </div>

        <!-- ============================================================
             SECTION 02: SHOWCASE & LINKS
             ============================================================ -->
        <div x-show="currentStep === 2" class="ny-card p-6 sm:p-8 space-y-5 bg-white dark:bg-[#141821] border border-[#d5dbe2] dark:border-[#262d3a]">
            <div class="border-b border-[#d5dbe2] dark:border-[#262d3a] pb-3">
                <span class="text-[11px] font-mono font-bold text-[#0e9c8b] dark:text-[#50d2c1]">LANGKAH 02 DARI 05</span>
                <h2 class="text-base sm:text-lg font-bold text-[#10161f] dark:text-[#eaecf0]">Visual Showcase & Tautan</h2>
                <p class="text-xs text-[#5c6979] dark:text-[#7e8a9a]">Tautkan demo aplikasi, repositori kode, atau tangkapan layar antarmuka.</p>
            </div>

            <!-- Thumbnail Upload or URL -->
            <div class="space-y-3">
                <label class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0]">
                    Tangkapan Layar / Thumbnail Project
                </label>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- File Upload -->
                    <div class="p-4 rounded-xl border border-dashed border-[#d5dbe2] dark:border-[#262d3a] text-center space-y-2 bg-[#eceff1] dark:bg-[#0b0e11]">
                        <template x-if="previewImage">
                            <div class="relative w-full aspect-video max-h-[160px] rounded-lg overflow-hidden border border-[#d5dbe2] dark:border-[#262d3a] mb-2 mx-auto">
                                <img :src="previewImage" alt="Preview Thumbnail" class="w-full h-full object-cover">
                            </div>
                        </template>
                        <template x-if="!previewImage">
                            <svg class="w-8 h-8 text-[#5c6979] mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </template>
                        <p class="text-xs text-[#5c6979]">Unggah file gambar (PNG, JPG, WebP - maks 4MB)</p>
                        <input type="file" name="thumbnail" accept="image/*" @change="handleFileSelect($event)" class="text-xs text-[#5c6979] w-full">
                    </div>

                    <!-- URL Alternative -->
                    <div class="space-y-1.5">
                        <label for="thumbnail_url" class="text-xs text-[#5c6979] dark:text-[#7e8a9a]">
                            Atau gunakan URL Gambar:
                        </label>
                        <input type="url" 
                               name="thumbnail_url" 
                               id="thumbnail_url"
                               x-model="thumbnailUrl"
                               placeholder="https://images.unsplash.com/..." 
                               class="ny-input text-xs">
                        <p class="text-[11px] text-[#5c6979]">Jika dikosongkan, gambar ilustrasi developer otomatis digunakan.</p>
                        <template x-if="thumbnailUrl && !previewImage">
                            <div class="relative w-full aspect-video max-h-[120px] rounded-lg overflow-hidden border border-[#d5dbe2] dark:border-[#262d3a] mt-2">
                                <img :src="thumbnailUrl" alt="Preview URL" class="w-full h-full object-cover" onerror="this.parentElement.classList.add('hidden')">
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- External Links: Live Demo, GitHub, Prototype -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                
                <!-- Demo URL -->
                <div class="space-y-1.5">
                    <label for="demo_url" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Live Demo (Opsional)</span>
                    </label>
                    <input type="url" 
                           name="demo_url" 
                           id="demo_url"
                           x-model="demoUrl"
                           placeholder="https://projectkamu.com" 
                           class="ny-input text-xs">
                </div>

                <!-- GitHub URL -->
                <div class="space-y-1.5">
                    <label for="github_url" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0] flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                        </svg>
                        <span>GitHub Repo (Opsional)</span>
                    </label>
                    <input type="url" 
                           name="github_url" 
                           id="github_url"
                           x-model="githubUrl"
                           placeholder="https://github.com/user/repo" 
                           class="ny-input text-xs">
                </div>

                <!-- Prototype URL -->
                <div class="space-y-1.5">
                    <label for="prototype_url" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0] flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        <span>Figma / Prototype (Opsional)</span>
                    </label>
                    <input type="url" 
                           name="prototype_url" 
                           id="prototype_url"
                           x-model="prototypeUrl"
                           placeholder="https://figma.com/proto/..." 
                           class="ny-input text-xs">
                </div>

            </div>

            <!-- Stepper Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-[#d5dbe2] dark:border-[#262d3a]">
                <x-button @click="goToStep(1)" type="button" variant="secondary" size="md">
                    &larr; Kembali
                </x-button>
                <x-button @click="goToStep(3)" type="button" variant="primary" size="md">
                    <span>Lanjut: Technology</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </x-button>
            </div>
        </div>

        <!-- ============================================================
             SECTION 03: TECHNOLOGY
             ============================================================ -->
        <div x-show="currentStep === 3" class="ny-card p-6 sm:p-8 space-y-5 bg-white dark:bg-[#141821] border border-[#d5dbe2] dark:border-[#262d3a]">
            <div class="border-b border-[#d5dbe2] dark:border-[#262d3a] pb-3">
                <span class="text-[11px] font-mono font-bold text-[#0e9c8b] dark:text-[#50d2c1]">LANGKAH 03 DARI 05</span>
                <h2 class="text-base sm:text-lg font-bold text-[#10161f] dark:text-[#eaecf0]">Tech Stacks & Tools</h2>
                <p class="text-xs text-[#5c6979] dark:text-[#7e8a9a]">Tentukan bahasa pemrograman, framework, dan tools yang dipakai.</p>
            </div>

            <div class="space-y-3">
                <!-- Tag Input Box -->
                <div class="space-y-1.5">
                    <label for="tech_input" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0]">
                        Ketik nama teknologi lalu tekan Enter atau klik Tambah:
                    </label>
                    <div class="flex gap-2">
                        <input type="text" 
                               id="tech_input"
                               x-model="techInput"
                               @keydown.enter.prevent="addTech()"
                               placeholder="Contoh: Laravel, Vue, TailwindCSS, PostgreSQL, Docker..." 
                               class="ny-input font-mono text-xs">
                        <x-button @click="addTech()" type="button" variant="secondary" size="sm" class="shrink-0">
                            Tambah
                        </x-button>
                    </div>
                </div>

                <!-- Active Chips -->
                <div class="p-3 rounded-xl bg-[#eceff1] dark:bg-[#0b0e11] border border-[#d5dbe2] dark:border-[#262d3a] min-h-[52px] flex items-center flex-wrap gap-2">
                    <template x-for="(tech, index) in techList" :key="index">
                        <span class="inline-flex items-center gap-1.5 font-mono text-xs px-2.5 py-1 rounded-md bg-[#d9fbf4] dark:bg-[#50d2c1]/12 text-[#0e9c8b] dark:text-[#50d2c1] border border-[#50d2c1]/30 dark:border-[#50d2c1]/30">
                            <span x-text="tech"></span>
                            <button @click="removeTech(index)" type="button" class="hover:text-rose-600 transition" aria-label="Hapus tag">&times;</button>
                        </span>
                    </template>
                    <p x-show="techList.length === 0" class="text-xs text-[#5c6979] italic">
                        Belum ada teknologi ditambahkan. Minimal sertakan 1 tech stack.
                    </p>
                </div>

                <!-- Quick Suggestions -->
                <div class="space-y-1.5 pt-2">
                    <span class="text-[11px] font-mono text-[#5c6979] dark:text-[#7e8a9a]">Pilihan Populer:</span>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['Laravel', 'Vue.js', 'React', 'TailwindCSS', 'TypeScript', 'Next.js', 'Python', 'Go', 'Flutter', 'PostgreSQL', 'MySQL', 'Docker'] as $suggestion)
                            <button @click="addTech('{{ $suggestion }}')" 
                                    type="button" 
                                    class="tech-pill text-[11px]">
                                + {{ $suggestion }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Stepper Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-[#d5dbe2] dark:border-[#262d3a]">
                <x-button @click="goToStep(2)" type="button" variant="secondary" size="md">
                    &larr; Kembali
                </x-button>
                <x-button @click="goToStep(4)" type="button" variant="primary" size="md">
                    <span>Lanjut: Knowledge</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </x-button>
            </div>
        </div>

        <!-- ============================================================
             SECTION 04: KNOWLEDGE SHARING
             ============================================================ -->
        <div x-show="currentStep === 4" class="ny-card p-6 sm:p-8 space-y-5 bg-white dark:bg-[#141821] border border-[#d5dbe2] dark:border-[#262d3a]">
            <div class="border-b border-[#d5dbe2] dark:border-[#262d3a] pb-3">
                <span class="text-[11px] font-mono font-bold text-[#0e9c8b] dark:text-[#50d2c1]">LANGKAH 04 DARI 05</span>
                <h2 class="text-base sm:text-lg font-bold text-[#10161f] dark:text-[#eaecf0]">Knowledge Sharing & How to Run</h2>
                <p class="text-xs text-[#5c6979] dark:text-[#7e8a9a]">NampungYuk adalah tempat berbagi pengalaman developer, bukan sekadar etalase.</p>
            </div>

            <!-- Full Description / About -->
            <div class="space-y-1.5">
                <label for="description" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0]">
                    Tentang Project Ini (Deskripsi Lengkap)
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="4"
                          x-model="description"
                          placeholder="Jelaskan latar belakang, arsitektur, atau fitur-fitur penting yang dibangun..."
                          class="ny-input text-xs sm:text-sm"></textarea>
            </div>

            <!-- Challenges -->
            <div class="space-y-1.5">
                <label for="challenges" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Tantangan Teknis yang Dihadapi (Challenges)</span>
                </label>
                <textarea name="challenges" 
                          id="challenges" 
                          rows="3"
                          x-model="challenges"
                          placeholder="Contoh: Kesulitan saat sinkronisasi state WebSocket real-time, optimasi query N+1..."
                          class="ny-input text-xs sm:text-sm"></textarea>
            </div>

            <!-- Learnings -->
            <div class="space-y-1.5">
                <label for="learnings" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Apa yang Dipelajari & Solusinya (Learnings)</span>
                </label>
                <textarea name="learnings" 
                          id="learnings" 
                          rows="3"
                          x-model="learnings"
                          placeholder="Contoh: Belajar mengimplementasikan batch indexing Redis dan pattern event-driven..."
                          class="ny-input text-xs sm:text-sm"></textarea>
            </div>

            <!-- How To Run (Truthful instructions) -->
            <div class="space-y-1.5">
                <label for="setup_instructions" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#0e9c8b] dark:bg-[#50d2c1]"></span>
                    <span>Cara Menjalankan Project (Opsional — Hanya tampil jika diisi)</span>
                </label>
                <textarea name="setup_instructions" 
                          id="setup_instructions" 
                          rows="4"
                          x-model="setupInstructions"
                          placeholder="git clone https://github.com/kamu/project.git&#10;composer install&#10;php artisan migrate&#10;npm install && npm run dev"
                          class="ny-input font-mono text-xs"></textarea>
                <p class="text-[11px] text-[#5c6979]">Tuliskan langkah-langkah nyata untuk menjalankan project di lokal.</p>
            </div>

            <!-- Stepper Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-[#d5dbe2] dark:border-[#262d3a]">
                <x-button @click="goToStep(3)" type="button" variant="secondary" size="md">
                    &larr; Kembali
                </x-button>
                <x-button @click="goToStep(5)" type="button" variant="primary" size="md">
                    <span>Lanjut: Preview & Publikasikan</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </x-button>
            </div>
        </div>

        <!-- ============================================================
             SECTION 05: PREVIEW & SUBMIT
             ============================================================ -->
        <div x-show="currentStep === 5" class="space-y-6">
            <div class="ny-card p-6 sm:p-8 space-y-4 bg-white dark:bg-[#141821] border border-[#d5dbe2] dark:border-[#262d3a]">
                <div class="border-b border-[#d5dbe2] dark:border-[#262d3a] pb-3">
                    <span class="text-[11px] font-mono font-bold text-[#0e9c8b] dark:text-[#50d2c1]">LANGKAH 05 DARI 05</span>
                    <h2 class="text-base sm:text-lg font-bold text-[#10161f] dark:text-[#eaecf0]">Pratinjau Publikasi</h2>
                    <p class="text-xs text-[#5c6979] dark:text-[#7e8a9a]">Berikut tampilan bagaimana project ini akan terlihat di feed komunitas.</p>
                </div>

                <!-- Mockup Preview Card -->
                <div class="ny-card p-4 sm:p-5 space-y-3 bg-[#eceff1] dark:bg-[#0b0e11] border border-[#d5dbe2] dark:border-[#262d3a]">
                    
                    <div class="flex items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2">
                            <x-user-avatar :user="Auth::user()" size="sm" />
                            <div>
                                <span class="font-bold text-[#10161f] dark:text-[#eaecf0]">{{ Auth::user()->name }}</span>
                                <span class="text-[#5c6979] text-[11px]">&bull; Baru saja</span>
                            </div>
                        </div>

                        <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-[#d9fbf4] dark:bg-[#50d2c1]/12 text-[#0e9c8b] dark:text-[#50d2c1] border border-[#50d2c1]/30 dark:border-[#50d2c1]/30"
                              x-text="status.toUpperCase()"></span>
                    </div>

                    <div class="space-y-1">
                        <h3 class="font-bold text-base text-[#10161f] dark:text-[#eaecf0]" x-text="title || 'Judul Project'"></h3>
                        <p class="text-xs text-[#5c6979] dark:text-[#7e8a9a]" x-text="tagline || 'Tagline ringkas project'"></p>
                    </div>

                    <!-- Visual Banner Preview -->
                    <div class="relative w-full aspect-video max-h-[220px] rounded-lg overflow-hidden border border-[#d5dbe2] dark:border-[#262d3a] bg-[#e6eaee] dark:bg-[#1e2530]">
                        <img :src="currentThumbnail" alt="Preview Thumbnail" class="w-full h-full object-cover">
                    </div>

                    <!-- Tech chips preview -->
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <template x-for="tech in techList" :key="tech">
                            <span class="tech-pill text-[11px]">
                                #<span x-text="tech"></span>
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Final Verification & CTA Button -->
                <div class="flex items-center justify-between pt-4 border-t border-[#d5dbe2] dark:border-[#262d3a]">
                    <x-button @click="goToStep(4)" type="button" variant="secondary" size="md">
                        &larr; Ubah Data
                    </x-button>

                    <x-button type="submit" variant="primary" size="lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Publikasikan Project</span>
                    </x-button>
                </div>
            </div>
        </div>

    </form>

</div>
@endsection
