@php
    $selectedMedia = $getSelectedMedia();
    $selectedMediaFiles = $getSelectedMediaFiles();
    $isMultiple = $isMultiple();
    $acceptedTypes = $getAcceptedFileTypes();
    $collection = $getCollection();
    $maxFiles = $getMaxFiles();
    $minFiles = $getMinFiles();
    $showUpload = $getShowUpload();
    $showLibrary = $getShowLibrary();
    $conversion = $getConversion();
    $maxFileSize = $getMaxFileSize();
    $allowReordering = $canReorder();
    $downloadable = $isDownloadable();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="mediaPickerUnified({
            selected: @js($selectedMedia),
            selectedFiles: @js($selectedMediaFiles),
            multiple: @js($isMultiple),
            acceptedTypes: @js($acceptedTypes),
            collection: @js($collection),
            maxFiles: @js($maxFiles),
            minFiles: @js($minFiles),
            showUpload: @js($showUpload),
            showLibrary: @js($showLibrary),
            conversion: @js($conversion),
            maxFileSize: @js($maxFileSize),
            allowReordering: @js($allowReordering),
            downloadable: @js($downloadable),
            baseUrl: @js(url('/media-library-pro/serve/')),
            statePath: @js($getStatePath())
        })"
        class="space-y-2"
    >
        {{-- Selected Media Display --}}
        <div class="flex flex-wrap gap-2" x-show="selected.length > 0">
            <template x-for="(mediaId, index) in selected" :key="mediaId">
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
                        $mediaFile = \Xavier\MediaLibraryPro\Models\MediaFile::find($mediaId);
                    @endphp
                    @if($mediaFile)
                        <div class="relative group">
                            <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded overflow-hidden ring-2 ring-primary-500">
                                @if($mediaFile->isImage())
                                    @php
                                        $imageUrl = $conversion && $mediaFile->getConversionUrl($conversion) 
                                            ? $mediaFile->getConversionUrl($conversion)
                                            : route('media-library-pro.serve', ['media' => $mediaFile->uuid]);
                                    @endphp
                                    <img src="{{ $imageUrl }}" alt="{{ $mediaFile->file_name }}" class="w-full h-full object-cover" />
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
                                x-on:click="removeMedia({{ $mediaId }})"
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
            <x-slot name="icon">
                <x-heroicon-o-photo class="w-4 h-4" />
            </x-slot>
            {{ $isMultiple ? 'Sélectionner des médias' : 'Sélectionner un média' }}
        </x-filament::button>

        {{-- Hidden Input --}}
        <input
            type="hidden"
            id="{{ $getStatePath() }}_hidden"
            x-ref="hiddenInput"
            wire:model.live="{{ $getStatePath() }}"
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
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0" style="position: relative; z-index: 1;">
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
                                {{ $isMultiple ? 'Sélectionner des médias' : 'Sélectionner un média' }}
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

                        {{-- Tabs --}}
                        @if($showUpload && $showLibrary)
                            <div class="flex space-x-1 mb-4 border-b border-gray-200 dark:border-gray-700">
                                <button
                                    type="button"
                                    x-on:click="activeTab = 'library'"
                                    x-bind:class="activeTab === 'library' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                    class="px-4 py-2 border-b-2 font-medium text-sm transition-colors"
                                >
                                    Bibliothèque
                                </button>
                                <button
                                    type="button"
                                    x-on:click="activeTab = 'upload'"
                                    x-bind:class="activeTab === 'upload' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                    class="px-4 py-2 border-b-2 font-medium text-sm transition-colors"
                                >
                                    Upload
                                </button>
                            </div>
                        @endif

                        {{-- Tab Content --}}
                        <div class="max-h-[70vh] overflow-y-auto">
                            {{-- Library Tab --}}
                            <div x-show="showLibrary && (!showUpload || activeTab === 'library')" x-transition>
                                @livewire('media-library-pro::media-library-picker', [
                                    'pickerMode' => true,
                                    'multiple' => $isMultiple,
                                    'acceptedTypes' => $acceptedTypes,
                                    'selectedIds' => $selectedMedia,
                                    'filterCollection' => $collection,
                                    'statePath' => $getStatePath(),
                                ], key('library-picker-' . $getStatePath()))
                            </div>

                            {{-- Upload Tab --}}
                            <div x-show="showUpload && activeTab === 'upload'" x-transition>
                                @livewire('media-library-pro::media-library-picker', [
                                    'pickerMode' => true,
                                    'multiple' => $isMultiple,
                                    'acceptedTypes' => $acceptedTypes,
                                    'selectedIds' => $selectedMedia,
                                    'uploadMode' => true,
                                    'filterCollection' => $collection,
                                    'statePath' => $getStatePath(),
                                ], key('upload-picker-' . $getStatePath()))
                            </div>
                        </div>

                        {{-- Footer Actions --}}
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                            <x-filament::button
                                type="button"
                                x-on:click="open = false"
                                size="sm"
                                color="gray"
                                outlined
                            >
                                Annuler
                            </x-filament::button>
                            <x-filament::button
                                type="button"
                                x-on:click="confirmSelection()"
                                x-bind:disabled="hasPendingUploads"
                                size="sm"
                                color="primary"
                            >
                                Valider
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('mediaPickerUnified', (config) => ({
            open: false,
            activeTab: config.showLibrary ? 'library' : 'upload',
            selected: config.selected || [],
            selectedFiles: config.selectedFiles || {},
            multiple: config.multiple || false,
            acceptedTypes: config.acceptedTypes || [],
            collection: config.collection || 'default',
            maxFiles: config.maxFiles,
            minFiles: config.minFiles || 0,
            showUpload: config.showUpload !== false,
            showLibrary: config.showLibrary !== false,
            conversion: config.conversion || null,
            baseUrl: config.baseUrl || '',
            statePath: config.statePath || '',
            hasPendingUploads: false, // Fichiers sélectionnés mais pas encore uploadés
            
            getMediaUrl(mediaId) {
                // mediaId est un ID, on doit récupérer l'UUID depuis selectedFiles
                const file = this.selectedFiles[mediaId];
                if (file) {
                    if (this.conversion && file.conversions?.[this.conversion]) {
                        return file.conversions[this.conversion];
                    }
                    return file.url || this.baseUrl + file.uuid;
                }
                // Fallback: utiliser l'ID directement (ne devrait pas arriver)
                return this.baseUrl + mediaId;
            },
            
            init() {
                // S'assurer que les IDs dans selected sont des entiers pour correspondre aux clés de selectedFiles
                this.selected = this.selected.map(id => parseInt(id));
                
                // S'assurer que les clés de selectedFiles sont des entiers
                const normalizedFiles = {};
                Object.keys(this.selectedFiles).forEach(key => {
                    normalizedFiles[parseInt(key)] = this.selectedFiles[key];
                });
                this.selectedFiles = normalizedFiles;
                
                // Watcher pour mettre à jour automatiquement le champ caché quand selected change
                this.$watch('selected', (newValue, oldValue) => {
                    if (JSON.stringify(newValue) !== JSON.stringify(oldValue)) {
                        this.$nextTick(() => {
                            this.updateForm();
                        });
                    }
                }, { deep: true });
                
                // Watcher pour selectedFiles pour forcer le rafraîchissement de l'affichage
                this.$watch('selectedFiles', () => {
                    // Forcer le rafraîchissement de l'affichage quand selectedFiles change
                    this.$nextTick(() => {
                        // L'affichage se mettra à jour automatiquement via Alpine.js
                    });
                }, { deep: true });
                
                // Écouter les événements globaux de sélection depuis le composant Livewire
                window.addEventListener('media-library-picker-select', (e) => {
                    // Filtrer par statePath pour isoler les instances
                    if (e.detail.statePath && e.detail.statePath !== this.statePath) {
                        return; // Ignorer les événements qui ne sont pas pour cette instance
                    }
                    
                    // Mettre à jour selectedFiles avec les infos du fichier sélectionné
                    if (e.detail.mediaId && e.detail.mediaUuid) {
                        const mediaId = parseInt(e.detail.mediaId);
                        this.selectedFiles[mediaId] = {
                            id: mediaId,
                            uuid: e.detail.mediaUuid,
                            file_name: e.detail.mediaFileName || '',
                            url: e.detail.mediaUrl || this.baseUrl + e.detail.mediaUuid,
                            conversions: e.detail.conversions || {}
                        };
                        
                        // Pour le mode single, mettre à jour immédiatement la sélection
                        if (!this.multiple) {
                            this.selected = [mediaId];
                            // Fermer le modal
                            this.open = false;
                            // Mettre à jour le formulaire immédiatement
                            this.$nextTick(() => {
                                this.updateForm();
                            });
                        } else {
                            // Pour le mode multiple, utiliser toggleMedia
                            this.toggleMedia(mediaId);
                        }
                    }
                });

                // Écouter les événements d'upload
                window.addEventListener('media-library-picker-uploaded', (e) => {
                    // Filtrer par statePath pour isoler les instances
                    if (e.detail.statePath && e.detail.statePath !== this.statePath) {
                        return; // Ignorer les événements qui ne sont pas pour cette instance
                    }
                    
                    if (e.detail.mediaId && e.detail.mediaUuid) {
                        const mediaId = parseInt(e.detail.mediaId);
                        // Mettre à jour selectedFiles avec les infos du fichier uploadé
                        this.selectedFiles[mediaId] = {
                            id: mediaId,
                            uuid: e.detail.mediaUuid,
                            file_name: e.detail.mediaFileName || '',
                            url: e.detail.mediaUrl || this.baseUrl + e.detail.mediaUuid,
                            conversions: {}
                        };
                        // Ajouter à la sélection
                        if (this.multiple) {
                            if (!this.selected.includes(mediaId)) {
                                this.selected.push(mediaId);
                            }
                            // Retourner à l'onglet Bibliothèque après l'upload
                            if (this.showLibrary) {
                                this.activeTab = 'library';
                            }
                        } else {
                            this.selected = [mediaId];
                            // Fermer le modal si sélection unique
                            this.open = false;
                        }
                        // Mettre à jour le formulaire
                        this.updateForm();
                        // Réinitialiser l'état des uploads en attente
                        this.hasPendingUploads = false;
                    }
                });
                
                // Vérifier périodiquement s'il y a des fichiers en attente d'upload
                const checkPendingUploads = () => {
                    // Chercher le composant Livewire d'upload dans le modal
                    const uploadTab = this.$el.querySelector('[x-show*=\"upload\"]');
                    if (uploadTab) {
                        const wireElement = uploadTab.querySelector('[wire\\:id]');
                        if (wireElement) {
                            const wireId = wireElement.getAttribute('wire:id');
                            if (wireId && window.Livewire) {
                                const component = window.Livewire.find(wireId);
                                if (component && component.get) {
                                    const files = component.get('uploadedFiles');
                                    this.hasPendingUploads = Array.isArray(files) && files.length > 0;
                                    return;
                                }
                            }
                        }
                    }
                    this.hasPendingUploads = false;
                };
                
                // Vérifier toutes les 300ms
                setInterval(checkPendingUploads, 300);
                // Vérifier immédiatement
                this.$nextTick(checkPendingUploads);
            },
            
            toggleMedia(mediaId) {
                mediaId = parseInt(mediaId);
                
                // Vérifier la limite maxFiles
                if (this.maxFiles && this.selected.length >= this.maxFiles && !this.selected.includes(mediaId)) {
                    return;
                }
                
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
                mediaId = parseInt(mediaId);
                const index = this.selected.indexOf(mediaId);
                if (index > -1) {
                    this.selected.splice(index, 1);
                    // Mettre à jour immédiatement
                    this.updateForm();
                }
            },
            
            confirmSelection() {
                // Vérifier minFiles
                if (this.selected.length < this.minFiles) {
                    alert(`Vous devez sélectionner au moins ${this.minFiles} fichier(s).`);
                    return;
                }
                
                this.updateForm();
                this.open = false;
            },
            
            updateForm() {
                const hiddenInput = this.$refs.hiddenInput || this.$el.querySelector('input[type="hidden"][wire\\:model], input[type="hidden"][wire\\:model\\.live], input[type="hidden"][wire\\:model\\.defer]');
                if (!hiddenInput) {
                    console.warn('Hidden input not found');
                    return;
                }
                
                // Calculer la nouvelle valeur
                let value;
                if (this.multiple) {
                    value = this.selected.length > 0 ? JSON.stringify(this.selected) : '[]';
                } else {
                    value = this.selected.length > 0 ? this.selected[0].toString() : '';
                }
                
                // Mettre à jour la valeur de l'input
                const oldValue = hiddenInput.value;
                hiddenInput.value = value;
                
                // Si la valeur n'a pas changé, ne rien faire
                if (oldValue === value) {
                    return;
                }
                
                // Trouver tous les composants Livewire possibles
                const wireElements = this.$el.querySelectorAll('[wire\\:id]');
                let livewireComponent = null;
                
                // Chercher le composant Livewire le plus proche (formulaire Filament)
                for (let element of wireElements) {
                    const wireId = element.getAttribute('wire:id');
                    if (wireId && window.Livewire) {
                        const component = window.Livewire.find(wireId);
                        if (component) {
                            livewireComponent = component;
                            break;
                        }
                    }
                }
                
                // Si on ne trouve pas, chercher dans le parent
                if (!livewireComponent) {
                    const parentWire = this.$el.closest('[wire\\:id]');
                    if (parentWire) {
                        const wireId = parentWire.getAttribute('wire:id');
                        if (wireId && window.Livewire) {
                            livewireComponent = window.Livewire.find(wireId);
                        }
                    }
                }
                
                // Méthode 1: Utiliser $wire si disponible (Alpine.js + Livewire v3)
                if (this.$wire && this.statePath) {
                    try {
                        this.$wire.set(this.statePath, value);
                        // Forcer la mise à jour pour le mode single
                        if (!this.multiple) {
                            this.$wire.$commit();
                        }
                    } catch (e) {
                        console.warn('Erreur $wire.set:', e);
                    }
                }
                
                // Méthode 2: Utiliser Livewire directement avec le statePath
                if (livewireComponent && this.statePath) {
                    try {
                        livewireComponent.set(this.statePath, value);
                        // Forcer la mise à jour
                        if (!this.multiple) {
                            livewireComponent.$commit();
                        }
                    } catch (e) {
                        console.warn('Erreur Livewire.set avec statePath:', e);
                    }
                }
                
                // Méthode 3: Utiliser le nom du wire:model directement
                const wireModelAttr = hiddenInput.getAttribute('wire:model') || hiddenInput.getAttribute('wire:model.live') || hiddenInput.getAttribute('wire:model.defer');
                if (livewireComponent && wireModelAttr) {
                    try {
                        livewireComponent.set(wireModelAttr, value);
                        // Forcer la mise à jour
                        if (!this.multiple) {
                            livewireComponent.$commit();
                        }
                    } catch (e) {
                        console.warn('Erreur Livewire.set avec wire:model:', e);
                    }
                }
                
                // Méthode 4: Déclencher les événements DOM (pour wire:model)
                // Utiliser un InputEvent au lieu d'un Event simple
                const inputEvent = new InputEvent('input', {
                    bubbles: true,
                    cancelable: true,
                    data: value,
                    inputType: 'insertText'
                });
                hiddenInput.dispatchEvent(inputEvent);
                
                const changeEvent = new Event('change', {
                    bubbles: true,
                    cancelable: true
                });
                hiddenInput.dispatchEvent(changeEvent);
                
                // Méthode 5: Déclencher un événement Livewire personnalisé
                if (livewireComponent) {
                    try {
                        livewireComponent.call('$set', wireModelAttr || this.statePath, value);
                    } catch (e) {
                        // Ignorer si la méthode n'existe pas
                    }
                }
            }
        }));
    });
</script>

