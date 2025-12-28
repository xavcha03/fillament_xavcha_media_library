<?php

namespace Xavier\MediaLibraryPro\Filament\Actions;

use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Xavier\MediaLibraryPro\Filament\Actions\Concerns\HasMediaAction;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Models\MediaFolder;

class MoveBulkAction extends BulkAction
{
    use HasMediaAction;

    public static function getDefaultName(): string
    {
        return 'moveBulk';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $allFolders = MediaFolder::orderBy('name')->get();
        $folderOptions = [null => 'Racine'];
        foreach ($allFolders as $folder) {
            $folderOptions[$folder->id] = $folder->getFullPath();
        }

        $this->label('Déplacer vers un dossier')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('info')
            ->form([
                Select::make('folder_id')
                    ->label('Déplacer vers')
                    ->options($folderOptions)
                    ->nullable()
                    ->searchable()
                    ->helperText('Sélectionnez le dossier de destination ou laissez vide pour la racine')
            ])
            ->action(function ($records, array $data) {
                try {
                    $folder = $data['folder_id'] ? MediaFolder::find($data['folder_id']) : null;
                    
                    $count = 0;
                    foreach ($records as $mediaFile) {
                        if ($mediaFile instanceof MediaFile) {
                            $mediaFile->folder_id = $folder?->id;
                            $mediaFile->save();
                            $count++;
                        }
                    }

                    $destination = $folder ? $folder->name : 'la racine';
                    $this->sendSuccessNotification("{$count} fichier(s) ont été déplacés vers {$destination}");
                } catch (\Exception $e) {
                    $this->sendErrorNotification($e->getMessage());
                }
            })
            ->requiresConfirmation()
            ->modalHeading('Déplacer les fichiers')
            ->modalDescription('Sélectionnez le dossier de destination pour tous les fichiers sélectionnés.');
    }

    /**
     * Configure l'action via une méthode statique
     */
    public static function configureUsing(\Closure $callback): void
    {
        static::configure($callback);
    }
}





