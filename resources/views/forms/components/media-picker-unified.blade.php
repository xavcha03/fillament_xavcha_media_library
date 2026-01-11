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
        class="space-y-2 overflow-visible"
    >
        {{-- Selected Media Display --}}
        <div x-show="selected.length > 0" class="mb-2 overflow-visible">
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 p-3 overflow-visible">
                {{-- Header avec compteur --}}
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-check-circle class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            <span x-text="selected.length"></span>
                            <span x-text="selected.length === 1 ? ' fichier sélectionné' : ' fichiers sélectionnés'"></span>
                        </span>
                    </div>
                    <button
                        type="button"
                        x-on:click="selected = []; updateForm();"
                        class="text-xs font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                        x-show="selected.length > 1"
                    >
                        Tout supprimer
                    </button>
                </div>
                
                {{-- Grille de miniatures --}}
                <div class="flex flex-wrap gap-3 overflow-visible">
                    <template x-for="(mediaId, index) in selected" :key="mediaId">
                        <div class="relative group flex-shrink-0">
                            <div class="relative w-32 h-32 bg-white dark:bg-gray-700 rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-600 shadow-md hover:shadow-lg transition-all hover:border-primary-400 dark:hover:border-primary-500">
                                <template x-if="isImage(mediaId)">
                                    <img 
                                        x-bind:src="getMediaUrl(mediaId)"
                                        class="w-full h-full object-cover"
                                        x-bind:alt="selectedFiles[mediaId]?.file_name || `Media ${mediaId}`"
                                    />
                                </template>
                                <template x-if="!isImage(mediaId)">
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-600 dark:to-gray-700">
                                        <x-heroicon-o-document class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                                    </div>
                                </template>
                                
                                {{-- Badge de numéro pour plusieurs images --}}
                                <div 
                                    x-show="selected.length > 1"
                                    class="absolute bottom-0 left-0 right-0 bg-black/70 text-white text-sm font-semibold px-2 py-1 text-center"
                                    x-text="index + 1"
                                ></div>
                            </div>
                            
                            {{-- Bouton de suppression --}}
                            <button
                                type="button"
                                x-on:click.stop="removeMedia(mediaId)"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-7 h-7 flex items-center justify-center hover:bg-red-600 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-10"
                                title="Supprimer"
                            >
                                <x-heroicon-o-x-mark class="w-4 h-4" />
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        
        {{-- Fallback pour le rendu initial côté serveur --}}
        @if(!empty($selectedMedia))
            <div class="mb-2 overflow-visible" x-show="false">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 p-3 overflow-visible">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-check-circle class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ count($selectedMedia) }} {{ count($selectedMedia) === 1 ? 'fichier sélectionné' : 'fichiers sélectionnés' }}
                            </span>
                        </div>
                        @if(count($selectedMedia) > 1)
                            <button
                                type="button"
                                x-on:click="selected = []; updateForm();"
                                class="text-xs font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                            >
                                Tout supprimer
                            </button>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-3 overflow-visible">
                        @foreach($selectedMedia as $index => $mediaId)
                            @php
                                $mediaFile = \Xavier\MediaLibraryPro\Models\MediaFile::find($mediaId);
                            @endphp
                            @if($mediaFile)
                                <div class="relative group flex-shrink-0">
                                    <div class="relative w-32 h-32 bg-white dark:bg-gray-700 rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-600 shadow-md hover:shadow-lg transition-all hover:border-primary-400 dark:hover:border-primary-500">
                                        @if($mediaFile->isImage())
                                            @php
                                                $imageUrl = $conversion && $mediaFile->getConversionUrl($conversion) 
                                                    ? $mediaFile->getConversionUrl($conversion)
                                                    : route('media-library-pro.serve', ['media' => $mediaFile->uuid]);
                                            @endphp
                                            <img src="{{ $imageUrl }}" alt="{{ $mediaFile->file_name }}" class="w-full h-full object-cover" />
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-600 dark:to-gray-700">
                                                <x-heroicon-o-document class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                                            </div>
                                        @endif
                                        
                                        @if(count($selectedMedia) > 1)
                                            <div class="absolute bottom-0 left-0 right-0 bg-black/70 text-white text-sm font-semibold px-2 py-1 text-center">
                                                {{ $index + 1 }}
                                            </div>
                                        @endif
                                    </div>
                                    <button
                                        type="button"
                                        x-on:click="removeMedia({{ $mediaId }})"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-7 h-7 flex items-center justify-center hover:bg-red-600 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-10"
                                        title="Supprimer"
                                    >
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
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
            class="fixed inset-0 z-50"
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
            <div class="flex items-center justify-center px-4 py-4 text-center sm:block sm:p-0" style="position: relative; z-index: 1;">
                <div
                    x-show="open"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full modal-content-bg"
                    style="position: relative; z-index: 2; max-height: 90vh; display: flex; flex-direction: column;"
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
                        <div class="overflow-y-auto" style="max-height: calc(90vh - 250px);">
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

