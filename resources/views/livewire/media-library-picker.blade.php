<div class="space-y-4">
    @if($uploadMode)
        {{-- Upload Mode --}}
        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
            <div wire:loading.remove wire:target="uploadedFiles">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="mt-4">
                    <label for="file-upload" class="cursor-pointer">
                        <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                            Glissez-déposez des fichiers ici
                        </span>
                        <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">
                            ou cliquez pour sélectionner
                        </span>
                    </label>
                    <input
                        id="file-upload"
                        type="file"
                        wire:model="uploadedFiles"
                        multiple
                        class="sr-only"
                        accept="{{ implode(',', $acceptedTypes) }}"
                    />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Formats acceptés: {{ implode(', ', $acceptedTypes) }}
                </p>
            </div>

            <div wire:loading wire:target="uploadedFiles" class="space-y-2">
                <div class="flex items-center justify-center">
                    <svg class="animate-spin h-8 w-8 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Upload en cours...</p>
            </div>

            @if(count($uploadedFiles) > 0)
                <div class="mt-4">
                    <button
                        type="button"
                        wire:click="uploadFiles"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="uploadFiles">Uploader {{ count($uploadedFiles) }} fichier(s)</span>
                        <span wire:loading wire:target="uploadFiles">Upload en cours...</span>
                    </button>
                </div>
            @endif
        </div>
    @else
        {{-- Library Mode --}}
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

