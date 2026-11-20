@extends('layouts.app')

@section('title', 'Draft Karya — NampungYuk')
@section('header_title', 'Draft Karya')

@section('content')
<div class="space-y-5 max-w-3xl mx-auto">

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-1.5 text-xs text-[var(--text-muted)] hover:text-[var(--primary)] transition font-medium mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Feed</span>
            </a>
            <h1 class="text-2xl font-black text-[var(--primary)] tracking-tight">Draft Karya</h1>
            <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1">
                Karya yang disimpan ke draft atau tersimpan otomatis saat halaman ke-refresh hanya terlihat oleh kamu.
            </p>
        </div>

        <a href="{{ route('projects.create') }}" class="btn-primary text-xs py-2.5 px-4 shrink-0 inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tulis Karya Baru</span>
        </a>
    </div>

    @if($drafts->count() === 0)
        <x-empty-state title="Belum ada draft tersimpan" description="Mulai tulis karya baru dan klik Simpan ke Draft, atau biarkan sistem menyimpannya otomatis saat mengetik." />
    @else
        <div class="space-y-3">
            @foreach($drafts as $draft)
                <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 space-y-3 shadow-xs">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[10px] font-mono font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">
                                    Draft
                                </span>
                                @if($draft->category)
                                    <span class="text-[11px] font-mono text-[var(--text-muted)]">{{ $draft->category->name }}</span>
                                @endif
                                <span class="text-[11px] font-mono text-[var(--text-faint)]">· Diperbarui {{ $draft->updated_at?->diffForHumans() }}</span>
                            </div>

                            <h2 class="text-base font-bold text-[var(--primary)] truncate">
                                {{ $draft->title ?: 'Draft Tanpa Judul' }}
                            </h2>

                            @if($draft->tagline)
                                <p class="text-xs text-[var(--text-muted)] line-clamp-2">{{ $draft->tagline }}</p>
                            @else
                                <p class="text-xs text-[var(--text-faint)] italic">Belum ada tagline.</p>
                            @endif
                        </div>
                    </div>

                    @if(! empty($draft->tech_stacks))
                        <div class="flex flex-wrap items-center gap-1.5">
                            @foreach(array_slice($draft->tech_stacks, 0, 6) as $stack)
                                <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-[var(--surface-muted)] text-[var(--text-muted)]">#{{ $stack }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-[var(--border-color)]">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('projects.create', ['draft' => $draft->slug]) }}" class="btn-primary text-xs py-1.5 px-3.5">
                                Lanjutkan Edit
                            </a>
                            <form method="POST" action="{{ route('projects.publish', $draft->slug) }}">
                                @csrf
                                <button type="submit" class="btn-secondary text-xs py-1.5 px-3.5 cursor-pointer">
                                    Publikasikan
                                </button>
                            </form>
                        </div>

                        <form method="POST" action="{{ route('projects.destroy', $draft->slug) }}" onsubmit="return confirm('Hapus draft ini secara permanen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-rose-400 hover:bg-rose-950/30 px-2.5 py-1.5 rounded-lg transition cursor-pointer">
                                Hapus Draft
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if($drafts->hasPages())
            <div class="pt-2">{{ $drafts->links() }}</div>
        @endif
    @endif

</div>
@endsection
