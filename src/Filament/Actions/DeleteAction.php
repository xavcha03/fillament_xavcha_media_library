<?php

namespace Xavier\MediaLibraryPro\Filament\Actions;

use Filament\Actions\Action;
use Xavier\MediaLibraryPro\Filament\Actions\Concerns\HasMediaAction;
use Xavier\MediaLibraryPro\Models\MediaFile;

class DeleteAction extends Action
{
    use HasMediaAction;

    public static function getDefaultName(): string
    {
        return 'deleteMedia';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Supprimer')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Supprimer le fichier')
            ->modalDescription('Êtes-vous sûr de vouloir supprimer ce fichier ? Cette action est irréversible.')
            ->action(function (MediaFile $record) {
                try {
                    $fileName = $record->file_name;
                    $record->delete();
                    $this->sendSuccessNotification("Le fichier '{$fileName}' a été supprimé avec succès");
                } catch (\Exception $e) {
                    $this->sendErrorNotification($e->getMessage());
                }
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





