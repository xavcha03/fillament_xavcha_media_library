{{-- Media Content - Grid View --}}
<div
    class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-8 gap-4"
    x-data="mediaGridSelection($wire)"
>
    @forelse($media as $item)
        @php $isSelected = in_array($item->id, $selectedMediaIds); @endphp
        <div
            class="media-card group relative rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden border transition-all cursor-pointer {{ $isSelected ? 'ring-2 ring-primary-500 border-primary-500 dark:ring-primary-400 dark:border-primary-400' : 'border-gray-200 dark:border-gray-700 hover:ring-2 hover:ring-primary-500' }}"
            data-media-id="{{ $item->id }}"
            x-on:click="handleCardClick($event, {{ $item->id }}, null)"
            x-on:dblclick="handleCardDblClick($event, {{ $item->id }}, '{{ $item->uuid }}')"
        >
            @if(!$pickerMode)
                <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity" @click.stop>
                    <button
                        type="button"
                        wire:click="openDetailModal('{{ $item->uuid }}')"
                        class="p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-500 dark:text-gray-400"
                        title="Détails"
                    >
                        <x-heroicon-o-ellipsis-vertical class="w-5 h-5" />
                    </button>
                </div>
            @endif

            <div class="aspect-square bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden pointer-events-none group">
                @if(str_starts_with($item->mime_type, 'image/'))
                    @php
                        $version = $item->updated_at?->timestamp ?? $item->size ?? time();
                        try {
                            $imageUrl = route('media-library-pro.serve', ['media' => $item->uuid, 't' => $version]);
                        } catch (\Exception $e) {
                            $imageUrl = url('/media-library-pro/serve/' . $item->uuid . '?t=' . $version);
                        }
                    @endphp
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $item->file_name }}"
                        class="w-full h-full object-cover"
                        loading="lazy"
                        onerror="console.error('Failed to load image:', this.src); this.style.display='none';"
                    />
                @else
                    <div class="text-gray-400">
                        @if(str_starts_with($item->mime_type, 'video/'))
                            <x-heroicon-o-video-camera class="w-12 h-12" />
                        @elseif(str_starts_with($item->mime_type, 'audio/'))
                            <x-heroicon-o-musical-note class="w-12 h-12" />
                        @else
                            <x-heroicon-o-document class="w-12 h-12" />
                        @endif
                    </div>
                @endif
            </div>

            <div class="p-3 border-t border-gray-200 dark:border-white/10 flex items-center gap-2">
                <input
                    type="checkbox"
                    class="fi-checkbox-input rounded border-gray-300 text-primary-600 focus:ring-primary-500 flex-shrink-0"
                    @checked($isSelected)
                    x-on:click.stop="const m = $event.shiftKey ? 'shift' : ($event.ctrlKey || $event.metaKey ? 'ctrl' : null); $wire.toggleSelect({{ $item->id }}, m)"
                />
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate min-w-0" title="{{ $item->file_name }}">
                    {{ Str::limit($item->file_name, 30) }}
                </p>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">Aucun média trouvé</p>
        </div>
    @endforelse
</div>

