@extends('layouts.app')

@section('title', 'Edit ' . $project->title . ' — NampungYuk')
@section('meta_description', 'Perbarui karya project codingan kamu di NampungYuk.')
@section('header_title', 'Edit Karya')

@section('content')
@php
    $defaultThumbnailPlaceholder = 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80';

    $normalizeDisplayUrl = static function (string $value): string {
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            if (preg_match('#/storage/(.+)$#', $value, $matches)) {
                return asset('storage/'.$matches[1]);
            }

            return $value;
        }
        if (str_starts_with($value, '/storage/')) {
            return asset(ltrim($value, '/'));
        }
        if (str_starts_with($value, 'storage/')) {
            return asset($value);
        }

        return asset('storage/'.ltrim($value, '/'));
    };

    $rawMediaPaths = [];
    $rawThumb = $project->getRawOriginal('thumbnail');
    if (is_string($rawThumb) && $rawThumb !== '' && $rawThumb !== $defaultThumbnailPlaceholder) {
        $rawMediaPaths[] = $rawThumb;
    }
    foreach ((array) ($project->images ?? []) as $imgPath) {
        if (is_string($imgPath) && $imgPath !== '' && ! in_array($imgPath, $rawMediaPaths, true)) {
            $rawMediaPaths[] = $imgPath;
        }
    }

    $existingMediaItems = array_map(static function (string $path) use ($normalizeDisplayUrl): array {
        $cleanName = basename((string) parse_url($path, PHP_URL_PATH)) ?: 'Gambar';

        return [
            'kind' => 'existing',
            'path' => $path,
            'url' => $normalizeDisplayUrl($path),
            'name' => $cleanName,
            'file' => null,
        ];
    }, array_slice($rawMediaPaths, 0, 9));

    $initialTitle = old('title', $project->title ?? '');
    $initialTagline = old('tagline', $project->tagline ?? '');
    $initialCategoryId = (string) old('category_id', $project->category_id ? (string) $project->category_id : '');
    $initialProjectType = (string) old('project_type', $project->project_type ?? 'web');
    $initialStatus = (string) old('status', $project->status ?? 'beta');
    $initialVisibility = (string) old('visibility', $project->visibility ?? 'public');
    $initialTechList = old('tech_stacks')
        ? array_values(array_filter(array_map('trim', explode(',', old('tech_stacks')))))
        : (is_array($project->tech_stacks) && count($project->tech_stacks)
            ? $project->tech_stacks
            : ['Laravel', 'TailwindCSS']);
    $initialDemoUrl = old('demo_url', $project->demo_url ?? '');
    $initialGithubUrl = old('github_url', $project->github_url ?? '');
    $initialPrototypeUrl = old('prototype_url', $project->prototype_url ?? '');
    $initialThumbnailUrl = old('thumbnail_url', '');
    $initialDescription = old('description', $project->description ?? '');
    $initialChallenges = old('challenges', $project->challenges ?? '');
    $initialLearnings = old('learnings', $project->learnings ?? '');
    $initialSetup = old('setup_instructions', $project->setup_instructions ?? '');

    $visibilityOptions = [
        ['value' => 'public', 'label' => 'Publik', 'hint' => 'Tampil di feed & profil'],
        ['value' => 'private', 'label' => 'Privat', 'hint' => 'Hanya terlihat oleh kamu'],
        ['value' => 'draft', 'label' => 'Draft', 'hint' => 'Simpan belum dipublikasikan'],
    ];

    $categoryOptions = $categories->map(static fn ($category): array => [
        'value' => (string) $category->id,
        'label' => (string) $category->name,
    ])->values()->all();

    $projectTypeOptions = [
        ['value' => 'web', 'label' => 'Web App'],
        ['value' => 'mobile', 'label' => 'Mobile App'],
        ['value' => 'developer_tool', 'label' => 'Developer Tool / CLI'],
        ['value' => 'library_package', 'label' => 'Library / Package'],
        ['value' => 'game', 'label' => 'Game'],
        ['value' => 'hardware_iot', 'label' => 'Hardware / IoT'],
        ['value' => 'other', 'label' => 'Lainnya'],
    ];

    $statusOptions = [
        ['value' => 'production', 'label' => 'Stabil / Rilis', 'hint' => 'Sudah siap dipakai publik'],
        ['value' => 'beta', 'label' => 'Beta', 'hint' => 'Berjalan, masih tahap uji coba'],
        ['value' => 'prototype', 'label' => 'Prototipe', 'hint' => 'Purwarupa / demo awal'],
        ['value' => 'idea', 'label' => 'Ide', 'hint' => 'Masih konsep & rencana'],
        ['value' => 'archived', 'label' => 'Diarsipkan', 'hint' => 'Tidak lagi dikembangkan aktif'],
    ];
@endphp

<div class="space-y-4 max-w-2xl mx-auto"
     x-data="{
         csrfToken: @js(csrf_token()),
         title: @js($initialTitle),
         tagline: @js($initialTagline),
         categoryId: @js($initialCategoryId),
         projectType: @js($initialProjectType),
         status: @js($initialStatus),
         visibility: @js($initialVisibility),
         techInput: '',
         techList: @js($initialTechList),
         demoUrl: @js($initialDemoUrl),
         githubUrl: @js($initialGithubUrl),
         prototypeUrl: @js($initialPrototypeUrl),
         thumbnailUrl: @js($initialThumbnailUrl),
         description: @js($initialDescription),
         challenges: @js($initialChallenges),
         learnings: @js($initialLearnings),
         setupInstructions: @js($initialSetup),
         mediaItems: @js($existingMediaItems),
         maxImages: 9,
         activeImageIndex: 0,
         lightboxOpen: false,
         dragIndex: null,
         dragOverIndex: null,
         deleteProjectModalOpen: false,
         showLinks: {{ ($initialDemoUrl || $initialGithubUrl) ? 'true' : 'false' }},
         detailsOpen: {{ ($initialDescription || $initialChallenges || $initialLearnings || $initialSetup || $initialPrototypeUrl || $initialThumbnailUrl) ? 'true' : 'false' }},
         isSubmitting: false,
         serverErrors: [],

         handleFileSelect(event) {
             const files = Array.from(event.target.files || []).filter(f => f && f.type && f.type.startsWith('image/'));
             if (! files.length) {
                 event.target.value = '';
                 return;
             }
             const wasEmpty = this.mediaItems.length === 0;
             const remaining = this.maxImages - this.mediaItems.length;
             if (remaining <= 0) {
                 if (window.notify) notify('Maksimal ' + this.maxImages + ' gambar per karya.');
                 event.target.value = '';
                 return;
             }
             if (files.length > remaining && window.notify) {
                 notify('Maksimal ' + this.maxImages + ' gambar. Hanya ' + remaining + ' gambar tambahan yang dimasukkan.');
             }
             for (const file of files.slice(0, remaining)) {
                 this.mediaItems.push({
                     kind: 'new',
                     path: '',
                     file,
                     url: URL.createObjectURL(file),
                     name: file.name,
                 });
             }
             if (wasEmpty) {
                 this.activeImageIndex = 0;
             }
             event.target.value = '';
         },
         removeMediaItem(index) {
             const removed = this.mediaItems.splice(index, 1)[0];
             if (removed && removed.kind === 'new' && removed.url) {
                 try { URL.revokeObjectURL(removed.url); } catch (e) {}
             }
             if (this.mediaItems.length === 0) {
                 this.activeImageIndex = 0;
                 this.lightboxOpen = false;
             } else if (this.activeImageIndex >= this.mediaItems.length) {
                 this.activeImageIndex = this.mediaItems.length - 1;
             } else if (index < this.activeImageIndex) {
                 this.activeImageIndex -= 1;
             }
         },
         moveImage(fromIndex, toIndex) {
             if (fromIndex === null || toIndex === null) return;
             if (fromIndex === toIndex || fromIndex < 0 || toIndex < 0) return;
             if (fromIndex >= this.mediaItems.length || toIndex >= this.mediaItems.length) return;
             const activeItem = this.mediaItems[this.activeImageIndex];
             const [moved] = this.mediaItems.splice(fromIndex, 1);
             this.mediaItems.splice(toIndex, 0, moved);
             const newActiveIdx = this.mediaItems.indexOf(activeItem);
             this.activeImageIndex = newActiveIdx >= 0 ? newActiveIdx : toIndex;
         },
         onDragStart(idx, event) {
             this.dragIndex = idx;
             if (event && event.dataTransfer) {
                 event.dataTransfer.effectAllowed = 'move';
                 try { event.dataTransfer.setData('text/plain', String(idx)); } catch (e) {}
             }
         },
         onDragOver(idx) {
             if (this.dragIndex !== null && this.dragIndex !== idx) {
                 this.dragOverIndex = idx;
             }
         },
         onDrop(idx) {
             if (this.dragIndex !== null && this.dragIndex !== idx) {
                 this.moveImage(this.dragIndex, idx);
             }
             this.dragIndex = null;
             this.dragOverIndex = null;
         },
         onDragEnd() {
             this.dragIndex = null;
             this.dragOverIndex = null;
         },
         openLightbox(idx = null) {
             if (! this.mediaItems.length) return;
             if (idx !== null) this.activeImageIndex = idx;
             this.lightboxOpen = true;
         },
         prevImage() {
             if (this.mediaItems.length <= 1) return;
             this.activeImageIndex = (this.activeImageIndex - 1 + this.mediaItems.length) % this.mediaItems.length;
         },
         nextImage() {
             if (this.mediaItems.length <= 1) return;
             this.activeImageIndex = (this.activeImageIndex + 1) % this.mediaItems.length;
         },
         addTech(tech) {
             const t = (tech || this.techInput).trim();
             if (t && ! this.techList.includes(t)) {
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
         normalizeAndValidateUrl(prop, label, inputId, openLinks = false, openDetails = false) {
             const raw = (this[prop] || '').trim();
             if (! raw) {
                 this[prop] = '';
                 return true;
             }
             let normalized = raw;
             if (! raw.includes('://') && ! /^[a-zA-Z][a-zA-Z0-9+.-]*:/.test(raw)) {
                 if (/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}([:\/?#].*)?$/.test(raw)) {
                     normalized = 'https://' + raw;
                     this[prop] = normalized;
                 }
             }
             try {
                 const parsed = new URL(normalized);
                 if (! ['http:', 'https:'].includes(parsed.protocol) || /\s/.test(normalized)) {
                     throw new Error('Invalid protocol');
                 }
                 return true;
             } catch (err) {
                 if (openLinks) this.showLinks = true;
                 if (openDetails) this.detailsOpen = true;
                 const msg = 'Kolom ' + label + ' harus berupa URL valid (contoh: https://domain.com).';
                 this.serverErrors = [msg];
                 if (window.notify) notify(msg);
                 this.$nextTick(() => document.getElementById(inputId)?.focus());
                 return false;
             }
         },
         validateOptionalUrls() {
             return this.normalizeAndValidateUrl('demoUrl', 'Live Demo', 'demo_url', true, false)
                 && this.normalizeAndValidateUrl('githubUrl', 'GitHub Repo', 'github_url', true, false)
                 && this.normalizeAndValidateUrl('prototypeUrl', 'Figma / Prototype URL', 'prototype_url', false, true)
                 && this.normalizeAndValidateUrl('thumbnailUrl', 'URL Gambar Eksternal', 'thumbnail_url', false, true);
         },
         async submitViaFetch(formEl) {
             if (this.isSubmitting) return;
             this.isSubmitting = true;
             this.serverErrors = [];

             try {
                 const fd = new FormData(formEl);
                 fd.set('_method', 'PUT');
                 fd.set('tech_stacks', this.techStacksJoined);

                 const mediaOrder = [];
                 let newFileIdx = 0;
                 for (const item of this.mediaItems) {
                     if (item.kind === 'existing' && item.path) {
                         mediaOrder.push({ type: 'existing', path: item.path });
                     } else if (item.kind === 'new' && item.file) {
                         mediaOrder.push({ type: 'new', index: newFileIdx });
                         fd.append('new_images[]', item.file);
                         newFileIdx++;
                     }
                 }
                 fd.set('media_order', JSON.stringify(mediaOrder));

                 const res = await fetch(formEl.action, {
                     method: 'POST',
                     headers: {
                         'Accept': 'application/json',
                         'X-Requested-With': 'XMLHttpRequest',
                         'X-CSRF-TOKEN': this.csrfToken,
                     },
                     body: fd,
                 });

                 const data = await res.json().catch(() => ({}));

                 if (! res.ok) {
                     this.isSubmitting = false;
                     const collected = [];
                     if (data.errors && typeof data.errors === 'object') {
                         for (const [field, msgs] of Object.entries(data.errors)) {
                             if (['demo_url', 'github_url'].includes(field)) this.showLinks = true;
                             if (['prototype_url', 'thumbnail_url', 'description', 'challenges', 'learnings', 'setup_instructions', 'status'].includes(field)) {
                                 this.detailsOpen = true;
                             }
                             if (Array.isArray(msgs)) {
                                 collected.push(...msgs);
                             }
                         }
                     }
                     this.serverErrors = collected.length ? collected : [data.message || 'Terjadi kesalahan saat memperbarui karya.'];
                     if (window.notify) notify(this.serverErrors[0]);
                     window.scrollTo({ top: 0, behavior: 'smooth' });
                     return;
                 }

                 window.location.href = data.redirect_url || @js(route('projects.show', $project->slug));
             } catch (err) {
                 this.isSubmitting = false;
                 const msg = 'Koneksi gagal atau server bermasalah. Perubahan belum tersimpan.';
                 this.serverErrors = [msg];
                 if (window.notify) notify(msg);
             }
         },
         submitForm(e) {
             this.serverErrors = [];
             if (! this.title.trim() || this.title.trim().length < 2) {
                 if (window.notify) notify('Judul project wajib diisi (minimal 2 karakter).');
                 document.getElementById('title')?.focus();
                 return;
             }
             if (! this.tagline.trim() || this.tagline.trim().length < 3) {
                 if (window.notify) notify('Tagline project wajib diisi (minimal 3 karakter).');
                 document.getElementById('tagline')?.focus();
                 return;
             }
             if (! this.categoryId) {
                 if (window.notify) notify('Pilih kategori project terlebih dahulu.');
                 document.getElementById('category_id')?.focus();
                 return;
             }
             if (! this.techList.length) {
                 if (window.notify) notify('Minimal sertakan 1 teknologi (tech stack).');
                 document.getElementById('tech_input')?.focus();
                 return;
             }
             if (! this.validateOptionalUrls()) return;
             this.submitViaFetch(e.target);
         }
     }">

    <!-- Top Header & Navigation -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <a href="{{ route('projects.show', $project->slug) }}"
               class="inline-flex items-center gap-1.5 text-xs text-[var(--text-muted)] hover:text-[var(--primary)] transition font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Karya</span>
            </a>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-[var(--primary)] truncate">Edit Project: {{ $project->title }}</h1>
        </div>

        <a href="{{ route('projects.show', $project->slug) }}" class="btn-secondary text-xs py-2 px-3.5 shrink-0">
            Lihat Karya &rarr;
        </a>
    </div>

    <!-- Dynamic AJAX Error Summary -->
    <div x-show="serverErrors.length > 0" x-cloak class="p-4 rounded-xl bg-rose-950/40 border border-rose-900 text-rose-300 text-xs space-y-1" role="alert">
        <p class="font-bold flex items-center gap-1.5">
            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Mohon periksa data berikut (perubahan &amp; gambar kamu tetap aman):</span>
        </p>
        <ul class="list-disc list-inside ml-5 space-y-0.5">
            <template x-for="(err, idx) in serverErrors" :key="idx">
                <li x-text="err"></li>
            </template>
        </ul>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-950/40 border border-rose-900 text-rose-300 text-xs space-y-1" role="alert">
            <p class="font-bold">Mohon periksa data berikut:</p>
            <ul class="list-disc list-inside ml-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Unified Quick Composer Card (identical flow to /unggah) -->
    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 sm:p-6 shadow-xs">
        <form x-ref="editForm" action="{{ route('projects.update', $project->slug) }}" method="POST" enctype="multipart/form-data" novalidate @submit.prevent="submitForm($event)" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" name="tech_stacks" :value="techStacksJoined">

            <!-- Author header & visibility row -->
            <div class="flex items-center justify-between gap-3 pb-3 border-b border-[var(--border-color)]">
                <div class="flex items-center gap-3 min-w-0">
                    <x-user-avatar :user="Auth::user()" size="sm" />
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-[var(--primary)] truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[11px] font-mono text-[var(--text-muted)] truncate">{{ '@' . Auth::user()->username }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <x-custom-select name="visibility" id="visibility" x-model="visibility" :options="$visibilityOptions" size="sm" align="right" min-width="min-w-[205px]" />
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

            <!-- Uncropped Media Stage + Draggable Bottom Filmstrip -->
            <div x-show="mediaItems.length > 0" x-cloak class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div class="flex flex-wrap items-center gap-2 min-w-0">
                        <span class="font-mono font-semibold text-[var(--primary)]" x-text="mediaItems.length + ' / ' + maxImages + ' gambar'"></span>
                        <span class="text-[var(--text-faint)]">&middot;</span>
                        <span class="text-[var(--text-muted)]" x-text="mediaItems.length > 1 ? 'Geser thumbnail di bawah untuk atur urutan' : 'Klik gambar untuk perbesar'"></span>
                    </div>

                    <label x-show="mediaItems.length < maxImages"
                           for="media_images_input"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[var(--surface-muted)] border border-[var(--border-color)] hover:border-[var(--border-strong)] text-xs font-semibold text-[var(--primary)] cursor-pointer transition select-none">
                        + Tambah Foto
                    </label>
                </div>

                <!-- Main Uncropped Image Stage -->
                <div class="relative w-full rounded-xl overflow-hidden border border-[var(--border-color)] bg-[var(--bg-app)] flex items-center justify-center min-h-[220px] max-h-[420px] group">
                    <button type="button"
                            @click="openLightbox(activeImageIndex)"
                            class="w-full flex items-center justify-center cursor-zoom-in focus:outline-none">
                        <img :src="mediaItems[activeImageIndex]?.url"
                             :alt="mediaItems[activeImageIndex]?.name || 'Pratinjau gambar'"
                             class="max-h-[420px] w-auto max-w-full object-contain block select-none">
                    </button>

                    <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-md text-[10.5px] font-mono font-semibold bg-black/75 text-white border border-white/10 pointer-events-none"
                          x-text="'#' + (activeImageIndex + 1) + (activeImageIndex === 0 ? ' · Cover' : '')"></span>

                    <div class="absolute top-2.5 right-2.5 flex items-center gap-1.5">
                        <button type="button"
                                @click.stop="openLightbox(activeImageIndex)"
                                class="w-7 h-7 rounded-full bg-black/75 hover:bg-black text-white flex items-center justify-center transition cursor-pointer"
                                title="Perbesar gambar">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0zm-6-3v6m-3-3h6"/></svg>
                        </button>
                        <button type="button"
                                @click.stop="removeMediaItem(activeImageIndex)"
                                class="w-7 h-7 rounded-full bg-black/75 hover:bg-black text-white flex items-center justify-center transition cursor-pointer"
                                title="Hapus gambar ini">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <button type="button"
                            x-show="mediaItems.length > 1"
                            @click.stop="prevImage()"
                            aria-label="Gambar sebelumnya"
                            class="absolute left-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/70 hover:bg-black text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button"
                            x-show="mediaItems.length > 1"
                            @click.stop="nextImage()"
                            aria-label="Gambar berikutnya"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/70 hover:bg-black text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <span class="absolute bottom-2.5 right-2.5 px-2.5 py-1 rounded-md text-[10.5px] font-medium bg-black/75 text-white border border-white/10 opacity-0 group-hover:opacity-100 transition pointer-events-none">
                        Klik untuk lihat penuh
                    </span>
                </div>

                <!-- Bottom Draggable Thumbnail Filmstrip Bar -->
                <div x-show="mediaItems.length > 1" class="space-y-2">
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-0.5 select-none">
                        <template x-for="(img, idx) in mediaItems" :key="img.url + '-' + idx">
                            <div draggable="true"
                                 @dragstart="onDragStart(idx, $event)"
                                 @dragover.prevent="onDragOver(idx)"
                                 @dragenter.prevent="onDragOver(idx)"
                                 @drop.prevent="onDrop(idx)"
                                 @dragend="onDragEnd()"
                                 @click="activeImageIndex = idx"
                                 :class="{
                                     'ring-2 ring-[var(--primary)] border-transparent': activeImageIndex === idx && dragOverIndex !== idx,
                                     'ring-2 ring-emerald-400 scale-105 border-transparent': dragOverIndex === idx,
                                     'opacity-40': dragIndex === idx,
                                     'border border-[var(--border-color)] opacity-80 hover:opacity-100': activeImageIndex !== idx && dragOverIndex !== idx && dragIndex !== idx
                                 }"
                                 class="w-16 h-16 sm:w-[72px] sm:h-[72px] shrink-0 rounded-xl relative overflow-hidden cursor-grab active:cursor-grabbing transition bg-[var(--surface-deep)]">
                                <img :src="img.url" :alt="img.name" class="w-full h-full object-cover pointer-events-none">

                                <span :class="idx === 0 ? 'bg-[var(--primary)] text-[var(--primary-text)]' : 'bg-black/75 text-white'"
                                      class="absolute top-1 left-1 min-w-[18px] h-[18px] px-1 rounded text-[10px] font-mono font-bold flex items-center justify-center pointer-events-none"
                                      x-text="idx + 1"></span>

                                <button type="button"
                                        @click.stop="removeMediaItem(idx)"
                                        aria-label="Hapus gambar"
                                        class="absolute top-1 right-1 w-4 h-4 rounded-full bg-black/75 hover:bg-black text-white flex items-center justify-center transition cursor-pointer">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>

                        <label x-show="mediaItems.length < maxImages"
                               for="media_images_input"
                               title="Tambah foto lagi"
                               class="w-16 h-16 sm:w-[72px] sm:h-[72px] shrink-0 rounded-xl border border-dashed border-[var(--border-color)] hover:border-[var(--primary)] bg-[var(--surface-muted)]/60 hover:bg-[var(--surface-hover)] text-[var(--text-muted)] hover:text-[var(--primary)] flex flex-col items-center justify-center gap-0.5 cursor-pointer transition select-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span class="text-[10px] font-medium">Tambah</span>
                        </label>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-[var(--text-muted)]">
                        <span>Seret thumbnail untuk ubah urutan dari awal (1 = Cover) sampai akhir</span>
                        <div class="flex items-center gap-1.5">
                            <button type="button"
                                    @click="moveImage(activeImageIndex, activeImageIndex - 1)"
                                    :disabled="activeImageIndex === 0"
                                    class="px-2 py-1 rounded-md border border-[var(--border-color)] bg-[var(--surface-muted)] text-[var(--primary)] hover:bg-[var(--surface-hover)] disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer font-medium">
                                &larr; Geser Kiri
                            </button>
                            <button type="button"
                                    @click="moveImage(activeImageIndex, activeImageIndex + 1)"
                                    :disabled="activeImageIndex >= mediaItems.length - 1"
                                    class="px-2 py-1 rounded-md border border-[var(--border-color)] bg-[var(--surface-muted)] text-[var(--primary)] hover:bg-[var(--surface-hover)] disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer font-medium">
                                Geser Kanan &rarr;
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metadata Row: Category & Type (Custom Selects) -->
            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-[var(--border-color)]">
                <div class="inline-flex items-center gap-2">
                    <label for="category_id" class="text-xs text-[var(--text-muted)] font-medium">Kategori <span class="text-rose-500">*</span>:</label>
                    <x-custom-select name="category_id" id="category_id" x-model="categoryId" :options="$categoryOptions" placeholder="Pilih Kategori" min-width="min-w-[210px]" />
                </div>

                <div class="inline-flex items-center gap-2">
                    <label for="project_type" class="text-xs text-[var(--text-muted)] font-medium">Tipe:</label>
                    <x-custom-select name="project_type" id="project_type" x-model="projectType" :options="$projectTypeOptions" min-width="min-w-[200px]" />
                </div>
            </div>

            <!-- Tech Stack Row -->
            <div class="space-y-2 pt-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-[var(--text-muted)] font-medium">Tech Stack <span class="text-rose-500">*</span>:</span>
                    <span class="text-[11px] font-mono text-[var(--text-faint)]" x-text="techList.length + ' dipilih'"></span>
                </div>

                <div class="flex flex-wrap items-center gap-1.5">
                    <template x-for="(tech, index) in techList" :key="index">
                        <span class="inline-flex items-center gap-1 font-mono text-xs px-2.5 py-1 rounded-md bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)]">
                            <span x-text="'#' + tech"></span>
                            <button @click="removeTech(index)" type="button" class="text-[var(--text-muted)] hover:text-rose-500 transition cursor-pointer ml-0.5" aria-label="Hapus stack">&times;</button>
                        </span>
                    </template>

                    <div class="inline-flex items-center gap-1">
                        <input type="text"
                               id="tech_input"
                               x-model="techInput"
                               @keydown.enter.prevent="addTech()"
                               placeholder="+ Tambah stack..."
                               class="text-xs px-2.5 py-1 rounded-md bg-[var(--surface-deep)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-muted)] outline-none focus:border-[var(--primary)] font-mono w-36">
                        <button type="button" @click="addTech()" class="text-xs px-2 py-1 rounded-md bg-[var(--surface-muted)] text-[var(--primary)] hover:bg-[var(--surface-hover)] border border-[var(--border-color)] cursor-pointer">
                            +
                        </button>
                    </div>
                </div>

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
                    <input type="url" name="demo_url" id="demo_url" x-model="demoUrl" placeholder="https://projectmu.com"
                           class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-muted)] text-xs outline-none focus:border-[var(--primary)]">
                </div>

                <div class="space-y-1">
                    <label for="github_url" class="text-xs text-[var(--text-muted)] flex items-center gap-1 font-medium">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                        <span>GitHub Repo (opsional)</span>
                    </label>
                    <input type="url" name="github_url" id="github_url" x-model="githubUrl" placeholder="https://github.com/user/repo"
                           class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-muted)] text-xs outline-none focus:border-[var(--primary)]">
                </div>
            </div>

            <!-- Optional Knowledge, Development Stage & Setup -->
            <details class="group pt-2 border-t border-[var(--border-color)]" :open="detailsOpen">
                <summary @click.prevent="detailsOpen = !detailsOpen" class="text-xs text-[var(--text-muted)] hover:text-[var(--primary)] cursor-pointer select-none flex items-center justify-between py-1 transition">
                    <span class="font-medium">+ Tambah Cerita Teknis, Tahap Rilis &amp; Cara Setup (opsional)</span>
                    <svg class="w-4 h-4 transition" :class="detailsOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="space-y-3 pt-3">
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-[var(--border-color)]/60">
                        <div>
                            <label for="status" class="text-xs font-medium text-[var(--primary)] block">Tahap Pengembangan Karya</label>
                            <p class="text-[11px] text-[var(--text-muted)]">Badge ini tampil di halaman detail karya.</p>
                        </div>
                        <x-custom-select name="status" id="status" x-model="status" :options="$statusOptions" align="right" min-width="min-w-[225px]" />
                    </div>

                    <div class="space-y-1">
                        <label for="description" class="text-xs text-[var(--text-muted)] font-medium">Deskripsi Lengkap / Fitur</label>
                        <textarea name="description" id="description" x-model="description" rows="3" placeholder="Ceritakan fitur utama atau arsitekturnya..."
                                  class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-muted)] text-xs outline-none focus:border-[var(--primary)]"></textarea>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label for="challenges" class="text-xs text-[var(--text-muted)] font-medium">Tantangan Teknis</label>
                            <textarea name="challenges" id="challenges" x-model="challenges" rows="2" placeholder="Masalah tersulit yang ditemui..."
                                      class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-muted)] text-xs outline-none focus:border-[var(--primary)]"></textarea>
                        </div>
                        <div class="space-y-1">
                            <label for="learnings" class="text-xs text-[var(--text-muted)] font-medium">Pelajaran Berharga</label>
                            <textarea name="learnings" id="learnings" x-model="learnings" rows="2" placeholder="Solusi atau hal baru yang dipelajari..."
                                      class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-muted)] text-xs outline-none focus:border-[var(--primary)]"></textarea>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label for="setup_instructions" class="text-xs text-[var(--text-muted)] font-medium">Cara Run di Lokal</label>
                        <textarea name="setup_instructions" id="setup_instructions" x-model="setupInstructions" rows="2" placeholder="git clone ...&#10;composer install&#10;npm run dev"
                                  class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-muted)] font-mono text-xs outline-none focus:border-[var(--primary)]"></textarea>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div class="space-y-1">
                            <label for="prototype_url" class="text-xs text-[var(--text-muted)] font-medium">Figma / Prototype URL</label>
                            <input type="url" name="prototype_url" id="prototype_url" x-model="prototypeUrl" placeholder="https://figma.com/proto/..."
                                   class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-muted)] text-xs outline-none focus:border-[var(--primary)]">
                        </div>
                        <div class="space-y-1">
                            <label for="thumbnail_url" class="text-xs text-[var(--text-muted)] font-medium">Atau URL Gambar Eksternal (jika tanpa file)</label>
                            <input type="url" name="thumbnail_url" id="thumbnail_url" x-model="thumbnailUrl" placeholder="https://images.unsplash.com/..."
                                   class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-muted)] text-xs outline-none focus:border-[var(--primary)]">
                        </div>
                    </div>
                </div>
            </details>

            <!-- Bottom Toolbar -->
            <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-[var(--border-color)]">
                <div class="flex items-center gap-1">
                    <label for="media_images_input" class="p-2 rounded-lg text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-muted)] transition cursor-pointer flex items-center gap-1.5 text-xs select-none" title="Pilih hingga 9 gambar">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="hidden sm:inline font-medium">Foto / Media</span>
                        <span x-show="mediaItems.length > 0"
                              x-cloak
                              class="px-1.5 py-0.5 rounded-full text-[10px] font-mono bg-[var(--surface-muted)] text-[var(--primary)]"
                              x-text="mediaItems.length"></span>
                        <input type="file" id="media_images_input" accept="image/*" multiple @change="handleFileSelect($event)" class="hidden">
                    </label>

                    <button type="button" @click="showLinks = !showLinks"
                            :class="showLinks ? 'text-[var(--primary)] bg-[var(--surface-muted)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-muted)]'"
                            class="p-2 rounded-lg transition cursor-pointer flex items-center gap-1.5 text-xs select-none" title="Tautkan Demo / GitHub">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <span class="hidden sm:inline font-medium">Link Demo</span>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('projects.show', $project->slug) }}" class="btn-secondary text-xs py-2.5 px-3.5">
                        Batal
                    </a>
                    <x-button type="submit" ::disabled="isSubmitting" variant="primary" size="md">
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                    </x-button>
                </div>
            </div>
        </form>
    </div>

    <!-- Danger Zone with Token Harbor Danger Modal -->
    <div class="p-4 sm:p-5 rounded-2xl border border-rose-500/25 bg-rose-500/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="space-y-0.5">
            <h3 class="text-sm font-bold text-rose-400">Hapus Karya Ini</h3>
            <p class="text-xs text-[var(--text-muted)]">Semua data karya, komentar, dan upvote terkait akan dihapus permanen.</p>
        </div>

        <button type="button"
                @click="deleteProjectModalOpen = true"
                class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs transition cursor-pointer shrink-0 self-start sm:self-auto">
            Hapus Karya
        </button>
    </div>

    <!-- Delete Project Danger Modal -->
    <div x-show="deleteProjectModalOpen"
         x-cloak
         @keydown.escape.window="deleteProjectModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-xs" @click="deleteProjectModalOpen = false"></div>
        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 shadow-2xl space-y-4 text-[var(--primary)]">
                <div class="flex items-start gap-3">
                    <span class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </span>
                    <div class="min-w-0 space-y-1">
                        <h3 class="text-base font-bold text-rose-400">Hapus Karya Permanen</h3>
                        <p class="text-xs text-[var(--text-muted)] break-words">{{ $project->title }}</p>
                    </div>
                </div>

                <p class="text-xs text-rose-200/80 px-3.5 py-2.5 rounded-xl bg-rose-500/10 border border-rose-500/25">
                    Tindakan ini tidak dapat dibatalkan. Seluruh komentar, upvote, dan gambar terkait akan dihapus permanen.
                </p>

                <form action="{{ route('projects.destroy', $project->slug) }}" method="POST" class="flex items-center justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteProjectModalOpen = false" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs cursor-pointer transition">Hapus Permanen</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Fullscreen Image Preview Lightbox -->
    <div x-show="lightboxOpen && mediaItems.length > 0"
         x-cloak
         @keydown.escape.window="lightboxOpen = false"
         @keydown.arrow-left.window="if (lightboxOpen) prevImage()"
         @keydown.arrow-right.window="if (lightboxOpen) nextImage()"
         class="fixed inset-0 z-50 bg-black/95 flex flex-col justify-between p-4 select-none"
         role="dialog"
         aria-modal="true"
         aria-label="Pratinjau penuh gambar">
        <div class="flex items-center justify-between gap-3 text-white">
            <div class="flex items-center gap-2 min-w-0">
                <span class="px-2.5 py-1 rounded-md text-xs font-mono font-semibold bg-white/10"
                      x-text="(activeImageIndex + 1) + ' / ' + mediaItems.length"></span>
                <span x-show="activeImageIndex === 0"
                      class="px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-white text-zinc-950">Cover</span>
                <span class="text-xs text-white/70 truncate" x-text="mediaItems[activeImageIndex]?.name"></span>
            </div>
            <button type="button"
                    @click="lightboxOpen = false"
                    aria-label="Tutup pratinjau"
                    class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="relative flex-1 min-h-0 flex items-center justify-center my-2" @click.self="lightboxOpen = false">
            <button type="button"
                    x-show="mediaItems.length > 1"
                    @click.stop="prevImage()"
                    aria-label="Gambar sebelumnya"
                    class="absolute left-2 sm:left-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <img :src="mediaItems[activeImageIndex]?.url"
                 :alt="mediaItems[activeImageIndex]?.name || 'Pratinjau penuh'"
                 class="max-h-[80vh] max-w-[92vw] object-contain rounded-lg">

            <button type="button"
                    x-show="mediaItems.length > 1"
                    @click.stop="nextImage()"
                    aria-label="Gambar berikutnya"
                    class="absolute right-2 sm:right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <div x-show="mediaItems.length > 1" class="flex items-center justify-center gap-2 overflow-x-auto pb-1">
            <template x-for="(img, lIdx) in mediaItems" :key="'lb-' + img.url + '-' + lIdx">
                <button type="button"
                        @click="activeImageIndex = lIdx"
                        :class="activeImageIndex === lIdx ? 'ring-2 ring-white opacity-100' : 'opacity-50 hover:opacity-85'"
                        class="w-12 h-12 rounded-lg overflow-hidden shrink-0 transition cursor-pointer bg-zinc-900">
                    <img :src="img.url" :alt="img.name" class="w-full h-full object-cover">
                </button>
            </template>
        </div>
    </div>

</div>
@endsection
