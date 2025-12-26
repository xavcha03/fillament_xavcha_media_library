@php
    $mediaItems = $getMediaItems($getRecord());
    $size = $getSize();
    $conversion = $getConversion();
    $multiple = $isMultiple();
@endphp

<div class="flex items-center gap-2">
    @if(empty($mediaItems))
        <div 
            class="bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center"
            style="width: {{ $size }}px; height: {{ $size }}px;"
        >
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    @else
        @foreach($mediaItems as $attachment)
            @php
                $mediaFile = $attachment->mediaFile;
                $url = $conversion && $mediaFile->getConversionUrl($conversion) 
                    ? $mediaFile->getConversionUrl($conversion)
                    : route('media-library-pro.serve', ['media' => $mediaFile->uuid]);
            @endphp
            @if($mediaFile->isImage())
                <img 
                    src="{{ $url }}" 
                    alt="{{ $mediaFile->file_name }}" 
                    class="rounded"
                    style="width: {{ $size }}px; height: {{ $size }}px; object-fit: cover;"
                />
            @else
                <div 
                    class="bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center"
                    style="width: {{ $size }}px; height: {{ $size }}px;"
                >
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            @endif
        @endforeach
    @endif
</div>
