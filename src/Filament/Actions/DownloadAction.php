<?php

namespace Xavier\MediaLibraryPro\Filament\Actions;

use Filament\Actions\Action;
use Xavier\MediaLibraryPro\Filament\Actions\Concerns\HasMediaAction;
use Xavier\MediaLibraryPro\Models\MediaFile;

class DownloadAction extends Action
{
    use HasMediaAction;

    public static function getDefaultName(): string
    {
        return 'downloadMedia';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Télécharger')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->url(fn (MediaFile $record) => route('media-library-pro.download', ['media' => $record->uuid]))
            ->openUrlInNewTab();
    }

    /**
     * Configure l'action via une méthode statique
     */
    public static function configureUsing(\Closure $callback): void
    {
        static::configure($callback);
    }
}





