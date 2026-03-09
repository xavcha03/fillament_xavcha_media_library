{{-- Media Content - List View --}}
<x-filament::section>
    <div class="overflow-x-auto -mx-4 sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full align-middle">
            <div class="overflow-hidden">
                <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                    <thead class="divide-y divide-gray-200 dark:divide-white/5">
                        <tr class="bg-gray-50 dark:bg-white/5">
                            @if(!$pickerMode)
                                <th scope="col" class="fi-ta-header-cell px-3 py-3.5 sm:px-6">
                                    <input
                                        type="checkbox"
                                        wire:click="selectAllInPage"
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
                            @if(!$pickerMode)
                                <th scope="col" class="fi-ta-header-cell px-3 py-3.5 sm:px-6">
                                    <span class="fi-ta-header-cell-label text-xs font-semibold text-gray-950 dark:text-white sm:text-sm">Actions</span>
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                        @forelse($media as $item)
                            @php $isSelected = in_array($item->id, $selectedMediaIds); @endphp
                            <tr class="fi-ta-row transition duration-75 hover:bg-gray-50 dark:hover:bg-white/5 {{ $isSelected ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}">
                                @if(!$pickerMode)
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3" x-data>
                                        <div class="px-3 py-4 sm:px-6">
                                            <input
                                                type="checkbox"
                                                @checked($isSelected)
                                                x-on:click="const m = $event.shiftKey ? 'shift' : ($event.ctrlKey || $event.metaKey ? 'ctrl' : null); $wire.toggleSelect({{ $item->id }}, m)"
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
                                                    $version = $item->updated_at?->timestamp ?? $item->size ?? time();
                                                    try {
                                                        $imageUrl = route('media-library-pro.serve', ['media' => $item->uuid, 't' => $version]);
                                                    } catch (\Exception $e) {
                                                        $imageUrl = url('/media-library-pro/serve/' . $item->uuid . '?t=' . $version);
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
                                @if(!$pickerMode)
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="px-3 py-4 sm:px-6">
                                            <button
                                                type="button"
                                                wire:click="openDetailModal('{{ $item->uuid }}')"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-primary-500 dark:hover:border-primary-400 transition-all"
                                                title="Voir les détails"
                                            >
                                                <x-heroicon-o-eye class="w-4 h-4" />
                                                <span>Détails</span>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $pickerMode ? '6' : '8' }}" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
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

