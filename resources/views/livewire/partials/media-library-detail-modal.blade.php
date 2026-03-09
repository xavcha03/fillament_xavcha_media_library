{{-- Detail Modal --}}
<div
    x-data="{ 
        show: @entangle('showDetailModal').live,
        handleKeydown(event) {
            if (event.key === 'Escape' && this.show) {
                $wire.closeDetailModal();
            }
            if (event.key === 'Enter' && event.ctrlKey && this.show) {
                event.preventDefault();
                $wire.updateMediaDetails();
            }
        }
    }"
    x-show="show"
    x-cloak
    x-transition
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="media-detail-title"
    @keydown.window="handleKeydown"
    x-init="$watch('show', value => { document.body.style.overflow = value ? 'hidden' : ''; })"
>
    {{-- Backdrop --}}
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/70 backdrop-blur-md transition-opacity z-40"
        @click="$wire.closeDetailModal()"
    ></div>

    {{-- Modal --}}
    <div 
        class="fixed inset-0 z-50 flex items-center justify-center p-6 pointer-events-none"
    >
        <div
            x-show="show"
            x-transition:enter="ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-[0.97]"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-[0.97]"
            class="relative mx-auto transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-700 transition-all pointer-events-auto focus:outline-none"
            style="max-width: 72rem; width: calc(100% - 3rem); max-height: 90vh;"
            @click.stop
            tabindex="-1"
        >
            @if($detailMedia)
                {{-- Header --}}
                <div class="fi-modal-header flex items-center justify-between gap-x-4 overflow-hidden px-6 py-4 sm:px-6">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3">
                            <div class="fi-icon-btn flex h-10 w-10 items-center justify-center rounded-lg bg-primary-500 dark:bg-primary-600">
                                @if($detailMedia->isImage())
                                    <x-heroicon-o-photo class="h-5 w-5 text-white" />
                                @elseif($detailMedia->isVideo())
                                    <x-heroicon-o-video-camera class="h-5 w-5 text-white" />
                                @elseif($detailMedia->isAudio())
                                    <x-heroicon-o-musical-note class="h-5 w-5 text-white" />
                                @else
                                    <x-heroicon-o-document class="h-5 w-5 text-white" />
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 id="media-detail-title" class="fi-modal-heading text-lg font-semibold leading-6 text-gray-950 dark:text-white truncate">
                                    {{ $detailMedia->file_name }}
                                </h3>
                                <div class="flex flex-wrap items-center gap-2 mt-1 text-xs">
                                    <span class="fi-badge inline-flex items-center gap-x-1 rounded-md px-2 py-1 font-medium ring-1 ring-inset bg-primary-50 text-primary-700 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/20">
                                        {{ $detailMedia->getFormattedSize() }}
                                    </span>
                                    @if($detailMedia->isImage() && $detailMedia->width && $detailMedia->height)
                                        <span class="text-gray-500 dark:text-gray-400">
                                            {{ $detailMedia->width }} × {{ $detailMedia->height }} px
                                        </span>
                                    @endif
                                    <span class="text-gray-500 dark:text-gray-400 break-all">
                                        {{ $detailMedia->mime_type }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-filament::button
                            icon="heroicon-o-chevron-left"
                            color="gray"
                            size="sm"
                            wire:click="openPreviousDetail"
                            :disabled="$detailIndex === null || $detailIndex <= 0"
                            class="hidden sm:inline-flex"
                        >
                            <span class="hidden lg:inline">Précédent</span>
                        </x-filament::button>
                        <x-filament::button
                            icon="heroicon-o-chevron-right"
                            color="gray"
                            size="sm"
                            wire:click="openNextDetail"
                            :disabled="$detailIndex === null || $detailIndex >= (count($detailMediaIdsOnPage) - 1)"
                            class="hidden sm:inline-flex"
                        >
                            <span class="hidden lg:inline">Suivant</span>
                        </x-filament::button>
                        <x-filament::icon-button
                            icon="heroicon-o-x-mark"
                            color="gray"
                            size="sm"
                            wire:click="closeDetailModal"
                            aria-label="Fermer"
                        />
                    </div>
                </div>

                {{-- Content 2 colonnes --}}
                <div class="fi-modal-content-ctn px-6 py-4" style="max-height: calc(90vh - 200px);">
                    <div class="lg:flex lg:items-start lg:gap-6">
                        {{-- Colonne gauche : Preview --}}
                        <div class="lg:w-2/3 mb-6 lg:mb-0">
                            <x-filament::section>
                                <div class="relative rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                                    @if($detailMedia->isImage())
                                        <div class="flex items-center justify-center bg-gray-100 dark:bg-gray-800 p-4">
                                            <img
                                                src="{{ $this->getMediaImageUrl($detailMedia) }}"
                                                alt="{{ $detailMedia->alt_text ?: $detailMedia->file_name }}"
                                                class="max-w-full max-h-[65vh] object-contain rounded-lg"
                                            />
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center justify-center min-h-[320px] bg-gray-100 dark:bg-gray-800">
                                            @if($detailMedia->isVideo())
                                                <x-heroicon-o-video-camera class="h-24 w-24 text-gray-400 dark:text-gray-500 mb-4" />
                                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Fichier vidéo</p>
                                            @elseif($detailMedia->isAudio())
                                                <x-heroicon-o-musical-note class="h-24 w-24 text-gray-400 dark:text-gray-500 mb-4" />
                                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Fichier audio</p>
                                            @else
                                                <x-heroicon-o-document class="h-24 w-24 text-gray-400 dark:text-gray-500 mb-4" />
                                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Document</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </x-filament::section>
                        </div>

                        {{-- Colonne droite : Métadonnées, Infos, Actions --}}
                        <div class="lg:w-1/3 flex flex-col gap-4">
                            {{-- Métadonnées --}}
                            <x-filament::section>
                                <x-slot name="heading">
                                    Métadonnées
                                </x-slot>
                                <div class="fi-section-content-ctn space-y-4">
                                    <div>
                                        <label class="fi-input-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-2">
                                            Texte alternatif (Alt)
                                        </label>
                                        <input
                                            type="text"
                                            wire:model="detailAltText"
                                            placeholder="Décrivez l'image pour l'accessibilité"
                                            class="fi-input w-full rounded-lg border-none bg-white shadow-sm ring-1 ring-inset transition duration-75 focus:ring-2 dark:bg-white/5 dark:text-white dark:ring-white/10 dark:placeholder:text-gray-500 sm:text-sm sm:leading-6"
                                        />
                                        <p class="fi-hint mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            Améliore l'accessibilité et le SEO
                                        </p>
                                    </div>

                                    <div>
                                        <label class="fi-input-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-2">
                                            Description
                                        </label>
                                        <textarea
                                            wire:model="detailDescription"
                                            rows="4"
                                            placeholder="Description optionnelle du média"
                                            class="fi-input w-full rounded-lg border-none bg-white shadow-sm ring-1 ring-inset transition duration-75 focus:ring-2 dark:bg-white/5 dark:text-white dark:ring-white/10 dark:placeholder:text-gray-500 sm:text-sm sm:leading-6 resize-none"
                                        ></textarea>
                                    </div>
                                </div>
                            </x-filament::section>

                            {{-- Informations détaillées --}}
                            <x-filament::section>
                                <x-slot name="heading">
                                    Informations détaillées
                                </x-slot>
                                <div class="fi-section-content-ctn">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Taille</p>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $detailMedia->getFormattedSize() }}</p>
                                        </div>
                                        @if($detailMedia->isImage() && $detailMedia->width && $detailMedia->height)
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Dimensions</p>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $detailMedia->width }} × {{ $detailMedia->height }} px</p>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Type MIME</p>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white break-all">{{ $detailMedia->mime_type }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Date de création</p>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $detailMedia->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                        @if($detailMedia->folder)
                                            <div class="col-span-2">
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Dossier</p>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                    <x-heroicon-o-folder class="h-4 w-4" />
                                                    {{ $detailMedia->folder->name }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                    @if(is_array($detailMedia->metadata ?? null) && isset($detailMedia->metadata['orientation']))
                                        <div class="mt-4 rounded-lg bg-gray-50 dark:bg-gray-900/40 border border-dashed border-gray-200 dark:border-gray-700 px-3 py-2">
                                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">
                                                Infos techniques
                                            </p>
                                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                                Orientation EXIF d’origine :
                                                <span class="font-semibold">
                                                    {{ $detailMedia->metadata['orientation'] }}
                                                </span>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </x-filament::section>

                            {{-- Actions --}}
                            <x-filament::section>
                                <x-slot name="heading">
                                    Actions
                                </x-slot>
                                <div class="fi-section-content-ctn space-y-4">
                                    <div class="space-y-2">
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                            Gestion
                                        </p>
                                        @if(config('media-library-pro.actions.rename', true))
                                            <x-filament::button
                                                wire:click="openRenameModal"
                                                color="warning"
                                                outlined
                                                size="sm"
                                                class="w-full justify-start"
                                            >
                                                <x-slot name="icon">
                                                    <x-heroicon-o-pencil class="h-4 w-4" />
                                                </x-slot>
                                                Renommer
                                            </x-filament::button>
                                        @endif
                                        @if(config('media-library-pro.actions.download', true))
                                            <x-filament::button
                                                tag="a"
                                                href="{{ route('media-library-pro.download', ['media' => $detailMedia->uuid]) }}"
                                                target="_blank"
                                                color="success"
                                                outlined
                                                size="sm"
                                                class="w-full justify-start"
                                            >
                                                <x-slot name="icon">
                                                    <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                                                </x-slot>
                                                Télécharger
                                            </x-filament::button>
                                        @endif
                                        @if(config('media-library-pro.actions.move', true) && config('media-library-pro.folders.enabled', true))
                                            <x-filament::button
                                                wire:click="openMoveModal"
                                                color="info"
                                                outlined
                                                size="sm"
                                                class="w-full justify-start"
                                            >
                                                <x-slot name="icon">
                                                    <x-heroicon-o-arrow-right-circle class="h-4 w-4" />
                                                </x-slot>
                                                Déplacer
                                            </x-filament::button>
                                        @endif
                                        @if(config('media-library-pro.actions.delete', true))
                                            <x-filament::button
                                                wire:click="deleteMedia('{{ $detailMedia->uuid }}')"
                                                wire:confirm="Êtes-vous sûr de vouloir supprimer ce fichier ?"
                                                color="danger"
                                                outlined
                                                size="sm"
                                                class="w-full justify-start"
                                            >
                                                <x-slot name="icon">
                                                    <x-heroicon-o-trash class="h-4 w-4" />
                                                </x-slot>
                                                Supprimer
                                            </x-filament::button>
                                        @endif
                                    </div>

                                    @if($detailMedia && $detailMedia->isImage())
                                        @php
                                            $optimizationEnabled = config('media-library-pro.optimization.enabled', false);
                                        @endphp
                                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                                Image
                                            </p>
                                            <div class="grid grid-cols-2 gap-2">
                                                <x-filament::button
                                                    wire:click="rotateLeft('{{ $detailMedia->uuid }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="rotateLeft"
                                                    color="gray"
                                                    size="sm"
                                                    class="w-full justify-center"
                                                >
                                                    <x-slot name="icon">
                                                        <x-heroicon-o-arrow-uturn-left class="h-4 w-4" />
                                                    </x-slot>
                                                    Pivoter à gauche
                                                </x-filament::button>
                                                <x-filament::button
                                                    wire:click="rotateRight('{{ $detailMedia->uuid }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="rotateRight"
                                                    color="gray"
                                                    size="sm"
                                                    class="w-full justify-center"
                                                >
                                                    <x-slot name="icon">
                                                        <x-heroicon-o-arrow-uturn-right class="h-4 w-4" />
                                                    </x-slot>
                                                    Pivoter à droite
                                                </x-filament::button>
                                            </div>
                                            @if($optimizationEnabled)
                                                <div class="pt-2 border-t border-dashed border-gray-200 dark:border-gray-700">
                                                    <x-filament::button
                                                        wire:click="optimizeImage('{{ $detailMedia->uuid }}')"
                                                        wire:loading.attr="disabled"
                                                        wire:target="optimizeImage"
                                                        color="primary"
                                                        size="sm"
                                                        class="w-full"
                                                    >
                                                        <x-slot name="icon">
                                                            <x-heroicon-o-arrow-path class="h-4 w-4" wire:loading.remove wire:target="optimizeImage" />
                                                            <svg class="animate-spin h-4 w-4" wire:loading wire:target="optimizeImage" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                        </x-slot>
                                                        <span wire:loading.remove wire:target="optimizeImage">Optimiser l'image</span>
                                                        <span wire:loading wire:target="optimizeImage">Optimisation en cours...</span>
                                                    </x-filament::button>
                                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 text-center">
                                                        Réduit la taille et optimise la qualité
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </x-filament::section>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="fi-modal-footer border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <kbd class="fi-kbd rounded-md border border-gray-300 bg-white px-1.5 py-0.5 text-xs font-semibold text-gray-600 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">Ctrl+Entrée</kbd> pour enregistrer • <kbd class="fi-kbd rounded-md border border-gray-300 bg-white px-1.5 py-0.5 text-xs font-semibold text-gray-600 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">Échap</kbd> pour fermer
                        </p>
                        <div class="flex items-center gap-3">
                            <x-filament::button
                                wire:click="closeDetailModal"
                                color="gray"
                                outlined
                                size="sm"
                            >
                                Annuler
                            </x-filament::button>
                            <x-filament::button
                                wire:click="updateMediaDetails"
                                color="primary"
                                size="sm"
                            >
                                <x-slot name="icon">
                                    <x-heroicon-o-check class="h-4 w-4" />
                                </x-slot>
                                Enregistrer
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

