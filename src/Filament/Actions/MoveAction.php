<?php

namespace Xavier\MediaLibraryPro\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Xavier\MediaLibraryPro\Filament\Actions\Concerns\HasMediaAction;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Models\MediaFolder;
use Xavier\MediaLibraryPro\Services\MediaFolderService;

class MoveAction extends Action
{
    use HasMediaAction;

    public static function getDefaultName(): string
    {
        return 'moveMedia';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $folderService = app(MediaFolderService::class);
        $allFolders = MediaFolder::orderBy('name')->get();
        $folderOptions = [null => 'Racine'];
        foreach ($allFolders as $folder) {
            $folderOptions[$folder->id] = $folder->getFullPath();
        }

        $this->label('Déplacer')
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
            ->action(function (MediaFile $record, array $data) {
                try {
                    $folder = $data['folder_id'] ? MediaFolder::find($data['folder_id']) : null;
                    $record->folder_id = $folder?->id;
                    $record->save();
                    
                    $destination = $folder ? $folder->name : 'la racine';
                    $this->sendSuccessNotification("Le fichier a été déplacé vers {$destination}");
                } catch (\Exception $e) {
                    $this->sendErrorNotification($e->getMessage());
                }
            })
            ->modalHeading('Déplacer le fichier')
            ->modalDescription('Sélectionnez le dossier de destination pour ce fichier.');
    }

    /**
     * Configure l'action via une méthode statique
     */
    public static function configureUsing(\Closure $callback): void
    {
        static::configure($callback);
    }
}


