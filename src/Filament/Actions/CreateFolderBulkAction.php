<?php

namespace Xavier\MediaLibraryPro\Filament\Actions;

use Filament\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Xavier\MediaLibraryPro\Filament\Actions\Concerns\HasMediaAction;
use Xavier\MediaLibraryPro\Models\MediaFile;
use Xavier\MediaLibraryPro\Services\MediaFolderService;

class CreateFolderBulkAction extends BulkAction
{
    use HasMediaAction;

    public static function getDefaultName(): string
    {
        return 'createFolderBulk';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Créer un dossier et déplacer')
            ->icon('heroicon-o-folder-plus')
            ->color('primary')
            ->form([
                TextInput::make('name')
                    ->label('Nom du dossier')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Un nouveau dossier sera créé et les fichiers sélectionnés y seront déplacés')
                    ->rules(['required', 'string', 'max:255', 'regex:/^[^<>:"|?*\/\\\]+$/']),
            ])
            ->action(function ($records, array $data) {
                try {
                    $folderService = app(MediaFolderService::class);
                    $folder = $folderService->create($data['name']);

                    $count = 0;
                    foreach ($records as $mediaFile) {
                        if ($mediaFile instanceof MediaFile) {
                            $mediaFile->folder_id = $folder->id;
                            $mediaFile->save();
                            $count++;
                        }
                    }

                    $this->sendSuccessNotification("Le dossier '{$folder->name}' a été créé et {$count} fichier(s) y ont été déplacés");
                } catch (\Exception $e) {
                    $this->sendErrorNotification($e->getMessage());
                }
            })
            ->requiresConfirmation()
            ->modalHeading('Créer un dossier et déplacer les fichiers')
            ->modalDescription('Un nouveau dossier sera créé et tous les fichiers sélectionnés y seront déplacés.');
    }

    /**
     * Configure l'action via une méthode statique
     */
    public static function configureUsing(\Closure $callback): void
    {
        static::configure($callback);
    }
}






