<div class="space-y-6">
    @include('media-library-pro::livewire.partials.media-library-header')

    {{-- Media Content --}}
    @if($view === 'grid')
        @include('media-library-pro::livewire.partials.media-library-grid')
    @else
        @include('media-library-pro::livewire.partials.media-library-list')
    @endif

    {{-- Pagination --}}
    @if($media->hasPages())
        <div class="mt-4">
            {{ $media->links() }}
        </div>
    @endif

    @include('media-library-pro::livewire.partials.media-library-upload-modal')
    @include('media-library-pro::livewire.partials.media-library-detail-modal')
    @include('media-library-pro::livewire.partials.media-library-rename-modal')
    @include('media-library-pro::livewire.partials.media-library-create-folder-modal')
    @include('media-library-pro::livewire.partials.media-library-move-modal')

</div>
