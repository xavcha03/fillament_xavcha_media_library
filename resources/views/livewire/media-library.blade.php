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
            x-data="{ 
                open: @entangle('showUploadModal'),
                isDragging: false,
                handleDragOver(e) {
                    e.preventDefault();
                    this.isDragging = true;
                },
                handleDragLeave() {
                    this.isDragging = false;
                },
                handleDrop(e) {
                    e.preventDefault();
                    this.isDragging = false;
                    const files = Array.from(e.dataTransfer.files);
                    const input = document.getElementById('file-upload');
                    if (input) {
                        const dataTransfer = new DataTransfer();
                        files.forEach(file => dataTransfer.items.add(file));
                        input.files = dataTransfer.files;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            }"
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            x-transition
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
                class="fixed inset-0 bg-black/70 backdrop-blur-md transition-opacity z-40"
                @click="open = false"
            ></div>

            {{-- Modal --}}
            <div 
                class="fixed inset-0 z-50 flex items-center justify-center p-6 pointer-events-none"
                x-data="{
                    handleKeydown(event) {
                        if (event.key === 'Escape' && $wire.showUploadModal) {
                            $wire.closeUploadModal();
                        }
                        if (event.key === 'Enter' && event.ctrlKey && $wire.showUploadModal && $wire.uploadedFiles.length > 0) {
                            event.preventDefault();
                            $wire.uploadFiles();
                        }
                    }
                }"
                @keydown="handleKeydown"
                x-init="
                    $watch('$wire.showUploadModal', value => {
                        if (value) {
                            document.body.style.overflow = 'hidden';
                            $nextTick(() => {
                                const firstInput = $el.querySelector('input[type=file], input[type=text]');
                                if (firstInput) firstInput.focus();
                            });
                        } else {
                            document.body.style.overflow = '';
                        }
                    });
                "
            >
                <div
                    x-show="open"
                    x-transition:enter="ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-[0.97]"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-[0.97]"
                    class="relative max-w-lg mx-auto w-full transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-700 transition-all pointer-events-auto focus:outline-none"
                    @click.stop
                    tabindex="-1"
                >
                    {{-- Header --}}
                    <div class="border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-900 px-6 py-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-900/30">
                                        <x-heroicon-o-cloud-arrow-up class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                        Téléverser des fichiers
                                    </h3>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 ml-13">
                                    Choisissez des fichiers à importer dans votre bibliothèque de médias
                                </p>
                            </div>
                            <button
                                type="button"
                                wire:click="closeUploadModal"
                                class="rounded-lg p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all"
                                aria-label="Fermer (Échap)"
                                title="Fermer (Échap)"
                            >
                                <x-heroicon-o-x-mark class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="px-6 py-6 space-y-6 bg-white dark:bg-gray-800">
                        {{-- Zone de drag & drop --}}
                        <label
                            for="file-upload"
                            @dragover.prevent="handleDragOver($event)"
                            @dragleave.prevent="handleDragLeave($event)"
                            @drop.prevent="handleDrop($event)"
                            :class="isDragging ? 'drag-zone-active border-primary-500 bg-primary-50 dark:bg-primary-900/30 ring-4 ring-primary-500/20 scale-[1.02]' : 'border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-800/30'"
                            class="relative block border-2 border-dashed rounded-2xl p-12 text-center transition-all duration-300 cursor-pointer hover:border-primary-400 hover:bg-gradient-to-br hover:from-primary-50 hover:to-white dark:hover:from-primary-900/10 dark:hover:to-gray-800 hover:shadow-lg group min-h-[280px] flex items-center justify-center"
                        >
                            <input
                                id="file-upload"
                                type="file"
                                wire:model="uploadedFiles"
                                multiple
                                class="sr-only"
                                tabindex="-1"
                            />
                            <div class="flex flex-col items-center justify-center space-y-5 w-full">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-primary-200 dark:bg-primary-800 rounded-full blur-2xl opacity-30 group-hover:opacity-50 transition-opacity"></div>
                                    <x-heroicon-o-cloud-arrow-up class="h-16 w-16 text-primary-500 dark:text-primary-400 relative z-10 transition-transform group-hover:scale-110" />
                                </div>
                                <div class="space-y-2">
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        ⇧ Déposez vos fichiers ici
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        ou
                                    </p>
                                    <p class="text-base font-semibold text-primary-600 dark:text-primary-400 underline decoration-2 underline-offset-4 cursor-pointer hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                                        Cliquez pour sélectionner
                                    </p>
                                </div>
                                <div class="flex items-center gap-3 px-4 py-2 rounded-full bg-white dark:bg-gray-700/70 border border-gray-200 dark:border-gray-600 shadow-sm">
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">📎 Formats : PNG, JPG, GIF, WEBP</span>
                                    <span class="text-gray-400 dark:text-gray-500">•</span>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Max 10MB</span>
                                </div>
                            </div>
                        </label>

                        {{-- Feedback des erreurs --}}
                        @php
                            $validationErrors = $this->getFileValidationErrors();
                        @endphp
                        @if(!empty($validationErrors))
                            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 space-y-1">
                                @foreach($validationErrors as $index => $error)
                                    <p class="text-sm text-red-600 dark:text-red-400">
                                        @if(isset($uploadedFiles[$index]) && method_exists($uploadedFiles[$index], 'getClientOriginalName'))
                                            {{ $uploadedFiles[$index]->getClientOriginalName() }} — {{ $error }}
                                        @else
                                            {{ $error }}
                                        @endif
                                    </p>
                                @endforeach
                            </div>
                        @endif

                        @error('uploadedFiles.*')
                            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3">
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            </div>
                        @enderror

                        {{-- Divider --}}
                        <div class="border-t border-gray-200 dark:border-gray-700"></div>

                        {{-- Collection --}}
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                <x-heroicon-o-folder class="h-4 w-4 text-primary-500 dark:text-primary-400" />
                                <span>Collection cible</span>
                            </label>
                            <input
                                type="text"
                                wire:model="uploadCollection"
                                placeholder="default"
                                class="fi-input block w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm px-4 py-2.5 font-medium"
                            />
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 italic">
                                💡 Vous pouvez changer la collection après import.
                            </p>
                        </div>

                        {{-- Preview des fichiers sélectionnés --}}
                        @if(!empty($uploadedFiles))
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-1">
                                            <span>📎</span>
                                            <span>Fichiers sélectionnés</span>
                                        </h4>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                            <span class="font-bold text-primary-600 dark:text-primary-400">{{ count($uploadedFiles) }}</span> fichier(s) — 
                                            <span class="font-bold">{{ $this->getTotalFileSize() }}</span> MB
                                            @if(empty($validationErrors))
                                                <span class="ml-2 px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-semibold">✓ OK</span>
                                            @endif
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="clearUploadedFiles"
                                        class="text-xs font-semibold text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 px-3 py-1.5 rounded-lg transition-all"
                                    >
                                        Tout effacer
                                    </button>
                                </div>
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 max-h-56 overflow-y-auto rounded-lg border-2 border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
                                    @foreach($uploadedFiles as $index => $file)
                                        @php
                                            $hasError = isset($validationErrors[$index]);
                                        @endphp
                                        <div class="relative group file-preview-item">
                                            <div class="aspect-square rounded-lg overflow-hidden bg-white dark:bg-gray-800 shadow-sm {{ $hasError ? 'border-4 border-red-500 ring-2 ring-red-500/50' : 'border-2 border-gray-200 dark:border-gray-700' }} transition-all group-hover:shadow-md group-hover:scale-105">
                                                @if(method_exists($file, 'getMimeType') && str_starts_with($file->getMimeType(), 'image/'))
                                                    <img 
                                                        src="{{ $file->temporaryUrl() }}" 
                                                        alt="{{ $file->getClientOriginalName() }}"
                                                        class="w-full h-full object-cover"
                                                    />
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800">
                                                        <x-heroicon-o-document class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                                                    </div>
                                                @endif
                                            </div>
                                            <button
                                                type="button"
                                                wire:click="removeFile({{ $index }})"
                                                class="absolute -top-2 -right-2 rounded-full bg-red-500 p-1.5 text-white hover:bg-red-600 shadow-lg opacity-0 group-hover:opacity-100 transition-all hover:scale-110 z-10"
                                                title="Supprimer"
                                            >
                                                <x-heroicon-o-x-mark class="h-4 w-4" />
                                            </button>
                                            <p class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate mt-2 text-center" title="{{ $file->getClientOriginalName() }}">
                                                {{ Str::limit($file->getClientOriginalName(), 15) }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                {{-- Footer --}}
                <div class="border-t-2 border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-900/50 dark:to-gray-800 px-6 py-5">
                    <div class="flex items-center justify-end gap-3">
                        <x-filament::button
                            wire:click="closeUploadModal"
                            color="gray"
                            outlined
                            size="sm"
                            class="btn-hover-lift"
                        >
                            Annuler
                        </x-filament::button>
                        <x-filament::button
                            wire:click="uploadFiles"
                            color="primary"
                            :disabled="empty($uploadedFiles) || !empty($validationErrors)"
                            wire:loading.attr="disabled"
                            wire:target="uploadFiles"
                            size="sm"
                            class="btn-hover-lift font-semibold"
                        >
                            <x-slot name="icon" wire:loading.remove wire:target="uploadFiles">
                                <x-heroicon-o-arrow-up-tray class="h-4 w-4" />
                            </x-slot>
                            <span wire:loading.remove wire:target="uploadFiles">
                                ↑ Importer
                                @if(count($uploadedFiles) > 0)
                                    <span class="ml-1 px-2 py-0.5 rounded-full bg-white/20 text-xs">({{ count($uploadedFiles) }})</span>
                                @endif
                            </span>
                            <span wire:loading wire:target="uploadFiles" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Import en cours...
                            </span>
                        </x-filament::button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 text-center">
                        💡 Appuyez sur <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded-md text-xs font-mono font-semibold">Ctrl+Entrée</kbd> pour importer rapidement
                    </p>
                </div>
                </div>
            </div>
        </div>
    </div>
