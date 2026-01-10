<?php

namespace Xavier\MediaLibraryPro\Filament\Actions;

use Filament\Actions\Action;
use Xavier\MediaLibraryPro\Filament\Actions\Concerns\HasMediaAction;
use Xavier\MediaLibraryPro\Models\MediaFile;

class SelectFileAction extends Action
{
    use HasMediaAction;

    public static function getDefaultName(): string
    {
        return 'selectFile';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Sélectionner')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->action(function (MediaFile $record) {
                // Cette action est principalement utilisée dans le contexte du picker
                // Elle peut être étendue pour émettre des événements Livewire
                $this->sendSuccessNotification("Le fichier '{$record->file_name}' a été sélectionné");
            });
    }

    /**
     * Configure l'action via une méthode statique
     */
    public static function configureUsing(\Closure $callback): void
    {
        static::configure($callback);
    }
}






