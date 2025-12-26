<x-filament-panels::page>
    @php
        $cssPath = public_path('vendor/media-library-pro/css/media-library-pro.css');
    @endphp
    
    @if(file_exists($cssPath))
        <style>
            {!! file_get_contents($cssPath) !!}
        </style>
    @endif
    
    @livewire('media-library-pro::media-library')
</x-filament-panels::page>

