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
            <div class="flex items-center justify-center px-4 py-4 text-center sm:block sm:p-0" style="position: relative; z-index: 1; min-height: 100%;">
                <div
                    x-show="open"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full modal-content-bg flex flex-col max-h-[90vh]"
                    style="position: relative; z-index: 2;"
                    @click.stop
                >
                    <div class="px-4 pt-5 pb-4 sm:p-6 modal-content-bg flex flex-col flex-1 min-h-0">
                        {{-- Header --}}
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
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
                            <div class="flex space-x-1 mb-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
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
                        <div class="flex-1 overflow-y-auto min-h-0">
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
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2 flex-shrink-0">
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

