<?php

namespace Xavier\MediaLibraryPro\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Xavier\MediaLibraryPro\Filament\Actions\Concerns\HasMediaAction;
use Xavier\MediaLibraryPro\Models\MediaFolder;
use Xavier\MediaLibraryPro\Services\MediaFolderService;

class CreateFolderAction extends Action
{
    use HasMediaAction;

    public static function getDefaultName(): string
    {
        return 'createFolder';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $folderService = app(MediaFolderService::class);
        $rootFolders = $folderService->getRootFolders();
        $folderOptions = [null => 'Racine'];
        foreach ($rootFolders as $folder) {
            $folderOptions[$folder->id] = $folder->name;
        }

        $this->label('Créer un dossier')
            ->icon('heroicon-o-folder-plus')
            ->color('primary')
            ->form([
                TextInput::make('name')
                    ->label('Nom du dossier')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Le nom ne peut pas contenir les caractères : < > : " | ? * / \')
                    ->rules(['required', 'string', 'max:255', 'regex:/^[^<>:"|?*\/\\\]+$/']),
                Select::make('parent_id')
                    ->label('Dossier parent')
                    ->options($folderOptions)
                    ->nullable()
                    ->searchable()
                    ->helperText('Laissez vide pour créer à la racine')
            ])
            ->action(function (array $data) use ($folderService) {
                try {
                    $parent = $data['parent_id'] ? MediaFolder::find($data['parent_id']) : null;
                    $folder = $folderService->create($data['name'], $parent);
                    $this->sendSuccessNotification("Le dossier '{$folder->name}' a été créé avec succès");
                } catch (\Exception $e) {
                    $this->sendErrorNotification($e->getMessage());
                }
            })
            ->modalHeading('Créer un nouveau dossier')
            ->modalDescription('Créez un nouveau dossier pour organiser vos médias.');
    }

    /**
     * Configure l'action via une méthode statique
     */
    public static function configureUsing(\Closure $callback): void
    {
        static::configure($callback);
    }
}





