@php
    $acceptedTypes = $acceptedTypes ?? [];
    $isMultiple = $isMultiple ?? false;
    $collection = $collection ?? 'default';
    $statePath = $statePath ?? $field->getStatePath();
@endphp

<div class="space-y-4 mt-4">
    {{-- Divider --}}
    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">OU</span>
        </div>
    </div>

    {{-- Media Library Picker Button --}}
    <div
        x-data="{
            open: false,
            selected: [],
            multiple: @js($isMultiple),
            acceptedTypes: @js($acceptedTypes),
            toggleMedia(mediaId) {
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
                    this.addMediaToSelection(mediaId);
                }
            },
            addMediaToSelection(mediaId) {
                // Stocker l'ID dans un champ caché pour qu'il soit sauvegardé avec le formulaire
                const hiddenInput = document.getElementById('{{ $statePath }}_selected_media');
                if (hiddenInput) {
                    if (this.multiple) {
                        const current = JSON.parse(hiddenInput.value || '[]');
                        if (!current.includes(mediaId)) {
                            current.push(mediaId);
                        }
                        hiddenInput.value = JSON.stringify(current);
                    } else {
                        hiddenInput.value = mediaId;
                    }
                    // Déclencher un événement pour mettre à jour Livewire
                    hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            },
            addSelectedMedia() {
                for (const mediaId of this.selected) {
                    this.addMediaToSelection(mediaId);
                }
                this.selected = [];
                this.open = false;
            }
        }"
    >
        <button
            type="button"
            x-on:click="open = true"
            class="fi-btn fi-btn-color-gray fi-btn-size-sm fi-btn-style-outline w-full"
        >
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Sélectionner depuis la bibliothèque
        </button>

        {{-- Hidden Input for Selected Media IDs --}}
        <input
            type="hidden"
            id="{{ $statePath }}_selected_media"
            wire:model="{{ $statePath }}_selected_media"
            value=""
        />

        {{-- Modal --}}
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div
                    x-on:click="open = false"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                ></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Sélectionner un média
                            </h3>
                            <button
                                type="button"
                                x-on:click="open = false"
                                class="text-gray-400 hover:text-gray-500"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="max-h-96 overflow-y-auto">
                            @livewire('media-library-pro::media-library', [
                                'pickerMode' => true, 
                                'multiple' => $isMultiple, 
                                'acceptedTypes' => $acceptedTypes
                            ])
                        </div>

                        @if($isMultiple)
                            <div class="mt-4 flex justify-end gap-2">
                                <button
                                    type="button"
                                    x-on:click="open = false"
                                    class="fi-btn fi-btn-color-gray fi-btn-size-sm fi-btn-style-outline"
                                >
                                    Annuler
                                </button>
                                <button
                                    type="button"
                                    x-on:click="addSelectedMedia()"
                                    x-bind:disabled="selected.length === 0"
                                    class="fi-btn fi-btn-color-primary fi-btn-size-sm"
                                >
                                    Ajouter (<span x-text="selected.length"></span>)
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>







