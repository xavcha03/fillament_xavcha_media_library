<div class="space-y-6">
    {{-- Debug: Afficher le nombre de médias --}}
    @if(config('app.debug') && isset($media))
        <div class="bg-yellow-100 dark:bg-yellow-900/20 p-2 rounded text-xs">
            Debug: {{ $media->total() }} média(s) trouvé(s) | Page {{ $media->currentPage() }}/{{ $media->lastPage() }}
        </div>
    @endif
    
    {{-- Toolbar --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <x-filament::button
                wire:click="toggleView"
                size="sm"
                color="gray"
                :outlined="$view !== 'grid'"
            >
                <x-slot name="icon">
                    <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                </x-slot>
                Grille
            </x-filament::button>
            <x-filament::button
                wire:click="toggleView"
                size="sm"
                color="gray"
                :outlined="$view !== 'list'"
            >
                <x-slot name="icon">
                    <x-heroicon-o-list-bullet class="w-4 h-4" />
                </x-slot>
                Liste
            </x-filament::button>
        </div>

        <div class="flex items-center gap-2">
            @if(!$pickerMode)
                <x-filament::button
                    wire:click="openUploadModal"
                    size="sm"
                    color="primary"
                >
                    <x-slot name="icon">
                        <x-heroicon-o-plus class="w-4 h-4" />
                    </x-slot>
                    Ajouter des fichiers
                </x-filament::button>
            @endif
            
            @if($selectMode && !empty($selectedItems))
                <div x-data="{ open: false }" class="relative">
                    <x-filament::button
                        x-on:click="open = !open"
                        size="sm"
                        color="primary"
                        outlined
                    >
                        Actions ({{ count($selectedItems) }})
                    </x-filament::button>
                    <div
                        x-show="open"
                        x-cloak
                        x-on:click.away="open = false"
                        class="absolute right-0 mt-2 w-48 fi-dropdown-panel rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 z-10"
                        style="display: none;"
                    >
                        <div class="py-1">
                            <button
                                wire:click="bulkDelete"
                                wire:confirm="Êtes-vous sûr de vouloir supprimer les médias sélectionnés ?"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>

                <x-filament::button
                    wire:click="selectAll"
                    size="sm"
                    color="gray"
                    outlined
                >
                    Tout sélectionner
                </x-filament::button>
                <x-filament::button
                    wire:click="deselectAll"
                    size="sm"
                    color="gray"
                    outlined
                >
                    Tout désélectionner
                </x-filament::button>
            @endif
            <x-filament::button
                wire:click="toggleSelectMode"
                size="sm"
                :color="$selectMode ? 'danger' : 'gray'"
                outlined
            >
                {{ $selectMode ? 'Annuler' : 'Sélectionner' }}
            </x-filament::button>
        </div>
    </div>

    {{-- Filters --}}
    <x-filament::section>
        <x-slot name="heading">
            Filtres
        </x-slot>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="fi-input-label block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Collection
                </label>
                <select 
                    wire:model.live="filters.collection" 
                    class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500"
                >
                    <option value="">Toutes les collections</option>
                    @foreach(\Xavier\MediaLibraryPro\Models\MediaAttachment::distinct()->pluck('collection_name')->filter() as $collection)
                        <option value="{{ $collection }}">{{ $collection }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="fi-input-label block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Type
                </label>
                <select 
                    wire:model.live="filters.type" 
                    class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500"
                >
                    <option value="">Tous les types</option>
                    <option value="image">Images</option>
                    <option value="video">Vidéos</option>
                    <option value="audio">Audio</option>
                    <option value="document">Documents</option>
                    <option value="archive">Archives</option>
                </select>
            </div>

            <div>
                <label class="fi-input-label block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Modèle associé
                </label>
                <select 
                    wire:model.live="filters.model_type" 
                    class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500"
                >
                    <option value="">Tous les modèles</option>
                    @foreach(\Xavier\MediaLibraryPro\Models\MediaAttachment::distinct()->pluck('model_type')->filter() as $modelType)
                        <option value="{{ $modelType }}">{{ class_basename($modelType) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="fi-input-label block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Trier par
                </label>
                <select 
                    wire:model.live="sortBy" 
                    wire:change="sortBy($event.target.value)" 
                    class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500"
                >
                    <option value="created_at">Date de création</option>
                    <option value="name">Nom</option>
                    <option value="size">Taille</option>
                    <option value="mime_type">Type</option>
                </select>
            </div>
        </div>
    </x-filament::section>

    {{-- Media Content --}}
    @if($view === 'grid')
        {{-- Grid View --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @forelse($media as $item)
                <div 
                    class="relative rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden border border-gray-200 dark:border-gray-700 @if($pickerMode) cursor-pointer hover:ring-2 hover:ring-primary-500 transition-all @endif"
                    @if($pickerMode)
                        data-media-id="{{ $item->uuid }}"
                        onclick="window.dispatchEvent(new CustomEvent('media-library-picker-select', { detail: { mediaId: '{{ $item->uuid }}' } }))"
                    @endif
                >
                    @if($selectMode)
                        <div class="absolute top-2 left-2 z-10">
                            <input
                                type="checkbox"
                                wire:click="toggleSelection('{{ $item->uuid }}')"
                                @checked(in_array($item->uuid, $selectedItems))
                                class="fi-checkbox-input rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            />
                        </div>
                    @endif
                    @if($pickerMode && !$selectMode)
                        <div class="absolute top-2 left-2 z-10">
                            <div class="w-6 h-6 bg-primary-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                ✓
                            </div>
                        </div>
                    @endif

                    <div class="aspect-square bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden @if($pickerMode) cursor-pointer hover:opacity-90 transition-opacity @endif">
                        @if(str_starts_with($item->mime_type, 'image/'))
                            @php
                                // Utiliser la route publique pour servir les images (accessible depuis le frontend)
                                try {
                                    $imageUrl = route('media-library-pro.serve', ['media' => $item->uuid]);
                                } catch (\Exception $e) {
                                    // Fallback si la route n'existe pas
                                    $imageUrl = url('/media-library-pro/serve/' . $item->uuid);
                                }
                            @endphp
                            @if(config('app.debug'))
                                <!-- Debug: ID={{ $item->id }} | UUID={{ $item->uuid }} | Disk={{ $item->disk }} | Path={{ $item->path }} | URL={{ $imageUrl }} -->
                            @endif
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

                    <div class="p-3 border-t border-gray-200 dark:border-white/10">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" title="{{ $item->file_name }}">
                            {{ Str::limit($item->file_name, 30) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $item->getFormattedSize() }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">Aucun média trouvé</p>
                </div>
            @endforelse
        </div>
    @else
        {{-- List View --}}
        <x-filament::section>
            <div class="overflow-x-auto -mx-4 sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full align-middle">
                    <div class="overflow-hidden">
                        <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                            <thead class="divide-y divide-gray-200 dark:divide-white/5">
                                <tr class="bg-gray-50 dark:bg-white/5">
                                    @if($selectMode)
                                        <th scope="col" class="fi-ta-header-cell px-3 py-3.5 sm:px-6">
                                            <input
                                                type="checkbox"
                                                wire:click="selectAll"
                                                class="fi-checkbox-input rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                            />
                                        </th>
                                    @endif
                                    <th scope="col" class="fi-ta-header-cell px-3 py-3.5 sm:px-6">
                                        <span class="group flex w-full items-center gap-x-1.5">
                                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white sm:text-sm">Preview</span>
                                        </span>
                                    </th>
                                    <th scope="col" class="fi-ta-header-cell px-3 py-3.5 sm:px-6">
                                        <button wire:click="sortBy('name')" class="group flex w-full items-center gap-x-1.5">
                                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white sm:text-sm">Nom</span>
                                        </button>
                                    </th>
                                    <th scope="col" class="fi-ta-header-cell px-3 py-3.5 sm:px-6">
                                        <button wire:click="sortBy('mime_type')" class="group flex w-full items-center gap-x-1.5">
                                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white sm:text-sm">Type</span>
                                        </button>
                                    </th>
                                    <th scope="col" class="fi-ta-header-cell px-3 py-3.5 sm:px-6">
                                        <button wire:click="sortBy('collection_name')" class="group flex w-full items-center gap-x-1.5">
                                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white sm:text-sm">Collection</span>
                                        </button>
                                    </th>
                                    <th scope="col" class="fi-ta-header-cell px-3 py-3.5 sm:px-6">
                                        <button wire:click="sortBy('size')" class="group flex w-full items-center gap-x-1.5">
                                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white sm:text-sm">Taille</span>
                                        </button>
                                    </th>
                                    <th scope="col" class="fi-ta-header-cell px-3 py-3.5 sm:px-6">
                                        <button wire:click="sortBy('created_at')" class="group flex w-full items-center gap-x-1.5">
                                            <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white sm:text-sm">Date</span>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                                @forelse($media as $item)
                                    <tr class="fi-ta-row transition duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                                        @if($selectMode)
                                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                                <div class="px-3 py-4 sm:px-6">
                                                    <input
                                                        type="checkbox"
                                                        wire:click="toggleSelection('{{ $item->uuid }}')"
                                                        @checked(in_array($item->uuid, $selectedItems))
                                                        class="fi-checkbox-input rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                                    />
                                                </div>
                                            </td>
                                        @endif
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-4 sm:px-6">
                                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded flex items-center justify-center overflow-hidden">
                                                    @if(str_starts_with($item->mime_type, 'image/'))
                                                        @php
                                                            // Utiliser la route publique pour servir les images (accessible depuis le frontend)
                                                            try {
                                                                $imageUrl = route('media-library-pro.serve', ['media' => $item->uuid]);
                                                            } catch (\Exception $e) {
                                                                // Fallback si la route n'existe pas
                                                                $imageUrl = url('/media-library-pro/serve/' . $item->uuid);
                                                            }
                                                        @endphp
                                                        <img src="{{ $imageUrl }}" alt="{{ $item->file_name }}" class="w-full h-full object-cover" loading="lazy" />
                                                    @else
                                                        <x-heroicon-o-document class="w-8 h-8 text-gray-400" />
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-4 sm:px-6">
                                                <div class="text-sm font-medium text-gray-950 dark:text-white max-w-md truncate" title="{{ $item->file_name }}">
                                                    {{ Str::limit($item->file_name, 50) }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-4 sm:px-6">
                                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($item->mime_type, 20) }}</span>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-4 sm:px-6">
                                                <span class="inline-flex items-center gap-x-1 rounded-md bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-700/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/20">
                                                    {{ $item->attachments->first()?->collection_name ?? 'default' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-4 sm:px-6">
                                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $item->getFormattedSize() }}</span>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-4 sm:px-6">
                                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $selectMode ? '7' : '6' }}" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="px-3 py-12 sm:px-6 text-center">
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Aucun média trouvé</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-filament::section>
    @endif

    {{-- Pagination --}}
    @if($media->hasPages())
        <div class="mt-4">
            {{ $media->links() }}
        </div>
    @endif

    {{-- Upload Modal --}}
    <div
        x-data="{ open: @entangle('showUploadModal') }"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
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
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            @click="open = false"
        ></div>

        {{-- Modal --}}
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
                @click.stop
            >
                {{-- Header avec titre et bouton de fermeture --}}
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">
                        Ajouter des fichiers
                    </h3>
                    <button
                        type="button"
                        wire:click="closeUploadModal"
                        class="inline-flex items-center justify-center rounded-md p-1.5 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors"
                        aria-label="Fermer"
                    >
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="w-full">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Collection (optionnel)
                            </label>
                            <input
                                type="text"
                                wire:model="uploadCollection"
                                placeholder="default"
                                class="fi-input block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-3 py-2"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fichiers
                            </label>
                            <input
                                type="file"
                                wire:model="uploadedFiles"
                                multiple
                                accept="image/*"
                                class="fi-input block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900 dark:file:text-primary-200"
                            />
                            @error('uploadedFiles.*')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(!empty($uploadedFiles))
                            <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <strong>{{ count($uploadedFiles) }}</strong> fichier(s) sélectionné(s)
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-2">
                    <x-filament::button
                        wire:click="uploadFiles"
                        color="primary"
                        :disabled="empty($uploadedFiles)"
                    >
                        Uploader
                    </x-filament::button>
                    <x-filament::button
                        wire:click="closeUploadModal"
                        color="gray"
                        outlined
                    >
                        Annuler
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>
</div>
