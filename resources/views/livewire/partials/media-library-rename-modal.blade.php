{{-- Modale de renommage --}}
@if($showRenameModal && $detailMedia)
    <div 
        x-data="{ open: @entangle('showRenameModal') }"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div 
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                x-on:click="open = false"
            ></div>

            <div 
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
            >
                <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                    <button
                        type="button"
                        wire:click="closeRenameModal"
                        class="rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    >
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>

                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-warning-100 dark:bg-warning-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <x-heroicon-o-pencil class="h-6 w-6 text-warning-600 dark:text-warning-400" />
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                        <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white mb-2">
                            Renommer le fichier
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Entrez le nouveau nom du fichier. L'extension sera conservée automatiquement.
                        </p>

                        <form wire:submit="renameMedia">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                        Nom du fichier
                                    </label>
                                    <input
                                        type="text"
                                        wire:model="renameFileName"
                                        placeholder="Nom du fichier"
                                        class="fi-input block w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm px-4 py-2.5 font-medium"
                                        autofocus
                                    />
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        Extension actuelle : <span class="font-semibold">{{ $detailMedia->getExtension() }}</span>
                                    </p>
                                    @error('renameFileName')
                                        <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                                <x-filament::button
                                    type="submit"
                                    color="warning"
                                    size="sm"
                                >
                                    <x-slot name="icon">
                                        <x-heroicon-o-check class="h-4 w-4" />
                                    </x-slot>
                                    Renommer
                                </x-filament::button>
                                <x-filament::button
                                    wire:click="closeRenameModal"
                                    color="gray"
                                    outlined
                                    size="sm"
                                >
                                    Annuler
                                </x-filament::button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

