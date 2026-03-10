{{-- Detail Modal --}}
@once
    <style>
        /**
         * La CSS Filament embarquée ne garantit pas la présence de tous les utilitaires Tailwind
         * (ex: col-span-*). On force donc la mise en page 2 colonnes via CSS "scopée" au composant.
         */
        .mlp-detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1.5rem;
            align-items: start;
        }

        .mlp-detail-dialog-shell {
            width: 100%;
            max-width: min(95vw, 96rem);
        }

        @media (min-width: 768px) {
            .mlp-detail-grid {
                grid-template-columns: minmax(0, 3fr) minmax(0, 1fr);
            }
        }

        .mlp-detail-fullrow {
            grid-column: 1 / -1;
        }

        .mlp-detail-preview {
            min-width: 0;
        }

        .mlp-detail-aside {
            min-width: 0;
        }

        .mlp-detail-media-img {
            display: block;
            width: 100%;
            height: auto;
            max-width: 48rem;
            max-height: 70vh;
            object-fit: contain;
        }
    </style>
@endonce

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
        },
    }"
    x-cloak
    x-show="show"
    x-transition
    @keydown.window="handleKeydown"
    x-init="$watch('show', value => { document.body.style.overflow = value ? 'hidden' : ''; })"
    class="fixed inset-0 z-50 flex items-center justify-center"
    role="dialog"
    aria-modal="true"
    aria-labelledby="media-detail-title"
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
        class="fixed inset-0 z-40 bg-black/70 backdrop-blur-md transition-opacity"
        @click="$wire.closeDetailModal()"
    ></div>

    {{-- Dialog container --}}
    <div class="mlp-detail-dialog-shell relative z-50 flex px-4 py-8 pointer-events-none">
        @if ($detailMedia)
            <div
                x-show="show"
                x-transition:enter="ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-[0.97] translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-[0.97] translate-y-1"
                class="pointer-events-auto flex w-full max-h-[90vh] flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-2xl transition-all dark:border-gray-700 dark:bg-gray-800"
                @click.stop
                tabindex="-1"
            >
                {{-- Header --}}
                <div class="fi-modal-header flex items-center justify-between gap-4 px-6 py-4">
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <div class="fi-icon-btn flex h-10 w-10 items-center justify-center rounded-lg bg-primary-500 dark:bg-primary-600">
                            @if ($detailMedia->isImage())
                                <x-heroicon-o-photo class="h-5 w-5 text-white" />
                            @elseif ($detailMedia->isVideo())
                                <x-heroicon-o-video-camera class="h-5 w-5 text-white" />
                            @elseif ($detailMedia->isAudio())
                                <x-heroicon-o-musical-note class="h-5 w-5 text-white" />
                            @else
                                <x-heroicon-o-document class="h-5 w-5 text-white" />
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3
                                id="media-detail-title"
                                class="fi-modal-heading truncate text-lg font-semibold leading-6 text-gray-950 dark:text-white"
                            >
                                {{ $detailMedia->file_name }}
                            </h3>

                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                                <span class="fi-badge inline-flex items-center gap-x-1 rounded-md bg-primary-50 px-2 py-1 font-medium text-primary-700 ring-1 ring-inset ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/20">
                                    {{ $detailMedia->getFormattedSize() }}
                                </span>

                                @if ($detailMedia->isImage() && $detailMedia->width && $detailMedia->height)
                                    <span class="text-gray-500 dark:text-gray-400">
                                        {{ $detailMedia->width }} × {{ $detailMedia->height }} px
                                    </span>
                                @endif

                                <span class="break-all text-gray-500 dark:text-gray-400">
                                    {{ $detailMedia->mime_type }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-filament::icon-button
                            icon="heroicon-o-chevron-left"
                            color="gray"
                            size="sm"
                            wire:click="openPreviousDetail"
                            :disabled="$detailIndex === null || $detailIndex <= 0"
                            class="hidden sm:inline-flex"
                            title="Précédent"
                            aria-label="Média précédent"
                        />

                        <x-filament::icon-button
                            icon="heroicon-o-chevron-right"
                            color="gray"
                            size="sm"
                            wire:click="openNextDetail"
                            :disabled="$detailIndex === null || $detailIndex >= (count($detailMediaIdsOnPage) - 1)"
                            class="hidden sm:inline-flex"
                            title="Suivant"
                            aria-label="Média suivant"
                        />

                        <x-filament::icon-button
                            icon="heroicon-o-x-mark"
                            color="gray"
                            size="sm"
                            wire:click="closeDetailModal"
                            aria-label="Fermer"
                        />
                    </div>
                </div>

                {{-- Main content : preview + panneau latéral --}}
                <div class="px-6 pb-4">
                    <div class="mlp-detail-grid">
                        {{-- Colonne gauche : preview (≈ 3/4) --}}
                        <section class="mlp-detail-preview">
                            <div class="flex items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50">
                                @if ($detailMedia->isImage())
                                    <div class="flex h-full w-full items-center justify-center bg-gray-100 p-4 dark:bg-gray-800">
                                        <img
                                            src="{{ $this->getMediaImageUrl($detailMedia) }}"
                                            alt="{{ $detailMedia->alt_text ?: $detailMedia->file_name }}"
                                            class="mlp-detail-media-img rounded-lg"
                                        />
                                    </div>
                                @else
                                    <div class="flex min-h-[320px] w-full flex-col items-center justify-center bg-gray-100 dark:bg-gray-800">
                                        @if ($detailMedia->isVideo())
                                            <x-heroicon-o-video-camera class="mb-4 h-24 w-24 text-gray-400 dark:text-gray-500" />
                                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                                Fichier vidéo
                                            </p>
                                        @elseif ($detailMedia->isAudio())
                                            <x-heroicon-o-musical-note class="mb-4 h-24 w-24 text-gray-400 dark:text-gray-500" />
                                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                                Fichier audio
                                            </p>
                                        @else
                                            <x-heroicon-o-document class="mb-4 h-24 w-24 text-gray-400 dark:text-gray-500" />
                                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                                Document
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </section>

                        {{-- Colonne droite : panneau latéral scrollable (≈ 1/4) --}}
                        <aside class="mlp-detail-aside mt-4 flex max-h-[70vh] flex-col gap-4 overflow-y-auto pr-1 md:mt-0">
                            {{-- Métadonnées --}}
                            <x-filament::section>
                                <x-slot name="heading">
                                    Métadonnées
                                </x-slot>

                                <div class="fi-section-content-ctn space-y-4">
                                    <div>
                                        <label class="fi-input-label mb-2 block text-sm font-medium leading-6 text-gray-950 dark:text-white">
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
                                        <label class="fi-input-label mb-2 block text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                            Description
                                        </label>

                                        <textarea
                                            wire:model="detailDescription"
                                            rows="4"
                                            placeholder="Description optionnelle du média"
                                            class="fi-input w-full resize-none rounded-lg border-none bg-white shadow-sm ring-1 ring-inset transition duration-75 focus:ring-2 dark:bg-white/5 dark:text-white dark:ring-white/10 dark:placeholder:text-gray-500 sm:text-sm sm:leading-6"
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
                                            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Taille
                                            </p>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $detailMedia->getFormattedSize() }}
                                            </p>
                                        </div>

                                        @if ($detailMedia->isImage() && $detailMedia->width && $detailMedia->height)
                                            <div>
                                                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                    Dimensions
                                                </p>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ $detailMedia->width }} × {{ $detailMedia->height }} px
                                                </p>
                                            </div>
                                        @endif

                                        <div>
                                            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Type MIME
                                            </p>
                                            <p class="break-all text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $detailMedia->mime_type }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Date de création
                                            </p>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $detailMedia->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>

                                        @if ($detailMedia->folder)
                                            <div class="mlp-detail-fullrow">
                                                <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                    Dossier
                                                </p>
                                                <p class="flex items-center gap-1.5 text-sm font-semibold text-gray-900 dark:text-white">
                                                    <x-heroicon-o-folder class="h-4 w-4" />
                                                    {{ $detailMedia->folder->name }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    @if (is_array($detailMedia->metadata ?? null) && isset($detailMedia->metadata['orientation']))
                                        <div class="mt-4 rounded-lg border border-dashed border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-900/40">
                                            <p class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">
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

                            {{-- Actions (gestion + image) --}}
                            <x-filament::section>
                                <x-slot name="heading">
                                    Actions
                                </x-slot>

                                <div class="fi-section-content-ctn space-y-4">
                                    <div class="space-y-2">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                            Gestion
                                        </p>

                                        <div class="grid grid-cols-2 gap-2">
                                            @if (config('media-library-pro.actions.rename', true))
                                                <x-filament::button
                                                    wire:click="openRenameModal"
                                                    color="warning"
                                                    size="sm"
                                                    outlined
                                                    class="w-full justify-center"
                                                >
                                                    <x-slot name="icon">
                                                        <x-heroicon-o-pencil class="h-4 w-4" />
                                                    </x-slot>
                                                    Renommer
                                                </x-filament::button>
                                            @endif

                                            @if (config('media-library-pro.actions.move', true) && config('media-library-pro.folders.enabled', true))
                                                <x-filament::button
                                                    wire:click="openMoveModal"
                                                    color="info"
                                                    size="sm"
                                                    outlined
                                                    class="w-full justify-center"
                                                >
                                                    <x-slot name="icon">
                                                        <x-heroicon-o-arrow-right-circle class="h-4 w-4" />
                                                    </x-slot>
                                                    Déplacer
                                                </x-filament::button>
                                            @endif
                                        </div>

                                        @if (config('media-library-pro.actions.download', true))
                                            <x-filament::button
                                                tag="a"
                                                href="{{ route('media-library-pro.download', ['media' => $detailMedia->uuid]) }}"
                                                target="_blank"
                                                color="success"
                                                size="sm"
                                                outlined
                                                class="w-full justify-center"
                                            >
                                                <x-slot name="icon">
                                                    <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                                                </x-slot>
                                                Télécharger
                                            </x-filament::button>
                                        @endif

                                        @if (config('media-library-pro.actions.delete', true))
                                            <x-filament::button
                                                wire:click="deleteMedia('{{ $detailMedia->uuid }}')"
                                                wire:confirm="Êtes-vous sûr de vouloir supprimer ce fichier ?"
                                                color="danger"
                                                size="sm"
                                                outlined
                                                class="w-full justify-center"
                                            >
                                                <x-slot name="icon">
                                                    <x-heroicon-o-trash class="h-4 w-4" />
                                                </x-slot>
                                                Supprimer
                                            </x-filament::button>
                                        @endif
                                    </div>

                                    @if ($detailMedia->isImage())
                                        @php
                                            $optimizationEnabled = config('media-library-pro.optimization.enabled', false);
                                        @endphp

                                        <div class="space-y-2 border-t border-gray-200 pt-4 dark:border-gray-700">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
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

                                            @if ($optimizationEnabled)
                                                <div class="border-t border-dashed border-gray-200 pt-2 dark:border-gray-700">
                                                    <x-filament::button
                                                        wire:click="optimizeImage('{{ $detailMedia->uuid }}')"
                                                        wire:loading.attr="disabled"
                                                        wire:target="optimizeImage"
                                                        color="primary"
                                                        size="sm"
                                                        class="w-full"
                                                    >
                                                        <x-slot name="icon">
                                                            <x-heroicon-o-arrow-path
                                                                class="h-4 w-4"
                                                                wire:loading.remove
                                                                wire:target="optimizeImage"
                                                            />
                                                            <svg
                                                                class="h-4 w-4 animate-spin"
                                                                wire:loading
                                                                wire:target="optimizeImage"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                fill="none"
                                                                viewBox="0 0 24 24"
                                                            >
                                                                <circle
                                                                    class="opacity-25"
                                                                    cx="12"
                                                                    cy="12"
                                                                    r="10"
                                                                    stroke="currentColor"
                                                                    stroke-width="4"
                                                                ></circle>
                                                                <path
                                                                    class="opacity-75"
                                                                    fill="currentColor"
                                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                                ></path>
                                                            </svg>
                                                        </x-slot>
                                                        <span wire:loading.remove wire:target="optimizeImage">
                                                            Optimiser l'image
                                                        </span>
                                                        <span wire:loading wire:target="optimizeImage">
                                                            Optimisation en cours...
                                                        </span>
                                                    </x-filament::button>

                                                    <p class="mt-2 text-center text-xs text-gray-500 dark:text-gray-400">
                                                        Réduit la taille et optimise la qualité
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </x-filament::section>
                        </aside>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="fi-modal-footer border-t border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <kbd class="fi-kbd rounded-md border border-gray-300 bg-white px-1.5 py-0.5 text-xs font-semibold text-gray-600 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                Ctrl+Entrée
                            </kbd>
                            pour enregistrer •
                            <kbd class="fi-kbd rounded-md border border-gray-300 bg-white px-1.5 py-0.5 text-xs font-semibold text-gray-600 shadow-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                Échap
                            </kbd>
                            pour fermer
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
            </div>
        @endif
    </div>
</div>
