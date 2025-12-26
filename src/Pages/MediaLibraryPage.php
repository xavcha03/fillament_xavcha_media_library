<?php

namespace Xavier\MediaLibraryPro\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class MediaLibraryPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected string $view = 'media-library-pro::pages.media-library-page';

    protected static ?string $navigationLabel = 'Médias';

    protected static ?string $title = 'Bibliothèque Médias';

    protected static ?int $navigationSort = 100;

    protected static ?string $slug = 'media-library';

}

