<div class="space-y-4">
    @if($uploadMode)
        {{-- Upload Mode --}}
        <div 
            x-data="{ 
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
                    const input = document.getElementById('file-upload-picker');
                    if (input) {
                        const dataTransfer = new DataTransfer();
                        files.forEach(file => dataTransfer.items.add(file));
                        input.files = dataTransfer.files;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            }"
        >
            <div class="grid grid-cols-2 gap-6">
                {{-- Colonne gauche : Zone de drag & drop --}}
                <div class="space-y-4">
                    <label
                        for="file-upload-picker"
                        @dragover.prevent="handleDragOver($event)"
                        @dragleave.prevent="handleDragLeave($event)"
                        @drop.prevent="handleDrop($event)"
                        :class="isDragging ? 'drag-zone-active border-primary-500 bg-primary-50 dark:bg-primary-900/30 ring-4 ring-primary-500/20 scale-[1.02]' : 'border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-800/30'"
                        class="relative block border-2 border-dashed rounded-2xl p-8 text-center transition-all duration-300 cursor-pointer hover:border-primary-400 hover:bg-gradient-to-br hover:from-primary-50 hover:to-white dark:hover:from-primary-900/10 dark:hover:to-gray-800 hover:shadow-lg group h-full min-h-[400px] flex items-center justify-center"
                    >
                        <input
                            id="file-upload-picker"
                            type="file"
                            wire:model="uploadedFiles"
                            multiple
                            class="sr-only"
                            tabindex="-1"
                            accept="{{ implode(',', $acceptedTypes) }}"
                        />
                        <div class="flex flex-col items-center justify-center space-y-4 w-full">
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
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">📎 Formats : {{ implode(', ', array_slice($acceptedTypes, 0, 3)) }}</span>
                                <span class="text-gray-400 dark:text-gray-500">•</span>
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Max {{ config('media-library-pro.validation.max_size', 10240) / 1024 }}MB</span>
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Colonne droite : Dossier, Collection, fichiers sélectionnés, erreurs --}}
                <div class="space-y-4 overflow-y-auto" style="max-height: calc(90vh - 250px);">
                    @if(config('media-library-pro.folders.enabled', true))
                        {{-- Sélection de dossier --}}
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <x-heroicon-o-folder class="h-4 w-4 text-primary-500 dark:text-primary-400" />
                                    <span>Dossier de destination</span>
                                </label>
                                <button
                                    type="button"
                                    wire:click="openCreateFolderModal"
                                    class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 px-2 py-1 rounded-lg transition-all"
                                    title="Créer un dossier"
                                >
                                    <x-heroicon-o-folder-plus class="h-4 w-4" />
                                </button>
                            </div>
                            <select 
                                wire:model="uploadFolderId" 
                                class="fi-input block w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm px-4 py-2.5 font-medium"
                            >
                                <option value="">Racine (aucun dossier)</option>
                                @foreach($this->rootFolders as $folder)
                                    <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 italic">
                                💡 Les fichiers seront placés dans ce dossier.
                            </p>
                        </div>
                    @endif

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
                            <div class="grid grid-cols-3 gap-3 max-h-64 overflow-y-auto rounded-lg border-2 border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
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
                                            wire:click="removeUploadedFile({{ $index }})"
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
                            <div class="mt-4 flex justify-end">
                                <button
                                    type="button"
                                    wire:click="uploadFiles"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                                >
                                    <span wire:loading.remove wire:target="uploadFiles">
                                        <x-heroicon-o-arrow-up-tray class="h-4 w-4 mr-2" />
                                        Uploader {{ count($uploadedFiles) }} fichier(s)
                                    </span>
                                    <span wire:loading wire:target="uploadFiles" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Upload en cours...
                                    </span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Modal de création de dossier --}}
            @if($showCreateFolderModal)
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0, 0, 0, 0.5);">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Créer un nouveau dossier</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nom du dossier
                                </label>
                                <input
                                    type="text"
                                    wire:model="folderName"
                                    placeholder="Nom du dossier"
                                    class="fi-input block w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm px-4 py-2.5 font-medium"
                                />
                                @error('folderName')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    wire:click="closeCreateFolderModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                                >
                                    Annuler
                                </button>
                                <button
                                    type="button"
                                    wire:click="createFolder"
                                    class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700"
                                >
                                    Créer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        {{-- Library Mode --}}
        @if(config('media-library-pro.folders.enabled', true))
            {{-- Breadcrumb de navigation --}}
            @if($currentFolderId !== null)
                <div class="mb-4 flex items-center gap-2 text-sm">
                    <button
                        type="button"
                        wire:click="navigateToFolder(null)"
                        class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium"
                    >
                        Racine
                    </button>
                    <span class="text-gray-400">/</span>
                    @if($this->currentFolder)
                        <span class="text-gray-700 dark:text-gray-300">{{ $this->currentFolder->name }}</span>
                    @endif
                </div>
            @endif

            {{-- Dossiers enfants --}}
            @if($this->childFolders && $this->childFolders->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-4">
                    @foreach($this->childFolders as $folder)
                        <button
                            type="button"
                            wire:click="navigateToFolder({{ $folder->id }})"
                            class="flex flex-col items-center justify-center p-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-primary-500 dark:hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all group"
                        >
                            <x-heroicon-o-folder class="h-10 w-10 text-primary-500 dark:text-primary-400 mb-2 group-hover:scale-110 transition-transform" />
                            <span class="text-xs font-medium text-gray-900 dark:text-white truncate w-full text-center">
                                {{ $folder->name }}
                            </span>
                        </button>
                    @endforeach
                </div>
            @endif
        @endif

        {{-- Filtre par collection --}}
        <div class="mb-4">
            <label class="fi-input-label block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Collection
            </label>
            <select 
                wire:model.live="filterCollection" 
                class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-base text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 outline-none transition duration-75 focus:ring-2 focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/20 dark:focus:ring-primary-500"
            >
                <option value="">Toutes les collections</option>
                @foreach(\Xavier\MediaLibraryPro\Models\MediaAttachment::distinct()->pluck('collection_name')->filter() as $collection)
                    <option value="{{ $collection }}">{{ $collection }}</option>
                @endforeach
            </select>
        </div>

        @if($media->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($media as $item)
                    @php
                        $isItemSelected = $this->isSelected($item->id);
                    @endphp
                    <div
                        wire:click="selectMedia('{{ $item->uuid }}')"
                        class="relative group cursor-pointer rounded-lg overflow-hidden border-2 transition-all {{ $isItemSelected ? 'border-primary-500 ring-2 ring-primary-500' : 'border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600' }}"
                    >
                        @if($item->isImage())
                            <div class="aspect-square bg-gray-100 dark:bg-gray-800">
                                <img
                                    src="{{ route('media-library-pro.serve', ['media' => $item->uuid]) }}"
                                    alt="{{ $item->file_name }}"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                />
                            </div>
                        @else
                            <div class="aspect-square bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        @endif
                        
                        @if($isItemSelected)
                            <div class="absolute top-2 right-2 bg-primary-500 text-white rounded-full p-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @endif

                        <div class="p-2 bg-white dark:bg-gray-800">
                            <p class="text-xs text-gray-600 dark:text-gray-400 truncate" title="{{ $item->file_name }}">
                                {{ Str::limit($item->file_name, 20) }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $media->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Aucun média</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Aucun média trouvé dans la bibliothèque.</p>
            </div>
        @endif
    @endif
</div>

