@extends('layouts.app')

@section('title', 'Edit ' . $project->title . ' — NampungYuk')
@section('meta_description', 'Perbarui karya project codingan kamu di NampungYuk.')
@section('header_title', 'Edit Karya')
@section('main_width', 'max-w-none')
@section('hide_right_rail', '1')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto"
     x-data="{
         currentStep: 1,
         totalSteps: 5,
         title: '{{ addslashes(old('title', $project->title)) }}',
         tagline: '{{ addslashes(old('tagline', $project->tagline)) }}',
         categoryId: '{{ old('category_id', $project->category_id) }}',
         categoryName: '',
         projectType: '{{ old('project_type', $project->project_type ?? 'web') }}',
         status: '{{ old('status', $project->status ?? 'beta') }}',
         thumbnailUrl: '{{ old('thumbnail_url', $project->thumbnail) }}',
         previewImage: '',
         demoUrl: '{{ old('demo_url', $project->demo_url ?? '') }}',
         githubUrl: '{{ old('github_url', $project->github_url ?? '') }}',
         prototypeUrl: '{{ old('prototype_url', $project->prototype_url ?? '') }}',
         techInput: '',
         techList: {{ json_encode(old('tech_stacks') ? array_filter(array_map('trim', explode(',', old('tech_stacks')))) : ($project->tech_stacks ?? ['Laravel', 'TailwindCSS'])) }},
         description: `{{ addslashes(old('description', $project->description ?? '')) }}`,
         challenges: `{{ addslashes(old('challenges', $project->challenges ?? '')) }}`,
         learnings: `{{ addslashes(old('learnings', $project->learnings ?? '')) }}`,
         setupInstructions: `{{ addslashes(old('setup_instructions', $project->setup_instructions ?? '')) }}`,

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
             this.$refs.form.submit();
         }
     }">

    <!-- Page Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('projects.show', $project->slug) }}" class="text-xs text-[var(--text-muted)] hover:text-[var(--primary)] flex items-center gap-1 transition">
                    &larr; Kembali ke Proyek
                </a>
            </div>
            <h1 class="text-2xl font-extrabold text-[var(--primary)] tracking-tight">Edit Project: {{ $project->title }}</h1>
            <p class="text-xs text-[var(--text-muted)] mt-0.5">Perbarui deskripsi, link demo, status rilis, atau media karya codingan kamu.</p>
        </div>

        <a href="{{ route('projects.show', $project->slug) }}" class="btn-secondary text-xs py-2 px-3.5">
            Lihat Karya &rarr;
        </a>
    </div>

    <!-- Interactive Step Indicators -->
    <div class="rounded-2xl p-2.5 sm:p-3 bg-[var(--surface)] border border-[var(--border-color)] shadow-xs">
        <div class="grid grid-cols-5 gap-1 sm:gap-2 text-center text-xs">
            <button type="button" @click="goToStep(1)" :class="currentStep === 1 ? 'border-[var(--primary)] text-[var(--primary)] font-bold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--primary)]'" class="py-2 border-b-2 transition flex items-center justify-center gap-1.5 cursor-pointer">
                <span class="w-5 h-5 rounded-full text-[10px] flex items-center justify-center font-mono" :class="currentStep === 1 ? 'bg-[var(--primary)] text-[var(--primary-text)] font-bold' : 'bg-[var(--surface-muted)] text-[var(--text-muted)]'">1</span>
                <span class="hidden sm:inline">Info Dasar</span>
            </button>
            <button type="button" @click="goToStep(2)" :class="currentStep === 2 ? 'border-[var(--primary)] text-[var(--primary)] font-bold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--primary)]'" class="py-2 border-b-2 transition flex items-center justify-center gap-1.5 cursor-pointer">
                <span class="w-5 h-5 rounded-full text-[10px] flex items-center justify-center font-mono" :class="currentStep === 2 ? 'bg-[var(--primary)] text-[var(--primary-text)] font-bold' : 'bg-[var(--surface-muted)] text-[var(--text-muted)]'">2</span>
                <span class="hidden sm:inline">Media Visual</span>
            </button>
            <button type="button" @click="goToStep(3)" :class="currentStep === 3 ? 'border-[var(--primary)] text-[var(--primary)] font-bold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--primary)]'" class="py-2 border-b-2 transition flex items-center justify-center gap-1.5 cursor-pointer">
                <span class="w-5 h-5 rounded-full text-[10px] flex items-center justify-center font-mono" :class="currentStep === 3 ? 'bg-[var(--primary)] text-[var(--primary-text)] font-bold' : 'bg-[var(--surface-muted)] text-[var(--text-muted)]'">3</span>
                <span class="hidden sm:inline">Tech & Tautan</span>
            </button>
            <button type="button" @click="goToStep(4)" :class="currentStep === 4 ? 'border-[var(--primary)] text-[var(--primary)] font-bold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--primary)]'" class="py-2 border-b-2 transition flex items-center justify-center gap-1.5 cursor-pointer">
                <span class="w-5 h-5 rounded-full text-[10px] flex items-center justify-center font-mono" :class="currentStep === 4 ? 'bg-[var(--primary)] text-[var(--primary-text)] font-bold' : 'bg-[var(--surface-muted)] text-[var(--text-muted)]'">4</span>
                <span class="hidden sm:inline">Cerita & Arsitektur</span>
            </button>
            <button type="button" @click="goToStep(5)" :class="currentStep === 5 ? 'border-[var(--primary)] text-[var(--primary)] font-bold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--primary)]'" class="py-2 border-b-2 transition flex items-center justify-center gap-1.5 cursor-pointer">
                <span class="w-5 h-5 rounded-full text-[10px] flex items-center justify-center font-mono" :class="currentStep === 5 ? 'bg-[var(--primary)] text-[var(--primary-text)] font-bold' : 'bg-[var(--surface-muted)] text-[var(--text-muted)]'">5</span>
                <span class="hidden sm:inline">Pratinjau</span>
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl border border-rose-200 dark:border-rose-900 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 text-xs space-y-1" role="alert">
            <p class="font-bold">Ada beberapa data yang perlu diperiksa:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form x-ref="form" action="{{ route('projects.update', $project->slug) }}" method="POST" enctype="multipart/form-data" @submit.prevent="submitForm">
        @csrf
        @method('PUT')

        <!-- Step 1: Info Dasar -->
        <div x-show="currentStep === 1" class="rounded-2xl p-6 sm:p-8 space-y-5 bg-[var(--surface)] border border-[var(--border-color)] shadow-xs">
            <div class="border-b border-[var(--border-color)] pb-3">
                <span class="text-[11px] font-mono font-bold text-emerald-600 dark:text-emerald-400">LANGKAH 01 DARI 05</span>
                <h2 class="text-base sm:text-lg font-bold text-[var(--primary)]">Informasi Dasar Karya</h2>
                <p class="text-xs text-[var(--text-muted)]">Nama project, tagline menarik, dan kategori yang tepat.</p>
            </div>

            <div class="space-y-1.5">
                <label for="title" class="text-xs font-semibold text-[var(--primary)]">
                    Judul Project <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" id="title" required x-model="title" maxlength="150" placeholder="Contoh: HyperFlow — Real-time Analytics Engine" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs sm:text-sm outline-none focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#EAE8E1] transition">
            </div>

            <div class="space-y-1.5">
                <label for="tagline" class="text-xs font-semibold text-[var(--primary)]">
                    Tagline Ringkas <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="tagline" id="tagline" required x-model="tagline" maxlength="255" placeholder="Contoh: Dashboard visualisasi metrik performa database berskala terdistribusi" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs sm:text-sm outline-none focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#EAE8E1] transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="category_id" class="text-xs font-semibold text-[var(--primary)]">
                        Kategori <span class="text-rose-500">*</span>
                    </label>
                    <select name="category_id" id="category_id" required x-model="categoryId" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] text-xs sm:text-sm outline-none focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#EAE8E1] transition">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label for="status" class="text-xs font-semibold text-[var(--primary)]">
                        Status Pengembangan
                    </label>
                    <select name="status" id="status" x-model="status" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] text-xs sm:text-sm outline-none focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#EAE8E1] transition">
                        <option value="idea">💡 Ide / Konsep</option>
                        <option value="prototype">🛠️ Prototype</option>
                        <option value="beta">🧪 Beta Testing</option>
                        <option value="production">🚀 Production / Rilis Publik</option>
                        <option value="archived">📦 Archived</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-[var(--border-color)]">
                <x-button @click="goToStep(2)" type="button" variant="primary" size="md">
                    <span>Lanjut: Media & Banner</span> &rarr;
                </x-button>
            </div>
        </div>

        <!-- Step 2: Media & Banner -->
        <div x-show="currentStep === 2" class="rounded-2xl p-6 sm:p-8 space-y-5 bg-[var(--surface)] border border-[var(--border-color)] shadow-xs">
            <div class="border-b border-[var(--border-color)] pb-3">
                <span class="text-[11px] font-mono font-bold text-emerald-600 dark:text-emerald-400">LANGKAH 02 DARI 05</span>
                <h2 class="text-base sm:text-lg font-bold text-[var(--primary)]">Thumbnail & Media Visual</h2>
                <p class="text-xs text-[var(--text-muted)]">Unggah screenshot, thumbnail, atau video demo karya kamu.</p>
            </div>

            <!-- Current Thumbnail -->
            <div class="space-y-2">
                <label class="text-xs font-semibold text-[var(--primary)]">Thumbnail Saat Ini / Pratinjau:</label>
                <div class="relative aspect-video max-h-48 rounded-xl overflow-hidden border border-[#242426] bg-[var(--surface-deep)]">
                    <img :src="currentThumbnail" alt="Preview" class="w-full h-full object-cover">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-[var(--primary)]">Ganti Thumbnail (Upload Gambar Baru)</label>
                    <input type="file" name="thumbnail" accept="image/*" @change="handleFileSelect" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] text-xs outline-none focus:border-[#EAE8E1] transition">
                    <p class="text-[11px] text-[var(--text-muted)]">Format: PNG, JPG, WebP · maks 5MB per gambar</p>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-[var(--primary)]">Atau Gunakan URL Thumbnail Eksternal</label>
                    <input type="url" name="thumbnail_url" x-model="thumbnailUrl" placeholder="https://..." class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs sm:text-sm outline-none focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#EAE8E1] transition">
                </div>
            </div>

            <div class="space-y-1.5 pt-2">
                <label class="text-xs font-semibold text-[var(--primary)]">Tambah Gambar Galeri Lainnya (Maks. 8 gambar · maks 5MB per gambar)</label>
                <input type="file" name="images[]" multiple accept="image/*" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] text-xs outline-none focus:border-[#EAE8E1] transition">
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-[var(--border-color)]">
                <x-button @click="goToStep(1)" type="button" variant="secondary" size="md">&larr; Kembali</x-button>
                <x-button @click="goToStep(3)" type="button" variant="primary" size="md"><span>Lanjut: Tech & Tautan</span> &rarr;</x-button>
            </div>
        </div>

        <!-- Step 3: Tech & Tautan -->
        <div x-show="currentStep === 3" class="rounded-2xl p-6 sm:p-8 space-y-5 bg-[var(--surface)] border border-[var(--border-color)] shadow-xs">
            <div class="border-b border-[var(--border-color)] pb-3">
                <span class="text-[11px] font-mono font-bold text-emerald-600 dark:text-emerald-400">LANGKAH 03 DARI 05</span>
                <h2 class="text-base sm:text-lg font-bold text-[var(--primary)]">Tech Stack & Tautan Eksternal</h2>
                <p class="text-xs text-[var(--text-muted)]">Teknologi yang kamu gunakan dan link agar orang lain bisa mencoba.</p>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-semibold text-[var(--primary)]">
                    Tech Stack (Minimal 1) <span class="text-rose-500">*</span>
                </label>
                <div class="flex gap-2">
                    <input type="text" x-model="techInput" @keydown.enter.prevent="addTech()" placeholder="Ketik nama teknologi lalu tekan Enter (misal: Docker, Next.js)" class="flex-1 px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs sm:text-sm outline-none focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#EAE8E1] transition">
                    <button type="button" @click="addTech()" class="btn-secondary text-xs px-3 cursor-pointer">Tambah</button>
                </div>
                <input type="hidden" name="tech_stacks" :value="techStacksJoined">

                <div class="flex flex-wrap gap-1.5 pt-1">
                    <template x-for="(tech, idx) in techList" :key="tech">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-mono bg-[#161619] dark:bg-[#19191D] text-[#EAE8E1] border border-[#242426]">
                            <span x-text="tech"></span>
                            <button type="button" @click="removeTech(idx)" class="text-rose-500 hover:text-rose-700 font-bold cursor-pointer">&times;</button>
                        </span>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="space-y-1.5">
                    <label for="demo_url" class="text-xs font-semibold text-[var(--primary)]">Link Live Demo (Opsional)</label>
                    <input type="url" name="demo_url" id="demo_url" x-model="demoUrl" placeholder="https://demo.kamu.com" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs sm:text-sm outline-none focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#EAE8E1] transition">
                </div>
                <div class="space-y-1.5">
                    <label for="github_url" class="text-xs font-semibold text-[var(--primary)]">Link Repository GitHub (Opsional)</label>
                    <input type="url" name="github_url" id="github_url" x-model="githubUrl" placeholder="https://github.com/..." class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs sm:text-sm outline-none focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#EAE8E1] transition">
                </div>
                <div class="space-y-1.5">
                    <label for="prototype_url" class="text-xs font-semibold text-[var(--primary)]">Link Prototype / Figma (Opsional)</label>
                    <input type="url" name="prototype_url" id="prototype_url" x-model="prototypeUrl" placeholder="https://figma.com/..." class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs sm:text-sm outline-none focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#EAE8E1] transition">
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-[var(--border-color)]">
                <x-button @click="goToStep(2)" type="button" variant="secondary" size="md">&larr; Kembali</x-button>
                <x-button @click="goToStep(4)" type="button" variant="primary" size="md"><span>Lanjut: Cerita & Arsitektur</span> &rarr;</x-button>
            </div>
        </div>

        <!-- Step 4: Cerita & Arsitektur -->
        <div x-show="currentStep === 4" class="rounded-2xl p-6 sm:p-8 space-y-5 bg-[var(--surface)] border border-[var(--border-color)] shadow-xs">
            <div class="border-b border-[var(--border-color)] pb-3">
                <span class="text-[11px] font-mono font-bold text-emerald-600 dark:text-emerald-400">LANGKAH 04 DARI 05</span>
                <h2 class="text-base sm:text-lg font-bold text-[var(--primary)]">Detail & Arsitektur Proyek</h2>
                <p class="text-xs text-[var(--text-muted)]">Ceritakan bagaimana project ini dibangun, tantangan teknis, dan apa yang dipelajari.</p>
            </div>

            <div class="space-y-1.5">
                <label for="description" class="text-xs font-semibold text-[var(--primary)]">Deskripsi Lengkap Project</label>
                <textarea name="description" id="description" rows="5" x-model="description" placeholder="Jelaskan latar belakang, arsitektur, dan fitur utama..." class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs sm:text-sm outline-none focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#EAE8E1] transition"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="challenges" class="text-xs font-semibold text-[var(--primary)]">Tantangan Teknis yang Dihadapi</label>
                    <textarea name="challenges" id="challenges" rows="3" x-model="challenges" placeholder="Misal: Optimasi latency websocket saat user melonjak..." class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs sm:text-sm outline-none focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#EAE8E1] transition"></textarea>
                </div>
                <div class="space-y-1.5">
                    <label for="learnings" class="text-xs font-semibold text-[var(--primary)]">Pelajaran yang Dipetik (Learnings)</label>
                    <textarea name="learnings" id="learnings" rows="3" x-model="learnings" placeholder="Misal: Memahami implementasi connection pooling..." class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] text-xs sm:text-sm outline-none focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#EAE8E1] transition"></textarea>
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="setup_instructions" class="text-xs font-semibold text-[var(--primary)]">Cara Menjalankan Project di Lokal (Opsional)</label>
                <textarea name="setup_instructions" id="setup_instructions" rows="3" x-model="setupInstructions" placeholder="git clone https://...&#10;composer install&#10;php artisan migrate" class="w-full px-3.5 py-2.5 rounded-xl bg-[var(--surface-deep)] border border-[#242426] text-[#EAE8E1] placeholder:text-[#8E8D86] font-mono text-xs outline-none focus:border-[#EAE8E1] focus:ring-1 focus:ring-[#EAE8E1] transition"></textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-[var(--border-color)]">
                <x-button @click="goToStep(3)" type="button" variant="secondary" size="md">&larr; Kembali</x-button>
                <x-button @click="goToStep(5)" type="button" variant="primary" size="md"><span>Lanjut: Pratinjau Perubahan</span> &rarr;</x-button>
            </div>
        </div>

        <!-- Step 5: Pratinjau & Simpan -->
        <div x-show="currentStep === 5" class="rounded-2xl p-6 sm:p-8 space-y-6 bg-[var(--surface)] border border-[var(--border-color)] shadow-xs">
            <div class="border-b border-[var(--border-color)] pb-3">
                <span class="text-[11px] font-mono font-bold text-emerald-600 dark:text-emerald-400">LANGKAH 05 DARI 05</span>
                <h2 class="text-base sm:text-lg font-bold text-[var(--primary)]">Konfirmasi & Simpan Perubahan</h2>
                <p class="text-xs text-[var(--text-muted)]">Periksa kembali pratinjau sebelum menyimpan pembaruan.</p>
            </div>

            <!-- Card Mockup -->
            <div class="rounded-xl p-4 sm:p-5 space-y-3 bg-[var(--surface-subtle)] border border-[var(--border-color)]">
                <div class="flex items-center justify-between gap-2 text-xs">
                    <span class="font-bold text-[var(--primary)]">{{ Auth::user()->name }}</span>
                    <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30" x-text="status.toUpperCase()"></span>
                </div>
                <div class="space-y-1">
                    <h3 class="font-bold text-base text-[var(--primary)]" x-text="title || 'Judul Project'"></h3>
                    <p class="text-xs text-[var(--text-muted)]" x-text="tagline || 'Tagline ringkas'"></p>
                </div>
                <div class="relative w-full aspect-video max-h-48 rounded-lg overflow-hidden border border-[var(--border-color)] bg-[var(--surface-subtle)]">
                    <img :src="currentThumbnail" alt="Preview Thumbnail" class="w-full h-full object-cover">
                </div>
                <div class="flex flex-wrap gap-1.5 pt-1">
                    <template x-for="tech in techList" :key="tech">
                        <span class="tech-pill text-[11px]">#<span x-text="tech"></span></span>
                    </template>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-[var(--border-color)]">
                <x-button @click="goToStep(4)" type="button" variant="secondary" size="md">&larr; Ubah Data</x-button>
                <x-button type="submit" variant="primary" size="lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Perubahan</span>
                </x-button>
            </div>
        </div>
    </form>

    <!-- Danger Zone: Hapus Proyek -->
    <div class="p-6 rounded-2xl border border-rose-200 dark:border-rose-950/60 bg-rose-50/50 dark:bg-rose-950/20 space-y-4">
        <div>
            <h3 class="text-sm font-bold text-rose-700 dark:text-rose-400">Zona Bahaya: Hapus Proyek</h3>
            <p class="text-xs text-rose-600/80 dark:text-rose-300/70 mt-0.5">Tindakan ini permanen. Semua data karya, ulasan, komentar, dan upvote terkait proyek ini akan dihapus dari NampungYuk.</p>
        </div>

        <form action="{{ route('projects.destroy', $project->slug) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS PERMANEN proyek {{ addslashes($project->title) }}? Tindakan ini tidak bisa dibatalkan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                Hapus Proyek Ini Secara Permanen
            </button>
        </form>
    </div>
</div>
@endsection
