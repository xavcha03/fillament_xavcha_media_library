{{-- Upload Modal --}}
<div
    x-data="{ 
        show: @entangle('showUploadModal'),
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
        },
        handleKeydown(event) {
            if (event.key === 'Escape' && this.show) {
                $wire.closeUploadModal();
            }
            if (event.key === 'Enter' && event.ctrlKey && this.show && $wire.uploadedFiles.length > 0) {
                event.preventDefault();
                $wire.uploadFiles();
            }
        }
    }"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50"
    x-transition
    role="dialog"
    aria-modal="true"
    aria-labelledby="media-upload-title"
    @keydown.window="handleKeydown"
    x-init="
        $watch('show', value => {
            document.body.style.overflow = value ? 'hidden' : '';
            if (value) {
                $nextTick(() => {
                    const firstInput = $el.querySelector('input[type=file], input[type=text]');
                    if (firstInput) firstInput.focus();
                });
            }
        });
    "
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
        @click="$wire.closeUploadModal()"
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
            class="relative max-w-5xl mx-auto w-full transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-700 transition-all pointer-events-auto focus:outline-none"
            style="max-height: 90vh;"
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
                            <h3 id="media-upload-title" class="text-xl font-bold text-gray-900 dark:text-white">
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
            <div class="px-6 py-6 bg-white dark:bg-gray-800 overflow-y-auto" style="max-height: calc(90vh - 200px);">
                <div class="grid grid-cols-2 gap-6">
                    {{-- Colonne gauche : Zone de drag & drop --}}
                    <div class="space-y-4">
                        <label
                            for="file-upload"
                            @dragover.prevent="handleDragOver($event)"
                            @dragleave.prevent="handleDragLeave($event)"
                            @drop.prevent="handleDrop($event)"
                            :class="isDragging ? 'drag-zone-active border-primary-500 bg-primary-50 dark:bg-primary-900/30 ring-4 ring-primary-500/20 scale-[1.02]' : 'border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-800/30'"
                            class="relative block border-2 border-dashed rounded-2xl p-8 text-center transition-all duration-300 cursor-pointer hover:border-primary-400 hover:bg-gradient-to-br hover:from-primary-50 hover:to-white dark:hover:from-primary-900/10 dark:hover:to-gray-800 hover:shadow-lg group h-full min-h-[400px] flex items-center justify-center"
                        >
                            <input
                                id="file-upload"
                                type="file"
                                wire:model="uploadedFiles"
                                multiple
                                class="sr-only"
                                tabindex="-1"
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
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">📎 Formats : PNG, JPG, GIF, WEBP</span>
                                    <span class="text-gray-400 dark:text-gray-500">•</span>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Max 10MB</span>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Colonne droite : Collection, fichiers sélectionnés, erreurs --}}
                    <div class="space-y-4 overflow-y-auto" style="max-height: calc(90vh - 250px);">
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
                </div>
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
                            Traitement en cours...
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

