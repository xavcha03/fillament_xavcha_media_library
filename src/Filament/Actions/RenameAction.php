<?php

namespace Xavier\MediaLibraryPro\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Xavier\MediaLibraryPro\Filament\Actions\Concerns\HasMediaAction;
use Xavier\MediaLibraryPro\Models\MediaFile;

class RenameAction extends Action
{
    use HasMediaAction;

    public static function getDefaultName(): string
    {
        return 'renameMedia';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Renommer')
            ->icon('heroicon-o-pencil')
            ->color('warning')
            ->form([
                TextInput::make('file_name')
                    ->label('Nom du fichier')
                    ->required()
                    ->maxLength(255)
                    ->default(fn (MediaFile $record) => pathinfo($record->file_name, PATHINFO_FILENAME))
                    ->helperText('L\'extension sera conservée automatiquement')
            ])
            ->action(function (MediaFile $record, array $data) {
                try {
                    $record->rename($data['file_name']);
                    $this->sendSuccessNotification('Le fichier a été renommé avec succès');
                } catch (\Exception $e) {
                    $this->sendErrorNotification($e->getMessage());
                }
            })
            ->requiresConfirmation()
            ->modalHeading('Renommer le fichier')
            ->modalDescription('Entrez le nouveau nom du fichier. L\'extension sera conservée automatiquement.');
    }

    /**
     * Configure l'action via une méthode statique
     */
    public static function configureUsing(\Closure $callback): void
    {
        static::configure($callback);
    }
}



