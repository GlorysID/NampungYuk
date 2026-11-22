@extends('layouts.app')

@section('title', 'Pamerkan Karya Codingan — NampungYuk')
@section('meta_description', 'Bagikan karya project codingan kamu kepada komunitas developer.')
@section('header_title', 'Pamerkan Karya')

@section('content')
@php
    $editingDraft = $draft ?? null;
    $initialTitle = old('title', $editingDraft?->title === 'Draft Tanpa Judul' ? '' : ($editingDraft?->title ?? ''));
    $initialTagline = old('tagline', $editingDraft?->tagline ?? '');
    $initialCategoryId = old('category_id', $editingDraft?->category_id ? (string) $editingDraft->category_id : '');
    $initialProjectType = old('project_type', $editingDraft?->project_type ?? 'web');
    $initialStatus = old('status', $editingDraft?->status ?? 'beta');
    $initialVisibility = old('visibility', ($editingDraft && $editingDraft->visibility !== 'draft') ? $editingDraft->visibility : 'public');
    $initialTechList = old('tech_stacks')
        ? array_values(array_filter(array_map('trim', explode(',', old('tech_stacks')))))
        : ($editingDraft && is_array($editingDraft->tech_stacks) && count($editingDraft->tech_stacks)
            ? $editingDraft->tech_stacks
            : ['Laravel', 'TailwindCSS']);
    $initialDemoUrl = old('demo_url', $editingDraft?->demo_url ?? '');
    $initialGithubUrl = old('github_url', $editingDraft?->github_url ?? '');
    $initialPrototypeUrl = old('prototype_url', $editingDraft?->prototype_url ?? '');
    $initialThumbnailUrl = old('thumbnail_url', '');
    $initialDescription = old('description', $editingDraft?->description ?? '');
    $initialChallenges = old('challenges', $editingDraft?->challenges ?? '');
    $initialLearnings = old('learnings', $editingDraft?->learnings ?? '');
    $initialSetup = old('setup_instructions', $editingDraft?->setup_instructions ?? '');
    $initialDraftId = old('draft_id', $editingDraft?->id);
    $defaultThumbnailPlaceholder = 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80';
    $existingDraftImages = $editingDraft
        ? array_values(array_filter(
            $editingDraft->galleryImages(),
            static fn (string $url): bool => $url !== $defaultThumbnailPlaceholder
        ))
        : [];

    $visibilityOptions = [
        ['value' => 'public', 'label' => 'Publik', 'hint' => 'Tampil di feed & profil'],
        ['value' => 'private', 'label' => 'Privat', 'hint' => 'Hanya terlihat oleh kamu'],
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
    ];

    $prevUrl = url()->previous();
    $prevHost = parse_url($prevUrl, PHP_URL_HOST);
    $prevPath = (string) (parse_url($prevUrl, PHP_URL_PATH) ?? '');
    $currentPath = (string) (parse_url(url()->current(), PHP_URL_PATH) ?? '');
    $sameOrigin = $prevUrl
        && ($prevHost === null || strcasecmp((string) $prevHost, (string) request()->getHost()) === 0)
        && $prevPath !== $currentPath;
    $backUrl = $sameOrigin ? $prevUrl : route('projects.index');
    $backLabel = match (true) {
        str_starts_with($prevPath, '/@') && $sameOrigin => 'Kembali ke Profil',
        str_starts_with($prevPath, '/komunitas') && $sameOrigin => 'Kembali ke Komunitas',
        str_starts_with($prevPath, '/draft') && $sameOrigin => 'Kembali ke Draft',
        $sameOrigin && $prevPath !== '/' => 'Kembali',
        default => 'Kembali ke Feed',
    };
@endphp

<div class="space-y-4 max-w-2xl mx-auto"
     x-data="{
         draftKey: 'ny_project_draft_{{ Auth::id() }}',
         autosaveUrl: @js(route('projects.autosave')),
         csrfToken: @js(csrf_token()),
         shouldClearSaved: {{ session('clear_project_draft') ? 'true' : 'false' }},
         hasServerDraft: {{ $editingDraft ? 'true' : 'false' }},
         hasValidationErrors: {{ $errors->any() ? 'true' : 'false' }},
         draftId: @js($initialDraftId ? (int) $initialDraftId : null),
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
         selectedImages: [],
         maxImages: 9,
         existingDraftImages: @js($existingDraftImages),
         activeImageIndex: 0,
         lightboxOpen: false,
         dragIndex: null,
         dragOverIndex: null,
         showLinks: {{ ($initialDemoUrl || $initialGithubUrl) ? 'true' : 'false' }},
         detailsOpen: {{ ($initialDescription || $initialChallenges || $initialLearnings || $initialSetup || $initialPrototypeUrl || $initialThumbnailUrl) ? 'true' : 'false' }},
         saveAsDraftFlag: false,
         restoredNotice: false,
         autoSaveStatus: '',
         autoSaveTimer: null,
         isSubmitting: false,
         serverErrors: [],

         init() {
             if (this.shouldClearSaved) {
                 try { localStorage.removeItem(this.draftKey); } catch (e) {}
             } else if (! this.hasServerDraft) {
                 this.restoreFromLocal();
             }

             const watchFields = [
                 'title', 'tagline', 'categoryId', 'projectType', 'status', 'visibility',
                 'techList', 'demoUrl', 'githubUrl', 'prototypeUrl', 'thumbnailUrl',
                 'description', 'challenges', 'learnings', 'setupInstructions'
             ];
             for (const field of watchFields) {
                 this.$watch(field, () => this.scheduleAutosave());
             }

             const flushBeforeLeave = () => {
                 if (this.isSubmitting) return;
                 this.saveToLocal();
                 this.beaconAutosave();
             };
             window.addEventListener('pagehide', flushBeforeLeave);
             window.addEventListener('beforeunload', flushBeforeLeave);
         },

         snapshot() {
             return {
                 draftId: this.draftId,
                 title: this.title,
                 tagline: this.tagline,
                 categoryId: this.categoryId,
                 projectType: this.projectType,
                 status: this.status,
                 visibility: this.visibility,
                 techList: this.techList,
                 demoUrl: this.demoUrl,
                 githubUrl: this.githubUrl,
                 prototypeUrl: this.prototypeUrl,
                 thumbnailUrl: this.thumbnailUrl,
                 description: this.description,
                 challenges: this.challenges,
                 learnings: this.learnings,
                 setupInstructions: this.setupInstructions,
                 showLinks: this.showLinks,
                 detailsOpen: this.detailsOpen,
                 savedAt: Date.now()
             };
         },

         hasMeaningfulContent() {
             return Boolean(
                 this.title.trim() || this.tagline.trim() || this.description.trim()
                 || this.challenges.trim() || this.learnings.trim() || this.setupInstructions.trim()
                 || this.demoUrl.trim() || this.githubUrl.trim() || this.prototypeUrl.trim()
             );
         },

         saveToLocal() {
             try {
                 if (! this.hasMeaningfulContent() && ! this.draftId) {
                     localStorage.removeItem(this.draftKey);
                     return;
                 }
                 localStorage.setItem(this.draftKey, JSON.stringify(this.snapshot()));
             } catch (e) {}
         },

         restoreFromLocal() {
             try {
                 const raw = localStorage.getItem(this.draftKey);
                 if (! raw) return;
                 const data = JSON.parse(raw);
                 if (! data || typeof data !== 'object') return;

                 if (this.hasValidationErrors) {
                     if (data.draftId && ! this.draftId) {
                         this.draftId = Number(data.draftId);
                     }
                     return;
                 }

                 const hasSavedText = Boolean(
                     (data.title || '').trim() || (data.tagline || '').trim() || (data.description || '').trim()
                     || (data.demoUrl || '').trim() || (data.githubUrl || '').trim()
                 );
                 if (! hasSavedText) return;

                 if (data.draftId) this.draftId = Number(data.draftId);
                 if (typeof data.title === 'string') this.title = data.title;
                 if (typeof data.tagline === 'string') this.tagline = data.tagline;
                 if (typeof data.categoryId === 'string') this.categoryId = data.categoryId;
                 if (typeof data.projectType === 'string') this.projectType = data.projectType;
                 if (typeof data.status === 'string') this.status = data.status;
                 if (typeof data.visibility === 'string') this.visibility = data.visibility;
                 if (Array.isArray(data.techList) && data.techList.length) this.techList = data.techList;
                 if (typeof data.demoUrl === 'string') this.demoUrl = data.demoUrl;
                 if (typeof data.githubUrl === 'string') this.githubUrl = data.githubUrl;
                 if (typeof data.prototypeUrl === 'string') this.prototypeUrl = data.prototypeUrl;
                 if (typeof data.thumbnailUrl === 'string') this.thumbnailUrl = data.thumbnailUrl;
                 if (typeof data.description === 'string') this.description = data.description;
                 if (typeof data.challenges === 'string') this.challenges = data.challenges;
                 if (typeof data.learnings === 'string') this.learnings = data.learnings;
                 if (typeof data.setupInstructions === 'string') this.setupInstructions = data.setupInstructions;
                 if (this.demoUrl || this.githubUrl || data.showLinks) this.showLinks = true;
                 if (this.description || this.challenges || this.learnings || this.setupInstructions || this.prototypeUrl || this.thumbnailUrl || data.detailsOpen) {
                     this.detailsOpen = true;
                 }
                 this.restoredNotice = true;
             } catch (e) {}
         },

         discardLocalDraft() {
             try { localStorage.removeItem(this.draftKey); } catch (e) {}
             this.draftId = null;
             this.title = '';
             this.tagline = '';
             this.categoryId = '';
             this.projectType = 'web';
             this.status = 'beta';
             this.visibility = 'public';
             this.techList = ['Laravel', 'TailwindCSS'];
             this.demoUrl = '';
             this.githubUrl = '';
             this.prototypeUrl = '';
             this.thumbnailUrl = '';
             this.description = '';
             this.challenges = '';
             this.learnings = '';
             this.setupInstructions = '';
             for (const item of this.selectedImages) {
                 if (item && item.url) {
                     try { URL.revokeObjectURL(item.url); } catch (e) {}
                 }
             }
             this.selectedImages = [];
             this.activeImageIndex = 0;
             this.lightboxOpen = false;
             this.dragIndex = null;
             this.dragOverIndex = null;
             this.restoredNotice = false;
             this.autoSaveStatus = '';
         },

         scheduleAutosave() {
             this.saveToLocal();
             if (this.autoSaveTimer) clearTimeout(this.autoSaveTimer);
             this.autoSaveTimer = setTimeout(() => this.syncDraftToServer(), 1200);
         },

         async syncDraftToServer() {
             if (this.isSubmitting) return;
             if (! this.hasMeaningfulContent() && ! this.draftId) return;

             this.autoSaveStatus = 'Menyimpan draft...';
             try {
                 const res = await fetch(this.autosaveUrl, {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': this.csrfToken,
                     },
                     body: JSON.stringify({
                         draft_id: this.draftId,
                         title: this.title,
                         tagline: this.tagline,
                         category_id: this.categoryId || null,
                         project_type: this.projectType,
                         status: this.status,
                         tech_stacks: this.techStacksJoined,
                         demo_url: this.demoUrl,
                         github_url: this.githubUrl,
                         prototype_url: this.prototypeUrl,
                         thumbnail_url: this.thumbnailUrl,
                         description: this.description,
                         challenges: this.challenges,
                         learnings: this.learnings,
                         setup_instructions: this.setupInstructions,
                     }),
                 });
                 if (! res.ok) return;
                 const data = await res.json();
                 if (data.saved && data.draft_id) {
                     this.draftId = Number(data.draft_id);
                     this.saveToLocal();
                     this.autoSaveStatus = 'Tersimpan otomatis ke Draft · ' + (data.saved_at || '');
                 }
             } catch (e) {
                 this.autoSaveStatus = 'Draft tersimpan di browser';
             }
         },

         beaconAutosave() {
             if (! this.hasMeaningfulContent() && ! this.draftId) return;
             if (! navigator.sendBeacon) return;
             const fd = new FormData();
             fd.append('_token', this.csrfToken);
             if (this.draftId) fd.append('draft_id', String(this.draftId));
             fd.append('title', this.title);
             fd.append('tagline', this.tagline);
             if (this.categoryId) fd.append('category_id', this.categoryId);
             fd.append('project_type', this.projectType);
             fd.append('status', this.status);
             fd.append('tech_stacks', this.techStacksJoined);
             fd.append('demo_url', this.demoUrl);
             fd.append('github_url', this.githubUrl);
             fd.append('prototype_url', this.prototypeUrl);
             fd.append('thumbnail_url', this.thumbnailUrl);
             fd.append('description', this.description);
             fd.append('challenges', this.challenges);
             fd.append('learnings', this.learnings);
             fd.append('setup_instructions', this.setupInstructions);
             navigator.sendBeacon(this.autosaveUrl, fd);
         },

         handleFileSelect(event) {
             const files = Array.from(event.target.files || []).filter(f => f && f.type && f.type.startsWith('image/'));
             if (! files.length) {
                 event.target.value = '';
                 return;
             }
             const wasEmpty = this.selectedImages.length === 0;
             const remaining = this.maxImages - this.selectedImages.length;
             if (remaining <= 0) {
                 if (window.notify) notify('Maksimal ' + this.maxImages + ' gambar per postingan.');
                 event.target.value = '';
                 return;
             }
             if (files.length > remaining && window.notify) {
                 notify('Maksimal ' + this.maxImages + ' gambar. Hanya ' + remaining + ' gambar tambahan yang dimasukkan.');
             }
             for (const file of files.slice(0, remaining)) {
                 this.selectedImages.push({
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
         removeSelectedImage(index) {
             const removed = this.selectedImages.splice(index, 1)[0];
             if (removed && removed.url) {
                 try { URL.revokeObjectURL(removed.url); } catch (e) {}
             }
             if (this.selectedImages.length === 0) {
                 this.activeImageIndex = 0;
                 this.lightboxOpen = false;
             } else if (this.activeImageIndex >= this.selectedImages.length) {
                 this.activeImageIndex = this.selectedImages.length - 1;
             } else if (index < this.activeImageIndex) {
                 this.activeImageIndex -= 1;
             }
         },
         moveImage(fromIndex, toIndex) {
             if (fromIndex === null || toIndex === null) return;
             if (fromIndex === toIndex || fromIndex < 0 || toIndex < 0) return;
             if (fromIndex >= this.selectedImages.length || toIndex >= this.selectedImages.length) return;
             const activeItem = this.selectedImages[this.activeImageIndex];
             const [moved] = this.selectedImages.splice(fromIndex, 1);
             this.selectedImages.splice(toIndex, 0, moved);
             const newActiveIdx = this.selectedImages.indexOf(activeItem);
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
             if (! this.selectedImages.length) return;
             if (idx !== null) this.activeImageIndex = idx;
             this.lightboxOpen = true;
         },
         prevImage() {
             if (this.selectedImages.length <= 1) return;
             this.activeImageIndex = (this.activeImageIndex - 1 + this.selectedImages.length) % this.selectedImages.length;
         },
         nextImage() {
             if (this.selectedImages.length <= 1) return;
             this.activeImageIndex = (this.activeImageIndex + 1) % this.selectedImages.length;
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
             this.saveToLocal();

             try {
                 const fd = new FormData(formEl);
                 fd.set('tech_stacks', this.techStacksJoined);
                 fd.set('save_as_draft', this.saveAsDraftFlag ? '1' : '0');
                 if (this.draftId) fd.set('draft_id', String(this.draftId));
                 fd.delete('thumbnail');
                 fd.delete('images[]');
                 fd.delete('images');
                 if (this.selectedImages.length > 0) {
                     fd.set('thumbnail', this.selectedImages[0].file);
                     for (let i = 1; i < this.selectedImages.length; i++) {
                         fd.append('images[]', this.selectedImages[i].file);
                     }
                 }

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
                             if (['prototype_url', 'thumbnail_url', 'description', 'challenges', 'learnings', 'setup_instructions'].includes(field)) {
                                 this.detailsOpen = true;
                             }
                             if (Array.isArray(msgs)) {
                                 collected.push(...msgs);
                             }
                         }
                     }
                     this.serverErrors = collected.length ? collected : [data.message || 'Terjadi kesalahan saat menyimpan karya. Data dan gambar kamu tetap aman di halaman ini.'];
                     if (window.notify) notify(this.serverErrors[0]);
                     window.scrollTo({ top: 0, behavior: 'smooth' });
                     return;
                 }

                 try { localStorage.removeItem(this.draftKey); } catch (e) {}
                 window.location.href = data.redirect_url || '/';
             } catch (err) {
                 this.isSubmitting = false;
                 const msg = 'Koneksi gagal atau server bermasalah. Data dan gambar kamu tetap aman dan tersimpan di Draft.';
                 this.serverErrors = [msg];
                 if (window.notify) notify(msg);
             }
         },
         submitAsDraft() {
             this.serverErrors = [];
             if (! this.validateOptionalUrls()) return;
             this.saveAsDraftFlag = true;
             this.$nextTick(() => this.submitViaFetch(this.$refs.composerForm));
         },
         submitForm(e) {
             this.serverErrors = [];
             if (this.saveAsDraftFlag) {
                 if (! this.validateOptionalUrls()) return;
                 this.submitViaFetch(e.target);
                 return;
             }
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
             if (! this.validateOptionalUrls()) return;
             this.submitViaFetch(e.target);
         }
     }">

    <!-- Contextual Back Link + Drafts Shortcut -->
    <div class="flex items-center justify-between gap-3">
        <a href="{{ $backUrl }}"
           class="inline-flex items-center gap-1.5 text-xs text-[var(--text-muted)] hover:text-[var(--primary)] transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>{{ $backLabel }}</span>
        </a>

        <a href="{{ route('projects.drafts') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[var(--surface)] border border-[var(--border-color)] hover:border-[var(--border-strong)] text-[var(--primary)] transition">
            <span>Draft Saya</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono bg-[var(--surface-muted)] text-[var(--text-muted)]">{{ (int) ($draftsCount ?? 0) }}</span>
        </a>
    </div>

    <!-- Restored / Auto-saved Banner -->
    <div x-show="restoredNotice || autoSaveStatus || hasServerDraft" x-cloak
         class="px-4 py-2.5 rounded-xl bg-[var(--surface)] border border-[var(--border-color)] text-xs flex items-center justify-between gap-3">
        <div class="flex items-center gap-2 min-w-0">
            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
            <span class="text-[var(--text-muted)] truncate"
                  x-text="autoSaveStatus || (hasServerDraft ? 'Melanjutkan edit dari Draft tersimpan.' : 'Draft lokal dipulihkan otomatis setelah refresh/keluar.')"></span>
        </div>
        <button type="button" x-show="restoredNotice && !hasServerDraft" @click="discardLocalDraft()"
                class="text-[11px] font-semibold text-rose-400 hover:underline shrink-0 cursor-pointer">
            Reset Form
        </button>
    </div>

    <!-- Dynamic AJAX Error Summary (no page refresh, preserves files & inputs) -->
    <div x-show="serverErrors.length > 0" x-cloak class="p-4 rounded-xl bg-rose-950/40 border border-rose-900 text-rose-300 text-xs space-y-1" role="alert">
        <p class="font-bold flex items-center gap-1.5">
            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Mohon periksa data berikut (gambar &amp; tulisan kamu tetap aman):</span>
        </p>
        <ul class="list-disc list-inside ml-5 space-y-0.5">
            <template x-for="(err, idx) in serverErrors" :key="idx">
                <li x-text="err"></li>
            </template>
        </ul>
    </div>

    <!-- Error Summary if any -->
    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-950/40 border border-rose-900 text-rose-300 text-xs space-y-1" role="alert">
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

    <!-- Quick Composer Card -->
    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 sm:p-6 shadow-xs">
        <form x-ref="composerForm" action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" novalidate @submit.prevent="submitForm($event)" class="space-y-4">
            @csrf

            <!-- Hidden inputs -->
            <input type="hidden" name="tech_stacks" :value="techStacksJoined">
            <input type="hidden" name="draft_id" :value="draftId ?? ''">
            <input type="hidden" name="save_as_draft" :value="saveAsDraftFlag ? '1' : '0'">

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
            <div x-show="selectedImages.length > 0" x-cloak class="space-y-3">
                <!-- Header bar -->
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div class="flex flex-wrap items-center gap-2 min-w-0">
                        <span class="font-mono font-semibold text-[var(--primary)]" x-text="selectedImages.length + ' / ' + maxImages + ' gambar'"></span>
                        <span class="text-[var(--text-faint)]">&middot;</span>
                        <span class="text-[var(--text-muted)]" x-text="selectedImages.length > 1 ? 'Geser thumbnail di bawah untuk atur urutan' : 'Klik gambar untuk perbesar'"></span>
                    </div>

                    <label x-show="selectedImages.length < maxImages"
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
                        <img :src="selectedImages[activeImageIndex]?.url"
                             :alt="selectedImages[activeImageIndex]?.name || 'Pratinjau gambar'"
                             class="max-h-[420px] w-auto max-w-full object-contain block select-none">
                    </button>

                    <!-- Order / Cover pill -->
                    <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-md text-[10.5px] font-mono font-semibold bg-black/75 text-white border border-white/10 pointer-events-none"
                          x-text="'#' + (activeImageIndex + 1) + (activeImageIndex === 0 ? ' · Cover' : '')"></span>

                    <!-- Zoom & Remove controls -->
                    <div class="absolute top-2.5 right-2.5 flex items-center gap-1.5">
                        <button type="button"
                                @click.stop="openLightbox(activeImageIndex)"
                                class="w-7 h-7 rounded-full bg-black/75 hover:bg-black text-white flex items-center justify-center transition cursor-pointer"
                                title="Perbesar gambar">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0zm-6-3v6m-3-3h6"/></svg>
                        </button>
                        <button type="button"
                                @click.stop="removeSelectedImage(activeImageIndex)"
                                class="w-7 h-7 rounded-full bg-black/75 hover:bg-black text-white flex items-center justify-center transition cursor-pointer"
                                title="Hapus gambar ini">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Stage Prev/Next Arrows -->
                    <button type="button"
                            x-show="selectedImages.length > 1"
                            @click.stop="prevImage()"
                            aria-label="Gambar sebelumnya"
                            class="absolute left-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/70 hover:bg-black text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button"
                            x-show="selectedImages.length > 1"
                            @click.stop="nextImage()"
                            aria-label="Gambar berikutnya"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/70 hover:bg-black text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <!-- Hover hint -->
                    <span class="absolute bottom-2.5 right-2.5 px-2.5 py-1 rounded-md text-[10.5px] font-medium bg-black/75 text-white border border-white/10 opacity-0 group-hover:opacity-100 transition pointer-events-none">
                        Klik untuk lihat penuh
                    </span>
                </div>

                <!-- Bottom Draggable Thumbnail Filmstrip Bar (multi-image) -->
                <div x-show="selectedImages.length > 1" class="space-y-2">
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-0.5 select-none">
                        <template x-for="(img, idx) in selectedImages" :key="img.url">
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

                                <!-- Order badge -->
                                <span :class="idx === 0 ? 'bg-[var(--primary)] text-[var(--primary-text)]' : 'bg-black/75 text-white'"
                                      class="absolute top-1 left-1 min-w-[18px] h-[18px] px-1 rounded text-[10px] font-mono font-bold flex items-center justify-center pointer-events-none"
                                      x-text="idx + 1"></span>

                                <!-- Remove button -->
                                <button type="button"
                                        @click.stop="removeSelectedImage(idx)"
                                        aria-label="Hapus gambar"
                                        class="absolute top-1 right-1 w-4 h-4 rounded-full bg-black/75 hover:bg-black text-white flex items-center justify-center transition cursor-pointer">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>

                        <!-- Append tile -->
                        <label x-show="selectedImages.length < maxImages"
                               for="media_images_input"
                               title="Tambah foto lagi"
                               class="w-16 h-16 sm:w-[72px] sm:h-[72px] shrink-0 rounded-xl border border-dashed border-[var(--border-color)] hover:border-[var(--primary)] bg-[var(--surface-muted)]/60 hover:bg-[var(--surface-hover)] text-[var(--text-muted)] hover:text-[var(--primary)] flex flex-col items-center justify-center gap-0.5 cursor-pointer transition select-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span class="text-[10px] font-medium">Tambah</span>
                        </label>
                    </div>

                    <!-- Reorder helper + touch shift buttons -->
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
                                    :disabled="activeImageIndex >= selectedImages.length - 1"
                                    class="px-2 py-1 rounded-md border border-[var(--border-color)] bg-[var(--surface-muted)] text-[var(--primary)] hover:bg-[var(--surface-hover)] disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer font-medium">
                                Geser Kanan &rarr;
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="selectedImages.length === 0 && existingDraftImages.length > 0" x-cloak class="space-y-2 p-3 rounded-xl bg-[var(--surface-muted)]/60 border border-[var(--border-color)]">
                <p class="text-[11px] text-[var(--text-muted)]">Gambar tersimpan di draft (unggah gambar baru untuk mengganti cover atau menambah galeri):</p>
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                    <template x-for="(draftImg, dIdx) in existingDraftImages" :key="dIdx">
                        <div class="relative aspect-[4/3] rounded-lg overflow-hidden border border-[var(--border-color)] bg-[var(--surface-deep)]">
                            <img :src="draftImg" alt="" class="w-full h-full object-cover">
                        </div>
                    </template>
                </div>
            </div>

            <!-- Metadata Row: Category & Type (Custom Selects) -->
            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-[var(--border-color)]">
                <!-- Category Select -->
                <div class="inline-flex items-center gap-2">
                    <label for="category_id" class="text-xs text-[var(--text-muted)] font-medium">Kategori <span class="text-rose-500">*</span>:</label>
                    <x-custom-select name="category_id" id="category_id" x-model="categoryId" :options="$categoryOptions" placeholder="Pilih Kategori" min-width="min-w-[210px]" />
                </div>

                <!-- Project Type -->
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

                <!-- Chips container -->
                <div class="flex flex-wrap items-center gap-1.5">
                    <template x-for="(tech, index) in techList" :key="index">
                        <span class="inline-flex items-center gap-1 font-mono text-xs px-2.5 py-1 rounded-md bg-[var(--surface-muted)] text-[var(--primary)] border border-[var(--border-color)]">
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
                               class="text-xs px-2.5 py-1 rounded-md bg-[var(--surface-deep)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-muted)] outline-none focus:border-[var(--primary)] font-mono w-36">
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

            <!-- Optional Knowledge, Development Stage & Setup collapsible details -->
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
                            <label for="thumbnail_url" class="text-xs text-[var(--text-muted)] font-medium">Atau URL Gambar Eksternal</label>
                            <input type="url" name="thumbnail_url" id="thumbnail_url" x-model="thumbnailUrl" placeholder="https://images.unsplash.com/..."
                                   class="w-full px-3 py-2 rounded-xl bg-[var(--surface-deep)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-muted)] text-xs outline-none focus:border-[var(--primary)]">
                        </div>
                    </div>
                </div>
            </details>

            <!-- Bottom Toolbar: Attachments + Save Draft + Publish -->
            <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-[var(--border-color)]">
                <div class="flex items-center gap-1">
                    <!-- Upload Media Button -->
                    <label for="media_images_input" class="p-2 rounded-lg text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-muted)] transition cursor-pointer flex items-center gap-1.5 text-xs select-none" title="Pilih 1 hingga 9 gambar sekaligus">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="hidden sm:inline font-medium">Foto / Media</span>
                        <span x-show="selectedImages.length > 0"
                              x-cloak
                              class="px-1.5 py-0.5 rounded-full text-[10px] font-mono bg-[var(--surface-muted)] text-[var(--primary)]"
                              x-text="selectedImages.length"></span>
                        <input type="file" id="media_images_input" accept="image/*" multiple @change="handleFileSelect($event)" class="hidden">
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

                <div class="flex items-center gap-2">
                    <button type="button" @click="submitAsDraft()" :disabled="isSubmitting" class="btn-secondary text-xs py-2.5 px-3.5 cursor-pointer disabled:opacity-50">
                        Simpan ke Draft
                    </button>
                    <x-button type="submit" @click="saveAsDraftFlag = false" ::disabled="isSubmitting" variant="primary" size="md">
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Pamerkan Karya'"></span>
                    </x-button>
                </div>
            </div>
        </form>
    </div>

    <!-- Fullscreen Image Preview Lightbox -->
    <div x-show="lightboxOpen && selectedImages.length > 0"
         x-cloak
         @keydown.escape.window="lightboxOpen = false"
         @keydown.arrow-left.window="if (lightboxOpen) prevImage()"
         @keydown.arrow-right.window="if (lightboxOpen) nextImage()"
         class="fixed inset-0 z-50 bg-black/95 flex flex-col justify-between p-4 select-none"
         role="dialog"
         aria-modal="true"
         aria-label="Pratinjau penuh gambar">
        <!-- Top Bar -->
        <div class="flex items-center justify-between gap-3 text-white">
            <div class="flex items-center gap-2 min-w-0">
                <span class="px-2.5 py-1 rounded-md text-xs font-mono font-semibold bg-white/10"
                      x-text="(activeImageIndex + 1) + ' / ' + selectedImages.length"></span>
                <span x-show="activeImageIndex === 0"
                      class="px-2 py-0.5 rounded-md text-[11px] font-mono font-semibold bg-white text-zinc-950">Cover</span>
                <span class="text-xs text-white/70 truncate" x-text="selectedImages[activeImageIndex]?.name"></span>
            </div>
            <button type="button"
                    @click="lightboxOpen = false"
                    aria-label="Tutup pratinjau"
                    class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Center Stage -->
        <div class="relative flex-1 min-h-0 flex items-center justify-center my-2" @click.self="lightboxOpen = false">
            <button type="button"
                    x-show="selectedImages.length > 1"
                    @click.stop="prevImage()"
                    aria-label="Gambar sebelumnya"
                    class="absolute left-2 sm:left-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <img :src="selectedImages[activeImageIndex]?.url"
                 :alt="selectedImages[activeImageIndex]?.name || 'Pratinjau penuh'"
                 class="max-h-[80vh] max-w-[92vw] object-contain rounded-lg">

            <button type="button"
                    x-show="selectedImages.length > 1"
                    @click.stop="nextImage()"
                    aria-label="Gambar berikutnya"
                    class="absolute right-2 sm:right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <!-- Bottom Mini Filmstrip -->
        <div x-show="selectedImages.length > 1" class="flex items-center justify-center gap-2 overflow-x-auto pb-1">
            <template x-for="(img, lIdx) in selectedImages" :key="'lb-' + img.url">
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
