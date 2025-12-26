@php
    $selectedMedia = $getSelectedMedia();
    $isMultiple = $isMultiple();
    $acceptedTypes = $getAcceptedFileTypes();
    $cssPath = public_path('vendor/media-library-pro/css/media-library-pro.css');
@endphp

@if(file_exists($cssPath))
    <style>
        {!! file_get_contents($cssPath) !!}
    </style>
@endif

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="mediaPicker({
            selected: @js($selectedMedia),
            multiple: @js($isMultiple),
            acceptedTypes: @js($acceptedTypes),
            baseUrl: @js(url('/media-library-pro/serve')),
            statePath: @js($getStatePath())
        })"
        class="space-y-2"
    >
        {{-- Selected Media Display --}}
        <div class="flex flex-wrap gap-2" x-show="selected.length > 0">
            <template x-for="mediaId in selected" :key="mediaId">
                <div class="relative group">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded overflow-hidden ring-2 ring-primary-500">
                        <img 
                            x-bind:src="getMediaUrl(mediaId)"
                            class="w-full h-full object-cover"
                            x-bind:alt="`Media ${mediaId}`"
                        />
                    </div>
                    <button
                        type="button"
                        x-on:click="removeMedia(mediaId)"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 shadow-lg"
                    >
                        ×
                    </button>
                </div>
            </template>
        </div>
        
        {{-- Fallback pour le rendu initial côté serveur --}}
        @if(!empty($selectedMedia))
            <div class="flex flex-wrap gap-2" x-show="false">
                @foreach($selectedMedia as $mediaId)
                    @php
                        // Utiliser le nouveau système MediaFile
                        $media = \Xavier\MediaLibraryPro\Models\MediaFile::find($mediaId);
                        if (!$media) {
                            // Essayer par UUID si c'est un UUID
                            $media = \Xavier\MediaLibraryPro\Models\MediaFile::where('uuid', $mediaId)->first();
                        }
                    @endphp
                    @if($media)
                        <div class="relative group">
                            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded overflow-hidden ring-2 ring-primary-500">
                                @if(str_starts_with($media->mime_type, 'image/'))
                                    <img src="{{ route('media-library-pro.serve', ['media' => $media->uuid]) }}" alt="{{ $media->file_name }}" class="w-full h-full object-cover" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <button
                                type="button"
                                x-on:click="removeMedia('{{ $media->uuid }}')"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 shadow-lg"
                            >
                                ×
                            </button>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Open Media Library Button --}}
        <x-filament::button
            type="button"
            x-on:click="open = true"
            size="sm"
            color="gray"
            outlined
        >
            Sélectionner depuis la bibliothèque
        </x-filament::button>

        {{-- Hidden Input --}}
        <input
            type="hidden"
            x-bind:value="multiple ? JSON.stringify(selected) : (selected.length > 0 ? selected[0] : '')"
            {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}"
        />

        {{-- Modal --}}
        <div
            x-show="open"
            x-cloak
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            @click.self="open = false"
        >
            {{-- Backdrop --}}
            <div
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity"
                style="background-color: rgba(0, 0, 0, 0.5);"
                @click="open = false"
            ></div>

            {{-- Modal Content --}}
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div
                    x-show="open"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full modal-content-bg"
                    style="position: relative; z-index: 2;"
                    @click.stop
                >
                    <div class="px-4 pt-5 pb-4 sm:p-6 modal-content-bg">
                        {{-- Header --}}
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Sélectionner un média
                            </h3>
                            <button
                                type="button"
                                x-on:click="open = false"
                                class="inline-flex items-center justify-center rounded-md p-1.5 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors"
                                aria-label="Fermer"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Media Library Content --}}
                        <div class="max-h-[70vh] overflow-y-auto">
                            @livewire('media-library-pro::media-library', ['pickerMode' => true, 'multiple' => $isMultiple, 'acceptedTypes' => $acceptedTypes])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('mediaPicker', (config) => ({
            open: false,
            selected: config.selected || [],
            multiple: config.multiple || false,
            acceptedTypes: config.acceptedTypes || [],
            baseUrl: config.baseUrl || '',
            statePath: config.statePath || '',
            
            getMediaUrl(mediaId) {
                // mediaId peut être un UUID ou un ID
                return `${this.baseUrl}/${mediaId}`;
            },
            
            init() {
                // Écouter les événements globaux de sélection depuis le composant Livewire
                window.addEventListener('media-library-picker-select', (e) => {
                    this.toggleMedia(e.detail.mediaId);
                });
            },
            
            toggleMedia(mediaId) {
                mediaId = parseInt(mediaId);
                if (this.multiple) {
                    const index = this.selected.indexOf(mediaId);
                    if (index > -1) {
                        this.selected.splice(index, 1);
                    } else {
                        this.selected.push(mediaId);
                    }
                } else {
                    this.selected = [mediaId];
                    this.open = false;
                }
                this.updateForm();
            },
            
            removeMedia(mediaId) {
                const index = this.selected.indexOf(parseInt(mediaId));
                if (index > -1) {
                    this.selected.splice(index, 1);
                    this.updateForm();
                }
            },
            
            updateForm() {
                const hiddenInput = this.$el.querySelector('input[type="hidden"][wire\\:model]');
                if (hiddenInput) {
                    const value = this.multiple 
                        ? JSON.stringify(this.selected) 
                        : (this.selected.length > 0 ? this.selected[0].toString() : '');
                    
                    hiddenInput.value = value;
                    
                    // Déclencher les événements pour mettre à jour Livewire
                    hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    // Utiliser $wire si disponible (Filament v3+)
                    if (this.$wire && this.statePath) {
                        this.$wire.set(this.statePath, value);
                    }
                }
            }
        }));
    });
</script>

