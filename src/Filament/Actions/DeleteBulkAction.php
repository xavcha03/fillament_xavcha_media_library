<?php

namespace Xavier\MediaLibraryPro\Filament\Actions;

use Filament\Actions\BulkAction;
use Xavier\MediaLibraryPro\Filament\Actions\Concerns\HasMediaAction;
use Xavier\MediaLibraryPro\Models\MediaFile;

class DeleteBulkAction extends BulkAction
{
    use HasMediaAction;

    public static function getDefaultName(): string
    {
        return 'deleteBulk';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Supprimer')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Supprimer les fichiers')
            ->modalDescription('Êtes-vous sûr de vouloir supprimer les fichiers sélectionnés ? Cette action est irréversible.')
            ->action(function ($records) {
                try {
                    $count = 0;
                    foreach ($records as $mediaFile) {
                        if ($mediaFile instanceof MediaFile) {
                            $mediaFile->delete();
                            $count++;
                        }
                    }
                    $this->sendSuccessNotification("{$count} fichier(s) ont été supprimés avec succès");
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






