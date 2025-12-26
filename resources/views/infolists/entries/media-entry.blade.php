@php
    $mediaItems = $getMediaItems($getRecord());
    $size = $getSize();
    $conversion = $getConversion();
    $multiple = $isMultiple();
@endphp

<div class="space-y-2">
    @if(empty($mediaItems))
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Aucun média
        </div>
    @else
        @if($multiple)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($mediaItems as $attachment)
                    @php
                        $mediaFile = $attachment->mediaFile;
                        $url = $conversion && $mediaFile->getConversionUrl($conversion) 
                            ? $mediaFile->getConversionUrl($conversion)
                            : route('media-library-pro.serve', ['media' => $mediaFile->uuid]);
                    @endphp
                    <div class="relative">
                        @if($mediaFile->isImage())
                            <img 
                                src="{{ $url }}" 
                                alt="{{ $mediaFile->file_name }}" 
                                class="w-full rounded-lg shadow-sm"
                                style="height: {{ $size }}px; object-fit: cover;"
                            />
                        @else
                            <div class="w-full rounded-lg shadow-sm bg-gray-100 dark:bg-gray-700 flex items-center justify-center" style="height: {{ $size }}px;">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        @endif
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $mediaFile->file_name }}
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            @php
                $attachment = $mediaItems[0] ?? null;
            @endphp
            @if($attachment)
                @php
                    $mediaFile = $attachment->mediaFile;
                    $url = $conversion && $mediaFile->getConversionUrl($conversion) 
                        ? $mediaFile->getConversionUrl($conversion)
                        : route('media-library-pro.serve', ['media' => $mediaFile->uuid]);
                @endphp
                <div>
                    @if($mediaFile->isImage())
                        <img 
                            src="{{ $url }}" 
                            alt="{{ $mediaFile->file_name }}" 
                            class="rounded-lg shadow-sm"
                            style="max-width: {{ $size }}px; max-height: {{ $size }}px; object-fit: contain;"
                        />
                    @else
                        <div class="rounded-lg shadow-sm bg-gray-100 dark:bg-gray-700 flex items-center justify-center" style="width: {{ $size }}px; height: {{ $size }}px;">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    @endif
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ $mediaFile->file_name }}
                    </p>
                </div>
            @endif
        @endif
    @endif
</div>
