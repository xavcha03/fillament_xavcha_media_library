<div class="space-y-6">
    {{-- Toolbar --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                Vue
            </span>
            <x-filament::button
                wire:click="setGridView"
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
                wire:click="setListView"
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
                @if(!empty($selectedMediaIds))
                    {{-- Toolbar contextuelle quand sélection active --}}
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ count($selectedMediaIds) }} {{ count($selectedMediaIds) === 1 ? 'média sélectionné' : 'médias sélectionnés' }}
                    </span>
                    <div x-data="{ open: false }" class="relative">
                        <x-filament::button
                            x-on:click="open = !open"
                            size="sm"
                            color="primary"
                            outlined
                        >
                            Actions
                        </x-filament::button>
                        <div
                            x-show="open"
                            x-cloak
                            x-on:click.away="open = false"
                            class="absolute right-0 mt-2 w-48 fi-dropdown-panel rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 z-10"
                        >
                            <div class="py-1">
                                <button
                                    wire:click="bulkDelete"
                                    wire:confirm="Êtes-vous sûr de vouloir supprimer les médias sélectionnés ?"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
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
                        wire:click="selectAllInPage"
                        size="sm"
                        color="gray"
                        outlined
                    >
                        Sélectionner tout dans la page
                    </x-filament::button>
                    <x-filament::button
                        wire:click="clearSelection"
                        size="sm"
                        color="gray"
                        outlined
                    >
                        Annuler
                    </x-filament::button>
                @else
                    {{-- Toolbar par défaut --}}
                    @if(config('media-library-pro.actions.create_folder', true) && config('media-library-pro.folders.enabled', true))
                        <x-filament::button
                            wire:click="openCreateFolderModal"
                            size="sm"
                            color="success"
                            outlined
                        >
                            <x-slot name="icon">
                                <x-heroicon-o-folder-plus class="w-4 h-4" />
                            </x-slot>
                            Créer un dossier
                        </x-filament::button>
                    @endif
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
            @endif
        </div>
    </div>

    {{-- Breadcrumbs pour navigation par dossiers --}}
    @if(!$pickerMode && config('media-library-pro.folders.enabled', true))
        <div class="flex items-center gap-2 text-sm">
            <button
                wire:click="navigateToFolder(null)"
                class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium"
            >
                Racine
            </button>
            @if($currentFolder)
                @php
                    $breadcrumbs = $currentFolder->getBreadcrumbs();
                @endphp
                @foreach($breadcrumbs as $index => $crumb)
                    @if($crumb['id'] !== null)
                        <span class="text-gray-400 dark:text-gray-600">/</span>
                        <button
                            wire:click="navigateToFolder({{ $crumb['id'] }})"
                            class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium"
                        >
                            {{ $crumb['name'] }}
                        </button>
                    @endif
                @endforeach
            @endif
        </div>
    @endif

    {{-- Dossiers enfants --}}
    @if(!$pickerMode && config('media-library-pro.folders.enabled', true) && $childFolders && $childFolders->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4 mb-6">
            @foreach($childFolders as $folder)
                <button
                    wire:click="navigateToFolder({{ $folder->id }})"
                    class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-primary-500 dark:hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all group"
                >
                    <x-heroicon-o-folder class="h-12 w-12 text-primary-500 dark:text-primary-400 mb-2 group-hover:scale-110 transition-transform" />
                    <span class="text-sm font-medium text-gray-900 dark:text-white truncate w-full text-center">
                        {{ $folder->name }}
                    </span>
                </button>
            @endforeach
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[150px]">
                <label class="fi-input-label block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Collection
                </label>
                <select 
                    wire:model.live="filters.collection" 
                    class="fi-input block w-full rounded-lg border-none bg-white px-2 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500"
                >
                    <option value="">Toutes</option>
                    @foreach(\Xavier\MediaLibraryPro\Models\MediaAttachment::distinct()->pluck('collection_name')->filter() as $collection)
                        <option value="{{ $collection }}">{{ $collection }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[150px]">
                <label class="fi-input-label block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Type
                </label>
                <select 
                    wire:model.live="filters.type" 
                    class="fi-input block w-full rounded-lg border-none bg-white px-2 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500"
                >
                    <option value="">Tous</option>
                    <option value="image">Images</option>
                    <option value="video">Vidéos</option>
                    <option value="audio">Audio</option>
                    <option value="document">Documents</option>
                    <option value="archive">Archives</option>
                </select>
            </div>

            <div class="flex-1 min-w-[150px]">
                <label class="fi-input-label block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Trier par
                </label>
                <select 
                    wire:model.live="sortBy" 
                    class="fi-input block w-full rounded-lg border-none bg-white px-2 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500"
                >
                    <option value="created_at">Date</option>
                    <option value="name">Nom</option>
                    <option value="size">Taille</option>
                    <option value="mime_type">Type</option>
                </select>
            </div>
        </div>
    </div>
</div>

